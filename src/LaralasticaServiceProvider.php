<?php

namespace Michaeljennings\Laralastica;

use Illuminate\Support\ServiceProvider;
use Michaeljennings\Laralastica\Commands\ReIndexLaralastica;

class LaralasticaServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the package.
     *
     * @var array
     */
    protected $listeners = [
        'Michaeljennings\Laralastica\Events\IndexesWhenSaved' => [
            'Michaeljennings\Laralastica\Listeners\IndexesSavedModel'
        ],
        'Michaeljennings\Laralastica\Events\RemovesDocumentWhenDeleted' => [
            'Michaeljennings\Laralastica\Listeners\RemovesDocumentBelongingToDeletedModel'
        ]
    ];

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/laralastica.php' => config_path('laralastica.php'),
        ]);

        foreach ($this->listeners as $event => $handlers) {
            foreach ($handlers as $listener) {
                $this->app['events']->listen($event, $listener);
            }
        }
    }

    /**
     * Register any application bindings.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laralastica.php', 'laralastica');

        $this->registerClient();
        $this->registerCommands();
    }

    /**
     * Register the laralastica client.
     */
    protected function registerClient()
    {
        $this->app->bind('Michaeljennings\Laralastica\Contracts\Laralastica', function($app) {
            $manager = new ClientManager(config('laralastica'));

            return new Laralastica($manager, $app['request']);
        });

        $this->app->alias('Michaeljennings\Laralastica\Contracts\Laralastica', 'laralastica');
    }

    /**
     * Register any artisan commands.
     */
    protected function registerCommands()
    {
        $this->commands([ReIndexLaralastica::class]);
    }
}