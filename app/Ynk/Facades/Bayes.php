<?php
namespace Ynk\Facades;

use Illuminate\Support\Facades\Facade;

class Bayes extends Facade {

    protected static function getFacadeAccessor()
    {
        return 'bayes';
    }
} 