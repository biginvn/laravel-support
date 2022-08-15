<?php

namespace Biginvn\Support\Providers;

use Biginvn\Support\Facades\IOCServiceFacade;
use Biginvn\Support\Utils\IOCService;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * @var string
     */
    protected $defaultMethod = IOCService::DEFAULT_METHOD;

    /**
     * @var array
     */
    protected $repositories = [];

    /**
     * @var array
     */
    protected $services = [];

    /**
     * @var array
     */
    protected $events = [];

    /**
     * @var array
     */
    protected $commands = [];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        IOCServiceFacade::getFacadeRoot()
            ->repositories($this->repositories)
            ->services($this->services, $this->defaultMethod)
            ->events($this->events)
            ->commands($this->commands);
    }
}
