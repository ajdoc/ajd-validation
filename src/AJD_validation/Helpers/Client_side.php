<?php namespace AJD_validation\Helpers;

use Exception;
use AJD_validation\Contracts\Base_validator;
use AJD_validation\AJD_validation;

class Client_side extends Base_validator
{
	const PARSLEY = 'parsley';

	protected static $js_rules;
	protected static $js_validation_rules = [];
	protected static $validJs = [
		self::PARSLEY
	];

	protected $cacheInstance = [];	

	protected $ajd;

	public static function addJSvalidationLibrary( $jsValidationLibrary )
	{
		static::$validJs[] 	= $jsValidationLibrary;
	}

	public function __construct( $js_rules = [], AJD_validation $ajd = null, $jsTypeFormat = self::PARSLEY )
	{
		$this->ajd = $ajd;

		if( $this->ajd )
		{
			$ajd = $this->ajd;
			$this->cacheInstance = $ajd::getCacheInstanceByField();
		}

		if( !in_array( $jsTypeFormat, static::$validJs ) )
		{
			throw new Exception('This is a not valid CLient Side Validation library');
		}

		if( !EMPTY( $js_rules ) ) 
		{
			static::$js_rules = $js_rules;

			foreach( static::$js_rules as $field => $rules ) 
			{
				$clean_field = $this->remove_number_sign( $field );

				$field_arr = $this->format_field_name( $clean_field );

				$this->processDefaultRequiredFormat( $jsTypeFormat, $field_arr['orig'] );
				
				if( in_array( 'required_rule', array_keys( $rules ) ) )
				{
					if( isset( static::$js_validation_rules[$field_arr['orig']] ) )
					{
						unset( static::$js_validation_rules[$field_arr['orig']]['required'] );
					}
				}

				foreach ( $rules  as $rule_name => $satisfier ) 
				{
					$clean_rule = $this->remove_appended_rule( $rule_name );
					
					$satis = ( isset( $satisfier[0]['satisfier'] ) ) ? $satisfier[0]['satisfier'] : '';

					$cus_err = ( $this->isset_empty( $satisfier, 1 ) ) ? $satisfier[1]['custom_error'] : [];

					$clientMessageOnly = ( ISSET( $satisfier[0]['client_message_only'] ) ) ? $satisfier[0]['client_message_only'] : false;

					$ucFirstRule = ucfirst( $rule_name );

					$errors = $this->js_errors( $clean_rule, $rule_name, $field, null, $satis, $cus_err, $ucFirstRule );

					if( ISSET( $this->cacheInstance[$field_arr['orig']][ $ucFirstRule ] ) )
					{
						$instance = $this->cacheInstance[$field_arr['orig']][ $ucFirstRule ];

						$field_or = $this->remove_number_sign( $field_arr['orig'] );
						
						$jsFormat = $instance->getCLientSideFormat( $field_or, $clean_rule, $jsTypeFormat, $clientMessageOnly, $satis, $errors );

						if( !empty( $jsFormat ) )
						{
							static::$js_validation_rules = array_merge_recursive( static::$js_validation_rules, $jsFormat );
						}
					}
					else if( method_exists( $this , $rule_name ) ) 
					{
						$field_or = $this->remove_number_sign( $field_arr['orig'] );

						call_user_func_array( [$this, $rule_name], [$field_or, $satis, $errors] );
					}
				}
			}
		}
	}

	protected function processDefaultRequiredFormat( $jsTypeFormat, $field )
	{
		if( $jsTypeFormat == self::PARSLEY )
		{
			static::$js_validation_rules[$field]['required'] 	= <<<JS
				data-parsley-required="false"
JS;
		}
	}	

	protected function js_errors( $rule_name, $append_rules, $field, $value = null, $satisfier = null, $cus_err = null, $ucFirstRule = null )
	{
		$field = $this->remove_number_sign( $field );
		$field_arr = $this->format_field_name( $field );
		$field = $field_arr[ 'clean' ];
		$orig_field = $field_arr[ 'orig' ];
		$err = static::get_errors_instance();
		$errors = $err->get_errors();

		if( !EMPTY( $this->cacheInstance ) && ISSET( $this->cacheInstance[$orig_field] ) )
		{
			$errors = $err->processExceptions( $rule_name, $ucFirstRule, $this->cacheInstance[$orig_field], $satisfier, $value, false, $errors 
				);
			
			$errors = $errors['errors'];
		}
		
		$errors = $this->format_errors( $rule_name, $append_rules, $field, $value, $satisfier, $errors, $cus_err, true, $err );

		return $errors;
	}

	protected function remove_number_sign( $field ) 
	{
		$check = (bool) ( preg_match( '/^#/', $field ) );
		$ret_field = $field;

		if( $check ) 
		{
			$ret_field = preg_replace( '/^#/' , '', $field );
		}

		return $ret_field;
	}

	public function get_js_validations($perField = false)
	{
		if( $perField )
		{
			$newArr = [];
			
			foreach( static::$js_validation_rules as $field => $rules )
			{
				$newArr[$field] = '';

				if( is_array($rules) )
				{
					foreach( $rules as $rule )
					{
						if(is_array($rule))
						{
							$rule = implode(' ', $rule);
						}

						$newArr[$field] .= $rule.' ';
					}
				}
			}
			
			return $newArr;
		}
		else
		{
			return static::$js_validation_rules;
		}
	}

}
