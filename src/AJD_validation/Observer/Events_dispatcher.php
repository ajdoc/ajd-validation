<?php namespace AJD_validation\Observer;

use AJD_validation\Observer\Observable;
use AJD_validation\Traits\Events_dispatcher_trait;
use AJD_validation\AJD_validation;

class Events_dispatcher
{
	use Events_dispatcher_trait;

	public static function get_ajd_instance()
	{
		return AJD_validation::get_ajd_instance();
	}	

}

