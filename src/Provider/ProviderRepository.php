<?php

namespace OxygenModule\Marketplace\Provider;

use Illuminate\Contracts\Config\Repository;
use Oxygen\Preferences\PreferencesManager;

class ProviderRepository {

    /**
     * Preferences Repository
     *
     * @var PreferencesManager
     */
    protected $preferences;

    protected $config;

    /**
     * Constructor.
     *
     * @param PreferencesManager                      $repository
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(PreferencesManager $repository, Repository $config) {
        $this->preferences = $repository;
        $this->config = $config;
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
     * Detects if the provider is a core service provider and thus can't be disabled.
     *
     * @param $provider
     * @return bool
     */
    public function isCore($provider) {
        return count(array_filter($this->config->get('app.providers'), function($value) use($provider) {
            return $value === $provider;
        })) > 0;
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
        return $this->preferences->get('providers::list');
    }

    /**
     * Updates the providers
     *
     * @param array
     */

    protected function updateProviders(array $providers) {
        $schema = $this->preferences->getSchema('providers');
        $schema->getRepository()->set('list', $providers);
        $schema->storeRepository();
    }

}