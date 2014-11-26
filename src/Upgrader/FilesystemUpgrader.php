<?php

namespace Oxygen\Marketplace\Upgrader;

use Illuminate\Filesystem\Filesystem;
use Oxygen\Marketplace\Package\Package;

class FilesystemUpgrader implements UpgraderInterface {

    /**
     * Filesystem instance
     *
     * @var Filesystem
     */

    protected $files;

    /**
     * Path to the Package files.
     *
     * @var string
     */

    protected $path;

    /**
     * Constructor.
     *
     * @param Filesystem $files
     * @param string     $path
     */

    public function __construct(Filesystem $files, $path) {
        $this->files = $files;
        $this->path = $path;
    }

    /**
     * Finds more information about an existing package, and 'upgrades' it.
     *
     * @param Package $package
     * @return void
     */

    public function upgrade(Package $package) {
        $file = $this->path . '/' . $package->getSplitName()[0] . '.json';
        $this->upgradeFromFile($package, $file);
        $file = $this->path . '/' . implode('.', $package->getSplitName()) . '.json';
        $this->upgradeFromFile($package, $file);
    }
    
    /**
     * @param  Package $package
     * @param  string  $file
     * @return boolean
     * @throws \Illuminate\Filesystem\FileNotFoundException
     */
    protected function upgradeFromFile(Package $package, $file) {
        if($this->files->exists($file)) {
            $package->fillFromArray(json_decode($this->files->get($file), true));
            return true;
        } else {
            return false;
        }
    }

}