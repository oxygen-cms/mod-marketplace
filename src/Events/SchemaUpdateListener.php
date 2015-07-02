<?php

namespace OxygenModule\Marketplace\Events;

use Composer\Progress\ProgressInterface;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Output\OutputInterface;

class SchemaUpdateListener {

    /**
     * Constructs the SchemaUpdateListener
     *
     * @param SchemaTool           $schema
     * @param ClassMetadataFactory $metadata
     */
    public function __construct(SchemaTool $schema, ClassMetadataFactory $metadata) {
        $this->schema = $schema;
        $this->metadata = $metadata;
    }

    /**
     * Updates the database schema.
     *
     * @param ProgressInterface $progress
     * @param OutputInterface   $output
     */
    public function handle($progress, $output) {
        $progress->section('Updating Database Schema');
        $progress->indeterminate();

        $metadata = $this->metadata->getAllMetadata();

        $sql = $this->schema->getUpdateSchemaSql($metadata);
        if(empty($sql)) {
            $output->writeln('No Updates Found');
        } else {
            $output->writeln('Updating Schema using SQL:' . "\n" . implode(';' . PHP_EOL, $sql));
            $this->schema->updateSchema($metadata);
        }
    }

}