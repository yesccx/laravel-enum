<?php

declare(strict_types = 1);

namespace Yesccx\Enum;

use Illuminate\Support\ServiceProvider;
use Yesccx\Enum\Console\EnumCacheCommand;
use Yesccx\Enum\Console\EnumClearCommand;
use Yesccx\Enum\Kernel\AnnotationEnumCollector;

final class EnumServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();

        AnnotationEnumCollector::loadCacheFile();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerCommands();
        $this->registerPublishing();
    }

    /**
     * Setup the configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/enum.php',
            'enum'
        );
    }

    /**
     * Register the package's commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            EnumClearCommand::class,
            EnumCacheCommand::class,
        ]);
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/enum.php' => config_path('enum.php'),
        ], 'enum-config');
    }
}
