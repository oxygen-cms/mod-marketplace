<?php

use Oxygen\Preferences\Loader\ConfigLoader;

Preferences::register('oxygen.marketplace', function($schema) {
    $schema->setTitle('Marketplace');
    $schema->setLoader(new ConfigLoader(App::make('config'), 'oxygen/marketplace::config'));

    $schema->makeFields([
        '' => [
            'Default' => [
                [
                    'name' => 'defaultSearch.query'
                ],
                [
                    'name' => 'defaultSearch.tag'
                ]
            ]
        ]
    ]);
});