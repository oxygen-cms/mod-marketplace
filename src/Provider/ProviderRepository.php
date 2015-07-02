<?php

namespace OxygenModule\Marketplace\Provider;

use Oxygen\Core\Config\Repository;

class ProviderRepository {

    /**
     * Core providers that should not be disabled.
     *
     * @var array
     */
    public $coreProviders = [
        'Illuminate\\Auth\\AuthServiceProvider',
        'Illuminate\\Cache\\CacheServiceProvider',
        'Illuminate\\Session\\CommandsServiceProvider',
        'Illuminate\\Routing\\ControllerServiceProvider',
        'Illuminate\\Cookie\\CookieServiceProvider',
        'Illuminate\\Database\\DatabaseServiceProvider',
        'Illuminate\\Encryption\\EncryptionServiceProvider',
        'Illuminate\\Filesystem\\FilesystemServiceProvider',
        'Illuminate\\Hashing\\HashServiceProvider',
        'Illuminate\\Html\\HtmlServiceProvider',
        'Illuminate\\Log\\LogServiceProvider',
        'Illuminate\\Database\\MigrationServiceProvider',
        'Illuminate\\Pagination\\PaginationServiceProvider',
        'Illuminate\\Queue\\QueueServiceProvider',
        'Illuminate\\Auth\\Reminders\\ReminderServiceProvider',
        'Illuminate\\Database\\SeedServiceProvider',
        'Illuminate\\Session\\SessionServiceProvider',
        'Oxygen\\Core\\Translation\\TranslationServiceProvider',
        'Oxygen\\Core\\Validation\\ValidationServiceProvider',
        'Illuminate\\View\\ViewServiceProvider',
        'Illuminate\\Workbench\\WorkbenchServiceProvider',
        'Oxygen\\Core\\View\\ViewServiceProvider',
        'Oxygen\\Core\\CoreServiceProvider',
        'Oxygen\\Core\\Routing\\RoutingServiceProvider',
        'Oxygen\\Core\\Console\\ConsoleServiceProvider',
        'Oxygen\\Marketplace\\MarketplaceServiceProvider',
    ];

    /**
     * Config Repository
     *
     * @var Repository
     */

    protected $config;

    /**
     * Constructor.
     *
     * @param Repository $repository
     */
    public function __construct(Repository $repository) {
        $this->config = $repository;
    }

    /**
     * Determines whether the given provider is already enabled.
     *
     * @param $provider
     * @return boolean
     */
    public function isEnabled($provider) {
        return count(array_filter($this->getProviders(), function($value) use($provider) {
            return $value === $provider;
        })) > 0;
    }

    /**
     * Determines whether the given provider is already enabled.
     *
     * @param $provider
     * @return boolean
     */
    public function isCore($provider) {
        return in_array($provider, $this->coreProviders);
    }

    /**
     * Enables the provider
     *
     * @param $provider
     */
    public function enable($provider) {
        if($this->isEnabled($provider)) { return; }
        $providers = $this->getProviders();
        $providers[] = $provider;
        $this->updateProviders($providers);
    }

    /**
     * Disables the provider.
     *
     * @param $provider
     */
    public function disable($provider) {
        $providers = array_filter($this->getProviders(), function($value) use($provider) {
            return $value !== $provider;
        });
        $this->updateProviders($providers);
    }

    /**
     * Returns the providers
     *
     * @return array
     */

    protected function getProviders() {
        return $this->config->get('app.providers');
    }

    /**
     * Updates the providers
     *
     * @param array
     */

    protected function updateProviders(array $providers) {
        $this->config->write('app.providers', $providers);
    }

}