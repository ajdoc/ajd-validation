<?php

namespace AJD_validation\Traits;

use Closure;
use AJD_validation\Helpers;
use AJD_validation\Contracts;
use AJD_validation\Factory;
use AJD_validation\Contracts\{
	AbstractDataSet
};

trait AjdValExtender
{
	public static function addRuleNamespace( $namespace )
	{
		array_push( static::$addRuleNamespace, $namespace );

		$err = static::get_errors_instance();

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

		$err = static::get_errors_instance();

		if( !EMPTY( $directory ) )
		{
			$err::addExceptionDirectory( $directory.'Exceptions'.DIRECTORY_SEPARATOR );
		}

		return static::get_ajd_instance();
	}

	public static function addFilterNamespace( $namespace )
	{
		$filter = static::get_filter_ins();

		$filter->addFilterNamespace( $namespace );

		return static::get_ajd_instance();
	}

	public static function addFilterDirectory( $directory )
	{
		$filter = static::get_filter_ins();

		$filter->addFilterDirectory( $directory );

		return static::get_ajd_instance();
	}

	public static function addClientSideNamespace( $namespace )
	{
		Helpers\Client_side::addNamespace( $namespace );

		return static::get_ajd_instance();
	}

	public static function addClientSideDirectory( $directory )
	{
		Helpers\Client_side::addDirectory( $directory );

		return static::get_ajd_instance();
	}

	public static function registerClass( $class_name, array $messages = [], array $passArgs = [], $path = null, $class_method = 'run', $namespace = null, $anonymousWay = true, $from_framework = null )
	{
		$object = null;
		$invokable = false;
		$class_name_object = false;

		if(is_object($class_name))
		{
			$class_name_object = true;
			$qualifiedClassName = get_class($class_name);
			$segments = explode('\\', $qualifiedClassName);
			$raw_class_name = end($segments);
			$raw_class_name = strtolower($raw_class_name);
			$object = $class_name;

			if(!empty($path))
			{
				$raw_class_name = strtolower($path);
			}
		}
		else
		{
			$raw_class_name = $class_name;
		}	

		$class_name = ucfirst( strtolower( $raw_class_name ) );

		$args = [];

		if(!empty($path) && !$class_name_object)
		{
			$args[] = $path;
		}

		$qualifiedClass = $class_name;

		if(!empty($namespace))
		{
			$qualifiedClass = $namespace.$class_name;	
		}

		if( !IS_NULL( $namespace ) )
		{
			$args[] = $namespace;
		}

		if( !empty( $path ) && empty( $from_framework ) && !$class_name_object )
		{
			$object = $path;

			if(is_string($path) && !is_object($path))
			{
				if($anonymousWay)
				{
					if(!class_exists($qualifiedClass))
					{
						require $path;
					}

					$object = new $qualifiedClass;
				}

				$err = static::get_errors_instance();

				$err::addExceptionDirectory( $path.DIRECTORY_SEPARATOR.'Exceptions'.DIRECTORY_SEPARATOR );
			}
		}

		if(!$anonymousWay)
		{
			static::$ajd_prop[ 'class_override' ][ $class_name.'_'.static::$rules_suffix ] = $args;
			static::$ajd_prop[ 'class_override' ][ $raw_class_name ] = [$from_framework, $class_method];
		}
		else
		{
			if(!empty($object) && method_exists($object, '__invoke'))
			{
				$invokable = true;
				$class_method = '__invoke';
			}

			if(
				!empty($object)
				&&
				method_exists($object, $class_method))
			{
				$realFunc = static::fromCallable([$object, $class_method]);
				
				if(!empty($args))
				{
					static::get_ajd_instance()->setArguments($args, $raw_class_name);
				}
				static::get_ajd_instance()->registerAsRule($realFunc, $messages, $raw_class_name, false, true, false);
			}
		}
	}

	public static function registerMethod( $rule, $object, array $messages = [], array $args = [], $anonymousWay = true, $from_framework = false )
	{
		if(!$anonymousWay)
		{
			static::$ajd_prop[ 'method_override' ][ $rule.'_'.static::$rules_suffix ] = $object;
			static::$ajd_prop[ 'method_override' ][ $rule ] = $from_framework;

			if( !EMPTY( $from_framework ) )
			{
				static::$ajd_prop[ 'method_override' ][ $from_framework ] = $args;
			}
		}
		else
		{
			if(method_exists($object, $rule))
			{
				$realFunc = static::fromCallable([$object, $rule]);
				if(!empty($args))
				{
					static::get_ajd_instance()->setArguments($args, $rule);
				}
				static::get_ajd_instance()->registerAsRule($realFunc, $messages, $rule, false, true, false);
			}
		}
	}

