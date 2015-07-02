<?php

namespace OxygenModule\Marketplace;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

use Oxygen\Core\Blueprint\BlueprintManager;
use Oxygen\Core\Html\Navigation\Navigation;
use OxygenModule\Marketplace\Events\MigrationListener;
use OxygenModule\Marketplace\Events\PublishAssetsListener;
use OxygenModule\Marketplace\Events\SchemaUpdateListener;
use OxygenModule\Marketplace\Loader\PackagistLoader;
use OxygenModule\Marketplace\Installer\ComposerInstaller;
use OxygenModule\Marketplace\Provider\ProviderRepository;
use OxygenModule\Marketplace\Upgrader\FilesystemUpgrader;
use Oxygen\Preferences\PreferencesManager;

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
        $this->mergeConfigFrom(__DIR__ . '/../resources/config/config.php', 'oxygen.mod-marketplace');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'oxygen/mod-marketplace');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'oxygen/mod-marketplace');

        $this->publishes([
            __DIR__ . '/../resources/lang' => base_path('resources/lang/vendor/oxygen/mod-auth'),
            __DIR__ . '/../resources/views' => base_path('resources/views/vendor/oxygen/mod-auth')
        ]);

		$this->app[BlueprintManager::class]->loadDirectory(__DIR__ . '/../resources/blueprints');
		$this->app[PreferencesManager::class]->loadDirectory(__DIR__ . '/../resources/preferences');

		$this->addNavigationItems();
	}

	/**
	 * Adds items the the admin navigation.
	 *
	 * @return void
	 */
	public function addNavigationItems() {
		$blueprint = $this->app[BlueprintManager::class]->get('Marketplace');

		$this->app[Navigation::class]->add($blueprint->getToolbarItem('getHome'));
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */

	public function register() {
		$this->app->singleton(Marketplace::class, function($app) {
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

        $this->app['events']->listen('oxygen.marketplace.postUpdate', MigrationListener::class);
        $this->app['events']->listen('oxygen.marketplace.postUpdate', PublishAssetsListener::class);
        $this->app['events']->listen('oxygen.marketplace.postUpdate', SchemaUpdateListener::class);
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
