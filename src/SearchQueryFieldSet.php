<?php


namespace OxygenModule\Marketplace;

use Oxygen\Core\Form\FieldSet;

class SearchQueryFieldSet extends FieldSet {

    /**
     * Creates the fields in the set.
     *
     * @return array
     */
    public function createFields() {
        return $this->makeFields([
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
    }

    /**
     * Returns the name of the title field.
     *
     * @return mixed
     */
    public function getTitleFieldName() {
        return null;
    }
}