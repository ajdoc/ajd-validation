<?php namespace AJD_validation\Helpers;

use AJD_validation\AJD_validation;
use AJD_validation\Contracts\Base_validator;
use AJD_validation\Contracts\Abstract_common;

class When extends Base_validator
{
	protected $ajd;
	protected $obs;
	protected $testPath;
	protected $given_field	= array();
	protected $customTest 	= array();

	protected static $testNamespace 			= array( 'AJD_validation\\Logics\\' );

	protected static $cacheTestInstance 		= array();
	protected static $testSuffix 				= 'logic';

	protected $classArgs 						= array();
	protected static $addTestClassPath 			= array();

	protected $currLogic;
	protected $currRule;
 
	public function __construct( AJD_validation $ajd, $obs )
	{
		$this->ajd 	= $ajd;

		$this->obs 	= $obs;

		$this->classArgs 	= array(
			$this->ajd->getValidator(), $this->ajd, $this->obs
		);

		$this->obs->attach_observer( 'endwhen', array( $ajd, 'reset_all_validation_prop' ) );
	}

	public function __call( $name, array $args )
	{
		$method 	= $this->processMethodName( $name );

		$factory 	= static::get_factory_instance()->get_instance( FALSE, FALSE, TRUE );

		$factory->rules( get_class( $this ), $method['method'] );

		array_unshift( $args, $method['name'] );
		
		return $factory->process_method( $args, $this );
	}

	protected function processMethodName( $name )
	{
		$ret_name 		= $name;
		$method = null;

		if( preg_match( '/^Giv/', $name ) )
		{
			$method 	= 'given';
			$ret_name 	= static::removeWord( $name, '/^Giv/' );
		}
		if( preg_match( '/^OrGiv/', $name ) )
		{
			$method 	= 'givenOr';
			$ret_name 	= static::removeWord( $name, '/^OrGiv/' );
		}
		else if( preg_match( '/^eGiv/', $name ) )
		{
			$method 	= 'endgiven';
			$ret_name 	= static::removeWord( $name, '/^eGiv/' );
		}
		else if( preg_match( '/^Th/' , $name ) )
		{
			$method 	= 'then';
			$ret_name 	= static::removeWord( $name, '/^Th/' );
		}
		else if( preg_match( '/^OrTh/' , $name ) )
		{
			$method 	= 'thenOr';
			$ret_name 	= static::removeWord( $name, '/^OrTh/' );
		}
		else if( preg_match('/^eTh/', $name ) )
		{
			$method 	= 'endthen';
			$ret_name 	= static::removeWord( $name, '/^eTh/' );	
		}
		else if( preg_match('/^Oth/', $name ) )
		{
			$method 	= 'otherwise';

			$ret_name 	= static::removeWord( $name, '/^Oth/' );
		}
		else if( preg_match('/^OrOth/', $name ) )
		{
			$method 	= 'otherwiseOr';

			$ret_name 	= static::removeWord( $name, '/^OrOth/' );
		}
		else if( preg_match('/^eOth/', $name ) )
		{
			$method 	= 'endotherwise';

			$ret_name 	= static::removeWord( $name, '/^eOth/' );
		}
		else if( preg_match('/^Lg/', $name ) )
		{
			$method 	= 'addLogic';

			$ret_name 	= static::removeWord( $name, '/^Lg/' );
		}

		return array(

			'method' 	=> $method,
			'name' 		=> $ret_name
		);
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
		$arguments 	= func_get_args();

		unset( $arguments[0] );

		$this->ajd->accessInitExtensions();

		$options 	= $this->processTests( $test, $arguments );

		if( is_array( $options['testObj'] ) )
		{
			if( ISSET( $options['testObj']['extensionObj'] ) )
			{
				$this->customTest[spl_object_hash($options['testObj']['extensionObj'])] 	= $options['testObj'];
			}
		}
		else
		{
			$this->customTest[spl_object_hash($options['testObj'])] 	= $options['testObj'];
		}

		$this->obs->notify_observer( 'ongiven' );

		return $this;
	}
	
	protected function processTests( $test, $arguments )
	{
		$rawTest 	= static::removeWord( $test, '/^!/' );
		$lowerTest 	= strtolower( $test );
		$cleanTest 	= static::clean_rule_name( $lowerTest );
		$appendTest = ucfirst( $cleanTest['rule'] ).'_'.static::$testSuffix;
		
		$testKind 	= $this->processTestsKind( $cleanTest['rule'], $appendTest, $rawTest, $arguments );

		return $testKind;
	}

