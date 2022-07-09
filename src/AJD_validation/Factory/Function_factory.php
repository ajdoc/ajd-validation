<?php namespace AJD_validation\Factory;

use ReflectionFunction;

use AJD_validation\Factory\Factory_interface;

class Function_factory implements Factory_interface
{
	protected static $valid_func 		= array(
		'filter_var',
		'in_array',
		'preg_match',
		'is_int',
		'is_numeric',
		'is_array',
		'is_float',
		'is_string',
		'is_object',
		'is_callable',
		'is_bool',
		'is_null',
		'is_resource',
		'is_scalar',
		'is_finite'
	);

	protected static $value_in_first 	= array(
		'filter_var',
		'in_array',
	);

	protected static $value_in_last 	= array(
		'preg_match'
	);

	protected static $only_value 		= array(
		'is_int'		=> 'is_int',
		'is_numeric'	=> 'is_numeric',
		'is_array'		=> 'is_array',
		'is_float'		=> 'is_float',
		'is_string'		=> 'is_string',
		'is_object'		=> 'is_object',
		'is_callable'	=> 'is_callable',
		'is_bool' 		=> 'is_bool',
		'is_null' 		=> 'is_null',
		'is_resource' 	=> 'is_resource',
		'is_scalar'		=> 'is_scalar',
		'is_finite' 	=> 'is_finite'
	);

	protected static $ref_func;
	protected static $func_name;

	public function rules( $rule_name, $options = array() )
	{
		$func_name 					= $rule_name;
		
		if( ISSET( $options['func'] ) AND !EMPTY( $options['func'] ) ) 
		{
			$func_name 				= $options['func'];
		}

		$func 						= new ReflectionFunction( $func_name );

		static::$ref_func 			= $func;
		static::$func_name 			= $rule_name;

		return $func;		

	}

	public function process_function( $field, $value, $satisfier, $inverse = FALSE, $include_field = FALSE, array $details = array() )
	{
		$args 						= array();

		$value_in_first 			= static::$value_in_first;

		$value_in_last				= static::$value_in_last;		

		$func 						= static::$ref_func;

		if( in_array( static::$func_name, $value_in_first ) ) $args[] = $value;

		if( $include_field ) $args[] = $field;

		if( !EMPTY( $satisfier ) ) 
		{
			if( is_array( $satisfier ) ) 
			{
				foreach( $satisfier as $satis_key => $satis_val ) 
				{
					$args[] 	= $satis_val;
				}

			} 
			else 
			{
				$args[] 		= $satisfier;
			}
		} 

		if( !EMPTY( $details ) AND ISSET( $details['origValue'] ) )
		{
			$args[] 			= $details['origValue'];
		}
		
		if( in_array( static::$func_name, $value_in_last ) ) $args[] = $value;

		if( !EMPTY( static::$only_value ) )
		{
			if( ISSET( static::$only_value[ static::$func_name ] ) )
			{
				$args 			= array();
				$args[] 		= $value;
			}
		}

		$check 					= $func->invokeArgs( $args );
		
		$passed 				= ( $inverse ) ? !$check : $check;

		return $passed;

	}

	public function set_values_in_first( $func_name )
	{
		static::$value_in_first[] = $func_name;
	}

	public function set_values_in_last( $func_name )
	{
		static::$value_in_last[] = $func_name;
	}

	public function set_valid_func( $func_name )
	{
		static::$valid_func[] 	= $func_name;	
	}

	public function set_only_value( $func_name )
	{
		static::$only_value[$func_name] = $func_name;
	}

	public function func_valid( $func_name )
	{
		return in_array( $func_name, static::$valid_func );		
	}

	public function reflection( $resolver )
	{
		return new ReflectionFunction( $resolver );
	}
}


