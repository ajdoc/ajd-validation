<?php

require ( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'autoload'.DIRECTORY_SEPARATOR.'Autoload.php' );


use AJD_validation\Helpers\When;
use AJD_validation\Helpers\Expression;
use AJD_validation\Helpers\Database;
use AJD_validation\Helpers\Client_side;
use AJD_validation\Contracts\Base_validator;
// use \Closure;

class AJD_validation extends Base_validator

{

	const DS 							= DIRECTORY_SEPARATOR;
	const LOG_AND 						= 'and',
		  LOG_OR 						= 'or';

	protected static $rules 			= array();
	protected static $or_rules 			= array();

	protected static $and_or_stack 		= array();

	protected static $or_args 			= array(
		'satisfier' 					=> array()
	);

	protected static $fields 			= array();
	protected static $current_field;

	protected static $check_group 		= FALSE;

	protected static $satisfier 		= array();
	protected static $js_rule			= array();
	protected static $result 			= array();
	protected static $message 			= array();
	protected static $cus_err 			= array();

	protected static $macros			= array();
	
	protected static $lang;

	protected static $filters 			= array();
	protected static $filter_satis 		= array();
	protected static $pre_filter 		= array();
	
	protected static $cache_instance 	= array();

	protected static $rules_suffix 		= 'rule';

	protected $rules_path;
	protected $check_cond 				= TRUE;

	protected static $method_override 	= array();
	protected static $class_override 	= array();
	protected static $function_override = array();

	protected static $scenarios 		= array();
	protected static $remove_scenario;

	protected static $ajd_ins;

	/**
	 * @param $name
	 * @param array $args
	 * @return mixed
     */
	public function __call( $name, array $args )

	{

		if( ISSET( static::$macros[ $name ] ) ) {

			$method 	= 'macro';

		} else {

			$method 	= 'addRule';

		}
		
		array_unshift( $args, $name );

		return call_user_func_array( array( $this, $method ), $args );
 
	}

	public static function __callStatic( $name, array $args )

	{

		if( ISSET( static::$macros[ $name ] ) ) {

			$method 	= 'macro';

		} else {

			$method 	= 'addRule';

		}

		array_unshift( $args, $name );

		$stat  						= static::get_ajd_instance();

		return call_user_func_array( array( $stat, $method ), $args );

	}

	/**
	 * @return mixed
     */
	protected static function get_ajd_instance()

	{

		if( IS_NULL( static::$ajd_ins ) ) {

			static::$ajd_ins 	= new static;

		}

		return static::$ajd_ins;

	}

	/**
	 * @return string
     */
	protected function get_rules_path()

	{

		$this->rules_path 		= dirname( __FILE__ ).self::DS.'Rules'.self::DS;

		return $this->rules_path;

	}

	/**
	 * @param $rule
	 * @param $object
     */
	public static function register_method_rule( $rule, $object )
	
	{

		static::$method_override[ $rule.'_'.static::$rules_suffix ] 	= $object;

	}

	/**
	 * @param $class_name
	 * @param $path
	 * @param null $namespace
     */
	public static function register_class_rule( $class_name, $path, $namespace = NULL )

	{

		$class_name 	= ucfirst( strtolower(  $class_name  ) );

		$args  			= array();

		$args[] 		= $path;

		if( !IS_NULL( $namespace ) ) {

			$args[] 	= $namespace;

		}

		static::$class_override[ $class_name.'_'.static::$rules_suffix ] = $args;

	}

	public static function register_function_rule( $func_name, $func = NULL, $last = FALSE ) 

	{
		
		$func_factory 	= static::get_factory_instance()->get_instance( FALSE, TRUE );

		$func_factory->set_valid_func( $func_name );

		$func_factory->set_values_in_first( $func_name );

		if( $last ) {

			$func_factory->set_values_in_last( $func_name );

		}

		static::$function_override[ $func_name ] 	= ( !IS_NULL( $func ) ) ? $func : $func_name;


	}

