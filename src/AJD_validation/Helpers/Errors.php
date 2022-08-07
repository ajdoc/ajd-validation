<?php namespace AJD_validation\Helpers; 

use AJD_validation\Config\Config;
use AJD_validation\Vefja\Vefja;
use AJD_validation\Contracts\Nested_rule_exception;
use AJD_validation\Contracts\Invokable_rule_interface;
use AJD_validation\Constants\Lang;

use InvalidArgumentException;

class Errors extends InvalidArgumentException
{

	public static $lang 					= Lang::EN;

	protected static $config_ins;

	protected static $error_msg 			= array();

	protected static $validation_err_msg 	= array();

	public $start_delimiter 				= '';

	public $end_delimiter 					= '</br>';

	protected static $appendErrorMsgMulti 	= 'at row {arr_key}.';
	protected static $errDir;

	protected $exceptionNamespace 			= 'AJD_validation\\Exceptions\\';

  	protected static $maxDepthOfString = 5;
    protected static $maxCountOfString = 10;
    protected static $maxReplacementOfString = '...';

    protected static $addExceptionNamespace 	= array();
    protected static $addExceptionDirectory 	= array();

    protected static $anonymousObj 	= [];

    protected static $addLangDir 	= [];

	public function __construct( $lang = NULL )
	{
		if( !IS_NULL( $lang ) ) 
		{
			static::$lang 			= $lang;
		}
		
		$config 					= static::get_config_ins();

		static::$error_msg 			= $config::get( 'error_msg' );
		
	}

	public static function addAnonExceptions($rule, $exception)
	{
		static::$anonymousObj[$rule] = $exception;
	}

	public static function addLangDir($lang, $path)
	{
		static::$addLangDir[$lang] = $path;
	}

