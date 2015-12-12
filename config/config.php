<?php

return [

    'scope' => [
        'oxygen' => [
            'tags' => 'OxygenExtension'
        ],
        'all' => []
    ],

    'defaultScope' => 'oxygen',

    'install' => [
        'progress' => storage_path() . '/marketplace/progress.json',
        'log'      => storage_path() . '/marketplace/log.txt',
        'command'  => [
            'command' => 'update',
            '--no-interaction' => true,
            '--prefer-dist' => true,
            '--no-dev' => env('COMPOSER_UPDATE_NO_DEV', true),
            '--optimize-autoloader' => true
        ]
    ]

];