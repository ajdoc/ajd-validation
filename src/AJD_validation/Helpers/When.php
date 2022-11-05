<?php namespace AJD_validation\Helpers;

use AJD_validation\AJD_validation;
use AJD_validation\Contracts\Base_validator;
use AJD_validation\Contracts\Abstract_common;
use AJD_validation\Helpers\Logics_map;
use AJD_validation\Helpers\LogicsAddMap;
use AJD_validation\Factory\Class_factory;
use AJD_validation\Contracts\AbstractAnonymousLogics;

class When extends AJD_validation
{
	const RUNTIMEGIVEN = 'given';
	const RUNTIMETHEN = 'then';
	const RUNTIMEOTHERWISE = 'otherwise';

	protected $ajd;
	protected $obs;
	protected $testPath;
	protected $given_field = [];
	protected $customTest = [];

	protected static $testNamespace = ['AJD_validation\\Logics\\'];

	protected static $cacheTestInstance = [];
	public static $testSuffix = 'logic';

	protected $classArgs = [];
	protected static $addTestClassPath = [];
	protected static $addLogicsMappings = [];

	protected static $customLogics = [];

	protected $currentLogicKey = 0;
	protected $givenPasses = [];

	protected $currLogic;
	protected $currRule;
	protected $whenRuleName;
	protected $currentRuleKey;

	protected $runtimeType;
	protected $runtimeLogic;

	protected $validRunTimeType = [
		'ifAnd', 'ifOr', 'ifXor', 
		'elseIfAnd', 'elseIfXor', 'elseIfOr', 
		'then', 'otherwise'
	];
 
	public function __construct( AJD_validation $ajd, $obs = null )
	{
		$this->ajd = $ajd;

		$this->obs = $obs;

		$this->classArgs = [
			$this->ajd->getValidator(), $this->ajd, $this->obs
		];

		if($this->obs)
		{
			$this->obs->attach_observer( 'endwhen', array( $ajd, 'reset_all_validation_prop' ) );
		}
	}

	public function __call( $name, array $args )
	{
		$method = $this->processMethodName( $name );

		$factory = static::get_factory_instance()->get_instance( false, false, true );

		$factory->rules( get_class( $this ), $method['method'] );

		array_unshift( $args, $method['name'] );
		
		return $factory->process_method( $args, $this );
	}

	public function endIf($field, $value = null, $operator = null, $check_arr = true, $customMesage = [])
	{
		$this->endgiven($field, $value, $operator ?? $this->runtimeLogic, $check_arr, $customMesage);

		return $this;
	}

	public function endElseIf($field, $value = null, $operator = null, $check_arr = true, $customMesage = [])
	{
		$this->endIf($field, $value, $operator ?? $this->runtimeLogic, $check_arr, $customMesage);

		return $this;
	}

	protected function clearRunTimeVars()
	{
		$this->runtimeType = null;
		$this->runtimeLogic = null;
	}

	public function runtimeType($runtimeType, $startElseif = false)
	{
		if(!in_array($runtimeType, $this->validRunTimeType))
		{
			throw new \InvalidArgumentException('Invalid Method.');
		}

		if($startElseif)
		{
			$this->currentLogicKey++;
		}

		switch($runtimeType)
		{
			case 'ifAnd':
			case 'elseIfAnd':
				$this->runtimeType = self::RUNTIMEGIVEN;
				$this->runtimeLogic = Abstract_common::LOG_AND;
			break;

			case 'ifOr':
			case 'elseIfOr':
				$this->runtimeType = self::RUNTIMEGIVEN;
				$this->runtimeLogic = Abstract_common::LOG_OR;
			break;

			case 'ifXor':
			case 'elseIfXor':
				$this->runtimeType = self::RUNTIMEGIVEN;
				$this->runtimeLogic = Abstract_common::LOG_XOR;
			break;

			case 'then':
				$this->runtimeType = self::RUNTIMETHEN;
			break;

			case 'otherwise':
				$this->runtimeType = self::RUNTIMEOTHERWISE;
			break;
		}

		return $this;
	}

