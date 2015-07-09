<?php

use Oxygen\Core\Form\FieldMetadata;
use Oxygen\Core\Html\Dialog\Dialog;
use Oxygen\Core\Html\Toolbar\ActionToolbarItem;
use Oxygen\Core\Html\Toolbar\Factory\FormToolbarItemFactory;
use OxygenModule\Marketplace\Controller\MarketplaceController;

Blueprint::make('Marketplace', function($blueprint) {
    $blueprint->setController(MarketplaceController::class);
    $blueprint->disablePluralForm();
    $blueprint->setIcon('cloud');

    $blueprint->setToolbarOrders([
        'section' => ['getHome.search', 'postInstall', 'getInstalled'],
        'item' => ['getDetails', 'postRequire'],
        'provider' => ['postToggleProvider']
    ]);

    $blueprint->makeAction([
        'name' => 'getHome',
        'pattern' => '/'
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'getHome',
        'label' => 'Marketplace',
        'icon' => 'shopping-cart'
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'getHome',
        'identifier' => 'getHome.search',
        'fields' => function() {
            $query = new FieldMetadata('q', 'search', true);
            $query->label = 'Query';
            $query->placeholder = 'Search for Packages';
            $query->attributes['results'] = 5;

            return [
                $query
            ];
        }
    ], new FormToolbarItemFactory());

    $blueprint->makeAction([
        'name' => 'postInstall',
        'pattern' => 'install',
        'method' => 'POST'
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'postInstall',
        'label' => 'Run Installer',
        'icon' => 'download',
        'color' => 'green',
        'dialog' => new Dialog(Lang::get('oxygen/mod-marketplace::dialogs.runInstaller'))
    ])->addDynamicCallback(function($item, $arguments) {
        $installer = Marketplace::getInstaller();
        if($installer->isInstalling()) {
            $item->color = 'white';
            $item->icon = 'info-circle';
            $item->label = 'Install Progress';
            $item->dialog = null;
        }
    });

    $blueprint->makeAction([
        'name' => 'getInstallProgress',
        'pattern' => 'install/progress'
    ]);

    $blueprint->makeAction([
        'name' => 'deleteInstallProgress',
        'pattern' => 'install/progress',
        'method' => 'DELETE'
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'deleteInstallProgress',
        'label' => 'Clear Install Log',
        'icon' => 'trash-o',
        'color' => 'red',
        'shouldRenderCallback' => function($item, $arguments) {
            return
                $item->shouldRenderBasic($arguments) &&
                Marketplace::getInstaller()->hasInstallProgress();
        }
    ]);

    $blueprint->makeAction([
        'name' => 'postInstallProgress',
        'pattern' => 'install/progress',
        'method' => 'POST'
    ]);

    $blueprint->makeAction([
        'name' => 'getDetails',
        'pattern' => '{vendor}/{package}',
        'routeParametersCallback' => function($action, array $options) {
            return [
                $options['vendor'],
                $options['package']
            ];
        }
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'getDetails',
        'label' => 'Show Details',
        'icon' => 'info-circle'
    ]);

    $blueprint->makeAction([
        'name' => 'postRequire',
        'pattern' => '{vendor}/{package}/require/{version?}',
        'method' => 'POST',
        'routeParametersCallback' => function($action, array $options) {
            return [
                $options['vendor'],
                $options['package']
            ];
        }
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'postRequire',
        'label' => 'Require',
        'icon' => 'plus',
        'color' => 'green'
    ])->addDynamicCallback(function($item, $arguments) {
        $installer = Marketplace::getInstaller();
        $package = $arguments['vendor'] . '/' . $arguments['package'];
        if($installer->isRequired($package)) {
            $item->color = 'red';
            $item->icon = 'trash-o';
            $item->label = 'Remove';
        }
    });

    $blueprint->makeAction([
        'name' => 'getInstalled',
        'pattern' => 'installed'
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'getInstalled',
        'label' => 'Installed Packages',
        'icon' => 'list',
        'color' => 'white'
    ]);

    $blueprint->makeAction([
        'name' => 'postToggleProvider',
        'pattern' => 'provider/toggle/{class}',
        'method' => 'POST',
        'routeParametersCallback' => function($action, array $options) {
            return [
                $options['provider']
            ];
        }
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'postToggleProvider',
        'label' => 'Enable',
        'icon' => 'check',
        'color' => 'white',
        'shouldRenderCallback' => function(ActionToolbarItem $item, array $arguments) {
            return $item->shouldRenderBasic($arguments) && !Marketplace::getProviderRepository()->isCore($arguments['provider']);
        }
    ])->addDynamicCallback(function($item, $arguments) {
        $repository = Marketplace::getProviderRepository();
        if($repository->isEnabled($arguments['provider'])) {
            $item->icon = 'power-off';
            $item->label = 'Disable';
        }
    });

});