<?php

namespace OxygenModule\Marketplace\Provider;

use Oxygen\Preferences\PreferencesManager;

class ProviderRepository {

    /**
     * Preferences Repository
     *
     * @var PreferencesManager
     */
    protected $preferences;

    /**
     * Constructor.
     *
     * @param PreferencesManager $repository
     */
    public function __construct(PreferencesManager $repository) {
        $this->preferences = $repository;
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