	public static function registerLogicsMappings(array $mappings)
	{
		foreach($mappings as $logicKey => $logic)
        {
            LogicsAddMap::register($logic);
            LogicsAddMap::setLogic($logic);
        }

        static::processMappings();
	}

	public static function processMappings()
	{
		$mappings = LogicsAddMap::getMappings();
		
		if($mappings)
		{
			static::$addLogicsMappings = array_merge(static::$addLogicsMappings, $mappings);

			Class_factory::addLogicsMappings($mappings);

			LogicsAddMap::flush();
		}
	}

	public static function registerLogic($name, $function, array $extraArgs = [])
	{
		if(isset(static::$customLogics[$name]))
		{
			return;
		}

		if(is_callable($function))
		{
			if(is_object($function) && $function instanceof AbstractAnonymousLogics)
			{
				if(!method_exists($function, '__invoke') && !method_exists($function, 'filter'))
				{
					throw new \InvalidArgumentException('Anonymous logic must have __invoke method or filter method.');
				}

				static::plotAnonymousLogic($name, $function, $extraArgs);

				return;
			}

			static::createAnonymousLogic($name, $function, $extraArgs);
		}

		return;
	}

	protected static function plotAnonymousLogic($name, $function, array $extraArgs = [])
	{
		static::$customLogics[$name]['function'] = $function;
		static::$customLogics[$name]['extraArgs'] = $extraArgs;
	}

	protected static function createAnonymousLogic($name, $function, array $extraArgs = [])
	{
		$anonClass = new class($function) extends AbstractAnonymousLogics
		{
			protected $callback;
			protected $extraArgs;

			public function __construct($callback, array $extraArgs = [])
			{
				$this->callback = $callback;
				$this->extraArgs = $extraArgs;
			}

			public function __invoke($value, $parameters = null)
			{
				$callback = $this->callback;

				if($callback instanceof \Closure)
            	{
            		$callback = $callback->bindTo($this, self::class);
            	}

            	$args = array_merge(func_get_args(), $this->extraArgs);

            	return \call_user_func_array($callback, $args);
			}
		};

		static::plotAnonymousLogic($name, $anonClass, $extraArgs);
	}

	protected function processMethodRunTimeName($methodName, $matchMain, $matchOr, $prependMain, $prependOr)
	{
		$name = $methodName;

		if(!$matchMain)
		{
			$name = $prependMain.$methodName;
		}

		if($matchOr)
		{
			$methodName = static::removeWord( $methodName, '/^oR/' );
			$name = $prependOr.$methodName;
		}

		return $name;
	}

	protected function checkPrefix($regex, $name)
	{
		return preg_match( $regex, $name );
	}

