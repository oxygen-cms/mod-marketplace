<?php

namespace OxygenModule\Marketplace\Facades;

use Illuminate\Support\Facades\Facade;

use OxygenModule\Marketplace\Marketplace as BaseMarketplace;

class Marketplace extends Facade {

    protected static function getFacadeAccessor() {
        return BaseMarketplace::class;
    }

}