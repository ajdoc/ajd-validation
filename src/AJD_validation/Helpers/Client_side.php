<?php namespace AJD_validation\Helpers;

use Exception;
use Closure;

use AJD_validation\Contracts\{
	Base_validator, AbstractClientSide
};

use AJD_validation\AJD_validation;

class Client_side extends Base_validator
{
	const PARSLEY = 'parsley';
	const JQ_VALIDATION = 'jqvalidation';

	protected static $js_rules;
	protected static $js_validation_rules = [];
	protected static $validJs = [
		self::PARSLEY,
		self::JQ_VALIDATION
	];

	protected static $clientSidePath;
	public static $clientSideSuffix = 'ClientSide';
	public static $altClientSideSuffix = '_client_side';

	protected $cacheInstance = [];	

	protected $ajd;

	protected static $clientSideNamespace = ['AJD_validation\\ClientSide\\'];

	protected static $customClientSide = [];
	protected static $defaultField = 'DefaultField';

	protected static $commonClientSideClass = 'AjdCommon';

	protected static $requiredRules = [
		'required', 'required_allowed_zero'
	];

	protected static $minMaxlengthRules = [
		'minlength', 'maxlength'
	];

	protected static $rulesCommonClass = [
		'required', 'required_allowed_zero', // required base rules
		'email', 'base_email', 'rfc_email', 'spoof_email', 'no_rfc_email', 'dns_email', // email base rules
		'in', 'date', 'multiple', // rules with client side support
		'alpha', 'alnum', 'digit', // ctype rules
		'regex', 'mac_address', 'consonant', 'mobileno', 'phone', 'vowel', // regex rules
		'maxlength', 'minlength' // length based rules
	];

	protected static $addDirectory = [];

	protected static $addMappings = [];

	public static function addNamespace( $namespace )
	{
		array_push( static::$clientSideNamespace, $namespace );
	}

	public static function addDirectory( $directory )
	{
		array_push( static::$addDirectory, $directory );
	}

	public static function addMappings( array $mappings )
	{
		static::$addMappings = array_merge(static::$addMappings, $mappings);
	}

	public function getClientSidePath()
	{
		static::$clientSidePath = dirname( dirname( __FILE__ ) ).self::DS.'ClientSide'.self::DS;

		return static::$clientSidePath;
	}

	public function generateClassDetails($rule, $jsTypeFormat = self::PARSLEY)
	{
		if(in_array($rule, static::$rulesCommonClass, true))
		{
			$jsTypeFormat = \ucfirst( \strtolower( $jsTypeFormat ) );	
			$classClientSide = $jsTypeFormat.static::$commonClientSideClass;
		}
		else
		{
			$classClientSide = \ucfirst( \strtolower( $rule ) );	
		}

		$fullClassClientSide = $classClientSide.static::$clientSideSuffix;
		$classPath = $this->getClientSidePath().$classClientSide.static::$clientSideSuffix.'.php';

		if(!file_exists($classPath))
		{
			$classPath = $this->getClientSidePath().$classClientSide.static::$altClientSideSuffix.'.php';
			$fullClassClientSide = $classClientSide.static::$altClientSideSuffix;
		}
		
		if( !empty( static::$addDirectory ) )
		{
			foreach( static::$addDirectory as $pathClass )
			{
				$pathHolder = $pathClass.$classClientSide.static::$clientSideSuffix.'.php';
				$pathHolderAlt = $pathClass.$classClientSide.static::$altClientSideSuffix.'.php';

				if( file_exists( $pathHolder ) )
				{
					$classPath = $pathHolder;
				}
				else
				{
					if(file_exists($pathHolderAlt))
					{
						$classPath = $pathHolderAlt;
					}
				}
			}
		}

		return [
			'classPath' => $classPath,
			'isClass' => file_exists($classPath),
			'className' => $fullClassClientSide
		];
	}

