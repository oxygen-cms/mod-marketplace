<?php

namespace Oxygen\Marketplace\Installer;

use Composer\Progress\FileProgress;
use Exception;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ComposerInstallJob {

    /**
     * Filesystem instance.
     *
     * @var Filesystem
     */

    protected $files;

    /**
     * Repository instance.
     *
     * @var Repository
     */

    protected $config;

    /**
     * Constructs the Install command.
     *
     * @param Filesystem $filesystem
     * @param Repository $repository
     */

    public function __construct(Filesystem $filesystem, Repository $repository) {
        $this->files = $filesystem;
        $this->config = $repository;
    }

    /**
     * Fires the job.
     *
     * @param $job
     * @param array $data
     * @return void
     */

    public function fire($job, $data) {
        echo 'Running: Oxygen\Marketplace\Installer\Install';

        // Composer\Factory::getHomeDir() method
        // needs COMPOSER_HOME environment variable set
        putenv('COMPOSER=' . base_path() . '/composer.json');

        $log = $this->config->get('oxygen/marketplace::config.install.log');
        $progress = $this->config->get('oxygen/marketplace::config.install.progress');
        foreach([$log, $progress] as $file) {
            $this->files->delete($file);
            if(!$this->files->exists(dirname($file))) {
                $this->files->makeDirectory(dirname($file));
            }
        }

        $input = new ArrayInput(['command' => 'update']);
        $output = new StreamOutput(fopen($log, 'a', false), OutputInterface::VERBOSITY_DEBUG);
        $progress = new FileProgress($progress, $output);
        $progress->section('Beginning Installation');
        $progress->indeterminate();
        $progress->notification('Installation Started');
        $application = new Application();
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);

        try {
            $application->run($input, $output, $progress);
            $progress->notification('Installation Complete');
            $progress->section('Complete');
            $progress->stopPolling();

            $job->delete();
        } catch(Exception $e) {
            $progress->notification($e->getMessage(), 'failed');
            $progress->stopPolling();
        }


    }

}