	protected function processTestsKind( $test, $appendTest, $rawTest, $arguments )
	{
		$options 	= array();
		$lowerTest 	= strtolower( $appendTest );
		$options 	= $this->processOptions( $lowerTest, $appendTest, $rawTest, $test );

		$extensionTests 	= $this->ajd->getExtensionLogics();

		$isExtension= ISSET( $extensionTests[ $lowerTest ] );
		$isClass 	= file_exists( $options['testPath'] );

		$isMethod 	= method_exists( $options['objIns'], $appendTest );

		$options['lowerTest'] 	= $lowerTest;
		$options['testKind'] 	= NULL;
		$options['appendTest'] 	= $appendTest;
		$options['rawTest'] 	= $rawTest;
		$options['test'] 		= $test;
		$options['testObj'] 	= NULL;
		$options['arguments'] 	= $arguments;
		$options['extensionTests'] 	= $extensionTests;

		$classArgs 			= $this->classArgs;

		if( !EMPTY( $options['arguments'] ) )
		{
			$classArgs 		= array_merge( $options['arguments'], $classArgs );
		}

		$options['classArgs'] 	= $classArgs;

		if( $isExtension )
		{
			$options['testKind']  	= '_processExtension';
			$options['testObj'] 	= $this->_processExtension( $options );
		}
		else if( $isClass )
		{
			$options['testKind'] 	= '_processClass';
			$options['testObj'] 	= $this->_processClass( $options );
		}
		else if( $isMethod )
		{
			$options['testKind'] 	= '_processMethod';
			$this->_processMethod( $options );
		}
		
		return $options;
	}

	protected function processOptions( $lowerTest, $appendTest, $rawTest, $test )
	{
		$options 	= array();

		$objIns 		= $this;
		$testPath 		= $this->getTestPath().$appendTest.'.php';

		if( !EMPTY( static::$addTestClassPath ) )
		{
			foreach( static::$addTestClassPath as $classPath )
			{
				if( file_exists( $classPath.$appendTest.'.php' ) )
				{
					$testPath 	= $classPath.$appendTest.'.php';
				}	
			}
		}
		
		$rawAppendTest 	= $rawTest.'_'.static::$testSuffix;

		$rawClass 		= $appendTest;

		$options['testPath'] 		= $testPath;
		$options['rawAppendTest'] 	= $rawAppendTest;
		$options['rawClass'] 		= $rawClass;
		$options['className'] 		= get_class( $objIns );
		$options['objIns'] 			= $objIns;

		return $options;

	}

	private function _processExtension( array $options )
	{
		$extensionTests 	= $options['extensionTests'];

		$extensionTest 		= $extensionTests[ $options['lowerTest'] ];

		$extensionObj 	 	= $extensionTest['extension_obj'];

		return array(
			'extensionObj' 	=> $extensionObj,
			'extensionName' => $extensionTest['test'],
			'classArgs' 	=> $options['classArgs']
		);
	}

	protected function getTestPath()
	{
		$this->testPath 		= dirname( dirname( __FILE__ ) ).Abstract_common::DS.'Logics'.Abstract_common::DS;

		return $this->testPath;
	}

	private function _processClass( array $options )
	{
		$appendTest 	= $options['appendTest'];

		/*if( !ISSET( static::$cacheTestInstance[ $appendTest ] ) )
		{*/
			$classFactory 	= static::get_factory_instance()->get_instance( TRUE );

			$this->appendTestNameSpace( $classFactory );

			$testObj 		= $classFactory->rules( $options['testPath'], $appendTest, $options['classArgs'], FALSE );
		/*}
		else
		{
			$testObj 		= static::$cacheTestInstance[ $appendTest ];
		}*/

		static::$cacheTestInstance[ $appendTest ] 	= $testObj;

		return $testObj;
	}

	public function appendTestNameSpace( $classFactory )
	{
		foreach( static::$testNamespace as $testNamespace )
		{
			$classFactory->append_rules_namespace( $testNamespace );
		}
	}

	public function givenOr( $rule, $satis = NULL, $custom_err = NULL, $client_side = NULL, $logic = Abstract_common::LOG_OR )
	{
		$this->given($rule, $satis, $custom_err, $client_side, $logic);

		return $this;
	}