	public function loadClientSideFile($classPath, $className)
	{
		$fullClassName = $className;

		foreach(static::$clientSideNamespace as $namespace)
		{
			$realClassName = $namespace.$className;
			
			if( !empty( $classPath ) && !class_exists( $realClassName ) )
			{
				if(is_string($classPath) && !is_object($classPath))
				{
					$requiredFiles = get_included_files();

					$classPath = str_replace(['\\', '/'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $classPath);
					
					$search = array_search($classPath, $requiredFiles);

					if( empty( $search ) )
					{
						if(file_exists($classPath))
						{
							$clientSeq = require $classPath;		
						}
					}
				}
			}

			if(class_exists($realClassName))
			{
				$fullClassName = $realClassName;
				continue;
			}
		}

		if(is_string($classPath) && !is_object($classPath))
		{
			return [
				'className' => $fullClassName,
				'classExists' => class_exists($fullClassName)
			];
		}
	}

	public static function addJSvalidationLibrary( $jsValidationLibrary )
	{
		static::$validJs[] 	= $jsValidationLibrary;
	}

	public static function registerCustomClientSide($forRuleName, CLosure $registration, $field = null)
	{
		$anon = new class() extends AbstractClientSide {

			protected static $registration;

			public static function bootAnonymous(Closure $registration)
			{
				$self = new self;
				$registration = $registration->bindTo($self, self::class);

				static::$registration = $registration;
			}

			public static function getCLientSideFormat(string $field, string $rule, $satisfier = null, string $error = null, $value = null)
			{
				if(empty(static::$registration))
				{
					return [];
				}

				$closure = static::$registration;

				$arguments = array_merge(func_get_args(), []);
				
				return call_user_func_array($closure, $arguments);
			}
		};

		$anon::bootAnonymous($registration);

		$field = $field ?: static::$defaultField;

		static::$customClientSide[$forRuleName][$field] = $anon;
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

				$keyRules = array_keys( $rules );

				$this->processDefaultRequiredFormat( $jsTypeFormat, $field_arr['orig'], $keyRules );				

				foreach( static::$requiredRules as $requiredRule )
				{
					if( in_array( $requiredRule.'_rule', $keyRules, true ) )
					{
						if( isset( static::$js_validation_rules[$field_arr['orig']] ) )
						{
							unset( static::$js_validation_rules[$field_arr['orig']][$requiredRule] );
						}
					}
				}

				foreach ( $rules  as $rule_name => $satisfier ) 
				{
					$clean_rule = $this->remove_appended_rule( $rule_name );
					
					$satis = ( isset( $satisfier[0]['satisfier'] ) ) ? $satisfier[0]['satisfier'] : '';

					$cus_err = ( $this->isset_empty( $satisfier, 1 ) ) ? $satisfier[1]['custom_error'] : [];

					$clientMessageOnly = ( isset( $satisfier[0]['client_message_only'] ) ) ? $satisfier[0]['client_message_only'] : false;

					$ucFirstRule = ucfirst( $rule_name );

					$errors = $this->js_errors( $clean_rule, $rule_name, $field, null, $satis, $cus_err, $ucFirstRule );

					if( isset( $this->cacheInstance[$field_arr['orig']][ $ucFirstRule ] ) )
					{
						$instance = $this->cacheInstance[$field_arr['orig']][ $ucFirstRule ];

						$field_or = $this->remove_number_sign( $field_arr['orig'] );

						$classDetails = [];

						$runClass = false;
						$fieldSet = false;

						if(isset(static::$customClientSide[$clean_rule]))
						{
							$anonField = static::$customClientSide[$clean_rule];
							$anonClass = $anonField[static::$defaultField] ?? null;
							
							if(isset($anonField[$field_or]))
							{
								$anonClass = $anonField[$field_or];
							}

							if(!empty($anonClass))
							{
								$anonClass::boot($instance, $jsTypeFormat, $clientMessageOnly);

								$jsFormat = $anonClass::getCLientSideFormat($field_or, $clean_rule, $satis, $errors, null);

								$fieldSet = true;
							}
							else
							{
								$runClass = true;
							}
						}

						if( (!isset(static::$customClientSide[$clean_rule]) || $runClass) && !$fieldSet )
						{
							$classDetails = $this->generateClassDetails($clean_rule, $jsTypeFormat);
						}

						$realKeyCheck = $clean_rule;

						if(!empty(static::$addMappings) && in_array($clean_rule, static::$rulesCommonClass, true))
						{
							$jsTypeFormat = \strtolower( $jsTypeFormat );	
							$classClientSide = \strtolower($jsTypeFormat.static::$commonClientSideClass.static::$clientSideSuffix);
							$altClassClientSide = \strtolower($jsTypeFormat.static::$commonClientSideClass.static::$altClientSideSuffix);
							
							 if(isset(static::$addMappings[$classClientSide]))
							 {
							 	$realKeyCheck = $classClientSide;
							 }
							 else if(isset(static::$addMappings[$altClassClientSide]))
							 {
							 	$realKeyCheck = $altClassClientSide;
							 }
						}
						
						if(!empty($classDetails) && !$classDetails['isClass'])
						{
							if(!empty(static::$addMappings))
							{
								if(isset(static::$addMappings[$realKeyCheck]))
								{
									$classDetails['isClass'] = true;
								}
							}
						}

						if(!empty($classDetails) && $classDetails['isClass'] && !$fieldSet)
						{
							$loadedDetails = $this->loadClientSideFile($classDetails['classPath'], $classDetails['className']);

							if(!$loadedDetails['classExists'])
							{
								if(!empty(static::$addMappings))
								{
									if(isset(static::$addMappings[$realKeyCheck]))
									{
										$loadedDetails['classExists'] = class_exists(static::$addMappings[$realKeyCheck]);
										$loadedDetails['className'] = static::$addMappings[$realKeyCheck];
									}
								}
							}
							
							if($loadedDetails['classExists'])
							{
								$loadedDetails['className']::boot($instance, $jsTypeFormat, $clientMessageOnly);

								if($jsTypeFormat == self::JQ_VALIDATION && in_array($clean_rule, static::$minMaxlengthRules, true))
								{
									if(!$satis[2])
									{
										$clean_rule = str_replace('length', '', $clean_rule);
									}
								}

								$jsFormat = $loadedDetails['className']::getCLientSideFormat($field_or, $clean_rule, $satis, $errors, null);
							}
						}
						else
						{
							if(!$fieldSet)
							{
								$jsFormat = $instance->getCLientSideFormat( $field_or, $clean_rule, $jsTypeFormat, $clientMessageOnly, $satis, $errors );
							}
						}
						
						if( !empty( $jsFormat ) )
						{
							static::$js_validation_rules = array_merge_recursive( static::$js_validation_rules, $jsFormat );
						}
					}
					else if( method_exists( $this, $rule_name ) ) 
					{
						$field_or = $this->remove_number_sign( $field_arr['orig'] );

						call_user_func_array( [$this, $rule_name], [$field_or, $satis, $errors] );
					}
				}
			}
		}
	}

	protected function processDefaultRequiredFormat( $jsTypeFormat, $field, array $rules = [] )
	{
		if( $jsTypeFormat == self::PARSLEY )
		{
			$required = 'required';
			$checkArr = [];

			foreach( static::$requiredRules as $requiredRule )
			{
				if(in_array($requiredRule.'_rule', $rules, true))
				{
					$checkArr[] = false;

					$required = $requiredRule;
				}
			}

			if(!in_array(false, $checkArr))
			{

				static::$js_validation_rules[$field][$required] 	= <<<JS
					data-parsley-required="false"
JS;
			}
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

		if( !empty( $this->cacheInstance ) && isset( $this->cacheInstance[$orig_field] ) )
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
		$arrExemptKey = ['clientSideJson', 'clientSideJsonMessages'];
		if( $perField )
		{
			$newArr = [];
			
			foreach( static::$js_validation_rules as $field => $rules )
			{
				if(in_array($field, $arrExemptKey))
				{
					$fieldJson = $field;

					if($field == 'clientSideJson')
					{
						$fieldJson = 'rules';
					}
					else if($field == 'clientSideJsonMessages')
					{
						$fieldJson = 'messages';
					}

					$newArr[$fieldJson] = $rules;
					continue;
				}

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
				else
				{
					$newArr[$field] .= $rules;
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
