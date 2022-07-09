<?php namespace AJD_validation;

use Illuminate\Support\Facades\Facade;

class AJDValFacade extends Facade
{
	/**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'AJD_validation';
    }
}