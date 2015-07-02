<?php

namespace OxygenModule\Marketplace\Installer;

use Composer\Progress\FileProgress;
use Exception;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Config\Repository;
use Illuminate\Events\Dispatcher;
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
     * Event dispatcher.
     *
     * @var Dispatcher
     */

    protected $events;

    /**
     * Laravel app.
     *
     * @var Application
     */

    protected $app;

    /**
     * Constructs the Install command.
     *
     * @param Filesystem $filesystem
     * @param Repository $repository
     * @param Dispatcher $events
     * @param LaravelApplication $app
     */
    public function __construct(Filesystem $filesystem, Repository $repository, Dispatcher $events, LaravelApplication $app) {
        $this->files = $filesystem;
        $this->config = $repository;
        $this->events = $events;
        $this->app = $app;
    }

    /**
     * Fires the job.
     *
     * @param       $job
     * @param array $data
     * @throws \Exception
     */
    public function fire($job, $data) {
        echo 'Running: OxygenModule\Marketplace\Installer\ComposerInstallJob' . "\n";

        // Composer\Factory::getHomeDir() method
        // needs COMPOSER_HOME environment variable set
        putenv('COMPOSER=' . base_path() . '/composer.json');
        putenv('COMPOSER_HOME=' . base_path() . '/.composer');

        $log = $this->config->get('oxygen/mod-marketplace::config.install.log');
        $progress = $this->config->get('oxygen/mod-marketplace::config.install.progress');
        foreach([$log, $progress] as $file) {
            $this->files->delete($file);
            if(!$this->files->exists(dirname($file))) {
                $this->files->makeDirectory(dirname($file));
            }
        }

        $input = new ArrayInput($this->config->get('oxygen/mod-marketplace::config.install.command'));
        $this->output = new StreamOutput(fopen($log, 'a', false), OutputInterface::VERBOSITY_DEBUG);
        $this->progress = new FileProgress($progress, $this->output);
        $this->progress->section('Beginning Installation');
        $this->progress->indeterminate();
        $this->progress->notification('Installation Started');
        $application = new Application();
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);

        try {
            $application->run($input, $this->output, $this->progress);

            $this->events->fire('oxygen.marketplace.postUpdate', [$this->progress, $this->output]);

            $this->progress->notification('Installation Complete');
            $this->progress->section('Complete');
            $this->progress->stopPolling();

            $job->delete();
        } catch(Exception $e) {
            $this->progress->notification($e->getMessage(), 'failed');
            $this->progress->stopPolling();
            throw $e;
        }
    }

}