	/**
	 * @param $macro_name
	 * @param Closure $func
     */
	public static function set_macro( $macro_name, Closure $func )
	{

		if( !EMPTY( $macro_name ) ) {

			$ajd 			= static::get_ajd_instance();

			$ajd->invoke_func( $func , array( $ajd ) );

			static::$macros[ $macro_name ]['rules']  	= static::$rules;			
			static::$macros[ $macro_name ]['satisfier'] = static::$satisfier;
			static::$macros[ $macro_name ]['cus_err']	= static::$cus_err;
			static::$macros[ $macro_name ]['js_rule'] 	= static::$js_rule;
			static::$macros[ $macro_name ]['post_filter'] 	= static::$filters;
			static::$macros[ $macro_name ]['pre_filter'] 	= static::$pre_filter;
			static::$macros[ $macro_name ]['filt_satis'] 	= static::$filter_satis;
			static::$macros[ $macro_name ]['scenario'] 		= static::$scenarios;

			$ajd->reset_validation();

		}

	}

	protected function invoke_func( $func, $args = array() )
	{
		$this->invoke_function( $func, $args );
	}

	public static function macro( $macro_name )
	{

		if( ISSET( static::$macros[ $macro_name ] ) ) {

			static::$rules 								= static::$macros[ $macro_name ]['rules'];
			static::$satisfier 							= static::$macros[ $macro_name ]['satisfier'];
			static::$cus_err 							= static::$macros[ $macro_name ]['cus_err'];
			static::$js_rule 							= static::$macros[ $macro_name ]['js_rule'];
			static::$filters  							= static::$macros[ $macro_name ]['post_filter'];
			static::$pre_filter 						= static::$macros[ $macro_name ]['pre_filter'];
			static::$filter_satis 						= static::$macros[ $macro_name ]['filt_satis'];
			static::$scenarios 							= static::$macros[ $macro_name ]['scenario'];

		}

		return static::get_ajd_instance();

	}

	public static function field( $field )
	{

	
		if( !EMPTY( static::$rules ) )
		{
			static::$fields[ self::LOG_AND ][ $field ]['rules'] 	= static::$rules;
			static::$fields[ self::LOG_AND ][ $field ]['satisfier'] = static::$satisfier;
		}

		if( !EMPTY( static::$or_rules ) )
		{
			static::$fields[ self::LOG_OR ][ $field ]['rules'] 		= static::$or_rules;
			static::$fields[ self::LOG_OR ][ $field ]['satisfier'] 	= static::$or_args['satisfier'];
		}

		static::$current_field 	= $field;

		return static::get_ajd_instance();

	}

	public function checkGroup( array $data )
	{
		
		static::$check_group 	= TRUE;

		$value 				 	= NULL;
		$or_success 			= array();

		if( ISSET( static::$fields[ self::LOG_OR ] ) )
		{

			foreach( static::$fields[ self::LOG_OR ] as $field_key => $field_value )
			{


				if( ISSET( $data[ $field_key ] ) ) 
					$value 	= $data[ $field_key ];
				else 
					$value 	= '';
			

				$or 		= $this->check( $field_key, $value, TRUE, self::LOG_OR, $field_value );
				
				foreach( $or as $key_res => $val_res )
				{
					
					$or_success[ $key_res ]['rules'][] 			= $val_res['pass_arr'][0];
					$or_success[ $key_res ]['satisfier'][] 		= $val_res['pass_arr'][1];

				}

			}
			
			$or_field 			= current( array_keys( static::$fields[ self::LOG_OR ] ) );
			
			foreach ( $or_success as $rule => $value ) 
			{
				
				$append_rules 	= ucfirst( $rule ).'_'.static::$rules_suffix;
				
				if( !in_array( 1, $value['rules'] ) )
				{
					$this->handle_errors( $rule, $append_rules, $or_field, $value, $value['satisfier'][0], FALSE );
				}

			}

		}

		if( ISSET( static::$fields[ self::LOG_AND ] ) )
		{

			foreach( static::$fields[ self::LOG_AND ] as $field_key => $field_value )
			{

				if( ISSET( $data[ $field_key ] ) )
					$value = $data[ $field_key ];
				else 
					$value 	= '';

				$this->check( $field_key, $value, TRUE, self::LOG_AND, $field_value );

			}

		}


		static::$check_group = FALSE;

		$this->clear_or_rules();
		$this->clear_current_field();
		static::$fields = array();
		$this->reset_validation();
	}

