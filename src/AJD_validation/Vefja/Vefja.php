<?php namespace AJD_validation\Vefja;

use AJD_validation\Factory\Factory_strategy;

class Vefja
{	
	protected $definitions  	= array();

	protected static $singleton = array();
	protected static $vefja_ins;

	public function add( $alias, array $args = array() )
	{
		$this->definitions[ $alias ] 	= $this->getDefinition( $alias, $args );
	}

	public function get( $alias )
	{
		return $this->definitions[ $alias ];
	}

	public function getDefinition( $definition, array $args = array() )
	{
		$factory 				= new Factory_strategy;

		if( is_array( $definition ) )
		{
			return $factory->make( Factory_strategy::F_METHOD, $definition );
		}
		else
		{

			$reflection 		= $factory->make( Factory_strategy::F_CLASS, $definition );
			
			return  $reflection->getConstructor() ? $reflection->newInstanceArgs( $args ) : $reflection->newInstanceWithoutConstructor();
		}
	}

	protected static function get_instance()
	{
		if( IS_NULL( static::$vefja_ins ) ) 
		{
			static::$vefja_ins 	= new static;
		}

		return static::$vefja_ins;
	}

	public static function singleton( $alias, array $args = array() )
	{
		$vefja 		= static::get_instance();

		$obj_ins 	= NULL;
		
		if( !ISSET( static::$singleton[ $alias ] ) )
		{
			if( class_exists( $alias ) )
			{
				$obj_ins = $vefja->getDefinition( $alias, $args );

				static::$singleton[ $alias ] 	= $obj_ins;
			}
		}
		else
		{
			$obj_ins 	= static::$singleton[ $alias ];
		}

		return $obj_ins;
	}

	public static function instance( $alias, array $args = array() )
	{
		$vefja 		= static::get_instance();

		$obj_ins 	= NULL;

		if( class_exists( $alias ) )
		{
			$obj_ins = $vefja->getDefinition( $alias, $args );

			static::$singleton[ $alias ] 	= $obj_ins;
		}
		
		return $obj_ins;
	}
}