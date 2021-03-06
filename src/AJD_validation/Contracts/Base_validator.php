<?php namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Abstract_common;
use AJD_validation\Vefja\Vefja;
use AJD_validation\Helpers\When;

abstract class Base_validator extends Abstract_common
{
	protected static $factory;
	
	protected static $scen_ins;	
	protected static $field_scene_ins;
	protected static $lang;

	protected static function get_factory_instance()
	{
		if( IS_NULL( static::$factory ) ) 
		{
			static::$factory 	= Vefja::singleton('AJD_validation\Factory\Factory_strategy');
		}

		return static::$factory;
	}

	protected static function set_factory_instance( $factory )
	{
		static::$factory 		= $factory;
	}

	public static function get_observable_instance($singleton = true)
	{
		if($singleton)
		{
			return Vefja::singleton('AJD_validation\Observer\Observable');
		}
		else
		{

			return Vefja::instance('AJD_validation\Observer\Observable');	
		}
	}

	protected static function get_filter_ins()
	{
		return Vefja::singleton('AJD_validation\Helpers\AJD_filter');
	}

	protected static function getMetadata()
	{
		return Vefja::singleton('AJD_validation\Helpers\Metadata');
	}

	protected static function get_scene_ins( $rule, $logic, $not_once = FALSE, When $when = NULL )
	{
		/*if( IS_NULL( static::$scen_ins ) OR $not_once ) 
		{
			static::$scen_ins 		= Vefja::singleton('AJD_validation\Helpers\Rule_scenario', array( $rule, $logic ) );
		}*/

		// return static::$scen_ins;

		return new \AJD_validation\Helpers\Rule_scenario($rule, $logic, $when);
	}

	protected static function get_field_scene_ins( $field, $not_once = FALSE, $singleton = true )
	{
		if( ( IS_NULL( static::$field_scene_ins ) OR $not_once ) AND $singleton )
		{
			static::$field_scene_ins 	= Vefja::singleton( 'AJD_validation\Helpers\Field_scenario', array( $field ) );
		}

		if(!$singleton)
		{
			static::$field_scene_ins 	= Vefja::instance( 'AJD_validation\Helpers\Field_scenario', array( $field ) );
		}

		return static::$field_scene_ins;
	}

	protected static function get_event_dispatcher_instance($singleton = true)
	{
		if($singleton)
		{
			return Vefja::singleton('AJD_validation\Observer\Events_dispatcher');
		}
		else
		{
			return Vefja::instance('AJD_validation\Observer\Events_dispatcher');
		}
		
	}

	protected static function get_promise_validator_instance($singleton = true)
	{
		if($singleton)
		{
			return Vefja::singleton('AJD_validation\Async\PromiseValidator');
		}
		else
		{
			return Vefja::instance('AJD_validation\Async\PromiseValidator');
		}
		
	}

	protected static function get_errors_instance( $lang = NULL, $singleton = true )
	{
		$error = null;

		if($singleton)
		{
			$error = Vefja::singleton('AJD_validation\Helpers\Errors');
		}
		else
		{
			$error = Vefja::instance('AJD_validation\Helpers\Errors');
		}

		if($error)
		{
			if(static::$lang)
			{
				$error::setLang(static::$lang);
			}
		}
		
		return $error;
	}

	protected static function getConfig( $file = 'common_config.php' )
	{   
		return Vefja::singleton('AJD_validation\Config\Config', array( $file ) );
	}

}

