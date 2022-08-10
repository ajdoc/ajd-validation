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
	protected static $addLangDir 			= [];
	protected static $createWriteLangDir 	= [];

	public static $rules_suffix 			= 'rule';

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

	protected static function get_filter_ins($singleton = true)
	{
		if($singleton)
		{
			return Vefja::singleton('AJD_validation\Helpers\AJD_filter');	
		}
		else
		{
			return Vefja::instance('AJD_validation\Helpers\AJD_filter');		
		}
	}

	protected static function getMetadata()
	{
		return Vefja::singleton('AJD_validation\Helpers\Metadata');
	}

	protected static function get_scene_ins( $rule, $logic, $not_once = FALSE, When $when = null, $currentRuleKey = null )
	{
		/*if( IS_NULL( static::$scen_ins ) OR $not_once ) 
		{
			static::$scen_ins 		= Vefja::singleton('AJD_validation\Helpers\Rule_scenario', array( $rule, $logic ) );
		}*/

		// return static::$scen_ins;

		return new \AJD_validation\Helpers\Rule_scenario($rule, $logic, $when, $currentRuleKey);
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

			if(!empty(static::$addLangDir))
			{
				foreach(static::$addLangDir as $lang => $path)
				{
					$create_write = false;

					if(isset(static::$createWriteLangDir[$lang]))
					{
						$create_write = static::$createWriteLangDir[$lang];
					}

					$error::addLangDir($lang, $path, $create_write);
				}
			}
			
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

	protected static function createRulesName($rule)
	{
		$rule 			= filter_var($rule, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES);
		$raw_class_name = strtolower($rule);
		$class_name 	= ucfirst($raw_class_name);
		$append_rule 	= $class_name.'_'.static::$rules_suffix;

		return [
			'raw_class_name' => $raw_class_name,
			'class_name' => $class_name,
			'append_rule' => $append_rule
		];
	}

	protected static function createAnonExceptionObj($anons)
	{
		$exceptions = [];
		try
		{
			$newClassStr = '';
			$usClassStr = 'use AJD_validation\Contracts\Abstract_anonymous_rule_exception;';

			foreach($anons as $anon)
			{
				$ruleNames = static::createRulesName($anon::getAnonName());

				$raw_class_name = $ruleNames['raw_class_name'];
				$class_name 	= $ruleNames['class_name'];
				$append_rule 	= $ruleNames['append_rule'];

				$newClassStr .= '

					$exceptions["'.$append_rule.'"] = new class() extends Abstract_anonymous_rule_exception
					{
						public static $defaultMessages = [];

						public static $localizeMessage = [];
					};
				';
			}

			if(!empty($newClassStr))
			{
				$exceptions = eval(
					$usClassStr.' '.
					'$exceptions = []; '.
					$newClassStr
					.' return $exceptions; '
				);
			}

		}
		catch(Exception $e) 
		{
			throw $e;
		}

		return $exceptions;
	}
}

