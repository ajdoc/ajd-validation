<?php namespace AJD_validation\Contracts;

use \RecursiveIteratorIterator;
use \RecursiveArrayIterator;
use AJD_validation\Contracts\Abstract_exceptions;

abstract class Abstract_common
{
	const DS 			= DIRECTORY_SEPARATOR;

	const LOG_AND 		= 'and',
		  LOG_OR 		= 'or',
		  LOG_XOR 		= 'xor',
		  SOMETIMES 	= 'sometimes',
		  CODEIGNITER 	= 'codeigniter',
		  LARAVEL  		= 'laravel',
		  RESPECT 		= 'respect',
		  SYMFONY 		= 'symfony',
		  CLIENT_PARSLEY= 'parsley',
		  EV_LOAD 		= 'load',
		  EV_SUCCESS 	= 'success',
		  EV_FAILS 		= 'fails',
		  URL_VERY_BASIC = 'verybasic';

   	protected $dir_root;
   	protected static $callbackRules 	= array('callback', 'filtervar');

	protected static function getDirRoot()
	{
		return dirname(__DIR__).self::DS;
	}

	protected function flattened_array( array $arr )
	{
		$flat_arr 			= iterator_to_array(

			new RecursiveIteratorIterator(

				new RecursiveArrayIterator( $arr )

			), false

		);

		return $flat_arr;
	}

	protected function format_field_name( $field )
	{
		$field_arr 					= array();
		$field_return 				= array();
		$clean_field 				= $field;
		$orig_field 				= $field;

		if( $this->check_has_pipe( $field ) ) 
		{
			$field_arr 				= explode( '|', $field );

			$orig_field 			= $field_arr[ 0 ];

			$clean_field 			= $field_arr[ 1 ];

		} 

		$clean_field 				= preg_replace( '/\_/', ' ', $clean_field );

		$clean_field 				= ucfirst( strtolower( $clean_field ) );
		
		$field_return[ 'clean' ] 	= $clean_field;

		$field_return[ 'orig' ] 	= $orig_field;

		return $field_return;
		

	}

	protected function invoke_function( $func, $args = array() )
	{
		return call_user_func_array( $func , $args );
	}


	protected function check_has_pipe( $value )
	{
		$check 						= ( bool ) preg_match( '/\|/' , $value );

		return $check;
	}

	protected function clean_rule_name( $rules )
	{
		$return_array 		= array();

		$clean_rule 		= $rules;

		$inverse 			= $this->check_inverse( $rules );

		$check 				= FALSE;

		if( $inverse[ 'check' ] ) 
		{
			$clean_rule 	= explode( $inverse[ 'match' ][0], $rules );

			$clean_rule 	= $clean_rule[ 1 ];

			$check 			= TRUE;
		}

		$return_array[ 'check' ]  		= $check;
		$return_array[ 'rule' ]			= $clean_rule;

		return $return_array;

	}

	protected function check_inverse( $value ) 
	{
		$match 			= NULL;

		$check 			= ( bool ) preg_match( '/^!|not_/' , $value, $match );

		$arr 			= array(

			'match' 	=> $match,
			'check' 	=> $check

		);

		return $arr;

	}

	protected function remove_appended_rule( $rule )
	{
		$ret_rule  		= $rule;

		$check 			= ( bool ) preg_match( '/_rule$/', $rule );

		if( $check ) 
		{
			$ret_rule 	= preg_replace( '/_rule$/' , '', $rule );
		}

	 	return $ret_rule;
	}

	protected static function removeWord( $rule, $pattern, $replacement = '' )
	{
		return trim( preg_replace( $pattern , $replacement, $rule ) );
	}

	protected function isset_empty( $value, $key = NULL )
	{
		if( ISSET( $key ) ) 
		{
			$check 		= ( ISSET( $value[ $key ] ) AND !EMPTY( $value[ $key ] ) );
		} 
		else 
		{
			$check 		= ( ISSET( $value ) AND !EMPTY( $value ) );
		}

		return $check;
	}

