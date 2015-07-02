<?php

namespace OxygenModule\Marketplace\Controller;

use App;
use Exception;
use Marketplace;
use Input;
use File;
use Config;
use Lang;
use OxygenModule\Marketplace\SearchQueryFieldSet;
use Paginator;
use Redirect;
use Validator;
use View;
use Response;

use Oxygen\Core\Blueprint\BlueprintManager;
use Oxygen\Core\Controller\BlueprintController;
use Oxygen\Core\Http\Notification;
use OxygenModule\Marketplace\Loader\LoadingException;

class MarketplaceController extends BlueprintController {

    /**
     * @var \OxygenModule\Marketplace\SearchQueryFieldSet
     */
    private $searchFields;

    /**
     * Constructs the PagesController.
     *
     * @param BlueprintManager                              $manager
     * @param \OxygenModule\Marketplace\SearchQueryFieldSet $searchFields
     * @throws \Oxygen\Core\Blueprint\BlueprintNotFoundException
     */
    public function __construct(BlueprintManager $manager, SearchQueryFieldSet $searchFields) {
        parent::__construct($manager->get('Marketplace'));
        $this->searchFields = $searchFields;
    }

    /**
     * Home page of the marketplace.
     *
     * @return Response
     */
    public function getHome() {
        try {
            $results = Marketplace::search($this->getFilters(Input::all()));
        } catch(LoadingException $e) {
            return View::make('oxygen/mod-marketplace::error', [
                'exception' => $e,
                'title' => Lang::get('oxygen/mod-marketplace::ui.error.title')
            ]);
        }

        if(!empty($results['results'])) {
            $paginator = Paginator::make($results['results'], $results['total'], count($results['results']));
        } else {
            $paginator = null;
        }

        return View::make('oxygen/mod-marketplace::home', [
            'results' => $results,
            'paginator' => $paginator,
            'fields' => $this->searchFields,
            'title' => Lang::get('oxygen/mod-marketplace::ui.home.title')
        ]);
    }

    /**
     * Parses the request input into an array of filters.
     *
     * @param $input
     * @return array
     */

    protected function getFilters($input) {
        if(!isset($input['scope'])) {
            $input['scope'] = Config::get('oxygen/mod-marketplace::defaultScope');
        }
        $scope = Config::get('oxygen/mod-marketplace::scope.' . $input['scope']);
        unset($input['scope']);

        if(isset($scope['q'])) {
            $input['q'] = str_replace('{q}', isset($input['q']) ? $input['q'] : '', $scope['q']);
        }

        if(isset($scope['tags'])) {
            $input['tags'] = array_merge((array) $scope['tags'], isset($input['tags']) ? (array) $input['tags'] : []);
        }

        if(isset($scope['type'])) {
            $input['type'] = $scope['type'];
        }

        if(isset($input['type']) && $input['type'] === '') {
            unset($input['type']);
        }

        return $input;
    }

    /**
     * Returns details about the given package.
     *
     * @param string $vendor
     * @param string $package
     * @return Response
     */
    public function getDetails($vendor, $package) {
        $name = $vendor . '/' . $package;

        try {
            $package = Marketplace::get($name);
        } catch(LoadingException $e) {
            return View::make('oxygen/mod-marketplace::error', [
                'exception' => $e
            ]);
        }

        return View::make('oxygen/mod-marketplace::view', [
            'package' => $package,
            'title' => Lang::get('oxygen/mod-marketplace::ui.view.title', ['name' => $package->getPrettyName()])
        ]);
    }

    /**
     * Requires the given package.
     *
     * @param string $vendor
     * @param string $package
     * @param string $version
     * @return Response
     */
    public function postRequire($vendor, $package, $version = '*') {
        $name = $vendor . '/' . $package;

        if(Marketplace::getInstaller()->isRequired($name)) {
            Marketplace::getInstaller()->remove($name);

            return Response::notification(
                new Notification(Lang::get('oxygen/mod-marketplace::messages.removed')),
                ['refresh' => true]
            );
        } else {
            Marketplace::getInstaller()->add($name, $version);

            return Response::notification(
                new Notification(Lang::get('oxygen/mod-marketplace::messages.added')),
                ['refresh' => true]
            );
        }
    }

