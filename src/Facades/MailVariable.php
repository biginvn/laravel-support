<?php

namespace Bigin\Support\Facades;

use Illuminate\Support\Facades\Facade;

class MailVariable extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'MailVariable';
    }
}
