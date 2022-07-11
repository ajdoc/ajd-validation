<?php namespace AJD_validation;

// require_once ( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'autoload.php' );

use AJD_validation\Contracts\Abstract_common;
use AJD_validation\Contracts\Base_validator;
use AJD_validation\Contracts\Validator;
use AJD_validation\Helpers\When;
use AJD_validation\Helpers\Expression;
use AJD_validation\Helpers\Database;
use AJD_validation\Helpers\Client_side;
use AJD_validation\Helpers\Metadata;
use AJD_validation\Helpers\Errors;
use AJD_validation\Helpers\Array_helper;
use AJD_validation\Helpers\Validation_helpers;
use AJD_validation\Contracts\Invokable_rule_interface;

class AJD_validation extends Base_validator
{
	protected static $raw_rule 				= array(
		Abstract_common::LARAVEL,
		Abstract_common::RESPECT,
		Abstract_common::SYMFONY
	);

	protected static $method_w_args 		= array(
		Abstract_common::SYMFONY,
		Abstract_common::RESPECT
	);

	protected static $ajd_prop 				= array(

			Abstract_common::LOG_AND 		=> array(
				'rules' 					=> array(),
				'details' 					=> array(),
				'satisfier' 				=> array(),
				'cus_err' 					=> array(),
				'filters' 					=> array(),
				'filter_satis' 				=> array(),
				'pre_filters' 				=> array(),
				'scenarios' 				=> array(),
				'sometimes' 				=> array()
			),

			Abstract_common::LOG_OR 		=> array(
				'rules' 					=> array(),
				'details' 					=> array(),
				'satisfier' 				=> array(),
				'cus_err' 					=> array(),
				'filters' 					=> array(),
				'filter_satis' 				=> array(),
				'pre_filters' 				=> array(),
				'scenarios' 				=> array(),
				'sometimes' 				=> array()
			),

			'extensions' 					=> array(),
			'extension_rule' 				=> array(),
			'extension_filter' 				=> array(),
			'extension_test' 				=> array(),
			'extensions_initialize' 		=> FALSE,
			'fields' 						=> array(),
			'js_rule' 						=> array(),
			'message' 						=> array(),
			'result' 						=> array(),
			'given_values' 					=> array(),
			'cache_filters' 				=> array(),
			'and_or_stack' 					=> array(),
			'class_override' 				=> array(),
			'method_override' 				=> array(),
			'function_override' 			=> array(),
			'current_field' 				=> NULL,
			'current_logic' 				=> Abstract_common::LOG_AND,
			'check_group' 					=> FALSE,
			'result_values' 				=> array(),
			'events'						=> array()
	);

	protected static $bail 					= FALSE;
	protected static $macros 				= array();
	protected static $cache_instance 		= array();

	protected static $cacheByFieldInstance 	= array();
	protected static $middleware 			= array();
	protected static $globalVar 			= array();
	protected static $remove_scenario 		= array();
	// protected static $exceptionObj 			= array();
	protected static $constraintStorageName;
	protected static $useContraintGroup;

	protected static $rules_suffix 			= 'rule';

	protected static $lang;
	protected static $ajd_ins;

	protected $rules_path;
	protected $check_cond 					= TRUE;
	protected $customMesage 				= array();

	protected static $addRuleNamespace 		= array();
	protected static $addRuleDirectory 		= array();

	protected static $dbConnections			= array();

	protected static function get_ajd_instance()
	{
		if( IS_NULL( static::$ajd_ins ) ) 
		{
			static::$ajd_ins 	= new static;
		}

		return static::$ajd_ins;
	}

	public function __call( $name, array $args )
	{
		$ajd 		= static::get_ajd_instance();
		$method 	= static::process_method_name( $name );
		$factory 	= static::get_factory_instance()->get_instance( FALSE, FALSE, TRUE );

		$factory->rules( get_class( $ajd ), $method['method'] );

		array_unshift( $args, $method['name'] );
		
		return $factory->process_method( $args, $ajd );

	}

	public static function __callStatic( $name, array $args )
	{
		return static::get_ajd_instance()->__call( $name, $args );
	}

	public static function boot() 
	{
	}

	public function getValidator()
	{
		return new Validator;
	}

	public static function trigger( $scenario )
	{
		if( !is_array( $scenario ) )
		{
			static::$remove_scenario[] = $scenario;
		}
		else
		{
			static::$remove_scenario 	= array_merge( static::$remove_scenario, $scenario );
		}
	}

	public static function setGlobalVar( $globalVar )
	{
		if( is_array( $globalVar ) )
		{
			static::$globalVar 		= array_merge( static::$globalVar, $globalVar );
		}
		else
		{
			static::$globalVar[]	= $globalVar;
		}
	}

	public static function setMiddleWare( $name, \Closure $func )
	{
		static::$middleware[ $name ]['func'] 	= $func;
	}

	public static function addDbConnection( $name, $dbConn )
	{
		static::$dbConnections[$name] 	= $dbConn;

		return static::get_ajd_instance();
	}

	public static function addRuleNamespace( $namespace )
	{
		array_push( static::$addRuleNamespace, $namespace );

		$err 		= static::get_errors_instance();

		if( !EMPTY( $namespace ) )
		{
			$err::addExceptionNamespace( $namespace.'Exceptions\\' );
		}
		else
		{
			$err::addExceptionNamespace( $namespace );	
		}

		return static::get_ajd_instance();
	}

	public static function addRuleDirectory( $directory )
	{
		array_push( static::$addRuleDirectory, $directory );

		$err 		= static::get_errors_instance();

		if( !EMPTY( $directory ) )
		{
			$err::addExceptionDirectory( $directory.'Exceptions'.DIRECTORY_SEPARATOR );
		}

		return static::get_ajd_instance();
	}

	public static function addFilterNamespace( $namespace )
	{
		$filter 	= static::get_filter_ins();

		$filter->addFilterNamespace( $namespace );

		return static::get_ajd_instance();
	}

	public static function addFilterDirectory( $directory )
	{
		$filter 	= static::get_filter_ins();

		$filter->addFilterDirectory( $directory );

		return static::get_ajd_instance();
	}

	public function checkAllMiddleware( $field, $value = NULL, array $customMesage = array(), $check_arr = TRUE )
	{
		if( !EMPTY( static::$middleware ) )
		{
			$current_name 	= key(static::$middleware);

			$this->middleware($current_name, $field, $value, $check_arr);
		}
		else
		{
			return $this->checkArr( $field, $value, $customMesage, $check_arr );
		}
	}

	public function middleware( $name, $field, $value = NULL, $check_arr = TRUE )
	{
		$ajd 			= static::get_ajd_instance();
		$args 			= array( $field, $value, $check_arr );
		$curr_field 	= static::$ajd_prop[ 'current_field' ];
		// $method = !EMPTY( static::$ajd_prop[ 'current_field' ] ) ? 'check' : 'check';
		
		if( ISSET( static::$middleware[ $name ] ) )
		{
			if( !EMPTY( $curr_field ) )
			{
				if( !EMPTY( static::$useContraintGroup ) )
				{
					if( ISSET( static::$ajd_prop[static::$useContraintGroup][ 'fields' ] ) )
					{
						static::$middleware[ $name ][ 'prop' ] 		= static::$ajd_prop[static::$useContraintGroup][ 'fields' ];
					}
					else
					{
						static::$middleware[ $name ][ 'prop' ] 		= static::$ajd_prop[ 'fields' ];
					}
				}
				else
				{
					static::$middleware[ $name ][ 'prop' ] 		= static::$ajd_prop[ 'fields' ];
				}
			}
			else 
			{
				if( !EMPTY( static::$useContraintGroup ) )
				{
					if( ISSET( static::$ajd_prop[static::$useContraintGroup][ Abstract_common::LOG_AND ] ) )
					{
						static::$middleware[ $name ][ 'prop_and' ] 	= static::$ajd_prop[static::$useContraintGroup][ Abstract_common::LOG_AND ];
					}
					else
					{
						static::$middleware[ $name ][ 'prop_and' ] 	= static::$ajd_prop[ Abstract_common::LOG_AND ];
					}

					if( ISSET( static::$ajd_prop[static::$useContraintGroup][ Abstract_common::LOG_OR ] ) )
					{
						static::$middleware[ $name ][ 'prop_or' ] 	= static::$ajd_prop[static::$useContraintGroup][ Abstract_common::LOG_OR ];
					}
					else
					{
						static::$middleware[ $name ][ 'prop_or' ] 	= static::$ajd_prop[ Abstract_common::LOG_OR ];
					}
				}
				else
				{
					static::$middleware[ $name ][ 'prop_and' ] 	= static::$ajd_prop[ Abstract_common::LOG_AND ];	
					static::$middleware[ $name ][ 'prop_or' ] 	= static::$ajd_prop[ Abstract_common::LOG_OR ];
				}
			}

			$middleWareKeys 	= array_keys( static::$middleware );
			$nextKey 			= next( $middleWareKeys );

			if( !EMPTY( $nextKey ) )
			{
				if( ISSET( static::$middleware[ $nextKey ] ) )
				{
					$func 	= function( $q, $args ) use ( $name, $curr_field, $nextKey, $field, $value, $check_arr ) {

						unset( static::$middleware[ $name ] );

						$q->invoke_func( array( $q, 'middleware' ), array( $nextKey, $field, $value, $check_arr ) );

						// unset( static::$middleware[ $name ] );
						
					};

					$currentKeyValue 	= array_search($name, $middleWareKeys);

					unset( $middleWareKeys[ $currentKeyValue ] ); 
				}
			}
			else
			{
				$this->reset_all_validation_prop();

				$func 	= function( $q, $args ) use ( $name, $curr_field ) {
					
					if( !EMPTY( $curr_field ) )
					{
						if( !EMPTY( static::$useContraintGroup ) )
						{
							if( ISSET( static::$ajd_prop[static::$useContraintGroup][ 'fields' ] ) )
							{
								static::$ajd_prop[static::$useContraintGroup][ 'fields' ] 			= static::$middleware[ $name ]['prop'];	
							}
							else
							{
								static::$ajd_prop[ 'fields' ] 										= static::$middleware[ $name ]['prop'];	
							}
						}
						else
						{
							static::$ajd_prop[ 'fields' ] 			= static::$middleware[ $name ]['prop'];	
						}

						unset( static::$middleware[ $name ]['prop'] );
					}
					else 
					{
						if( !EMPTY( static::$useContraintGroup ) )
						{
							if( ISSET( static::$ajd_prop[static::$useContraintGroup][ Abstract_common::LOG_AND ] ) )
							{
								static::$ajd_prop[static::$useContraintGroup][ Abstract_common::LOG_AND ] 		= static::$middleware[ $name ][ 'prop_and' ];
							}
							else
							{
								static::$ajd_prop[ Abstract_common::LOG_AND ] 		= static::$middleware[ $name ][ 'prop_and' ];
							}

							if( ISSET( static::$ajd_prop[static::$useContraintGroup][ Abstract_common::LOG_OR ] ) )
							{
								static::$ajd_prop[static::$useContraintGroup][ Abstract_common::LOG_OR ] 		= static::$middleware[ $name ][ 'prop_or' ];
							}
							else
							{
								static::$ajd_prop[ Abstract_common::LOG_OR ] 		= static::$middleware[ $name ][ 'prop_or' ];
							}
							
						}
						else
						{
							static::$ajd_prop[ Abstract_common::LOG_AND ] 		= static::$middleware[ $name ][ 'prop_and' ];
							static::$ajd_prop[ Abstract_common::LOG_OR ] 		= static::$middleware[ $name ][ 'prop_or' ];
						}

						unset( static::$middleware[ $name ]['prop_and'] );
						unset( static::$middleware[ $name ]['prop_or'] );
					}
					
					$q->invoke_func( array( $q, 'check' ), $args );
				};
			}
			
			$ajd->invoke_func( static::$middleware[ $name ]['func'], array( $ajd, $func, $args ) );

		}
		else 
		{
			$this->reset_all_validation_prop();
		}

	}

	public static function registerClass( $class_name, $path, $namespace = NULL, $from_framework = NULL, $class_method = 'run' )
	{
		$raw_class_name 	= $class_name;
		$class_name 		= ucfirst( strtolower( $class_name ) );
		$args 				= array();
		$args[] 			= $path;

		if( !IS_NULL( $namespace ) )
		{
			$args[] 		= $namespace;
		}

		if( !EMPTY( $path ) AND EMPTY( $from_framework ) )
		{
			if(is_string($path) && !is_object($path))
			{
				$err 		= static::get_errors_instance();

				$err::addExceptionDirectory( $path.DIRECTORY_SEPARATOR.'Exceptions'.DIRECTORY_SEPARATOR );
			}
		}

		static::$ajd_prop[ 'class_override' ][ $class_name.'_'.static::$rules_suffix ] 	= $args;
		static::$ajd_prop[ 'class_override' ][ $raw_class_name ] 						= array( $from_framework, $class_method );
	}

	public static function registerMethod( $rule, $object, $from_framework = FALSE, $args = array() )
	{
		static::$ajd_prop[ 'method_override' ][ $rule.'_'.static::$rules_suffix ] 	= $object;
		static::$ajd_prop[ 'method_override' ][ $rule ] 							= $from_framework;

		if( !EMPTY( $from_framework ) )
		{
			static::$ajd_prop[ 'method_override' ][ $from_framework ] 				= $args;
		}
	}

