<?php

use Oxygen\Preferences\Loader\Database\PreferenceRepositoryInterface;
use Oxygen\Preferences\Loader\DatabaseLoader;

Preferences::register('modules.marketplace', function($schema) {
    $schema->setTitle('Marketplace');
    $schema->setLoader(new DatabaseLoader(app(PreferenceRepositoryInterface::class), 'modules.marketplace'));

    $schema->makeFields([
        '' => [
            'Default Search' => [
                [
                    'name' => 'defaultSearch.query',
                    'label' => 'Query'
                ],
                [
                    'name' => 'defaultSearch.tag',
                    'label' => 'Tag'
                ],
                [
                    'name' => 'defaultSearch.type',
                    'label' => 'Type'
                ]
            ]
        ]
    ]);
});