<?php

namespace Adaptcms\FieldFile\Facades;

use Illuminate\Support\Facades\Facade;

class FieldFile extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'FieldFile';
    }
}
