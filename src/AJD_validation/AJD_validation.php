<?php namespace AJD_validation;

use AJD_validation\Contracts\{ 
	Abstract_common, Base_validator, Validator, Rule_interface, Invokable_rule_interface,
	Abstract_anonymous_rule, Abstract_anonymous_rule_exception, Abstract_compound, 
	Abstract_sequential, Grouping_sequence_interface, ValidationProviderInterface, ExpressionRuleInterface,
	Validation_interface, DataSetInterface, AbstractRuleDataSet, AbstractRuleAliasRegistrar
};

use AJD_validation\Helpers\{ 
	When, Database, Client_side, Errors, Array_helper, Validation_helpers,
	Db_instance, Logics_map, Group_sequence, TriggerWhen, Expression
};

use AJD_validation\Async\{ 
	PromiseValidator, PromiseHelpers, FailedPromise, ValidationResult,
	Promise_interface, DeferredValidator
};

use AJD_validation\Combinators\{ 
	Combinator
};

use AJD_validation\Traits;

class AJD_validation extends Base_validator
{
	use Traits\AjdValidationMacro {
		__call as canMacroCall;
		__callStatic as canMacroCallStatic;
	}

	use Traits\AjdValExtender;
	use Traits\Conditionable;
	use Traits\Scenarioable;

	protected static $ajd_prop = [
		Abstract_common::LOG_AND => [
			'rules' => [],
			'details' => [],
			'satisfier' => [],
			'cus_err' => [],
			'filters' => [],
			'filter_satis' => [],
			'pre_filters' => [],
			'scenarios' => [],
			'sometimes' => [],
			'sometimes_arguments' => [],
			'stop_on_error' => [],
			'groups' => []
		],
		Abstract_common::LOG_OR => [
			'rules' => [],
			'details' => [],
			'satisfier' => [],
			'cus_err' => [],
			'filters' => [],
			'filter_satis' => [],
			'pre_filters' => [],
			'scenarios' => [],
			'sometimes' => [],
			'sometimes_arguments' => [],
			'stop_on_error' => [],
			'groups' => []
		],
		'groupings' => null,
		'cache_groupings' => null,
		'grouping_queue' => null,
		'extensions' => [],
		'extension_rule' => [],
		'extension_filter' => [],
		'extension_test' => [],
		'extension_anonymous_class' => [],
		'extensions_initialize' => false,
		'fields' => [],
		'js_rule' => [],
		'message' => [],
		'result' => [],
		'given_values' => [],
		'cache_filters' => [],
		'cache_stored_filters' => [],
		'and_or_stack' => [],
		'class_override' => [],
		'anonymous_class_override' => [],
		'method_override' => [],
		'function_override' => [],
		'current_field' => null,
		'current_rule' => null,
		'current_logic' => Abstract_common::LOG_AND,
		'check_group' => false,
		'result_values' => [],
		'events' => [],
		'global_fiberize' => false,
		'fibers' => [],
		'fiber_suspend' => [],
		'fiber_events' => [],
		'makeAsync' => false,
		'setUpFrom' => null
	];

	protected static $bail = false;
	protected static $cache_instance = [];

	protected static $cacheByFieldInstance = [];
	protected static $middleware = [];
	protected static $cacheMiddleware = [];
	protected static $globalVar = [];
	protected static $remove_scenario = [];

	protected static $lang;
	protected static $addLangDir = [];
	protected static $createWriteLangDir = [];
	protected static $langStubs = [];
	
	protected static $ajd_ins;

	protected static $currentObj;
	protected static $currentObjString;
	protected $rules_path;
	protected $check_cond = true;
	protected $customMesage = [];

	protected static $addRuleNamespace = [];
	protected static $addRuleDirectory = [];

	protected static $addRulesMappings = [];
	protected static $addValidationsMappings = [];
	protected static $registeredPackaged = [];
	protected static $packagesToRegister = [];

	protected static $dbConnections = [];

	protected $validationResult = [];

	protected static $fiberRule = 'fiberize';

	protected static $globalUseValidation = null;

	protected static $currRuleDetails = [];
	protected static $cacheSceneInstance = [];
	protected static $generators = [];
	protected static $customDataSets = [
		'dataSets' => [],
		'inverse' => [],
		'options' => []
	];

	protected static $registerDataSet = [];
	protected static $removeSpecificDataSetRegistry = [];
	protected static $flushDataSetRegistry = false;

	public static function get_ajd_instance()
	{
		if( is_null( static::$ajd_ins ) ) 
		{
			static::$ajd_ins = new AJD_validation;
		}

		return static::$ajd_ins;
	}

	protected function processCall($name, array $args)
	{
		$ajd = static::get_ajd_instance();
		$method = static::process_method_name( $name );
		$factory = static::get_factory_instance()->get_instance( false, false, true );
		$factory->rules( get_class( $ajd ), $method['method'] );

		array_unshift( $args, $method['name'] );

		static::$currentObj = $this;
		
		return $factory->process_method( $args, $ajd );
	}

	public function __call( $name, array $args )
	{
		if (static::hasMacro($name)) 
		{
            return $this->canMacroCall($name, $args);
        }

		return $this->processCall($name, $args);
	}

	public static function __callStatic( $name, array $args )
	{
		if (static::hasMacro($name)) 
		{
            return static::canMacroCallStatic($name, $args);
        }
        
        static::$currentObjString = get_called_class();
        
		return static::get_ajd_instance()->processCall( $name, $args );
	}

	public static function boot() 
	{
		if(!empty(static::$packagesToRegister))
		{
			foreach(static::$packagesToRegister as $package)
			{
				$packageInstance = new $package;

				static::registerPackage($packageInstance);

				if(isset(static::$registeredPackaged[get_class($packageInstance)]))
				{
					unset(static::$packagesToRegister[$package]);
				}
			}
		}
	}

	public function getValidator()
	{
		return new Validator;
	}

	public static function getExpression(array $vars = [])
	{
		return new Expression($vars);
	}

	public static function trigger( $scenario )
	{
		if( !is_array( $scenario ) )
		{
			static::$remove_scenario[] = $scenario;
		}
		else
		{
			static::$remove_scenario = array_merge( static::$remove_scenario, $scenario );
		}
	}

	public static function setGlobalVar( $globalVar )
	{
		if( is_array( $globalVar ) )
		{
			static::$globalVar = array_merge( static::$globalVar, $globalVar );
		}
		else
		{
			static::$globalVar[] = $globalVar;
		}
	}

	public static function setMiddleWare( $name, \Closure $func )
	{
		static::$middleware[ $name ]['func'] = $func;
	}

	public static function addDbConnection( $name, $dbConn )
	{
		static::$dbConnections[$name] = $dbConn;

		return static::get_ajd_instance();
	}

	public static function getDb( $name )
	{
		if(!isset(static::$dbConnections[$name]))
		{
			return null;
		}

		$db_instance = new Db_instance($name, [Db_instance::JUST_INSTANCE_STR => true]);

		return $db_instance->getDbInstance();
	}

	public function triggerWhen($checker, array $vars = [])
	{
		$ajd = static::get_ajd_instance();

		$triggerWhen = new TriggerWhen($ajd, $checker, $vars);

		return $triggerWhen;
	}

	public static function setValidation($validation)
	{
		$ajd = static::get_ajd_instance();

		$newValidation = $ajd->useValidation($validation);

		if($newValidation != $ajd)
		{
			static::$globalUseValidation = $newValidation;
		}
	}

	public function useValidation($validation)
	{
		$ajd = static::get_ajd_instance();

		if(
			isset(static::$addValidationsMappings[$validation])
			&& 
			!empty(static::$addValidationsMappings[$validation])
		)
		{
			$validation = key(static::$addValidationsMappings[$validation]);
		}

		if(class_exists($validation))
		{
			$reflectValidation = new \ReflectionClass($validation);

			$interfaces  = array_keys($reflectValidation->getInterfaces());

			if(in_array(Validation_interface::class, $interfaces, true))
	        {
				return new $validation($ajd);
			}
		}

		return $ajd;
	}

	public function resetGlobalValidation()
	{
		static::$globalUseValidation = null;
	}

	public function resetTriggerWhen()
	{
		$this->reset_all_validation_prop();
	}

	public static function addDataSets($dataSets, array $options = [], $inverse = false, $registerNameDataSet = null, $plotToRegistry = false)
	{
		if(
			!$plotToRegistry 
			&& is_string($dataSets)
			&& isset(static::$registerDataSet[$dataSets])
			&& !empty(static::$registerDataSet[$dataSets])
		)
		{
			$registeredDataSet = static::$registerDataSet[$dataSets];

			$dataSets = $registeredDataSet['dataSets'];

			if(empty($options))
			{
				$options = $registeredDataSet['options'];	
			}
		}

		$dataSets = (!is_array($dataSets)) ? [$dataSets] : $dataSets;

		if(is_callable($dataSets))
		{
			if(!$plotToRegistry)
			{
				static::plotDataSet($dataSets, $options, $inverse);
			}

			return static::registerDataSet($registerNameDataSet, $dataSets, $options, $inverse);
		}

		foreach($dataSets as $dataSet)
		{
			if(!is_callable($dataSet))
			{
				$reflection = new \ReflectionClass($dataSet);

				$interfaces =  array_keys($reflection->getInterfaces());

				if(
					in_array(DataSetInterface::class, $interfaces, true)
					&& 
					!in_array($dataSet, static::$customDataSets['dataSets'], true)
				)
				{
					if(!$plotToRegistry)
					{
						static::plotDataSet($dataSet, $options, $inverse);
					}

					static::registerDataSet($registerNameDataSet, $dataSet, $options, $inverse);
				}
			}

			if(is_callable($dataSet))
			{
				if(!$plotToRegistry)
				{
					static::plotDataSet($dataSet, $options, $inverse);
				}

				static::registerDataSet($registerNameDataSet, $dataSet, $options, $inverse);
			}
		}

		return static::get_ajd_instance();
	}

	protected static function plotDataSet($dataSets, array $options = [], $inverse = false)
	{
		static::$customDataSets['dataSets'][] = $dataSets;
		static::$customDataSets['inverse'][] = $inverse;
		static::$customDataSets['options'][] = $options;

	}

	public function checkAllMiddleware( $field, $value = null, array $customMesage = [], $check_arr = true )
	{
		$validation = $this->processGlobalValidation('checkAllMiddleware', $field, $value, $customMesage, $check_arr);
		
		if($validation)
		{
			return $validation;
		}

		if( !EMPTY( static::$middleware ) )
		{
			$current_name = key(static::$middleware);

			return $this->middleware($current_name, $field, $value, $check_arr, true);
		}
		else
		{
			return $this->checkArr( $field, $value, $customMesage, $check_arr );
		}
	}

	public function middleware( $name, $field, $value = null, $check_arr = true, $all = false )
	{
		$validation = $this->processGlobalValidation('middleware', $name, $field, $value, $check_arr);
		
		if($validation)
		{
			return $validation;
		}

		$ajd = static::get_ajd_instance();
		$args = array( $field, $value, $check_arr );
		$curr_field = static::$ajd_prop[ 'current_field' ];
		
		if( isset( static::$middleware[ $name ] ) )
		{
			if( !empty( $curr_field ) )
			{
				static::$middleware[ $name ][ 'prop' ] = static::$ajd_prop[ 'fields' ];	
			}
			else 
			{
				static::$middleware[ $name ][ 'prop_and' ] = static::$ajd_prop[ Abstract_common::LOG_AND ];	
				static::$middleware[ $name ][ 'prop_or' ] = static::$ajd_prop[ Abstract_common::LOG_OR ];
				static::$middleware[ $name ][ 'events' ] = static::$ajd_prop['events'];
				static::$middleware[ $name ][ 'setUpFrom' ] = static::$ajd_prop['setUpFrom'];
				static::$middleware[ $name ][ 'fiber_suspend' ] = static::$ajd_prop['fiber_suspend'];
			}

			$nextKey = false;

			$allMiddlewares = static::$middleware;

			$middleWareKeys = array_keys( static::$middleware );

			if($all)
			{
				$nextKey = next( $middleWareKeys );
			}
			
			if( !empty( $nextKey ) )
			{
				if( isset( static::$middleware[ $nextKey ] ) )
				{
					$keys = array_keys(static::$cacheMiddleware);
							
					if(empty($keys))
					{
						static::$cacheMiddleware = static::$middleware;
					}

					$func = function( $q, $args ) use ( $name, $curr_field, $nextKey, $field, $value, $check_arr, $all ) 
					{
						unset( static::$middleware[ $name ] );

						$result = $q->invoke_func( [$q, 'middleware'], [$nextKey, $field, $value, $check_arr, $all] );

						return static::handleFailedMiddleware($result);
						// unset( static::$middleware[ $name ] );
					};

					$currentKeyValue = array_search($name, $middleWareKeys);

					unset( $middleWareKeys[ $currentKeyValue ] ); 	
				}
			}
			else
			{
				$this->reset_all_validation_prop();

				$func = function( $q, $args ) use ( $name, $curr_field ) 
				{	
					if( !empty( $curr_field ) )
					{
						static::$ajd_prop[ 'fields' ] = static::$middleware[ $name ]['prop'];	

						unset( static::$middleware[ $name ]['prop'] );
					}
					else 
					{
						static::$ajd_prop[ Abstract_common::LOG_AND ] = static::$middleware[ $name ][ 'prop_and' ];
						static::$ajd_prop[ Abstract_common::LOG_OR ] = static::$middleware[ $name ][ 'prop_or' ];
						static::$ajd_prop[ 'events' ] = static::$middleware[ $name ][ 'events' ];
						static::$ajd_prop[ 'setUpFrom' ] = static::$middleware[ $name ][ 'setUpFrom' ];	
						static::$ajd_prop[ 'fiber_suspend' ] = static::$middleware[ $name ][ 'fiber_suspend' ];	

						unset( static::$middleware[ $name ]['prop_and'] );
						unset( static::$middleware[ $name ]['prop_or'] );
						unset( static::$middleware[ $name ]['events'] );
						unset( static::$middleware[ $name ]['setUpFrom'] );
						unset( static::$middleware[ $name ]['fiber_suspend'] );
					}

					$result = $q->invoke_func( array( $q, 'check' ), $args );
						
					return static::handleFailedMiddleware($result);
					
				};
			}

			$main = $ajd->invoke_func( static::$middleware[ $name ]['func'], array( $ajd, $func, $args ) );

			if($all && !empty(static::$cacheMiddleware))
			{
				$middleKeys = array_keys(static::$cacheMiddleware);
				$lastKey = end($middleKeys);
				
				if(strtolower($lastKey) == strtolower($nextKey))
				{
					static::$middleware = static::$cacheMiddleware;

					static::$cacheMiddleware = [];
				}
			}

			return static::handleFailedMiddleware($main);
		}
		else 
		{
			return $this->check($field, $value, $check_arr);
			// $this->reset_all_validation_prop();
		}

	}

	public static function handleFailedMiddleware($result)
	{
		if(!$result)
		{
			$ajd = static::get_ajd_instance();

			$ajd->reset_all_validation_prop();

			$obs = static::get_observable_instance();

			return (static function() use ($result, $ajd, $obs)
			{
				$promise = new PromiseValidator(function(callable $resolve, callable $reject, $target) use ($result, $ajd)
				{
					try 
					{
						throw new \Exception("Middleware Failed.");
					}
				 	catch (\Throwable $exception) 
	                {
	                    $reject($exception);
	                } 
					finally 
	                {
	                	return $target;
	                }
				},
				function () use (&$mainFiber) 
				{
	        		if (\method_exists($target, 'cancel')) 
					{
	                	$target->cancel();
	                }
        		});

        		$obs->attach_observer( 'fails', $promise, array( $ajd ) );
        		$obs->notify_observer( 'fails' );

				return $promise;

			})($result, $ajd, $obs);
		}
		else
		{
			return $result;
		}
	}

	public static function hasAnonymousClass($name)
	{
		$ruleNames = static::createRulesName($name);

		return isset(static::$ajd_prop['anonymous_class_override'][$ruleNames['append_rule']]);
	}

	public static function registerAnonClass( $anons )
	{
		if(!is_array($anons))
		{
			$anons = [$anons];
		}

		$exceptions = static::createAnonExceptionObj($anons);

		foreach($anons as $anon)
		{
			$ruleNames = static::createRulesName($anon::getAnonName());
			$raw_class_name = $ruleNames['raw_class_name'];
			$class_name = $ruleNames['class_name'];
			$append_rule = $ruleNames['append_rule'];

			if(!isset(static::$cache_instance[$append_rule]))
			{
				if($anon instanceof Abstract_anonymous_rule)
				{
					$exception = true;

					static::$ajd_prop[ 'anonymous_class_override' ][ $append_rule ] = [
						'raw_class_name' => $raw_class_name,
						'class_name' => $class_name,
						'obj' => $anon
					];

					if($exception)
					{
						if(!empty($exceptions)
							&& isset($exceptions[$append_rule])
							&& !empty($exceptions[$append_rule])
						)
						{
							if($exceptions[$append_rule] instanceof Abstract_anonymous_rule_exception)
							{
								static::$ajd_prop[ 'anonymous_class_override' ][ $append_rule ]['exception'] = $exceptions[$append_rule];
							}
						}
					}
				}
			}
		}
	}

	public static function registerRuleAlias($alias, $registration, array $messages = [])
	{
		$validaion = null;
		$customError = [];
		$clientSides = [];

		if(static::hasAnonymousClass($alias))
        {
        	throw new \InvalidArgumentException('The alias "'.$alias.'" has already been registered.');
        }

        if($registration instanceof Rule_interface)
        {
        	$validation = $registration;
        	$cusErr = $validation->getValidatorCustomError();
        	$client = $validation->getValidatorClientSide();

        	if(!empty($cusErr))
        	{
        		$customError[$alias] = $cusErr;
        	}

        	if(!empty($client))
        	{
        		$clientSides[$alias] = $client;
        	}
        }
        
        if(!$registration instanceof Rule_interface && is_callable($registration))
        {
        	$validation = $registration(static::get_ajd_instance()->getValidator());	
        }

		if(empty($validation))
		{
			throw new \Exception('Registration must return a '.Rule_interface::class);
		}

		$anonClass = new class($customError, $clientSides) extends AbstractRuleAliasRegistrar
		{};
		
		$anonClass::setAnonName($alias);
		$anonClass::setAliasValidation($validation);
		$anonClass::setAliasErrorMessages($messages);

		static::registerAnonClass($anonClass);
	}

	public static function makeAsync()
	{
		static::$ajd_prop['makeAsync'] = true;

		return static::get_ajd_instance();
	}

	public static function field( $field )
	{
		$key_arr = static::get_ajd_and_or_prop();
		$and_arr = [];
		$or_arr = [];

		$curr_logic = static::$ajd_prop[ 'current_logic' ];
		
		foreach ( $key_arr as $prop ) 
		{
			$and_arr[ $prop ] = static::$ajd_prop[ Abstract_common::LOG_AND ][ $prop ];
			$or_arr[ $prop ] = static::$ajd_prop[ Abstract_common::LOG_OR ][ $prop ];
		}
 		
		if( !EMPTY( $and_arr['rules'] ) )
		{   
			foreach ( $and_arr as $key => $value ) 
			{
				static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_AND ][ $field ][ Abstract_common::LOG_AND ][ $key ] = $value;
			}
		}
		
