<?php

namespace OxygenModule\Marketplace\Upgrader;

use OxygenModule\Marketplace\Package\Package;

interface UpgraderInterface {

    /**
     * Finds more information about an existing package, and 'upgrades' it.
     *
     * @param Package $package
     * @return void
     */
    public function upgrade(Package $package);

}