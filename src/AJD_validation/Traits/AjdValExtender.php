<?php

namespace AJD_validation\Traits;

use Closure;
use AJD_validation\Helpers;
use AJD_validation\Contracts;
use AJD_validation\Factory;

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

		if( !ISSET( static::$ajd_prop['extensions'][ $name ] ) )
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

			if(!empty($validations))
			{
				static::$addValidationsMappings = array_merge(static::$addValidationsMappings, $validations);
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
		}
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