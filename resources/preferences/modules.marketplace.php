<?php

use Oxygen\Preferences\Loader\ConfigLoader;

Preferences::register('modules.marketplace', function($schema) {
    $schema->setTitle('Marketplace');
    $schema->setLoader(new ConfigLoader(App::make('config'), 'oxygen/marketplace::config'));

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
                    'label' => 'type'
                ]
            ]
        ]
    ]);
});