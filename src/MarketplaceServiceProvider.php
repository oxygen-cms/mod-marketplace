<?php

namespace Oxygen\Marketplace;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Oxygen\Core\Html\Navigation\Navigation;
use Oxygen\Marketplace\Loader\PackagistLoader;
use Oxygen\Marketplace\Installer\ComposerInstaller;
use Oxygen\Marketplace\Provider\ProviderRepository;
use Oxygen\Marketplace\Upgrader\FilesystemUpgrader;

class MarketplaceServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */

	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */

	public function boot() {
		$this->package('oxygen/marketplace', 'oxygen/marketplace', __DIR__ . '/../resources');

		$this->app['oxygen.blueprintManager']->loadDirectory(__DIR__ . '/../resources/blueprints');
		$this->app['oxygen.preferences']->loadDirectory(__DIR__ . '/../resources/preferences');

		$this->addNavigationItems();
	}

	/**
	 * Adds items the the admin navigation.
	 *
	 * @return void
	 */

	public function addNavigationItems() {
		$blueprint = $this->app['oxygen.blueprintManager']->get('Marketplace');

		$this->app['oxygen.navigation']->add($blueprint->getToolbarItem('getHome'));
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */

	public function register() {
		$this->app->bindShared(['oxygen.marketplace' => 'Oxygen\Marketplace\Marketplace'], function($app) {
	        return new Marketplace(
	        	new PackagistLoader(
					new Client(['base_url' => 'http://packagist.org/']),
					$app['cache'],
					$app['config']
				),
	        	new ComposerInstaller(
	        		$app['files'],
	        		$app['queue'],
					$app['config'],
	        		$app['path.base'] . '/composer.json',
	        		$app['path.base'] . '/vendor/composer/installed.json'
	        	),
				new FilesystemUpgrader(
					$app['files'],
					__DIR__ . '/../resources/packages'
				),
				new ProviderRepository(
					$app['config']
				)
	        );
	    });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */

	public function provides() {
		return [];
	}

}