	/**
	 * @param $field
	 * @param null $value
	 * @param bool|TRUE $check_arr
	 * @return mixed
     */
	public function check( $field, $value = NULL, $check_arr = TRUE, $logic = self::LOG_AND, $group_rules = NULL )
	{
		
		if( static::$check_group ) 
		{
			$rules_arr 	= $group_rules['rules'];
			$logic 		= $logic;
			$satis 		= $group_rules['satisfier'];
		}
		else 
		{
			$rules_arr  = static::$rules;
			$logic 		= self::LOG_AND;
			$satis 		= static::$satisfier;
		}

		$pass_arr 		= array();

		$args = array(
			'logic' 	=> $logic,
			'pass_arr' 	=> array()
		);

		$ob = static::get_observable_instance();
		$ev = static::get_event_dispatcher_instance();

		if( !EMPTY( static::$remove_scenario ) ) {

			unset( static::$scenarios[ static::$remove_scenario ] );

		}

		
		if( !EMPTY( static::$scenarios ) ) {

			$arr_search 	= $this->array_search_recursive( $field, static::$scenarios );

			if( !EMPTY( $arr_search ) ) return $ev;

		}

		// $ob->notify_observer( 'before' );

		$filter_arr 			= static::$filters;
		$filt_satis_arr 		= static::$filter_satis;
		$pre_filter 			= static::$pre_filter;

		$filter 				= ( $this->isset_empty( $filter_arr ) ) ? $filter_arr : NULL;
		$filt_satis 			= ( $this->isset_empty( $filt_satis_arr ) ) ? $filt_satis_arr : NULL;
		$pre_filter 			= ( $this->isset_empty( $pre_filter ) ) ? $pre_filter 	: array();

		if( !EMPTY( $filter ) ) {

			$this->handle_filter( $filter, $value, $field, $filt_satis, $pre_filter, $check_arr );

			$filt_value 		= static::pre_filter_value( $field );

			$value  			= ( $this->isset_empty( $filt_value ) ) ? $filt_value : $value;

		}

		if( !EMPTY( $rules_arr ) ) {

			$count 				= count( $rules_arr );

			foreach( $rules_arr as $rules_key => $rules_value ) {

				$check_rules 	= $this->clean_rule_name( $rules_value );

				$rules_value 	= $check_rules[ 'rule' ];

				$append_rules   = ucfirst( $rules_value ).'_'.static::$rules_suffix;

				$satisfier 		= $satis[ $rules_key ];
				
				if( !EMPTY( static::$scenarios ) ) {

					$check_scena 	= $this->array_search_recursive( $rules_value, static::$scenarios );

					if( !EMPTY( $check_scena ) ) continue;

				}
				
				if( is_array( $value ) AND $check_arr ) {

					$value 			= $this->flattened_array( $value );

					foreach( $value as $k_val => $v_val ) {

						$pass_arr[ $rules_value ] = $this->_process_validate( $rules_value, $append_rules, $field, $v_val, $satisfier, $check_rules['check'], TRUE. $args );

					}

				} else {

					$pass_arr[ $rules_value ] 	= $this->_process_validate( $rules_value, $append_rules, $field, $value, $satisfier, $check_rules['check'], FALSE, $args );

				}

			}
 
		}
		
		// $ob = new Observable();

		$ob->attach_observer( 'passed', $ev, array( $this ) );
		$ob->attach_observer( 'fails', $ev, array( $this ) );

		if( !$this->validation_fails( $field ) ) {

			$ob->notify_observer( 'passed' );


		}

		if( $this->validation_fails( $field ) ) {

			$ob->notify_observer( 'fails' );

		}

		// $ob->detach_observer( 'success' );

		$this->reset_validation();

		if( $logic == self::LOG_OR )
		{
			
			return $pass_arr;

		}
		else 
		{
		
			return $ev;

		}


	}

	public static function add_field_scenario( $scenario, $field )

	{

		static::$scenarios[ $scenario ][]  		= $field;

	}

	public static function filter_value( $key = NULL )

	{

		$filter 	= static::get_filter_ins();

		return $filter->get_filtered_value( $key );

	}

	public static function pre_filter_value( $key = NULL )

	{

		$filter 	= static::get_filter_ins();

		return $filter->get_pre_filter_value( $key );
 
	}

	public static function trigger( $scenario )
	{

		static::$remove_scenario = $scenario;

	}

	public function add_listener( $event, $func, $args = array() )

	{

		$observable 		= static::get_observable_instance();

		$observable->attach_observer( $event, $func, $args );

	}

	public function when()