	protected function processMethodName( $name )
	{
		$ret_name = $name;
		$method = null;

		$checkLogicPrefix = $this->checkPrefix('/^Lg/', $name);
		$checkGiv = $this->checkPrefix('/^Giv/', $name);
		$checkThen = $this->checkPrefix('/^Th/', $name);
		$checkOtherWise = $this->checkPrefix('/^Oth/', $name);
		$checkOr = $this->checkPrefix('/^oR/', $name);

		if(!empty($this->runtimeType) && !$checkLogicPrefix)
		{
			switch($this->runtimeType)
			{
				case self::RUNTIMEGIVEN:
					$name = $this->processMethodRunTimeName($name, $checkGiv, $checkOr, 'Giv', 'OrGiv');
				break;
				case self::RUNTIMETHEN:
					$name = $this->processMethodRunTimeName($name, $checkThen, $checkOr, 'Th', 'OrTh');
				break;
				case self::RUNTIMEOTHERWISE:
					$name = $this->processMethodRunTimeName($name, $checkOtherWise, $checkOr, 'Oth', 'OrOth');
				break;
			}
		}

		if( $this->checkPrefix('/^Giv/', $name) )
		{
			$method = 'given';
			$ret_name = static::removeWord( $name, '/^Giv/' );
		}
		if( $this->checkPrefix('/^OrGiv/', $name) )
		{
			$method = 'givenOr';
			$ret_name = static::removeWord( $name, '/^OrGiv/' );
		}
		else if( $this->checkPrefix('/^eGiv/', $name) )
		{
			$method = 'endgiven';
			$ret_name = static::removeWord( $name, '/^eGiv/' );
		}
		else if( $this->checkPrefix('/^Th/', $name) )
		{
			$method = 'mainThen';
			$ret_name = static::removeWord( $name, '/^Th/' );
		}
		else if( $this->checkPrefix('/^OrTh/', $name) )
		{
			$method = 'thenOr';
			$ret_name = static::removeWord( $name, '/^OrTh/' );
		}
		else if( $this->checkPrefix('/^eTh/', $name) )
		{
			$method = 'endthen';
			$ret_name = static::removeWord( $name, '/^eTh/' );	
		}
		else if( $this->checkPrefix('/^Oth/', $name) )
		{
			$method = 'mainOtherwise';
			$ret_name = static::removeWord( $name, '/^Oth/' );
		}
		else if( $this->checkPrefix('/^OrTh/', $name) )
		{
			$method = 'otherwiseOr';
			$ret_name = static::removeWord( $name, '/^OrOth/' );
		}
		else if( $this->checkPrefix('/^eOth/', $name) )
		{
			$method = 'endotherwise';
			$ret_name = static::removeWord( $name, '/^eOth/' );
		}
		else if( $checkLogicPrefix )
		{
			$method = 'addLogic';
			$ret_name = static::removeWord( $name, '/^Lg/' );
		}

		if(in_array($name, $this->validRunTimeType))
		{
			$method = 'runtimeType';
			$ret_name = $name;
		}

		return [
			'method' => $method,
			'name' => $ret_name
		];
	}

	public function addLogicNamespace( $namespace )
	{
		array_push( static::$testNamespace, $namespace );

		return $this;
	}

	public function addLogicClassPath( $classPath )
	{
		array_push( static::$addTestClassPath, $classPath );

		return $this;
	}

	public function addLogic( $test )
	{
		$arguments = func_get_args();

		unset( $arguments[0] );

		$this->ajd->accessInitExtensions();
		
		$options = $this->processTests( $test, $arguments );

		if( is_array( $options['testObj'] ) )
		{
			if( isset( $options['testObj']['extensionObj'] ) )
			{
				$this->customTest[spl_object_hash($options['testObj']['extensionObj'])] = $options['testObj'];
			}
		}
		else
		{
			if(!empty($options['testObj']))
			{
				$this->customTest[spl_object_hash($options['testObj'])] = $options['testObj'];
			}
		}

		if($this->obs)
		{
			$this->obs->notify_observer( 'ongiven' );
		}

		return $this;
	}

	public function wrapLogic()
	{
		return new Logics_map($this, $this->customTest);
	}

	public function getCustomTest()
	{
		return $this->customTest;
	}

	public function clearCustomTest()
	{
		$this->customTest = [];
	}
	
	protected function processTests( $test, $arguments )
	{
		$rawTest = static::removeWord( $test, '/^!/' );
		$lowerTest = strtolower( $test );
		$cleanTest = static::clean_rule_name( $lowerTest );
		$appendTest = ucfirst( $cleanTest['rule'] ).'_'.static::$testSuffix;
		$testKind = $this->processTestsKind( $cleanTest['rule'], $appendTest, $rawTest, $arguments );

		return $testKind;
	}

