<?php namespace AJD_validation\Helpers;

use AJD_validation\Contracts\{ 
	Abstract_compound, Abstract_sequential, Abstract_common
};

use AJD_validation\Helpers\When;
use AJD_validation\AJD_validation;

class Rule_scenario extends AJD_validation
{
	protected $rule_name;
	protected $logic;
	protected $when;
	protected $currentRuleKey;

	public function __construct( $rule = null, $logic = Abstract_common::LOG_AND, When $when = null, $currentRuleKey = null )
	{
		if( !empty( $rule ) ) 
		{
			$this->rule_name = $rule;	
		}
		
		$this->logic = $logic;

		$this->currentRuleKey = $currentRuleKey;

		if( !empty( $when ) )
		{
			$this->when = $when;

			return $this->when;
		}
		else
		{
			return $this;
		}
	}

	public function getCurrentRuleKey()
	{
		return $this->currentRuleKey;
	}

	public function on( $scenario = null, $ruleOverride = null, $forJs = false )
	{
		$logic = static::$ajd_prop[ 'current_logic' ];
		$curr_field = static::$ajd_prop[ 'current_field' ];

		$rule = $this->rule_name;

		if( !empty( $ruleOverride ) )
		{
			$rule = $ruleOverride;
		}

		if( !$forJs )
		{
			if( !empty( $scenario ) ) 
			{
				if( !empty( $curr_field ) )
				{	
					if(!is_null($this->currentRuleKey))
					{
						static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ $this->logic ][ 'scenarios' ][ $scenario ][][] = $this->currentRuleKey.'|+'.$rule;
					}
					else
					{
						static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ $this->logic ][ 'scenarios' ][ $scenario ][][] = $rule;	
					}
				}
				else
				{
					if(!is_null($this->currentRuleKey))
					{
						static::$ajd_prop[ $logic ][ 'scenarios' ][ $scenario ][][] = $this->currentRuleKey.'|+'.$rule;
					}
					else
					{
						static::$ajd_prop[ $logic ][ 'scenarios' ][ $scenario ][][] = $rule;	
					}
				}
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

	public function publish($event, \Closure $callback = null, $eventType = Abstract_common::EV_LOAD, $ruleOverride = null, $forJs = false)
	{
		$logic = static::$ajd_prop[ 'current_logic' ];
		$curr_field = static::$ajd_prop[ 'current_field' ];
		$rule = $this->rule_name;

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

		if( !empty( $ruleOverride ) )
		{
			$rule = $ruleOverride;
		}

		if( !$forJs )
		{
			if(!empty($curr_field))
			{
				if(!is_null($this->currentRuleKey))
				{
					static::$ajd_prop['events'][$eventType][$curr_field.'-|'.$rule][$this->currentRuleKey][] = $curr_field.'-|'.$event;
				}
				else
				{
					static::$ajd_prop['events'][$eventType][$curr_field.'-|'.$rule][] = $curr_field.'-|'.$event;
				}
			}
			else
			{	
				if(!is_null($this->currentRuleKey))
				{
					static::$ajd_prop['events'][$eventType][$rule][$this->currentRuleKey][] = $event;
				}
				else
				{
					static::$ajd_prop['events'][$eventType][$rule][] = $event;	
				}
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

	public function publishSuccess($event, \Closure $callback = null, $forJs = false, $ruleOverride = null)
	{
		return $this->publish($event, $callback, Abstract_common::EV_SUCCESS, $ruleOverride, $forJs);
	}

	public function publishFail($event, \Closure $callback = null, $forJs = false, $ruleOverride = null)
	{
		return $this->publish($event, $callback, Abstract_common::EV_FAILS, $ruleOverride, $forJs);
	}

	public function sometimes( $sometimes = Abstract_common::SOMETIMES, $ruleOverride = null, $forJs = false )
	{
		$logic = static::$ajd_prop[ 'current_logic' ];
		$curr_field = static::$ajd_prop[ 'current_field' ];
		$rule = $this->rule_name;

		if( !empty( $ruleOverride ) )
		{
			$rule = $ruleOverride;
		}

		if( !$forJs )
		{
			if( !empty( $curr_field ) )
			{ 
				if(!is_null($this->currentRuleKey))
				{
					static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ $this->logic ][ 'sometimes' ][ $rule ][$this->currentRuleKey] = $sometimes;
				}
				else
				{
					static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ $this->logic ][ 'sometimes' ][ $rule ] = $sometimes;	
				}
			}
			else 
			{
				if(!is_null($this->currentRuleKey))
				{
					static::$ajd_prop[ $this->logic ][ 'sometimes' ][ $rule ][$this->currentRuleKey] = $sometimes;
				}
				else
				{
					static::$ajd_prop[ $this->logic ][ 'sometimes' ][ $rule ] = $sometimes;	
				}
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

	public function groups( $groups = null, $ruleOverride = NULL, $forJs = false )
	{
		$logic = static::$ajd_prop[ 'current_logic' ];
		$curr_field = static::$ajd_prop[ 'current_field' ];
		$rule = $this->rule_name;

		if(!is_array($groups))
		{
			$groups = [$groups];
		}

		if( !empty( $ruleOverride ) )
		{
			$rule = $ruleOverride;
		}

		if( !$forJs )
		{
			if( !empty( $curr_field ) )
			{ 
				if(!is_null($this->currentRuleKey))
				{
					static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ $this->logic ][ 'groups' ][ $rule ][$this->currentRuleKey] = $groups;
				}
				else
				{
					static::$ajd_prop[ 'fields' ][ $logic ][ $curr_field ][ $this->logic ][ 'groups' ][ $rule ] = $groups;
				}
			}
			else 
			{	
				if(!is_null($this->currentRuleKey))
				{
					static::$ajd_prop[ $this->logic ][ 'groups' ][ $rule ][$this->currentRuleKey] = $groups;
				}
				else
				{
					static::$ajd_prop[ $this->logic ][ 'groups' ][ $rule ] = $groups;
				}
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

	public function getInstance()
	{
		$ruleKey = $this->getCurrentRuleKey();

		$ruleObj = (!empty($this->when)) ? $this->when : $this;

		$dontRunIn = [Abstract_compound::class, Abstract_sequential::class];

		if(isset(static::$currRuleDetails['details'][$ruleKey]) && !empty(static::$currRuleDetails['details'][$ruleKey]))
		{
			$details = [
				'details' => static::$currRuleDetails['details'][$ruleKey]
			];

			$details['satisfier'] = $details['details'][3]['class_args'];
			$details['value'] = null;
			$details['clean_field'] = null;
			$details['field'] = null;

			$details['dontRunValdidationIn'] = $dontRunIn;
			
			$ruleDetails = $this->{$details['details'][2]}($details);

			$ruleObj = $ruleDetails;
			
			if(isset($ruleDetails['rule_obj']) && !empty($ruleDetails['rule_obj']))
			{
				$ruleObj = $ruleDetails['rule_obj'];
				if(!empty($this->when))
				{
					$ruleObj->setWhenInstance($this->when);
				}

				static::$cacheSceneInstance[$details['details'][1]][$ruleKey] = $ruleObj;
			}
		}		

		static::$currRuleDetails = [];
		
		return $ruleObj;
	}

	public function suspend($ruleOverride = null, $forJs = false)
	{
		$rule = $this->rule_name;

		if( !empty( $ruleOverride ) )
		{
			$rule = $ruleOverride;
		}

		if(!is_null($this->currentRuleKey))
		{
			static::$ajd_prop['fiber_suspend'][$rule][$this->currentRuleKey] = true;
		}
		else
		{
			static::$ajd_prop['fiber_suspend'][$rule] = true;
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
}