<?php namespace AJD_validation\Factory;

use ReflectionClass;
use AJD_validation\Contracts\Abstract_common;
use AJD_validation\Factory\Factory_interface;

class Class_factory implements Factory_interface
{
	const DS = DIRECTORY_SEPARATOR;

	protected $filter_namespace = ['AJD_validation\\Filters\\'];
	protected $rules_namespace 	= ['AJD_validation\\Rules\\'];
	protected $rules_suffix = 'rule';	
	protected $classDontPutArgs = [
		'Filtervar_rule', 'Numeric_filter', 'Email_filter'
	];

	protected static $addRulesMappings = [];
	protected static $addFiltersMappings = [];
	protected static $addLogicsMappings = [];

	public static function addRulesMappings(array $mappings)
	{
		if($mappings)
		{
			static::$addRulesMappings = array_merge(static::$addRulesMappings, $mappings);
		}
	}

	public static function addFiltersMappings(array $mappings)
	{
		if($mappings)
		{
			static::$addFiltersMappings = array_merge(static::$addFiltersMappings, $mappings);
		}
	}

	public static function addLogicsMappings(array $mappings)
	{
		if($mappings)
		{
			static::$addLogicsMappings = array_merge(static::$addLogicsMappings, $mappings);
		}
	}

	public function get_rules_namespace()
	{
		return $this->rules_namespace;
	}

	public function get_filter_namespace()
	{
		return $this->filter_namespace;
	}

	public function set_rules_namespace( $namespace )
	{
		$this->rules_namespace = $namespace;
	}

	public function set_filter_namespace( $namespace )
	{
		$this->filter_namespace = $namespace;
	}

	public function append_rules_namespace( $rules_namespace )
	{
		array_push( $this->rules_namespace, $rules_namespace );
	}

	public function prepend_rules_namespace( $rules_namespace )
	{
		array_unshift( $this->rules_namespace, $rules_namespace );
	}

	public function append_filter_namespace( $filter_namespace )
	{
		array_push( $this->filter_namespace, $filter_namespace );
	}

	public function prepend_filter_namespace( $filter_namespace )
	{
		array_unshift( $this->filter_namespace, $filter_namespace );
	}

	public function rules( $rules_path = NULL, $rule_name = null, $args = [], $filter = FALSE, array $globalVar = [])
	{
		$namespaces = ( $filter ) ? $this->get_filter_namespace() : $this->get_rules_namespace();

		$lower_rule_name = strtolower($rule_name);
		
		foreach( $namespaces as $namespace ) 
		{
			if(
				!empty(static::$addRulesMappings)
				&&
				isset(static::$addRulesMappings[$lower_rule_name])
			)
			{
				$class_prefix = key(static::$addRulesMappings[$lower_rule_name]);
			}
			else if(
				!empty(static::$addFiltersMappings)
				&&
				isset(static::$addFiltersMappings[$lower_rule_name])
			)
			{
				$class_prefix = key(static::$addFiltersMappings[$lower_rule_name]);
			}
			else if(
				!empty(static::$addLogicsMappings)
				&&
				isset(static::$addLogicsMappings[$lower_rule_name])
			)
			{
				$class_prefix = key(static::$addLogicsMappings[$lower_rule_name]);
			}
			else
			{
				if(is_string($rules_path) && !is_object($rules_path))
				{
					$class_name = $rule_name;
				}
				else
				{
					$class_name = get_class($rules_path);
				}

				if(is_string($rules_path) && !is_object($rules_path))
				{
					$class_prefix = ( !empty( $namespace ) ) ? $namespace.$class_name : $class_name;
				}
				else
				{
					$class_prefix = $class_name;
				}
				
				if( !empty( $rules_path ) AND !class_exists( $class_prefix ) )
				{
					if(is_string($rules_path) && !is_object($rules_path))
					{
						$requiredFiles = get_included_files();

						$rules_path = str_replace(array('\\\\', '//'), [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $rules_path);
						
						$search = array_search($rules_path, $requiredFiles);
						
						if( empty( $search ) )
						{
							$rules_req = require $rules_path;	
						}
					}
				}
			}
			
			if( !class_exists( $class_prefix ) ) 
			{
				continue;
			}

			$reflect = new ReflectionClass( $class_prefix );
			$getConstructor = $reflect->getConstructor();

			/*if( EMPTY( $args ) )
			{*/
				if( ( bool ) $getConstructor )
				{
					$defaultParams = $getConstructor->getParameters();
					
					$args = Abstract_common::processDefaultParams( $defaultParams, $args );
				}
			// }

			if( !in_array($rule_name, $this->classDontPutArgs ) )
			{
				if( is_array( $args ) )
				{
					$args = array_merge( $args, $globalVar );
				}
			}

			
			if(is_string($rules_path) && !is_object($rules_path))
			{
				$newObj = (bool) $getConstructor ? $reflect->newInstanceArgs( $args ) : $reflect->newInstanceWithoutConstructor();

				return $newObj;
			}
			else
			{
				return $rules_path;
			}
		}
	}

	public function reflection( $resolver )
	{
		return new ReflectionClass( $resolver );
	}

}