	protected function processTestsKind( $test, $appendTest, $rawTest, $arguments )
	{
		$options = [];
		$lowerTest = strtolower( $appendTest );
		$options = $this->processOptions( $lowerTest, $appendTest, $rawTest, $test );

		$extensionTests = $this->ajd->getExtensionLogics();
		$isExtension = isset( $extensionTests[ $lowerTest ] );
		$isClass = file_exists( $options['testPath'] );

		$isMethod = method_exists( $options['objIns'], $appendTest );

		$isAnon = false;

		$anonObj = null;

		if(isset(static::$customLogics[$rawTest]) && !empty(static::$customLogics[$rawTest]))
		{
			$anonObj = static::$customLogics[$rawTest];
			$isAnon = true;
		}

		if(!$isClass)
		{
			if(!empty(static::$addLogicsMappings))
			{
				if(isset(static::$addLogicsMappings[$lowerTest]))
				{
					$isClass = true;
				}
			}
		}

		$options['lowerTest'] = $lowerTest;
		$options['testKind'] = null;
		$options['appendTest'] = $appendTest;
		$options['rawTest'] = $rawTest;
		$options['test'] = $test;
		$options['testObj'] = null;
		$options['arguments'] = $arguments;
		$options['extensionTests'] = $extensionTests;
		$classArgs = $this->classArgs;

		if( !empty( $options['arguments'] ) )
		{
			$classArgs = array_merge( $options['arguments'], $classArgs );
		}

		$options['classArgs'] = $classArgs;

		if( $isExtension )
		{
			$options['testKind'] = '_processExtension';
			$options['testObj'] = $this->_processExtension( $options );
		}
		else if( $isClass )
		{
			$options['testKind'] = '_processClass';
			$options['testObj'] = $this->_processClass( $options );
		}
		else if( $isAnon )
		{
			$options['testKind'] = '_processAnon';
			$options['anonObj'] = $anonObj;
			$options['testObj'] = $this->_processAnon( $options );	
		}
		else if( $isMethod )
		{
			$options['testKind'] = '_processMethod';
			$this->_processMethod( $options );
		}
		
		return $options;
	}

	protected function processOptions( $lowerTest, $appendTest, $rawTest, $test )
	{
		$options = [];
		$objIns = $this;
		$testPath = $this->getTestPath().$appendTest.'.php';

		if( !EMPTY( static::$addTestClassPath ) )
		{
			foreach( static::$addTestClassPath as $classPath )
			{
				if( file_exists( $classPath.$appendTest.'.php' ) )
				{
					$testPath = $classPath.$appendTest.'.php';
				}	
			}
		}
		
		$rawAppendTest = $rawTest.'_'.static::$testSuffix;
		$rawClass = $appendTest;
		$options['testPath'] = $testPath;
		$options['rawAppendTest'] = $rawAppendTest;
		$options['rawClass'] = $rawClass;
		$options['className'] = get_class( $objIns );
		$options['objIns'] = $objIns;

		return $options;

	}

	private function _processExtension( array $options )
	{
		$extensionTests = $options['extensionTests'];

		$extensionTest = $extensionTests[ $options['lowerTest'] ];

		$extensionObj = $extensionTest['extension_obj'];

		return [
			'extensionObj' => $extensionObj,
			'extensionName' => $extensionTest['test'],
			'classArgs' => $options['classArgs']
		];
	}

	protected function getTestPath()
	{
		$this->testPath = dirname( dirname( __FILE__ ) ).Abstract_common::DS.'Logics'.Abstract_common::DS;

		return $this->testPath;
	}

	private function _processClass( array $options )
	{
		$appendTest = $options['appendTest'];

		/*if( !ISSET( static::$cacheTestInstance[ $appendTest ] ) )
		{*/
			$classFactory = static::get_factory_instance()->get_instance( TRUE );

			$this->appendTestNameSpace( $classFactory );

			$testObj = $classFactory->rules( $options['testPath'], $appendTest, $options['classArgs'], FALSE );
		/*}
		else
		{
			$testObj 		= static::$cacheTestInstance[ $appendTest ];
		}*/

		static::$cacheTestInstance[ $appendTest ] = $testObj;

		return $testObj;
	}

	private function _processAnon( array $options )
	{
		if(empty($options['anonObj']))
		{
			return null;
		}

		$anonObj = $options['anonObj'];

		$appendTest = $options['appendTest'];

		$testObj = $anonObj['function'];

		$testObj->setExtraArgs($anonObj['extraArgs']);

		static::$cacheTestInstance[ $appendTest ] = $testObj;

		return $testObj;
	}

