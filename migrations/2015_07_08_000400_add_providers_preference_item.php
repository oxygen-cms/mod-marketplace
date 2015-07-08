<?php

use Illuminate\Database\Migrations\Migration;
use Oxygen\Preferences\Loader\Database\PreferenceRepositoryInterface;
use Oxygen\Preferences\Repository;

class AddProvidersPreferenceItem extends Migration {

    /**
     * Run the migrations.
     *
     */
    public function up() {
        $preferences = App::make(PreferenceRepositoryInterface::class);

        $item = $preferences->make();
        $item->setKey('providers');
        $data = new Repository([]);
        $data->set('list', []);
        $item->setPreferences($data);
        $preferences->persist($item);
    }

    /**
     * Reverse the migrations.
     *
     */
    public function down() {
        $preferences = App::make(PreferenceRepositoryInterface::class);

        $preferences->delete($preferences->findByKey('providers'));
    }
}
