<?php namespace AJD_validation\Contracts;

use \RecursiveIteratorIterator;
use \RecursiveArrayIterator;
use AJD_validation\Contracts\Abstract_exceptions;

abstract class Abstract_common
{
	const DS = DIRECTORY_SEPARATOR;
	const LOG_AND = 'and',
		  LOG_OR = 'or',
		  LOG_XOR = 'xor',
		  SOMETIMES = 'sometimes',
		  CODEIGNITER = 'codeigniter',
		  LARAVEL = 'laravel',
		  RESPECT = 'respect',
		  SYMFONY = 'symfony',
		  CLIENT_PARSLEY = 'parsley',
		  EV_LOAD = 'load',
		  EV_SUCCESS = 'success',
		  EV_FAILS = 'fails',
		  URL_VERY_BASIC = 'verybasic';

   	protected $dir_root;
   	protected static $callbackRules = ['callback', 'filtervar'];

	protected static function getDirRoot()
	{
		return dirname(__DIR__).self::DS;
	}

	protected function flattened_array( array $arr )
	{
		$flat_arr = iterator_to_array(

			new RecursiveIteratorIterator(

				new RecursiveArrayIterator( $arr )

			), false

		);

		return $flat_arr;
	}

	protected function format_field_name( $field )
	{
		$field_arr = [];
		$field_return = [];
		$clean_field = $field;
		$orig_field = $field;

		if( $this->check_has_pipe( $field ) ) 
		{
			$field_arr = explode( '|', $field );

			$orig_field = $field_arr[ 0 ];
			$clean_field = $field_arr[ 1 ];
		} 

		$clean_field = (!empty($clean_field)) ? $clean_field : '';
		$clean_field = preg_replace( '/\_/', ' ', $clean_field );
		$clean_field = ucfirst( strtolower( $clean_field ) );
		
		$field_return[ 'clean' ] = $clean_field;
		$field_return[ 'orig' ] = $orig_field;

		return $field_return;
		

	}

	protected function invoke_function( $func, $args = array(), $event = null )
	{
		return call_user_func_array( $func , $args );
	}


	protected function check_has_pipe( $value )
	{
		$check = (!empty($value)) ? ( bool ) preg_match( '/\|/' , $value ) : false;

		return $check;
	}

	protected function clean_rule_name( $rules )
	{
		$return_array = [];
		$clean_rule = $rules;
		$inverse = $this->check_inverse( $rules );
		$check 	= false;

		if( $inverse[ 'check' ] ) 
		{
			$clean_rule = explode( $inverse[ 'match' ][0], $rules );
			$clean_rule = $clean_rule[ 1 ];
			$check 	= true;
		}

		$return_array[ 'check' ] = $check;
		$return_array[ 'rule' ]	= $clean_rule;

		return $return_array;

	}

	protected function check_inverse( $value ) 
	{
		$match = null;
		$check = ( bool ) preg_match( '/^!|not_/' , $value, $match );

		$arr = [
			'match' => $match,
			'check' => $check
		];

		return $arr;

	}

	protected function remove_appended_rule( $rule )
	{
		$ret_rule = $rule;
		$check = ( bool ) preg_match( '/_rule$/', $rule );

		if( $check ) 
		{
			$ret_rule = preg_replace( '/_rule$/' , '', $rule );
		}

	 	return $ret_rule;
	}

	protected static function removeWord( $rule, $pattern, $replacement = '' )
	{
		return trim( preg_replace( $pattern , $replacement, $rule ) );
	}

	public function isset_empty( $value, $key = NULL )
	{
		if( isset( $key ) ) 
		{
			$check = ( isset( $value[ $key ] ) && !empty( $value[ $key ] ) );
		} 
		else 
		{
			$check = ( isset( $value ) && !empty( $value ) );
		}

		return $check;
	}

	protected function isset_null( $value, $key = null )
	{
		if( isset( $key ) ) 
		{
			$check = ( isset( $value[ $key ] ) && !is_null( $value[ $key ] ) );
		} 
		else 
		{
			$check = ( isset( $value ) && !is_null( $value ) );
		}

		return $check;
	}

	protected function format_errors( $rules_name, $append_rules, $field, $value, $satisfier, $errors, $cus_err = [], $check_arr = true, $error_instance = null, $arr_key = null, array $append_errors = [], $inverse = false )
	{
		// $cus_err 			= static::$cus_err;
		$satis = $satisfier;
		$keys = $cus_err;

		if( is_callable( $satisfier ) ) 
		{
			$satis = '';
		}

		if( !EMPTY( $cus_err ) ) 
		{
			$keys = array_keys( $cus_err );
		}
		
		if( in_array( $rules_name, $keys ) ) 
		{
			$cus_arr = $cus_err[ $rules_name ];
			
			$cus_field = $this->isset_empty( $cus_arr, 'field' ) ? $cus_arr[ 'field' ] : $field;

			if( is_array( $cus_err[ $rules_name ] ) ) 
			{
				if( $this->isset_empty( $cus_arr, 'satisfier' ) ) 
				{
					$cus_satis = $cus_arr[ 'satisfier' ];
				} 
				else 
				{
					$cus_satis = $satis;
				}

				$errors = $this->process_format_errors( $cus_field, $cus_satis, $errors, $rules_name, $value, $inverse );
			} 
			else 
			{
				$errors = $this->process_format_errors( $cus_field, $satis, $cus_err, $rules_name, $value, $inverse );
			}

		} 
		else 
		{
			$errors = $this->process_format_errors( $field, $satis, $errors, $rules_name, $value, $inverse );
		}

		if( isset( $append_errors[ $rules_name ] ) && !empty( $append_errors[ $rules_name ] ) )
		{
			$errors = $errors.' '.$append_errors[ $rules_name ].'. ';

		}

		if( !is_null( $arr_key ) && !empty( $check_arr ) )
		{
			$arr_key_str = $arr_key + 1;	
			$multiErr = $error_instance->processMultiMsg( $arr_key_str );
			$errors = $errors.' '.$multiErr;
		}

		return $errors;

	}

