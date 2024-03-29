<?php namespace AJD_validation\Helpers; 

use AJD_validation\Config\Config;
use AJD_validation\Vefja\Vefja;
use AJD_validation\Contracts\Nested_rule_exception;
use AJD_validation\Contracts\Invokable_rule_interface;
use AJD_validation\Constants\Lang;
use AJD_validation\Helpers\VarExport;
use AJD_validation\AJD_validation as v;
use InvalidArgumentException;

class Errors extends InvalidArgumentException
{

	public static $lang = Lang::EN;

	protected static $config_ins;

	protected static $error_msg = [];

	protected static $validation_err_msg = [];

	public $start_delimiter = '';

	public $end_delimiter = '</br>';

	protected static $appendErrorMsgMulti = 'at row {arr_key}.';
	protected static $errDir;

	protected $exceptionNamespace = 'AJD_validation\\Exceptions\\';

  	protected static $maxDepthOfString = 5;
    protected static $maxCountOfString = 10;
    protected static $maxReplacementOfString = '...';

    protected static $addExceptionNamespace = [];
    protected static $addExceptionDirectory = [];
    protected static $addRulesMappings = [];
    protected static $anonymousObj = [];
    protected static $anonymousObjErrorMessages = [];
    protected static $addLangDir = [];

	public function __construct( $lang = null )
	{
		if( !is_null( $lang ) ) 
		{
			static::$lang = $lang;
		}
		
		$config = static::get_config_ins();

		static::$error_msg = $config::get( 'error_msg' );
		
	}

	public static function addRulesMappings(array $mappings)
	{
		if($mappings)
		{
			static::$addRulesMappings = array_merge(static::$addRulesMappings, $mappings);
		}
	}

	public static function addAnonExceptions($rule, $exception)
	{
		static::$anonymousObj[$rule] = $exception;
	}

	public static function addAnonErrorMessages($rule, $exception)
	{
		static::$anonymousObjErrorMessages[$rule] = $exception;
	}

	public static function addLangDir($lang, $path, $create_write = false)
	{
		static::$addLangDir[$lang] = $path;

		if($create_write)
		{
			if(!is_dir($path))
			{
				mkdir($path,0777,TRUE);
			}

			if(file_exists($path))
			{
				$file_lang = $lang.'_lang.php';

				if(!file_exists($path.DIRECTORY_SEPARATOR.$file_lang))
				{
					$lang_dir = dirname(__DIR__).DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR;
					$lang_stubs = $lang_dir.'lang.stubs';

					$lang_stubs_result = require $lang_stubs;
					$addLangStubs = v::getAddLangStubs();

					if(!empty($addLangStubs))
					{
						foreach($addLangStubs as $stubs)
						{
							$addedStubs = require $stubs;

							if(!empty($addedStubs) && is_array($addedStubs))
							{
								if(isset($addedStubs['error_msg']))
								{
									$lang_stubs_result['error_msg'] = array_merge($lang_stubs_result['error_msg'], $addedStubs['error_msg']);
								}
							}
						}
					}
					
					$lang_arr = VarExport::export($lang_stubs_result, VarExport::FORCED_SHOW_ARRAY_KEY);

					$lang_stubs_result_str = <<<EOS
<?php 

use AJD_validation\Contracts\Abstract_exceptions as ex;

use AJD_validation\Exceptions as Assert;

// 1 parent key means Abstract_exceptions::ERR_DEFAULT
// 2 parent key means Abstract_exceptions::ERR_NEGATIVE when you inverse the result
// 0 child key usually means Abstract_exceptions::STANDARD
// if there child keys greater than 0 you may refer to that rule's exception class to see what it means.

return $lang_arr;
EOS;
				

					$file_lang = file_put_contents($path.DIRECTORY_SEPARATOR.$file_lang, $lang_stubs_result_str);
				}
			}
		}
	}

	public static function setLang($lang)
	{
		static::$lang = $lang;
		$config = static::get_config_ins(static::$lang);
		$newError = $config::get( 'error_msg' );

		if(!empty(static::$error_msg))
		{
			if(!empty($newError))
			{
				static::$error_msg = array_merge(static::$error_msg, $newError);
			}
		}
		else
		{
			static::$error_msg = $newError;
		}
	}

	public static function addExceptionNamespace( $namespace )
	{
		array_push( static::$addExceptionNamespace, $namespace );
	}

	public static function addExceptionDirectory( $directory )
	{
		array_push( static::$addExceptionDirectory, $directory );
	}

	public static function getExceptionNamespace()
	{
		return static::$addExceptionNamespace;
	}

