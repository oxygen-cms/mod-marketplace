<?php

use Illuminate\Database\Migrations\Migration;
use Oxygen\Preferences\Loader\Database\PreferenceRepositoryInterface;
use Oxygen\Preferences\Repository;

class CreateAuthPreferences extends Migration {

    /**
     * Run the migrations.
     *
     * @param \Oxygen\Preferences\Loader\Database\PreferenceRepositoryInterface $preferences
     */
    public function up(PreferenceRepositoryInterface $preferences) {
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
     * @param \Oxygen\Preferences\Loader\Database\PreferenceRepositoryInterface $preferences
     */
    public function down(PreferenceRepositoryInterface $preferences) {
        $preferences->delete($preferences->findByKey('providers'));
    }
}
