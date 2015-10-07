<?php namespace Michaeljennings\Laralastica;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class LaralasticaServiceProvider extends EventServiceProvider {

    /**
     * The event handler mappings for the package.
     *
     * @var array
     */
    protected $listeners = [
        'Michaeljennings\Laralastica\Events\IndexesWhenSaved' => [
            'Michaeljennings\Laralastica\Handlers\Events\IndexesSavedModel'
        ],
        'Michaeljennings\Laralastica\Events\RemovesDocumentWhenDeleted' => [
            'Michaeljennings\Laralastica\Handlers\Events\RemovesDocumentBelongingToDeletedModel'
        ]
    ];

    /**
     * Bootstrap the application events.
     *
     * @param Dispatcher $dispatcher
     */
    public function boot(Dispatcher $dispatcher)
    {
        $this->publishes([
            __DIR__.'/../config/laralastica.php' => config_path('laralastica.php'),
        ]);

        $this->mergeConfigFrom(__DIR__.'/../config/laralastica.php', 'laralastica');

        foreach ($this->listeners as $event => $handlers) {
            foreach ($handlers as $listener) {
                $dispatcher->listen($event, $listener);
            }
        }
    }

    /**
     * Register laralastica to the app.
     * 
     * @return void
     */
    public function register()
    {
        $this->app->bind('Michaeljennings\Laralastica\Contracts\Wrapper', function($app)
        {
            return new Laralastica(config('laralastica'), $app['request']);
        });

        $this->app->alias('Michaeljennings\Laralastica\Contracts\Wrapper', 'laralastica');
    }
}