<?php namespace Michaeljennings\Laralastica;

use Illuminate\Support\ServiceProvider;

class LaralasticaServiceProvider extends ServiceProvider {

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
		//
	}

}