<?php

namespace OxygenModule\Marketplace\Events;

use Composer\Progress\ProgressInterface;
use Illuminate\Foundation\Application;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationListener {

    /**
     * Constructs the MigrationListener
     *
     * @param Application $application
     */
    public function __construct(Application $application) {
        $this->app = $application;
    }

    /**
     * Runs migrations.
     *
     * @param ProgressInterface $progress
     * @param OutputInterface   $output
     */
    public function handle($progress, $output) {
        $progress->section('Running Migrations');

        $migrator = $this->app->make('migrator');
        $paths = $this->app->make('oxygen.autoMigrator')->getPaths();

        $progress->total(count($paths));

        if(empty($paths)) {
            $output->writeln('No Migrations Found');
        }

        foreach($paths as $package => $path) {
            $progress->write('Running migrations for ' . $package);
            $migrator->run($path);
            foreach ($migrator->getNotes() as $note) {
                $output->writeln($note);
            }
            $output->writeln('Ran migrations for  ' . $package);
        }
    }

}