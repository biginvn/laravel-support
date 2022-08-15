<?php

namespace Biginvn\Support\Facades;

use Illuminate\Support\Facades\Facade;

class IOCServiceFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'IOCService';
    }
}
