<?php

use Oxygen\Core\Form\Field;
use Oxygen\Core\Html\Dialog\Dialog;
use Oxygen\Core\Html\Toolbar\Factory\FormToolbarItemFactory;

Blueprint::make('Marketplace', function($blueprint) {
    $blueprint->setController('Oxygen\Marketplace\Controller\MarketplaceController');
    $blueprint->setDisplayName('Marketplace', Blueprint::PLURAL);
    $blueprint->setIcon('cloud');

    $blueprint->setToolbarOrders([
        'section' => ['getHome.search', 'getInstall', 'getInstalled'],
        'item' => ['getDetails', 'postRequire'],
        'provider' => ['postToggleProvider']
    ]);

    $blueprint->makeAction([
        'name' => 'getHome',
        'pattern' => '/'
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'getHome',
        'label' => 'Marketplace',
        'icon' => 'shopping-cart'
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'getHome',
        'identifier' => 'getHome.search',
        'fields' => function() {
            $query = new Field('q', 'search', true);
            $query->label = 'Query';
            $query->placeholder = 'Search for Packages';
            $query->attributes['results'] = 5;

            return [
                $query
            ];
        }
    ], new FormToolbarItemFactory());

    $blueprint->makeAction([
        'name' => 'getInstall',
        'pattern' => 'install'
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'getInstall',
        'label' => 'Run Installer',
        'icon' => 'download',
        'color' => 'green',
        'dialog' => new Dialog(Lang::get('oxygen/marketplace::dialogs.runInstaller'))
    ])->addDynamicCallback(function($item, $arguments) {
        $installer = Marketplace::getInstaller();
        if($installer->isInstalling()) {
            $item->color = 'white';
            $item->icon = 'info-circle';
            $item->label = 'Install Progress';
            $item->dialog = null;
        }
    });

    $blueprint->makeAction([
        'name' => 'getInstallProgress',
        'pattern' => 'install/progress'
    ]);

    $blueprint->makeAction([
        'name' => 'deleteInstallProgress',
        'pattern' => 'install/progress',
        'method' => 'DELETE'
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'deleteInstallProgress',
        'label' => 'Clear Install Log',
        'icon' => 'trash-o',
        'color' => 'red',
        'shouldRenderCallback' => function($item, $arguments) {
            return
                $item->shouldRenderBasic($arguments) &&
                Marketplace::getInstaller()->hasInstallProgress();
        }
    ]);

    $blueprint->makeAction([
        'name' => 'postInstallProgress',
        'pattern' => 'install/progress',
        'method' => 'POST'
    ]);

    $blueprint->makeAction([
        'name' => 'getDetails',
        'pattern' => '{vendor}/{package}',
        'routeParametersCallback' => function($action, array $options) {
            return [
                $options['vendor'],
                $options['package']
            ];
        }
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'getDetails',
        'label' => 'Show Details',
        'icon' => 'info-circle'
    ]);

    $blueprint->makeAction([
        'name' => 'postRequire',
        'pattern' => '{vendor}/{package}/require/{version?}',
        'routeParametersCallback' => function($action, array $options) {
            return [
                $options['vendor'],
                $options['package']
            ];
        }
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'postRequire',
        'label' => 'Require',
        'icon' => 'plus',
        'color' => 'green'
    ])->addDynamicCallback(function($item, $arguments) {
        $installer = Marketplace::getInstaller();
        $package = $arguments['vendor'] . '/' . $arguments['package'];
        if($installer->isRequired($package)) {
            $item->color = 'red';
            $item->icon = 'trash-o';
            $item->label = 'Remove';
        }
    });

    $blueprint->makeAction([
        'name' => 'getInstalled',
        'pattern' => 'installed'
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'getInstalled',
        'label' => 'Installed Packages',
        'icon' => 'list',
        'color' => 'white'
    ]);

    $blueprint->makeAction([
        'name' => 'postToggleProvider',
        'pattern' => 'provider/toggle/{class}',
        'method' => 'POST',
        'routeParametersCallback' => function($action, array $options) {
            return [
                $options['provider']
            ];
        }
    ]);
    $blueprint->makeToolbarItem([
        'action' => 'postToggleProvider',
        'label' => 'Enable',
        'icon' => 'check',
        'color' => 'white'
    ])->addDynamicCallback(function($item, $arguments) {
        $repository = Marketplace::getProviderRepository();
        if($repository->isEnabled($arguments['provider'])) {
            $item->icon = 'power-off';
            $item->label = 'Disable';
            if($repository->isCore($arguments['provider'])) {
                $item->dialog = new Dialog(Lang::get('oxygen/marketplace::dialogs.disableCoreProvider'));
            }
        }
    });

    $blueprint->makeFields([
        [
            'name'              => 'q',
            'label'             => 'Query',
            'type'              => 'search',
            'placeholder'       => 'Search for Packages',
            'editable'          => true
        ],
        [
            'name'              => 'scope',
            'label'             => 'Scope',
            'type'              => 'radio',
            'editable'          => true,
            'options'           => [
                'oxygen' => 'Only Oxygen Packages',
                'all'    => 'All Composer Packages'
            ]
        ],
        [
            'name'              => 'tags',
            'label'             => 'Tags',
            'type'              => 'tags',
            'placeholder'       => 'Find by Tag',
            'editable'          => true
        ],
        [
            'name'              => 'type',
            'label'             => 'Type',
            'type'              => 'text',
            'placeholder'       => 'Package Type',
            'editable'          => true,
            'datalist'          => [
                'library',
                'symfony-bundle',
                'wordpress-plugin',
                'project',
                'metapackage',
                'composer-plugin'
            ]
        ]
    ]);

});