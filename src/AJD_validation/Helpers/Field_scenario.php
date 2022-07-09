<?php namespace AJD_validation\Helpers;

use AJD_validation\Contracts\Abstract_common;

class Field_scenario extends \AJD_validation\AJD_validation
{
	protected $field_name;

	public function __construct( $field_name = NULL )
	{
		if( !EMPTY( $field_name ) )
		{
			$this->field_name 	= $field_name;
		}

		return $this;
	}

	public function on( $scenario = NULL )
	{
		$logic 												= static::$ajd_prop[ 'current_logic' ];
		$curr_field 										= static::$ajd_prop[ 'current_field' ];

		if( !EMPTY( $scenario ) )
		{
			if( !EMPTY( $curr_field ) )
			{
				if( !EMPTY( static::$constraintStorageName ) )
				{
					static::$ajd_prop[static::$constraintStorageName][ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ 'scenarios' ][ $scenario ][] 	= $this->field_name;
				}
				else
				{
					static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ 'scenarios' ][ $scenario ][] 	= $this->field_name;
				}
			}
		}

		return $this;
	}

	public function sometimes( $sometimes = Abstract_common::SOMETIMES, $ruleOverride = NULL )
	{
		$logic 												= static::$ajd_prop[ 'current_logic' ];
		$curr_field 										= static::$ajd_prop[ 'current_field' ];

		$rule 												= $this->rule_name;

		if( !EMPTY( $ruleOverride ) )
		{
			$rule 											= $ruleOverride;
		}
		
		if( !EMPTY( $curr_field ) )
		{ 
			if( !EMPTY( static::$constraintStorageName ) )
			{
				static::$ajd_prop[static::$constraintStorageName][ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ 'sometimes' ][ $this->field_name ] 	= $sometimes;
			}
			else
			{
				static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ 'sometimes' ][ $this->field_name ] 	= $sometimes;
			}
		}

		return $this;

	}
}