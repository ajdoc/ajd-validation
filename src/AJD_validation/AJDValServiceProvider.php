<?php namespace AJD_validation;

use Illuminate\Support\ServiceProvider;

class AJDValServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton( 'AJD_validation', function( $app ) {

            return new AJD_validation;
            
        } );
         
    }
}