	public static function getExceptionDirectory()
	{
		return static::$addExceptionDirectory;
	}

	public static function stringify($value, $depth = 1, $jsonEncode = true)
    {
        if ($depth >= static::$maxDepthOfString) 
        {
            return static::$maxReplacementOfString;
        }

        if( is_array($value) ) 
        {
    		return static::stringifyArray($value, $depth);
        }

        if( is_object($value) ) 
        {
            return static::stringifyObject($value, $depth);
        }

        if( is_resource($value) ) 
        {
            return sprintf('`[resource] (%s)`', get_resource_type($value));
        }

        if( is_float($value) ) 
        {
        	if ( is_infinite($value) ) 
        	{
        		return ($value > 0 ? '' : '-').'INF';
        	}

        	if( is_nan($value) ) 
        	{
        		return 'NaN';
        	}
        }

        if(!$jsonEncode)
        {
        	return $value;
        }

        return (@json_encode($value, (JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?: $value);
    }

    public static function stringifyArray(array $value, $depth = 1)
    {
        $nextDepth = ($depth + 1);

        if( $nextDepth >= static::$maxDepthOfString ) 
        {
            return  static::$maxReplacementOfString;
        }

        if( EMPTY($value) ) 
        {
             return '{ }';
        }

     	$total = count($value);
        $string = '';
        $current = 0;

        foreach($value as $key => $val)
        {
	 		if( $current++ >= static::$maxCountOfString) 
	 		{
		 		$string .= static::$maxReplacementOfString;
		 		break;
	 		}

 		 	if( !is_int($key) ) 
 			{
 				$string .= sprintf('%s: ', static::stringify($key, $nextDepth));
 			}

 			$string .= static::stringify($val, $nextDepth);

 			if($current !== $total) 
 			{
		 		$string .= ', ';
 			}
        }

     	return sprintf('{ %s }', $string);
    }

    public static function stringifyObject($value, $depth = 2)
    {
    	$nextDepth = $depth + 1;

    	if($value instanceof DateTime) 
    	{
    		return sprintf('"%s"', $value->format('Y-m-d H:i:s'));
    	}

    	$class = get_class($value);

    	if($value instanceof \Traversable) 
    	{
    		return sprintf('`[traversable] (%s: %s)`', $class, static::stringify(iterator_to_array($value), $nextDepth));
    	}

    	if($value instanceof \Exception) 
    	{
    		$errProp = [
		 		'message' => $value->getMessage(),
		 		'code' => $value->code(),
		 		'file' => $value->getFile().':'.$value->getLine(),
		 		'trace' => $value->getTraceAsString()
    		];

    		return sprintf('`[exception] (%s: %s)`', $class, static::stringify($errProp, $nextDepth));
    	}

    	if( method_exists($value, '__toString') ) 
    	{
    		return static::stringify($value->__toString(), $nextDepth);
    	}

    	$errProp = static::stringify(get_object_vars($value), $nextDepth);

    	return sprintf('`[object] (%s: %s)`', $class, str_replace('`', '', $errProp));
    }

	public static function get_config_ins($lang = null)
	{
		if( IS_NULL( static::$config_ins ) || !empty($lang) ) 
		{
			$dir = dirname( dirname( __FILE__ ) ).DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR;

			static::$errDir = $dir;
			$realLang = static::$lang;

			if(!empty($lang))
			{
				$realLang = $lang;
			}
			
			$file_name = $realLang.'_lang.php';
			
			static::$config_ins = new Config( $file_name, $dir );

			if(!empty(static::$addLangDir))
			{
				foreach(static::$addLangDir as $lang => $path)
				{
					if(file_exists($path))
					{
						if(file_exists($path.DIRECTORY_SEPARATOR.$file_name))
						{
							static::$config_ins = new Config( $file_name, $path );
						}
					}
				}
			}
		}

		return static::$config_ins;
	}

	public function get_lang()
	{
		return static::$lang;
	}

	public function get_error( $rule )
	{
		if( isset( static::$error_msg[ $rule ] ) )
		{
			return static::$error_msg[ $rule ];
		}
	}

	public function get_errors()
	{
		return static::$error_msg;
	}

	public function set_errors( $rule, $msg )
	{
		static::$error_msg[ $rule ] = $msg;
	}

	public function all()
	{
		return static::outputError();
	}

	public function set_validation_errors( $msg = [] )
	{
		static::$validation_err_msg = $msg;

		return $this;
	}

	public function find( $field )
	{  
		return static::outputError( false, $field );
	}

	public function first( $field )
	{  
		return static::outputError( false, $field, 0 );
	}

	public function firstAll()
	{
		$messages = static::outputError(true);
		$newMsgArr = [];

		if( !EMPTY( $messages ) )
		{
			foreach( $messages as $field => $message )
			{
				$currentRule = key($message);
				$currentMsg = current($message);
				
				$newMsgArr[$field][$currentRule] = $currentMsg;
			}
		}
		
		return $newMsgArr;
	}

	public function assocMsg()
	{
		return static::outputError(true);
	}

	public static function outputError( $assoc_msg = true, $keys = null, $err_key = null )
	{
		$msg = [];

		foreach( static::$validation_err_msg as $key => $value ) 
		{
			if(is_string($value))
			{
				$msg[] = $value;
				continue;
			}

			$len = ( int )count( $value );

			for( $i = 0; $i < $len; $i++ ) 
			{
				$val_keys = array_keys( $value );
				$type = $val_keys[ $i ];

				if( $assoc_msg ) 
				{
					if( is_string( $type ) ) 
					{
						$msg[ $key ][ $val_keys[ $i ] ] = $value[ $val_keys[ $i ] ];
					}	
					else if( is_numeric( $type ) )
					{
						$msg[ $key ][ $val_keys[ $i ] ][] = $value[ $val_keys[ $i ] ];	
					}
				} 
				else 
				{
					if( is_numeric( $type ) ) 
					{
						$msg[ $key ][] = $value[ $val_keys[ $i ] ];
					}
					else if( is_string( $type ) )
					{
						$msg[ $key ][] = $value[ $val_keys[ $i ] ];
					}
				}
			}
		}
		
		if( !empty( $keys ) ) 
		{
			if( isset( $msg[ $keys ] ) ) $msg = $msg[ $keys ];

			if( !is_null( $err_key ) )

				if( isset( $msg[ $err_key ] ) ) $msg = $msg[ $err_key ];
		}

		return $msg;
	}

	public function removeFieldError($field)
	{
		if(!empty(static::$validation_err_msg))
		{
			unset(static::$validation_err_msg[$field]);
		}
	}

	public function toStringErr( $msg = array(), $addParent = false )
	{	
		$err_msg = !empty( $msg ) ? $msg : static::$validation_err_msg;
		
		if( !is_array( $msg ) )
		{
			$err_msg = $this->start_delimiter.$err_msg.$this->end_delimiter;
		}
		else
		{
			$str = "";

			if( $addParent )
			{
				$prefix = str_repeat('&nbsp;', 1 * 2).'- ';

				$allRuleException = Vefja::singleton('AJD_validation\\Exceptions\\All_rule_exception');

				$parentErrorMessage	= $allRuleException->getExceptionMessage();

				$checkArr = [];

				foreach( $err_msg as $field => $rules )
				{	
					if(is_string($rules))
					{
						$str .= $this->start_delimiter.$rules.$this->end_delimiter;
						continue;
					}

					$currRule = current( $rules );
					$currRuleKey = key( $rules );

					if( is_numeric( key($currRule) ) )
					{
						foreach( $rules as $k => $rule )
						{
							$arrCleanField = current( $rule );

							$str = $this->processErrors( $arrCleanField, $rule, $parentErrorMessage, $prefix, $str, $currRuleKey, $field, $checkArr );	
							$checkArr[$field]  = true;	
						}
					}
					else
					{
						$cleanField	= current($rules);
						
						$str = $this->processErrors( $cleanField, $rules, $parentErrorMessage, $prefix, $str, $currRuleKey, $field, $checkArr );

						$checkArr[$field] = true;
					}
				}
			}
			else
			{
				// $err_msg = $this->flattened_array( $err_msg );
				foreach( $err_msg as $field => $rules ) 
				{
					if(is_string($rules))
					{
						$str .= $this->start_delimiter.$rules.$this->end_delimiter;
						continue;
					}
					
					$currRule = current( $rules );
					
					foreach( $rules as $rule )
					{
						if( is_numeric( key($currRule) ) )
						{
							$currSubRule = current($rule);

							if( is_numeric( key( $currSubRule ) ) )
							{
								foreach( $rule as $k => $r )	
								{
									$ruleStr = $this->processRuleStrArr($r);

									$str .= $ruleStr['ruleErrStr'];
								}
							}
							else
							{
								$ruleStr = $this->processRuleStrArr($rule);

								$str .= $ruleStr['ruleErrStr'];
							}
						}
						else
						{
							$str .= $this->start_delimiter.$rule['errors'].$this->end_delimiter;
						}
					}
				}
			}

			$err_msg = $str;
		}

		return $err_msg;

	}

	public static function getDelimiters()
	{
		$self = new static;
		return [
			'start_delimiter' => $self->start_delimiter,
			'end_delimiter' => $self->end_delimiter
		];
	}

	public function processRuleStrArr( array $ruleErrors )
	{
		$obj = $this;

		$ruleStr = array_map(function($r) use ( &$obj )
		{
			return $obj->start_delimiter.$r['errors'].$obj->end_delimiter;
		}, $ruleErrors);

		return [
			'ruleErrArr' => $ruleStr,
			'ruleErrStr' => implode('', $ruleStr)
		];
	}

	protected function processErrors( $cleanField, $rules, $parentErrorMessage, $prefix, $str, $currRule, $field, array $checkArr )
	{
		if( EMPTY( $checkArr[$field] ) )
		{
			$currCleanField = current( $cleanField );
			
			if( is_array( $currCleanField ) )
			{
				$parErrArr = [
					'field'	=> $currCleanField['clean_field']
				];
			}
			else
			{
				$parErrArr = [
					'field' => $cleanField['clean_field']
				];
			}

			$parErrMsg = static::formatError( $parErrArr, $parentErrorMessage );

			$str .= $this->start_delimiter.$parErrMsg.$this->end_delimiter;
		}

		foreach( $rules as $key => $rule )
		{
			$currSubRule = current($rule);
			
			if( is_array( $currSubRule ) )
			{
				$obj = $this;

				$ruleStr = array_map(function($r) use( &$prefix, &$obj )
				{
					return $prefix.$obj->start_delimiter.$r['errors'].$obj->end_delimiter;
				}, $rule);

				$str .= implode('', $ruleStr);
			}
			else
			{
				if( ISSET( $rule['errors'] ) )
				{
					$str .= $prefix.$this->start_delimiter.$rule['errors'].$this->end_delimiter;
				}
			}
		}

		return $str;
	}

	public function toJsonErr( $msg = array() )
	{
		$err_msg = !EMPTY( $msg ) ? $msg : static::$validation_err_msg;

		if( extension_loaded( 'json' ) )
		{
			return json_encode( $err_msg );
		}
		else 
		{
			return '{}';
		}
	}

	protected function flattened_array( array $arr )
	{
		$flat_arr = iterator_to_array(

			new \RecursiveIteratorIterator(

				new \RecursiveArrayIterator( $arr )

			), false

		);

		return $flat_arr;
	}

	public function replace_err_msg( $key, $new_msg )
	{
		$err_msg = static::$error_msg;

		if( in_array( $key, array_keys( $err_msg ) ) ) 
		{
			$err_msg[ $key ] = $new_msg;
		} 

		static::$error_msg = $err_msg;
	}

	public function processMultiMsg( $arr_key )
	{
		$msg_arr = [
			'arr_key' => $arr_key
		];

		return $this->replaceErrorPlaceholder( $msg_arr, static::$appendErrorMsgMulti );
	}

	public static function formatError( array $message_details, $message, $pattern = '/{(\w+)}/', $jsonEncode = true )
	{
		$newMessage = preg_replace_callback(
           $pattern,
            function ($match) use (&$message_details, $jsonEncode) {
     			
                if( !isset( $message_details[$match[1]] ) )  
               	{
                    return $match[0];
                }

                $real_match = $match[0];

                if( isset( $match[1] ) )
                {
                	$real_match = $match[1];
                }
                
                if( isset( $message_details[ $match[1] ] ) )
                {
            		$value = $message_details[$match[1]];	
                }
                
                if('name' == $real_match && is_string( $value ) ) 
                {
                    return $value;
                }

                return static::stringify($value, 1, $jsonEncode);
            },
            $message
        );

        return $newMessage;
	}

	public function replaceErrorPlaceholder( array $message_details, $message )
	{
		$newMessage = static::formatError( $message_details, $message );
		$newMessage = static::formatError( $message_details, $newMessage, '/:(\w+)/' );

		return $newMessage;
	}

	public function processExceptions( $rule_name, $called_class, $rule_instance, $satisfier, $values, $inverse, array $errors, $passRuleObj = null, $formatter = null, array $extraOptions = [] )
	{
		$exception_class = $this->exceptionNamespace.$called_class.'_exception';
		$qualified_exception_class = $exception_class;

		$is_annon_class = false;
		$lower_rule_name = strtolower($rule_name.'_'.v::$rules_suffix);

		if( isset($rule_instance[$called_class]) 
			&& $rule_instance[$called_class] instanceof Invokable_rule_interface 
			&& !isset(static::$anonymousObj[$called_class])
		)
		{
			$exception_class = $this->exceptionNamespace.'Common_invokable_rule'.'_exception';
		}
	
		$exceptionObj = null;
		$exception_arr = [];
		
		if( !EMPTY( static::$addExceptionDirectory ) )
		{
			foreach( static::$addExceptionDirectory as $key => $directory )
			{
				$namespace = '';

				if( ISSET( static::$addExceptionNamespace[ $key ] ) )
				{
					$namespace 	= static::$addExceptionNamespace[ $key ];
				}

				$exceptionPath = $directory.$called_class.'_exception.php';

				$requiredFiles = get_required_files();

				$search = array_search($exceptionPath, $requiredFiles);
				
				if( file_exists($exceptionPath) )
				{
					$exception_class = $namespace.$called_class.'_exception';
					
					if( !class_exists($exception_class) && empty( $search ) )
					{
						require $exceptionPath;
					}
				}
			}
		}

		if( isset(static::$anonymousObj[$called_class]) )
		{
			$is_annon_class = true;
		}

		if(!class_exists($exception_class) && !$is_annon_class)
		{
			if(
				!empty(static::$addRulesMappings)
				&&
				isset(static::$addRulesMappings[$lower_rule_name])
			)
			{
				$ruleMapKey = key(static::$addRulesMappings[$lower_rule_name]);
				
				$exception_class = static::$addRulesMappings[$lower_rule_name][$ruleMapKey];
			}
		}

		if( class_exists( $exception_class ) || $is_annon_class ) 
		{
			if(!empty($passRuleObj))
			{
				$ruleCh = $passRuleObj;	
				$rule = $passRuleObj;
			}
			else
			{
				$ruleCh = $rule_instance[ $called_class ];	
				$rule = $rule_instance[ $called_class ];	
			}
			
			if( isset(static::$anonymousObj[$called_class]) )
			{
				$exception_class_obj = static::$anonymousObj[$called_class];

				$exception_class_obj::setFromRuleName(strtolower($rule_name));
				
				if(isset(static::$anonymousObjErrorMessages[$called_class]))
				{
					$exception_class_obj = static::$anonymousObjErrorMessages[$called_class];
				}
				else
				{
					$rule::getAnonExceptionMessage($exception_class_obj, $rule);
				}
			}
			else
			{
				$exception_class_obj = Vefja::singleton($exception_class);
			}
			
			if( !empty( $exception_class_obj ) )
			{
				if( isset( $rule_instance[ $called_class ] ) 
					|| !empty($passRuleObj)
				)
				{
					$params = array_merge(
						get_class_vars( get_class( $rule ) ),
						get_object_vars( $rule ),
						compact('satisfier'),
						compact('values'),
						compact('inverse')
					);
					
					$params['called_class'] = $called_class;
					
					if( 
						$ruleCh instanceof Invokable_rule_interface 
						&& !isset(static::$anonymousObj[$called_class])
					)
					{
						$params['id_pass'] = $qualified_exception_class;
					}

					$exception_class_obj->configure($params);

					$exceptionObj = $exception_class_obj;

					if( 
						$ruleCh instanceof Invokable_rule_interface 
						&& !isset(static::$anonymousObj[$called_class])
					)
					{
						$ruleCh->setException($exceptionObj);
						
						$message = $ruleCh($values, $satisfier, null, null, null);
					}
					else
					{
						$message = $exception_class_obj->getExceptionMessage();
					}

					if(!empty($formatter))
					{
						$formatter->appendOptions($extraOptions);
						$formatField = $extraOptions['clean_field'] ?? $extraOptions['orig_field'] ?? null;
						$formatterResult = $formatter->format($message, $exceptionObj, $formatField, $satisfier, $values);

						if(!empty($formatterResult))
						{
							$message = $formatterResult;
						}
					}
					
					$errors[$rule_name] = $message;
					
					$exception_arr[] = $exceptionObj;
				}
			}
		}

		/*if( !EMPTY( $exception_arr ) )
		{
			// $allRuleException 		= Vefja::singleton('AJD_validation\\Contracts\\All_rule_exception');

			$allRuleException		= new All_rule_exception;
			array_unshift($exception_arr, $allRuleException);

			$nested_exception 	= new Nested_rule_exception;

			print_r( $nested_exception->setRelated($exception_arr)->getFullMessage());
		}*/

		return [
			'errors' => $errors,
			'exObj' => $exceptionObj
		];
	}
}