	public function appendTestNameSpace( $classFactory )
	{
		foreach( static::$testNamespace as $testNamespace )
		{
			$classFactory->append_rules_namespace( $testNamespace );
		}
	}

	public function givenOr( $rule, $satis = null, $custom_err = null, $client_side = null, $logic = Abstract_common::LOG_OR )
	{
		$this->given($rule, $satis, $custom_err, $client_side, $logic);

		return $this;
	}

	public function given( $rule, $satis = null, $custom_err = null, $client_side = null, $logic = Abstract_common::LOG_AND )
	{
		$this->currRule = $rule;
		$this->currLogic = $logic;

		$addRule = $this->ajd->addRule( $rule, $satis, $custom_err, $client_side, $logic );

		$this->currentRuleKey = $addRule->getCurrentRuleKey();

		if($this->obs)
		{
			$this->obs->notify_observer( 'ongiven' );
		}

		$clean_rule = $this->ajd->clean_rule_name( $rule );
		$this->whenRuleName = $clean_rule['rule'];

		return $this;
	}

	protected function processCustomTest( $value, array $paramaters = [], $forGetValues = false )
	{
		$logicValues = [];

		if( !empty( $this->customTest ) )
		{
			foreach( $this->customTest as $test )
			{
				if( is_array( $test ) ) 
				{
					if( isset( $test['extensionObj'] ) )
					{
						if( !$this->processCustomExtensionTest( $value, $test, $paramaters ) )
						{
							if(!$forGetValues)
							{
								return false;	
							}
						}

						if($forGetValues)
						{
							$logicValues = array_merge($logicValues, []);	
						}
						
					}
				}
				else
				{

					if(!empty($paramaters))
					{
						foreach($paramaters as $paramKey => $paramValue)
						{
							$test->$paramKey = $paramValue;
						}
					}

					$test->forGetValues = $forGetValues;
						
					if($test instanceof AbstractAnonymousLogics)
					{
						if( !$test($value, $test) )
						{
							if(!$forGetValues)
							{
								return false;
							}
						}
					}
					else
					{
						if( !$test->logic( $value ) )
						{
							if(!$forGetValues)
							{
								return false;
							}
						}
					}

					if($forGetValues)
					{
						if($test instanceof AbstractAnonymousLogics)
						{
							$logicValue = $test($value, $test, $paramaters);
						}
						else
						{
							$logicValue = $test->getLogicValue($value, $paramaters);	
						}
						
						$logicValue = (!is_array($logicValue)) ? [$logicValue] : $logicValue;
						$logicValues = array_merge($logicValues, $logicValue);	
					}
				}
			}
		}

		$this->customTest = [];

		return (!$forGetValues) ? true : $logicValues;
	}

	protected function processCustomExtensionTest( $value, $test, array $paramaters = [] )
	{
		if( method_exists( $test['extensionObj'], $test['extensionName'] ) )
		{
			$args = array( $value );

			$args = array_merge( $args, $test['classArgs'] );
			
			if(!empty($paramaters))
			{
				foreach($paramaters as $paramKey => $paramValue)
				{
					$test['extensionObj']->$paramKey = $paramValue;
				}
			}

			$result = call_user_func_array( array( $test['extensionObj'], $test['extensionName'] ), $args );
			
			if( !$result )
			{
				return false;
			}
		}

		return true;
	}

	public function runLogics($value, array $paramaters = [], $reset = true)
	{
		if($reset && $this->obs)
		{
			$this->obs->notify_observer( 'endwhen' );
			$this->obs->detach_observer( 'ongiven' );
			$this->obs->detach_observer( 'endgiven' );
			$this->obs->detach_observer( 'endwhen' );
		}

		return $this->processCustomTest($value, $paramaters);
	}

	public function getValues($value = null, array $paramaters = [])
	{
		return $this->processCustomTest($value, $paramaters, true);
	}