	public static function registerFunction($func_name, $func = null, array $messages = [], array $args = [], $anonymousWay = true, $last = false, $val_only = false)
	{
		if(!$anonymousWay)
		{
			$func_factory 	= static::get_factory_instance()->get_instance( false, true );
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

			if( ( !is_bool( $func ) || $func == false ) )
			{
				static::$ajd_prop[ 'function_override' ][ $func_name ] = !is_null( $func ) ? $func : $func_name;
			}
		}
		else
		{
			$realFunc = null;
			$bindObj = true;

			if(is_string($func_name) && function_exists($func_name))
			{
				$bindObj  = false;
				$realFunc = static::fromCallable($func_name);
			}
			else if(
				!empty($func) 
				&& 
				$func instanceof Closure
			)	
			{
				$realFunc = $func;
			}

			if(!empty($realFunc))
			{
				if(!empty($args))
				{
					static::get_ajd_instance()->setArguments($args, $func_name);
				}

				static::get_ajd_instance()->registerAsRule($realFunc, $messages, $func_name, false, true, $bindObj);
			}
		}
	}

	public static function registerExtension( $extension )
	{
		$name = $extension->getName();

		if( !isset( static::$ajd_prop['extensions'][ $name ] ) )
		{
			static::$ajd_prop['extensions'][ $name ] = $extension;
		}
		
		static::init_extensions(true);
	}

	public static function registerRulesMappings(array $mappings)
	{
		foreach($mappings as $rule => $exception)
        {
            Helpers\Rules_map::register($rule);
            Helpers\Rules_map::setException($rule, $exception);
        }

        static::processMappings();

        return static::get_ajd_instance();
	}

	public static function registerFiltersMappings(array $mappings)
	{
		Helpers\AJD_filter::registerFiltersMappings($mappings);

        return static::get_ajd_instance();
	}

	public static function registerClientSideMapping(array $mappings)
	{
		Helpers\Client_side::addMappings($mappings);

        return static::get_ajd_instance();
	}

	public static function registerCustomClientSide($forRuleName, \CLosure $registration, $field = null)
	{
		Helpers\Client_side::registerCustomClientSide($forRuleName, $registration, $field);
	}

	public static function addJSvalidationLibrary($jsValidationLibrary)
	{
		Helpers\Client_side::addJSvalidationLibrary($jsValidationLibrary);	
	}

	public static function registerLogicsMappings(array $mappings)
	{
		Helpers\When::registerLogicsMappings($mappings);

        return static::get_ajd_instance();
	}

	public static function addPackages(array $packages)
	{
		foreach($packages as $package)
		{
			if(class_exists($package)
				&& !isset(static::$packagesToRegister[$package])
			)
			{
				static::$packagesToRegister[$package] = $package;	
			}
		}

		static::boot();
	}

	public static function registerPackage(Contracts\ValidationProviderInterface $package)
	{
		if( !isset(static::$registeredPackaged[get_class($package)]) )
		{
			$package->register();
			
			static::processMappings();
			Helpers\AJD_filter::processMappings();
			Helpers\When::processMappings();

			$validations = $package::getValidationsCollection();
			$clientSides = $package::getClientSideCollection();
			
			if(!empty($validations))
			{
				static::$addValidationsMappings = array_merge(static::$addValidationsMappings, $validations);
			}

			if(!empty($clientSides))
			{
				static::registerClientSideMapping($clientSides);
			}

			static::$registeredPackaged[get_class($package)] = true;
		}
	}

	protected static function processMappings()
	{
		$mappings = Helpers\Rules_map::getMappings();
		
		if($mappings)
		{
			static::$addRulesMappings = array_merge(static::$addRulesMappings, $mappings);

			Factory\Class_factory::addRulesMappings($mappings);
			Helpers\Errors::addRulesMappings($mappings);

			Helpers\Rules_map::flush();
		}
	}