	public static function registerFunction( $func_name, $func = NULL, $last = FALSE, $val_only = FALSE )
	{
		$func_factory 	= static::get_factory_instance()->get_instance( FALSE, TRUE );

		$func_factory->set_valid_func( $func_name );

		$func_factory->set_values_in_first( $func_name );

		if( $last ) 
		{
			$func_factory->set_values_in_last( $func_name );
		}

		if( $val_only )
		{
			$func_factory->set_only_value( $func_name );
		}

		if( ( !is_bool( $func ) OR $func == FALSE ) )
		{
			static::$ajd_prop[ 'function_override' ][ $func_name ] 	= !IS_NULL( $func ) ? $func : $func_name;
		}

	}

	public static function registerExtension( $extension )
	{
		$name 										= $extension->getName();

		if( !ISSET( static::$ajd_prop['extensions'][ $name ] ) )
		{
			static::$ajd_prop['extensions'][ $name ] = $extension;
		}
	}

	public static function field( $field )
	{
		$key_arr 				= static::get_ajd_and_or_prop();
		$and_arr 				= array();
		$or_arr 				= array();

		$curr_logic 			= static::$ajd_prop[ 'current_logic' ];

		foreach ( $key_arr as $prop ) 
		{
			if( !EMPTY( static::$constraintStorageName ) )
			{
				if( ISSET( static::$ajd_prop[static::$constraintStorageName][ Abstract_common::LOG_AND ][ $prop ] ) )
				{
					$and_arr[ $prop ]	= static::$ajd_prop[static::$constraintStorageName][ Abstract_common::LOG_AND ][ $prop ];
				}
				else
				{
					$and_arr[ $prop ]	= static::$ajd_prop[ Abstract_common::LOG_AND ][ $prop ];
				}

				if( ISSET( static::$ajd_prop[static::$constraintStorageName][ Abstract_common::LOG_OR ][ $prop ] ) )
				{
					$or_arr[ $prop ]	= static::$ajd_prop[static::$constraintStorageName][ Abstract_common::LOG_OR ][ $prop ];
				}
				else
				{
					$or_arr[ $prop ]	= static::$ajd_prop[ Abstract_common::LOG_OR ][ $prop ];
				}
			}
			else
			{
				$and_arr[ $prop ]	= static::$ajd_prop[ Abstract_common::LOG_AND ][ $prop ];
				$or_arr[ $prop ]	= static::$ajd_prop[ Abstract_common::LOG_OR ][ $prop ];
			}
		}
 		
		if( !EMPTY( $and_arr['rules'] ) )
		{   
			foreach ( $and_arr as $key => $value ) 
			{
				if( !EMPTY( static::$constraintStorageName ) )
				{
					static::$ajd_prop[static::$constraintStorageName][ 'fields' ][ Abstract_common::LOG_AND ][ $field ][ Abstract_common::LOG_AND ][ $key ]	 		= $value;
				}
				else
				{
					static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_AND ][ $field ][ Abstract_common::LOG_AND ][ $key ]	 		= $value;
				}
			}
		}
		
