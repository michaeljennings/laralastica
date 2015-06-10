<?php namespace Michaeljennings\Laralastica;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class LaralasticaServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

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

        $dispatcher->listen(
            'Michaeljennings\Laralastica\Events\IndexesWhenSaved',
            'Michaeljennings\Laralastica\Handlers\Events\IndexesWhenSaved'
        );
    }

    /**
     * Register laralastica to the app.
     * 
     * @return void
     */
    public function register()
    {
        $this->app->bind('Michaeljennings\Laralastica\Contracts\Wrapper', function()
        {
            return new Laralastica(config('laralastica'));
        });

        $this->app->alias('laralastica', 'Michaeljennings\Laralastica\Contracts\Wrapper');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laralastica', 'Michaeljennings\Laralastica\Contracts\Wrapper'];
    }

}