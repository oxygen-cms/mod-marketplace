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
            '--prefer-dist' => true,
            '--no-dev' => true,
            '--optimize-autoloader' => true
        ]
    ]

];