	public function endgiven( $field, $value = null, $operator = null, $check_arr = true, $customMesage = [] )
	{
		$result = true;
		$passArr = [
			'field' => $field
		];
		
		$result = $this->processCustomTest( $value );
		
		if( !empty($this->customTest) ) 
		{
			$passArr['result'] = $result;
		}

		if( !empty( $this->given_field ) ) 
		{
			if( !empty( $operator ) ) 
			{
				$this->given_field[$this->currentLogicKey][][ strtolower( $operator ) ] = $passArr;
			} 
			else 
			{
				$this->given_field[$this->currentLogicKey][][Abstract_common::LOG_AND] = $passArr;
			}

		} 
		else 
		{
			$operatorKey = $operator ?? Abstract_common::LOG_AND;
			$this->given_field[$this->currentLogicKey][][$operatorKey] = $passArr;
		}

		$this->ajd->checkArr( $field, $value, $customMesage, $check_arr );

		if($this->obs)
		{
			$this->obs->notify_observer( 'endgiven' );
		}

		$this->clearRunTimeVars();

		return $this;
	}

	private function _check_given($key = 0)
	{
		if(!isset($this->given_field[$key]))
		{
			return false;
		}

		$and = [];
		$or = [];
		$xor = [];
		
		foreach( $this->given_field[$key] as $key => $value ) 
		{
			if( !empty( $value[Abstract_common::LOG_AND] ) ) 
			{
				$andDetails = $value[Abstract_common::LOG_AND];

				$ch_and = $this->ajd->validation_fails( $andDetails['field'], null, true );
				
				$and[] = !(bool) $ch_and;
				
				if(isset($andDetails['result']))
				{
					$and[] = $andDetails['result'];
				}
			} 

			if( !empty( $value[Abstract_common::LOG_OR] ) ) 
			{
				$orDetails = $value[Abstract_common::LOG_OR];

				$or[] = !(bool) $this->ajd->validation_fails( $orDetails['field'], null, true );				

				if(isset($orDetails['result']))
				{
					$or[] = $orDetails['result'];				
				}
				
			}

			if( !empty( $value[Abstract_common::LOG_XOR] ) ) 
			{
				$xorDetails = $value[Abstract_common::LOG_XOR];

				$xor[] = !(bool) $this->ajd->validation_fails( $xorDetails['field'], null, true );

				if(isset($xorDetails['result']))
				{
					$xor[] = $xorDetails['result'];
				}
			}
		}

		$andResult = false;
		$xorResult = false;
		$orResult = false;

		if(!empty($and))
		{
			$andResult = array_reduce($and, function($carry, $item)
			{
				$carry = ($carry && $item);
				
				return $carry;
				
			}, true);
		}

		if(!empty($xor))
		{
			$xorResult = array_reduce($xor, function($carry, $item)
			{
				$carry = ($carry xor $item);
				
				return $carry;
				
			}, false);
		}

		if(!empty($or))
		{
			$orResult = array_reduce($or, function($carry, $item)
			{
				$carry = ($carry || $item);
				
				return $carry;
				
			}, false);
		}

		if(!empty($and) && !empty($xor) && !empty($or))
		{
			return $andResult xor $xorResult || $orResult;
		}

		if(empty($and) && !empty($xor) && !empty($or))
		{
			return $xorResult || $orResult;
		}

		if(!empty($and) && !empty($xor) && empty($or))
		{
			return $andResult xor $xorResult;
		}

		if(!empty($and) && empty($xor) && !empty($or))
		{
			return $andResult || $orResult;
		}

		if(!empty($and) && empty($xor) && empty($or))
		{
			return $andResult;
		}

		if(empty($and) && !empty($xor) && empty($or))
		{
			return $xorResult;
		}

		if(empty($and) && empty($xor) && !empty($or))
		{
			return $orResult;
		}

		return false;
	}

	public function thenOr( $rule, $satis = null, $custom_err = null, $client_side = null, $logic = Abstract_common::LOG_OR )
	{
		$this->mainThen($rule, $satis, $custom_err, $client_side, $logic);

		return $this;

	}

