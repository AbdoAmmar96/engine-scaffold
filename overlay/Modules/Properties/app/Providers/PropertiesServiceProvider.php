<?php

namespace Modules\Properties\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Properties\Console\SendSearchAlerts;
use Nwidart\Modules\Support\ModuleServiceProvider;

class PropertiesServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Properties';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'properties';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    protected array $commands = [
        SendSearchAlerts::class,
    ];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
