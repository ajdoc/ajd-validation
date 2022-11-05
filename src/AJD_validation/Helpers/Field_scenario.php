<?php namespace AJD_validation\Helpers;

use AJD_validation\Contracts\Abstract_common;
use AJD_validation\AJD_validation;

class Field_scenario extends AJD_validation
{
	protected $field_name;

	public function __construct( $field_name = null )
	{
		if( !empty( $field_name ) )
		{
			$this->field_name = $field_name;
		}

		return $this;
	}

	public function on( $scenario = null )
	{
		$logic = static::$ajd_prop[ 'current_logic' ];
		$curr_field = static::$ajd_prop[ 'current_field' ];

		if( !empty( $scenario ) )
		{
			if( !empty( $curr_field ) )
			{
				static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ 'scenarios' ][ $scenario ][] = $this->field_name;
			}
		}

		return $this;
	}

	public function sometimes( $sometimes = Abstract_common::SOMETIMES, $ruleOverride = NULL )
	{
		$logic = static::$ajd_prop[ 'current_logic' ];
		$curr_field = static::$ajd_prop[ 'current_field' ];
		
		$rule = static::$ajd_prop['current_rule'];

		if( !empty( $ruleOverride ) )
		{
			if(is_string($ruleOverride))
			{
				$rule = $ruleOverride;
			}
		}
		
		if( !empty( $curr_field ) )
		{ 
			static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ 'sometimes' ][ $this->field_name ] = $sometimes;

			if(!empty($ruleOverride) && is_array($ruleOverride))
			{
				static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ Abstract_common::LOG_AND ][ 'sometimes_arguments' ][ $this->field_name ] = $ruleOverride;
			}
		}

		return $this;

	}

	public function publish($event, $callback = null, $customEvent = null, $eventType = Abstract_common::EV_LOAD, $ruleOverride = null, $forJs = false)
	{
		$logic = static::$ajd_prop[ 'current_logic' ];
		$curr_field = static::$ajd_prop[ 'current_field' ];

		if(!empty($curr_field))
		{
			if(!empty($callback))
			{
				$this->subscribe($curr_field.'-|'.$event, $callback, $customEvent);
			}

			if( !$forJs )
			{
				static::$ajd_prop['events'][$eventType][$curr_field][] = $curr_field.'-|'.$event;
			}
		}

		if( !empty( $this->when ) )
		{
			return $this->when;
		}
		else
		{
			return $this;
		}
	}

	public function publishSuccess($event, $callback = null, $customEvent = null, $forJs = false, $ruleOverride = null)
	{
		return $this->publish($event, $callback, $customEvent, Abstract_common::EV_SUCCESS, $ruleOverride, $forJs);
	}

	public function publishFail($event, $callback = null, $customEvent = null, $forJs = false, $ruleOverride = null)
	{
		return $this->publish($event, $callback, $customEvent, Abstract_common::EV_FAILS, $ruleOverride, $forJs);
	}
}