<?php namespace AJD_validation\Rules;
use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Contracts\Rule_interface;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Rules\All_rule;
use AJD_validation\Exceptions\Nested_rule_exception;
use AJD_validation\Contracts\Abstract_invokable;
use AJD_validation\Contracts\Abstract_anonymous_rule;

class Inverse_rule extends Abstract_rule
{
	public $rule;

	public function __construct( Rule_interface $rule )
	{
		$this->rule = $rule;
	}

	public function run( $value, $satisfier = null, $field = null, $clean_field = null )
	{
		$rules = $this->rule;

		$name = !empty($clean_field) ? $clean_field : $this->name;

		if($rules->getRules())
		{
			foreach($rules->getRules() as $rule)
			{
				if(property_exists($rule, 'inverseCheck'))
				{
					$rule->inverseCheck = true;
				}
			}
		}

		$check = ( $rules->validate( $value ) === false );

		$msg = '';

		if( !$check )
		{
			try
			{
				$this->setName($name)->assertErr($value, true, true);
			}
			catch( Nested_rule_exception $e )
			{
				$msg = $e->getFullMessage();	
			}
			catch( Abstract_exceptions $e )
			{
				$msg = $e->getExceptionMessage();
			}
		}

		return [
			'check' => $check,
			'msg' => $msg
		];
	}

	public function validate( $value )
	{
	 	$check = $this->run( $value );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
	}

 	public function assertErr($value, $override = false, $dontCheck = false)
    {
    	if( !$dontCheck )
    	{
    		$args = [];

    		if(
    			$this instanceof Abstract_invokable
    			||
    			$this instanceof Abstract_anonymous_rule
    		)
    		{
    			$args = static::$ruleArguments[\spl_object_id($this)] ?? [];

    			if( $this($value, $args, $this->name) ) 
				{
					return true;
				}
    		}
    		else
    		{
				if( $this->validate($value) ) 
				{
		            return true;
		        }
		    }
	    }

        $rule = $this->rule;

        if( $rule instanceof All_rule ) 
        {
            $rule = $this->processAll($rule, $value);
        }
        
        throw $rule
            ->getExceptionError($value, array(), null, $override, $rule, true)
            ->setMode(Abstract_exceptions::ERR_NEGATIVE);
    }

	public function setName($name)
    {
        $this->rule->setName($name);

        return parent::setName($name);
    }

    private function processAll(All_rule $rule, $input)
    {
		$rules = $rule->getRules();

		while( ( $current = array_shift($rules) ) ) 
		{
			$rule = $current;
			$args = [];

			if( !$rule instanceof All_rule ) 
			{
			    continue;
			}

			if( 
				$rule instanceof Abstract_invokable 
				||
    			$rule instanceof Abstract_anonymous_rule
			)
			{
				$args = static::$ruleArguments[\spl_object_id($rule)] ?? [];
				
				if( !$rule( $input, $args, $this->name ) ) 
			 	{
	                continue;
	            }
			}
			else
			{
			 	if( !$rule->validate( $input ) ) 
			 	{
	                continue;
	            }
	        }

            $rules = $rule->getRules();
		}

		return $rule;
    }
}