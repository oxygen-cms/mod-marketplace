<?php

use Oxygen\Preferences\Loader\Database\PreferenceRepositoryInterface;
use Oxygen\Preferences\Loader\DatabaseLoader;

Preferences::register('providers', function($schema) {
        $schema->setTitle('Service Providers');
        $schema->setLoader(new DatabaseLoader(app(PreferenceRepositoryInterface::class), 'providers'));

        $schema->makeFields([
            '' => [
                'Service Providers' => [
                    [
                        'name' => 'list',
                        'label' => 'Extra Providers'
                    ]
                ]
            ]
        ]);
    });