	public function mainThen($rule, $satis = null, $custom_err = null, $client_side = null, $logic = Abstract_common::LOG_AND)
	{
		$checkStop = $this->stopThen();

		if(!empty($checkStop))
		{
			return $checkStop;
		}

		$this->currRule = $rule;
		$this->currLogic = $logic;

		$this->currentRuleKey = $this->currentRuleKey + 1;

		if( $this->_check_given($this->currentLogicKey) ) 
		{

			$addRule = $this->ajd->addRule( $rule, $satis, $custom_err, $client_side, $logic );

			$this->currentRuleKey = $addRule->getCurrentRuleKey();

			$clean_rule = $this->ajd->clean_rule_name( $rule );

			$this->whenRuleName = $clean_rule['rule'];
		}

		return $this;
	}

	protected function stopThen()
	{
		if(!empty($this->givenPasses))
		{
			if($this->givenPasses['currentLogicKey'] != $this->currentLogicKey && $this->givenPasses['passes'] === true)
			{
				return $this;
			}
		}

		return null;
	}

	public function endthen( $field, $value = null, $check_arr = true, $customMesage = [] )
	{
		$checkStop = $this->stopThen();

		if(!empty($checkStop))
		{
			return $checkStop;
		}

		if( $this->_check_given($this->currentLogicKey) ) 
		{
			$this->givenPasses = [
				'passes' => true,
				'currentLogicKey' => $this->currentLogicKey
			];

			$this->ajd->checkArr( $field, $value, $customMesage, $check_arr );
		}

		$this->clearRunTimeVars();

		return $this;
	}

	protected function checkAllGivens()
	{
		$rangeLogicKeys = range(0, $this->currentLogicKey);
		$checkGivens = [];

		foreach($rangeLogicKeys as $key)
		{
			$checkGivens[] = $this->_check_given($key);
		}

		return $checkGivens;
	}

	public function mainOtherwise( $rule, $satis = null, $custom_err = null, $client_side = null, $logic = Abstract_common::LOG_AND )
	{
		$this->currRule = $rule;
		$this->currLogic = $logic;

		$this->currentRuleKey = $this->currentRuleKey + 1;

		$checkGivens = $this->checkAllGivens();

		if( !in_array(true, $checkGivens, true) ) 
		{
			$addRule = $this->ajd->addRule( $rule, $satis, $custom_err, $client_side, $logic );

			$this->currentRuleKey = $addRule->getCurrentRuleKey();

			$clean_rule = $this->ajd->clean_rule_name( $rule );

			$this->whenRuleName = $clean_rule['rule'];
		}

		return $this;
	}

	public function otherwiseOr( $rule, $satis = null, $custom_err = null, $client_side = null, $logic = Abstract_common::LOG_OR )
	{
		$this->mainOtherwise($rule, $satis, $custom_err, $client_side, $logic);

		return $this;
	}

	public function endotherwise( $field, $value = null, $check_arr = true, $customMesage = [] )
	{
		$checkGivens = $this->checkAllGivens();

		if( !in_array(true, $checkGivens, true) ) 
		{
			$this->ajd->checkArr( $field, $value, $customMesage, $check_arr );
		}

		$this->clearRunTimeVars();

		return $this;
	}

	public function endwhen()
	{
		if($this->obs)
		{
			$this->obs->notify_observer( 'endwhen' );
			$this->obs->detach_observer( 'ongiven' );
			$this->obs->detach_observer( 'endgiven' );
			$this->obs->detach_observer( 'endwhen' );
		}

		return $this->ajd::get_promise_validator_instance();
	}

	public function on( $scenario, $ruleOverride = null, $forJs = false )
	{
		$clean_rule = $this->ajd->clean_rule_name( $this->currRule );

		return static::get_scene_ins( $clean_rule['rule'], $this->currLogic, true, $this, $this->currentRuleKey )->on( $scenario, $ruleOverride, $forJs );
	}

