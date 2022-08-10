<?php namespace AJD_validation\Factory;

use ReflectionMethod;

use AJD_validation\Contracts\Abstract_common;
use AJD_validation\Factory\Factory_interface;

class Method_factory implements Factory_interface
{
	protected static $ref_meth;
	protected static $object;
	protected static $method;

	public function rules( $obj_name, $rule_name )
	{
		$method 			= new ReflectionMethod( $obj_name, $rule_name );

		static::$ref_meth 	= $method;
		static::$object 	= $obj_name;
		static::$method 	= $rule_name;

		return $method;
	}

	public function process_method( $args = array(), $object = null, $accessible = FALSE, $inverse = FALSE, array $globalVar = array() )
	{
		$ref_meth 			= static::$ref_meth;

		if( $accessible ) 
		{
			$check_method	= ( $ref_meth->isProtected() OR $ref_meth->isPublic() );
		} 
		else 
		{
			$check_method 	= ( $ref_meth->isPublic() );
		}

		if( $check_method ) 
		{
			$ref_meth->setAccessible( TRUE );

			$defaultParams 	= $ref_meth->getParameters();
			
			Abstract_common::processDefaultParams( $defaultParams, $args );

			if( is_array( $args ) )
			{
				$args 		= array_merge( $args, $globalVar );
			}

			$passed 		= ( $inverse ) ? !$ref_meth->invokeArgs( $object, $args ) : $ref_meth->invokeArgs( $object, $args );
		}

		return $passed;
	}

	public function reflection( $resolver )
	{
		return new ReflectionMethod( $resolver[0], $resolver[1] );
	}

}