    /**
     * Sends the install request.
     * The installation will begin to process in the background.
     *
     * @return Response
     */
    public function postInstall() {
        $sentRequest = Marketplace::getInstaller()->install();
        $route = $this->blueprint->getRouteName('getInstallProgress');

        if($sentRequest) {
            return Response::notification(
                new Notification(Lang::get('oxygen/mod-marketplace::messages.installRequestSent')),
                ['redirect' => $route]
            );
        } else {
            return Redirect::route($this->blueprint->getRouteName('getInstallProgress'));
        }
    }

    /**
     * Shows a view that shows installation progress.
     *
     * @return Response
     */
    public function getInstallProgress() {
        return View::make('oxygen/mod-marketplace::installProgress', [
            'title' => Lang::get('oxygen/mod-marketplace::ui.installProgress.title')
        ]);
    }

    /**
     * Returns the installation progress.
     *
     * @return string
     */
    public function postInstallProgress() {
        $response = Marketplace::getInstaller()->getInstallProgress();

        if($response === false) {
            return Response::json([
                'progress' => false,
                'notification' => ['status' => 'failed', 'content' => Lang::get('oxygen/mod-marketplace::messages.logNotFound'), 'unique' => 'notStarted']
            ]);
        }

        if(isset($response['stopPolling']) && $response['stopPolling'] === true) {
            Marketplace::getInstaller()->clearInstallProgress();
        }

        return Response::json($response);
    }

    /**
     * Returns the installation progress.
     *
     * @return string
     */
    public function deleteInstallProgress() {
        Marketplace::getInstaller()->clearInstallProgress();

        return Response::notification(
            new Notification(Lang::get('oxygen/mod-marketplace::messages.logCleared')),
            ['refresh' => true]
        );
    }

    /**
     * Lists installed packages.
     *
     * @return Response
     */
    public function getInstalled() {
        $installed = Marketplace::getInstalledPackages($this->getFilters(Input::all()));
        $page = (int) Input::get('page', 1) - 1;
        $chunk = array_chunk($installed, 30);
        $chunk = isset($chunk[$page]) ? $chunk[$page] : [];
        $paginator = empty($chunk) ? [] : Paginator::make($chunk, count($installed), 30);
        return View::make('oxygen/mod-marketplace::installed', [
            'installed' => $chunk,
            'paginator' => $paginator,
            'fields' => $this->searchFields,
            'title' => Lang::get('oxygen/mod-marketplace::ui.installed.title')
        ]);
    }

    /**
     * Enables/disables the service provider.
     *
     * @return Response
     */
    public function postToggleProvider($provider) {
        $repository = Marketplace::getProviderRepository();

        if(!class_exists($provider)) {
            return Response::notification(
                new Notification(Lang::get('oxygen/mod-marketplace::messages.provider.classNotFound'), Notification::FAILED)
            );
        }

        try {
            $object = new $provider(App::make('app'));
            if(!is_subclass_of($object, 'Illuminate\Support\ServiceProvider')) {
                return Response::notification(
                    new Notification(Lang::get('oxygen/mod-marketplace::messages.provider.invalid'), Notification::FAILED)
                );
            }
        } catch(Exception $e) {
            return Response::notification(
                new Notification(Lang::get('oxygen/mod-marketplace::messages.provider.invalid'), Notification::FAILED)
            );
        }

        $repository->isEnabled($provider) ? $repository->disable($provider) : $repository->enable($provider);

        return Response::notification(new Notification(
            $repository->isEnabled($provider)
                ? Lang::get('oxygen/mod-marketplace::messages.provider.enabled')
                : Lang::get('oxygen/mod-marketplace::messages.provider.disabled')
        ), ['refresh' => true]);
    }

}