		if( !EMPTY( $or_arr['rules'] ) )
		{
			foreach ( $or_arr as $key => $value ) 
			{
				static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_OR ][ $field ][ Abstract_common::LOG_AND ][ $key ] = $value;
			}
		}

		static::$ajd_prop[ 'current_field' ] = $field;
		
		return static::get_field_scene_ins( $field, true, false );
	}

	public static function bail()
	{
		static::$bail = true;

		return static::get_ajd_instance();
	}

	public static function addRule( $rule, $satis = null, $custom_err = null, $client_side = null, $logic = Abstract_common::LOG_AND )
	{
		if( !static::$ajd_prop['extensions_initialize'] )
		{
			static::init_extensions();
		}

		$all_args = func_get_args();	

		$real_satis = [];
		$real_satis[] = $satis;	

		$satis = !empty( $satis ) ? $satis : '';	

		$clientMessageOnly = false;	

		if( !empty( $all_args ) )
		{
			$arg_proc = $all_args;

			unset( $arg_proc[0] );
			unset( $arg_proc[1] );
			
			if( count( $all_args ) > 1 )
			{
				$real_satis = array_merge( $real_satis, $arg_proc );
			
				$funct_cuss_err = null;
				$funct_client_side = null;
				$funct_logic = Abstract_common::LOG_AND;
				$funct_client_message_only = false;
				
				foreach( $arg_proc as $funct_args )
				{
					if( is_string( $funct_args ) 
						&& ( bool ) preg_match('/@custom_error_/', $funct_args) != false
						&& $funct_args !== Abstract_common::LOG_AND 
						&& $funct_args !== Abstract_common::LOG_OR 
					)
					{
						$funct_cuss_err = preg_replace('/@custom_error_/', '', $funct_args);
					}
					else if( is_string( $funct_args ) && (bool) preg_match( '/#client_/', $funct_args ) != false )
					{
						$funct_client_side = preg_replace('/#client_/', '', $funct_args);
					}
					else if( is_string( $funct_args ) && (bool) preg_match( '/#clientmessageonly/', $funct_args ) != false )
					{
						$funct_client_message_only = preg_replace('/#clientmessageonly_/', '', $funct_args);
					}
					else if( $funct_args === Abstract_common::LOG_AND || $funct_args === Abstract_common::LOG_OR )
					{
						$funct_logic = $funct_args;
					}
				}
				
				$custom_err = $funct_cuss_err;
				$client_side = $funct_client_side;
				$logic = $funct_logic;
				$clientMessageOnly = $funct_client_message_only;
			}
		}
		
		$ajd = static::get_ajd_instance();
		$raw_rule = static::removeWord( $rule, '/^!/' );
		$rule = strtolower( $rule );
		$curr_field = static::$ajd_prop[ 'current_field' ];
		$logic = strtolower( $logic );
		$clean_rule = $ajd->clean_rule_name( $rule );		
		$append_rule = ucfirst( $clean_rule['rule'] ).'_'.static::$rules_suffix;
		$rule_kind = $ajd->_process_rule_kind( $clean_rule['rule'], $append_rule, $raw_rule, $real_satis );

		$curr_logic = static::$ajd_prop['current_logic'];
		
		$args = [
			'curr_field' => $curr_field,
			'clean_rule' => $clean_rule,
			'satis' => $real_satis,
			'rule_kind' => $rule_kind,
			'append_rule' => $append_rule,
			'logic' => $logic,
			'curr_logic' => $curr_logic,
			'custom_err' => $custom_err,
			'client_side' => $client_side,
			'raw_rule' => $raw_rule,
			'rule' => $rule,
			'client_message_only' => $clientMessageOnly
		];

		$plotDetails = static::plotValidationDetails( $args );

		$currentRuleKey = $plotDetails['currentRuleKey'];

		static::$currRuleDetails = $plotDetails['currDetails'];

		return static::get_scene_ins( $clean_rule['rule'], $logic, true, null, $currentRuleKey );
	}

	protected static function plotValidationDetails( array $args )
	{
		$currentRuleKey = null;
		$curr_field = $args['curr_field'];
		$clean_rule = $args['clean_rule'];
		$satis = $args['satis'];
		$rule_kind = $args['rule_kind'];
		$append_rule = $args['append_rule'];
		$logic = $args['logic'];
		$curr_logic = $args['curr_logic'];
		$custom_err = $args['custom_err'];
		$client_side = $args['client_side'];
		$raw_rule = $args['raw_rule'];
		$rule = $args['rule'];
		$clientMessageOnly = $args['client_message_only'];

		$rulesStorage = [];
		$currDetails = [];

		if( !empty( $curr_field ) )
		{
			$currentRuleKeyCurrField = null;

			$rulesStorage = static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'rules' ];

			if(!empty($rulesStorage))
			{
				$rulesKeysCurrField = array_keys($rulesStorage);
				$currentRuleKeyCurrField = end($rulesKeysCurrField);
			}

			$key_value = [
				'rules' => $clean_rule['rule'],
				'satisfier' => $satis,
				'details' => [ $clean_rule['check'], $append_rule, $rule_kind['rule_kind'], $rule_kind['args'], $rule_kind['lower_rule'], $currentRuleKeyCurrField ],
			];

			$constraintName = null;

			foreach( $key_value as $key => $value )
			{
				static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ $key ][] = $value;
			}

			// static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'cus_err' ] 									= array();
		

			static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'filters' ][] = null;
			static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'filter_satis' ][] = null;
			static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'pre_filters' ][] = null;

			/*if( EMPTY( static::$ajd_prop[ 'check_group' ] ) )
			{
				static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'scenarios' ] 								= array();
			}*/

			if(!is_null($currentRuleKeyCurrField))
			{	
				static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'sometimes' ][ $clean_rule['rule'] ][$currentRuleKeyCurrField] = null;
			}
			else
			{
				static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'sometimes' ][ $clean_rule['rule'] ] = null;
			}
			
			if( !empty( $custom_err ) )
			{
				if(!is_null($currentRuleKeyCurrField))
				{
					static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'cus_err' ][$currentRuleKeyCurrField][ $clean_rule[ 'rule' ] ] = $custom_err;
				}
				else
				{
					static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ][ 'cus_err' ][ $clean_rule[ 'rule' ] ] = $custom_err;	
				}
			}

			$currDetails = static::$ajd_prop[ 'fields' ][ $curr_logic ][ $curr_field ][ $logic ];
		}
		else 
		{
			static::$ajd_prop[ $logic ][ 'rules' ][] = $clean_rule['rule'];

			$rulesStorage = static::$ajd_prop[ $logic ][ 'rules' ];

			$currentRuleKeyCurr = null;
			if(!empty($rulesStorage))
			{
				$rulesKeysCurr = array_keys($rulesStorage);
				$currentRuleKeyCurr = end($rulesKeysCurr);
			}

			static::$ajd_prop[ $logic ][ 'satisfier' ][] = $satis;
			static::$ajd_prop[ $logic ][ 'details' ][] = [ $clean_rule['check'], $append_rule, $rule_kind['rule_kind'], $rule_kind['args'], $rule_kind['lower_rule'], $currentRuleKeyCurr ];

			if(!is_null($currentRuleKeyCurr))
			{
				static::$ajd_prop[ $logic ][ 'sometimes' ][ $clean_rule['rule'] ][$currentRuleKeyCurr] = null;
			}
			else
			{
				static::$ajd_prop[ $logic ][ 'sometimes' ][ $clean_rule['rule'] ] = null;	
			}
			
			
			if( !EMPTY( $custom_err ) ) 
			{
				$rule_name 		= $clean_rule[ 'rule' ];
				
				if(!is_null($currentRuleKeyCurr))
				{
					static::$ajd_prop[ $logic ][ 'cus_err' ][$currentRuleKeyCurr][ $rule_name ] = $custom_err;
				}
				else
				{
					static::$ajd_prop[ $logic ][ 'cus_err' ][ $rule_name ] = $custom_err;
				}
			}

			$currDetails = static::$ajd_prop[ $logic ];
		}

		if(!empty($rulesStorage))
		{
			$rulesKeys = array_keys($rulesStorage);

			$currentRuleKey = end($rulesKeys);
		}

		if( !empty( $client_side ) )
		{
			static::plotCLientSide($rule, $client_side, $clientMessageOnly, $curr_field, $satis, $custom_err);
		}

		return [
			'currDetails' => $currDetails,
			'currentRuleKey' => $currentRuleKey
		];
	}

	public static function plotCLientSide($rule, $client_side, $clientMessageOnly, $curr_field, array $satis = [], $custom_err = null)
	{
		$orig_rule = strtolower( $rule );
		$rule = $orig_rule.'_'.static::$rules_suffix;
		
		static::$ajd_prop[ 'js_rule' ][ $client_side ][ $rule ][] = [
			'satisfier' => $satis,
			'curr_field' => $curr_field,
			'client_message_only' => $clientMessageOnly
		];

		if( !empty( $custom_err ) )
		{
			static::$ajd_prop[ 'js_rule' ][ $client_side ][ $rule ][] = [
				'custom_error' => [ $orig_rule => $custom_err ]
			];
		}
	}

	public static function addOrRule( $rule, $satis = null, $custom_err = null, $client_side = null )
	{
		return static::addRule( $rule, $satis, $custom_err, $client_side, Abstract_common::LOG_OR );
	}

	public static function superRule( $rule, $satis = null, $logic = Abstract_common::LOG_AND, $custom_err = null, $client_side = null )
	{
		static::$ajd_prop[ 'current_field' ] = null;
		static::$ajd_prop[ 'current_rule' ] = $rule;
		
		static::$ajd_prop[ 'and_or_stack' ][] = $logic;
		static::$ajd_prop['current_logic'] = $logic;
		
		return static::addRule( $rule, $satis, $custom_err, $client_side, $logic );

	}

	public static function endSuperRule()
	{
		static::$ajd_prop[ 'current_field' ] = null;
		static::$ajd_prop[ 'current_rule' ] = null;
		$logic = array_pop( static::$ajd_prop[ 'and_or_stack' ] );

		$key_arr = static::get_ajd_and_or_prop();

		foreach( $key_arr as $key )
		{	
			if( isset( static::$ajd_prop[ $logic ][ $key ] ) )
			{
				array_pop( static::$ajd_prop[ $logic ][ $key ] );
			}

			/*if( ISSET( static::$ajd_prop[ 'fields' ][ $logic ][ $key ] ) )
				array_pop( static::$ajd_prop[ 'fields' ][ $logic ][ $key ] );*/
		}

		return static::get_ajd_instance();
	}

	public static function getClientSide( $perField = true, $format = Client_side::PARSLEY )
	{
		$ajdIns = static::get_ajd_instance();
		$clientSide = new Client_side( static::$ajd_prop['js_rule'], $ajdIns, $format );
		
		return $clientSide->get_js_validations($perField);
	}

	public static function combinator(...$args)
	{
		return new Combinator(...$args);
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
		$logic = static::$ajd_prop[ 'current_logic' ];
		$curr_field = static::$ajd_prop[ 'current_field' ];
		$filters = [];
		$filter_satis = [];
		$pre_filters = [];

		if( !empty( $curr_field ) )
		{
			$filters = static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ 'filters' ];
			$filter_satis = static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ 'filter_satis' ];
			$pre_filters = static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ 'pre_filters' ];

			static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ 'filters' ] = [];
			static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ 'filter_satis' ] = [];
			static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ 'pre_filters' ] = [];
		}
		else
		{
			$filters = static::$ajd_prop[ $logic ]['filters'];
			$filter_satis = static::$ajd_prop[ $logic ]['filter_satis'];
			$pre_filters = static::$ajd_prop[ $logic ]['pre_filters'];
			
			static::$ajd_prop[ $logic ]['filters'] = [];
			static::$ajd_prop[ $logic ]['filter_satis'] = [];
			static::$ajd_prop[ $logic ]['pre_filters'] = [];
		}

		static::$ajd_prop['cache_filters'][ $field ] = [
			'filters' => $filters,
			'filter_satis' => $filter_satis,
			'pre_filters' => $pre_filters
		];

		return static::get_ajd_instance();
	}

	protected static function processFilters(array $filter_details, $field, $value, $check_arr, $val_only, $append = false)
	{
		$filter_value = null;

		if( !empty( $filter_details['filters'] ) )
		{
			$check = TRUE;

			if( !empty( $check_arr ) )
			{
				if( !is_array( $value ) )
				{
					$check = false;
				}
			}
			else
			{
				$check = false;
			}
			
			$real_val = static::handle_filter( $filter_details['filters'], $value, $field, $filter_details['filter_satis'], $filter_details['pre_filters'], $check, $val_only, $append );
			$pre_filt_value = static::pre_filter_value( $field );
			$filt_value = static::filter_value( $field );

			if( $val_only )
			{
				$new_value = $real_val;
			}
			else
			{
				$new_value = ( isset( $pre_filt_value ) && !empty( $pre_filt_value ) ) ? $pre_filt_value : $filt_value;
			}

			/*if( EMPTY( $new_value ) )
			{
				$new_value 	= $value;
			}*/

			$filter_value = $new_value;
		}

		return $filter_value;
	}

	public static function filterSingleValue( $value, $val_only = false, $check_arr = true, $clearCache = true, $append = false )
	{
		$filter_value = $value;

		if( !EMPTY( static::$ajd_prop['cache_filters'] ) )
		{
			foreach( static::$ajd_prop['cache_filters'] as $field => $filter_details )
			{
				$filter_value = static::processFilters($filter_details, $field, $value, $check_arr, $val_only, $append);
			}
		}

		if( $clearCache )
		{
			static::$ajd_prop['cache_filters'] 	= [];
		}
		
		return $filter_value;
	}

	public static function filterValue($value, $append = true, $val_only = true, $check_arr = true, $clearCache = true)
	{
		return static::filterSingleValue($value, $val_only, $check_arr, $clearCache, $append);
	}

	public static function filterAllValues( array $value, $append = true, $check_arr = true)
	{
		return static::filterValues($value, $check_arr, $append);
	}

	public static function filterValues( array $values, $check_arr = true, $append = false )
	{
		$filter_value = [];
		$ajd_ins = static::get_ajd_instance();

		if( !empty( static::$ajd_prop['cache_filters'] ) )
		{
			foreach( static::$ajd_prop['cache_filters'] as $field => $filter_details )
			{
				if( isset( $values[ $field ] ) )
				{
					$value = $values[ $field ];

					$new_value = static::processFilters($filter_details, $field, $value, true, true, $append );

					$filter_value[ $field ]	= $new_value;
				}
			}
		}

		static::$ajd_prop['cache_filters'] 	= [];

		return $filter_value;
	}

	public static function addFilter( $filter, $satis = null, $pre_filter = false )
	{
		if( !static::$ajd_prop['extensions_initialize'] )
		{
			static::init_extensions();
		}
		
		$logic = static::$ajd_prop[ 'current_logic' ];
		$curr_field = static::$ajd_prop[ 'current_field' ];

		if( !empty( $curr_field ) )
		{
			$key_value = [
				'filters' => $filter,
				'filter_satis' => $satis,
				'pre_filters' => $pre_filter
			];			

			foreach( $key_value as $key => $value )
			{
				static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ $key ][] = $value;
			}			
		}
		else 
		{
			static::$ajd_prop[ $logic ]['filters'][] = strtolower( $filter );
			static::$ajd_prop[ $logic ]['filter_satis'][] = $satis;
			static::$ajd_prop[ $logic ]['pre_filters'][] = $pre_filter;
		}
 		
		return static::get_ajd_instance();
	}

	public static function addRuleMessage($rule, $msg)
	{
		static::add_rule_msg($rule, $msg, true);
	}

	public static function add_rule_msg( $rule, $msg, $anonWay = false )
	{
		if($anonWay)
		{
			if(static::hasAnonymousClass($rule))
			{
				$rule = static::createRulesName($rule);
				$anon = static::$ajd_prop['anonymous_class_override'][$rule['append_rule']] ?? null;

				if(empty($anon))
				{
					return;
				}

				$messageArr = [];	

				if(!is_array($msg))
				{
					$messageArr['default'] = $msg;
				}
				else
				{
					$messageArr = $msg;
				}
				
				$defaultMessage = $messageArr['default'] ?? '';
				$inverseMessage = $messageArr['inverse'] ?? $defaultMessage;

				$exceptionObj = $anon['exception'];

				$exceptionObj::$defaultMessages = [
					 $exceptionObj::ERR_DEFAULT => [
					 	$exceptionObj::STANDARD => $defaultMessage,
					 ],
				  	 $exceptionObj::ERR_NEGATIVE => [
			            $exceptionObj::STANDARD => $inverseMessage,
			        ]
				];

				Errors::addAnonErrorMessages($rule['append_rule'], $exceptionObj);
			}

			return;
		}

		$err = static::get_errors_instance();

		$err->set_errors( $rule, $msg );
	}

	public static function assert( $addParent = true )
	{
		$ajd = static::get_ajd_instance();
		
		if( $ajd->validation_fails() )
		{
			if( !EMPTY( $ajd->errors()->all() ) )
			{
				throw new \Exception( $ajd->errors()->toStringErr(array(), $addParent) );
			}
		}
	}

	public function forceReset()
	{
		$this->resetTriggerWhen();
		$this->setPropMessage([]);
	}

	public static function assertFirst( $addParent = true )
	{
		$ajd = static::get_ajd_instance();

		if( $ajd->validation_fails() )
		{
			if( !EMPTY( $ajd->errors()->all() ) )
			{
				throw new \Exception( $ajd->errors()->toStringErr( $ajd->errors()->firstAll(), $addParent ) );
			}
		}
	}

	public static function removeErrorMessage($field, array $messages = [], $override = false)
	{
		if(empty($messages))
		{
			$ajd = static::get_ajd_instance();
			$messages = static::$ajd_prop['message'];
		}

		if(!empty($field) && isset($messages[$field]))
		{
			unset($messages[$field]);

			if(!$override)
			{
				static::$ajd_prop['message'] = $messages;
			}
		}
	}

	public function setAjdProp(array $ajd_prop)
	{
		if(isset($ajd_prop[Abstract_common::LOG_AND]))
		{
			static::$ajd_prop[Abstract_common::LOG_AND] = $ajd_prop[Abstract_common::LOG_AND];	
		}

		if(isset($ajd_prop[Abstract_common::LOG_OR]))
		{
			static::$ajd_prop[Abstract_common::LOG_OR] = $ajd_prop[Abstract_common::LOG_OR];	
		}

		if(isset($ajd_prop['events']))
		{
			static::$ajd_prop['events'] = $ajd_prop['events'];	
		}

		if(isset($ajd_prop['setUpFrom']) && !empty($ajd_prop['setUpFrom']))
		{
			static::$ajd_prop['setUpFrom'] = $ajd_prop['setUpFrom'];	
		}

		if(isset($ajd_prop['fiber_suspend']) && !empty($ajd_prop['fiber_suspend']))
		{
			static::$ajd_prop['fiber_suspend'] = $ajd_prop['fiber_suspend'];	
		}

		if(isset($ajd_prop['cacheSceneInstance']) && !empty($ajd_prop['cacheSceneInstance']))
		{
			static::$cacheSceneInstance = $ajd_prop['cacheSceneInstance'];	
		}

		if(isset($ajd_prop['generators']) && !empty($ajd_prop['generators']))
		{
			static::$generators = $ajd_prop['generators'];	
		}

		return $this;
	}

	public static function overrideErrorMessage($field, $newMessage)
	{
		$ajd = static::get_ajd_instance();
		$ajdMessage = $ajd->getPropMessage();

		static::removeErrorMessage($field, $ajdMessage, true);
		
		$ajd->setPropMessage($ajdMessage);

		if(!empty($newMessage))
		{
			static::$ajd_prop['message'] = array_merge(static::$ajd_prop['message'], $newMessage);
		}
	}

	public static function any(...$promises)
	{
		$ajd = static::get_ajd_instance();

		$ajdMessage = $ajd->getPropMessage();

		$messages = [];

		$newPromises = [];
		
		foreach($promises as $promise)
		{
			$promise = $ajd->promiseOrValidation($promise);

			$newPromises[] = $promise;
			
			$field = $promise->getField();
			$fields = $promise->getFields();

			if(!empty($field) && isset($ajdMessage[$field]))
			{
				$messages[$field] = $ajdMessage[$field];

				unset($ajdMessage[$field]);
			}

			if(!empty($fields))
			{
				foreach($fields as $f)
				{
					if(!empty($f) && isset($ajdMessage[$f]))
					{
						$messages[$f] = $ajdMessage[$f];

						unset($ajdMessage[$f]);
					}
				}
			}

			$ajd->setPropMessage($ajdMessage);
		}

		$isValids = false;

		$orPromise = PromiseHelpers::any($newPromises);
		$deferredValidator = new DeferredValidator();

		$orPromise->then(
			function() use (&$isValids, $deferredValidator)
			{
				$isValids = true;

				$deferredValidator->resolve();
			},
			function($exception) use($messages, $deferredValidator)
			{
				if(!empty($messages))
				{
					static::$ajd_prop['message'] = array_merge(static::$ajd_prop['message'], $messages);
				}

				$deferredValidator->reject($exception);
			}
		);

		Combinator::dontRunCheck();
		$orCombinator = static::combinator(...$newPromises);

		if($isValids)
		{
			foreach($newPromises as $promise)
			{
				$promise->getValidationResult()->setIsValid(true);
			}
		}

		$orValidationResults = $orCombinator->check('');
		Combinator::dontRunCheck(false);

		$orValidationResults = $orValidationResults->setIsValid($isValids);
		$deferredValidator->promise()->setValidationResult($orValidationResults);

		return $deferredValidator->promise();
	}

	private function _checkGroup( array $data, $middleware = false )
	{
		static::$ajd_prop['check_group'] = true;
		
		$value = null;
		$or_success = [];
		$or_pass_arr = [];
		$or_passed_per_pass = [];
		$or_passed_per_single_pass = [];

		$obs = static::get_observable_instance();
		$ev = static::get_promise_validator_instance();

		$and_check = array();
		$or_check = array();

		$validator = $this->getValidator();
		$paramValidator = $validator->one_or( Validator::contains('.'), Validator::contains('*') );

		$orPromises = [];
		$orPromisesRaw = [];
		$andPromises = [];
		$andPromisesRaw = [];

		$or_fields_key = [];
		$and_fields_key = [];
		$or_field_merge = [];

		if( isset( static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_OR ] ) )
		{
			$or_field = static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_OR ];
			
			if( !empty( $or_field ) )
			{
				$fk = 0;
				foreach( $or_field as $field_key => $field_value )
				{
					$fieldValueOr = [];

					$or_fields_key[] = $field_key;

					$realFieldKey = Validation_helpers::getParentPath($field_key);
					
					if( ISSET( $field_value[Abstract_common::LOG_OR] ) )
					{
						$fieldValueOr = $field_value[Abstract_common::LOG_OR];
					}

					$propScene = $this->clearScenario( $field_value[Abstract_common::LOG_AND], $fieldValueOr );

					$field_value[Abstract_common::LOG_AND] = $propScene['prop_and'];
					$field_value[Abstract_common::LOG_OR] = $propScene['prop_or'];

					if( isset( $data[ $realFieldKey ] ) ) 
					{
						$value = $data[ $realFieldKey ];
					}
					else 
					{
						$value = '';
					}
					
					if( isset( $field_value[Abstract_common::LOG_AND]['scenarios'] ) || isset( $field_value[Abstract_common::LOG_OR]['scenarios'] ) )
					{
						$and_search = [];
						$or_search = [];

						if( isset( $field_value[Abstract_common::LOG_AND]['scenarios'] ) )
						{
							$and_search = $this->array_search_recursive( $field_key, $field_value[Abstract_common::LOG_AND]['scenarios'] );
						}

						if( isset( $field_value[Abstract_common::LOG_OR]['scenarios'] ) )
						{
							$or_search 	= $this->array_search_recursive( $field_key, $field_value[Abstract_common::LOG_OR]['scenarios'] );
						}

						if( !empty( $and_search ) || !empty( $or_search ) )
						{
							break;
						}
					}

					if( $paramValidator->validate($field_key) )
					{
						$field_key = Validation_helpers::removeParentPath( $realFieldKey, $field_key );
					}
 					
 					$or_pass_arr = [];
 					$orResultArr = [];
 					$or_passed_per = [];

 					$ffield_value = $field_value;

 					foreach($ffield_value as $vlogic => $vv)
 					{
 						$ffield_value[$vlogic]['no_error_message'] = [];

 						if(isset($vv['rules']))
 						{
	 						foreach($vv['rules'] as $kvv => $vvv)
	 						{
	 							$ffield_value[$vlogic]['no_error_message'][$vvv][$kvv] = true;
	 						}
	 					}
 					}

 					$or_field_merge[$field_key]['field_value'] = $ffield_value;
 					$or_field_merge[$field_key]['value'] = $value;
 					$or_field_merge[$field_key]['no_error_message'] = true;

					$or = $this->checkArr( $field_key, $value, array(), true, Abstract_common::LOG_AND, $field_value,  
						function($ajd, $checkResult) use (&$or_pass_arr, &$orResultArr, &$or_passed_per)
						{

						},
						false
					);

					$or = $this->promiseOrValidation($or);

					$orPromisesRaw[] = $or;

					$or_field_ch = $this->validation_fails( $field_key, null, true );
					$or_field_ch_orig = $this->validation_fails( $field_key );
					
					if($or_field_ch)
					{						
						$or->then(function()
						{
							throw new Exception('Validation Failed.');
						});
						
						$orFailed = PromiseHelpers::reject($or);
						
						$orPromises[] = $orFailed;	
					}
					else
					{
						$orPromises[] = $or;	
					}

					$or_check[] = $or_field_ch_orig;

					$fk++;
				}

				$or_field_arr = [];
				$or_field_name = current( array_keys( static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_OR ] ) );
				$or_field_arr = static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_OR ];
 			 	

				$details = $or_field[ $or_field_name ][ Abstract_common::LOG_AND ];
				$field_arr = $this->format_field_name( $or_field_name );
				
				$subCheck = $this->_processOrCollection( $orPromisesRaw );

				$value_or = (isset($data[$field_arr['orig']])) ? $data[$field_arr['orig']] : null;

				if( isset( static::$ajd_prop['events'][Abstract_common::EV_LOAD][$field_arr['orig']] ) )
				{
					$eventLoad = static::$ajd_prop['events'][Abstract_common::EV_LOAD][$field_arr['orig']];

					unset(static::$ajd_prop['events'][Abstract_common::EV_LOAD][$field_arr['orig']]);
					$this->_runEvents($eventLoad, $value_or, $field_arr['orig']);
				}

				if(!in_array(0, $subCheck))
				{
					if( ISSET( static::$ajd_prop['events'][Abstract_common::EV_FAILS][$field_arr['orig']] ) )
					{
						$eventFails = static::$ajd_prop['events'][Abstract_common::EV_FAILS][$field_arr['orig']];

						unset(static::$ajd_prop['events'][Abstract_common::EV_FAILS][$field_arr['orig']]);
						$this->_runEvents($eventFails, $value_or, $field_arr['orig']);
					}
				}
				else
				{
					if( isset( static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$field_arr['orig']] ) )
					{
						$eventSuccess = static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$field_arr['orig']];

						unset(static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$field_arr['orig']]);
						$this->_runEvents($eventSuccess, $value_or, $field_arr['orig']);
					}
				}

				$or_check = array_merge( $or_check, $subCheck );
			}

		}

		$check_and_arr = null;

		if( isset( static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_AND ] ) )
		{
			$check_and_arr = static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_AND ];
		}

		if( !empty( $check_and_arr ) )
		{
			$and_field = $check_and_arr;

			if( !empty( $and_field ) )
			{
				foreach( $and_field as $field_key => $field_value )
				{
					$realFieldKey = Validation_helpers::getParentPath($field_key);

					$and_fields_key[] = $field_key;

					$fieldValueOr = [];

					if( ISSET( $field_value[Abstract_common::LOG_OR] ) )
					{
						$fieldValueOr = $field_value[Abstract_common::LOG_OR];
					}

					$propScene = $this->clearScenario( $field_value[Abstract_common::LOG_AND], $fieldValueOr );

					$field_value[Abstract_common::LOG_AND] = $propScene['prop_and'];
					$field_value[Abstract_common::LOG_OR] = $propScene['prop_or'];

					if( isset( $field_value[Abstract_common::LOG_AND]['scenarios'] ) || isset( $field_value[Abstract_common::LOG_OR]['scenarios'] ) )
					{
						$and_search = [];
						$or_search = [];

						if( isset( $field_value[Abstract_common::LOG_AND]['scenarios'] ) )
						{
							$and_search = $this->array_search_recursive( $field_key, $field_value[Abstract_common::LOG_AND]['scenarios'] );
						}

						if( isset( $field_value[Abstract_common::LOG_OR]['scenarios'] ) )
						{
							$or_search = $this->array_search_recursive( $field_key, $field_value[Abstract_common::LOG_OR]['scenarios'] );
						}
						
						if( !empty( $and_search ) || !empty( $or_search ) )
						{
							break;
						}
					}

					if( isset( $data[ $realFieldKey ] ) )
					{
						$value = $data[ $realFieldKey ];
					}
					else 
					{
						$value = '';
					}

					if( $middleware )
					{

					}
					else 
					{
						if( $paramValidator->validate($field_key) )
						{
							$field_key = Validation_helpers::removeParentPath( $realFieldKey, $field_key );
						}

						if( isset( static::$ajd_prop['events'][Abstract_common::EV_LOAD][$field_key] ) )
						{
							$eventLoad = static::$ajd_prop['events'][Abstract_common::EV_LOAD][$field_key];

							unset(static::$ajd_prop['events'][Abstract_common::EV_LOAD][$field_key]);
							$this->_runEvents($eventLoad, $value, $field_key);
						}

						$validateGroupings = static::$ajd_prop['groupings'];

						if(
							isset($or_field_merge[$field_key])
							&& !empty($validateGroupings)
							&& 
							(
								isset(static::$ajd_prop['cache_groupings'][$field_key])
								&&
								static::$ajd_prop['cache_groupings'][$field_key] instanceof Grouping_sequence_interface
							)
						)
						{
							$field_value_merge = $or_field_merge[$field_key]['field_value'];
							$field_value[Abstract_common::LOG_AND]['rules'] = array_merge($field_value_merge[Abstract_common::LOG_AND]['rules'], $field_value[Abstract_common::LOG_AND]['rules']);

							$field_value[Abstract_common::LOG_AND]['details'] = array_merge($field_value_merge[Abstract_common::LOG_AND]['details'], $field_value[Abstract_common::LOG_AND]['details']);

							$field_value[Abstract_common::LOG_AND]['satisfier'] = array_merge($field_value_merge[Abstract_common::LOG_AND]['satisfier'], $field_value[Abstract_common::LOG_AND]['satisfier']);

							$field_value[Abstract_common::LOG_AND]['cus_err'] = array_merge($field_value_merge[Abstract_common::LOG_AND]['cus_err'], $field_value[Abstract_common::LOG_AND]['cus_err']);

							
							$field_value[Abstract_common::LOG_AND]['filters'] = array_merge($field_value_merge[Abstract_common::LOG_AND]['filters'], $field_value[Abstract_common::LOG_AND]['filters']);
							

							$field_value[Abstract_common::LOG_AND]['filter_satis'] = array_merge($field_value_merge[Abstract_common::LOG_AND]['filter_satis'], $field_value[Abstract_common::LOG_AND]['filter_satis']);

							$field_value[Abstract_common::LOG_AND]['pre_filters'] = array_merge($field_value_merge[Abstract_common::LOG_AND]['pre_filters'], $field_value[Abstract_common::LOG_AND]['pre_filters']);

							$field_value[Abstract_common::LOG_AND]['scenarios'] = array_merge($field_value_merge[Abstract_common::LOG_AND]['scenarios'], $field_value[Abstract_common::LOG_AND]['scenarios']);

							$field_value[Abstract_common::LOG_AND]['sometimes'] = array_merge($field_value_merge[Abstract_common::LOG_AND]['sometimes'], $field_value[Abstract_common::LOG_AND]['sometimes']);

							$field_value[Abstract_common::LOG_AND]['sometimes_arguments'] = array_merge($field_value_merge[Abstract_common::LOG_AND]['sometimes_arguments'], $field_value[Abstract_common::LOG_AND]['sometimes_arguments']);

							$field_value[Abstract_common::LOG_AND]['stop_on_error'] = array_merge($field_value_merge[Abstract_common::LOG_AND]['stop_on_error'], $field_value[Abstract_common::LOG_AND]['stop_on_error']);

							$field_value[Abstract_common::LOG_AND]['groups'] = array_merge($field_value_merge[Abstract_common::LOG_AND]['groups'], $field_value[Abstract_common::LOG_AND]['groups']);


							$field_value[Abstract_common::LOG_AND]['no_error_message'] = $field_value_merge[Abstract_common::LOG_AND]['no_error_message'];

							
							$groupingsMerge = $field_value[Abstract_common::LOG_AND]['groups'];
							$sometimesMerge = $field_value[Abstract_common::LOG_AND]['sometimes'];

							$newGroups = [];
							$newSometimes = [];

							if(isset($field_value[Abstract_common::LOG_AND]['rules']))
							{
								foreach($field_value[Abstract_common::LOG_AND]['rules'] as $rk => $rr)
								{	
									if(isset($groupingsMerge[$rr]))
									{
										$newGroups[$rr][$rk] = $groupingsMerge[$rr];
									}

									if(isset($sometimesMerge[$rr]))
									{
										if(isset($sometimesMerge[$rr][$rk]))
										{
											$newSometimes[$rr][$rk] = $sometimesMerge[$rr][$rk];
										}
										else
										{
											$newSometimes[$rr][$rk] = null;
										}
									}
								}

								$newGroups = Array_helper::flatten($newGroups, 2);	
								
								foreach($field_value[Abstract_common::LOG_AND]['rules'] as $rk => $rr)
								{
									if(isset($newGroups[$rk]))
									{
										unset($field_value[Abstract_common::LOG_AND]['groups'][$rr]);
										$field_value[Abstract_common::LOG_AND]['groups'][$rr][$rk] = $newGroups[$rk];
									}

									if(isset($newSometimes[$rr]))
									{
										unset($field_value[Abstract_common::LOG_AND]['sometimes'][$rr]);
										$field_value[Abstract_common::LOG_AND]['sometimes'][$rr][$rk] = $newSometimes[$rr][$rk];
									}
								}	
							}	
						}
							
						$andPromise = $this->checkArr( $field_key, $value, array(), true, Abstract_common::LOG_AND, $field_value, null, false );

						$andPromise = $this->promiseOrValidation($andPromise);

						$andPromises[] = $andPromise;
						$andPromisesRaw[] = $andPromise;

						$val_and_fails = $this->validation_fails( $field_key );

						$and_check[] = $val_and_fails;

						if($val_and_fails)
						{
							if( ISSET( static::$ajd_prop['events'][Abstract_common::EV_FAILS][$field_key] ) )
							{
								$eventFails = static::$ajd_prop['events'][Abstract_common::EV_FAILS][$field_key];

								unset(static::$ajd_prop['events'][Abstract_common::EV_FAILS][$field_key]);
								$this->_runEvents($eventFails, $value, $field_key);
							}
						}
						else
						{
							if( ISSET( static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$field_key] ) )
							{
								$eventSuccess = static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$field_key];

								unset(static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$field_key]);
								$this->_runEvents($eventSuccess, $value, $field_key);
							}
						}
					}
				}
			}
		}

		$orAllPromises = null;
		$andAllPromises = null;
		$allPromise = null;

		$allPromises = [];
		$allResults = [];

		Combinator::dontRunCheck();

		if(!empty($orPromises))
		{
			$orCombinator = static::combinator(...$orPromisesRaw);
			$orValidationResults = $orCombinator->check('');
			$allResults[] = $orValidationResults;
			$orAllPromises = PromiseHelpers::any($orPromises);			
			
			if(!empty($orValidationResults))
			{
				if(method_exists($orAllPromises, 'method_exists'))
				{
					$orAllPromises->setValidationResult($orValidationResults);		
				}
			}
		}

		if(!empty($andPromises))
		{
			$andCombinator = static::combinator(...$andPromises);
			$andValidationResults = $andCombinator->check('');
			$andAllPromises = PromiseHelpers::all($andPromises);

			if(!empty($andValidationResults))
			{
				$allResults[] = $andValidationResults;
				$andAllPromises->setValidationResult($andValidationResults);	
			}
		}

		if(!empty($orAllPromises))
		{
			$allPromises[] = $orAllPromises;
		}

		if(!empty($andAllPromises))
		{
			$allPromises[] = $andAllPromises;
		}

		if(!empty($allPromises))
		{
			$validationResults = null;

			if(!empty($allResults))
			{
				$combinator = static::combinator(...$allResults);
				$validationResults = $combinator->check('');	
			}

			$allPromise = PromiseHelpers::all($allPromises);

			if(!empty($validationResults))
			{
				$isValids = false;
				
				$allPromise->done(function() use(&$isValids)
				{
					$isValids = true;
				});

				$validationResults = $validationResults->setIsValid($isValids);

				foreach($allResults as $allResult)
				{
					$allResult->setIsValid($isValids);

					$validationResults = $validationResults->join(
						$allResult,
						function($valueA, $valueB)
						{
							return array_merge($valueA, $valueB);
						},
						function($errorsA, $errorsB)
						{
							return array_merge($errorsA, $errorsB);
						}
					);
				}

				$validationResults = $validationResults->setIsValid($isValids);
				$allPromise->setValidationResult($validationResults);
			}

			$mergeFieldsKey = array_merge($and_fields_key, $or_fields_key);

			if(!empty($mergeFieldsKey))
			{
				$allPromise->setFields($mergeFieldsKey);
			}	
		}

		$realEv = $ev;
		if(!empty($allPromise))
		{
			$realEv = $allPromise;
		}
		
		$obs->attach_observer( 'passed', $realEv, array( $this ) );
		$obs->attach_observer( 'fails', $realEv, array( $this ) );
		
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
		Combinator::dontRunCheck(false);
		return $realEv;

	}

	public static function subscribe($event, $callbacks, $customEvent = null)
	{
		$obs = static::get_observable_instance();
		$ajds = static::get_ajd_instance();

		if(is_array($callbacks))
		{
			if(is_callable($callbacks))
			{
				static::processEventCallback($obs, $event, $callbacks, [$ajds], $customEvent);

				return $ajds;
			}

			foreach($callbacks as $callback)
			{
				static::processEventCallback($obs, $event, $callback, [$ajds], $customEvent);
			}
		}
		else
		{
			static::processEventCallback($obs, $event, $callbacks, [$ajds], $customEvent);
		}
		

		return $ajds;
	}

	protected static function processEventCallback($obs, $event, $callback, array $args = [], $customEvent = null)
	{
		if(is_callable($callback))
		{
			$obs->attach_observer( $event, $callback, $args, $customEvent );
		}
		else
		{
			if(is_string($callback) && class_exists($callback))
			{
				$obs->attach_observer( $event, [new $callback, 'handle'], $args, $customEvent );
			}
		}
	}

	private function _processOrCollection( array $orPromisesRaw = [] )
	{
		$check = [];
		if(!empty($orPromisesRaw))
		{
			call_user_func_array([$this, 'any'], $orPromisesRaw);

			foreach($orPromisesRaw as $promise)
			{
				$field = $promise->getField();

				if(!empty($field))
				{
					$check[] = $this->validation_fails($field);
				}
			}
		}

		return $check;
	}

	public function checkGroup( array $data )
	{	
		$validation = $this->processGlobalValidation('checkGroup', $data);
		
		if($validation)
		{
			return $validation;
		}

		return $this->_checkGroup( $data );
	}

	public static function createGroupSequence(array $groupSequence)
	{
		return new Group_sequence($groupSequence);
	}

	protected function processCustomMessage( array $customMesage, $value )
	{
		if( !EMPTY( $customMesage ) )
		{
			foreach( $customMesage as $find => $message )
			{
				$formatFind = $this->format_field_name( $find );

				if( is_array( $value ) )
				{
					$custData = Validation_helpers::initializeProcessData($formatFind['orig'], $value);
				}
				else
				{
					$custData = $value;
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
				$realField = $field;
				$passArr = [
					'message' => $message
				];

				if( !EMPTY( $prependField ) )
				{
					$realField = $prependField.'.'.$field;
				}

				$passArr['formatField'] = null;

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
			$passArr = [
				'message' => $message
			];

			$passArr['formatField'] = $findClean;

			$this->customMesage[ $findOrig ] = $passArr;
		}
	}

	public function checkArr( $field, $value, $customMesage = [], $check_arr = true, $logic = Abstract_common::LOG_AND, $group = null, $func = null, $dontResetGrouping = false )
	{
		$validation = $this->processGlobalValidation('checkArr', $field, $value, $customMesage, $check_arr);
		
		if($validation)
		{
			return $validation;
		}

		$obs = static::get_observable_instance();
		$ev = static::get_promise_validator_instance();

		$this->processCustomMessage( $customMesage, $value ); 

		$checks = $this->_checkArr( $field, $value, $check_arr, $logic, $group, $func, $dontResetGrouping );

		$promiseAll = null;

		if( is_array( $checks ) )
		{
			if( empty( $group ) )
			{
				if( isset( $checks['checkValidations'] ) )
				{
					if(isset($checks['checkArr']) && !empty($checks['checkArr']))
					{
						Combinator::dontRunCheck();
						$promises = !is_array($checks['checkArr']) ? [$checks['checkArr']] : $checks['checkArr'];
						$combinator = static::combinator(...$promises);
						$validationResults = $combinator->check('');
						$promiseAll = PromiseHelpers::all($promises);
						
						if(!empty($validationResults))
						{
							$isValids = false;

							$promiseAll->done(function() use (&$isValids)
							{	
								$isValids = true;
							});

							$validationResults = $validationResults->setIsValid($isValids);

							$promiseAll->setValidationResult($validationResults);
						}

						Combinator::dontRunCheck(false);
					}

					$realEv = $ev; 

					if(!empty($promiseAll))
					{
						$realEv = $promiseAll;
					}
					
					$obs->attach_observer( $field.'-|passed', $realEv, [$this] );
					$obs->attach_observer( $field.'-|fails', $realEv, [$this] );

					if( !in_array(true, $checks['checkValidations']) ) 
					{
						$obs->notify_observer( $field.'-|passed' );
					}
					else
					{
						$obs->notify_observer( $field.'-|fails' );
					}

					$this->reset_all_validation_prop();
					
					return $realEv;
				}
			}
			else
			{
				if( isset( $checks['checkArr'] ) )
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

	protected function _checkArr( $field, $value, $check_arr = true, $logic = Abstract_common::LOG_AND, $group = null, $func = null, $dontResetGrouping = false )
	{
		$validator = $this->getValidator();
		$paramValidator = $validator->one_or( Validator::contains('.'), Validator::contains('*') );

		static $checkValidations = [];
		static $checkArr = [];

		if( 
			( $paramValidator->validate( $field ) ) 
			AND is_array( $value ) 
		)
		{
			$data = Validation_helpers::initializeProcessData($field, $value);
			
			foreach( $data as $subField => $v )
			{
				$formatSubField = $subField;
				$customDetails = [];

				if( ISSET( $this->customMesage[ $subField ] ) )
				{
					$customDetails = $this->customMesage[ $subField ];

					if( !EMPTY( $customDetails['formatField'] ) )
					{
						$formatSubField = $subField.'|'.$customDetails['formatField'];
					}
				}

				if( is_array( $v ) )
				{
					if(!empty($v))
					{
						$checkDet = $this->_checkArr($subField.'.*', $value, $check_arr, $logic, $group, $func, $dontResetGrouping);

						if(isset($checkDet['checkArr']))
						{
							if( is_array( $checkDet['checkArr'] ) )
							{
								$checkArr = array_merge( $checkArr, $checkDet['checkArr'] );
							}
							else
							{
								$checkArr[] = $checkDet['checkArr'];
							}
						}
					}
					else
					{
						$checkDet = $this->check( $formatSubField, $v, false, $logic, $group, true, $value, $func, $dontResetGrouping );
						
						if( is_array( $checkDet ) )
						{
							$checkArr = array_merge( $checkArr, $checkDet );
						}
						else
						{
							$checkArr[] = $checkDet;
						}

						$checkValidations[] = $this->validation_fails( $subField );
					}
				}
				else
				{
					if(empty($v))
					{
						$check_arr = false;
					}

					$checkDet = $this->check( $formatSubField, $v, $check_arr, $logic, $group, true, $value, $func, $dontResetGrouping );

					if( is_array( $checkDet ) )
					{
						$checkArr = array_merge( $checkArr, $checkDet );
					}
					else
					{
						$checkArr[] = $checkDet;
					}

					$checkValidations[] = $this->validation_fails( $subField );
				}
			}

			return [
				'checkValidations' => $checkValidations,
				'checkArr' => $checkArr
			];
		}
		else
		{			
			$check = $this->check( $field, $value, $check_arr, $logic, $group, false, $value, $func, $dontResetGrouping );

			return $check;
		}
	}

	public function checkDependent( $field, $value = null, $origValue = null, array $customMessage = array(), $check_arr = true, $logic = Abstract_common::LOG_AND, $group = null, $dontReset = false )
	{
		$validation = $this->processGlobalValidation('checkDependent', $field, $value, $origValue, $customMessage, $check_arr);
		
		if($validation)
		{
			return $validation;
		}

		$validator = $this->getValidator();
		$paramValidator = $validator->one_or( Validator::contains('.'), Validator::contains('*') );

		if( $paramValidator->validate( $field ) )
		{
			return $this->checkArr( $field, $value, $customMessage, $check_arr, $logic, $group );
		}
		else
		{
			return $this->check( $field, $value, $check_arr, $logic, $group, $dontReset, $origValue );
		}
	}

	public function checkAsync($field, $value = null, $function = null, $check_arr = true, $logic = Abstract_common::LOG_AND, $group = null, $dontReset = false, $origValue = null)
	{
		$validation = $this->processGlobalValidation('checkAsync', $field, $value, $function, $check_arr);
		
		if($validation)
		{
			return $validation;
		}

		return $this->check($field, $value, $check_arr, $logic, $group, $dontReset, $origValue, $function);
	}

	protected function processGlobalValidation($method, ...$args)
	{
		$globalUseValidation = null;

		if(static::$globalUseValidation)
		{
			$globalUseValidation = static::$globalUseValidation->{$method}(...$args);

			static::$globalUseValidation = $globalUseValidation;

			return $globalUseValidation;
		}
	}

	protected function checkifFieldArray($field, $value = null, $check_arr = true, $logic = Abstract_common::LOG_AND, $group = null, $dontReset = false, $origValue = null, $function = null, $dontResetGrouping = false, $fieldIsArray = false)
	{
		
		$that = $this;
		$checkFiber = class_exists('Fiber');
		$formatField = $this->format_field_name($field);
		$func = $function;

		if(!empty($function) && is_callable($function))
		{
			$func = $function;
		}
		else
		{
			$func = function($a){};	
		}

		$checkAsync = false;

		if(static::$ajd_prop['makeAsync'] && $checkFiber)
		{
			static::field($formatField['orig']);
			
			$this->reset_validation_selected_prop(
				[
					'current_rule', 'current_field', 'and_or_stack', 'given_values'
				]
			);

			$checkAsync = true;
		}
		else
		{
			if(empty($group))
			{
				$this->reset_validation_selected_prop(
					[
						'fields', 'current_field', 'current_rule'
					],
					true
				);
			}
			
		}

		static::$ajd_prop['makeAsync'] = false;

		return $that->createCheckPromiseValidator($func, $that, $field, $value, $check_arr, $logic, $group, $dontReset, $origValue, $dontResetGrouping, $formatField, $checkAsync, $fieldIsArray);
	}

	public function check( $field, $value = null, $check_arr = true, $logic = Abstract_common::LOG_AND, $group = null, $dontReset = false, $origValue = null, $function = null, $dontResetGrouping = false )
	{
		$validation = $this->processGlobalValidation('check', $field, $value, $check_arr);

		if($validation)
		{
			return $validation;
		}

		if(!is_array($field))
		{
			return $this->checkifFieldArray($field, $value, $check_arr, $logic, $group, $dontReset, $origValue, $function, $dontResetGrouping);
		}

		$promises = [];
		$arrayKeys = array_keys($field);
		$endIndex = end($arrayKeys);
		$firsIndex = reset($arrayKeys);
		$fistValResult = null;
		$fields = $field;

		foreach($field as $index => $field)
		{
			$dontReset = true;

			if($endIndex == $index)
			{
				$dontReset = false;
			}
			
			$promiseOrVal = $this->checkifFieldArray($field, $value, $check_arr, $logic, $group, $dontReset, $origValue, $function, $dontResetGrouping, $fields);	

			if(method_exists($promiseOrVal, 'getPromise'))
			{
				$promise = $promiseOrVal->getPromise();
			}
			else
			{
				$promise = $promiseOrVal;
			}

			if($firsIndex == $index)
			{
				$firsValResult = $promise->getValidationResult();

				$firsValResult = $firsValResult->mapErrors(function($messages, $self)
				{
					return Combinators\Each::commonFirstValMapErrors($messages, $self);
				});

				$firsValResult = $firsValResult->mapValue(function($value, $self)
				{
					$field = $self->getField();

					return Combinators\Each::commonMapValues($value, $field);
				});
			}
			else
			{
				if(!empty($firsValResult))
				{
					$validationResult = $promise->getValidationResult();

					$realField = $validationResult->getField();

					$firsValResult = $firsValResult
					->join(
						$validationResult,
						function($valueA, $valueB) use($realField)
						{
							$valueBProcess = Combinators\Each::commonMapValues($valueB, $realField);
							
							return array_merge($valueA, $valueBProcess);
						},
						function($errorsA, $errorsB) use($realField)
						{
							if(!empty($errorsB))
							{
								$errorsBMain = [
									$realField => $errorsB
								];

								return array_merge($errorsA, $errorsBMain);
							}

							return $errorsA;
						}
					);
				}
			}

			if($firsValResult)
			{
				$ajdProp = $firsValResult->getAjdProp();

				if($dontReset)
				{
					if(isset($ajdProp['cacheSceneInstance']) && !empty($ajdProp['cacheSceneInstance']))
					{
						static::$cacheSceneInstance = $ajdProp['cacheSceneInstance'];	
					}
				}

				$ajdProp['setUpFrom'] = $firsValResult->getField();

				if(isset($ajdProp['setUpFrom']) && !empty($ajdProp['setUpFrom']))
				{
					static::$ajd_prop['setUpFrom'] = $ajdProp['setUpFrom'];	
				}
			}

			$promises[] = $promise;
		}
		
		static::$ajd_prop['setUpFrom'] = null;

		$allPromise = PromiseHelpers::all($promises);

		$obs = static::get_observable_instance();

		$obs->attach_observer( 'passed', $allPromise, array( $this ) );
		$obs->attach_observer( 'fails', $allPromise, array( $this ) );

		$isValid = true;

		$allPromise->then(function() use(&$obs)
		{
			$obs->notify_observer( 'passed' );
		},
		function() use(&$obs, &$isValid)
		{
			$isValid = false;
			$obs->notify_observer( 'fails' );
		});

		if(!empty($firsValResult))
		{
			$firsValResult = $firsValResult->setIsValid($isValid);
			$allPromise->setValidationResult($firsValResult);
		}

		return $allPromise;
	}

	private function setPromiseError($ajd, $field, $target)
	{
		if($ajd->validation_fails($field))
		{
			if(
				!empty($ajd->errors()->outputError(true, $field))
			)
			{
				$errorMsg = $ajd->errors()->toStringErr($ajd->errors()->find($field));

				$errorMessages[] = [
					'errorMessages' => $errorMsg,
					'field' => $field,
					'ajd' => $ajd
				];

				$target->setHasErrors($errorMessages, $field);
			}

			throw new \Exception("Validation Failed.");
		}
	}

	public static function useGroupingsField($group = null, $queueSequence = null, $field = null)
	{
		return static::useGroupings($group, $queueSequence, $field, true);
	}

	public static function useGroupings($group = null, $queueSequence = null, $field = null, $forAlternativeSyntax = false)
	{
		$curr_field = null;
		if($forAlternativeSyntax)
		{
			$curr_field = static::$ajd_prop[ 'current_field' ];

			if(empty($field) && !empty($curr_field))
			{
				$field = $curr_field;
			}
		}

		if(!empty($group))
		{	
			$realGroup = $group;
			if(!empty($field))
			{	
				if(!is_array($group))
				{
					$group = [
						$field => $group
					];
				}
				else
				{
					if(!isset($group[$field]))
					{
						$group = [
							$field => $group
						];
					}
				}

				if(isset($group[$field]))
				{
					$realGroup = $group[$field];
				}
			}
			// $groupVal = $group;

			if(!is_array($realGroup)
				&& !$realGroup instanceof Grouping_sequence_interface
			)
			{
				if(!empty($field))
				{
					$group = [
						$field => $realGroup
					];
				}
				else
				{
					$group = [$realGroup];	
				}
				
				$groupVal = $realGroup;
			}

			if($realGroup instanceof Grouping_sequence_interface)
			{
				$groupVal = $realGroup->sequence();
			}

			if(
				$forAlternativeSyntax
			)
			{	
				if(!empty($curr_field))
				{
					if(!is_array(static::$ajd_prop['groupings']))
					{
						static::$ajd_prop['groupings'] = [];	
					}

					if(isset($group[$field]))
					{
						static::$ajd_prop['groupings'][$field] = $group[$field];		
					}
					
				}
					
			}
			else
			{
				if(!empty($field))
				{
					if(!is_array(static::$ajd_prop['groupings']))
					{
						static::$ajd_prop['groupings'] = [];	
					}

					if(isset($group[$field]))
					{
						static::$ajd_prop['groupings'][$field] = $group[$field];	
					}
				}
				else
				{
					static::$ajd_prop['groupings'] = $group;		
				}	
			}
			
			if($realGroup instanceof Grouping_sequence_interface)
			{
				$firstInQueue = reset($groupVal);
				if(!empty($firstInQueue) && empty($queueSequence))
				{
					if(!empty($field))
					{
						$firstInQueueArr = [
							$field => $firstInQueue
						];
						
						static::$ajd_prop['grouping_queue'] = $firstInQueueArr;
					}
					else
					{

						static::$ajd_prop['grouping_queue'] = $firstInQueue;	
					}
					
				}
			}

			if(!empty($queueSequence))
			{
				if(!empty($field))
				{
					$queueSequenceArr = [
						$field => $queueSequence
					];

					static::$ajd_prop['grouping_queue'] = $queueSequenceArr;
				}
				else
				{
					static::$ajd_prop['grouping_queue'] = $queueSequence;	
				}
				
			}
		}
		
		return static::get_ajd_instance();
	}

	protected function promiseOrValidation($object)
	{
		$reflection = new \ReflectionClass($object);

		$interfaces =  array_keys($reflection->getInterfaces());

		if(
			!in_array(Promise_interface::class, $interfaces, true)
			&&
			in_array(Validation_interface::class, $interfaces, true)
		)
		{
			return $object->getPromise();
		}

		return $object;
	}

	public function each(array $rules, $context = null)
	{
		return new Combinators\Each($rules, $context);
	}

	public function setUpValidation($field = null, $check_arr = true, $logic = Abstract_common::LOG_AND, $group = null)
	{
		if(is_null($field))
		{
			$orig_field = 'spl_object_id##'; 
			$field_arr = [
				'clean' => $orig_field
			];
		}
		else
		{
			$field_arr = $this->format_field_name( $field );
			$orig_field = $field_arr['orig'];
		}

		$this->setUpConfigValidationResult($orig_field, $field_arr, $logic);

		$prop_and = static::process_check_args( Abstract_common::LOG_AND, $group );
		$prop_or = static::process_check_args( Abstract_common::LOG_OR, $group );
		$prop = $prop_and;
		$propScene = $this->clearScenario( $prop_and, $prop_or, $prop );

		$prop = $propScene['prop'];
		$prop_and = $propScene['prop_and'];
		$prop_or = $propScene['prop_or'];

		$this->plotValidationResult($orig_field, $prop_and, $prop_or);

		$validationResult = new ValidationResult(null, null, $this->validationResult);

		$this->reset_all_validation_prop();

		return $validationResult;
	}

	private function plotValidationResult($orig_field, $prop_and, $prop_or)
	{
		$this->validationResult[$orig_field]['ajd_prop'][Abstract_common::LOG_AND] = $prop_and;
		$this->validationResult[$orig_field]['ajd_prop'][Abstract_common::LOG_OR] = $prop_or ?: static::$ajd_prop[Abstract_common::LOG_OR];
		$this->validationResult[$orig_field]['ajd_prop']['events'] = static::$ajd_prop['events'];
		$this->validationResult[$orig_field]['ajd_prop']['js_rule'] = static::$ajd_prop['js_rule'];
		$this->validationResult[$orig_field]['ajd_prop']['fiber_suspend'] = static::$ajd_prop['fiber_suspend'];
		$this->validationResult[$orig_field]['ajd_prop']['cacheSceneInstance'] = static::$cacheSceneInstance;
		$this->validationResult[$orig_field]['ajd_prop']['generators'] = static::$generators;
	}

	private function setUpConfigValidationResult($orig_field, array $field_arr, $logic)
	{
		$this->validationResult[$orig_field] = [
			'logic' => $logic,
			'clean_field' => $field_arr['clean'] ?: $orig_field,
			'ajd' => $this,
			'value' => [],
			'errors' => [],
			'valid' => null,
			'ajd_prop' => [],
			'cacheSceneInstance' => [],
			'generators' => [],
		];
	}

	protected function processDataSet($field, $value = null, array $field_arr = [], $check_arr = true, $fieldIsArray = false, $inRule = false, array $scenarios = [], $runPrevalidate = true )
	{
		$newObjects = [];

		if(!empty(static::$customDataSets['dataSets']))
		{
			foreach(static::$customDataSets['dataSets'] as $key => $dataSet)
			{
				if(is_callable($dataSet))
				{
					$objDataSet = $this->createDataSet($dataSet);
				}
				else
				{
					$objDataSet = new $dataSet;
				}

				$objDataSet->scenarios = $scenarios;

				$objDataSet->arguments = static::$customDataSets['options'][$key] ?? [];
				$objDataSet->inverse = static::$customDataSets['inverse'][$key] ?? false;
				$objDataSet->ajd = $this;
				$objDataSet->value = $value;
				$objDataSet->orig_field = $field_arr['orig'];
				$objDataSet->clean_field = $field_arr['clean'];
				$objDataSet->check_arr = $check_arr;

				$newObjects[] = $objDataSet;

				$fieldDataSet = $objDataSet->field();

				$firstField = null;

				if(!empty($fieldIsArray))
				{
					$firstField = reset($fieldIsArray);
				}

				if(
					( 
						empty($fieldDataSet)
						||
						$fieldDataSet == $field_arr['orig']
					)
				)
				{
					if(
						!empty($fieldDataSet)
						||
						( empty($firstField) || $firstField == $field )
					)
					{
						if(!$inRule)
						{
							$objDataSet->rules();		
						}
						
					}

					if($runPrevalidate)
					{
						$preValidate = $this->processDataSetPreValidate($objDataSet, $value, $field, $check_arr);

						$value = $preValidate['value'];
						$field = $preValidate['field'];
						$check_arr = $preValidate['check_arr'];
					}
				}
			}
		}

		return $newObjects;
	}

	protected function processDataSetPreValidate(DataSetInterface $objDataSet, $value = null, $field = null, $check_arr = true)
	{
		$returnValue = [
			'value' => $value,
			'field' => $field,
			'check_arr' => $check_arr
		];

		$preValidate = $objDataSet->preValidate($value, $field, $check_arr);

		if(!empty($preValidate))
		{
			foreach($preValidate as $preValidateName => $preValidateValue)
			{
				$returnValue[$preValidateName] = $preValidateValue;
			}	
		}

		$objDataSet->setPreValidate($returnValue);

		return $returnValue;
	}

	protected function processDataSetValidation(array $dataSets, $orig_field, $value = null, $key = null)
	{
		if(empty($dataSets))
		{
			return true;
		}

		$checks = [];
		$valueKey = $key ?? 0;

		foreach($dataSets as $dataSet)
		{
			$field = $dataSet->field();

			if(
				empty($field)
				||
				$field == $orig_field
			)
			{
				$result = $dataSet->validation($value, $valueKey);

				$appendedMessages = $dataSet->getErrorMessage();

				$check = $result ?? (!empty($orig_field) && !empty($appendedMessages)) ? !$this->validation_fails($orig_field) : $dataSet->checkValidation;

				$checks[] = ($dataSet->inverse) ? !$check : $check;
			}
		}

		return !in_array(false, $checks, true);
	}

	protected function processExtendRule(array $props, $field, $value = null, array $field_arr = [], $check_arr = true, $fieldIsArray = false, $dontAppendNewObject = false)
	{
		$alters = [];
		$preValidate = [];
		$realNewObject = [];

		if(isset($props['details']) && !empty($props['details']))
		{
			$lastIndex = 0;

			foreach($props['details'] as $index => $details)
			{
				if($details[4] == 'extend_rule')
				{
					$lastIndex = $index;
				}
			}
			
			foreach($props['details'] as $index => $details)
			{
				if($details[4] == 'extend_rule')
				{
					$dataSets = $props['satisfier'][$index][0]  ?? null;
					$arguments = $props['satisfier'][$index][1]  ?? [];

					if(isset($props['satisfier'][$index][2]))
					{
						$props['satisfier'][$index][] = '';
						$details[3]['class_args'][] = '';	
					}

					$registerNameDataSet = $props['satisfier'][$index][2]  ?? '';
					$inverse = $details[0];
					$ruleName = $props['rules'][$index] ?? '';

					if(!empty($fieldIsArray) && !empty($registerNameDataSet))
					{
						if( isset(static::$registerDataSet[$registerNameDataSet]) && !empty(static::$registerDataSet[$registerNameDataSet]) )
						{
							$dataSets = $registerNameDataSet;
							$registerNameDataSet = '';
						}
					}

					static::addDataSets($dataSets, $arguments, $inverse, $registerNameDataSet);

					if( !empty( $props['scenarios'] ) )
					{
						$check_scena = $this->array_search_recursive( $index.'|+'.$ruleName, $props['scenarios'] );
						
						if( !empty( $check_scena ) ) continue;
					}

					if($lastIndex == $index)
					{
						$scenarios = $this->processScenarios($props, $index, $ruleName, $details[1], $details);
						
						$newObjects = $this->processDataSet($field, $value, $field_arr, $check_arr, $fieldIsArray, false, $scenarios);

						$realNewObject = array_merge($realNewObject, $newObjects);

						if(!empty($newObjects) && !$dontAppendNewObject)
						{
							if(isset($props['satisfier'][$index]))
							{
								$props['satisfier'][$index][] = $newObjects;
							}

							if(isset($details[3]['class_args']))
							{
								$details[3]['class_args'][] = $newObjects;	

								if(isset($props['details'][$index][3]['class_args']))
								{
									$props['details'][$index][3]['class_args'] = $details[3]['class_args'];
								}
							}

							$alters[] = true;
						}
					}
				}
				
			}
		}

		if(!empty($realNewObject))
		{
			$lastNewObject = end($realNewObject);

			$preValidate = $lastNewObject->getPrevalidate();
		}
		
		return [
			'props' => $props,
			'alter' => in_array(true, $alters, true),
			'preValidate' => $preValidate
		];
	}

	private function _fiberize_check($field, $value = null, $check_arr = true, $logic = Abstract_common::LOG_AND, $group = null, $dontReset = false, $origValue = null, $fiberize = false, $promise = null, $dontResetGrouping = false, $fieldIsArray = false)
	{
		$prop_or = [];
		$prop_and = [];
		$prop = [];

		$field_arr = $this->format_field_name( $field );
		$orig_field = $field_arr['orig'];
		$cacheOrigValue = $origValue ?? $value;
		
		if( is_array( $value ) && $check_arr )
		{
			if( isset( $value[ $field_arr['orig'] ] ) )
			{
				$value = $value[$field_arr['orig']];
			}
			else
			{
				$value = null;
			}
		}

		$this->setUpConfigValidationResult($orig_field, $field_arr, $logic);
		
		if(isset(static::$ajd_prop['setUpFrom']) && !empty(static::$ajd_prop['setUpFrom']))
		{
			$setUpFrom = static::$ajd_prop['setUpFrom'];

			if(isset(static::$ajd_prop['js_rule'][$setUpFrom]) && !empty(static::$ajd_prop['js_rule'][$setUpFrom]))
			{
				if(isset(static::$ajd_prop['js_rule'][$orig_field]))
				{
					static::$ajd_prop['js_rule'][$orig_field] = array_merge(static::$ajd_prop['js_rule'][$setUpFrom], static::$ajd_prop['js_rule'][$orig_field]);	
				}
				else
				{
					static::$ajd_prop['js_rule'][$orig_field] = static::$ajd_prop['js_rule'][$setUpFrom];	
				}
			}
		}

		if($fiberize)
		{
			if(
				isset(static::$ajd_prop['fields'])
				&& !empty(static::$ajd_prop['fields'])
			)
			{
				if(
					isset( static::$ajd_prop['fields'][Abstract_common::LOG_AND][$field][Abstract_common::LOG_AND] )
					&& 
					!empty(static::$ajd_prop['fields'][Abstract_common::LOG_AND][$field][Abstract_common::LOG_AND] )
				)
				{
					static::$ajd_prop[Abstract_common::LOG_AND] = static::$ajd_prop['fields'][Abstract_common::LOG_AND][$field][Abstract_common::LOG_AND];
					unset(static::$ajd_prop['fields'][Abstract_common::LOG_AND][$field]);
				}

				if(
					isset( static::$ajd_prop['fields'][Abstract_common::LOG_OR][$field][Abstract_common::LOG_AND] )
					&& 
					!empty(static::$ajd_prop['fields'][Abstract_common::LOG_OR][$field][Abstract_common::LOG_AND] )
				)
				{
					static::$ajd_prop[Abstract_common::LOG_AND] = static::$ajd_prop['fields'][Abstract_common::LOG_AND][$field][Abstract_common::LOG_AND];
					unset(static::$ajd_prop['fields'][Abstract_common::LOG_OR][$field]);
				}

				if(
					isset( static::$ajd_prop['fields'][Abstract_common::LOG_OR][$field][Abstract_common::LOG_OR] )
					&& 
					!empty(static::$ajd_prop['fields'][Abstract_common::LOG_OR][$field][Abstract_common::LOG_OR] )
				)
				{
					static::$ajd_prop[Abstract_common::LOG_OR] = static::$ajd_prop['fields'][Abstract_common::LOG_OR][$field][Abstract_common::LOG_OR];
					unset(static::$ajd_prop['fields'][Abstract_common::LOG_OR][$field]);
				}

				if(
					isset( static::$ajd_prop['fields'][Abstract_common::LOG_AND][$field][Abstract_common::LOG_OR] )
					&& 
					!empty(static::$ajd_prop['fields'][Abstract_common::LOG_AND][$field] )
				)
				{
					static::$ajd_prop[Abstract_common::LOG_OR] = static::$ajd_prop['fields'][Abstract_common::LOG_AND][$field][Abstract_common::LOG_OR];
					unset(static::$ajd_prop['fields'][Abstract_common::LOG_AND][$field]);
				}
			}
		}
		
		// if( $logic == Abstract_common::LOG_AND )
		// {
			$prop_and = static::process_check_args( Abstract_common::LOG_AND, $group );
			// $prop 		= $prop_and;
		// }
        
		/*if( $logic == Abstract_common::LOG_OR )
		{*/
			$prop_or = static::process_check_args( Abstract_common::LOG_OR, $group );
			// $prop 		= $prop_or;
		// }
		
		$prop = $prop_and;
		$obs = static::get_observable_instance();
		$ev = $promise;

		if(empty($ev))
		{
			$ev = static::get_promise_validator_instance(false);	
		}
		
		$auto_arr = ( is_array( $value ) && $check_arr );

		$propScene = $this->clearScenario( $prop_and, $prop_or, $prop );

		$prop = $propScene['prop'];
		$prop_and = $propScene['prop_and'];
		$prop_or = $propScene['prop_or'];

		$this->plotValidationResult($orig_field, $prop_and, $prop_or);

		$rePropAnd = $this->processExtendRule($prop_and, $field, $value, $field_arr, $check_arr, $fieldIsArray);
		$rePropOor = $this->processExtendRule($prop_or, $field, $value, $field_arr, $check_arr, $fieldIsArray);
		$reClearScene = false;

		if($rePropAnd['alter'])
		{
			$reClearScene = true;
			$prop_and = $rePropAnd['props'];
			$prop_and = static::process_check_args( Abstract_common::LOG_AND, $group );	
			$prop = $prop_and;
		}

		if($rePropOor['alter'])
		{
			$reClearScene = true;
			$prop_or = $rePropOor['props'];
			$prop_or = static::process_check_args( Abstract_common::LOG_OR, $group );
		}

		if($reClearScene)
		{
			$propScene = $this->clearScenario( $prop_and, $prop_or, $prop );
		}

		$prop = $propScene['prop'];
		$prop_and = $propScene['prop_and'];
		$prop_or = $propScene['prop_or'];


		$preValidate = [];

		if(!empty($rePropOr['preValidate']))
		{
			$preValidate = $rePropOr['preValidate'];
		}

		if(!empty($rePropAnd['preValidate']))
		{
			$preValidate = $rePropAnd['preValidate'];
		}

		$value = $preValidate['value'] ?? $value;
		$check_arr = $preValidate['check_arr'] ?? $check_arr;

		if(isset($preValidate['field']) && !empty($preValidate['field']))
		{
			$newFieldArr = $this->format_field_name($preValidate['field']);	

			$field_arr['clean'] = $newFieldArr['clean'];
			$clean_field = $newFieldArr['clean'];
		}

		$validateGroupings = [];
		
		if(
			is_array(static::$ajd_prop['groupings'])
			&&
			Array_helper::isAssoc(static::$ajd_prop['groupings'])
			&& 
			isset(static::$ajd_prop['groupings'][$orig_field])
		)
		{
			$validateGroupings = static::$ajd_prop['groupings'][$orig_field];
		}
		else
		{
			if(
				( 
					!is_array(static::$ajd_prop['groupings'])
					||
					!Array_helper::isAssoc(static::$ajd_prop['groupings'])
				)
				&&
				(
					is_array(static::$ajd_prop['groupings'])
					&&
					!isset(static::$ajd_prop['groupings'][$orig_field])
				)
				
			)
			{
				$validateGroupings = static::$ajd_prop['groupings'];
			}
			else
			{
				$validateGroupings = static::$ajd_prop['groupings'];
			}
		}

		if(!empty($validateGroupings))
		{
			if(

				!is_array($validateGroupings)
				||
				!Array_helper::isAssoc($validateGroupings)

			)
			{
				if(!isset(static::$ajd_prop['cache_groupings'][$orig_field]))
				{
					static::$ajd_prop['cache_groupings'][$orig_field] = $validateGroupings;		
				}
				
			}
			else
			{
				if(!isset(static::$ajd_prop['cache_groupings'][$orig_field]))
				{
					static::$ajd_prop['cache_groupings'][$orig_field] = $this->processGroupingsArray(static::$ajd_prop['cache_groupings'], $orig_field);
				}
			
			}
			
			if(isset(static::$ajd_prop['cache_groupings'][$orig_field]))
			{
				$validateGroupings = static::$ajd_prop['cache_groupings'][$orig_field];
			}
			
			$validateGroupings = $this->processGroupingsArray($validateGroupings, $orig_field);
			
			if(!empty($validateGroupings))
			{
				$validateGroupings = [
					$orig_field => $validateGroupings
				];
				
				$this->useGroupings($validateGroupings, null, $orig_field);

				if(
					isset(static::$ajd_prop['groupings'][$orig_field])
					&& !empty(static::$ajd_prop['groupings'][$orig_field])
				)
				{
					$validateGroupings = static::$ajd_prop['groupings'][$orig_field];
				}
			}
		}

		$prop['validateGroupings'] = $validateGroupings;
		$prop_and['validateGroupings'] = $validateGroupings;
		$prop_or['validateGroupings'] = $validateGroupings;
		
		$and_search = [];
		$or_search = [];
		
		if( !empty( $prop['scenarios'] ) 
			|| !empty( $prop_or['scenarios'] )
		)
		{
			if( ISSET( $prop['scenarios'] ) )
			{
				$and_search = $this->array_search_recursive( $field, $prop['scenarios'] );
			}

			if( ISSET( $prop_or['scenarios'] ) )
			{
				$or_search = $this->array_search_recursive( $field, $prop_or['scenarios'] );
			}
			
			if( !empty( $and_search ) || !empty( $or_search ) )
			{
				return;
			}
		}

		$extra_args = [];
		$check_logic = [
			Abstract_common::LOG_AND => [
				'passed' => [],
				'pass_arr' => [],
				'arr_keys' => []
			],
			Abstract_common::LOG_OR => [
				'passed' => [],
				'pass_arr' => [],
				'arr_keys' => []
			]
		];

		$real_value_before_filter = null;

		if( !EMPTY( $prop['filters'] ) )
		{
			$real_value_before_filter = $value;
			
			static::handle_filter( $prop['filters'], $real_value_before_filter, $field, $prop['filter_satis'], $prop['pre_filters'], $check_arr, true, true );

			$filt_value = static::pre_filter_value($field_arr['orig']);

			if(in_array(1, $prop['pre_filters']))
			{
				$value = ( isset( $filt_value ) ) ? $filt_value : $value;
			}
			else
			{
				$value = ( isset( $filt_value ) && !empty( $filt_value ) ) ? $filt_value : $value;	
			}
		}

		if( empty( $origValue ) )
		{
			$origValue = (!is_null($real_value_before_filter)) ? $real_value_before_filter : $value;
		}

		if( !empty( $origValue ) && !empty( $prop['filters'] ) )
		{
			$origValue = $this->processFilterOrigValue( $prop['filters'], $origValue, $field, $prop['filter_satis'], $prop['pre_filters'], $check_arr );
		}

		if( $auto_arr )
		{
			$value = $this->flattened_array( $value );
		}

		$obs->attach_observer( 'endgiven', array( $this, 'endCondition' ) );

		$sometimes_and_arguments = [];
		$sometimes_or_arguments = [];
		
		if( !empty( $prop_and['rules'] ))
		{   
			$sometimes_and = null;
			$sometime_and_result = true;

			$sometimes_or = null;
			$sometime_or_result = true;

			if(!empty($group))
			{
				if(isset($group[Abstract_common::LOG_AND]['sometimes'][$field_arr['orig']]))
				{
					$sometimes_and = $group[Abstract_common::LOG_AND]['sometimes'][$field_arr['orig']];
				}

				if(isset($group[Abstract_common::LOG_OR]['sometimes'][$field_arr['orig']]))
				{
					$sometimes_or = $group[Abstract_common::LOG_OR]['sometimes'][$field_arr['orig']];
				}

				if(isset($group[Abstract_common::LOG_AND]['sometimes_arguments'][$field_arr['orig']]))
				{
					$sometimes_and_arguments = $group[Abstract_common::LOG_AND]['sometimes_arguments'][$field_arr['orig']];
				}

				if(isset($group[Abstract_common::LOG_OR]['sometimes_arguments'][$field_arr['orig']]))
				{
					$sometimes_or_arguments = $group[Abstract_common::LOG_OR]['sometimes_arguments'][$field_arr['orig']];
				}

				$defaultExpressionPlaceholders = [
					'orig_field' => $orig_field,
					'valueKey' => 0
				];
			}

			if( $auto_arr )
			{
				$check_logic[ Abstract_common::LOG_AND ]['auto_arr_result'] = true;
				
				foreach( $value as $k_value => $v_value ) 
				{
					if(!empty($sometimes_and))
					{
						if($sometimes_and instanceof Validator)
						{
							$sometime_and_result = $sometimes_and->validate($v_value);
						}
						else if($sometimes_and instanceof Logics_map)
						{
							$sometime_and_result = $sometimes_and->deferToWhen()->runLogics($v_value, [], false);
						}
						else if( is_callable( $sometimes_and ) )
						{
							$sometime_and_result = $this->invoke_func( $sometimes_and, [ $v_value, $field_arr['orig'], $k_value, $group, $origValue ] );
						}
						else if(is_bool($sometimes_and))
						{
							$sometime_and_result = $sometimes_and;
						}
						else if( $sometimes_and == Abstract_common::SOMETIMES )
						{
							$sometime_and_result = !empty( $v_value );
						}
						else if( is_string($sometimes_and) )
						{
							$defaultExpressionPlaceholders['valueKey'] = $k_value;
							$sometimes_and = $this->processExpressionAddtionalPlaceholder(
								$sometimes_and, 
								$defaultExpressionPlaceholders
							);

							$sometime_and_result = $this->processExpression($sometimes_and, $sometimes_and_arguments, $cacheOrigValue, $prop_and);
						}
						else
						{
							$sometime_and_result = true;	
						}
					}

					if(!empty($sometimes_or))
					{
						if( $sometimes_or instanceof Validator )
						{
							$sometime_or_result = $sometimes_or->validate($v_value);
						}
						else if($sometimes_or instanceof Logics_map)
						{
							$sometime_or_result = $sometimes_or->deferToWhen()->runLogics($v_value, [], false);
						}
						else if( is_callable( $sometimes_or ) )
						{
							$sometime_or_result = $this->invoke_func( $sometimes_or, [ $v_value, $field_arr['orig'], $k_value, $group, $origValue ] );
						}
						else if(is_bool($sometimes_or))
						{
							$sometime_or_result = $sometimes_or;
						}
						else if( $sometimes_or == Abstract_common::SOMETIMES )
						{
							$sometime_or_result = !empty( $v_value );
						}
						else if( is_string($sometimes_or) )
						{
							$defaultExpressionPlaceholders['valueKey'] = $k_value;
							$sometimes_or = $this->processExpressionAddtionalPlaceholder(
								$sometimes_or, 
								$defaultExpressionPlaceholders
							);

							$sometime_or_result = $this->processExpression($sometimes_or, $sometimes_or_arguments, $cacheOrigValue, $prop_and);
						}
						else
						{
							$sometime_or_result = true;	
						}
					}

					if($sometime_and_result && $sometime_or_result)
					{
						if(!empty($prop_and['rules']))
						{
							$prop = $this->processValidateGroupings($validateGroupings, $prop);

							if(
								isset(static::$ajd_prop['groupings'][$orig_field])
								&&
								!empty(static::$ajd_prop['groupings'][$orig_field])
								&& !$dontResetGrouping
							)
							{
								static::$ajd_prop['cache_groupings'][$orig_field] = static::$ajd_prop['groupings'][$orig_field];
							}

							$check_logic[ Abstract_common::LOG_AND ][] = $this->_process_and_or_check( $prop, $field, $field_arr, $v_value, $auto_arr, $extra_args, $group, $logic, $k_value, $origValue, $promise, $cacheOrigValue );

							if(
								isset(static::$ajd_prop['cache_groupings'][$orig_field])
								&&
								static::$ajd_prop['cache_groupings'][$orig_field] instanceof Grouping_sequence_interface
								&& !$dontResetGrouping
							)
							{
								$groupArr = [
									$orig_field => $this->createGroupSequence(static::$ajd_prop['cache_groupings'][$orig_field]->sequence())
								];
								$this->useGroupings($groupArr, null, $orig_field);	
							}
						}
					}
				}

				foreach( $check_logic[ Abstract_common::LOG_AND ] as $k_and => $and )
				{
					if( !empty( $and['passed'] ) )
					{
						foreach( $and['passed'] as $pass )
						{
							$cp_pass[] = $pass;

							$check_logic[ Abstract_common::LOG_AND ][ 'passed' ][] = $pass;

							$check_logic[ Abstract_common::LOG_AND ][ 'passed_field' ][$field_arr['orig']][] = $pass;
						}
					}
					
					if( !empty( $and['pass_arr'] ) )
					{
						foreach( $and['pass_arr'] as $rule => $pass_arr )
						{  
							$check_logic[ Abstract_common::LOG_AND ][ 'pass_arr' ][ $k_and ][ $rule ] = $pass_arr;
						}
					}

					if( !empty( $and['passed_or'] ) )
					{
						foreach( $and['passed_or'] as $rule => $pass_arr )
						{  
							$check_logic[ Abstract_common::LOG_AND ][ 'passed_or' ][ $k_and ][ $rule ] = $pass_arr;

							$check_logic[ Abstract_common::LOG_AND ][ 'passed_field_or' ][$field_arr['orig']][ $k_and ][ $rule ] = $pass_arr;
						}
					}

					if( !empty( $and['sequence_check'] ) )
					{
						foreach( $and['sequence_check'] as $rule => $pass_arr )
						{  
							$check_logic[ Abstract_common::LOG_AND ][ 'sequence_check' ][ $k_and ][ $rule ] = $pass_arr;
						}
					}

					if( !empty( $and['passed_per'] ) )
					{
						foreach( $and['passed_per'] as $ruleKey => $pass_arr )
						{  
							$check_logic[ Abstract_common::LOG_AND ][ 'passed_per' ][ $k_and ][$ruleKey] = $pass_arr;
						}
					}

					if( isset( $and['passed'] ) )
					{
						$check_logic[ Abstract_common::LOG_AND ][ 'arr_keys' ][ $k_and ] = $and['passed'];
					}

					unset( $check_logic[ Abstract_common::LOG_AND ][ $k_and ] );
				}

				$check_logic[ Abstract_common::LOG_AND ]['auto_arr_result'] = true;
			}
			else 
			{
				if(!empty($sometimes_and))
				{
					if( $sometimes_and instanceof Validator )
					{
						$sometime_and_result = $sometimes_and->validate($value);
					}
					else if($sometimes_and instanceof Logics_map)
					{
						$sometime_and_result = $sometimes_and->deferToWhen()->runLogics($value, [], false);
					}
					else if( is_callable( $sometimes_and ) )
					{
						$sometime_and_result = $this->invoke_func( $sometimes_and, [ $value, $field_arr['orig'], null, $group, $origValue ] );
					}
					else if(is_bool($sometimes_and))
					{
						$sometime_and_result = $sometimes_and;
					}
					else if( $sometimes_and == Abstract_common::SOMETIMES )
					{
						$sometime_and_result = !empty( $value );
					}
					else if( is_string($sometimes_and) )
					{
						$sometimes_and = $this->processExpressionAddtionalPlaceholder(
							$sometimes_and, 
							$defaultExpressionPlaceholders
						);

						$sometime_and_result = $this->processExpression($sometimes_and, $sometimes_and_arguments, $cacheOrigValue, $prop_and);
					}
					else
					{
						$sometime_and_result = true;	
					}
				}

				if(!empty($sometimes_or))
				{
					if($sometimes_or instanceof Validator )
					{
						$sometime_or_result = $sometimes_or->validate($value);
					}
					else if($sometimes_or instanceof Logics_map)
					{
						$sometime_or_result = $sometimes_or->deferToWhen()->runLogics($value, [], false);
					}
					else if( is_callable( $sometimes_or ) )
					{
						$sometime_or_result = $this->invoke_func( $sometimes_or, [ $value, $field_arr['orig'], null, $group, $origValue ] );
					}
					else if(is_bool($sometimes_or))
					{
						$sometime_or_result = $sometimes_or;
					}
					else if( $sometimes_or == Abstract_common::SOMETIMES )
					{
						$sometime_or_result = !empty( $value );
					}
					else if( is_string($sometimes_or) )
					{
						$sometimes_or = $this->processExpressionAddtionalPlaceholder(
							$sometimes_or, 
							$defaultExpressionPlaceholders
						);

						$sometime_or_result = $this->processExpression($sometimes_or, $sometimes_or_arguments, $cacheOrigValue, $prop_and);
					}
					else
					{
						$sometime_or_result = true;	
					}
				}

				if($sometime_and_result && $sometime_or_result)
				{
					if(!empty($prop_and['rules']))
					{
						$prop = $this->processValidateGroupings($validateGroupings, $prop);
						$check_logic[ Abstract_common::LOG_AND ] = $this->_process_and_or_check( $prop, $field, $field_arr, $value, $auto_arr, $extra_args, $group, $logic, null, $origValue, $promise, $cacheOrigValue );
					}

				}
			}
			
		}

		if( ( isset( $prop_or['rules'] ) && !empty( $prop_or['rules'] ) ) 
			/*AND
			( EMPTY( $check_logic[ Abstract_common::LOG_AND ]['passed'] ) OR in_array( 0, $check_logic[ Abstract_common::LOG_AND ]['passed'] ) )*/
		  )
		{
			if( $auto_arr )
			{
				$check_logic[ Abstract_common::LOG_OR ]['auto_arr_result'] = true;
				
				foreach( $value as $k_value => $v_value )
				{
					$prop_or = $this->processValidateGroupings($validateGroupings, $prop_or);

					if(
						isset(static::$ajd_prop['groupings'][$orig_field])
						&&
						!empty(static::$ajd_prop['groupings'][$orig_field])
						&& !$dontResetGrouping
					)
					{
						static::$ajd_prop['cache_groupings'][$orig_field] = static::$ajd_prop['groupings'][$orig_field];
					}

					$check_logic[ Abstract_common::LOG_OR ][] = $this->_process_and_or_check( $prop_or, $field, $field_arr, $v_value, $auto_arr, $extra_args, $group, $logic, $k_value, $origValue, $promise, $cacheOrigValue );

					if(
						isset(static::$ajd_prop['cache_groupings'][$orig_field])
						&&
						static::$ajd_prop['cache_groupings'][$orig_field] instanceof Grouping_sequence_interface
						&& !$dontResetGrouping
					)
					{
						$groupArr = [
							$orig_field => $this->createGroupSequence(static::$ajd_prop['cache_groupings'][$orig_field]->sequence())
						];

						$this->useGroupings($groupArr, null, $orig_field);	
					}				
				}

				foreach( $check_logic[ Abstract_common::LOG_OR ] as $k_or => $or )
				{   
					if( !empty( $or['passed'] ) )
					{
						foreach( $or['passed'] as $pass )
						{
							$check_logic[ Abstract_common::LOG_OR ][ 'passed' ][] = $pass;
						}
					}
					
					if( !empty( $or['pass_arr'] ) )
					{
						foreach( $or['pass_arr'] as $rule => $pass_arr )
						{
							$check_logic[ Abstract_common::LOG_OR ][ 'pass_arr' ][ $rule ] = $pass_arr;
						}
					}

					if( !empty( $or['passed_or'] ) )
					{
						foreach( $or['passed_or'] as $rule => $pass_arr )
						{
							$check_logic[ Abstract_common::LOG_OR ][ 'passed_or' ][ $rule ] = $pass_arr;
						}
					}

					if( !empty( $or['sequence_check'] ) )
					{
						foreach( $or['sequence_check'] as $rule => $pass_arr )
						{
							$check_logic[ Abstract_common::LOG_OR ][ 'sequence_check' ][$k_or][ $rule ] = $pass_arr;
						}
					}

					if( !empty( $or['passed_per'] ) )
					{
						foreach( $or['passed_per'] as $ruleKey => $pass_arr )
						{  
							$check_logic[ Abstract_common::LOG_OR ][ 'passed_per' ][ $k_or ][$ruleKey] = $pass_arr;
						}
					}

					if( isset( $or['passed'] ) )
					{
						if( !empty( $check_logic[ Abstract_common::LOG_AND ][ 'arr_keys' ] ) && !empty( $or['passed'] ) )
						{
							if( !in_array( 0, $check_logic[ Abstract_common::LOG_AND ][ 'arr_keys' ][ $k_or ] ) || !in_array( 0, $or['passed'] ) )
							{
								if( !empty( static::$ajd_prop['message'][ $field ] ) )
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

				if( empty( static::$ajd_prop['message'][ $field ] ) )
				{
					unset( static::$ajd_prop['message'][ $field ] );
				}
				
				$check_logic[ Abstract_common::LOG_OR ]['auto_arr_result'] = true;
			}
			else 
			{
				$prop_or = $this->processValidateGroupings($validateGroupings, $prop_or);
				
				$check_logic[ Abstract_common::LOG_OR ] = $this->_process_and_or_check( $prop_or, $field, $field_arr, $value, $auto_arr, $extra_args, $group, $logic, NULL, $origValue, $promise, $cacheOrigValue );		
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

		if( empty( $group ) && !$dontReset )
		{
			$obs->attach_observer( $orig_field.'-|passed', $ev, [ $this ] );
			$obs->attach_observer( $orig_field.'-|fails', $ev, [ $this ] );
			
			$obs->attach_observer( $orig_field.'-|customEvent', [ $ev, 'customEvent' ], [ $obs, $this, $field ] );

			$obs->notify_observer($orig_field.'-|customEvent');
			
			if( !$this->validation_fails( $orig_field ) ) 
			{
				$obs->notify_observer( $orig_field.'-|passed' );
			}

			if( $this->validation_fails( $orig_field ) ) 
			{
				$obs->notify_observer( $orig_field.'-|fails' );
			}
		}

		$this->validationResult[$orig_field]['valid'] = true;

		if( $this->validation_fails( $orig_field ) ) 
		{
			$this->validationResult[$orig_field]['valid'] = false;
		}

		if(
			!empty($ev)
			&& $ev instanceof PromiseValidator
		)
		{
			$ev->setValidationResult($this->validationResult);
			$this->resetValidationResult();
		}
		
		if( !empty( $group ) )
		{
			return $check_logic;
		}
		else 
		{
			if( !$dontReset )
			{
				if($fiberize)
				{
					$this->reset_validation_selected_prop(
						['current_rule', 'current_field', 'and_or_stack', 'given_values']
					);
				}
				else
				{
					// $this->resetFiberize($field);
					$this->reset_all_validation_prop();	
				}
			}

			return $ev;
		}
	}


	protected function processFilterOrigValue( $filters, $origValue, $field, $filterSatis, $preFilters, $check_arr )
	{
		if( is_array( $origValue ) && $check_arr )
		{
			$newArr = [];
			
			foreach( $origValue as $key => $val )
			{
				if( is_array( $val ) )
				{
					$newDetail = $this->processFilterOrigValue($filters, $val, $field, $filterSatis, $preFilters, $check_arr);

					$newArr[$key] = $newDetail;
				}
				else
				{
					$newVal = static::handle_filter( $filters, $val, $field, $filterSatis, $preFilters, $check_arr, true, true );
				}
			}

			return $newArr;
		}
		else
		{
			$newVal = static::handle_filter( $filters, $origValue, $field, $filterSatis, $preFilters, $check_arr, true, true );

			return $newVal;
		}
	}

	protected function clearScenario( array $prop_and, array $prop_or = [], array $prop = [] ) 
	{
		if( !EMPTY( static::$remove_scenario ) )
		{
			static::$remove_scenario = array_unique( static::$remove_scenario );
			
			foreach( static::$remove_scenario as $scene )
			{	
				if( !empty( $prop ) )
				{
					if( isset( $prop['scenarios'][$scene] ) )
					{
						unset( $prop['scenarios'][$scene] );
					}
				}

				if( isset( $prop_and['scenarios'][$scene] ) )
				{
					unset( $prop_and['scenarios'][$scene] );
				}

				if( !empty( $prop_or ) )
				{
					if( isset( $prop_or['scenarios'][$scene] ) )
					{
						unset( $prop_or['scenarios'][$scene] );
					}
				}
			}
		}

		return [
			'prop' => $prop,
			'prop_and' => $prop_and,
			'prop_or' => $prop_or
		];	
	}

	private function _process_and_or_check( $prop, $field, $field_arr, $value, $auto_arr, $extra_args, $group, $logic, $key = null, $origValue = NULL, $promise = null, $cacheOrigValue = null )
	{	
		return call_user_func_array([$this, 'fiberize'], func_get_args());
	}

	private function _refactored_process_and_or_check($prop, $field, $field_arr, $value, $auto_arr, $extra_args, $group, $logic, $key = null, $origValue = null, $promise = null, $cacheOrigValue = null)
	{
		$params = func_get_args();
		$check_arr = [];
		$or_pass_arr = [];
		$countErr = 0;

		// $params[] = true;
		
		$fiberize = ( in_array(static::$fiberRule, array_values($prop['rules']), true ) );

		$global_fiberize = static::$ajd_prop['global_fiberize'];
		
		$fiberized 	= ($fiberize || $global_fiberize);

		$obs = static::get_observable_instance();
		$ev = $promise;

		if(empty($ev))
		{
			$ev = static::get_promise_validator_instance();
		}

		$check_arr_det 	= [];
		$rulesInSeq = [];
		$newSeq = (isset($prop['newSeq'])) ? $prop['newSeq'] : [];
		$seqValidateGroupings = [];

		foreach( $prop['rules'] as $rule_key => $rule_value )
		{
			$validateGroupings = [];

			if(
				is_array(static::$ajd_prop['groupings'])
				&&
				isset(static::$ajd_prop['groupings'][$field_arr['orig']])
			)
			{
				$validateGroupings = static::$ajd_prop['groupings'][$field_arr['orig']];
			}

			$prop['validateGroupings'] = $validateGroupings;
			
			$firstSeq = null;

			if(!empty($validateGroupings)
				&& $validateGroupings instanceof Grouping_sequence_interface
			)
			{
				$seqValidateGroupings = $validateGroupings->sequence();

				$firstSeq = reset($seqValidateGroupings);

				if(isset($prop['groups']) && !empty($prop['groups']))
				{
					if(isset($prop['groups'][$rule_value][$rule_key]))
					{
						$groupings_per = $prop['groups'][$rule_value][$rule_key];
						
						if(!in_array($firstSeq, $groupings_per))
						{
							break;
						}
					}
				}
			}

			$newSeqCh = ( !empty($firstSeq) && isset($newSeq[$firstSeq])) ? $newSeq[$firstSeq] : [];
			
			$paramaters = [
				$rule_key,
				$rule_value,
				$check_arr,
				$or_pass_arr,
				$countErr,
				$firstSeq,
				$rulesInSeq,
				$seqValidateGroupings
			];
			
			$paramaters = array_merge($paramaters, $params);

			if( !empty( $prop['scenarios'] ) )
			{
				$check_scena = $this->array_search_recursive( $rule_key.'|+'.$rule_value, $prop['scenarios'] );
				
				if( !empty( $check_scena ) ) continue;
			}

			$realValKey = is_null($key) ? 0 : $key;

			if(class_exists('Fiber') && $fiberized)
			{
				$paramaters[] = true;
				$paramaters[] = $cacheOrigValue;
				
				$fiber = new \Fiber([$this, '_refactor_fiber_process_and_or_check']);

				if(
					isset(static::$ajd_prop['fiber_suspend'][$rule_value][$rule_key])
					&&
					!empty(static::$ajd_prop['fiber_suspend'][$rule_value][$rule_key])
				)
				{
					$fiber_ajd_prop['fibers'][$field][$rule_value][$rule_key][$realValKey] = [
						'fiber' => $fiber,
						'paramaters' => $paramaters,
						'rule' => $rule_value,
						'field' => $field,
						'rule_key' => $rule_key
					];
				}

				$val = [];

				if(!$fiber->isStarted())
				{
					$val = call_user_func_array([$fiber, 'start'], $paramaters);
				}

				if(
					isset(static::$ajd_prop['fiber_suspend'][$rule_value][$rule_key])
					&&
					!empty(static::$ajd_prop['fiber_suspend'][$rule_value][$rule_key])
				)
				{

					$fiber_ajd_prop['fibers'][$field][$rule_value][$rule_key][$realValKey]['fiber_suspend_val'] = $val;
					
					$obs->attach_observer( $rule_value.'_'.$rule_key.'_'.$realValKey.'_'.$field.'-|fiber', $ev, [$this, $fiber_ajd_prop['fibers'], $rule_value, $field, $realValKey] );
					$obs->notify_observer( $rule_value.'_'.$rule_key.'_'.$realValKey.'_'.$field.'-|fiber' );
				}

				if($fiber->isTerminated())
				{
					$check_arr_det = $fiber->getReturn();
				}
			}
			else
			{
				if(
					isset(static::$ajd_prop['fiber_suspend'][$rule_value])
					&&
					!empty(static::$ajd_prop['fiber_suspend'][$rule_value])
				)
				{
					$obs->attach_observer( $rule_value.'_'.$field.'-|fiber', $ev, array( $this, [] ) );
					$obs->notify_observer( $rule_value.'_'.$field.'-|fiber' );
				}

				$paramaters[] = $cacheOrigValue;
				$check_arr_det = call_user_func_array([$this, '_refactor_fiber_process_and_or_check'], $paramaters);
			}

			if(!empty($check_arr_det))
			{
				$pass_arr = $check_arr_det['pass_arr'];
				$or_pass_arr = $check_arr_det['or_pass_arr'];
				$check_arr = array_merge($check_arr, $check_arr_det['check_arr']);

				if(static::$bail)
				{
					if(in_array(0, $check_arr_det['check_arr']['passed']))
					{
						break;
					}
				}

				if(isset($prop['stop_on_error'][$rule_value][$rule_key]) && !empty($prop['stop_on_error'][$rule_value][$rule_key]))
				{
					if(in_array(0, $check_arr_det['check_arr']['passed']))
					{
						break;
					}
				}
				
				if(!empty($firstSeq))
				{
					if(isset($check_arr['sequence_check'][$firstSeq]))
					{
						if(!empty($newSeqCh))
						{
							$resSeq = $check_arr['sequence_check'][$firstSeq][$field_arr['orig']];
							
							$cntS = count($resSeq);

							$checkOther = false;

							if(count($newSeqCh) == $cntS)
							{
								$arrayKeysS = array_keys($resSeq);
								$endRule = end($arrayKeysS);

								if($endRule == $rule_value)
								{
									if(!is_null($resSeq[$rule_value][$rule_key]) && in_array(0, $resSeq[$rule_value]))
									{
										break;
									}	
									else
									{
										$checkOther = true;
									}
								}

								$chArr = [];
								foreach($resSeq as $chS)
								{
									if(in_array(0, $chS))
									{
										$chArr[] = true;
									}
								}
								
								if(in_array(1, $chArr))
								{
									break;
								}

								if($check_arr['orig_field'] == $field_arr['orig'])
								{
									$firstSeq = $check_arr['firstSeq'];
									$seqValidateGroupings = $check_arr['seqValidateGroupings'];
									
									$seqArr = [
										$field_arr['orig'] => $this->createGroupSequence($seqValidateGroupings)
									];

									$this->useGroupings($seqArr, null, $field_arr['orig']);
								}
							}
						}
					}
				}
			}
		}
		

		if( $prop['logic'] == Abstract_common::LOG_OR )
		{
			if( isset( $check_arr['pass_arr'][$rule_value][2][0] ) )
			{
				$pass_arr['cus_err'] = $check_arr['pass_arr'][$rule_value][2][0][$rule_value];
				$prop['cus_err'] = $check_arr['pass_arr'][$rule_value][2][0][$rule_value];
			}

			if( !in_array( 1, $check_arr['passed'] ) )
			{
				// $pass_arr['rule'] 		= current( $prop['rules'] );
				
				foreach( $or_pass_arr as $rule_key => $or_pass )
				{
					$cus_err = [];
					$append_err = [];

					if(
						isset($or_pass['pass_arr'][2][0][$or_pass['rule']][$rule_key])
					)
					{
						$cus_err = $or_pass['pass_arr'][2][0][$or_pass['rule']][$rule_key];
					}
					
					if(
						isset($or_pass['pass_arr'][3][0][$or_pass['rule']][$rule_key])
					)
					{
						$append_err = $or_pass['pass_arr'][3][0][$or_pass['rule']][$rule_key];
					}

					$or_pass['cus_err'] = $cus_err;
					$or_pass['append_error'] = $append_err;

					if( $this->check_cond ) 
					{
						$this->handle_errors( $or_pass, $auto_arr, $key );
					}
				}
			}
		}
		
		return $check_arr;
	}

	private function _refactor_fiber_process_and_or_check($rule_key, $rule_value, array $check_arr, array $or_pass_arr, $countErr, $firstSeq, $rulesInSeq, $seqValidateGroupings, $prop, $field, $field_arr, $value, $auto_arr, $extra_args, $group, $logic, $key = null, $origValue = null, $fibered = false, $cacheOrigValue = null)
	{
		$pass_arr = [];

		$satisfier = $prop['satisfier'][ $rule_key ];
		$details = $prop['details'][ $rule_key ];

		$sometimes_arguments = [];
		
		if(isset($prop['sometimes_arguments'][ $rule_value ][$rule_key]))
		{
			$sometimes_arguments = $prop['sometimes_arguments'][ $rule_value ][$rule_key];	
		}
		else
		{
			$sometimes_arguments = $prop['sometimes_arguments'][ $rule_value ] ?? [];		
		}

		$sometimes_arguments['value'] = $value;

		if(isset($prop['sometimes'][ $rule_value ][$rule_key]))
		{
			$sometimes = $prop['sometimes'][ $rule_value ][$rule_key];	
		}
		else
		{
			$sometimes = $prop['sometimes'][ $rule_value ];		
		}
		
		$groupings = (isset($prop['groups'][$rule_value])) ? $prop['groups'][$rule_value] : null;
		$validateGroupings = (isset($prop['validateGroupings'])) ? $prop['validateGroupings'] : null;
		
		$pass_arr['rule'] = $rule_value;
		$pass_arr['satisfier'] = $satisfier;
		$pass_arr['no_error_message'] = (isset($prop['no_error_message'])) ? $prop['no_error_message'] : null;
		$pass_arr['field'] = $field;
		$pass_arr['details'] = $details;
		$pass_arr['value'] = $value;
		$pass_arr['validateGroupings'] = $validateGroupings;
		$pass_arr['cus_err'] = ( isset( $prop['cus_err'][$rule_key] ) ) ? $prop['cus_err'][$rule_key] : [];
		$pass_arr['clean_field'] = $field_arr['clean'];
		$pass_arr['orig_field'] = $field_arr['orig'];
		$pass_arr['logic'] = $prop['logic'];
		$pass_arr['field_logic'] = $logic;
		$pass_arr['origValue'] = $origValue;
		$pass_arr['fibered'] = $fibered;
		$pass_arr['groups'] = $groupings;

		$or_pass_arr[$rule_key]['rule'] = $rule_value;
		$or_pass_arr[$rule_key]['satisfier'] = $satisfier;
		$or_pass_arr[$rule_key]['field'] = $field;
		$or_pass_arr[$rule_key]['details'] = $details;
		$or_pass_arr[$rule_key]['value'] = $value;
		$or_pass_arr[$rule_key]['cus_err'] = ( isset( $prop['cus_err'][$rule_key] ) ) ? $prop['cus_err'][$rule_key] : [];
		$or_pass_arr[$rule_key]['clean_field'] = $field_arr['clean'];
		$or_pass_arr[$rule_key]['orig_field'] = $field_arr['orig'];
		$or_pass_arr[$rule_key]['logic'] = $prop['logic'];
		$or_pass_arr[$rule_key]['field_logic'] = $logic;
		$or_pass_arr[$rule_key]['fibered'] = $fibered;
		
		if( $sometimes instanceof Validator )
		{
			$sometimes = $sometimes->validate($pass_arr['value']);
		}
		else if( $sometimes instanceof Logics_map )
		{
			$sometimes = $sometimes->deferToWhen()->runLogics($pass_arr['value'], [], false);
		}
		else if( is_callable( $sometimes ) )
		{
			$sometimes = $this->invoke_func( $sometimes, [ $pass_arr['value'], $pass_arr['satisfier'], $pass_arr['orig_field'], $key, $pass_arr['origValue'] ] );
		}
		else if(is_bool($sometimes))
		{
			$sometimes = $sometimes;
		}
		else if( $sometimes == Abstract_common::SOMETIMES)
		{
			$sometimes = !empty( $pass_arr['value'] );
		}
		else if( is_string($sometimes) )
		{
			$sometimes = $this->processExpressionAddtionalPlaceholder(
				$sometimes, 
				[
					'valueKey' => $key ?? 0,
					'orig_field' => $field_arr['orig'] ?? ''
				]
			);

			$sometimes = $this->processExpression($sometimes, $sometimes_arguments, $cacheOrigValue, $prop);
		}
		else 
		{
			$sometimes = true;
		}

		$pass_arr['sometimes'] = $sometimes;
		$or_pass_arr[$rule_key]['sometimes'] = $sometimes;
		
		$checkGenerator = $this->_process_validate( $pass_arr, $auto_arr, $extra_args, $key, $countErr, $rule_key, $firstSeq, $rulesInSeq, $seqValidateGroupings, $cacheOrigValue );

		$checkGenerator->current();
		$valueKey = $key ?? 0;

		if(isset( static::$generators[$details[1]][$details[5]] ) && !empty(static::$generators[$details[1]][$details[5]]) )
		{
			static::$generators[$details[1]][$details[5]]($checkGenerator, $this, $details, $pass_arr);	

			$this->cleanUpLogic($origValue, $valueKey, 'unset', [ static::$generators[$details[1]][$details[5]] ]);
		}

		$checkGenerator->next();
		$check = $checkGenerator->getReturn();

		if( !$check['passed'][0] )
		{
			$countErr++;
		}

		$current_f_field = $check['orig_field'];

		$check_arr['orig_field'] = $current_f_field;
		
		if(isset($firstSeq) && !empty($firstSeq))
		{
			if(!empty($current_f_field))
			{
				$check_arr['sequence_check'][$firstSeq][$current_f_field][$rule_value][$rule_key] = $check['sequence_check'];			
			}
			else
			{
				$check_arr['sequence_check'][$firstSeq][$rule_value][$rule_key] = $check['sequence_check'];			
			}
		}
		else
		{
			if(!empty($current_f_field))
			{
				$check_arr['sequence_check'][$current_f_field][$rule_value][$rule_key] = $check['sequence_check'];			
			}
			else
			{
				$check_arr['sequence_check'][$rule_value][$rule_key] = $check['sequence_check'];				
			}
		}

		$check_arr['firstSeq'] = $check['firstSeq'];
		$check_arr['seqValidateGroupings'] = $check['seqValidateGroupings'];
		$check_arr['rulesInSeq'] = $check['rulesInSeq'];

		$check_arr['passed_or'][$rule_value][$rule_key] = $check['passed'][0];
		$check_arr['passed'][] = $check['passed'][0];

		$check_arr['pass_arr'][ $rule_value ][$rule_key] = $check['pass_arr'];

		$check_arr['passed_per'][$rule_key]['rule_name'] = $rule_value;
		$check_arr['passed_per'][$rule_key]['details'] = $check['passed_per'][$rule_key];
		
		$or_pass_arr[$rule_key]['pass_arr'] = $check_arr['pass_arr'][ $rule_value ][$rule_key];
		
		return [
			'check_arr' => $check_arr,
			'or_pass_arr' => $or_pass_arr,
			'pass_arr' => $pass_arr
		];
	}

	public function processExpressionAddtionalPlaceholder($expression, array $placeholders = [])
	{
		if(!empty($expression))
		{
			$expression = static::replaceExrpressionPlaceholder(
				$placeholders,
				$expression,
				false
			);
		}

		return $expression;
	}

	public static function replaceExrpressionPlaceholder(array $expressionPlaceholders, $expression, $jsonEncode = true)
	{
		$newExpression = Errors::formatError( $expressionPlaceholders, $expression, '/{(\w+)}/', $jsonEncode );
        $newExpression = Errors::formatError( $expressionPlaceholders, $newExpression, '/:(\w+)/', $jsonEncode );

        return $newExpression;
	}

	public function processExpression($expressionString, array $arguments = [], $cacheOrigValue = null, array $prop = [])
	{
		if(!empty($expressionString))
		{
			if(!is_array($cacheOrigValue))
			{
				$cacheOrigValue = [];	
			}
			
			$cacheOrigValue = array_merge($cacheOrigValue, $arguments);

			$expression = static::getExpression($cacheOrigValue);

			try
			{
				$evaluate = $expression->evaluate($expressionString);	
				
				if(!is_bool($evaluate))
				{
					$result = false;
				}
				else
				{
					$result = $evaluate;
				}
			}
			catch(\Exception $e)
			{
				$result = false;
			}
		}
		else
		{
			$result = false;
		}

		return $result;
	}
	
	private function fiberize()
	{
		$paramaters = func_get_args();

		return call_user_func_array([$this, '_refactored_process_and_or_check'], $paramaters);
	}

	public static function pre_filter_value( $key = NULL )
	{
		$filter = static::get_filter_ins();
		
		return $filter->get_pre_filter_value( $key );
	}

	public static function filter_value( $key = NULL )
	{
		$filter = static::get_filter_ins();

		return $filter->get_filtered_value( $key );
	}

	public static function get_error( $rule )
	{
		$err = static::get_errors_instance();

		return $err->get_error( $rule );
	}

	public static function errors()
	{
		$err = static::get_errors_instance();

		return $err->set_validation_errors( static::$ajd_prop['message'] );
	}

	public static function getPropMessage()
	{
		return static::$ajd_prop['message'];
	}

	public static function setPropMessage($message)
	{
		static::$ajd_prop['message'] = $message;
	}

	public static function toStringErr( $msg = [] )
	{
		$err = static::get_errors_instance();

		return $err->toStringErr( $msg );
	}

	public static function setLang( $lang )
	{
		static::$lang = $lang;
	}

	public static function validation_fails( $key = null, $err_key = null, $when = false )
	{
		$count_var = ( $when ) ? static::$ajd_prop['result'] : static::$ajd_prop['message'];

		$check = count( $count_var ) ? true : false;

		if( !empty( $key ) ) 
		{
			if( isset( $count_var[ $key ] ) )
			{
				if( $count_var[ $key ] instanceof \Countable )
				{
					$check = ( !empty( $count_var[ $key ] ) && count( $count_var[ $key ] ) ) ? true : false;
				}
				else
				{
					$check = ( isset( $count_var[ $key ] ) && !empty( $count_var[ $key ] ) ) ? true : false;
				}
			}
			else
			{
				/*if( $when OR $arrCheck )
				{*/
					$check = false;
				// }
			}

			if( !is_null( $err_key ) ) 
			{
				$check = ( !empty( $count_var[ $key ][ $err_key ] ) && count( $count_var[ $key ][ $err_key ] ) ) ? true : false;
			}
		}

		return $check;
	}

	public static function db( $connection = null, $user = null, $pass = null, $options = array() )
	{
		return new Database( $connection, $user, $pass, $options );
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

	protected static function init_extensions($onlyMacro = false)
	{ 
		if( static::$ajd_prop['extensions_initialize'] 
			|| empty( static::$ajd_prop['extensions'] ) 
		  )
		{
			return;
		}

		if(!$onlyMacro)
		{
			static::$ajd_prop['extensions_initialize'] 	= true;
			static::$ajd_prop['extension_rule'] = [];
			static::$ajd_prop['extension_filter'] = [];
			static::$ajd_prop['extension_test'] = [];
			static::$ajd_prop['extension_anonymous_class'] = [];
		}

		foreach( static::$ajd_prop['extensions'] as $name => $extension )
		{
			static::init_extension( $extension, $name, $onlyMacro );
		}
	}


	protected static function init_extension( $extension, $name, $onlyMacro = false )
	{
		if(!$onlyMacro)
		{
			$ruleMessages = $extension->getRuleMessages();

			foreach( $extension->getRules() as $rule )
			{
				$messages = $ruleMessages[$rule] ?? '';

				if(!is_array($messages))
				{
					$messages = ['default' => $messages];
				}

				static::registerMethod($rule, $extension, $messages);
				// static::$ajd_prop['extension_rule'][$rule ] 		= array( 'rule' => $rule, 'extension_name' => $name );
			}

			foreach( $extension->getFilters() as $filter )
			{
				static::$ajd_prop['extension_filter'][ $filter ] = ['filter' => $filter, 'extension_name' => $name, 'extension_obj' => $extension];
			}

			foreach( $extension->getLogics() as $test )
			{
				static::$ajd_prop['extension_test'][ $test ] = ['test' => $test, 'extension_name' => $name, 'extension_obj' => $extension];
			}

			foreach( $extension->getMiddleWares() as $name => $func )
			{	
				static::$middleware[ $name ][ 'func' ] = $func;
			}

			foreach( $extension->getClientSides() as $ruleName => $configs )
			{
				if(!isset($configs['clientSide']) || empty($configs['clientSide']))
				{
					continue;
				}

				$specificField = null;

				if(isset($configs['field']) && !empty($configs['field']))
				{
					$specificField = $configs['field'];
				}

				static::registerCustomClientSide($ruleName, $configs['clientSide'], $specificField);
			}

			$anons = $extension->getAnonClass();

			if(!empty($anons))
			{
				static::registerAnonClass($anons);
			}
		}

		foreach( $extension->getMacros() as $macro )
		{	
			if (!static::hasMacro($macro)
				&& method_exists($extension, $macro)
			) 
			{
				$macroValue = $extension->{$macro}();

				if(is_callable($macroValue))
				{
					static::macro($macro, $macroValue);
				}
			}
		}
	}

	protected static function handle_filter( $filter, $value, $field, $satisfier, $pre_filter, $check_arr, $val_only = false, $append = false )
	{
		$filter_ins = static::get_filter_ins();
		$ajd = static::get_ajd_instance();

		$extension_filter = static::$ajd_prop['extension_filter'];

		$filter = ( $ajd->isset_empty( $filter ) ) ? $filter : null;
		$satisfier = ( $ajd->isset_empty( $satisfier ) ) ? $satisfier : null;
		$pre_filter = ( $ajd->isset_empty( $pre_filter ) ) ? $pre_filter : [];

		$filter_ins->set_filter( $filter, $value, $field, $satisfier, $pre_filter, $extension_filter, $append );

		$real_val = $filter_ins->filter( $check_arr, $val_only );
		
		if( $val_only )
		{
			return $real_val;
		}
	}

	protected static function process_method_name( $name )
	{
		$ret_name = $name;

		if( isset( static::$macros[ $name ] ) )
		{
			$method = 'macro';
		}
		else if( preg_match( '/^S/', $name ) )
		{
			$method = 'superRule';
			$ret_name = static::removeWord( $name, '/^S/' );
		}
		else if( preg_match( '/^F/', $name ) )
		{
			$method = 'addFilter';
			$ret_name = static::removeWord( $name, '/^F/' );
		}
		else if( preg_match( '/^eS/' , $name ) )
		{
			$method = 'endSuperRule';
			$ret_name = static::removeWord( $name, '/^eS/' );
		}
		else if( preg_match('/^oR/', $name ) )
		{
			$method = 'addOrRule';
			$ret_name = static::removeWord( $name, '/^oR/' );	
		}
		else if( preg_match('/^Not/', $name ) )
		{
			$method = 'addRule';
			$ret_name = static::removeWord( $name, '/^Not/' );
			$ret_name = '!'.$ret_name;
		}
		else if( preg_match('/^Lg/', $name ) )
		{
			$method = 'addMainLogic';
			$ret_name = static::removeWord( $name, '/^Lg/' );
		}
		else 
		{
			$method = 'addRule';
		}

		return [
			'method' => $method,
			'name' => $ret_name
		];
	}

	public function addMainLogic($test, ...$args)
	{
		$when = $this->when(true);

		return $when->addLogic($test, ...$args);
	}

	protected static function process_check_args( $logic, $group )
	{
		$ret_args = [];
		$key_arr = static::get_ajd_and_or_prop();
	
		if( static::$ajd_prop['check_group'] )
		{  
			foreach ( $key_arr as $prop ) 
			{
				if( isset( $group[ $logic ][ $prop ] ) )
				{
					$ret_args[ $prop ] = $group[ $logic ][ $prop ];
				}
			}

			if(isset($group[ $logic ]['no_error_message']))
			{
				$ret_args['no_error_message'] = $group[ $logic ]['no_error_message'];
			}

			if( !EMPTY( $ret_args ) )
			{
				$ret_args['logic'] = $logic;
			}
		}
		else
		{
			foreach ( $key_arr as $prop ) 
			{
				$ret_args[ $prop ] = static::$ajd_prop[ $logic ][ $prop ];
			}

			if( !EMPTY( $ret_args ) )
			{
				$ret_args['logic'] = $logic;
			}
		}

		return $ret_args;
	}

	protected static function get_ajd_and_or_prop()
	{
		return ['rules', 'details', 'satisfier', 'cus_err', 'filters', 'filter_satis', 'pre_filters', 'scenarios', 'sometimes', 'sometimes_arguments', 'stop_on_error', 'groups'];
	}

	protected function reset_validation_prop( $key, $sub_key = null )
	{
		$and_or = [Abstract_common::LOG_AND, Abstract_common::LOG_OR];	
		$and_or_arr = static::get_ajd_and_or_prop();

		if( in_array( $key, $and_or ) )
		{
			foreach ( $and_or_arr as $prop ) 
			{
				static::$ajd_prop[ $key ][ $prop ] = [];
			}
		}
		else 
		{
			if( is_array( static::$ajd_prop[ $key ] ) )
			{
				static::$ajd_prop[ $key ] = [];
			}
			else if( in_array( static::$ajd_prop[ $key ], $and_or ) )
			{
				static::$ajd_prop[ $key ] = Abstract_common::LOG_AND;
			}
			else 
			{
				static::$ajd_prop[ $key ] = null;
			}
		}
	}

	public function resetMessage()
	{	
		static::$ajd_prop['message'] = [];
	}

	protected function reset_validation_selected_prop($pass_properties = [], $given_prop_only = false)
	{
		$common_prop = [
			'fields', 'current_rule', 'current_field', 'and_or_stack', 'given_values'
		];

		if(empty($pass_properties))
		{
			$properties = $common_prop;	
		}
		else
		{
			if(is_array($pass_properties))
			{
				$properties = $pass_properties;	
			}
			else
			{
				$properties = $common_prop;
			}
		}

		$and_or = static::get_ajd_and_or_prop();

		foreach( $properties as $prop )
		{
			if( is_array( static::$ajd_prop[ $prop ] ) )
			{
			 	static::$ajd_prop[ $prop ] = [];
			}
			else 
			{
				static::$ajd_prop[ $prop ] = null;	
			}
		}

		if(!$given_prop_only)
		{
			foreach( $and_or as $prop )
			{
				static::$ajd_prop[ Abstract_common::LOG_AND ][ $prop ] = [];
				static::$ajd_prop[ Abstract_common::LOG_OR ][ $prop ] = [];
			}
		}
	}

	protected function resetValidationResult()
	{
		$this->validationResult = [];
	}

	protected function reset_all_validation_prop($rest_prop = [])
	{
		$this->reset_validation_selected_prop($rest_prop);

		$this->reset_validation_prop( 'events' );
		$this->reset_validation_prop( 'fibers' );
		$this->reset_validation_prop( 'fiber_suspend' );
		$this->reset_validation_prop( 'fiber_events' );
		$this->reset_validation_prop( 'current_logic' );
		
		$this->resetBail();

		static::$ajd_prop['makeAsync'] = false;
		static::$ajd_prop['groupings'] = null;
		static::$ajd_prop['grouping_queue'] = null;
		static::$ajd_prop['cache_groupings'] = null;	
		static::$ajd_prop['setUpFrom'] = null;
		static::$cacheSceneInstance = [];
		static::$generators = [];

		$this->resetDataSet();
		$this->resetValidationResult();

		if(!empty(static::$removeSpecificDataSetRegistry))
		{
			static::clearDataSetRegistry(static::$removeSpecificDataSetRegistry);	
		}

		if(static::$flushDataSetRegistry)
		{
			static::clearDataSetRegistry();
		}

		static::$flushDataSetRegistry = false;
		static::$removeSpecificDataSetRegistry = [];

		static::$currentObj = null;
		static::$currentObjString = null;
		
		// $filter_ins = static::get_filter_ins();
	}

	protected function resetDataSet()
	{
		static::$customDataSets['dataSets'] = [];
		static::$customDataSets['inverse'] = [];
		static::$customDataSets['options'] = [];
	}

	protected function resetBail()
	{
		static::$bail = false;
	}

	protected function reset_check_group()
	{
		static::$ajd_prop[ 'check_group' ] = false;
	}

	protected function reset_current_field()
	{
		static::$ajd_prop[ 'current_field' ] = null;
	}

	protected function invoke_func( $func, $args = array() )
	{
		return $this->invoke_function( $func, $args );
	}

	protected function get_rules_path()
	{
		$this->rules_path = dirname( __FILE__ ).Abstract_common::DS.'Rules'.Abstract_common::DS;

		return $this->rules_path;
	}

	protected static function get_errors_instance( $lang = NULL, $singleton = true ) 
	{
		return parent::get_errors_instance( static::$lang, $singleton );
	}

	public function processValidateGroupings($validateGroupings, array $prop = [], $rearrangeGroup = true)
	{
		$cacheCheckRules = [];

		if(!empty($validateGroupings)
			&& $validateGroupings instanceof Grouping_sequence_interface
		)
		{
			$seqValidateGroupings = $validateGroupings->sequence();

			if(isset($prop['groups']) && !empty($prop['groups']))
			{
				$groupKey = 0;
				$newRules = [];
				$newDetails = [];
				$newSatis = [];
				$newCusErr = [];
				$newGroups = [];
				$rkseq = 0;

				foreach ($seqValidateGroupings as $kseq => $vseq) 
				{
					foreach ($prop['groups'] as $rule => $groupings)
					{
						foreach($groupings as $ruleKey => $grouping)
						{
							$ruleDetails = $prop['details'][$ruleKey];

							$ruleNames = static::remove_appended_rule($ruleDetails[4]);
							$propSatis = $prop['satisfier'][$ruleKey];

							$cusErr = (isset($prop['cus_err'][$ruleKey])) ? $prop['cus_err'][$ruleKey] : null;
							$groups = (isset($prop['groups'][$ruleNames])) ? $prop['groups'][$ruleNames] : [];

							$continueAdd = false;
						
							if(in_array($vseq, $grouping, true))
							{	
								if(!empty($prop['rules']))
								{
									if(in_array($rule, $prop['rules'], true))
									{
										$cntProp = array_count_values($prop['rules']);
										$cacheCheckRules[] = $rule;

										$cacheCheckRulesWhere = array_count_values($cacheCheckRules);

										$cacheCheckRulesWhere = Array_helper::where($cacheCheckRulesWhere, function($value, $key)
										{
											return $value > 1;
										});

										$addRule = true;

										if(!empty($cacheCheckRulesWhere))
										{
											if(isset($cacheCheckRulesWhere[$rule]))
											{
												if($cntProp[$rule] != $cacheCheckRulesWhere[$rule] )
												{
													$addRule = false;
												}
											}
										}

										if($addRule)
										{
											$continueAdd = true;
											$newRules[$kseq][$rkseq] = $rule;
										}
									}
								}
								
								if(
									$rule == $ruleNames
									&& $continueAdd
								)
								{
									$newDetails[$kseq][$rkseq] = $ruleDetails;
									$newSatis[$kseq][$rkseq] = $propSatis;

									if(!empty($cusErr))
									{
										$newCusErr[$kseq][$rkseq] = $cusErr;
									}

									if(!empty($groups)
										&& $rearrangeGroup
									)
									{
										$newGroups[$kseq][$rkseq] = $groups[$ruleKey];
									}
								}
								
								$rkseq++;
							}
						}						
					}

					$groupKey++;
				}

				if(!empty($newRules))
				{
					$newRules = Array_helper::flatten($newRules, 1);
					ksort($newRules);
					$prop['rules'] = $newRules;
				}

				if(!empty($newDetails))
				{
					$newDetails = Array_helper::flatten($newDetails, 1);
					ksort($newDetails);
					$prop['details'] = $newDetails;
				}

				if(!empty($newSatis))
				{
					$newSatis = Array_helper::flatten($newSatis, 1);
					ksort($newSatis);
					$prop['satisfier'] = $newSatis;
				}

				if(!empty($newCusErr))
				{
					$newCusErr = Array_helper::flatten($newCusErr, 1, true);
					ksort($newCusErr);
					$prop['cus_err'] = $newCusErr;
				}

				if(!empty($newGroups)
					&& $rearrangeGroup
				)
				{
					$newGroups = Array_helper::flatten($newGroups, 1);
					ksort($newGroups);

					$newContGroup = [];
					$newSeq = [];
					
					foreach($prop['rules'] as $k => $rule)
					{
						$newContGroup[$rule][$k] = $newGroups[$k];
						
						foreach($seqValidateGroupings as $seq)
						{
							if(in_array($seq, $newGroups[$k], true))
							{
								$newSeq[$seq][$k] = $rule;
							}
						}
					}
					
					$prop['newSeq'] = $newSeq;
					$prop['groups'] = $newContGroup;
				}
			}

		}

		return $prop;
	}

	protected function handle_errors( $details, $check_arr, $key = NULL, $singleton = true, $useRuleObj = false )
	{
		$cus_err = $details['cus_err'];
		$append_err = ( ISSET( $details['append_error'] ) ) ? $details['append_error'] : [];

		$err = static::get_errors_instance($singleton);

		$formatter = $details['formatter'][ $details['rule'] ][$details['details'][5]] ?? null;
		
		$errors = $err->get_errors();

		$called_class = ( isset( $details['details'][1] ) ) ? $details['details'][1] : null;
		$rule_instance = static::$cache_instance;

		$inverse = $details['details'][0];

		$rule_obj = null;

		if(
			$useRuleObj
			&& isset($details['rule_obj'])
			&& !empty($details['rule_obj'])
		)
		{
			$rule_obj = $details['rule_obj'];
		}

		$extraOptions = [
			'cus_err' => $cus_err[ $details['rule'] ] ?? '',
			'valueKey' => $key,
			'clean_field' => $details['clean_field'],
			'orig_field' => $details['orig_field']
		];

		$errors = $err->processExceptions( $details['rule'], $called_class, $rule_instance, $details['satisfier'], $details['value'], $inverse, $errors, $rule_obj, $formatter, $extraOptions );

		if(!empty($formatter))
		{
			$cus_err = [];
		}
		
		$errors = $this->format_errors( $details['rule'], $details['details'][1], $details['clean_field'], $details['value'], $details['satisfier'], $errors['errors'], $cus_err, $check_arr, $err, $key, $append_err, $inverse );
		
		$this->append_error_msg( $errors, $details['orig_field'], $details['clean_field'], $details['rule'], $check_arr, $key );	

		if(isset($this->validationResult[$details['orig_field']]))
		{
			$realKey = (!empty($key)) ? $key : 0;
			$this->validationResult[$details['orig_field']]['errors'][$details['rule']][$realKey] = $errors;		
		}
		
	}

	public function append_error_msg( $errors, $field = null, $clean_field = null, $rules_name = null, $check_arr = false, $key = null )
	{
		$valArr = [
			'errors' => $errors,
			'clean_field' => $clean_field
		];

		if( isset( $field ) ) 
		{
			if( isset( $rules_name ) ) 
			{
				if( $check_arr ) 
				{
					static::$ajd_prop['message'][ $field ][ $rules_name ][ $key ][]	= $valArr;
				} 
				else 
				{
					static::$ajd_prop['message'][ $field ][ $rules_name ][] = $valArr;
				}
			} 
			else 
			{
				static::$ajd_prop['message'][ $field ][] = $valArr;
			}
		} 
		else 
		{
			static::$ajd_prop['message'][] = $valArr;
		}
	}

	private function _runEvents(array $events, $value, $field, $checkForField = false)
	{
		try
		{
			$ob = static::get_observable_instance();
			$args = array($value, $field);

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

	private function _process_validate( $details, $check_arr, $extra_args, $key = NULL, $countErr = 0, $rule_key = null, $firstSeq = null, $rulesInSeq = null, $seqValidateGroupings = null, $cacheOrigValue = null )
	{
		$ob = static::get_observable_instance();
		$passed = true;

		$details['valueKey'] = $key ?? 0;
		$extra_args['pass_arr'] = [];
		$real_val = $details['value'];
		$details['append_error'][ $details['rule'] ] = '';
		$validateGroupings = $details['validateGroupings'];
		$details['cacheOrigValue'] = $cacheOrigValue;

		$sequence_check = null;
		$rule_obj = null;
		$runValidate = true;
		$orig_field = $details['orig_field'];

		$realKey = (!empty($key)) ? $key : 0;

		$this->validationResult[$orig_field]['value'][$realKey]  = $real_val;
		// static $countErr 			= 0;
		$key_load_event_common = $details['orig_field'].'-|'.$details['rule'];
		$key_load_event = $key_load_event_common;

		if( isset( static::$ajd_prop['events'][Abstract_common::EV_LOAD][$details['rule']][$rule_key] ) )
		{
			$eventLoad 	= static::$ajd_prop['events'][Abstract_common::EV_LOAD][$details['rule']][$rule_key];
			unset(static::$ajd_prop['events'][Abstract_common::EV_LOAD][$details['rule']][$rule_key]);
			$this->_runEvents($eventLoad, $details['value'], $details['orig_field'], true);
		}
		else if(
			isset( static::$ajd_prop['events'][Abstract_common::EV_LOAD][$key_load_event][$rule_key] ) 
		)
		{
			$eventLoad 	= static::$ajd_prop['events'][Abstract_common::EV_LOAD][$key_load_event][$rule_key];
			unset(static::$ajd_prop['events'][Abstract_common::EV_LOAD][$key_load_event][$rule_key]);
			$this->_runEvents($eventLoad, $details['value'], $details['orig_field'], true);
		}

		$checkRuleGroupings = [];
		
		if( $this->isset_empty( $details['details'], 2 ) )
		{ 
			if(!empty($validateGroupings)
				&& !$validateGroupings instanceof Grouping_sequence_interface
			)
			{
				if(
					isset($details['groups'])
					&& !empty($details['groups'])
				)
				{	
					if(isset($details['groups'][$rule_key]))
					{
						foreach($details['groups'][$rule_key] as $grouping)
						{
									
							if(in_array($grouping, $validateGroupings, true))
							{
								$checkRuleGroupings[] = true;
							}
						}
					}

					if(in_array(true, $checkRuleGroupings))
					{
						$runValidate = true;
					}
					else
					{
						$runValidate = false;
					}
					
				}
				else
				{
					$runValidate = false;
				}
			}

			if( $details['sometimes'] && $runValidate )
			{
				if(!empty(static::$ajd_prop['fiber_suspend']) 
					&&
					(
						isset($details['fibered'])
						&&
						!empty($details['fibered'])
						&&
						class_exists('Fiber')
						
					)
				)
				{

					if(
						isset(static::$ajd_prop['fiber_suspend'][$details['rule']][$rule_key])
						&&
						!empty(static::$ajd_prop['fiber_suspend'][$details['rule']][$rule_key])
					)
					{
						$fiberRule = \Fiber::getCurrent();

						if($fiberRule)
						{
							$suspend_val = $fiberRule::suspend($details);	
						}
						
						if(!empty($suspend_val))
						{
							if($suspend_val instanceof \Closure)
							{
								$suspend_val($extra_args);	
							}
							else
							{
								if($suspend_val)
								{
									$details = $suspend_val;
								}
							}
						}

						unset(static::$ajd_prop['fiber_suspend'][$details['rule']]);
					}
				}	

				$generatorSend = yield;
				
				if(is_array($generatorSend))
				{
					$details = array_merge($details, $generatorSend);
				}

				if( isset( $details['satisfier'][0] ) && !empty( $details['satisfier'][0] ) 
					&& is_callable( $details['satisfier'][0] )
					&& !$details['satisfier'][0] instanceof Validator
					&& !in_array( $details['rule'], static::$callbackRules )
				)
				{
					$ajd_ins = static::get_ajd_instance();
					$error_ins = static::get_errors_instance();

					$over_satis_arg = [ $details['value'], $details['satisfier'], $details['field'], $details['origValue'], $ajd_ins, $error_ins, $key ];

					$closure = call_user_func_array( $details['satisfier'][0], $over_satis_arg);
					
					static::$cache_instance[$details['details'][1]] = $details['satisfier'][0];

					$pass_check = $closure;
				}
				else
				{
					$pass_check = $this->{ $details['details'][2] }( $details );	

					$psc = $pass_check;

					$runValidate = false;
					
					if(!empty($validateGroupings)
						&& $validateGroupings instanceof Grouping_sequence_interface
					)
					{
						if(
							isset($details['groups'])
							&& !empty($details['groups'])
						)
						{
							if(isset($details['groups'][$rule_key]))
							{

								if(in_array($firstSeq, $details['groups'][$rule_key], true))
								{
									$runValidate = true;
									$rulesInSeq[] = $rule_key.'_'.$details['rule'];
								}
							}
						}

						if($runValidate)
						{
							if( is_array( $psc ) )
							{
								if($details['details'][2] == '_process_class')
								{
									if(is_array($psc['check']))
									{
										$sequence_check = $psc['check']['check'];
									}
									else
									{
										$sequence_check = $psc['check'];	
									}
								}
								else
								{
									$sequence_check = $psc['check'];	
								}	
							}
							else
							{
								$sequence_check = $psc;
							}

							if($sequence_check)
							{
								$keyFirstSeq = array_search($firstSeq, $seqValidateGroupings);

								unset($seqValidateGroupings[$keyFirstSeq]);

								$seqValidateGroupings = array_values($seqValidateGroupings);
								$firstSeq = reset($seqValidateGroupings);			
							}
						}
					}

					if(
						( $details['details'][2] == '_process_class' || $details['details'][2] == '_process_anon_class' )
						&& is_array($pass_check)
					)
					{
						$rule_obj = $pass_check['rule_obj'];
						
						$pass_check = $pass_check['check'];
					}
				}

				if( !is_array( $pass_check ) )
				{
					$passed = $pass_check;
				}
				else
				{
					$passed = $pass_check['check'];

					if( isset( $pass_check['msg'] ) && !empty( $pass_check['msg'] ) 
						&& !isset( $details['cus_err'][ $details['rule'] ] )
					)
					{
						$details['cus_err'][ $details['rule'] ] = $pass_check['msg'];
					}

					if( isset( $pass_check['append_error'] ) && !empty( $pass_check['append_error'] ) )
					{
						$details['append_error'][ $details['rule'] ] = $pass_check['append_error'];
					}

					if( isset( $pass_check['formatter'] ) && !empty( $pass_check['formatter'] ) )
					{
						$details['formatter'][ $details['rule'] ][$details['details'][5]] = $pass_check['formatter'];
					}

					if( isset( $pass_check['val'] ) )
					{
						$real_val = $pass_check['val'];

						$this->validationResult[$orig_field]['value'][$realKey]  = $real_val;
					}
				}
				
				if( isset( $this->customMesage[ $details['orig_field'] ] ) )
				{	
					$customMessage = $this->customMesage[ $details['orig_field'] ];

					if( is_array( $customMessage['message'] ) )
					{
						if( isset( $customMessage['message'][ $details['rule'] ] ) )
						{
							$details['cus_err'][ $details['rule'] ] = $customMessage['message'][ $details['rule'] ];
						}
					}
					else
					{
						$details['cus_err'][ $details['rule'] ] = $customMessage['message'];
					}
				}
			}
		}

		if( $this->isset_empty( $details['details'], 0 ) )
		{
			$passed = !$passed;
		}

		// $ob->attach_observer( 'endgiven', array( $this, 'end_condition' ) );
		
		if( !$passed )
		{  
			$key_event_fails = $key_load_event_common;
			if( ISSET( static::$ajd_prop['events'][Abstract_common::EV_FAILS][$details['rule']][$rule_key] ) 
			)
			{
				$eventFails = static::$ajd_prop['events'][Abstract_common::EV_FAILS][$details['rule']][$rule_key];

				unset(static::$ajd_prop['events'][Abstract_common::EV_FAILS][$details['rule']][$rule_key]);
				$this->_runEvents($eventFails, $details['value'], $details['orig_field'], TRUE);
			}
			else if(
				ISSET(
					static::$ajd_prop['events'][Abstract_common::EV_FAILS][$key_event_fails][$rule_key]
				)
			)
			{
				$eventFails = static::$ajd_prop['events'][Abstract_common::EV_FAILS][$key_event_fails][$rule_key];

				unset(static::$ajd_prop['events'][Abstract_common::EV_FAILS][$key_event_fails][$rule_key]);
				$this->_runEvents($eventFails, $details['value'], $details['orig_field'], TRUE);	
			}

			/*if( static::$bail )
			{
				if( $countErr != 0 )
				{
					return;
				}
			}*/
			
			static::$ajd_prop['result'][ $details['field'] ] = $details['details'][1];
			
			if( $details['logic'] == Abstract_common::LOG_AND && $details['field_logic'] == Abstract_common::LOG_AND )
			{
				if( $this->check_cond ) 
				{
					$showError = true;

					if(
						(
							is_array($pass_check)
							&& isset($pass_check['no_error_message'])
							&& !empty($pass_check['no_error_message'])
						)
						||
						(
							isset($details['no_error_message'][$details['rule']][$rule_key])
							&&
							!empty($details['no_error_message'][$details['rule']][$rule_key])
						)
					)
					{
						$showError = false;
					}
					
					if($showError)
					{
						$this->handle_errors( $details, $check_arr, $key );	
					}
					
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
				$extra_args['pass_arr'] 	= [ $details['rule'], $details['satisfier'],
												[
													[
														$details['rule'] => [
															$rule_key => $details['cus_err']
														]
													]
												],
												[
													[
														$details['rule'] => [
															$rule_key => $details['append_error']
														]
													]
												],
												[
													$details['rule'] => $rule_obj
												],
												[
													'rule_key' => $rule_key
												],
											 ];
			}

		}
		else
		{
			if( !empty( $key ) )
			{
				if( !empty($details['field']) 
					&&
					!empty( static::$ajd_prop['result_values'][ $details['field'] ] )
				)
				{
					if(isset(static::$ajd_prop['result_values'][ $details['field'] ][ $key ]))
					{
						if(is_array(static::$ajd_prop['result_values'][ $details['field'] ][ $key ]))
						{
							static::$ajd_prop['result_values'][ $details['field'] ][ $key ] = $real_val;
						}
					}
				}
			}
			else
			{
				static::$ajd_prop['result_values'][ $details['field'] ] = $real_val;
			}


			$key_load_sucess = $key_load_event_common;
			
			if( isset( static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$details['rule']][$rule_key] ) )
			{
				$eventSuccess 	= static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$details['rule']][$rule_key];

				unset(static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$details['rule']][$rule_key]);
				$this->_runEvents($eventSuccess, $details['value'], $details['orig_field'], true);
			}
			else if(
				isset( static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$key_load_sucess][$rule_key] )
			)
			{
				$eventSuccess 	= static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$key_load_sucess][$rule_key];

				unset(static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$key_load_sucess][$rule_key]);
				$this->_runEvents($eventSuccess, $details['value'], $details['orig_field'], true);
			}
		}

		$extra_args['passed'][] = $passed;
		
		if( $details['logic'] == Abstract_common::LOG_OR && $passed )
		{
			
			$extra_args['pass_arr'] 		= [ 1, $details['satisfier'],
												[
													[
														$details['rule'] => [
															$rule_key => $details['cus_err']
														]
													]
												],
												[
													[
														$details['rule'] => [
															$rule_key => $details['append_error']
														]
													]
												],
											 ];
		}

		$extra_args['passed_per'][$rule_key]['rule_name'] = $details['rule'];
		$extra_args['passed_per'][$rule_key]['passed'][] = $passed;

		$extra_args['passed_per'][$rule_key]['value'] = $details['value'];
		$extra_args['passed_per'][$rule_key]['rule_obj'] = $rule_obj;
		$extra_args['passed_per'][$rule_key]['satisfier'] = $details['satisfier'];
		$extra_args['passed_per'][$rule_key]['clean_field'] = $details['clean_field'];
		$extra_args['passed_per'][$rule_key]['rule_key'] = $rule_key;

		$extra_args['passed_per'][$rule_key]['field'] = $details['orig_field'];

		$extra_args['passed_per'][$rule_key]['sequence_check'] = [
			'firstSeq' => $firstSeq,
			'sequence_check' => $sequence_check,
			'rulesInSeq' => $rulesInSeq,
			'seqValidateGroupings' => $seqValidateGroupings
		];

		$extra_args['passed_per'][$rule_key]['cus_err'] = [
			$details['rule'] => [
				$rule_key => $details['cus_err']
			]
		];

		$extra_args['passed_per'][$rule_key]['append_error'] = [
			$details['rule'] => [
				$rule_key => $details['append_error']
			]
		];

		$extra_args['pass_arr']['values'][$details['rule']] = $details['value'];

		$extra_args['orig_field'] = $orig_field;
		$extra_args['firstSeq'] = $firstSeq;
		$extra_args['sequence_check'] = $sequence_check;
		$extra_args['rulesInSeq'] = $rulesInSeq;
		$extra_args['seqValidateGroupings'] = $seqValidateGroupings;
		$extra_args['valueKey'] = $details['valueKey'];

		$origValue = ( isset( $details['origValue'] ) ) ? $details['origValue'] : null;	
		
		$this->cleanUpLogic($origValue, $details['valueKey'] ?? 0, [$this, 'resetDataSet']);

		return $extra_args;
	}

	protected function cleanUpLogic($origValue, $valueKey, $reset, array $arguments = [])
	{
		$clear = true;

		if(!is_null($origValue) && is_array($origValue))
		{
			$origValueKeys = array_keys($origValue);
			$lastKey = end($origValueKeys);

			if($lastKey != $valueKey)
			{
				$clear = false;
			}
		}

		if($clear)
		{
			if(is_callable($reset))
			{
				call_user_func_array($reset, $arguments);	
			}
			
		}
	}

	public static function get_values()
	{
		return static::$ajd_prop['result_values'];
	}

	public static function whenInstance()
	{
		return static::get_ajd_instance()->when(true);
	}

	public function when($justInstance = false)
	{
		$ob = null;

		if(!$justInstance)
		{
			static::$ajd_prop['result'] = array();

			$ob = static::get_observable_instance();

			$ob->attach_observer( 'ongiven', array( $this, 'checkCondition' ) );
		}

		$when = new When( $this, $ob );

		return $when;
	}

	protected function checkCondition()
	{
		$this->check_cond = false;
	}

	protected function endCondition()
	{
		$this->check_cond = true;
	}

	protected function _process_extension( $details )
	{	
		$extension_rule = static::$ajd_prop[ 'extension_rule' ][ $details['details'][4] ];

		$extension_obj = static::$ajd_prop[ 'extensions' ][ $extension_rule[ 'extension_name' ] ];
		$origValue = ( isset( $details['origValue'] ) ) ? $details['origValue'] : null;
		$args = [
			$extension_rule['rule'], $details['value'], $details['satisfier'], $details['field'], $origValue
		];

		$args = array_merge( $args, static::$globalVar );

		$extension_result = call_user_func_array([ $extension_obj, 'runRules'], $args );

		return $extension_result;
	}

	protected function _process_anon_class($details)
	{
		$raw_append_rule = $details['details'][3]['raw_append_rule'];
		$append_rule = $details['details'][3]['append_rule'];
		$rule_details = $details['details'];
		$anon_obj = $rule_details[3]['anon_obj'];
		$exceptionObj = $rule_details[3]['anon_exception_obj'];
		$rule_obj = $anon_obj;
		$origValue = ( isset( $details['origValue'] ) ) ? $details['origValue'] : null;	
		$cacheOrigValue = $details['cacheOrigValue'] ?? null;

		if( isset( static::$cache_instance[ $append_rule ] ) && static::$cache_instance[ $append_rule ] instanceof \Closure )
		{
			unset( static::$cache_instance[ $append_rule ] );
		}
		
		static::$cache_instance[ $append_rule ] = $rule_obj;

		if(isset($details['orig_field']))
		{
			static::$cacheByFieldInstance[$details['orig_field']][$append_rule] = $rule_obj;
		}

		$check_r = false;

		Errors::addAnonExceptions($append_rule, $exceptionObj);

		$inverse = $details['details'][0];

		$details = $this->checkDontRunValidationIn($details, $rule_obj);

		if(!isset($details['dontRunValdidation']) || empty($details['dontRunValdidation']))
		{
			$inverse = $details['details'][0];

			if(property_exists($rule_obj, 'inverseCheck'))
			{
				$rule_obj->inverseCheck = $inverse;	
			}

			if(property_exists($rule_obj, 'calledAnonRule'))
			{
				$rule_obj->calledAnonRule = $details['rule'];	
			}

			$origValue = $cacheOrigValue;
			
			if($rule_obj instanceof Invokable_rule_interface)
			{
				$check_r = $rule_obj( $details['value'], $details['satisfier'], $details['field'], $details['clean_field'], $origValue, $inverse );
			}
			else
			{
				if(method_exists($rule_obj, 'run'))
				{
					$check_r = $rule_obj->run( $details['value'], $details['satisfier'], $details['field'], $details['clean_field'], $origValue, $inverse );
				}
			}
		}

		$check_r = $this->processAbstractRuleCustomErrorMessage($rule_obj, $check_r);

		return [
			'check' => $check_r,
			'rule_obj' => $rule_obj
		];
	}

	protected function _process_class( $details )
	{
		$append_rule = $details['details'][3]['raw_class'];
		$rule_details = $details['details'];
		$from_framework = $details['details'][3]['from_framework'];	
		$origValue = ( isset( $details['origValue'] ) ) ? $details['origValue'] : null;	
		$cacheOrigValue = $details['cacheOrigValue'] ?? null;

		if( isset( static::$cache_instance[ $append_rule ] ) && static::$cache_instance[ $append_rule ] instanceof \Closure )
		{
			unset( static::$cache_instance[ $append_rule ] );
		}
		
		if( !isset( static::$cacheSceneInstance[ $append_rule ][ $details['details'][5] ] ) )
		{
			$class_factory = static::get_factory_instance()->get_instance( true );

			if( $this->isset_null( $rule_details[3], 'namespace' ) )
			{
				static::addRuleNamespace( $rule_details[3]['namespace'] );
				// $class_factory->set_rules_namespace( array( $rule_details[3]['namespace'] ) );
			}

			if( !EMPTY( static::$addRuleNamespace ) )
			{
				$this->_appendRuleNameSpace( $class_factory );
			}
			
			$class_args = $details[ 'details' ][3][ 'class_args' ];
			
			$rule_obj = $class_factory->rules( $rule_details[3]['rules_path'], $append_rule, $class_args, false, static::$globalVar );
		}
		else
		{
			$rule_obj = static::$cacheSceneInstance[ $append_rule ][ $details['details'][5] ];

			$this->cleanUpLogic($origValue, $details['valueKey'] ?? 0, 'unset', [static::$cacheSceneInstance[ $append_rule ][ $details['details'][5] ]]);
		}
		
		static::$cache_instance[ $append_rule ] = $rule_obj;
		if(isset($details['orig_field']))
		{
			static::$cacheByFieldInstance[$details['orig_field']][$append_rule] = $rule_obj;
		}

		$check_r = false;

		$rule_obj->valueKey = $details['valueKey'] ?? 0;

		if(!$details['details'][3]['is_anon_class'])
		{
			$inverse = $details['details'][0];

			if(
				property_exists($rule_obj, 'inverseCheck')
				&& 
				$rule_obj instanceof AbstractRuleDataSet
			)
			{
				$rule_obj->inverseCheck = true;	
			}

			if($inverse)
			{
				if(property_exists($rule_obj, 'inverseCheck'))
				{
					if(
						$rule_obj instanceof Abstract_compound
						|| $rule_obj instanceof Abstract_sequential
					)
					{
						$rule_obj->inverseCheck = true;	
					}
				}
			}

			$details = $this->checkDontRunValidationIn($details, $rule_obj);

			if(!isset($details['dontRunValdidation']) || empty($details['dontRunValdidation']))
			{
				if($rule_obj instanceof Invokable_rule_interface)
				{
					$check_r = $rule_obj( $details['value'], $details['satisfier'], $details['field'], $details['clean_field'], $origValue );
				}
				else
				{
					if(method_exists($rule_obj, $details[ 'details' ][3][ 'class_meth_call' ]))
					{
						if($rule_obj instanceof ExpressionRuleInterface)
						{
							$origValue = $cacheOrigValue;
						}

						$check_r = $rule_obj->{ $details[ 'details' ][3][ 'class_meth_call' ] }( $details['value'], $details['satisfier'], $details['field'], $details['clean_field'], $origValue );
					}
				}
			}
		}

		$check_r = $this->processAbstractRuleCustomErrorMessage($rule_obj, $check_r);

		return [
			'check' => $check_r,
			'rule_obj' => $rule_obj
		];
	}

	protected function processAbstractRuleCustomErrorMessage($rule_obj, $check_r)
	{
		if(!$rule_obj)
		{
			return $check_r;
		}

		$check = $check_r;

		if(is_array($check_r))
		{
			$check = $check_r['check'];
		}

		if($check)
		{
			return $check_r;
		}

		$customError = $rule_obj->getCustomErrorMessage();
		
		if(empty($customError))
		{
			return $check_r;
		}

		$returnValue = $check_r;

		if(!is_array($check_r))
		{
			$returnValue = [];
			$returnValue['check'] = $check_r;
		}

		if(!empty($customError['appendError']))
		{
			if(!isset($returnValue['append_error']))
			{
				$returnValue['append_error'] = $customError['appendError'];
			}
			else
			{
				$returnValue['append_error'] = $returnValue['append_error'].' '.$customError['appendError'];	
			}
		}

		if(!empty($customError['overrideError']))
		{
			$returnValue['msg'] = $customError['overrideError'];
		}

		if(!empty($customError['formatter']))
		{
			$returnValue['formatter'] = $customError['formatter'];
		}

		return $returnValue;
	}

	protected function checkDontRunValidationIn($details, $rule_obj)
	{
		if(isset($details['dontRunValdidationIn']) && !empty($details['dontRunValdidationIn']))
		{
			foreach($details['dontRunValdidationIn'] as $class)
			{
				if($rule_obj instanceof $class)
				{
					$details['dontRunValdidation'] = true;
					break;
				}
			}
		}

		return $details;
	}

	private function _appendRuleNameSpace( $classFactory )
	{
		foreach( static::$addRuleNamespace as $ruleNamespace )
		{
			$classFactory->append_rules_namespace( $ruleNamespace );
		}
	}

	protected function _process_method( $details )
	{
		$method_args = [];

		$method_factory = static::get_factory_instance()->get_instance( false, false, true );

		$from_framework = $details['details'][3]['from_framework'];

		$rule = !empty( $details['details'][3]['meth_override'] ) ? $details['rule'] : $details['details'][4];
		$orig_rule = $rule;

		$args = $this->_process_method_args_for_framework( $from_framework, $details );
		$method_args = $args;

		$method = $method_factory->rules( $details['details'][3]['class_name'], $rule );
		
		$passed = $method_factory->process_method( $method_args, $details['details'][3]['obj_ins'], true );

		return $passed;

	}

	protected function _process_function( $details )
	{
		$passed 			= false;
		$funct_factory 		= static::get_factory_instance()->get_instance( false, true );

		if( $funct_factory->func_valid( $details['rule'] ) )
		{
			$inc_field 		= false;

			if( $this->isset_empty( $details['details'][3], 'func' ) )
			{
				$inc_field 	= true;
			}
			
			$func = $funct_factory->rules( $details['rule'], $details['details'][3] );

			$passed = $funct_factory->process_function( $details['field'], $details['value'], $details['satisfier'], FALSE, $inc_field );
		}

		return $passed;

	}

	private function _process_method_args_for_framework( $from_framework, $details )
	{
		$origValue = ( isset( $details['origValue'] ) ) ? $details['origValue'] : null;	

		$args = [
			$details['value'], $details['satisfier'], $details['field'], $origValue
		];

		return $args;
		
	}

	private function _process_rule_kind( $rule, $append_rule, $raw_rule, $satis )
	{
		$args = [];
		$lower_rule = strtolower( $append_rule );
		
		$options = $this->_process_overrides( $lower_rule, $append_rule, $raw_rule, $rule, $satis );

		$is_anon_class = false;

		if( is_string($options['rules_path']) && !is_object($options['rules_path']) )
		{
			$is_class = file_exists( $options['rules_path'] );	
		}
		else
		{
			$is_class = (!is_string($options['rules_path']) && is_object($options['rules_path']));
		}

		if(!$is_class)
		{
			if(!empty(static::$addRulesMappings))
			{
				if(isset(static::$addRulesMappings[$lower_rule]))
				{
					$is_class = true;
				}
			}
		}

		$is_method = method_exists( $options['obj_ins'], $append_rule );
		$is_function = function_exists( $rule );
		$is_extension = isset( static::$ajd_prop['extension_rule'][ $lower_rule ] );
		$satis = !empty( $satis ) ? $satis : array();
		$satis = !is_array( $satis ) ? array( $satis ) : $satis;

		if($is_method && $options['obj_ins'] == $this)
		{
			$is_method = false;
		}
		
		if(isset($options['is_anon_class']) && !empty($options['is_anon_class']))
		{
			$is_anon_class = $options['is_anon_class'];
		}
		
		$args['lower_rule'] = $lower_rule;
		$args['rule_kind'] = null;
		
		if( $is_extension )
		{
			$args['rule_kind'] = '_process_extension';
		}
		else if( $is_class && !$options['override'] && !$is_anon_class )
		{
			$args['rule_kind'] = '_process_class';
		}
		else if( ( $is_method || $options['meth_override'] ) && !$is_anon_class )
		{
			$args['rule_kind'] = '_process_method';
		}
		else if( ( $is_function || $options['func_override'] ) && !$is_anon_class )
		{
			$args['rule_kind'] = '_process_function';
		}
		else if($is_anon_class)
		{
			$args['rule_kind'] = '_process_anon_class';
		}
		else
		{
			$currentObj = static::$currentObj;
			
			if(!empty(static::$currentObjString))
			{
				if(is_string(static::$currentObjString) && class_exists(static::$currentObjString))
				{
					$currentObj = new static::$currentObjString;
				}
			}

			if(method_exists($currentObj, $lower_rule))
			{
				if(!static::hasAnonymousClass($lower_rule))
				{
					static::registerMethod($lower_rule, $currentObj, []);

					$options = $this->_process_overrides( $lower_rule, $append_rule, $raw_rule, $rule, $satis );

					$args['rule_kind'] = '_process_anon_class';
				}
			}
			else if(!empty(static::$ajd_prop['extensions']))
			{
				foreach(static::$ajd_prop['extensions'] as $name => $extension)
				{
					if(method_exists($extension, $lower_rule))
					{
						if(!static::hasAnonymousClass($lower_rule))
						{
							static::registerMethod($lower_rule, $extension, []);

							$options = $this->_process_overrides( $lower_rule, $append_rule, $raw_rule, $rule, $satis );

							$args['rule_kind'] = '_process_anon_class';

							break;
						}
					}
				}
			}
		}

		$args['args'] = $options;
		$args['args']['class_args'] = $satis;
		
		return $args;
	}

	private function _process_overrides( $lower_rule, $append_rule, $raw_rule, $rule )
	{
		$override = false;
		$function_override = false;
		$method_override = false;

		$from_framework = "";
		$args = array();
		$obj_ins = static::get_ajd_instance();
		$rules_path = $this->get_rules_path().$append_rule.'.php';

		$args['is_anon_class'] = false;
		$args['anon_obj'] = null;
		$args['anon_exception_obj'] = null;

		if( !EMPTY( static::$addRuleDirectory ) )
		{
			foreach( static::$addRuleDirectory as $classPath )
			{
				$pathHolder = $classPath.$append_rule.'.php';
				
				if( file_exists( $pathHolder ) )
				{
					$rules_path = $pathHolder;
				}	
			}
		}

		$raw_append_rule = $raw_rule.'_'.static::$rules_suffix;
		$class_meth_call = 'run';
		$raw_class = $append_rule;
		$symfony_args = null;

		if( $this->isset_empty( static::$ajd_prop['class_override'], $append_rule ) ||
			$this->isset_empty( static::$ajd_prop['class_override'], $raw_append_rule ) )
		{
			$from_framework = static::$ajd_prop[ 'class_override' ][ $raw_rule ][0];
			$class_rule = $append_rule;
			$class_meth_call = static::$ajd_prop[ 'class_override' ][ $raw_rule ][1];
			$class_name = $append_rule;
		
			$class = static::$ajd_prop['class_override'][ $class_rule ];

			if(is_string($class[0]) && !is_object($class[0]))
			{
				$rules_path = $class[0].Abstract_common::DS.$class_name.'.php';
			}
			else
			{
				$rules_path = $class[0];
			}

			$args['namespace'] = isset($class[1]) ? $class[1] : null;

		}
		else if( $this->isset_empty( static::$ajd_prop['method_override'], $lower_rule ) || 
				 $this->isset_empty( static::$ajd_prop['method_override'], $raw_append_rule ) )
		{ 
			$override = true;
			$from_framework = static::$ajd_prop['method_override'][ $raw_rule ];
			$method_rule = $lower_rule;

			$obj_ins = static::$ajd_prop['method_override'][ $method_rule ];
		}
		else if( $this->isset_empty( static::$ajd_prop['function_override'], $rule ) )
		{
			$function_override = true;
			$func = static::$ajd_prop['function_override'][ $rule ];
			$args['func'] = $func;
		}
		else if( $this->isset_empty( static::$ajd_prop['anonymous_class_override'], $append_rule ) ||
			$this->isset_empty( static::$ajd_prop['anonymous_class_override'], $raw_append_rule ) )
		{
			$anon_details = [];
			
			if(isset(static::$ajd_prop['anonymous_class_override'][$raw_append_rule]))
			{
				$anon_details =	static::$ajd_prop['anonymous_class_override'][$raw_append_rule];
			}
			else if(isset(static::$ajd_prop['anonymous_class_override'][$append_rule]))
			{
				$anon_details =	static::$ajd_prop['anonymous_class_override'][$append_rule];
			}

			if(!empty($anon_details))
			{
				$args['append_rule'] = $append_rule;
				$args['raw_append_rule'] = $raw_append_rule;
				$args['is_anon_class'] = true;
				$args['anon_obj'] = $anon_details['obj'];
				$args['anon_exception_obj'] = $anon_details['exception'];
			}
		}

		$args['override'] = $override;
		$args['meth_override'] = $method_override;
		$args['obj_ins'] = $obj_ins;

		$args['rules_path'] = $rules_path;
		$args['class_name'] = get_class( $obj_ins );
		$args['func_override'] = $function_override;
		$args['from_framework'] = $from_framework;
		$args['class_meth_call'] = $class_meth_call;
		$args['raw_class'] = $raw_class;
		$args['symfony_args'] = $symfony_args;
		
		return $args;
	}

	public static function addLangStubs($stubs)
	{
		if(file_exists($stubs))
		{
			static::$langStubs[] = $stubs;
		}
	}

	public static function getAddLangStubs()
	{
		return static::$langStubs;
	}

	public static function addLangDir($lang, $path, $create_write = false)
	{
		Errors::addLangDir($lang, $path, $create_write);
		static::$addLangDir[$lang] = $path;
		static::$createWriteLangDir[$lang] = $create_write;
	}

	public static function setGlobalFiberize($onOff = false)
	{
		static::$ajd_prop['global_fiberize'] = $onOff;

		return static::get_ajd_instance();
	}

	protected function processGroupingsArray($groupings, $field)
	{
		if(
			is_array($groupings)
			&& Array_helper::isAssoc($groupings)
			&& !isset(static::$ajd_prop['groupings'][$field])
		)
		{
			$groupings = Array_helper::flatten($groupings, 1);

			if(!empty($groupings))
			{
				if(
					is_array($groupings[0])
					|| $groupings[0] instanceof Grouping_sequence_interface
				)
				{
					$groupings = $groupings[0];	
				}
			}
		}

		return $groupings;
	}

	protected function forwardToCheck($func, $that, $field, $value, $check_arr, $logic, $group, $dontReset, $origValue, $dontResetGrouping, $formatField, $checkAsync, $target, $resolve, $reject, $fieldIsArray)
	{
		$target->setField($formatField['orig']);

		$promise = $target;

		if($checkAsync)
		{
			$promise = null;
		}
		
		$fiberize_check = $that->_fiberize_check($field, $value, $check_arr, $logic, $group, $dontReset, $origValue, $checkAsync, $promise, $dontResetGrouping, $fieldIsArray);

		try 
		{
			$that->setPromiseError($that, $formatField['orig'], $target);

			$resolve($func($that, $fiberize_check));
		}
	 	catch (\Throwable $exception) 
        {
            $reject($exception);
        } 
		finally 
        {
        	return $fiberize_check;
        }
	}

	protected function createCheckPromiseValidator($func, $that, $field, $value, $check_arr, $logic, $group, $dontReset, $origValue, $dontResetGrouping, $formatField, $checkAsync = false, $fieldIsArray = false)
	{
		return (static function() use ($func, $that, $field, $value, $check_arr, $logic, $group, $dontReset, $origValue, $dontResetGrouping, $formatField, $checkAsync, $fieldIsArray)
		{
			$mainFiber = null;

			$promise = new PromiseValidator(function(callable $resolve, callable $reject, $target) use ($func, $that, $field, $value, $check_arr, $logic, $group, $dontReset, $origValue, $dontResetGrouping, $formatField, $checkAsync, $fieldIsArray, &$mainFiber)
			{
				if($checkAsync)
				{
					$mainFiber = new \Fiber(function() use ($func, $resolve, $reject, $that, $field, $value, $check_arr, $logic, $group, $dontReset, $origValue, $dontResetGrouping, $formatField, $target, $checkAsync, $fieldIsArray, &$mainFiber)
					{
						\Fiber::suspend();

						return $that->forwardToCheck($func, $that, $field, $value, $check_arr, $logic, $group, $dontReset, $origValue, $dontResetGrouping, $formatField, $checkAsync, $target, $resolve, $reject, $fieldIsArray);
					});

					$target->setFiber($mainFiber);
				}
				else
				{
					return $that->forwardToCheck($func, $that, $field, $value, $check_arr, $logic, $group, $dontReset, $origValue, $dontResetGrouping, $formatField, $checkAsync, $target, $resolve, $reject, $fieldIsArray);
				}
			},
			function () use (&$mainFiber) 
			{
        		if (\method_exists($target, 'cancel')) 
				{
                	$target->cancel();
                }
            	
    		});
			
			return $promise;

		})($that, $field, $value, $check_arr, $logic, $group, $dontReset, $origValue, $dontResetGrouping, $formatField, $checkAsync, $fieldIsArray);
	}
}