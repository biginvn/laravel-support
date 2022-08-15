<?php

namespace Bigin\Support\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

trait UserTimezoneTrait
{
    /**
     * Get TimeZone
     *
     * @return String
     */
    public function getDefaultTimezone(string $timezone = null): string
    {
        if ($timezone) {
            return $timezone;
        }

        $authenticated = Auth::user();

        if ($authenticated && $authenticated->timezone) {
            return $authenticated->timezone;
        }

        return $this->getDefaultTimezone(Config::get('app.timezone', 'UTC'));
    }
}
