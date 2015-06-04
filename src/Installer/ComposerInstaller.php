<?php

namespace Oxygen\Marketplace\Installer;

use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Queue\QueueManager;

class ComposerInstaller implements InstallerInterface {

    /**
     * Filesystem instance.
     *
     * @var Filesystem
     */

    protected $files;

    /**
     * QueueManager instance.
     *
     * @var QueueManager
     */

    protected $queue;

    /**
     * Config Repository instance.
     *
     * @var Repository
     */

    protected $config;

    /**
     * Path to the composer.json file.
     *
     * @var string
     */

    protected $composerFilename;

    /**
     * Contents of the composer.json file.
     *
     * @var string
     */

    protected $composerFileContents;

    /**
     * Path to the installed.json file.
     *
     * @var string
     */

    protected $installedFilename;

    /**
     * Contents of the installed.json file.
     *
     * @var string
     */

    protected $installedFileContents;

    /**
     * Constructs the ComposerInstaller.
     *
     * @param Filesystem $files
     * @param QueueManager $queue
     * @param string $composerFilename path to the composer.json file
     * @param string $installedFilename path to the installed.json file
     */
    public function __construct(Filesystem $files, QueueManager $queue, Repository $config, $composerFilename, $installedFilename) {
        $this->files = $files;
        $this->queue = $queue;
        $this->config = $config;
        $this->composerFilename = $composerFilename;
        $this->installedFilename = $installedFilename;
    }

    /**
     * Adds the given package.
     *
     * @param string $package
     * @param string $version
     * @return void
     */
    public function add($package, $version) {
        $composer = $this->getComposer();
        if(!isset($composer['require'])) {
            $composer['require'] = [];
        }
        $composer['require'][$package] = $version;
        $this->putComposer($composer);
    }

    /**
     * Removes the given package.
     *
     * @param string $package
     * @return void|boolean
     */
    public function remove($package) {
        $composer = $this->getComposer();
        if(!isset($composer['require'][$package])) {
            return false;
        }
        unset($composer['require'][$package]);
        $this->putComposer($composer);
    }

    /**
     * Determines the given package is required.
     *
     * @param string $package
     * @return boolean
     */
    public function isRequired($package) {
        $composer = $this->getComposer();
        return isset($composer['require'][$package]);
    }

    /**
     * Determines if the specified package has been installed already.
     *
     * @param string $name
     * @return boolean
     */
    public function isInstalled($name) {
        $installed = $this->getInstalledPackages();
        $filtered = array_filter($installed, function($package) use($name) {
            return $package['name'] === $name;
        });
        return count($filtered) === 1;
    }

    /**
     * Retrieves the package's installation status.
     *
     * @param string $name
     * @return string
     */
    public function getStatus($name) {
        $required = $this->isRequired($name);
        $installed = $this->isInstalled($name);

        if($required && $installed) {
            return 'Installed';
        } else if($required && !$installed) {
            return 'Pending Installation';
        } else {
            return 'Not Required';
        }
    }

    /**
     * Installs dependencies.
     *
     * @return boolean
     */
    public function install() {
        if(!$this->isInstalling()) {
            $this->queue->push('Oxygen\Marketplace\Installer\ComposerInstallJob');
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determines if the system is currently installing packages.
     *
     * @return boolean
     */
    public function isInstalling() {
        return
            $this->files->exists($this->config->get('oxygen/marketplace::config.install.progress')) &&
            $this->files->exists($this->config->get('oxygen/marketplace::config.install.log'));
    }

    /**
     * Returns the progress for the installation.
     *
     * @return array
     */
    public function getInstallProgress() {
        if(!$this->hasInstallProgress()) {
            return false;
        }

        $log = $this->config->get('oxygen/marketplace::config.install.log');
        $progress = $this->config->get('oxygen/marketplace::config.install.progress');

        $log = $this->files->get($log);
        $response = json_decode($this->files->get($progress), true);
        $response['log'] = $log;
        return $response;
    }

    /**
     * Clears installation progress.
     *
     * @return void
     */
    public function clearInstallProgress() {
        $files = [
            $this->config->get('oxygen/marketplace::config.install.log'),
            $this->config->get('oxygen/marketplace::config.install.progress')
        ];

        foreach($files as $file) {
            $this->files->delete($file);
        }
    }

    /**
     * Determines if the installation progress exists.
     *
     * @return boolean
     */
    public function hasInstallProgress() {
        $log = $this->config->get('oxygen/marketplace::config.install.log');
        $progress = $this->config->get('oxygen/marketplace::config.install.progress');
        if(!$this->files->exists($log) || !$this->files->exists($progress)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Returns an array form of the composer.json file.
     *
     * @return array
     */

    protected function getComposer() {
        if($this->composerFileContents === null) {
            $this->composerFileContents = json_decode($this->files->get($this->composerFilename), true);
        }
        return $this->composerFileContents;
    }

    /**
     * Stores the given array in the composer.json file.
     *
     * @param array $new
     * @return void
     */

    protected function putComposer(array $new) {
        $contents = json_encode($new, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->files->put($this->composerFilename, $contents);
        $this->composerFileContents = $contents;
    }

    /**
     * Returns an array form of the installed.json file.
     *
     * @return array
     */
    public function getInstalledPackages() {
        if($this->installedFileContents === null) {
            $this->installedFileContents = json_decode($this->files->get($this->installedFilename), true);
        }
        return $this->installedFileContents;
    }

}