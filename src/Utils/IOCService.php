<?php

namespace Bigin\Support\Utils;

use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class IOCService
{
    const DEFAULT_METHOD = 'singleton';

    /**
     * The application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * IOCService constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param array $repositories
     * @return $this
     */
    public function repositories(array $repositories)
    {
        foreach ($repositories as $repositoryInterface => $config) {
            # code...
            list($repositoryInstance, $repositoryCacheInstance, $entity) = $config;
            $method = Arr::get($config, 'method', 'singleton');

            $this->app->{$method}($repositoryInterface, function () use ($repositoryInstance, $repositoryCacheInstance, $entity) {
                return new $repositoryCacheInstance(new $repositoryInstance(new $entity));
            });
        }

        return $this;
    }

    /**
     * @param array $services
     * @param string $defaultMethod
     * @return $this
     */
    public function services(array $services, string $defaultMethod = IOCService::DEFAULT_METHOD)
    {
        foreach ($services as $serviceInterface => $serviceInstance) {
            $method = $defaultMethod;
            if (is_array($serviceInstance)) {
                [$method, $serviceInstance] = $serviceInstance;
            }
            $this->app->{$method}($serviceInterface, $serviceInstance);
        }

        return $this;
    }

    /**
     * @param array $events
     * @return $this
     */
    public function events(array $events)
    {
        foreach ($events as $event => $handlers) {
            foreach (is_array($handlers) ? $handlers : [$handlers] as $handler) {
                $this->app['events']->listen($event, $handler);
            }
        }
        return $this;
    }

    /**
     * @param array $commands
     * @return $this
     */
    public function commands(array $commands)
    {
        Artisan::starting(function ($artisan) use ($commands) {
            $artisan->resolveCommands($commands);
        });
        return $this;
    }

    /**
     * Register all of the commands in the given directory.
     *
     * @param array|string $paths
     * @param mixed $namespace
     * @throws \ReflectionException
     */
    public function loadCommands($paths, $namespace = null)
    {
        $paths = array_unique(Arr::wrap($paths));

        $paths = array_filter($paths, function ($path) {
            return is_dir($path);
        });

        if (empty($paths)) {
            return;
        }

        if ($namespace) {
            $namespacePath = base_path($namespace);
            $namespace = "{$namespace}\\";
        } else {
            $namespace = $this->app->getNamespace();
            $namespacePath = app_path();
        }

        foreach ((new Finder())->in($paths)->files() as $command) {
            $command = $namespace . str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($command->getRealPath(), realpath($namespacePath) . DIRECTORY_SEPARATOR)
                );

            if (is_subclass_of($command, Command::class) &&
                !(new \ReflectionClass($command))->isAbstract()) {
                Artisan::starting(function ($artisan) use ($command) {
                    $artisan->resolve($command);
                });
            }
        }
    }
}