	{

		static::$result 			= array();

		$ob = static::get_observable_instance();

		$ob->attach_observer( 'ongiven', array( $this, 'check_condition' ) );

		$when 						= new When( $this, $ob );

		return $when;

	}

	public function buildWhen( $if, \Closure $then, $else = NULL )

	{

		$ajd 							= static::get_ajd_instance();

		$args 							= array( $ajd );

		if( $if instanceof \Closure ) {

			$if 						= $this->invoke_function( $if, $args );

		} else if( $if instanceof Expression ) {

			$if 						= Expression::evaluate();

		}

		if( $if ) {

			$then 						= $this->invoke_function( $then, $args );

		} else {

			if( !IS_NULL( $else ) ) { 					

				$else 					= $this->invoke_function( $else, $args );

			}

		}

	}

	public static function expression( $expr, $expr_value )

	{

		$ajds 	= static::get_ajd_instance();

		return new Expression( $expr, $expr_value, $ajds );

	}

	public static function db( $connection = NULL, $user = NULL, $pass = NULL, $options = array() )

	{

		return new Database( $connection, $user, $pass, $options );

	}

	private function _process_validate( $rules_name, $append_rules, $field, $value, $satisfier, $inverse, $check_arr, $ext_args )

	{

		$ob 						= static::get_observable_instance();

		$lower_rule 				= strtolower( $append_rules );

		$args 						= $this->_process_customs( $lower_rule, $append_rules, $rules_name );
		
		$class_name 				= get_class( $args['obj_ins'] );

		$is_class 					= file_exists( $args['rules_path'] );	
		
		$is_function 				= function_exists( $rules_name );

		$is_method 					= method_exists( $args['obj_ins'], $append_rules );

		$passed 					= TRUE;
		
		if( $is_class AND !$args['override'] ) {

			$passed 				= $this->_process_class( $append_rules, $is_class, $args['rules_path'], $rules_name, $field, $value, $satisfier, $args );

		} else if( $is_function OR $args['func_override'] ) {

			$passed 				= $this->_process_function( $rules_name, $field, $value, $satisfier, TRUE, $args );

		} else if( $is_method ) {

			$passed 				= $this->_process_method( $rules_name, $lower_rule, $field, $value, $satisfier, $args, $is_method );

		}

		if( $inverse ) {

			$passed 				= !$passed;

		}
			
		$ob->attach_observer( 'endgiven', array( $this, 'end_condition' ) );

		if( !$passed ) {

			static::$result[ $field ] 	= $append_rules;

			if( $ext_args['logic'] != self::LOG_OR )
			{

				if( $this->check_cond ) {

					$this->handle_errors( $rules_name, $append_rules, $field, $value, $satisfier, $check_arr );

				} else {

					// echo 'no report';

				}

			} 
			else 
			{
				
				$ext_args['pass_arr'] 			= array( $rules_name, $satisfier );
			}

		}

		if( $ext_args['logic'] == self::LOG_OR AND $passed )
			$ext_args['pass_arr']				= array( 1, $satisfier );
		


		return $ext_args;

	}

	private function _process_customs( $lower_rule, $append_rules, $rules_name )

	{

		$args 						= array();

		$override 					= FALSE;
		$rules_path 				= $this->get_rules_path().$append_rules.'.php';
		$obj_ins 					= static::get_ajd_instance();	

		$func_override 				= FALSE;

		if( $this->isset_empty( static::$class_override, $append_rules ) ) {

			$class 					= static::$class_override[ $append_rules ];

			$rules_path 			= $class[0].self::DS.$append_rules.'.php';

			$args['namespace'] 		= $class[1];

		} else if( $this->isset_empty( static::$method_override, $lower_rule ) ) {

			$obj_ins 				= static::$method_override[ $lower_rule ];

			$override 				= TRUE;

		} else if( $this->isset_empty( static::$function_override, $rules_name ) ) {

			$func_override 			= TRUE;

			$func 					= static::$function_override[ $rules_name ];

			$args['func'] 			= $func;

		}


		$args['override'] 			= $override;
		$args['obj_ins'] 			= $obj_ins;
		$args['rules_path'] 		= $rules_path;
		$args['class_name'] 		= get_class( $obj_ins );
		$args['func_override'] 		= $func_override;
		

		return $args;

	}

	protected function handle_filter( $filter, $value, $field, $satisfier, $pre_filter, $check_arr )

