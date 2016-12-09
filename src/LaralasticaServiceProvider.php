<?php

namespace Michaeljennings\Laralastica;

use Illuminate\Events\EventServiceProvider;

class LaralasticaServiceProvider extends EventServiceProvider
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

        $this->mergeConfigFrom(__DIR__.'/../config/laralastica.php', 'laralastica');

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
        $this->app->bind('Michaeljennings\Laralastica\Contracts\Laralastica', function($app) {
            $config = config('laralastica');
            $manager = new ClientManager($config);

            return new Laralastica($manager, config('laralastica'), $app['request']);
        });

        $this->app->alias('Michaeljennings\Laralastica\Contracts\Wrapper', 'laralastica');
    }
}