	public function createDataSet(callable $callable)
	{
		$that = $this;

		$anonClass =  new class($callable, $that) extends AbstractDataSet
		{
			protected $dataSet;
			protected $functions;
			protected $that;

			protected $invokeInString = 'invoke';
			protected $explodeString = 'to-';

			public function __construct($dataSet, $that)
            {
                $this->dataSet = $dataSet;

                $this->that = $that;

                $this->functions = $this->invoke($this->dataSet, [$this]);
            }

            protected function parseAnnotations($docComment)
            {
            	if(empty($docComment))
            	{
            		return [];
            	}

			    preg_match_all('/@([a-z]+?)\s+(.*?)\n/i', $docComment, $annotations);

			    if(!isset($annotations[1]) || count($annotations[1]) == 0)
			    {
			        return [];
			    }

			    return array_combine(array_map("trim",$annotations[1]), array_map("trim",$annotations[2]));
			}

			protected function checkHasDocComment(array $docComment)
			{
				return !empty($docComment);
			}

			protected function getReflectionFunction(callable $function)
			{
				return new \ReflectionFunction($function);
			}

			protected function getClosureDocComment(\ReflectionFunction $reflection)
			{
				return $reflection->getDocComment();
			}

            protected function checkInvokeClosure(callable $function, $invokeIn)
            {
            	$reflection = $this->getReflectionFunction($function);	
            	$docComment = $this->parseAnnotations($this->getClosureDocComment($reflection));

            	$checkInvoke = false;

            	if(!isset($docComment[$this->invokeInString]) || empty($docComment[$this->invokeInString]))
            	{
            		$checkInvoke = false;
            	}
            	else
            	{
            		$returnString = $docComment[$this->invokeInString];
            		$returnStringArr = explode($this->explodeString, $returnString);

            		$stringTo = $returnStringArr[1] ?? $returnStringArr[0];

	            	if(\strtolower($stringTo) === strtolower($invokeIn))
	            	{
	            		$checkInvoke = true;
	            	}
	            }

            	return [
            		'checkInvoke' => $checkInvoke,
            		'checkHasDocComment' => !empty($docComment)
            	];
            }

            protected function invoke(callable $closure, array $args)
            {
            	if($closure instanceof \Closure)
            	{
            		$closure = $closure->bindTo($this, self::class);
            	}

            	return \call_user_func_array($closure, $args);
            }

            public function field()
            {
            	return $this->commonProxy('field', [$this]);
            }

            protected function commonProxy($type, array $args = [])
            {
            	if($type == 'field')
            	{
            		if(!is_array($this->functions) && !is_callable($this->functions) && !is_string($this->functions))
	            	{
	            		return null;
	            	}
            	}
            	else
            	{
	            	if(!is_array($this->functions) && !is_callable($this->functions))
	            	{
	            		return null;
	            	}
	            }

	            if($type == 'field')
            	{
            		if(is_string($this->functions))
	            	{
	            		return $this->functions;
	            	}
            	}

            	if(is_callable($this->functions))
            	{
            		$checkInvoke = $this->checkInvokeClosure($this->functions, $type);

            		if($checkInvoke['checkInvoke'])
            		{
            			return $this->invoke($this->functions, $args);
            		}

            		return null;
            	}

				if(isset( $this->functions[$type] ) && !empty($this->functions[$type]))
            	{
            		return $this->invoke($this->functions[$type], $args);
            	}

				return null;
            }

            public function rules()
			{
				return $this->commonProxy('rules', [$this]);
			}

			public function preValidate($value = null, $field = null, $check_arr = true)
			{
				return $this->commonProxy('preValidate', [$value, $field, $check_arr, $this]);
			}

			public function validation($value = null, $key = null)
			{
				if(!is_array($this->functions) && is_callable($this->functions))
				{
					$checkInvoke = $this->checkInvokeClosure($this->functions, 'validation');
					
					if(!$checkInvoke['checkHasDocComment'] || $checkInvoke['checkInvoke'])
					{
						return $this->invoke($this->functions, [$value, $key, $this]);	
					}

					return false;
				}

				if(isset( $this->functions['validation'] ) && !empty($this->functions['validation']))
            	{
            		return $this->invoke($this->functions['validation'], [$value, $key, $this]);
            	}

				return false;
			}
		};

		return $anonClass;
	}

	public static function registerDataSet($registerNameDataSet, $dataSet, array $options = [], $inverse = false)
	{
		if(!empty($registerNameDataSet))
		{
			static::$registerDataSet[$registerNameDataSet]['dataSets'][] = $dataSet;
			static::$registerDataSet[$registerNameDataSet]['options'][] = $options;
			static::$registerDataSet[$registerNameDataSet]['inverse'][] = $inverse;
		}

		return static::get_ajd_instance();
	}

	public static function registerDataSets($registerNameDataSet, $dataSets, array $options = [], $inverse = false)
	{
		return static::addDataSets($dataSets, $options, $inverse, $registerNameDataSet, true);
	}

	public static function removeSpecificDataSetRegistry(array $names)
	{
		static::$removeSpecificDataSetRegistry = $names;

		return static::get_ajd_instance();
	}

	public static function flushDataSetRegistry()
	{
		static::$flushDataSetRegistry = true;

		return static::get_ajd_instance();
	}

	public static function clearDataSetRegistry(array $names = [])
	{
		$ajd = static::get_ajd_instance();

		if(!empty($names))
		{
			foreach($names as $name)
			{
				if(isset(static::$registerDataSet[$name]))
				{
					unset(static::$registerDataSet[$name]);
				}
			}

			return $ajd;
		}

		static::$registerDataSet = [];

		return $ajd;
	}

	public static function registerFilter($name, $function, array $extraArgs = [])
	{
		Helpers\AJD_filter::registerFilter($name, $function, $extraArgs);
	}

	public static function registerLogic($name, $function, array $extraArgs = [])
	{
		Helpers\When::registerLogic($name, $function, $extraArgs);
	}

	/**
     * @see \Closure::fromCallable()
     * @param callable $callable
     * @return \Closure
     */
    public static function fromCallable(callable $callable)
    {
        // In case we've got it native, let's use that native one!
        if(method_exists(\Closure::class, 'fromCallable')) 
        {
            return \Closure::fromCallable($callable);
        }

        return function () use ($callable) 
        {
            return call_user_func_array($callable, func_get_args());
        };
    }
}