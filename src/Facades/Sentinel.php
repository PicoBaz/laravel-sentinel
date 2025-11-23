<?php

namespace PicoBaz\Sentinel\Facades;

use Illuminate\Support\Facades\Facade;

class Sentinel extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sentinel';
    }
}
