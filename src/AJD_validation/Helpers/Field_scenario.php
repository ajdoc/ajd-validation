<?php namespace AJD_validation\Helpers;

use AJD_validation\Contracts\Abstract_common;
use AJD_validation\AJD_validation;

class Field_scenario extends AJD_validation
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

	public function publish($event, \Closure $callback = null, $eventType = Abstract_common::EV_LOAD, $ruleOverride = NULL, $forJs = FALSE)
	{
		$logic 												= static::$ajd_prop[ 'current_logic' ];
		$curr_field 										= static::$ajd_prop[ 'current_field' ];

		if(!empty($curr_field))
		{

			if(!empty($callback))
			{
				$this->subscribe($curr_field.'-|'.$event, $callback);
			}

			if( !$forJs )
			{
				if( !EMPTY( static::$constraintStorageName ) )
				{
					static::$ajd_prop[static::$constraintStorageName]['events'][$eventType][$curr_field][] 	= $curr_field.'-|'.$event;
				}
				else
				{
					static::$ajd_prop['events'][$eventType][$curr_field][] 	= $curr_field.'-|'.$event;
				}
			}
		}

		if( !EMPTY( $this->when ) )
		{
			return $this->when;
		}
		else
		{
			return $this;
		}
	}

	public function publishSuccess($event, \Closure $callback = null, $forJs = FALSE, $ruleOverride = NULL)
	{
		return $this->publish($event, $callback, Abstract_common::EV_SUCCESS, $ruleOverride, $forJs);
	}

	public function publishFail($event, \Closure $callback = null, $forJs = FALSE, $ruleOverride = NULL)
	{
		return $this->publish($event, $callback, Abstract_common::EV_FAILS, $ruleOverride, $forJs);
	}
}