	protected function isset_null( $value, $key = NULL )
	{
		if( ISSET( $key ) ) 
		{
			$check 		= ( ISSET( $value[ $key ] ) AND !IS_NULL( $value[ $key ] ) );
		} 
		else 
		{
			$check 		= ( ISSET( $value ) AND !IS_NULL( $value ) );
		}

		return $check;
	}

	protected function format_errors( $rules_name, $append_rules, $field, $value, $satisfier, $errors, $cus_err = array(), $check_arr = TRUE, $error_instance, $arr_key = NULL, array $append_errors = array(), $inverse = FALSE )
	{
		// $cus_err 			= static::$cus_err;

		$satis 			 	= $satisfier;

		$keys 				= $cus_err;

		if( is_callable( $satisfier ) ) 
		{
			$satis 			= '';
		}

		if( !EMPTY( $cus_err ) ) 
		{
			$keys 			= array_keys( $cus_err );
		}
		
		if( in_array( $rules_name, $keys ) ) 
		{
			$cus_arr 	= $cus_err[ $rules_name ];
			
			$cus_field 	= $this->isset_empty( $cus_arr, 'field' ) ? $cus_arr[ 'field' ] : $field;

			if( is_array( $cus_err[ $rules_name ] ) ) 
			{
				if( $this->isset_empty( $cus_arr, 'satisfier' ) ) 
				{
					$cus_satis 	= $cus_arr[ 'satisfier' ];
				} 
				else 
				{
					$cus_satis 	= $satis;
				}

				$errors 		= $this->process_format_errors( $cus_field, $cus_satis, $errors, $rules_name, $value, $inverse );
			} 
			else 
			{
				$errors 		= $this->process_format_errors( $cus_field, $satis, $cus_err, $rules_name, $value, $inverse );
			}

		} 
		else 
		{
			$errors 			= $this->process_format_errors( $field, $satis, $errors, $rules_name, $value, $inverse );
		}

		if( ISSET( $append_errors[ $rules_name ] ) AND !EMPTY( $append_errors[ $rules_name ] ) )
		{
			$errors 			= $errors.' '.$append_errors[ $rules_name ].'. ';

		}

		if( !IS_NULL( $arr_key ) AND !EMPTY( $check_arr ) )
		{
			$arr_key_str 		= $arr_key + 1;	
			
			$multiErr 			= $error_instance->processMultiMsg( $arr_key_str );
			
			$errors 			= $errors.' '.$multiErr;
		}

		return $errors;

	}

	protected function processErrorTemplate( array $errors, $rules_name, $inverse = FALSE )
	{
		$errMsg 	= '';

		$errMsg  	= ( ISSET( $errors[ $rules_name ] ) ) ? $errors[ $rules_name ] : '';

		if( is_array( $errMsg ) )
		{
			$template 		= ( $inverse ) ? Abstract_exceptions::ERR_NEGATIVE : Abstract_exceptions::ERR_DEFAULT;

			if( ISSET( $errMsg[ $template ] ) )
			{
				if( ISSET( $errMsg[ $template ][ Abstract_exceptions::STANDARD ] ) )
				{
					$errMsg 	= $errMsg[ $template ][ Abstract_exceptions::STANDARD ];
				}
				else
				{
					$errMsg 	= '';
				}
			}
			else
			{
				$errMsg 	= '';
			}
		}

		return $errMsg;
	}