	public function given( $rule, $satis = NULL, $custom_err = NULL, $client_side = NULL, $logic = Abstract_common::LOG_AND )
	{
		$this->currRule 	= $rule;
		$this->currLogic 	= $logic;

		$this->ajd->addRule( $rule, $satis, $custom_err, $client_side, $logic );

		$this->obs->notify_observer( 'ongiven' );

		$clean_rule 			= $this->ajd->clean_rule_name( $rule );

		return $this;
	}

	protected function processCustomTest( $value )
	{
		if( !EMPTY( $this->customTest ) )
		{
			foreach( $this->customTest as $test )
			{
				if( is_array( $test ) ) 
				{
					if( ISSET( $test['extensionObj'] ) )
					{
						if( !$this->processCustomExtensionTest( $value, $test ) )
						{
							return FALSE;
						}
					}
				}
				else
				{
					if( !$test->logic( $value ) )
					{
						return FALSE;
					}
				}
			}
		}

		return TRUE;
	}

	protected function processCustomExtensionTest( $value, $test )
	{
		if( method_exists( $test['extensionObj'], $test['extensionName'] ) )
		{
			$args 		= array( $value );

			$args 		= array_merge( $args, $test['classArgs'] );

			$result 	= call_user_func_array( array( $test['extensionObj'], $test['extensionName'] ), $args );
			
			if( !$result )
			{
				return FALSE;
			}
		}

		return TRUE;
	}

	public function endgiven( $field, $value = NULL, $operator = NULL, $check_arr = TRUE, $customMesage = array() )
	{
		$result 		= TRUE;
		$passArr 		= array(
			'field' 	=> $field
		);
		
		$result 			= $this->processCustomTest( $value );
		
		if( !empty($this->customTest) ) 
		{
			$passArr['result'] 	= $result;
		}

		if( !EMPTY( $this->given_field ) ) 
		{
			if( !EMPTY( $operator ) ) 
			{
				$this->given_field[][ strtolower( $operator ) ] 	= $passArr;
			} 
			else 
			{
				$this->given_field[][Abstract_common::LOG_AND] 		= $passArr;
			}

		} 
		else 
		{
			$this->given_field[][Abstract_common::LOG_AND] 			= $passArr;
		}


		$this->ajd->checkArr( $field, $value, $customMesage, $check_arr );

		$this->obs->notify_observer( 'endgiven' );

		return $this;

	}

	private function _check_given()
	{
		$and 	= array();
		$or 	= array();
		$xor 	= array();
		
		foreach( $this->given_field as $key => $value ) 
		{
			if( !EMPTY( $value[Abstract_common::LOG_AND] ) ) 
			{
				$andDetails 	= $value[Abstract_common::LOG_AND];

				$ch_and 		= $this->ajd->validation_fails( $andDetails['field'], NULL, TRUE );
				
				$and[] 			= !(bool) $ch_and;
				
				if(isset($andDetails['result']))
				{
					$and[] 			= $andDetails['result'];
				}
			} 

			if( !EMPTY( $value[Abstract_common::LOG_OR] ) ) 
			{
				$orDetails 		= $value[Abstract_common::LOG_OR];

				$or[] 			= !(bool) $this->ajd->validation_fails( $orDetails['field'], NULL, TRUE );				

				if(isset($orDetails['result']))
				{
					$or[] 			= $orDetails['result'];				
				}
				
			}

			if( !EMPTY( $value[Abstract_common::LOG_XOR] ) ) 
			{
				$xorDetails 	= $value[Abstract_common::LOG_XOR];

				$xor[] 			= !(bool) $this->ajd->validation_fails( $xorDetails['field'], NULL, TRUE );

				if(isset($xorDetails['result']))
				{
					$xor[] 			= $xorDetails['result'];
				}
			}
		}
		
		$and_check2 	= !in_array( 0, $and );
		
		// $and_check 		= !in_array( 1, $and );

		$and_check 		= !in_array( 0, $and );
			
		$or_check		= in_array( 1, $or );

		if( COUNT( $xor ) === 1 ) 
		{
			$xor_check 		= in_array( 1, $xor );
		} 
		else 
		{
			$str_func = '';
			foreach($xor as $xorr)
			{	

				if(is_bool($xorr))
				{
					$xorr_str_val = ($xorr === true) ? 'true' : 'false';
					$str_func .= $xorr_str_val.' xor ';
				}

			}


			$str_func = rtrim($str_func, ' xor ');

			$xor_check = eval( "return $str_func;" );
		}


		if( !EMPTY( $or ) OR !EMPTY( $xor ) ) 
		{
			if(!empty($and))
			{
				if(!empty($xor) && empty($or))
				{
					return ( $and_check XOR $xor_check );
				}
				else if( !empty($xor) && !empty($or) )
				{
					return ( $and_check XOR $or_check XOR $xor_check );
				}
				else if( empty($xor) && !empty($or) )
				{
					return ( $and_check || $or_check );
				}
				else
				{
					return $and_check;
				}
			}
			else
			{
				if( !empty( $xor ) && !empty($or) ) 
				{
					return ( $or_check XOR $xor_check );
				}
				else if( empty( $xor ) && !empty($or) )
				{
					return $or_check;
				}
				else if( !empty( $xor ) && empty($or) )
				{
					return $xor_check;
				}
				else
				{
					if(!empty($and))
					{
						return ( $and_check2 OR $or_check );	
					}
					else
					{
						return $or_check;
					}
				}
			}

			/*if( !EMPTY( $and_check ) ) 
			{
				if( !EMPTY( $xor ) ) 
				{
					return ( $and_check XOR $xor_check );
				} 
				else if(!empty($or))
				{
					return ( $and_check XOR $or_check );
				}
				else 
				{
					return $and_check;
				}
			} 
			else 
			{
				if( !EMPTY( $xor ) ) 
				{
					return ( $or_check XOR $xor_check );
				} 
				else 
				{
					if(!empty($and))
					{
						return ( $and_check2 OR $or_check );	
					}
					else
					{
						return $or_check;
					}
					
				}
			}*/
		} 
		else 
		{
			return $and_check;
		}

		// return in_array( 1, $check );

	}

