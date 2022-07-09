<?php namespace AJD_validation\Helpers;

use AJD_validation\Contracts\Abstract_common;

class Metadata extends \App\Libraries\AJD_validation\AJD_validation
{
	const PROP_PROPERTY 					= 'propertyConstraints';
	const METH_PROPERTY 					= 'methodConstraints';
	const CLASS_PROPERTY 					= 'classConstraints';

	protected static $methodMapping 		= 'setValidatorMetada';
	protected static $propertyConstraints 	= array();
	protected static $methodConstraints 	= array();
	protected static $classConstraints 		= array();
	protected static $classMethodMapping 	= 'validateClass';

	protected static $currentClassName;

	public function __construct()
	{

	}

	public static function setCurrentClassName( $currentClassName )
	{
		static::$currentClassName 		= $currentClassName;
	}

	public static function addMethodMapping( $methodMapping )
	{
		static::$methodMapping 			= $methodMapping;
	}

	protected static function addToConstraints( $property, $propertyName, array $constraints, $static, array $filters = array() )
	{
		$counter 						= 0;

		foreach( $constraints as $cons_key => $constraint )
		{

			if( is_numeric( $cons_key ) )
			{
				$static::$$property[ $propertyName ]['rules'][] 	= $constraint;
			}
			else
			{
				$static::$$property[ $propertyName ]['rules'][] 	= $cons_key;

				if( ISSET( $constraint['satisfier'] ) AND !EMPTY( $constraint['satisfier'] ) )
				{
					$static::$$property[ $propertyName ]['satisfier'][] 		= $constraint['satisfier'];
				}
				else
				{
					$static::$$property[ $propertyName ]['satisfier'][] 		= NULL;
				}

				if( ISSET( $constraint['custom_err'] ) )
				{
					$static::$$property[ $propertyName ]['custom_err'][] 		= $constraint['custom_err'];
				}
				else
				{
					$static::$$property[ $propertyName ]['custom_err'][] 		= NULL;
				}

				if( ISSET( $constraint['client_side'] ) )
				{
					$static::$$property[ $propertyName ]['client_side'][] 		= $constraint['client_side'];
				}
				else
				{
					$static::$$property[ $propertyName ]['client_side'][] 		= NULL;
				}

				if( ISSET( $constraint['logic'] ) )
				{
					$static::$$property[ $propertyName ]['logic'][] 		= $constraint['logic'];
				}
				else
				{
					$static::$$property[ $propertyName ]['logic'][] 		= Abstract_common::LOG_AND;
				}

				if( ISSET( $constraint['sometimes'] ) )
				{
					$static::$$property[ $propertyName ]['sometimes'][$counter] 	= $constraint['sometimes'];
				}

				if( ISSET( $constraint['on'] ) )
				{
					$static::$$property[ $propertyName ]['on'][$counter] 			= $constraint['on'];
				}
			}

			$counter++;
		}

		if( !EMPTY( $filters ) )
		{
			foreach( $filters as $fils_key => $filter )
			{
				if( is_numeric( $fils_key ) )
				{
					$static::$$property[ $propertyName ]['filters'][] 	= $filter;
				}
				else
				{
					$static::$$property[ $propertyName ]['filters'][] 	= $fils_key;
				}

				if( ISSET( $filter['satisfier'] ) )
				{
					$static::$$property[ $propertyName ]['filter_satisfier'][] 		= $filter['filter_satisfier'];
				}
				else
				{
					$static::$$property[ $propertyName ]['filter_satisfier'][] 		= array();
				}

				if( ISSET( $filter['pre_filter'] ) )
				{
					$static::$$property[ $propertyName ]['pre_filter'][] 			= $filter['pre_filter'];
				}
				else
				{
					$static::$$property[ $propertyName ]['pre_filter'][] 			= FALSE;
				}
			}
		}
	}

