<?php namespace Michaeljennings\Laralastica;

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
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/laralastica.php' => config_path('laralastica.php'),
        ]);

        $this->mergeConfigFrom(__DIR__.'/../config/laralastica.php', 'laralastica');
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