	protected function process_format_errors( $field = NULL, $satisfier = NULL, $errors, $rules_name, $value, $inverse = FALSE )
	{

		if(function_exists('enum_exists')) 
		{
			if( $value instanceof \UnitEnum ) 
			{
				$value = $value->name;
		    }
		}

		if( is_array( $satisfier ) ) 
		{			
			$errors 			= $this->replace_satisfier_errors( $satisfier, $errors, $rules_name, $inverse );

			if( is_array( $value ) ) 
			{
				$value 			= $this->flattened_array( $value );
				$valueStr		= implode( ', ', $value );
				$errors 		= str_replace( array( ':field', ':value' ), array( $field, $valueStr ), $errors );
			}
			else
			{
				$errors 		= str_replace( array( ':field', ':value' ), array( $field, $value ), $errors );
			}
		} 
		else 
		{
			$errMsg 			= $this->processErrorTemplate( $errors, $rules_name, $inverse );

			if( ISSET( $field ) AND ISSET( $satisfier ) ) 
			{
				if( is_string( $satisfier ) )
				{
					$cl_satis 	= $this->format_field_name( $satisfier );
					$satisfier  = $cl_satis['clean'];
				}

				$errors 		= str_replace( array( ':field', ':value', ':satisfier' ), array( $field, $value, $satisfier ), $errMsg );

			} 
			else 
			{
				$errors 		= str_replace( array( ':field', ':value', ':satisfier' ), array( $field, $value, $satisfier ), $errMsg );
			}
		}

		$errors 				= preg_replace( '/(\s)+/', ' ', $errors );

		return $errors;

	}

	protected function replace_satisfier_errors( $satisfier, $errors, $rules_name, $inverse = FALSE ) 
	{
		$satisfier_flat    		= $this->flattened_array( $satisfier );

		array_walk( $satisfier_flat, function( $value, $key ) use ( &$satisfier_flat ) {

			if( is_string( $value ) )
			{
				$cl_satis 				= $this->format_field_name( $value );
				$satisfier_flat[ $key ] = $cl_satis['clean'];
			}

		} );

      	$satis_replace 			= array_map( function( $i ) 
      	{
            return ":$i";

        }, array_keys( $satisfier_flat ) );
              
      	$implode 				= implode( ', ', $satisfier_flat );

            	// Number of arguments
    	$satisfier_flat[] 		= count( $satisfier_flat );
    	$satis_replace[] 		= ':#';

	            // All arguments
        $satisfier_flat[] 		= $implode;
        $satis_replace[] 		= ':*';        

        $errMsg 				= $this->processErrorTemplate( $errors, $rules_name, $inverse );
        
        $message 				= str_replace( $satis_replace , $satisfier_flat, $errMsg );

        return $message;

	}

	protected function array_search_recursive( $needle, $haystack, $strict = FALSE )
	{
		$path 				= array();

		if( !is_array( $haystack ) ) 
		{
	        return FALSE;
	    }

	    foreach ( $haystack as $key => $val ) 
	    {
	    	$sub_path 		 	= $this->array_search_recursive( $needle, $val, $strict );

	    	if( is_array( $val ) AND !IS_NULL( $sub_path ) AND $sub_path !== FALSE ) 
	    	{
	    		$path[ $key ] 	= $sub_path;

	    		return $path;
	    	} 
	    	else if( ( !$strict AND $val == $needle ) || ( $strict AND $val === $needle ) ) 
	    	{
	    		$path 			= $key;

	    		return $path;
	    	}
	    	
	    }

	    return FALSE;
	}

	public static function invokeClosure($value)
	{
		return ( $value instanceof Closure ) ? $value() : $value;
	}

	public static function processDefaultParams( array $defaultParams = array(), array $args = array() )
	{
		$addToArgs 		= array();

		if( !EMPTY( $defaultParams ) )
		{
			foreach( $defaultParams as $key => $reflectParams )
			{
				if( !ISSET( $args[ $key ] ) )
				{
					if( $reflectParams->isDefaultValueAvailable() )
					{
						$addToArgs[] 	= $reflectParams->getDefaultValue();
					}
					else
					{
						$addToArgs[] 	= NULL;
					}
				}
				else
				{
					$addToArgs[] 	= $args[$key];
				}
			}

			$args 					= $addToArgs;
		}

		return $args;
	}
}