	public function sometimes( $sometimes = Abstract_common::SOMETIMES, $ruleOverride = null, $forJs = false )
	{
		$clean_rule = $this->ajd->clean_rule_name( $this->currRule );

		return static::get_scene_ins( $clean_rule['rule'], $this->currLogic, true, $this, $this->currentRuleKey )->sometimes( $sometimes, $ruleOverride, $forJs );
	}

	public function stopOnError( $stop = true, $ruleOverride = null, $forJs = false )
	{
		$clean_rule = $this->ajd->clean_rule_name( $this->currRule );

		return static::get_scene_ins( $clean_rule['rule'], $this->currLogic, true, $this, $this->currentRuleKey )->stopOnError( $stop, $ruleOverride, $forJs );
	}

	public function getInstance()
	{
		$clean_rule = $this->ajd->clean_rule_name( $this->currRule );
		
		return static::get_scene_ins( $clean_rule['rule'], $this->currLogic, true, $this, $this->currentRuleKey )->getInstance();
	}

	public function generator(Closure $func)
	{
		$clean_rule = $this->ajd->clean_rule_name( $this->currRule );
		
		return static::get_scene_ins( $clean_rule['rule'], $this->currLogic, true, $this, $this->currentRuleKey )->generator($func);
	}

	/*public static function bail()
	{
		$v 	= $this->ajd;
		$v::bail();

		return $this;
	}*/

	public function publish($event, $callback = null, $customEvent = null, $eventType = Abstract_common::EV_LOAD, $ruleOverride = null, $forJs = false)
	{
		$logic = static::$ajd_prop[ 'current_logic' ];
		$curr_field = static::$ajd_prop[ 'current_field' ];

		$rule = $this->whenRuleName;

		if(!empty($callback))
		{
			if(!empty($curr_field))
			{
				$this->subscribe($curr_field.'-|'.$event, $callback, $customEvent);
			}
			else
			{
				$this->subscribe($event, $callback, $customEvent);
			}
		}

		if( !EMPTY( $ruleOverride ) )
		{
			$rule = $ruleOverride;
		}

		if( !$forJs )
		{
			if(!empty($curr_field))
			{
				if(!is_null($this->currentRuleKey))
				{
					static::$ajd_prop['events'][$eventType][$curr_field.'-|'.$rule][$this->currentRuleKey][] = $curr_field.'-|'.$event;
				}
				else
				{
					static::$ajd_prop['events'][$eventType][$curr_field.'-|'.$rule][] = $curr_field.'-|'.$event;
				}
			}
			else
			{	
				if(!is_null($this->currentRuleKey))
				{
					static::$ajd_prop['events'][$eventType][$rule][$this->currentRuleKey][] = $event;
				}
				else
				{
					static::$ajd_prop['events'][$eventType][$rule][] = $event;	
				}
			}
		}

		if( !EMPTY( $this->when ) )
		{
			return $this->when;
		}
		else
		{
			return $this;
		}
	}

	public function publishSuccess($event, $callback = null, $customEvent = null, $forJs = false, $ruleOverride = null)
	{
		return $this->publish($event, $callback, $customEvent, Abstract_common::EV_SUCCESS, $ruleOverride, $forJs);
	}

	public function publishFail($event, $callback = null, $customEvent = null, $forJs = false, $ruleOverride = null)
	{
		return $this->publish($event, $callback, $customEvent, Abstract_common::EV_FAILS, $ruleOverride, $forJs);
	}

	public function suspend($ruleOverride = null, $forJs = false)
	{
		$rule = $this->whenRuleName;

		if( !EMPTY( $ruleOverride ) )
		{
			$rule = $ruleOverride;
		}

		if(!is_null($this->currentRuleKey))
		{
			static::$ajd_prop['fiber_suspend'][$rule][$this->currentRuleKey] = true;
		}
		else
		{
			static::$ajd_prop['fiber_suspend'][$rule] = true;
		}
		
		if( !EMPTY( $this->when ) )
		{
			return $this->when;
		}
		else
		{
			return $this;
		}
	}
}

