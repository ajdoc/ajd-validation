<?php namespace AJD_validation\Rules;
use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Contracts\Rule_interface;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Rules\All_rule;
use AJD_validation\Exceptions\Nested_rule_exception;
use AJD_validation\Contracts\Abstract_invokable;

class Inverse_rule extends Abstract_rule
{
	public $rule;

	public function __construct( Rule_interface $rule )
	{
		$this->rule 	= $rule;
	}

	public function run( $value, $satisfier = NULL, $field = NULL, $clean_field = NULL )
	{
		$check 			= ( FALSE == $this->rule->validate( $value ) );
		$msg 			= '';

		if( !$check )
		{
			try
			{
				$this->setName($clean_field)->assertErr($value, TRUE, TRUE);
			}
			catch( Nested_rule_exception $e )
			{
				$msg 	= $e->getFullMessage();	
			}
			catch( Abstract_exceptions $e )
			{
				$msg 	= $e->getExceptionMessage();
			}
		}

		return array( 
			'check' 	=> $check,
			'msg'		=> $msg
		);
	}

	public function validate( $value )
	{
	 	$check              = $this->run( $value );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
	}

 	public function assertErr($value, $override = FALSE, $dontCheck = FALSE)
    {
    	if( !$dontCheck )
    	{
    		if($this instanceof Abstract_invokable)
    		{
    			if( $this($value) ) 
				{
					return TRUE;
				}
    		}
    		else
    		{
				if( $this->validate($value) ) 
				{
		            return TRUE;
		        }
		    }
	    }

        $rule 		= $this->rule;

        if( $rule instanceof All_rule ) 
        {
            $rule 	= $this->processAll($rule, $value);
        }

        throw $rule
            ->getExceptionError($value, array(), NULL, $override, $rule, true)
            ->setMode(Abstract_exceptions::ERR_NEGATIVE);
    }

	public function setName($name)
    {
        $this->rule->setName($name);

        return parent::setName($name);
    }

    private function processAll(All_rule $rule, $input)
    {
		$rules 		= $rule->getRules();

		while( ( $current = array_shift($rules) ) ) 
		{
			$rule 	= $current;

			if( !$rule instanceof All_rule ) 
			{
			    continue;
			}

			if( $rule instanceof Abstract_invokable )
			{
				if( !$rule( $value ) ) 
			 	{
	                continue;
	            }
			}
			else
			{
			 	if( !$rule->validate( $value ) ) 
			 	{
	                continue;
	            }
	         }

            $rules 	= $rule->getRules();
		}

		return $rule;
    }
}