	public static function addMethodConstraints( $methodName, array $constraints, array $filters = array() )
	{
		$static 			= new static;

		static::addToConstraints( 'methodConstraints', $methodName, $constraints, $static, $filters );

		return $static;
	}

	public static function addPropertyConstraints( $propertyName, array $constraints, array $filters = array() )
	{
		$static 			= new static;

		static::addToConstraints( 'propertyConstraints', $propertyName, $constraints, $static, $filters );

		return $static;
	}

	public static function addClassConstraints( array $constraints, array $filters = array() )
	{
		$static 			= new static;

		$propertyName 		= static::$currentClassName;
		
		static::addToConstraints( 'classConstraints', $propertyName, $constraints, $static, $filters );

		return $static;
	}

	public static function validateMetada( $object, $assert = FALSE, $recursive = FALSE )
	{
		$static 			= new static;

		if( !EMPTY( $object ) )
		{
			$factory 		= static::get_factory_instance();
			$classFactory 	= $factory->get_instance(TRUE);
			$methodFactory 	= $factory->get_instance(FALSE, FALSE, TRUE);

			$reflect 		= $classFactory->reflection( $object );

			$static->setCurrentClassName( $reflect->getName() );

			if( method_exists( $object, static::$methodMapping ) AND !$recursive )
			{
				$metadata 	= call_user_func_array( array( $object, static::$methodMapping ), array( $static ) );
			}

			static::processConstraints( 'propertyConstraints', $object, $reflect, $static, $methodFactory );
			static::processConstraints( 'methodConstraints', $object, $reflect, $static, $methodFactory );
			static::processConstraints( 'classConstraints', $object, $reflect, $static, $methodFactory );

			if( $assert )
			{
				$static->assert();
			}
		}

		return $static;
	}

	protected static function processConstraints( $property, $object, $reflect, $static, $methodFactory = NULL )
	{
		if( !EMPTY( $static::$$property ) )
		{
			foreach( $static::$$property as $metadataName => $details )
			{

				if( !EMPTY( $details['filters'] ) )
				{
					foreach( $details['filters'] as $fil_key => $filter )
					{
						$filt_satis 	= array();
						$pre_filter 	= FALSE;

						if( ISSET( $details['filter_satisfier'][$fil_key] ) )
						{
							$filt_satis 	= $details['filter_satisfier'][$fil_key];
						}

						if( ISSET( $details['pre_filter'][$fil_key] ) )
						{
							$pre_filter = $details['pre_filter'][$fil_key];
						}
						
						static::addFilter( $filter, $filt_satis, $pre_filter );
					}
				}

				if( !EMPTY( $details['rules'] ) )
				{
					foreach( $details['rules'] as $key => $rules )
					{
						$satis 			= NULL;
						$custom_err 	= NULL;
						$client_side 	= NULL;
						$logic 			= Abstract_common::LOG_AND;

						if( ISSET( $details['satisfier'][$key] ) )
						{
							$satis 		= $details['satisfier'][$key];
						}

						if( ISSET( $details['custom_err'][$key] ) )
						{
							$custom_err = $details['custom_err'][$key];
						}

						if( ISSET( $details['client_side'][$key] ) )
						{
							$client_side = $details['client_side'][$key];
						}

						if( ISSET( $details['logic'][$key] ) )
						{
							$logic 		= $details['logic'][$key];
						}

						$sceneIns 		= static::addRule( $rules, $satis, $custom_err, $client_side, $logic );

						if( ISSET( $details['sometimes'][$key] ) )
						{
							$sceneIns->sometimes( $details['sometimes'][$key], $rules );
						}

						if( ISSET( $details['on'][$key] ) )
						{
							$sceneIns->on( $details['on'][$key], $rules );
						}
					}
				}

				$static::$$property 	= array();

				switch( $property )
				{
					case self::PROP_PROPERTY :
						static::processProperty( $object, $metadataName, $reflect, $static, $methodFactory, TRUE );	
					break;

					case self::METH_PROPERTY :
						static::processMethod( $object, $metadataName, $reflect, $static, $methodFactory, TRUE );
					break;

					case self::CLASS_PROPERTY :
						static::processClass( $object, $metadataName, $reflect, $static, $methodFactory, TRUE );
					break;
				}
			}
		}
	}