	{

		$filter_ins 						= static::get_filter_ins();

		$filter_ins->set_filter( $filter, $value, $field, $satisfier, $pre_filter );
		$filter_ins->filter( $check_arr );

	}

	protected function handle_errors( $rules_name, $append_rules, $field, $value, $satisfier, $check_arr )

	{

		$cus_err 		= static::$cus_err;

		$err 			= static::get_errors_instance();

		$field_arr 		= $this->format_field_name( $field );

		$field 			= $field_arr[ 'clean' ];

		$orig_field 	= $field_arr[ 'orig' ];

		$errors 		= $err->get_errors();

		$errors 		= $this->format_errors( $rules_name, $append_rules, $field, $value, $satisfier, $errors, $cus_err );

		$this->append_error_msg( $errors, $orig_field, $rules_name, $check_arr );


	}

	protected function append_error_msg( $errors, $field = NULL, $rules_name = NULL, $check_arr = FALSE )

	{

		if( ISSET( $field ) ) {

			if( ISSET( $rules_name ) ) {

				if( $check_arr ) {

					static::$message[ $field ][ $rules_name ][]	= $errors;

				} else {

					static::$message[ $field ][ $rules_name ] 	= $errors;

				}

			} else {

				static::$message[ $field ][] 					= $errors;
			}

		} else {

			static::$message[] 									= $errors;

		}

	}

	public static function all_err()

	{

		$err 		= static::get_errors_instance();

		return $err->all( static::$message );

	}

	public static function add_rule_msg( $rule, $msg )

	{

		$err 		= static::get_errors_instance();

		$err->set_errors( $rule, $msg );

	}

	public static function replace_rule_msg( $rule, $new_msg )

	{

		$err 		= static::get_errors_instance();

		$err->replace_err_msg( $rule, $new_msg );

	}

	public static function set_lang( $lang )

	{

		static::$lang 	= $lang;

	}

	protected static function get_errors_instance( $lang = NULL ) 
	{

		return parent::get_errors_instance( static::$lang );

	}

	public static function validation_fails( $key = NULL, $err_key = NULL, $when = FALSE )

	{

		$count_var	= ( $when ) ? static::$result : static::$message;

		$check 		= COUNT( $count_var ) ? TRUE : FALSE;

		if( !EMPTY( $key ) ) {

			$check 	= ( !EMPTY( $count_var[ $key ] ) AND COUNT( $count_var[ $key ] ) ) ? TRUE : FALSE;

			if( !IS_NULL( $err_key ) ) {

				$check = ( !EMPTY( $count_var[ $key ][ $err_key ] ) AND COUNT( $count_var[ $key ][ $err_key ] ) ) ? TRUE : FALSE;

			}

		}

		return $check;

	}

	protected function end_condition()

	{

		$this->check_cond 	= TRUE;

	}

	protected function check_condition()

	{

		$this->check_cond 	= FALSE;

	}

	protected function clear_rules()

	{

		static::$rules 		= array();

	}

	protected function clear_or_rules()
	{

		static::$or_rules 	= array();

	}

	protected function clear_current_field()
	{

		static::$current_field = NULL;

	}

	protected function clear_satisfier()

	{

		static::$satisfier 	= array();
		// static::$or_args['satisfier'] = array();

	}

	protected function clear_custom_err()

	{

		static::$cus_err 	= array();

	}

	protected function clear_filter()

	{

		static::$filters 	= array();

	}

	protected function clear_filter_satisfier()

	{

		static::$filter_satis 	= array();

	}

	protected function clear_pre_filter()

	{

		static::$pre_filter 	= array();

	}

	protected function clear_scenarios()

	{

		static::$scenarios 		= array();

	}

	protected function reset_validation()

	{

		$this->clear_rules();
		$this->clear_satisfier();
		$this->clear_custom_err();
		$this->clear_filter();
		$this->clear_filter_satisfier();
		$this->clear_pre_filter();
		$this->clear_scenarios();


	}

	private function _process_class( $append_rules, $is_class, $rules_path, $rules_name, $field, $value, $satisfier, $args )

	{

		
		if( !ISSET( static::$cache_instance[ $append_rules ] ) ) {

			$class_factory 		= static::get_factory_instance()->get_instance( $is_class );

			if( $this->isset_null( $args, 'namespace' ) ) {

				 $class_factory->set_rules_namespace( array( $args['namespace'] ) );

			} 

			$rule_obj 			= $class_factory->rules( $rules_path, $append_rules );

		} else {

			$rule_obj 			= static::$cache_instance[ $append_rules ];

		}
		
		static::$cache_instance[ $append_rules ] 		= $rule_obj;

		// return false;
		return $rule_obj->run( $field, $value, $satisfier );

	}

