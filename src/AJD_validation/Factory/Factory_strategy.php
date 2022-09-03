<?php namespace AJD_validation\Factory;

use AJD_validation\Factory\Class_factory;
use AJD_validation\Factory\Function_factory;
use AJD_validation\Factory\Method_factory;

class Factory_strategy
{
	const F_CLASS = 'class',
		  F_METHOD = 'method',
		  F_FUNCTION = 'function';

	protected static $class_factory;
	protected static $function_factory;
	protected static $method_factory;

	protected static function get_class_factory_instance()
	{
		if( !static::$class_factory instanceof Class_factory ) 
		{
			static::$class_factory = new Class_factory();
		}

		return static::$class_factory;
	}

	protected static function get_function_factory_instance()
	{
		if( !static::$function_factory instanceof Function_factory ) 
		{
			static::$function_factory = new Function_factory();
		}

		return static::$function_factory;
	}

	protected static function get_method_factory_instance()
	{
		if( !static::$method_factory instanceof Method_factory ) 
		{
			static::$method_factory = new Method_factory();
		}

		return static::$method_factory;		

	}

	public function get_instance( $is_class = FALSE, $is_function = FALSE, $is_method = FALSE )
	{
		if( $is_class ) 
		{
			return static::get_class_factory_instance();
		} 
		else if( $is_function ) 
		{
			return static::get_function_factory_instance();
		} 
		else if( $is_method ) 
		{
			return static::get_method_factory_instance();
		}

	}

	public function make( $type, $resolver )
	{
		$options = [
			self::F_CLASS => $this->get_instance( TRUE ),
			self::F_METHOD => $this->get_instance( FALSE, FALSE, TRUE ),
			self::F_FUNCTION => $this->get_instance( FALSE, TRUE )
		];

		if( isset( $options[ $type ] ) )
		{
			return $options[ $type ]->reflection( $resolver );
		}

	}

}