	protected static function processProperty( $object, $metadataName, $reflect, $static, $methodFactory, $check_arr = TRUE, $return = FALSE )
	{
		if( property_exists( $object, $metadataName ) )
		{
			$propertyObject 	= $reflect->getProperty( $metadataName );

			if( !EMPTY( $propertyObject ) )
			{
				$propertyObject->setAccessible(true);

				$checkObj 		= $static->check( $propertyObject->getName(), $propertyObject->getValue($object), $check_arr );

				if( $return )
				{
					return $checkObj;
				}
			}
		}
	}

	protected static function processMethod( $object, $metadataName, $reflect, $static, $methodFactory, $check_arr = TRUE, $return = FALSE )
	{
		if( method_exists( $object, $metadataName ) )
		{
			if( !EMPTY( $methodFactory ) )
			{
				$reflectMethod 	= $methodFactory->reflection( array( $object, $metadataName ) );

				$methodValue 	= $reflectMethod->invokeArgs( $object, array() );

				$name 			= $reflectMethod->getName();
				$value 			= $methodValue;

				if( is_array( $methodValue ) )
				{
					if( ISSET( $methodValue['value'] ) )
					{
						$value 	= $methodValue['value'];
					}

					if( ISSET( $methodValue['name'] ) )
					{
						$name 	= $methodValue['name'];
					}
				}

				$checkObj 		= $static->check( $name, $value, $check_arr );

				if( $return )
				{
					return $checkObj;
				}
			}
		}
	}

	protected static function processClass( $object, $metadataName, $reflect, $static, $methodFactory, $check_arr = TRUE, $return = FALSE )
	{
		$args 					= array( $reflect, $static );

		$value 					= NULL;
		$name 					= $metadataName;
		
		if( method_exists( $object, static::$classMethodMapping ) )
		{
			$reflectMethod 		= $methodFactory->reflection( array( $object, static::$classMethodMapping ) );

			$classValue 		= $reflectMethod->invokeArgs( $object, $args );
		}		

		if( is_array( $classValue ) )
		{
			if( ISSET( $classValue['value'] ) )
			{
				$value 	= $classValue['value'];
			}

			if( ISSET( $classValue['name'] ) )
			{
				$name 	= $classValue['name'];
			}
		}
		else
		{
			$value 				= $classValue;
		}

		$checkObj 	= $static->check( $name, $value, $check_arr );

		$static->validateMetada( $object, FALSE, TRUE );

		if( $return )
		{
			return $checkObj;
		}
	}

	public static function checkMetadata( $property, $object, $metadataName = NULL, $check_arr = TRUE )
	{
		$factory 		= static::get_factory_instance();
		$classFactory 	= $factory->get_instance(TRUE);
		$methodFactory 	= $factory->get_instance(FALSE, FALSE, TRUE);

		$reflect 		= $classFactory->reflection( $object );
		$static 		= new static;

		switch( $property )
		{
			case self::PROP_PROPERTY :
				return $static->processProperty( $object, $metadataName, $reflect, $static, $methodFactory, $check_arr, TRUE );
			break;

			case self::METH_PROPERTY :
				return $static->processMethod( $object, $metadataName, $reflect, $static, $methodFactory, $check_arr, TRUE );
			break;

			case self::CLASS_PROPERTY :

				$cName 		= $reflect->getName();

				if( !EMPTY( $metadataName ) )
				{
					$cName 	= $metadataName;
				}

				return static::processClass( $object, $cName, $reflect, $static, $methodFactory, $check_arr, TRUE );
			break;
		}
	}
}