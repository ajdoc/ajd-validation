<?php namespace AJD_validation\Helpers;

use AJD_validation\Contracts\Abstract_common;
use AJD_validation\Helpers\When;
use AJD_validation\AJD_validation;

class Rule_scenario extends AJD_validation
{
	protected $rule_name;
	protected $logic;
	protected $when;

	public function __construct( $rule = NULL, $logic = Abstract_common::LOG_AND, When $when = NULL )
	{
		if( !EMPTY( $rule ) ) 
		{
			$this->rule_name 							= $rule;	
		}
		
		$this->logic 									= $logic;

		if( !EMPTY( $when ) )
		{
			$this->when 								= $when;

			return $this->when;
		}
		else
		{
			return $this;
		}
	}

	public function on( $scenario = NULL, $ruleOverride = NULL, $forJs = FALSE )
	{
		$logic 												= static::$ajd_prop[ 'current_logic' ];
		$curr_field 										= static::$ajd_prop[ 'current_field' ];

		$rule 												= $this->rule_name;

		if( !EMPTY( $ruleOverride ) )
		{
			$rule 											= $ruleOverride;
		}

		if( !$forJs )
		{
			if( !EMPTY( $scenario ) ) 
			{
				if( !EMPTY( $curr_field ) )
				{	
					if( !EMPTY( static::$constraintStorageName ) )
					{
						static::$ajd_prop[static::$constraintStorageName][ 'fields' ][ $logic ][ $curr_field ][ $this->logic ][ 'scenarios' ][ $scenario ][][] 	= $rule;
					}
					else
					{
						static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ $this->logic ][ 'scenarios' ][ $scenario ][][] 	= $rule;
					}
				}
				else
				{
					if( !EMPTY( static::$constraintStorageName ) )
					{
						static::$ajd_prop[static::$constraintStorageName][ $logic ][ 'scenarios' ][ $scenario ][][] 											= $rule;
					}
					else
					{
						static::$ajd_prop[ $logic ][ 'scenarios' ][ $scenario ][][] 											= $rule;
					}
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

	public function publish($event, \Closure $callback = null, $eventType = Abstract_common::EV_LOAD, $ruleOverride = NULL, $forJs = FALSE)
	{
		$logic 												= static::$ajd_prop[ 'current_logic' ];
		$curr_field 										= static::$ajd_prop[ 'current_field' ];

		$rule 												= $this->rule_name;

		if(!empty($callback))
		{
			if(!empty($curr_field))
			{
				$this->subscribe($curr_field.'-|'.$event, $callback);
			}
			else
			{
				$this->subscribe($event, $callback);
			}
		}

		if( !EMPTY( $ruleOverride ) )
		{
			$rule 											= $ruleOverride;
		}

		if( !$forJs )
		{
			if( !EMPTY( static::$constraintStorageName ) )
			{
				if(!empty($curr_field))
				{
					static::$ajd_prop[static::$constraintStorageName]['events'][$eventType][$curr_field.'-|'.$rule][] 	= $curr_field.'-|'.$event;
				}
				else
				{
					static::$ajd_prop[static::$constraintStorageName]['events'][$eventType][$rule][] 	= $event;	
				}
				
			}
			else
			{
				if(!empty($curr_field))
				{
					static::$ajd_prop['events'][$eventType][$curr_field.'-|'.$rule][] 	= 	$curr_field.'-|'.$event;
				}
				else
				{	
					static::$ajd_prop['events'][$eventType][$rule][] 	= 	$event;
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

	public function sometimes( $sometimes = Abstract_common::SOMETIMES, $ruleOverride = NULL, $forJs = FALSE )
	{
		$logic 												= static::$ajd_prop[ 'current_logic' ];
		$curr_field 										= static::$ajd_prop[ 'current_field' ];

		$rule 												= $this->rule_name;

		if( !EMPTY( $ruleOverride ) )
		{
			$rule 											= $ruleOverride;
		}

		if( !$forJs )
		{

			if( !EMPTY( $curr_field ) )
			{ 
				if( !EMPTY( static::$constraintStorageName ) )
				{
					static::$ajd_prop[static::$constraintStorageName][ 'fields' ][ $logic ][ $curr_field ][ $this->logic ][ 'sometimes' ][ $rule ] 	= $sometimes;
				}
				else
				{
					static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ $this->logic ][ 'sometimes' ][ $rule ] 		= $sometimes;
				}
			}
			else 
			{
				if( !EMPTY( static::$constraintStorageName ) )
				{
					static::$ajd_prop[static::$constraintStorageName][ $this->logic ][ 'sometimes' ][ $rule ] 										= $sometimes;
				}
				else
				{
					static::$ajd_prop[ $this->logic ][ 'sometimes' ][ $rule ] 										= $sometimes;
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

	public function suspend($ruleOverride = NULL, $forJs = FALSE)
	{
		$rule 			= $this->rule_name;

		if( !EMPTY( $ruleOverride ) )
		{
			$rule 		= $ruleOverride;
		}

		static::$ajd_prop['fiber_suspend'][$rule] = true;

		if( !EMPTY( $this->when ) )
		{
			return $this->when;
		}
		else
		{
			return $this;
		}
	}
}