	protected function processErrorTemplate( array $errors, $rules_name, $inverse = false )
	{
		$errMsg = '';
		$errMsg = ( isset( $errors[ $rules_name ] ) ) ? $errors[ $rules_name ] : '';

		if( is_array( $errMsg ) )
		{
			$template = ( $inverse ) ? Abstract_exceptions::ERR_NEGATIVE : Abstract_exceptions::ERR_DEFAULT;

			if( isset( $errMsg[ $template ] ) )
			{
				if( isset( $errMsg[ $template ][ Abstract_exceptions::STANDARD ] ) )
				{
					$errMsg = $errMsg[ $template ][ Abstract_exceptions::STANDARD ];
				}
				else
				{
					$errMsg = '';
				}
			}
			else
			{
				$errMsg = '';
			}
		}

		return $errMsg;
	}

	protected function process_format_errors( $field = null, $satisfier = null, $errors = null, $rules_name = null, $value = null, $inverse = false )
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
			$errors = $this->replace_satisfier_errors( $satisfier, $errors, $rules_name, $inverse );

			if( is_array( $value ) ) 
			{
				$value = $this->flattened_array( $value );
				$valueStr = implode( ', ', $value );
				$errors = str_replace( array( ':field', ':value' ), array( $field, $valueStr ), $errors );
			}
			else
			{
				if(is_object($value))
				{
					$errors = str_replace( array( ':field' ), array( $field ), $errors );
				}
				else
				{
					$errors = str_replace( array( ':field', ':value' ), array( $field, $value ), $errors );	
				}
				
			}
		} 
		else 
		{
			$errMsg = $this->processErrorTemplate( $errors, $rules_name, $inverse );

			if( ISSET( $field ) AND ISSET( $satisfier ) ) 
			{
				if( is_string( $satisfier ) )
				{
					$cl_satis = $this->format_field_name( $satisfier );
					$satisfier = $cl_satis['clean'];
				}

				if(is_object($value))
				{
					$errors = str_replace( array( ':field', ':satisfier' ), array( $field, $satisfier ), $errMsg );
				}
				else
				{

					$errors = str_replace( array( ':field', ':value', ':satisfier' ), array( $field, $value, $satisfier ), $errMsg );
				}

			} 
			else 
			{
				if(is_object($value))
				{
					$errors = str_replace( array( ':field', ':satisfier' ), array( $field, $satisfier ), $errMsg );
				}
				else
				{
					$errors = str_replace( array( ':field', ':value', ':satisfier' ), array( $field, $value, $satisfier ), $errMsg );
				}
			}
		}

		$errors = preg_replace( '/(\s)+/', ' ', $errors );

		return $errors;

	}

	protected function replace_satisfier_errors( $satisfier, $errors, $rules_name, $inverse = false ) 
	{
		$satisfier_flat = $this->flattened_array( $satisfier );

		array_walk( $satisfier_flat, function( $value, $key ) use ( &$satisfier_flat ) {

			if( is_string( $value ) )
			{
				$cl_satis = $this->format_field_name( $value );
				$satisfier_flat[ $key ] = $cl_satis['clean'];
			}

		} );

      	$satis_replace = array_map( function( $i ) 
      	{
            return ":$i";

        }, array_keys( $satisfier_flat ) );
              
      	$implode = implode( ', ', $satisfier_flat );

        // Number of arguments
    	$satisfier_flat[] = count( $satisfier_flat );
    	$satis_replace[] = ':#';

	    // All arguments
        $satisfier_flat[] = $implode;
        $satis_replace[] = ':*';        

        $errMsg = $this->processErrorTemplate( $errors, $rules_name, $inverse );
        
        $message = str_replace( $satis_replace , $satisfier_flat, $errMsg );

        return $message;

	}

	protected function array_search_recursive( $needle, $haystack, $strict = false )
	{
		$path = [];

		if( !is_array( $haystack ) ) 
		{
	        return FALSE;
	    }

	    foreach ( $haystack as $key => $val ) 
	    {
	    	$sub_path = $this->array_search_recursive( $needle, $val, $strict );

	    	if( is_array( $val ) && !is_null( $sub_path ) && $sub_path !== false ) 
	    	{
	    		$path[ $key ] = $sub_path;

	    		return $path;
	    	} 
	    	else if( ( !$strict && $val == $needle ) || ( $strict && $val === $needle ) ) 
	    	{
	    		$path = $key;

	    		return $path;
	    	}
	    }

	    return false;
	}

	public static function invokeClosure($value)
	{
		return ( $value instanceof Closure ) ? $value() : $value;
	}

	public static function processDefaultParams( array $defaultParams = [], array $args = [] )
	{
		$addToArgs = [];

		if( !empty( $defaultParams ) )
		{
			foreach( $defaultParams as $key => $reflectParams )
			{
				if( !isset( $args[ $key ] ) )
				{
					if( $reflectParams->isDefaultValueAvailable() )
					{
						$addToArgs[] = $reflectParams->getDefaultValue();
					}
					else
					{
						$addToArgs[] = null;
					}
				}
				else
				{
					$addToArgs[] = $args[$key];
				}
			}

			$args = $addToArgs;
		}

		return $args;
	}
}