		if( !EMPTY( $or_arr['rules'] ) )
		{
			foreach ( $or_arr as $key => $value ) 
			{
				if( !EMPTY( static::$constraintStorageName ) )
				{
					static::$ajd_prop[static::$constraintStorageName][ 'fields' ][ Abstract_common::LOG_OR ][ $field ][ Abstract_common::LOG_AND ][ $key ]	 		= $value;
				}
				else
				{
					static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_OR ][ $field ][ Abstract_common::LOG_AND ][ $key ]	 		= $value;
				}
			}
		}

		static::$ajd_prop[ 'current_field' ] 	= $field;

		return static::get_field_scene_ins( $field, TRUE );
	}

	public static function setMacro( $macro_name, \Closure $func )
	{
		if( !EMPTY( $macro_name ) )
		{
			$ajd 								= static::get_ajd_instance();

			$key_arr 							= static::get_ajd_and_or_prop();

			$ajd->invoke_func( $func, array( $ajd ) );

			foreach( $key_arr as $prop )
			{
				static::$macros[ $macro_name ][ $prop ] 	= static::$ajd_prop[ Abstract_common::LOG_AND ][ $prop ];
			}

			$ajd->reset_all_validation_prop();
		}
	}

	public static function setMacroGroup( $macro_name, \Closure $func )
	{
		if( !EMPTY( $macro_name ) )
		{
			$ajd 			= static::get_ajd_instance();

			$ajd->invoke_func( $func, array( $ajd ) );

			$curr_field 	= static::$ajd_prop[ 'current_field' ];

			if( !EMPTY( $curr_field ) )
				static::$macros[ $macro_name ][ 'fields' ] 		= static::$ajd_prop[ 'fields' ];

			$ajd->reset_all_validation_prop();
		}
	}

	public static function macro( $macro_name )
	{
		$key_arr 					= static::get_ajd_and_or_prop();

		if( ISSET( static::$macros[ $macro_name ] ) )
		{
			if( ISSET( static::$macros[ $macro_name ][ 'fields' ] ) )
			{
				static::$ajd_prop[ 'fields' ] 						= static::$macros[ $macro_name ][ 'fields' ];
			}
			else
			{
				foreach( $key_arr as $prop )
				{
					static::$ajd_prop[ Abstract_common::LOG_AND ][ $prop ] 	= static::$macros[ $macro_name ][ $prop ];
				}
			}
		}

		return static::get_ajd_instance();
	}

	public static function useContraintStorage( $constraintGroup, $clientField = NULL  )
	{
		static::$useContraintGroup 		= $constraintGroup;
		
		if( !EMPTY( $clientField ) AND !EMPTY( static::$ajd_prop['js_rule'] ) )
		{
			if( ISSET( static::$ajd_prop['js_rule'][$constraintGroup] ) )
			{
				static::$ajd_prop['js_rule'][$clientField] 	= static::$ajd_prop['js_rule'][$constraintGroup];
			}
		}

		$ajd_ins 	= static::get_ajd_instance();

		return $ajd_ins;
	}

	public static function storeConstraintTo( $constraintGroup )
	{
		static::$constraintStorageName 	= $constraintGroup;

		$ajd_ins 	= static::get_ajd_instance();		

		return $ajd_ins;
	}

	public static function endstoreConstraintTo()
	{
		static::$constraintStorageName 	= NULL;

		$ajd_ins 	= static::get_ajd_instance();

		return $ajd_ins;
	}

	public static function bail()
	{
		static::$bail = TRUE;

		return static::get_ajd_instance();
	}

	public static function addRule( $rule, $satis = NULL, $custom_err = NULL, $client_side = NULL, $logic = Abstract_common::LOG_AND )
	{
		if( !static::$ajd_prop['extensions_initialize'] )
		{
			static::init_extensions();
		}

		$all_args 								= func_get_args();	

		$real_satis 							= array();
		$real_satis[] 							= $satis;	

		$satis  								= !EMPTY( $satis ) ? $satis : '';	

		$clientMessageOnly 						= FALSE;	

		if( !EMPTY( $all_args ) )
		{
			$arg_proc 			= $all_args;

			unset( $arg_proc[0] );
			unset( $arg_proc[1] );
			
			if( count( $all_args ) > 1 )
			{
				$real_satis				= array_merge( $real_satis, $arg_proc );
			
				$funct_cuss_err 					= NULL;
				$funct_client_side 					= NULL;
				$funct_logic 						= Abstract_common::LOG_AND;
				$funct_client_message_only 			= FALSE;
				
				foreach( $arg_proc as $funct_args )
				{
					if( is_string( $funct_args ) 
						AND ( bool ) preg_match('/@custom_error_/', $funct_args) != FALSE
						AND $funct_args !== Abstract_common::LOG_AND 
						AND $funct_args !== Abstract_common::LOG_OR 
					)
					{
						$funct_cuss_err 			= preg_replace('/@custom_error_/', '', $funct_args);

					}
					else if( is_string( $funct_args ) AND (bool) preg_match( '/#client_/', $funct_args ) != FALSE )
					{
						$funct_client_side 			= preg_replace('/#client_/', '', $funct_args);
					}
					else if( is_string( $funct_args ) AND (bool) preg_match( '/#clientmessageonly/', $funct_args ) != FALSE )
					{
						$funct_client_message_only 	= preg_replace('/#clientmessageonly_/', '', $funct_args);
					}
					else if( $funct_args === Abstract_common::LOG_AND OR $funct_args === Abstract_common::LOG_OR )
					{
						$funct_logic 					= $funct_args;
					}
				}
				
				$custom_err 		= $funct_cuss_err;
				$client_side 		= $funct_client_side;
				$logic 				= $funct_logic;
				$clientMessageOnly 	= $funct_client_message_only;
			}
			
		}
		
		$ajd 									= static::get_ajd_instance();
		$raw_rule 								= static::removeWord( $rule, '/^!/' );
		$rule 									= strtolower( $rule );
		$curr_field 							= static::$ajd_prop[ 'current_field' ];
		$logic 									= strtolower( $logic );
		$clean_rule 							= $ajd->clean_rule_name( $rule );		
		$append_rule 							= ucfirst( $clean_rule['rule'] ).'_'.static::$rules_suffix;
		$rule_kind 								= $ajd->_process_rule_kind( $clean_rule['rule'], $append_rule, $raw_rule, $real_satis );

		$curr_logic 							= static::$ajd_prop['current_logic'];
		
		$args 									= array(
			'curr_field' 		=> $curr_field,
			'clean_rule' 		=> $clean_rule,
			'satis' 			=> $real_satis,
			'rule_kind' 		=> $rule_kind,
			'append_rule' 		=> $append_rule,
			'logic' 			=> $logic,
			'curr_logic' 		=> $curr_logic,
			'custom_err' 		=> $custom_err,
			'client_side' 		=> $client_side,
			'raw_rule' 			=> $raw_rule,
			'rule' 				=> $rule,
			'client_message_only' => $clientMessageOnly
		);

		static::plotValidationDetails( $args );

		return static::get_scene_ins( $clean_rule['rule'], $logic, TRUE );
	}

	protected static function plotValidationDetails( array $args )
	{
		$curr_field 	= $args['curr_field'];
		$clean_rule 	= $args['clean_rule'];
		$satis 			= $args['satis'];
		$rule_kind 		= $args['rule_kind'];
		$append_rule 	= $args['append_rule'];
		$logic 			= $args['logic'];
		$curr_logic 	= $args['curr_logic'];
		$custom_err 	= $args['custom_err'];
		$client_side 	= $args['client_side'];
		$raw_rule 		= $args['raw_rule'];
		$rule 			= $args['rule'];
		$clientMessageOnly = $args['client_message_only'];

		if( !EMPTY( $curr_field ) )
		{
			$key_value 							= array(
				'rules' 						=> $clean_rule['rule'],
				'satisfier' 					=> $satis,
				'details' 						=> array( $clean_rule['check'], $append_rule, $rule_kind['rule_kind'], $rule_kind['args'], $rule_kind['lower_rule'] ),
			);

			foreach( $key_value as $key => $value )
			{
				if( !EMPTY( static::$constraintStorageName ) )
				{
					static::$ajd_prop[ static::$constraintStorageName ][ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ $key ][] 	= $value;
				}
				else
				{
					static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ $key ][] 	= $value;
				}
			}

			// static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'cus_err' ] 									= array();
			if( !EMPTY( static::$constraintStorageName ) )
			{
				static::$ajd_prop[static::$constraintStorageName][ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'filters' ][] 									= NULL;
				static::$ajd_prop[static::$constraintStorageName][ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'filter_satis' ][] 								= NULL;
				static::$ajd_prop[static::$constraintStorageName][ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'pre_filters' ][] 								= NULL;
				/*static::$ajd_prop[static::$constraintStorageName][ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'scenarios' ] 									= array();*/
				static::$ajd_prop[static::$constraintStorageName][ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'sometimes' ][ $clean_rule['rule'] ] 			= NULL;
			}
			else
			{
				static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'filters' ][] 								= NULL;
				static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'filter_satis' ][] 							= NULL;
				static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'pre_filters' ][] 							= NULL;

				/*if( EMPTY( static::$ajd_prop[ 'check_group' ] ) )
				{
					static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'scenarios' ] 								= array();
				}*/

				static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'sometimes' ][ $clean_rule['rule'] ] 		= NULL;
			}

			if( !EMPTY( $custom_err ) )
			{
				if( !EMPTY( static::$constraintStorageName ) )
				{
					static::$ajd_prop[static::$constraintStorageName][ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'cus_err' ][ $clean_rule[ 'rule' ] ] 			= $custom_err;
				}
				else
				{
					static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'cus_err' ][ $clean_rule[ 'rule' ] ] 			= $custom_err;
				}
			}
		}
		else 
		{
			if( !EMPTY( static::$constraintStorageName ) )
			{
				static::$ajd_prop[static::$constraintStorageName][ $logic ][ 'rules' ][] 			= $clean_rule['rule'];
				static::$ajd_prop[static::$constraintStorageName][ $logic ][ 'satisfier' ][] 		= $satis;
				static::$ajd_prop[static::$constraintStorageName][ $logic ][ 'details' ][] 			= array( $clean_rule['check'], $append_rule, $rule_kind['rule_kind'], $rule_kind['args'], $rule_kind['lower_rule'] );

				static::$ajd_prop[static::$constraintStorageName][ $logic ][ 'sometimes' ][ $clean_rule['rule'] ] = NULL;
				
				if( !EMPTY( $custom_err ) ) 
				{
					$rule_name 		= $clean_rule[ 'rule' ];

					static::$ajd_prop[static::$constraintStorageName][ $logic ][ 'cus_err' ][ $rule_name ] 	= $custom_err;
				}
			}
			else
			{
				static::$ajd_prop[ $logic ][ 'rules' ][] 			= $clean_rule['rule'];
				static::$ajd_prop[ $logic ][ 'satisfier' ][] 		= $satis;
				static::$ajd_prop[ $logic ][ 'details' ][] 			= array( $clean_rule['check'], $append_rule, $rule_kind['rule_kind'], $rule_kind['args'], $rule_kind['lower_rule'] );

				static::$ajd_prop[ $logic ][ 'sometimes' ][ $clean_rule['rule'] ] = NULL;
				
				if( !EMPTY( $custom_err ) ) 
				{
					$rule_name 		= $clean_rule[ 'rule' ];

					static::$ajd_prop[ $logic ][ 'cus_err' ][ $rule_name ] 	= $custom_err;
				}
			}
		}

		if( !EMPTY( $client_side ) )
		{
			$orig_rule 		= strtolower( $rule );
			$rule 			= $orig_rule.'_'.static::$rules_suffix;

			static::$ajd_prop[ 'js_rule' ][ $client_side ][ $rule ][] 		= array(
				'satisfier'		=> $satis,
				'curr_field'	=> $curr_field,
				'client_message_only' => $clientMessageOnly
			);


			if( !EMPTY( $custom_err ) )
			{
				static::$ajd_prop[ 'js_rule' ][ $client_side ][ $rule ][] 	= array(
					'custom_error'	=> array( $orig_rule => $custom_err )
				);
			}
		}
	}

	public static function addOrRule( $rule, $satis = NULL, $custom_err = NULL, $client_side = NULL )
	{
		return static::addRule( $rule, $satis, $custom_err, $client_side, Abstract_common::LOG_OR );
	}

	public static function superRule( $rule, $satis = NULL, $logic = Abstract_common::LOG_AND, $custom_err = NULL, $client_side = NULL )
	{
		static::$ajd_prop[ 'current_field' ] 				= NULL;

		static::$ajd_prop[ 'and_or_stack' ][] 	= $logic;
		static::$ajd_prop['current_logic'] 		= $logic;
		
		return static::addRule( $rule, $satis, $custom_err, $client_side, $logic );

	}

	public static function endSuperRule()
	{
		static::$ajd_prop[ 'current_field' ] 				= NULL;

		$logic 		= array_pop( static::$ajd_prop[ 'and_or_stack' ] );

		$key_arr 	= static::get_ajd_and_or_prop();

		foreach( $key_arr as $key )
		{	
			if( ISSET( static::$ajd_prop[ $logic ][ $key ] ) )
				array_pop( static::$ajd_prop[ $logic ][ $key ] );

			/*if( ISSET( static::$ajd_prop[ 'fields' ][ $logic ][ $key ] ) )
				array_pop( static::$ajd_prop[ 'fields' ][ $logic ][ $key ] );*/
		}

		return static::get_ajd_instance();
	}

	public static function getClientSide( $perField = TRUE, $format = Client_side::PARSLEY )
	{
		$ajdIns 		= static::get_ajd_instance();
		
		$clientSide 	= new Client_side( static::$ajd_prop['js_rule'], $ajdIns, $format );
		
		return $clientSide->get_js_validations($perField);
	}

	public static function getCacheInstance()
	{
		return static::$cache_instance;
	}

	public static function getCacheInstanceByField()
	{
		return static::$cacheByFieldInstance;
	}

	public static function cacheFilter( $field )
	{
		$logic 												= static::$ajd_prop[ 'current_logic' ];
		$curr_field 										= static::$ajd_prop[ 'current_field' ];
		$filters 											= array();
		$filter_satis 										= array();
		$pre_filters 										= array();

		if( !EMPTY( $curr_field ) )
		{
			if( !EMPTY( static::$constraintStorageName ) )
			{
				$filters 		= static::$ajd_prop[static::$constraintStorageName][ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ]['filters'];
				$filter_satis 	= static::$ajd_prop[static::$constraintStorageName][ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ]['filter_satis'];
				$pre_filters 	= static::$ajd_prop[static::$constraintStorageName][ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ]['pre_filters'];

				static::$ajd_prop[static::$constraintStorageName][ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ]['filters'] 			= array();

				static::$ajd_prop[static::$constraintStorageName][ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ]['filter_satis']	= array();

				static::$ajd_prop[static::$constraintStorageName][ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ]['pre_filters']	= array();
			}
			else
			{
				$filters 		= static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ 'filters' ];
				$filter_satis 	= static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ 'filter_satis' ];
				$pre_filters 	= static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ 'pre_filters' ];

				static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ 'filters' ] 		= array();
				static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ 'filter_satis' ] 	= array();
				static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ 'pre_filters' ] 	= array();
			}
		}
		else
		{
			if( !EMPTY( static::$constraintStorageName ) )
			{
				$filters 		= static::$ajd_prop[static::$constraintStorageName][ $logic ]['filters'];
				$filter_satis 	= static::$ajd_prop[static::$constraintStorageName][ $logic ]['filter_satis'];
				$pre_filters 	= static::$ajd_prop[static::$constraintStorageName][ $logic ]['pre_filters'];

				static::$ajd_prop[static::$constraintStorageName][ $logic ]['filters'] 			= array();
				static::$ajd_prop[static::$constraintStorageName][ $logic ]['filter_satis'] 	= array();
				static::$ajd_prop[static::$constraintStorageName][ $logic ]['pre_filters'] 		= array();
			}
			else
			{
				$filters 		= static::$ajd_prop[ $logic ]['filters'];
				$filter_satis 	= static::$ajd_prop[ $logic ]['filter_satis'];
				$pre_filters 	= static::$ajd_prop[ $logic ]['pre_filters'];

				static::$ajd_prop[ $logic ]['filters'] 			= array();
				static::$ajd_prop[ $logic ]['filter_satis'] 	= array();
				static::$ajd_prop[ $logic ]['pre_filters'] 		= array();
				
			}
		}

		static::$ajd_prop['cache_filters'][ $field ] 	= array(
			'filters' 			=> $filters,
			'filter_satis'		=> $filter_satis,
			'pre_filters' 		=> $pre_filters
		);

		return static::get_ajd_instance();
	}

	public static function filterSingleValue( $value, $val_only = FALSE, $check_arr = TRUE, $clearCache = TRUE )
	{
		$filter_value 	= $value;

		if( !EMPTY( static::$ajd_prop['cache_filters'] ) )
		{
			foreach( static::$ajd_prop['cache_filters'] as $field => $filter_details )
			{
				if( !EMPTY( $filter_details['filters'] ) )
				{
					$check 	= TRUE;

					if( !EMPTY( $check_arr ) )
					{
						if( !is_array( $value ) )
						{
							$check 	= FALSE;
						}
					}

					$real_val 			= static::handle_filter( $filter_details['filters'], $value, $field, $filter_details['filter_satis'], $filter_details['pre_filters'], $check, $val_only );

					$pre_filt_value 	= static::pre_filter_value( $field );
					$filt_value 		= static::filter_value( $field );

					if( $val_only )
					{
						$new_value		= $real_val;
					}
					else
					{
						$new_value  	= ( ISSET( $pre_filt_value ) AND !EMPTY( $pre_filt_value ) ) ? $pre_filt_value : $filt_value;
					}

					if( EMPTY( $new_value ) )
					{
						$new_value 	= $value;
					}

					$filter_value	= $new_value;
				}
			}
		}

		if( $clearCache )
		{
			static::$ajd_prop['cache_filters'] 	= array();
		}
		
		return $filter_value;
	}

	public static function filterValues( array $values, $check_arr = TRUE )
	{
		$filter_value 	= array();
		$ajd_ins 		= static::get_ajd_instance();

		if( !EMPTY( static::$ajd_prop['cache_filters'] ) )
		{
			foreach( static::$ajd_prop['cache_filters'] as $field => $filter_details )
			{
				if( ISSET( $values[ $field ] ) )
				{
					$value 		= $values[ $field ];
					
					$new_value 	= $ajd_ins->filterSingleValue( $value, TRUE, $check_arr, FALSE );

					if( EMPTY( $new_value ) )
					{
						$new_value 	= $value;
					}

					$filter_value[ $field ]	= $new_value;
				}
			}
		}

		static::$ajd_prop['cache_filters'] 	= array();

		return $filter_value;
	}

	public static function addFilter( $filter, $satis = NULL, $pre_filter = FALSE )
	{
		if( !static::$ajd_prop['extensions_initialize'] )
		{
			static::init_extensions();
		}
		
		$logic 												= static::$ajd_prop[ 'current_logic' ];
		$curr_field 										= static::$ajd_prop[ 'current_field' ];

		if( !EMPTY( $curr_field ) )
		{
			$key_value 							= array(
				'filters' 						=> $filter,
				'filter_satis' 					=> $satis,
				'pre_filters' 					=> $pre_filter
			);			

			foreach( $key_value as $key => $value )
			{
				if( !EMPTY( static::$constraintStorageName ) )
				{
					static::$ajd_prop[static::$constraintStorageName][ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ $key ][] 	= $value;
				}
				else
				{
					static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ $key ][] 	= $value;
				}
			}			
		}
		else 
		{
			if( !EMPTY( static::$constraintStorageName ) )
			{
				static::$ajd_prop[static::$constraintStorageName][ $logic ]['filters'][] 		= strtolower( $filter );
				static::$ajd_prop[static::$constraintStorageName][ $logic ]['filter_satis'][] 	= $satis;
				static::$ajd_prop[static::$constraintStorageName][ $logic ]['pre_filters'][] 	= $pre_filter;
			}
			else
			{
				static::$ajd_prop[ $logic ]['filters'][] 		= strtolower( $filter );
				static::$ajd_prop[ $logic ]['filter_satis'][] 	= $satis;
				static::$ajd_prop[ $logic ]['pre_filters'][] 	= $pre_filter;
			}
		}
 		
		return static::get_ajd_instance();
	}

	public static function add_rule_msg( $rule, $msg )
	{
		$err 		= static::get_errors_instance();

		$err->set_errors( $rule, $msg );
	}

	public static function assert( $addParent = TRUE )
	{
		$ajd 	= static::get_ajd_instance();
		
		if( $ajd->validation_fails() )
		{
			if( !EMPTY( $ajd->errors()->all() ) )
			{
				throw new \Exception( $ajd->errors()->toStringErr(array(), $addParent) );
			}
		}
	}

	public static function assertFirst( $addParent = TRUE )
	{
		$ajd 	= static::get_ajd_instance();

		if( $ajd->validation_fails() )
		{
			if( !EMPTY( $ajd->errors()->all() ) )
			{
				throw new \Exception( $ajd->errors()->toStringErr( $ajd->errors()->firstAll(), $addParent ) );
			}
		}
	}

	private function _checkGroup( array $data, $middleware = FALSE )
	{
		static::$ajd_prop['check_group'] 	= TRUE;
		
		$value 								= NULL;
		$or_success 						= array();

		$or_pass_arr 						= array();

		$obs           	 					= static::get_observable_instance();
		$ev									= static::get_event_dispatcher_instance();

		$and_check 							= array();
		$or_check 							= array();

		$validator 							= $this->getValidator();
		$paramValidator 					= $validator->one_or( Validator::contains('.'), Validator::contains('*') );

		if( ISSET( static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_OR ] ) )
		{
			if( !EMPTY( static::$useContraintGroup ) )
			{
				$or_field 						= static::$ajd_prop[static::$useContraintGroup][ 'fields' ][ Abstract_common::LOG_OR ];
			}
			else
			{
				$or_field 						= static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_OR ];
			}

			if( !EMPTY( $or_field ) )
			{
				foreach( $or_field as $field_key => $field_value )
				{
					$fieldValueOr 	= array();

					$realFieldKey 	= Validation_helpers::getParentPath($field_key);
					
					if( ISSET( $field_value[Abstract_common::LOG_OR] ) )
					{
						$fieldValueOr 	= $field_value[Abstract_common::LOG_OR];
					}

					$propScene 			= $this->clearScenario( $field_value[Abstract_common::LOG_AND], $fieldValueOr );

					$field_value[Abstract_common::LOG_AND] 	= $propScene['prop_and'];
					$field_value[Abstract_common::LOG_OR] 	= $propScene['prop_or'];

					if( ISSET( $data[ $realFieldKey ] ) ) 
					{
						$value 				= $data[ $realFieldKey ];
					}
					else 
					{
						$value 				= '';
					}
					
					if( ISSET( $field_value[Abstract_common::LOG_AND]['scenarios'] ) OR ISSET( $field_value[Abstract_common::LOG_OR]['scenarios'] ) )
					{
						$and_search = array();
						$or_search 	= array();

						if( ISSET( $field_value[Abstract_common::LOG_AND]['scenarios'] ) )
						{
							$and_search = $this->array_search_recursive( $field_key, $field_value[Abstract_common::LOG_AND]['scenarios'] );
						}

						if( ISSET( $field_value[Abstract_common::LOG_OR]['scenarios'] ) )
						{
							$or_search 	= $this->array_search_recursive( $field_key, $field_value[Abstract_common::LOG_OR]['scenarios'] );
						}
						
						if( !EMPTY( $and_search ) OR !EMPTY( $or_search ) )
						{
							return;
						}
					}

					if( $paramValidator->validate($field_key) )
					{
						$field_key 		= Validation_helpers::removeParentPath( $realFieldKey, $field_key );
					}
 					
					$or 					 = $this->checkArr( $field_key, $value, array(), TRUE, Abstract_common::LOG_OR, $field_value );
					
					$or_check[] 			 = $this->validation_fails( $field_key );
					
					$or_pass_arr['pass_arr'] = $or[ Abstract_common::LOG_AND ]['pass_arr'];
					
					$cnt 					 = 0;
					
					if( !EMPTY( $or_pass_arr['pass_arr'] ) )  
					{

						foreach( $or_pass_arr['pass_arr'] as $key_res => $val_res )
						{
							if( !EMPTY( $val_res ) )
							{
								if( ISSET( $or_pass_arr['pass_arr'][ 0 ] ) AND is_array( $or_pass_arr['pass_arr'][ 0 ] ) )
								{
									foreach( $val_res as $k => $v )
									{
										$or_success[ $key_res ][ $k ]['passed'][] 		= $or[Abstract_common::LOG_AND]['passed'][ $cnt ];

										if( !EMPTY( $v ) AND ISSET( $v[0] ) )
										{
											$or_success[ $key_res ][ $k ]['rules'][] 		= $v[0];
											$or_success[ $key_res ][ $k ]['satisfier'][] 	= $v[1];
											$or_success[ $key_res ][ $k ]['cus_err'][] 		= $v[2][ $k ];
											$or_success[ $key_res ][ $k ]['values'][] 		= $v['values'][$v[0]];
											$or_success[ $key_res ][ $k ]['append_error'][] = $v[3][ $k ];

										}

										$cnt++;
									}
									
								}
								else 
								{
									/*if( ISSET( $val_res[0] ) )
									{*/
										$or_success[ $key_res ]['passed'][] 		= $or[Abstract_common::LOG_AND]['passed'][ $cnt ];

										if( ISSET( $val_res[0] ) )
										{
											
											$or_success[ $key_res ]['rules'][] 			= $val_res[0];
											$or_success[ $key_res ]['satisfier'][] 		= $val_res[1];
											$or_success[ $key_res ]['cus_err'][] 		= $val_res[2][ $key_res ];
											$or_success[ $key_res ]['values'][] 		= $val_res['values'][$val_res[0]];

											$or_success[ $key_res ]['append_error'][$val_res[0]] 	= $val_res[3][ $key_res ];
										}
									// }

									$cnt++;
								}
							}
						}
					}
				}
			
				$or_field_arr 					= array();

				if( !EMPTY( static::$useContraintGroup ) )
				{
					$or_field_name 				= current( array_keys( static::$ajd_prop[static::$useContraintGroup][ 'fields' ][ Abstract_common::LOG_OR ] ) );

					$or_field_arr 				= static::$ajd_prop[static::$useContraintGroup][ 'fields' ][ Abstract_common::LOG_OR ];
 			 	}
 			 	else
 			 	{
					$or_field_name 				= current( array_keys( static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_OR ] ) );
					$or_field_arr 				= static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_OR ];
 			 	}

				$details 					= $or_field[ $or_field_name ][ Abstract_common::LOG_AND ];
				$field_arr 					= $this->format_field_name( $or_field_name );
				
				$subCheck 					= $this->_processOrCollection( $or_field_arr, $or_success, $or_field, $data );

				$value_or = (isset($data[$field_arr['orig']])) ? $data[$field_arr['orig']] : null;

				if( ISSET( static::$ajd_prop['events'][Abstract_common::EV_LOAD][$field_arr['orig']] ) )
				{
					$eventLoad 	= static::$ajd_prop['events'][Abstract_common::EV_LOAD][$field_arr['orig']];

					unset(static::$ajd_prop['events'][Abstract_common::EV_LOAD][$field_arr['orig']]);


					$this->_runEvents($eventLoad, $value_or, $field_arr['orig']);
				}

				if(!in_array(0, $subCheck))
				{
					if( ISSET( static::$ajd_prop['events'][Abstract_common::EV_FAILS][$field_arr['orig']] ) )
					{
						$eventFails 	= static::$ajd_prop['events'][Abstract_common::EV_FAILS][$field_arr['orig']];

						unset(static::$ajd_prop['events'][Abstract_common::EV_FAILS][$field_arr['orig']]);
						$this->_runEvents($eventFails, $value_or, $field_arr['orig']);
					}
				}
				else
				{
					if( ISSET( static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$field_arr['orig']] ) )
					{
						$eventSuccess 	= static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$field_arr['orig']];

						unset(static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$field_arr['orig']]);
						$this->_runEvents($eventSuccess, $value_or, $field_arr['orig']);
					}
				}

				$or_check 					= array_merge( $or_check, $subCheck );
			}

		}

		$check_and_arr 		= NULL;

		if( !EMPTY( static::$useContraintGroup ) )
		{
			$check_and_arr 	= static::$ajd_prop[static::$useContraintGroup][ 'fields' ][ Abstract_common::LOG_AND ];
		}
		else
		{
			if( ISSET( static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_AND ] ) )
			{
				$check_and_arr 	= static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_AND ];
			}
		}

		if( !EMPTY( $check_and_arr ) )
		{
			$and_field 						= $check_and_arr;

			if( !EMPTY( $and_field ) )
			{
				foreach( $and_field as $field_key => $field_value )
				{
					$realFieldKey 	= Validation_helpers::getParentPath($field_key);

					$fieldValueOr 	= array();

					if( ISSET( $field_value[Abstract_common::LOG_OR] ) )
					{
						$fieldValueOr = $field_value[Abstract_common::LOG_OR];
					}

					$propScene 		= $this->clearScenario( $field_value[Abstract_common::LOG_AND], $fieldValueOr );

					$field_value[Abstract_common::LOG_AND] 	= $propScene['prop_and'];
					$field_value[Abstract_common::LOG_OR] 	= $propScene['prop_or'];

					if( ISSET( $field_value[Abstract_common::LOG_AND]['scenarios'] ) OR ISSET( $field_value[Abstract_common::LOG_OR]['scenarios'] ) )
					{
						$and_search = array();
						$or_search 	= array();

						if( ISSET( $field_value[Abstract_common::LOG_AND]['scenarios'] ) )
						{
							$and_search = $this->array_search_recursive( $field_key, $field_value[Abstract_common::LOG_AND]['scenarios'] );
						}

						if( ISSET( $field_value[Abstract_common::LOG_OR]['scenarios'] ) )
						{
							$or_search 	= $this->array_search_recursive( $field_key, $field_value[Abstract_common::LOG_OR]['scenarios'] );
						}
						
						if( !EMPTY( $and_search ) OR !EMPTY( $or_search ) )
						{
							continue;
						}
					}

					if( ISSET( $data[ $realFieldKey ] ) )
					{
						$value 				= $data[ $realFieldKey ];
					}
					else 
					{
						$value 				= '';
					}

					if( $middleware )
					{

					}
					else 
					{
						if( $paramValidator->validate($field_key) )
						{
							$field_key 		= Validation_helpers::removeParentPath( $realFieldKey, $field_key );
						}

						if( ISSET( static::$ajd_prop['events'][Abstract_common::EV_LOAD][$field_key] ) )
						{
							$eventLoad 	= static::$ajd_prop['events'][Abstract_common::EV_LOAD][$field_key];

							unset(static::$ajd_prop['events'][Abstract_common::EV_LOAD][$field_key]);


							$this->_runEvents($eventLoad, $value, $field_key);
						}

						$this->checkArr( $field_key, $value, array(), TRUE, Abstract_common::LOG_AND, $field_value );

						$val_and_fails 		= $this->validation_fails( $field_key );

						$and_check[] 		= $val_and_fails;

						if($val_and_fails)
						{
							if( ISSET( static::$ajd_prop['events'][Abstract_common::EV_FAILS][$field_key] ) )
							{
								$eventFails 	= static::$ajd_prop['events'][Abstract_common::EV_FAILS][$field_key];

								unset(static::$ajd_prop['events'][Abstract_common::EV_FAILS][$field_key]);
								$this->_runEvents($eventFails, $value, $field_key);
							}
						}
						else
						{
							if( ISSET( static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$field_key] ) )
							{
								$eventSuccess 	= static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$field_key];

								unset(static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$field_key]);
								$this->_runEvents($eventSuccess, $value, $field_key);
							}
						}
					}

				}
			}

		}

		$obs->attach_observer( 'passed', $ev, array( $this ) );
		$obs->attach_observer( 'fails', $ev, array( $this ) );
		
		if( in_array( 1, $and_check ) OR in_array( 1, $or_check ) ) 
		{
			$obs->notify_observer( 'fails' );
		}

		if( !in_array( 1, $and_check ) AND !in_array( 1, $or_check ) ) 
		{
			$obs->notify_observer( 'passed' );
		}

		$this->reset_check_group();
		$this->reset_all_validation_prop();

		return $ev;

	}

	public static function subscribe($event, \Closure $callback)
	{
		$obs            = static::get_observable_instance();
		$ajds 			= static::get_ajd_instance();

		$obs->attach_observer( $event, $callback, array( $ajds ) );

		return $ajds;
	}

	private function _processOrCollection( array $or_field_arr, array $or_success, array $or_field, array $data )
	{
		$check 						= array();

		if( !EMPTY( $or_field_arr ) )
		{
			foreach( $or_field_arr as $f_arr => $f_arr_v )
			{
				$or_field_details 	= $or_field[ $f_arr ][ Abstract_common::LOG_AND ];
				$or_f_arr 			= $this->format_field_name( $f_arr );

				if(isset($data[ $f_arr ]))
				{
					if( is_array( $data[ $f_arr ] ) )
					{
						$this->_processMultiOrRule( $or_success, $or_field_details, $or_f_arr );
					}
					else
					{
						$this->_processSingleOrRule( $or_success, $or_field_details, $or_f_arr, $data[ $f_arr ] );
					}
				}

				$check[] 			= $this->validation_fails($or_f_arr['orig']);
			}
		}

		return $check;
	}

	private function _processMultiOrRule( array $or_success, array $or_field_details, array $or_f_arr )
	{
		foreach( $or_success as $rule => $value_ar )
		{
			if( is_numeric( $rule ) )
			{
				$r_cnt 								= 0;

				foreach ( $value_ar as $r => $v ) 
				{
					$check_rule 					= array_search($r, $or_field_details['rules']);
					
					if( !in_array( 1, $v['passed'] ) AND $check_rule !== FALSE )
					{
						$real_det 					= array();
						
						$real_det['clean_field'] 	= $or_f_arr['clean'];
						$real_det['orig_field'] 	= $or_f_arr['orig'];
						$real_det['rule'] 			= $r;
						$real_det['satisfier'] 		= $v['satisfier'][0];
						$real_det['value'] 			= $v['values'][0];						
						$real_det['cus_err'] 		= $v['cus_err'][0];
						$real_det['append_error']	= $v['append_error'][0];
						 
					 	$real_det['details'] 		= $or_field_details['details'][$r_cnt];
					 	
						$this->handle_errors( $real_det, TRUE, $rule );
					}
					
					$r_cnt++;
				}
			}
		}
	}

	private function _processSingleOrRule( array $or_success, array $or_field_details, array $or_f_arr, $dataValue )
	{
		foreach( $or_field_details['rules'] as $rule_key => $rule_per )
	 	{
	 		
	 		if( ISSET( $or_success[$rule_per]['passed'] ) AND !in_array( 1, $or_success[$rule_per]['passed'] ) )
			{
	 			
	 			$check_rule 					= array_search($rule_per, $or_field_details['rules']);

		 		if( $check_rule !== FALSE )
		 		{
			 		$real_det 					= array();
			 		$real_det['clean_field'] 	= $or_f_arr['clean'];
					$real_det['orig_field'] 	= $or_f_arr['orig'];
					$real_det['rule'] 			= $rule_per;
					$real_det['satisfier'] 		= ( ISSET( $or_success[$rule_per]['satisfier'][$rule_key] ) ) ? $or_success[$rule_per]['satisfier'][$rule_key] : NULL;
					$real_det['value'] 			= $dataValue;						

					if( ISSET( $or_success[$rule_per]['cus_err'][$rule_key] ) )
					{
						$real_det['cus_err'] 	= $or_success[$rule_per]['cus_err'][$rule_key];
					}
					else
					{
						$real_det['cus_err'] 	= array();
					}

					if( ISSET( $or_success[$rule_per]['append_error'][$rule_per]  ) )
					{

						$real_det['append_error']	= $or_success[$rule_per]['append_error'][$rule_per];
					}
					
				 	$real_det['details'] 		= $or_field_details['details'][$rule_key];
				 	
					$this->handle_errors( $real_det, FALSE );
				}
			}
		}
	}

	public function checkGroup( array $data )
	{	
		return $this->_checkGroup( $data );
	}

	public function checkProperty( $object, $propertyName, $check_arr = TRUE )
	{
		return static::getMetadata()->checkMetadata( Metadata::PROP_PROPERTY, $object, $propertyName, $check_arr );
	}

	public function checkMethod( $object, $methodName, $check_arr = TRUE )
	{
		return static::getMetadata()->checkMetadata( Metadata::METH_PROPERTY, $object, $methodName, $check_arr );
	}

	public function checkClass( $object, $className = NULL, $check_arr = TRUE )
	{
		return static::getMetadata()->checkMetadata( Metadata::CLASS_PROPERTY, $object, $className, $check_arr );
	}

	protected function processCustomMessage( array $customMesage, $value )
	{
		if( !EMPTY( $customMesage ) )
		{
			foreach( $customMesage as $find => $message )
			{
				$formatFind		= $this->format_field_name( $find );

				if( is_array( $value ) )
				{
					$custData 	= Validation_helpers::initializeProcessData($formatFind['orig'], $value);
				}
				else
				{
					$custData 	= $value;
				}

				$this->addToCustomMessage( $custData, $message, $formatFind['orig'], $formatFind['clean'] );
			}
		}
	}

	protected function addToCustomMessage( array $custData, $message, $findOrig, $findClean, $prependField = '' )
	{
		if( is_array( $custData ) )
		{
			foreach( $custData as $field => $val )
			{
				$realField 		= $field;
				$passArr 		= array(
					'message'		=> $message
				);

				if( !EMPTY( $prependField ) )
				{
					$realField 	= $prependField.'.'.$field;
				}

				$passArr['formatField'] 	= NULL;

				if( $realField == $findOrig )
				{
					$passArr['formatField'] = $findClean;
				}

				if( is_array( $val ) )
				{
					$this->customMesage[ $realField ] = $passArr;
					
					$this->addToCustomMessage( $val, $message, $findOrig, $findClean, $realField );
				}
				else
				{
					$this->customMesage[ $realField ] = $passArr;
				}
			}
		}
		else
		{
			$passArr 					= array(
				'message'				=> $message
			);

			$passArr['formatField'] 	= $findClean;

			$this->customMesage[ $findOrig ] = $passArr;
		}
	}

	public function checkArr( $field, $value, $customMesage = array(), $check_arr = TRUE, $logic = Abstract_common::LOG_AND, $group = NULL )
	{
		$obs            = static::get_observable_instance();
		$ev				= static::get_event_dispatcher_instance();

		$this->processCustomMessage( $customMesage, $value ); 

		$checks 		= $this->_checkArr( $field, $value, $check_arr, $logic, $group );

		if( is_array( $checks ) )
		{
			if( EMPTY( $group ) )
			{
				if( ISSET( $checks['checkValidations'] ) )
				{
					$obs->attach_observer( $field.'-|passed', $ev, array( $this ) );
					$obs->attach_observer( $field.'-|fails', $ev, array( $this ) );

					if( !in_array(TRUE, $checks['checkValidations']) ) 
					{
						$obs->notify_observer( $field.'-|passed' );
					}
					else
					{
						$obs->notify_observer( $field.'-|fails' );
					}

					$this->reset_all_validation_prop();

					return $ev;
				}
			}
			else
			{
				if( ISSET( $checks['checkArr'] ) )
				{
					return $checks['checkArr'];
				}
				else
				{
					return $checks;
				}
			}
		}
		else 
		{
			return $checks;
		}
	}

	protected function _checkArr( $field, $value, $check_arr = TRUE, $logic = Abstract_common::LOG_AND, $group = NULL )
	{
		$validator 		= $this->getValidator();
		$paramValidator 	= $validator->one_or( Validator::contains('.'), Validator::contains('*') );

		static $checkValidations 	= array();
		static $checkArr 			= array();

		if( 
			( $paramValidator->validate( $field ) ) 
			AND is_array( $value ) 
		)
		{
			$data 		= Validation_helpers::initializeProcessData($field, $value);
			
			foreach( $data as $subField => $v )
			{
				$formatSubField 	= $subField;
				$customDetails 		= array();

				if( ISSET( $this->customMesage[ $subField ] ) )
				{
					$customDetails 		= $this->customMesage[ $subField ];

					if( !EMPTY( $customDetails['formatField'] ) )
					{
						$formatSubField = $subField.'|'.$customDetails['formatField'];
					}
				}

				if( is_array( $v ) )
				{
					$this->_checkArr($subField.'.*', $value, $check_arr, $logic, $group);
				}
				else
				{
					$checkDet 				= $this->check( $formatSubField, $v, $check_arr, $logic, $group, TRUE, $value );

					if( is_array( $checkDet ) )
					{
						$checkArr 			= array_merge( $checkArr, $checkDet );
					}
					else
					{
						$checkArr 			= $checkDet;
					}

					$checkValidations[] 	= $this->validation_fails( $subField );
				}
			}

			return array(
				'checkValidations' 	=> $checkValidations,
				'checkArr' 			=> $checkArr
			);
		}
		else
		{			
			$check 		= $this->check( $field, $value, $check_arr, $logic, $group, FALSE, $value );

			return $check;
		}
	}

	public function checkDependent( $field, $value = NULL, $origValue = NULL, array $customMessage = array(), $check_arr = TRUE, $logic = Abstract_common::LOG_AND, $group = NULL, $dontReset = FALSE )
	{
		$validator 			= $this->getValidator();
		$paramValidator 	= $validator->one_or( Validator::contains('.'), Validator::contains('*') );

		if( $paramValidator->validate( $field ) )
		{
			return $this->checkArr( $field, $value, $customMessage, $check_arr, $logic, $group );
		}
		else
		{
			return $this->check( $field, $value, $check_arr, $logic, $group, $dontReset, $origValue );
		}
	}

	public function check( $field, $value = NULL, $check_arr = TRUE, $logic = Abstract_common::LOG_AND, $group = NULL, $dontReset = FALSE, $origValue = NULL )
	{
		$prop_or 		= array();
		$prop_and 		= array();
		$prop  			= array();

		$field_arr 		= $this->format_field_name( $field );

		if( is_array( $value ) )
		{
			if( ISSET( $value[ $field_arr['orig'] ] ) )
			{
				$value 	= $value[$field_arr['orig']];
			}
			else
			{
				$value 	= NULL;
			}
		}
			
		// if( $logic == Abstract_common::LOG_AND )
		// {
			$prop_and 	= static::process_check_args( Abstract_common::LOG_AND, $group );
			// $prop 		= $prop_and;
		// }
        
		/*if( $logic == Abstract_common::LOG_OR )
		{*/
			$prop_or 	= static::process_check_args( Abstract_common::LOG_OR, $group );
			// $prop 		= $prop_or;
		// }

		$prop 			= $prop_and;

		$obs            = static::get_observable_instance();
		$ev				= static::get_event_dispatcher_instance();
		$auto_arr 		= ( is_array( $value ) AND $check_arr );

		$propScene 		= $this->clearScenario( $prop_and, $prop_or, $prop );

		$prop 			= $propScene['prop'];
		$prop_and 		= $propScene['prop_and'];
		$prop_or 		= $propScene['prop_or'];

		$and_search 	= array();
		$or_search 		= array();
		
		if( !EMPTY( $prop['scenarios'] ) 
			OR !EMPTY( $prop_or['scenarios'] )
		)
		{
			if( ISSET( $prop['scenarios'] ) )
			{
				$and_search = $this->array_search_recursive( $field, $prop['scenarios'] );
			}

			if( ISSET( $prop_or['scenarios'] ) )
			{
				$or_search 	= $this->array_search_recursive( $field, $prop_or['scenarios'] );
			}
			
			if( !EMPTY( $and_search ) OR !EMPTY( $or_search ) )
			{
				return;
			}
		}

		$extra_args 	= array();
		$check_logic 		= array(
			Abstract_common::LOG_AND 	=> array(
				'passed' 	=> array(),
				'pass_arr' 	=> array(),
				'arr_keys' 	=> array()
			),
			Abstract_common::LOG_OR		=> array(
				'passed' 	=> array(),
				'pass_arr' 	=> array(),
				'arr_keys' 	=> array()
			)
		);

		$real_value_before_filter = NULL;

		if( !EMPTY( $prop['filters'] ) )
		{
			$real_value_before_filter = $value;

			static::handle_filter( $prop['filters'], $real_value_before_filter, $field, $prop['filter_satis'], $prop['pre_filters'], $check_arr );

			$filt_value 		= static::pre_filter_value( $field );

			$value  			= ( ISSET( $filt_value ) AND !EMPTY( $filt_value ) ) ? $filt_value : $value;
		}

		if( EMPTY( $origValue ) )
		{
			$origValue 			= (!is_null($real_value_before_filter)) ? $real_value_before_filter : $value;
		}

		if( !EMPTY( $origValue ) AND !EMPTY( $prop['filters'] ) )
		{
			$origValue 			= $this->processFilterOrigValue( $prop['filters'], $origValue, $field, $prop['filter_satis'], $prop['pre_filters'], $check_arr );
		}

		if( $auto_arr )
		{
			$value 				= $this->flattened_array( $value );
		}

		$obs->attach_observer( 'endgiven', array( $this, 'endCondition' ) );
		
		if( !EMPTY( $prop_and['rules'] ) )
		{   
			if( $auto_arr )
			{
				foreach( $value as $k_value => $v_value ) 
				{
					$check_logic[ Abstract_common::LOG_AND ][] =  $this->_process_and_or_check( $prop, $field, $field_arr, $v_value, $auto_arr, $extra_args, $group, $logic, $k_value, $origValue );
				}

				foreach( $check_logic[ Abstract_common::LOG_AND ] as $k_and => $and )
				{
					if( !EMPTY( $and['passed'] ) )
					{
						foreach( $and['passed'] as $pass )
						{
							$check_logic[ Abstract_common::LOG_AND ][ 'passed' ][] 	= $pass;
						}
					}
					
					if( !EMPTY( $and['pass_arr'] ) )
					{
						foreach( $and['pass_arr'] as $rule => $pass_arr )
						{  
							$check_logic[ Abstract_common::LOG_AND ][ 'pass_arr' ][ $k_and ][ $rule ] 	= $pass_arr;
						}
					}

					if( ISSET( $and['passed'] ) )
					{
						$check_logic[ Abstract_common::LOG_AND ][ 'arr_keys' ][ $k_and ] 				= $and['passed'];
					}

					unset( $check_logic[ Abstract_common::LOG_AND ][ $k_and ] );

				}
				
			}
			else 
			{
				$check_logic[ Abstract_common::LOG_AND ] 		=  $this->_process_and_or_check( $prop, $field, $field_arr, $value, $auto_arr, $extra_args, $group, $logic, NULL, $origValue );
			}
			
		}
		
		if( ( ISSET( $prop_or['rules'] ) AND !EMPTY( $prop_or['rules'] ) ) AND
			( EMPTY( $check_logic[ Abstract_common::LOG_AND ]['passed'] ) OR in_array( 0, $check_logic[ Abstract_common::LOG_AND ]['passed'] ) )
		  )
		{
			if( $auto_arr )
			{
				foreach( $value as $k_value => $v_value )
				{ 
					$check_logic[ Abstract_common::LOG_OR ][] 	= $this->_process_and_or_check( $prop_or, $field, $field_arr, $v_value, $auto_arr, $extra_args, $group, $logic, $k_value, $origValue );				
				}

				foreach( $check_logic[ Abstract_common::LOG_OR ] as $k_or => $or )
				{   
					if( !EMPTY( $or['passed'] ) )
					{
						foreach( $or['passed'] as $pass )
						{
							$check_logic[ Abstract_common::LOG_OR ][ 'passed' ][] 	= $pass;

						}
					}
					
					if( !EMPTY( $or['pass_arr'] ) )
					{
						foreach( $or['pass_arr'] as $rule => $pass_arr )
						{
							$check_logic[ Abstract_common::LOG_OR ][ 'pass_arr' ][ $rule ]    = $pass_arr;
						}
					}

					if( ISSET( $or['passed'] ) )
					{
						if( !EMPTY( $check_logic[ Abstract_common::LOG_AND ][ 'arr_keys' ] ) AND !EMPTY( $or['passed'] ) )
						{
							if( !in_array( 0, $check_logic[ Abstract_common::LOG_AND ][ 'arr_keys' ][ $k_or ] ) OR !in_array( 0, $or['passed'] ) )
							{
								if( !EMPTY( static::$ajd_prop['message'][ $field ] ) )
								{
									foreach( static::$ajd_prop['message'][ $field ] as $rule => $message )
									{
										unset( static::$ajd_prop['message'][ $field ][ $rule ][ $k_or ] );
										
										if( EMPTY( static::$ajd_prop['message'][ $field ][ $rule ] ) )
										{ 
											unset( static::$ajd_prop['message'][ $field ][ $rule ] );
										}
									}
								}
							}
						}
					}

					unset( $check_logic[ Abstract_common::LOG_OR ][ $k_or ] );

				}

				if( EMPTY( static::$ajd_prop['message'][ $field ] ) )
				{
					unset( static::$ajd_prop['message'][ $field ] );
				}
				
			}
			else 
			{
				$check_logic[ Abstract_common::LOG_OR ] 		= $this->_process_and_or_check( $prop_or, $field, $field_arr, $value, $auto_arr, $extra_args, $group, $logic, NULL, $origValue );		
			}
		}

		if( !$auto_arr )
		{
			if( in_array( 1, $check_logic[ Abstract_common::LOG_OR ]['passed'] ) )
			{
				unset( static::$ajd_prop['result'][ $field ] );
				unset( static::$ajd_prop['message'][ $field ] );
			}
		}

		if( EMPTY( $group ) AND !$dontReset )
		{
			$obs->attach_observer( $field.'-|passed', $ev, array( $this ) );
			$obs->attach_observer( $field.'-|fails', $ev, array( $this ) );

			$obs->attach_observer( $field.'-|customEvent', array( $ev, 'customEvent' ), array( $obs, $this, $field ) );

			$obs->notify_observer($field.'-|customEvent');
			
			if( !$this->validation_fails( $field ) ) 
			{
				$obs->notify_observer( $field.'-|passed' );
			}

			if( $this->validation_fails( $field ) ) 
			{
				$obs->notify_observer( $field.'-|fails' );
			}
			
		}
		
		if( !EMPTY( $group ) )
		{
			return $check_logic;
		}
		else 
		{
			if( !$dontReset )
			{
				$this->reset_all_validation_prop();
			}

			return $ev;
		}
	}


	protected function processFilterOrigValue( $filters, $origValue, $field, $filterSatis, $preFilters, $check_arr )
	{
		if( is_array( $origValue ) )
		{
			$newArr 	= array();

			foreach( $origValue as $key => $val )
			{
				if( is_array( $val ) )
				{
					$newDetail 		= $this->processFilterOrigValue($filters, $val, $field, $filterSatis, $preFilters, $check_arr);

					$newArr[$key] 	= $newDetail;
				}
				else
				{
					$newVal 		= static::handle_filter( $filters, $val, $field, $filterSatis, $preFilters, $check_arr, TRUE );
						
					$newArr[$key] 	= $newVal;
				}
			}

			return $newArr;
		}
		else
		{
			$newVal 				= static::handle_filter( $filters, $origValue, $field, $filterSatis, $preFilters, $check_arr, TRUE );

			return $newVal;
		}
	}

	protected function clearScenario( array $prop_and, array $prop_or = array(), array $prop = array() ) 
	{
		if( !EMPTY( static::$remove_scenario ) )
		{
			static::$remove_scenario 		= array_unique( static::$remove_scenario );
			
			foreach( static::$remove_scenario as $scene )
			{	
				if( !EMPTY( $prop ) )
				{
					if( ISSET( $prop['scenarios'][$scene] ) )
					{
						unset( $prop['scenarios'][$scene] );
					}
				}

				if( ISSET( $prop_and['scenarios'][$scene] ) )
				{
					unset( $prop_and['scenarios'][$scene] );
				}

				if( !EMPTY( $prop_or ) )
				{
					if( ISSET( $prop_or['scenarios'][$scene] ) )
					{
						unset( $prop_or['scenarios'][$scene] );
					}
				}
			}
		}

		return array(
			'prop'		=> $prop,
			'prop_and'	=> $prop_and,
			'prop_or'	=> $prop_or
		);	
	}

	private function _process_and_or_check( $prop, $field, $field_arr, $value, $auto_arr, $extra_args, $group, $logic, $key = NULL, $origValue = NULL )
	{	
		$check_arr 			= array();
		$or_pass_arr 		= array();
		$countErr 			= 0;
		
		foreach( $prop['rules'] as $rule_key => $rule_value )
		{
			if( !EMPTY( $prop['scenarios'] ) )
			{
				$check_scena 		= $this->array_search_recursive( $rule_value, $prop['scenarios'] );

				if( !EMPTY( $check_scena ) ) continue;
			}

			$pass_arr 		= array();

			$satisfier 		= $prop['satisfier'][ $rule_key ];
			$details 		= $prop['details'][ $rule_key ];
			$sometimes 		= $prop['sometimes'][ $rule_value ];

			$pass_arr['rule'] 			= $rule_value;
			$pass_arr['satisfier'] 		= $satisfier;
			$pass_arr['field'] 			= $field;
			$pass_arr['details'] 		= $details;
			$pass_arr['value'] 			= $value;
			$pass_arr['cus_err'] 		= ( ISSET( $prop['cus_err'] ) ) ? $prop['cus_err'] : array();
			$pass_arr['clean_field']	= $field_arr['clean'];
			$pass_arr['orig_field'] 	= $field_arr['orig'];
			$pass_arr['logic'] 			= $prop['logic'];
			$pass_arr['field_logic'] 	= $logic;
			$pass_arr['origValue'] 		= $origValue;

			$or_pass_arr[$rule_key]['rule'] 		= $rule_value;
			$or_pass_arr[$rule_key]['satisfier'] 	= $satisfier;
			$or_pass_arr[$rule_key]['field'] 		= $field;
			$or_pass_arr[$rule_key]['details'] 		= $details;
			$or_pass_arr[$rule_key]['value'] 		= $value;
			$or_pass_arr[$rule_key]['cus_err'] 		= ( ISSET( $prop['cus_err'] ) ) ? $prop['cus_err'] : array();
			$or_pass_arr[$rule_key]['clean_field'] 	= $field_arr['clean'];
			$or_pass_arr[$rule_key]['orig_field'] 	= $field_arr['orig'];
			$or_pass_arr[$rule_key]['logic'] 		= $prop['logic'];
			$or_pass_arr[$rule_key]['field_logic'] 	= $logic;
 			
			if( is_callable( $sometimes ) )
			{
				$sometimes 				= $this->invoke_func( $sometimes, array( $pass_arr['value'], $pass_arr['satisfier'], $pass_arr['orig_field'], $pass_arr['origValue'] ) );

			}
			else if( $sometimes == Abstract_common::SOMETIMES 
				OR $sometimes === TRUE
			)
			{
				$sometimes 				= !EMPTY( $pass_arr['value'] );
			}
			else 
			{
				$sometimes 				= TRUE;
			}

			$pass_arr['sometimes'] 					= $sometimes;
			$or_pass_arr[$rule_key]['sometimes'] 	= $sometimes;

			$check 						= $this->_process_validate( $pass_arr, $auto_arr, $extra_args, $key, $countErr );

			if(!is_null($check))
			{
				if(  !$check['passed'][0] )
				{
					$countErr++;
				}
				
				$check_arr['passed'][] 					= $check['passed'][0];
				
				$check_arr['pass_arr'][ $rule_value ] 	= $check['pass_arr'];

				$or_pass_arr[$rule_key]['pass_arr'] 	= $check_arr['pass_arr'];
			}

		}
		
		if( $prop['logic'] == Abstract_common::LOG_OR )
		{
			if( ISSET( $check_arr['pass_arr'][$rule_value][2] ) )
			{
				$pass_arr['cus_err'] = $check_arr['pass_arr'][$rule_value][2][$rule_value];
				$prop['cus_err'] 	 = $check_arr['pass_arr'][$rule_value][2][$rule_value];
			}
		
			if( !in_array( 1, $check_arr['passed'] ) )
			{
				// $pass_arr['rule'] 		= current( $prop['rules'] );

				foreach( $or_pass_arr as $rule_key => $or_pass )
				{
					$or_pass['cus_err']			= $or_pass['pass_arr'][$or_pass['rule']][2][$or_pass['rule']];
					$or_pass['append_error']	= $or_pass['pass_arr'][$or_pass['rule']][3][$or_pass['rule']];
					
					$this->handle_errors( $or_pass, $auto_arr, $key );
				}
			}
		}

		return $check_arr;
	}

	public static function validateMetada( $object, $assert = FALSE )
	{
		return static::getMetadata()->validateMetada( $object, $assert );
	}


	public static function pre_filter_value( $key = NULL )
	{
		$filter 	= static::get_filter_ins();

		return $filter->get_pre_filter_value( $key );
	}

	public static function filter_value( $key = NULL )
	{
		$filter 	= static::get_filter_ins();

		return $filter->get_filtered_value( $key );
	}

	public static function get_error( $rule )
	{
		$err 		= static::get_errors_instance();

		return $err->get_error( $rule );
	}

	public static function errors()
	{
		$err 		= static::get_errors_instance();

		return $err->set_validation_errors( static::$ajd_prop['message'] );
	}

	public static function toStringErr( $msg = array() )
	{
		$err 		= static::get_errors_instance();

		return $err->toStringErr( $msg );
	}

	public static function setLang( $lang )
	{
		static::$lang 	= $lang;
		Errors::$lang 	= $lang;
	}

	public static function validation_fails( $key = NULL, $err_key = NULL, $when = FALSE )
	{
		$count_var	= ( $when ) ? static::$ajd_prop['result'] : static::$ajd_prop['message'];

		$check 		= COUNT( $count_var ) ? TRUE : FALSE;

		if( !EMPTY( $key ) ) 
		{
			if( ISSET( $count_var[ $key ] ) )
			{
				if( $count_var[ $key ] instanceof Countable )
				{
					$check 	= ( !EMPTY( $count_var[ $key ] ) AND COUNT( $count_var[ $key ] ) ) ? TRUE : FALSE;
				}
				else
				{
					$check 	= ( ISSET( $count_var[ $key ] ) AND !EMPTY( $count_var[ $key ] ) ) ? TRUE : FALSE;
				}
			}
			else
			{
				/*if( $when OR $arrCheck )
				{*/
					$check 	= FALSE;
				// }
			}

			if( !IS_NULL( $err_key ) ) 
			{

				$check = ( !EMPTY( $count_var[ $key ][ $err_key ] ) AND COUNT( $count_var[ $key ][ $err_key ] ) ) ? TRUE : FALSE;

			}

		}

		return $check;

	}

	public static function db( $connection = NULL, $user = NULL, $pass = NULL, $options = array() )
	{
		return new Database( $connection, $user, $pass, $options );
	}

	public static function expression( $expr, $expr_value )
	{
		$ajds 	= static::get_ajd_instance();

		return new Expression( $expr, $expr_value, $ajds );
	}

	public function accessInitExtensions()
	{
		if( !static::$ajd_prop['extensions_initialize'] )
		{
			static::init_extensions();
		}
	}

	public function getExtensionLogics()
	{
		return static::$ajd_prop['extension_test'];
	}

	protected static function init_extensions()
	{ 
		if( static::$ajd_prop['extensions_initialize'] 
			OR EMPTY( static::$ajd_prop['extensions'] ) 
		  )
		{
			return;
		}

		static::$ajd_prop['extensions_initialize'] 	= TRUE;
		static::$ajd_prop['extension_rule'] 		= array();
		static::$ajd_prop['extension_filter'] 		= array();
		static::$ajd_prop['extension_test'] 		= array();

		foreach( static::$ajd_prop['extensions'] as $name => $extension )
		{
			static::init_extension( $extension, $name );
		}
	}

	protected static function init_extension( $extension, $name )
	{
		foreach( $extension->getRules() as $rule )
		{
			static::$ajd_prop['extension_rule'][ $rule ] 		= array( 'rule' => $rule, 'extension_name' => $name );
		}

		foreach( $extension->getRuleMessages() as $rule => $message )
		{
			static::add_rule_msg( $rule, $message );
		}

		foreach( $extension->getFilters() as $filter )
		{
			static::$ajd_prop['extension_filter'][ $filter ] 	= array( 'filter' => $filter, 'extension_name' => $name, 'extension_obj' => $extension );
		}

		foreach( $extension->getLogics() as $test )
		{
			static::$ajd_prop['extension_test'][ $test ] 	= array( 'test' => $test, 'extension_name' => $name, 'extension_obj' => $extension );
		}

		foreach( $extension->getMiddleWares() as $name => $func )
		{	
			static::$middleware[ $name ][ 'func' ] 				= $func;
		}
	}

	protected static function handle_filter( $filter, $value, $field, $satisfier, $pre_filter, $check_arr, $val_only = FALSE )
	{
		$filter_ins 						= static::get_filter_ins();
		$ajd  								= static::get_ajd_instance();

		$extension_filter 					= static::$ajd_prop['extension_filter'];

		$filter 							= ( $ajd->isset_empty( $filter ) ) ? $filter : NULL;
		$satisfier 							= ( $ajd->isset_empty( $satisfier ) ) ? $satisfier : NULL;
		$pre_filter 						= ( $ajd->isset_empty( $pre_filter ) ) ? $pre_filter : array();

		$filter_ins->set_filter( $filter, $value, $field, $satisfier, $pre_filter, $extension_filter );

		$real_val 	= $filter_ins->filter( $check_arr, $val_only );
		
		if( $val_only )
		{
			return $real_val;
		}
	}

	protected static function process_method_name( $name )
	{
		$ret_name 		= $name;

		if( ISSET( static::$macros[ $name ] ) )
		{
			$method 	= 'macro';
		}
		else if( preg_match( '/^S/', $name ) )
		{
			$method 	= 'superRule';
			$ret_name 	= static::removeWord( $name, '/^S/' );
		}
		else if( preg_match( '/^F/', $name ) )
		{
			$method 	= 'addFilter';
			$ret_name 	= static::removeWord( $name, '/^F/' );
		}
		else if( preg_match( '/^eS/' , $name ) )
		{
			$method 	= 'endSuperRule';
			$ret_name 	= static::removeWord( $name, '/^eS/' );
		}
		else if( preg_match('/^oR/', $name ) )
		{
			$method 	= 'addOrRule';
			$ret_name 	= static::removeWord( $name, '/^oR/' );	
		}
		else if( preg_match('/^Not/', $name ) )
		{
			$method 	= 'addRule';

			$ret_name 	= static::removeWord( $name, '/^Not/' );

			$ret_name 	= '!'.$ret_name;
		}
		else 
		{
			$method 	= 'addRule';
		}

		return array(

			'method' 	=> $method,
			'name' 		=> $ret_name
		);
	}

	protected static function process_check_args( $logic, $group )
	{
		$ret_args 			= array();
		$key_arr  			= static::get_ajd_and_or_prop();
	
		if( static::$ajd_prop['check_group'] )
		{  
			foreach ( $key_arr as $prop ) 
			{
				if( ISSET( $group[ $logic ][ $prop ] ) )
				{
					$ret_args[ $prop ]	= $group[ $logic ][ $prop ];
				}
			}

			if( !EMPTY( $ret_args ) )
			{
				$ret_args['logic']	= $logic;
			}
		}
		else
		{
			foreach ( $key_arr as $prop ) 
			{
				if( !EMPTY( static::$useContraintGroup ) )
				{
					if( ISSET( static::$ajd_prop[static::$useContraintGroup][ $logic ][ $prop ] ) )
					{
						$ret_args[ $prop ]	= static::$ajd_prop[static::$useContraintGroup][ $logic ][ $prop ];
					}
				}
				else
				{
					$ret_args[ $prop ]	= static::$ajd_prop[ $logic ][ $prop ];
				}
			}

			if( ISSET( static::$ajd_prop[static::$useContraintGroup]['events'] ) AND !EMPTY( static::$ajd_prop[static::$useContraintGroup]['events'] ) )
			{
				static::$ajd_prop['events']	= static::$ajd_prop[static::$useContraintGroup]['events'];
			}

			if( !EMPTY( $ret_args ) )
			{
				$ret_args['logic']	= $logic;
			}
		}

		return $ret_args;
	}

	protected static function get_ajd_and_or_prop()
	{
		return array( 'rules', 'details', 'satisfier', 'cus_err', 'filters', 'filter_satis', 'pre_filters', 'scenarios', 'sometimes' );
	}

	protected function reset_validation_prop( $key, $sub_key = NULL )
	{
		$and_or 			= array( Abstract_common::LOG_AND, Abstract_common::LOG_OR );	
		$and_or_arr 		= static::get_ajd_and_or_prop();

		if( in_array( $key, $and_or ) )
		{
			foreach ( $and_or_arr as $prop ) 
			{
				static::$ajd_prop[ $key ][ $prop ] 	= array();
			}
		}
		else 
		{
			if( is_array( static::$ajd_prop[ $key ] ) )
				static::$ajd_prop[ $key ] 			= array();
			else if( in_array( static::$ajd_prop[ $key ], $and_or ) )
				static::$ajd_prop[ $key ] 			= Abstract_common::LOG_AND;
			else 
				static::$ajd_prop[ $key ] 			= NULL;
		}

	}

	public function resetMessage()
	{
		static::$ajd_prop['message'] = array();
	}

	protected function reset_all_validation_prop()
	{
		$properties 	= array(
			'fields', 'current_field', 'and_or_stack', 'given_values'
		);

		$and_or 		= static::get_ajd_and_or_prop();

		foreach( $properties as $prop )
		{
			if( is_array( static::$ajd_prop[ $prop ] ) )
			 	static::$ajd_prop[ $prop ] 	= array();
			else 
				static::$ajd_prop[ $prop ] 	= NULL;	
		}

		foreach( $and_or as $prop )
		{
			static::$ajd_prop[ Abstract_common::LOG_AND ][ $prop ] 	= array();
			static::$ajd_prop[ Abstract_common::LOG_OR ][ $prop ] 	= array();
		}

		$this->reset_validation_prop( 'events' );
		$this->reset_validation_prop( 'current_logic' );
		$this->resetConstraintGroup();
		$this->resetBail();

		$filter_ins = static::get_filter_ins();
	}

	protected function resetConstraintGroup()
	{
		static::$constraintStorageName 		= NULL;
		static::$useContraintGroup 			= NULL;
	}

	protected function resetBail()
	{
		static::$bail 						= FALSE;
	}

	protected function reset_check_group()
	{
		static::$ajd_prop[ 'check_group' ]	= FALSE;
	}

	protected function reset_current_field()
	{
		static::$ajd_prop[ 'current_field' ] 				= NULL;
	}

	protected function invoke_func( $func, $args = array() )
	{
		return $this->invoke_function( $func, $args );
	}

	protected function get_rules_path()
	{
		$this->rules_path 		= dirname( __FILE__ ).Abstract_common::DS.'Rules'.Abstract_common::DS;

		return $this->rules_path;
	}

	protected static function get_errors_instance( $lang = NULL ) 
	{
		return parent::get_errors_instance( static::$lang );
	}

	protected function handle_errors( $details, $check_arr, $key = NULL )
	{
		$cus_err 				= $details['cus_err'];
		$append_err 			= ( ISSET( $details['append_error'] ) ) ? $details['append_error'] : array();

		$err 					= static::get_errors_instance();
		$errors 				= $err->get_errors();

		$called_class 			= ( ISSET( $details['details'][1] ) ) ? $details['details'][1] : NULL;
		$rule_instance 			= static::$cache_instance;

		$inverse 				= $details['details'][0];

		$errors 				= $err->processExceptions( $details['rule'], $called_class, $rule_instance, $details['satisfier'], $details['value'], $inverse, $errors );

		$errors 				= $this->format_errors( $details['rule'], $details['details'][1], $details['clean_field'], $details['value'], $details['satisfier'], $errors['errors'], $cus_err, $check_arr, $err, $key, $append_err, $inverse );
		
		$this->append_error_msg( $errors, $details['orig_field'], $details['clean_field'], $details['rule'], $check_arr, $key );	
	}

	protected function append_error_msg( $errors, $field = NULL, $clean_field = NULL, $rules_name = NULL, $check_arr = FALSE, $key = NULL )
	{
		$valArr 	= array(
			'errors'		=> $errors,
			'clean_field'	=> $clean_field
		);

		if( ISSET( $field ) ) 
		{
			if( ISSET( $rules_name ) ) 
			{
				if( $check_arr ) 
				{
					static::$ajd_prop['message'][ $field ][ $rules_name ][ $key ][]	= $valArr;
				} 
				else 
				{
					static::$ajd_prop['message'][ $field ][ $rules_name ][] 		= $valArr;
				}
			} 
			else 
			{
				static::$ajd_prop['message'][ $field ][] 					= $valArr;
			}
		} 
		else 
		{
			static::$ajd_prop['message'][] 									= $valArr;
		}
	}

	private function _runEvents(array $events, $value, $field, $checkForField = false)
	{
		try
		{
			$ob 					= static::get_observable_instance();

			$args 					= array($value, $field);
			// print_r($args);
			if( !EMPTY( $events ) )
			{
				foreach( $events as $event )
				{
					if($checkForField)
					{
						$eventArr = explode('-|', $event);
						$checkField = null;

						if(isset($eventArr[1]))
						{
							$checkField = $eventArr[0];
						}

						if(!empty($checkField))
						{
							if($checkField == $field)
							{
								$ob->notify_observer($event, $args);	
							}
						}
						else
						{
							$ob->notify_observer($event, $args);	
						}
					}
					else
					{
						$ob->notify_observer($event, $args);	
					}
					
				}
			}
		}
		catch(\Exception $e)
		{
			throw $e;
		}
	}

	private function _process_validate( $details, $check_arr, $extra_args, $key = NULL, $countErr = 0 )
	{
		$ob 					= static::get_observable_instance();

		$passed 				= TRUE;

		$extra_args['pass_arr'] = array();

		$real_val 					= $details['value'];

		$details['append_error'][ $details['rule'] ]	= '';

		// static $countErr 			= 0;
		
		if( ISSET( static::$ajd_prop['events'][Abstract_common::EV_LOAD][$details['rule']] ) )
		{
			$eventLoad 	= static::$ajd_prop['events'][Abstract_common::EV_LOAD][$details['rule']];
			unset(static::$ajd_prop['events'][Abstract_common::EV_LOAD][$details['rule']]);
			$this->_runEvents($eventLoad, $details['value'], $details['orig_field'], TRUE);
		}

		if( $this->isset_empty( $details['details'], 2 ) )
		{ 
			if( $details['sometimes'] )
			{
				if( ISSET( $details['satisfier'][0] ) AND !EMPTY( $details['satisfier'][0] ) 
					AND is_callable( $details['satisfier'][0] )
					AND !$details['satisfier'][0] instanceof Validator
					AND !in_array( $details['rule'], static::$callbackRules )
				)
				{
					$ajd_ins 			= static::get_ajd_instance();
					$error_ins 			= static::get_errors_instance();

					$over_satis_arg 	= array( $details['value'], $details['satisfier'], $details['field'], $details['origValue'], $ajd_ins, $error_ins, $key );

					$closure  			= call_user_func_array( $details['satisfier'][0], $over_satis_arg);
					
					static::$cache_instance[$details['details'][1]] 	= $details['satisfier'][0];

					$pass_check 		= $closure;
				}
				else
				{
					$pass_check 		= $this->{ $details['details'][2] }( $details );	
				}
				
				if( !is_array( $pass_check ) )
				{
					$passed 		= $pass_check;
				}
				else
				{
					$passed 		= $pass_check['check'];

					if( ISSET( $pass_check['msg'] ) AND !EMPTY( $pass_check['msg'] ) 
						AND !ISSET( $details['cus_err'][ $details['rule'] ] )
					)
					{
						$details['cus_err'][ $details['rule'] ] 	= $pass_check['msg'];
					}

					if( ISSET( $pass_check['append_error'] ) AND !EMPTY( $pass_check['append_error'] ) )
					{
						$details['append_error'][ $details['rule'] ] = $pass_check['append_error'];
					}

					if( ISSET( $pass_check['val'] ) )
					{
						$real_val 	= $pass_check['val'];
					}
				}
				
				if( ISSET( $this->customMesage[ $details['orig_field'] ] ) )
				{	
					$customMessage 	= $this->customMesage[ $details['orig_field'] ];

					if( is_array( $customMessage['message'] ) )
					{
						if( ISSET( $customMessage['message'][ $details['rule'] ] ) )
						{
							$details['cus_err'][ $details['rule'] ] = $customMessage['message'][ $details['rule'] ];
						}
					}
					else
					{
						$details['cus_err'][ $details['rule'] ] 	= $customMessage['message'];
					}
				}
			}
		}

		if( $this->isset_empty( $details['details'], 0 ) )
		{
			$passed 			= !$passed;
		}

		// $ob->attach_observer( 'endgiven', array( $this, 'end_condition' ) );
		
		if( !$passed )
		{  
			if( ISSET( static::$ajd_prop['events'][Abstract_common::EV_FAILS][$details['rule']] ) )
			{
				$eventFails 	= static::$ajd_prop['events'][Abstract_common::EV_FAILS][$details['rule']];

				unset(static::$ajd_prop['events'][Abstract_common::EV_FAILS][$details['rule']]);
				
				$this->_runEvents($eventFails, $details['value'], $details['orig_field'], TRUE);
			}

			if( static::$bail )
			{
				if( $countErr != 0 )
				{
					return;
				}
			}
			
			static::$ajd_prop['result'][ $details['field'] ] 	= $details['details'][1];
			
			if( $details['logic'] == Abstract_common::LOG_AND AND $details['field_logic'] == Abstract_common::LOG_AND )
			{
				if( $this->check_cond ) 
				{
					$this->handle_errors( $details, $check_arr, $key );

					if( ISSET( static::$cache_instance[ $details['details'][1] ] ) )
					{
						if( static::$cache_instance[ $details['details'][1] ] instanceof \Closure )
						{
							unset( static::$cache_instance[ $details['details'][1] ] );
						}
					}
				}
			}
			else 
			{
				$extra_args['pass_arr'] 	= array( $details['rule'], $details['satisfier'],
												array(
													$details['rule'] => $details['cus_err']
												),
												array(
													$details['rule'] => $details['append_error']
												)
											 );

			}

		}
		else
		{
			if( !EMPTY( $key ) )
			{
				if( !EMPTY($details['field']) 
					AND
					!EMPTY( static::$ajd_prop['result_values'][ $details['field'] ] )
				)
				{

					if(isset(static::$ajd_prop['result_values'][ $details['field'] ][ $key ]))
					{
					
						static::$ajd_prop['result_values'][ $details['field'] ][ $key ]  	= $real_val;
					}
				}
			}
			else
			{
				static::$ajd_prop['result_values'][ $details['field'] ]  			= $real_val;
			}
		
			if( ISSET( static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$details['rule']] ) )
			{
				$eventSuccess 	= static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$details['rule']];

				unset(static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$details['rule']]);

				$this->_runEvents($eventSuccess, $details['value'], $details['orig_field'], TRUE);
			}
		}

		$extra_args['passed'][] 			= $passed;
		
		if( $details['logic'] == Abstract_common::LOG_OR AND $passed )
		{
			$extra_args['pass_arr'] 		= array( 1, $details['satisfier'],
												array(
													$details['rule'] => $details['cus_err']
												),
												array(
													$details['rule'] => $details['append_error']
												)
											 );
		}

		$extra_args['pass_arr']['values'][$details['rule']] 	= $details['value'];
	
		return $extra_args;
	}

	public static function get_values()
	{
		return static::$ajd_prop['result_values'];
	}

	public function when()
	{
		static::$ajd_prop['result'] = array();

		$ob = static::get_observable_instance();

		$ob->attach_observer( 'ongiven', array( $this, 'checkCondition' ) );

		$when 						= new When( $this, $ob );

		return $when;
	}

	protected function checkCondition()
	{
		$this->check_cond 	= FALSE;
	}

	protected function endCondition()
	{
		$this->check_cond 	= TRUE;
	}

	private function _process_extension( $details )
	{	
		$extension_rule 		= static::$ajd_prop[ 'extension_rule' ][ $details['details'][4] ];

		$extension_obj 			= static::$ajd_prop[ 'extensions' ][ $extension_rule[ 'extension_name' ] ];
		$origValue 				= ( ISSET( $details['origValue'] ) ) ? $details['origValue'] : NULL;

		$args 					= array(
			$extension_rule['rule'], $details['value'], $details['satisfier'], $details['field'], $origValue
		);

		$args 					= array_merge( $args, static::$globalVar );

		$extension_result 		= call_user_func_array(array( $extension_obj, 'runRules' ), $args );

		return $extension_result;
	}

	private function _process_class( $details )
	{
		$append_rule 			= $details['details'][3]['raw_class'];
		$rule_details 			= $details['details'];
		$from_framework 		= $details['details'][3]['from_framework'];	
		$origValue 				= ( ISSET( $details['origValue'] ) ) ? $details['origValue'] : NULL;	

		if( ISSET( static::$cache_instance[ $append_rule ] ) AND static::$cache_instance[ $append_rule ] instanceof \Closure )
		{
			unset( static::$cache_instance[ $append_rule ] );
		}
		
		/*if( !ISSET( static::$cache_instance[ $append_rule ] ) )
		{*/
			$class_factory 		= static::get_factory_instance()->get_instance( TRUE );

			if( $this->isset_null( $rule_details[3], 'namespace' ) )
			{
				static::addRuleNamespace( $rule_details[3]['namespace'] );
				// $class_factory->set_rules_namespace( array( $rule_details[3]['namespace'] ) );
			}

			if( !EMPTY( static::$addRuleNamespace ) )
			{
				$this->_appendRuleNameSpace( $class_factory );
			}
			
			$class_args 		= $details[ 'details' ][3][ 'class_args' ];
			
			$rule_obj 			= $class_factory->rules( $rule_details[3]['rules_path'], $append_rule, $class_args, FALSE, static::$globalVar );
		/*}
		else 
		{
			$rule_obj 			= static::$cache_instance[ $append_rule ];
		}*/
		
		static::$cache_instance[ $append_rule ] 	= $rule_obj;
		static::$cacheByFieldInstance[$details['orig_field']][$append_rule] = $rule_obj;

		if($rule_obj instanceof Invokable_rule_interface)
		{
			return $rule_obj( $details['value'], $details['satisfier'], $details['field'], $details['clean_field'], $origValue );
		}
		else
		{
			return $rule_obj->{ $details[ 'details' ][3][ 'class_meth_call' ] }( $details['value'], $details['satisfier'], $details['field'], $details['clean_field'], $origValue );
		}
	}

	private function _appendRuleNameSpace( $classFactory )
	{
		foreach( static::$addRuleNamespace as $ruleNamespace )
		{
			$classFactory->append_rules_namespace( $ruleNamespace );
		}
	}

	private function _process_method( $details )
	{
		$method_args    = array();

		$method_factory = static::get_factory_instance()->get_instance( FALSE, FALSE, TRUE );

		$from_framework = $details['details'][3]['from_framework'];

		$rule 			= !EMPTY( $details['details'][3]['meth_override'] ) ? $details['rule'] : $details['details'][4];
		$orig_rule 		= $rule;

		$args  			= $this->_process_method_args_for_framework( $from_framework, $details );
		$method_args    = $args;

		if( $from_framework == Abstract_common::SYMFONY )
		{
			$rule 		= 'validate';
		}
		else if( $from_framework == Abstract_common::RESPECT )
		{
			$method_args    	= array();
			$method_args[]  	= $orig_rule;

			if( !EMPTY( $details['satisfier'] ) )
			{
				$method_args[]  = ( is_array( $details['satisfier'] ) ) ? $details['satisfier'] : array( $details['satisfier'] );
			}
			else 
			{
				$method_args[]  = $details['details'][3]['symfony_args'];
			}

			$method_args 		= array_merge( $method_args, static::$globalVar );

			$rule 		= '__call';
		}
		
		$method 		= $method_factory->rules( $details['details'][3]['class_name'], $rule );
		
		$passed 		= $method_factory->process_method( $method_args, $details['details'][3]['obj_ins'], TRUE );

		if( $from_framework == Abstract_common::SYMFONY )
		{
			$passed 	= EMPTY( COUNT( $passed ) ) ? TRUE : FALSE;
		}
		else if( $from_framework == Abstract_common::RESPECT )
		{	
			$passed     = $passed->validate( $args[0] );
		}

		return $passed;

	}

	private function _process_function( $details )
	{
		$passed 			= FALSE;

		$funct_factory 		= static::get_factory_instance()->get_instance( FALSE, TRUE );

		if( $funct_factory->func_valid( $details['rule'] ) )
		{
			$inc_field 		= FALSE;

			if( $this->isset_empty( $details['details'][3], 'func' ) )
			{
				$inc_field 	= TRUE;
			}
			
			$func 			= $funct_factory->rules( $details['rule'], $details['details'][3] );

			$passed 		= $funct_factory->process_function( $details['field'], $details['value'], $details['satisfier'], FALSE, $inc_field );
			
		}

		return $passed;

	}

	private function _process_method_args_for_framework( $from_framework, $details )
	{
		if( !EMPTY( $from_framework ) )
		{
			$args 	= array(
				Abstract_common::CODEIGNITER => array(
					$details['value'], $details['satisfier'], $details['field']
				),
				Abstract_common::LARAVEL 	  => array(
					$details['field'], $details['value'], $details['satisfier']
				),
				Abstract_common::RESPECT => array(
					$details['value'], $details['satisfier'], $details['field']
				),
				Abstract_common::SYMFONY => array(
					$details['value'], $details['details'][3]['symfony_args']
				)
			);
			
			return $args[ $from_framework ];
		}
		else 
		{
			$origValue 	= ( ISSET( $details['origValue'] ) ) ? $details['origValue'] : NULL;	

			$args 	= array(
				$details['value'], $details['satisfier'], $details['field'], $origValue
			);

			return $args;
		}
		
	}

	private function _process_rule_kind( $rule, $append_rule, $raw_rule, $satis )
	{
		$args 	 		= array();
		$lower_rule 	= strtolower( $append_rule );
		$options 		= $this->_process_overrides( $lower_rule, $append_rule, $raw_rule, $rule, $satis );

		if( is_string($options['rules_path']) && !is_object($options['rules_path']) )
		{
			$is_class 		= file_exists( $options['rules_path'] );	
		}
		else
		{
			$is_class 		= (!is_string($options['rules_path']) && is_object($options['rules_path']));
		}
		
		$is_method 		= method_exists( $options['obj_ins'], $append_rule );

		$is_function 	= function_exists( $rule );
		$is_extension   = ISSET( static::$ajd_prop['extension_rule'][ $lower_rule ] );
		$satis 			= !EMPTY( $satis ) ? $satis : array();
		$satis 			= !is_array( $satis ) ? array( $satis ) : $satis;
		
		$args['lower_rule'] 		= $lower_rule;
		$args['rule_kind'] 			= NULL;
		
		if( $is_extension )
		{	
			$args['rule_kind'] 		= '_process_extension';
		}
		else if( $is_class AND !$options['override'] )
		{
			$args['rule_kind']  	= '_process_class';
		}
		else if( $is_method OR $options['meth_override'] )
		{
			$args['rule_kind'] 		= '_process_method';
		}
		else if( $is_function OR $options['func_override'] )
		{
			$args['rule_kind'] 		= '_process_function';
		}

		//var_dump(file_exists('application/libraries/respect/validation/library/Rules/EndsWith.php'));
		
		$args['args'] 				= $options;
		$args['args']['class_args'] = $satis;
		
		return $args;

	}

	private function _process_overrides( $lower_rule, $append_rule, $raw_rule, $rule )
	{
		$override 			= FALSE;
		$function_override 	= FALSE;
		$method_override 	= FALSE;

		$from_framework 	= "";
		$args 				= array();
		$obj_ins 			= static::get_ajd_instance();
		$rules_path 		= $this->get_rules_path().$append_rule.'.php';

		if( !EMPTY( static::$addRuleDirectory ) )
		{
			foreach( static::$addRuleDirectory as $classPath )
			{
				$requiredFiles 	= get_required_files();

				$pathHolder 	= $classPath.$append_rule.'.php';

				$search 		= array_search($pathHolder, $requiredFiles);

				if( file_exists( $pathHolder ) AND EMPTY( $search ) )
				{
					$rules_path 	= $pathHolder;
				}	
			}
		}

		$raw_append_rule 	= $raw_rule.'_'.static::$rules_suffix;
		$class_meth_call 	= 'run';
		$raw_class 			= $append_rule;
		$symfony_args 		= NULL;
		

		if( $this->isset_empty( static::$ajd_prop['class_override'], $append_rule ) OR
			$this->isset_empty( static::$ajd_prop['class_override'], $raw_append_rule ) )
		{
			$from_framework  	= static::$ajd_prop[ 'class_override' ][ $raw_rule ][0];
			$class_rule 		= $append_rule;
			$class_meth_call 	= static::$ajd_prop[ 'class_override' ][ $raw_rule ][1];
			$class_name 		= $append_rule;
			
			if( !EMPTY( $from_framework ) )
			{	
				$class_rule 	= ( in_array( $from_framework, static::$raw_rule ) ) ? ucfirst( strtolower( $raw_append_rule ) ) : $append_rule;
				$class_name 	= ( in_array( $from_framework, static::$raw_rule ) ) ? $raw_rule : $append_rule;
				$raw_class 		= $class_name;
			}
			
			$class 				= static::$ajd_prop['class_override'][ $class_rule ];

			if(is_string($class[0]) && !is_object($class[0]))
			{
				$rules_path 		= $class[0].Abstract_common::DS.$class_name.'.php';
			}
			else
			{
				$rules_path 		= $class[0];
			}

			$args['namespace'] 	= isset($class[1]) ? $class[1] : null;

		}
		else if( $this->isset_empty( static::$ajd_prop['method_override'], $lower_rule ) OR 
				 $this->isset_empty( static::$ajd_prop['method_override'], $raw_append_rule ) )
		{ 
			$override 			= TRUE;
			$from_framework 	= static::$ajd_prop['method_override'][ $raw_rule ];
			$method_rule 		= $lower_rule;

			if( !EMPTY( $from_framework ) )
			{
				$method_override= TRUE;
				$method_rule 	= ( in_array( $from_framework, static::$raw_rule ) ) ? $raw_append_rule : $lower_rule;

				if( in_array( $from_framework, static::$method_w_args ) ) 
				{
					$meth_arg_processor 	= '_process_'.$from_framework;

					$args 					= $this->{ $meth_arg_processor }( $method_rule, static::$ajd_prop[ 'method_override' ][ $from_framework ] );
					$symfony_args 			= $args['args'];
				}

			}

			$obj_ins 			= static::$ajd_prop['method_override'][ $method_rule ];
		}
		else if( $this->isset_empty( static::$ajd_prop['function_override'], $rule ) )
		{
			$function_override 	= TRUE;
			$func 				= static::$ajd_prop['function_override'][ $rule ];
			$args['func'] 		= $func;
		}

		$args['override'] 			= $override;
		$args['meth_override'] 		= $method_override;
		$args['obj_ins'] 			= $obj_ins;
		$args['rules_path'] 		= $rules_path;
		$args['class_name'] 		= get_class( $obj_ins );
		$args['func_override'] 		= $function_override;
		$args['from_framework'] 	= $from_framework;
		$args['class_meth_call'] 	= $class_meth_call;
		$args['raw_class'] 			= $raw_class;
		$args['symfony_args'] 		= $symfony_args;
		
		return $args;
	}

	private function _process_respect( $rule, $args )
	{
		return array(
			'args' => $args
		);
	}	

	private function _process_symfony( $constraint, $args )
	{
		if( !ISSET( static::$cache_instance[ $constraint ] ) )
		{
			$classReflection 			= static::get_factory_instance()->get_instance( TRUE );

			$ds 						= DIRECTORY_SEPARATOR;

			$constraint 				= $this->remove_appended_rule( $constraint );

			$path 						= NULL;

			if( ISSET( $args['default_path'] ) AND $args['default_path'] == TRUE )
			{
				$path 					= static::getConfig();
				$path 					= $path->get( 'symfony_path' ).$constraint.'.php';

				unset( $args['default_path'] );
			}

			$classReflection->set_rules_namespace( array( 'Symfony\\Component\\Validator\\Constraints\\' ) );

			if( ISSET( $args[ 'path' ] ) )
			{
				$path 					= $args[ 'path' ];

				unset( $args['path'] );
			}

			$obj 						= $classReflection->rules( $path, $constraint, $args );

		}
		else 
		{
			$obj 						= static::$cache_instance[ $constraint ];
		}

		static::$cache_instance[ $constraint ] 	= $obj;

		return array(
			'args' 						=> $obj
		);

	}
}