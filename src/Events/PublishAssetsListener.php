<?php

namespace Oxygen\Marketplace\Events;

use Composer\Progress\ProgressInterface;
use Illuminate\Foundation\Application;
use Symfony\Component\Console\Output\OutputInterface;

class PublishAssetsListener {

    /**
     * Constructs the MigrationListener
     *
     * @param Application $application
     */
    public function __construct(Application $application) {
        $this->app = $application;
    }

    /**
     * Updates the database schema.
     *
     * @param ProgressInterface $progress
     * @param OutputInterface   $output
     */
    public function handle($progress, $output) {
        $this->progress = $progress;

        $this->progress->section('Publishing Assets');

        $this->app->register('Illuminate\Foundation\Providers\PublisherServiceProvider');
        $publisher = $this->app->make('asset.publisher');

        $vendorPath = base_path() . '/vendor';

        $packages = $this->getPackages($vendorPath, function($directory) {
            return is_dir($directory . '/public');
        });

        $this->progress->total(count($packages));

        if(empty($packages)) {
            $output->writeln('No Packages Need Their Assets Published');
        }

        foreach($packages as $package) {
            $this->progress->write('Publishing assets for ' . $package);

            $publisher->publishPackage($package, $vendorPath);

            $output->writeln('Published assets for ' . $package);
        }
    }

    /**
     * Returns an array of packages that are suitable
     *
     * @param $vendorPath
     * @param $callback
     * @return array
     */

    protected function getPackages($vendorPath, $callback) {
        $this->progress->write('Listing Packages');

        $vendorLength = strlen($vendorPath);
        $packages = [];
        foreach(glob($vendorPath . '/*/*', GLOB_ONLYDIR) as $directory) {
            if(!$callback($directory)) {
                continue;
            }

            $packages[] = substr($directory, $vendorLength + 1);
        }

        return $packages;
    }

}