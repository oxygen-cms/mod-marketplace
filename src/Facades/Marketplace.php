<?php

namespace Oxygen\Marketplace\Facades;

use Illuminate\Support\Facades\Facade;

class Marketplace extends Facade {

    protected static function getFacadeAccessor() {
        return 'oxygen.marketplace';
    }

}