	public static function setLang($lang)
	{
		static::$lang = $lang;

		$config 					= static::get_config_ins(static::$lang);
		$newError 					= $config::get( 'error_msg' );

		if(!empty(static::$error_msg))
		{
			static::$error_msg 			= array_merge(static::$error_msg, $newError);
		}
		else
		{
			static::$error_msg 			= $newError;
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

	public static function stringify($value, $depth = 1)
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

        return (@json_encode($value, (JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?: $value);
    }

    public static function stringifyArray(array $value, $depth = 1)
    {
        $nextDepth  = ($depth + 1);

        if( $nextDepth >= static::$maxDepthOfString ) 
        {
            return  static::$maxReplacementOfString;
        }

        if( EMPTY($value) ) 
        {
             return '{ }';
        }

     	$total 		= count($value);
        $string 	= '';
        $current 	= 0;

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

 			$string 	.= static::stringify($val, $nextDepth);

 			if($current !== $total) 
 			{
		 		$string .= ', ';
 			}
        }

     	return sprintf('{ %s }', $string);
    }

    public static function stringifyObject($value, $depth = 2)
    {
    	$nextDepth 	= $depth + 1;

    	if($value instanceof DateTime) 
    	{
    		return sprintf('"%s"', $value->format('Y-m-d H:i:s'));
    	}

    	$class 			= get_class($value);

    	if($value instanceof Traversable) 
    	{
    		return sprintf('`[traversable] (%s: %s)`', $class, static::stringify(iterator_to_array($value), $nextDepth));
    	}

    	if($value instanceof Exception) 
    	{
    		$errProp 		= array(
		 		'message' 	=> $value->getMessage(),
		 		'code' 		=> $value->code(),
		 		'file'		=> $value->getFile().':'.$value->getLine(),
		 		'trace'		=> $value->getTraceAsString()
    		);

    		return sprintf('`[exception] (%s: %s)`', $class, static::stringify($errProp, $nextDepth));
    	}

    	if( method_exists($value, '__toString') ) 
    	{
    		return static::stringify($value->__toString(), $nextDepth);
    	}

    	$errProp 		= static::stringify(get_object_vars($value), $nextDepth);

    	return sprintf('`[object] (%s: %s)`', $class, str_replace('`', '', $errProp));
    }

	public static function get_config_ins($lang = null)
	{
		if( IS_NULL( static::$config_ins ) || !empty($lang) ) 
		{
			$dir 				= dirname( dirname( __FILE__ ) ).DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR;

			static::$errDir 	= $dir;

			$realLang 			= static::$lang;

			if(!empty($lang))
			{
				$realLang 		= $lang;
			}
			
			$file_name 			= $realLang.'_lang.php';
			
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
		if( ISSET( static::$error_msg[ $rule ] ) )
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
		static::$error_msg[ $rule ] 	= $msg;
	}

	public function all()
	{
		return static::outputError();
	}

	public function set_validation_errors( $msg = array() )
	{
		static::$validation_err_msg = $msg;

		return $this;
	}

	public function find( $field )
	{  
		return static::outputError( FALSE, $field );
	}

	public function first( $field )
	{  
		return static::outputError( FALSE, $field, 0 );
	}

	public function firstAll()
	{
		$messages 	= static::outputError(TRUE);
		$newMsgArr 	= array();

		if( !EMPTY( $messages ) )
		{
			foreach( $messages as $field => $message )
			{
				$currentRule 		= key($message);
				$currentMsg 		= current($message);
				
				$newMsgArr[$field][$currentRule] 	= $currentMsg;
			}
		}
		
		return $newMsgArr;
	}

	public function assocMsg()
	{
		return static::outputError( TRUE );
	}

	public static function outputError( $assoc_msg = TRUE, $keys = NULL, $err_key = NULL )
	{
		$msg 							= array();

		foreach( static::$validation_err_msg as $key => $value ) 
		{
			$len 	= ( int )count( $value );

			for( $i = 0; $i < $len; $i++ ) 
			{
				$val_keys 	= array_keys( $value );

				$type 		= $val_keys[ $i ];

				if( $assoc_msg ) 
				{
					if( is_string( $type ) ) 
					{
						$msg[ $key ][ $val_keys[ $i ] ] 	= $value[ $val_keys[ $i ] ];
					}	
					else if( is_numeric( $type ) )
					{
						$msg[ $key ][ $val_keys[ $i ] ][] 	= $value[ $val_keys[ $i ] ];	
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
						$msg[ $key ][]  = $value[ $val_keys[ $i ] ];
					}
				}
			}
		}
		
		if( !EMPTY( $keys ) ) 
		{
			if( ISSET( $msg[ $keys ] ) ) $msg = $msg[ $keys ];

			if( !IS_NULL( $err_key ) )

				if( ISSET( $msg[ $err_key ] ) ) $msg = $msg[ $err_key ];
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

	public function toStringErr( $msg = array(), $addParent = FALSE )
	{	
		$err_msg 		= !EMPTY( $msg ) ? $msg : static::$validation_err_msg;
		
		if( !is_array( $msg ) )
		{
			$err_msg 	= $this->start_delimiter.$err_msg.$this->end_delimiter;
		}
		else
		{
			$str 		= "";

			if( $addParent )
			{
				$prefix 		= str_repeat('&nbsp;', 1 * 2).'- ';

				$allRuleException 	= Vefja::singleton('AJD_validation\\Exceptions\\All_rule_exception');

				$parentErrorMessage	= $allRuleException->getExceptionMessage();

				$checkArr 			= array();

				foreach( $err_msg as $field => $rules )
				{	
					$currRule 	= current( $rules );
					$currRuleKey= key( $rules );

					if( is_numeric( key($currRule) ) )
					{
						foreach( $rules as $k => $rule )
						{
							$arrCleanField 	= current( $rule );

							$str 	= $this->processErrors( $arrCleanField, $rule, $parentErrorMessage, $prefix, $str, $currRuleKey, $field, $checkArr );	

							$checkArr[$field] 	= TRUE;	
						}

					}
					else
					{
						$cleanField	= current($rules);
						
						$str 		= $this->processErrors( $cleanField, $rules, $parentErrorMessage, $prefix, $str, $currRuleKey, $field, $checkArr );

						$checkArr[$field] 	= TRUE;
					}

					
				}
			}
			else
			{
				// $err_msg 	= $this->flattened_array( $err_msg );

				foreach( $err_msg as $field => $rules ) 
				{
					$currRule 	= current( $rules );
					
					foreach( $rules as $rule )
					{
						if( is_numeric( key($currRule) ) )
						{
							$currSubRule 	= current($rule);

							if( is_numeric( key( $currSubRule ) ) )
							{
								foreach( $rule as $k => $r )	
								{
									$ruleStr 		= $this->processRuleStrArr($r);

									$str 			.= $ruleStr['ruleErrStr'];
								}
							}
							else
							{
								$ruleStr 		= $this->processRuleStrArr($rule);

								$str 			.= $ruleStr['ruleErrStr'];
							}
						}
						else
						{
							$str       .= $this->start_delimiter.$rule['errors'].$this->end_delimiter;
						}
					}
				}

			}

			$err_msg 		= $str;

		}

		return $err_msg;

	}

	public static function getDelimiters()
	{
		$self = new static;
		return [
			'start_delimiter' => $self->start_delimiter,
			'end_delimiter' 	=> $self->end_delimiter
		];
	}

	public function processRuleStrArr( array $ruleErrors )
	{
		$obj 			= $this;

		$ruleStr 		= array_map(function($r) use ( &$obj )
		{
			return $obj->start_delimiter.$r['errors'].$obj->end_delimiter;
		}, $ruleErrors);

		return array(
			'ruleErrArr'	=> $ruleStr,
			'ruleErrStr'	=> implode('', $ruleStr)
		);
	}

	protected function processErrors( $cleanField, $rules, $parentErrorMessage, $prefix, $str, $currRule, $field, array $checkArr )
	{
		if( EMPTY( $checkArr[$field] ) )
		{
			$currCleanField 	= current( $cleanField );
			
			if( is_array( $currCleanField ) )
			{
				$parErrArr 	= array(
					'field'	=> $currCleanField['clean_field']
				);
			}
			else
			{
				$parErrArr 	= array(
					'field'	=> $cleanField['clean_field']
				);
			}

			$parErrMsg 	= static::formatError( $parErrArr, $parentErrorMessage );

			$str       .= $this->start_delimiter.$parErrMsg.$this->end_delimiter;
		}

		foreach( $rules as $key => $rule )
		{
			$currSubRule 	= current($rule);
			
			if( is_array( $currSubRule ) )
			{
				$obj 		= $this;

				$ruleStr 	= array_map(function($r) use( &$prefix, &$obj )
				{
					return $prefix.$obj->start_delimiter.$r['errors'].$obj->end_delimiter;
				}, $rule);

				$str       .= implode('', $ruleStr);
			}
			else
			{
				if( ISSET( $rule['errors'] ) )
				{
					$str       .= $prefix.$this->start_delimiter.$rule['errors'].$this->end_delimiter;
				}
			}
		}

		return $str;
	}

	public function toJsonErr( $msg = array() )
	{
		$err_msg 		= !EMPTY( $msg ) ? $msg : static::$validation_err_msg;

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
		$flat_arr 			= iterator_to_array(

			new \RecursiveIteratorIterator(

				new \RecursiveArrayIterator( $arr )

			), false

		);

		return $flat_arr;
	}

	public function replace_err_msg( $key, $new_msg )
	{
		$err_msg 				= static::$error_msg;

		if( in_array( $key, array_keys( $err_msg ) ) ) 
		{
			$err_msg[ $key ] 	= $new_msg;
		} 

		static::$error_msg 		= $err_msg;
	}

	public function processMultiMsg( $arr_key )
	{
		$msg_arr 		= array(
			'arr_key'	=> $arr_key
		);

		return $this->replaceErrorPlaceholder( $msg_arr, static::$appendErrorMsgMulti );
	}

	public static function formatError( array $message_details, $message, $pattern = '/{(\w+)}/' )
	{
		$newMessage 	= preg_replace_callback(
           $pattern,
            function ($match) use (&$message_details) {
     			
                if( !ISSET( $message_details[$match[1]] ) )  
               	{
                    return $match[0];
                }

                $real_match 	= $match[0];

                if( ISSET( $match[1] ) )
                {
                	$real_match = $match[1];
                }
                
                if( ISSET( $message_details[ $match[1] ] ) )
                {
            		$value 		= $message_details[$match[1]];	
                }
                
                if('name' == $real_match AND is_string( $value ) ) 
                {
                    return $value;
                }

                return static::stringify($value);
            },
            $message
        );

        return $newMessage;
	}

	public function replaceErrorPlaceholder( array $message_details, $message )
	{
		$newMessage 	= static::formatError( $message_details, $message );
		$newMessage 	= static::formatError( $message_details, $newMessage, '/:(\w+)/' );

		return $newMessage;
	}

	public function processExceptions( $rule_name, $called_class, $rule_instance, $satisfier, $values, $inverse, array $errors, $passRuleObj = null  )
	{
		$exception_class 			= $this->exceptionNamespace.$called_class.'_exception';
		$qualified_exception_class = $exception_class;

		$is_annon_class = false;

		if( isset($rule_instance[$called_class]) 
			&& $rule_instance[$called_class] instanceof Invokable_rule_interface 
			&& !isset(static::$anonymousObj[$called_class])
		)
		{
			$exception_class 			= $this->exceptionNamespace.'Common_invokable_rule'.'_exception';
		}
	
		$exceptionObj 				= NULL;
		$exception_arr 				= array();
		
		if( !EMPTY( static::$addExceptionDirectory ) )
		{
			foreach( static::$addExceptionDirectory as $key => $directory )
			{
				$namespace 		= '';

				if( ISSET( static::$addExceptionNamespace[ $key ] ) )
				{
					$namespace 	= static::$addExceptionNamespace[ $key ];
				}

				$exceptionPath 	= $directory.$called_class.'_exception.php';

				$requiredFiles 	= get_required_files();

				$search 		= array_search($exceptionPath, $requiredFiles);
				
				if( file_exists($exceptionPath) )
				{
					$exception_class = $namespace.$called_class.'_exception';
					
					if( !class_exists($exception_class) AND EMPTY( $search ) )
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

		if( class_exists( $exception_class ) || $is_annon_class ) 
		{
			if(!empty($passRuleObj))
			{
				$ruleCh 		= $passRuleObj;	
				$rule 			= $passRuleObj;

			}
			else
			{
				$ruleCh 		= $rule_instance[ $called_class ];	
				$rule 			= $rule_instance[ $called_class ];	
			}

			if( isset(static::$anonymousObj[$called_class]) )
			{
				$exception_class_obj 	= static::$anonymousObj[$called_class];

				$rule::getAnonExceptionMessage($exception_class_obj);
			}
			else
			{
				$exception_class_obj 	= Vefja::singleton($exception_class);
			}
			
			if( !EMPTY( $exception_class_obj ) )
			{
				if( ISSET( $rule_instance[ $called_class ] ) 
					|| !empty($passRuleObj)
				)
				{
					$params 		= array_merge(
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

					$exceptionObj 			= $exception_class_obj;

					if( 
						$ruleCh instanceof Invokable_rule_interface 
						&& !isset(static::$anonymousObj[$called_class])
					)
					{
						$ruleCh->setException($exceptionObj);
						
						$message 	= $ruleCh($values, $satisfier, null, null, null);
					}
					else
					{
						$message 				= $exception_class_obj->getExceptionMessage();
					}
					
					$errors[$rule_name]		= $message;
					
					$exception_arr[]  		= $exceptionObj;
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

		return array( 
			'errors' 	=> $errors,
			'exObj'		=> $exceptionObj
		);
	}
}

