<?php

namespace Oxygen\Marketplace;

use Oxygen\Marketplace\Loader\LoaderInterface;
use Oxygen\Marketplace\Installer\InstallerInterface;
use Oxygen\Marketplace\Package\Package;
use Oxygen\Marketplace\Provider\ProviderRepository;
use Oxygen\Marketplace\Upgrader\UpgraderInterface;

class Marketplace {

    /**
     * LoaderInterface implementation
     *
     * @var LoaderInterface
     */

    protected $loader;

    /**
     * InstallerInterface implementation
     *
     * @var InstallerInterface
     */

    protected $installer;

    /**
     * ProviderRepository
     *
     * @var ProviderRepository
     */

    protected $providerRepository;

    /**
     * Constructs the Marketplace.
     *
     * @param LoaderInterface    $loader
     * @param InstallerInterface $installer
     * @param ProviderRepository $providers
     */

    public function __construct(LoaderInterface $loader, InstallerInterface $installer, UpgraderInterface $upgrader, ProviderRepository $providers) {
        $this->loader = $loader;
        $this->installer = $installer;
        $this->upgrader = $upgrader;
        $this->providerRepository = $providers;
    }

    /**
     * Retrieves all packages.
     *
     * @param array $filters
     * @param boolean $loadDetails
     * @return array
     */

    public function search(array $filters = [], $loadDetails = true) {
        $page = isset($filters['page']) ? $filters['page'] : 1;
        $response = $this->loader->search($filters);
        $response['results'] = $this->addPackagesFromArray($response, $loadDetails);

        return $response;
    }

    /**
     * Retrieves a package.
     *
     * @param string $name
     * @return Package
     */

    public function get($name) {
        $data = $this->loader->getPackageDetails($name)['package'];
        $package = new Package($this->loader, $data['name']);
        $package->fillFromArray($data);
        return $package;
    }

    /**
     * Loads details for multiple packages.
     *
     * @param array $packages
     * @return void
     */

    public function loadDetails($packages) {
        $results = $this->loader->getPackageDetails(array_keys($packages));

        foreach($results as $package) {
            $packages[$package['name']]->fillFromArray($package);
            $this->upgrader->upgrade($packages[$package['name']]);
        }
    }

    /**
     * Returns the installer.
     *
     * @return InstallerInterface
     */

    public function getInstaller() {
        return $this->installer;
    }

    /**
     * Returns the ProviderRepository.
     *
     * @return ProviderRepository
     */

    public function getProviderRepository() {
        return $this->providerRepository;
    }

    /**
     * Returns the installer.
     *
     * @param array $filters
     * @return array
     */

    public function getInstalledPackages(array $filters) {
        $installed = $this->installer->getInstalledPackages();

        return $this->filterInstalledPackages(
            $this->addPackagesFromArray(['results' => $installed], true),
            $filters
        );
    }

    /**
     * Adds packages from the given array.
     *
     * @param array     $response
     * @param boolean   $loadDetails
     * @return array
     */

    public function addPackagesFromArray(array $response, $loadDetails = true) {
        $packages = [];

        foreach($response['results'] as $result) {
            $package = new Package($this->loader, $result['name']);
            $package->fillFromArray($result);
            $packages[$package->getName()] = $package;
        }

        if($loadDetails) {
            $this->loadDetails($packages);
        }

        return $packages;
    }

    /**
     * Filters an array of packages.
     *
     * @param array $packages
     * @param array $filters
     * @return array
     */

    protected function filterInstalledPackages(array $packages, array $filters) {
        return array_filter($packages, function ($package) use ($filters) {
            foreach($filters as $name => $value) {
                switch($name) {
                    case 'tags':
                        foreach($value as $item) {
                            if(!in_array(strtolower($item), array_map('strtolower', $package->getKeywords()))) {
                                return false;
                            }
                        }
                        break;
                    case 'type':
                        if($value !== $package->type) {
                            return false;
                        }
                        break;
                }
            }

            $this->upgrader->upgrade($package);

            return true;
        });
    }

}