	public function thenOr( $rule, $satis = NULL, $custom_err = NULL, $client_side = NULL, $logic = Abstract_common::LOG_OR )
	{
		$this->then($rule, $satis, $custom_err, $client_side, $logic);

		return $this;

	}

	public function then( $rule, $satis = NULL, $custom_err = NULL, $client_side = NULL, $logic = Abstract_common::LOG_AND )
	{
		$this->currRule 	= $rule;
		$this->currLogic 	= $logic;

		if( $this->_check_given() ) 
		{
			$this->ajd->addRule( $rule, $satis, $custom_err, $client_side, $logic );
		}

		$clean_rule 			= $this->ajd->clean_rule_name( $rule );

		return $this;

	}

	public function endthen( $field, $value = NULL, $check_arr = TRUE, $customMesage = array() )
	{
		if( $this->_check_given() ) 
		{
			$this->ajd->checkArr( $field, $value, $customMesage, $check_arr );
		}

		return $this;
	}

	public function otherwise( $rule, $satis = NULL, $custom_err = NULL, $client_side = NULL, $logic = Abstract_common::LOG_AND )
	{
		$this->currRule 	= $rule;
		$this->currLogic 	= $logic;

		if( !$this->_check_given() ) 
		{
			$this->ajd->addRule( $rule, $satis, $custom_err, $client_side, $logic );
		}

		$clean_rule 			= $this->ajd->clean_rule_name( $rule );

		return $this;
	}

	public function otherwiseOr( $rule, $satis = NULL, $custom_err = NULL, $client_side = NULL, $logic = Abstract_common::LOG_OR )
	{
		$this->otherwise($rule, $satis, $custom_err, $client_side, $logic);

		return $this;
	}

	public function endotherwise( $field, $value = NULL, $check_arr = TRUE, $customMesage = array() )
	{
		if( !$this->_check_given() ) 
		{
			$this->ajd->checkArr( $field, $value, $customMesage, $check_arr );
		}

		return $this;
	}

	public function endwhen()
	{
		$this->obs->notify_observer( 'endwhen' );

		$this->obs->detach_observer( 'ongiven' );
		$this->obs->detach_observer( 'endgiven' );

		$this->obs->detach_observer( 'endwhen' );

		return $this->ajd;
	}

	public function on( $scenario )
	{
		$clean_rule 	= $this->ajd->clean_rule_name( $this->currRule );

		return static::get_scene_ins( $clean_rule['rule'], $this->currLogic, TRUE, $this )->on( $scenario );
	}

	public function sometimes( $sometimes = Abstract_common::SOMETIMES )
	{
		$clean_rule 	= $this->ajd->clean_rule_name( $this->currRule );

		return static::get_scene_ins( $clean_rule['rule'], $this->currLogic, TRUE, $this )->sometimes( $sometimes );
	}	

	public function bail()
	{
		$v 	= $this->ajd;
		$v::bail();

		return $this;
	}
}