	private function _process_function( $rules_name, $field, $value, $satisfier, $is_function, $args )

	{

		$passed 				= FALSE;

		$function_factory 		= static::get_factory_instance()->get_instance( FALSE, $is_function );

		if( $function_factory->func_valid( $rules_name ) ) {

			$include_field 		= FALSE;

			if( $this->isset_empty( $args, 'func' ) ) {

				$include_field 	= TRUE;

			}
			
			$func 				= $function_factory->rules( $rules_name, $args );

			$passed 			= ( bool ) $function_factory->process_function( $field, $value, $satisfier, FALSE, $include_field );

		}

		return $passed;

	}

	private function _process_method( $rules_name, $append_rules, $field, $value, $satisfier, $args, $is_method )

	{

	
		$method_factory 		= static::get_factory_instance()->get_instance( FALSE, FALSE, $is_method );

		$method 				= $method_factory->rules( $args['class_name'], $append_rules );

		$passed					= ( bool ) $method_factory->process_method( array( $field, $value, $satisfier ), $args['obj_ins'], TRUE );

		return $passed;

	}

	protected function rule_callback_rule( $field, $value, $satisfier )

	{

		return $satisfier( $field, $value );

	}

	public static function superRule( $rule, $logic = self::LOG_AND, $satis = NULL, $custom_err = NULL, $client_side = NULL )
	{

		$ajd 	= static::get_ajd_instance();

		$ajd->addRule( $rule, $satis, $custom_err, $client_side, $logic );

		return $ajd;
	}

	public function endSuperRule()
	{


		switch( array_pop( static::$and_or_stack ) )
		{
			case self::LOG_AND:

				array_pop( static::$rules );
				array_pop( static::$satisfier );

			break;

			case self::LOG_OR:

				array_pop( static::$or_rules );
				array_pop( static::$or_args['satisfier'] );

			break;

		}

		return static::get_ajd_instance();
	}

	public function addRule( $rule, $satis = NULL, $custom_err = NULL, $client_side = NULL, $logic = self::LOG_AND )
	{

		static::$and_or_stack[] 	= $logic;

		$curr_field 				= static::$current_field;
		$fields 					= static::$fields;


		if( !EMPTY( $curr_field ) ) {

			static::$fields[ self::LOG_AND ][ $curr_field ]['rules'][] 		= $rule;
			static::$fields[ self::LOG_AND ][ $curr_field ]['satisfier'][] 	= $satis;

		} else {

			switch( strtolower( $logic ) )
			{

				case self::LOG_AND:

					static::$rules[] 		= $rule;

					static::$satisfier[] 	= ( !EMPTY( $satis ) ) ? $satis : '';

				break;

				case self::LOG_OR:

					static::$or_rules[]  	= $rule;

					static::$or_args['satisfier'][] = ( !EMPTY( $satis ) ) ? $satis : '';

				break;

			}

			

		}

		$clean_rule 				= $this->clean_rule_name( $rule );
		
		if( !EMPTY( $custom_err ) ) {

			$rules_name 			= $clean_rule[ 'rule' ];

			static::$cus_err[ $rules_name ] 	= $custom_err;

		}

		if( !EMPTY( $client_side ) ) {

			$orig_rule 											= strtolower( $rule );

			$rule 												= $orig_rule.'_'.static::$rules_suffix;

			static::$js_rule[ $client_side ][ $rule ][] 		= $satis;

			if( !EMPTY( $custom_err ) ) {

				static::$js_rule[ $client_side ][ $rule ][] 	= array( $orig_rule => $custom_err );

			}

		}

		return static::get_scene_ins( $clean_rule['rule'], TRUE );

	}

	public function addFilter( $filter, $satis = NULL, $pre_filter = FALSE )

	{

		static::$filters[] 					= $filter;
		static::$filter_satis[] 			= $satis;
		static::$pre_filter[] 				= $pre_filter;

		return $this;	

	}

	public static function get_js_rules()
	
	{


		$client_side 			= new Client_side( static::$js_rule );

		return $client_side->get_js_validations();

	}

}

