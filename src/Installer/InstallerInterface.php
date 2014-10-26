<?php

namespace Oxygen\Marketplace\Installer;

interface InstallerInterface {

    /**
     * Adds the given package.
     *
     * @param string $package
     * @param string $version
     * @return void
     */

    public function add($package, $version);

    /**
     * Removes the given package.
     *
     * @param string $package
     * @return void|boolean
     */

    public function remove($package);

    /**
     * Determines the given package is required.
     *
     * @param string $package
     * @return boolean
     */

    public function isRequired($package);

    /**
     * Determines if the specified package has been installed already.
     *
     * @param string $package
     * @return boolean
     */

    public function isInstalled($package);

    /**
     * Retrieves the package's installation status.
     *
     * @param string $package
     * @return string
     */

    public function getStatus($package);

    /**
     * Installs dependencies.
     *
     * @return boolean
     */

    public function install();

    /**
     * Determines if the system is currently installing.
     *
     * @return boolean
     */

    public function isInstalling();

    /**
     * Returns installed packages.
     *
     * @return array
     */

    public function getInstalledPackages();

}