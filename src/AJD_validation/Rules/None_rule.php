<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_all;
use AJD_validation\Vefja\Vefja;
use AJD_validation\Exceptions\Nested_rule_exception;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Contracts\Abstract_invokable;
use AJD_validation\Contracts\Abstract_anonymous_rule;

class None_rule extends Abstract_all
{
	public function run( $value, $satisfier = null, $field = null, $clean_field = null )
	{
		$check = true;
		$append_error = "";

		if( is_array( $satisfier ) )
		{
			if( ISSET( $satisfier[0] ) )
			{
				$details = $this->processRules( $satisfier[0], $value, $field, $clean_field );
                
				if( !empty( $details['check'] ) && in_array( true, $details['check'] ) )
				{
					$check = false;
				}

				if( !EMPTY( $details['exceptions'] ) )
				{
					$append_error = implode('', $details['errorMessage']);
					$append_error = rtrim( $append_error, '<br/>' );
				}
			}
		}

		return [
			'check' => $check,
			'append_error' => '<br/>'.$append_error
		];
	}

 	protected function processRules( $rules, $value, $field = null, $clean_field = null )
    {
        $retArr = [];
        $checkRule = [];
        $exceptions = [];
        $errorMessage = [];

        if( !empty( $rules ) )
        {
            if( is_array( $rules ) )
            {
                foreach( $rules as $subRule )
                {
                    foreach( $subRule->getRules() as $rule )
                    {
                        if(
                            $rule instanceof Abstract_invokable
                            ||
                            $rule instanceof Abstract_anonymous_rule
                        )
                        {
                            $check = $rule( $value, null, $field );    
                        }
                        else
                        {
                            $check = $rule->run( $value, null, $field );       
                        }

                        if(is_array($check))
                        {
                            if($check['check'])
                            {
                                $checkRule[] = true;
                            }
                        }
                        else
                        {
                            if( $check )
                            {
                                $checkRule[] = true;
                            }
                        }
                        
                        $exception = $this->getExceptionError($value, [], $rule, false, $rule);
                        $exceptions[] = $exception;
                        if( $exception instanceof Nested_rule_exception )
                        {
                            $error = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- '.str_replace([':field'], [$clean_field], $exception->getFullMessage()).'<br/>';
                        }
                        else
                        {
                            $error = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- '.str_replace([':field'], [$clean_field], $exception->getExceptionMessage()).'<br/>';
                        }
                        $errorMessage[] = $error;
                    }
                }
            }
            else
            {
                foreach( $rules->getRules() as $rule )
                {
                    if(
                        $rule instanceof Abstract_invokable
                        ||
                        $rule instanceof Abstract_anonymous_rule
                    )
                    {
                        $check = $rule( $value, null, $field );
                    }
                    else
                    {
                        $check = $rule->run( $value, null, $field );
                    }
                    
                    if(is_array($check))
                    {
                        if($check['check'])
                        {
                            $checkRule[] = true;
                        }
                    }
                    else
                    {
                        if( $check )
                        {
                            $checkRule[] = true;
                        }
                    }
                    
                    $exception = $this->getExceptionError($value, array(), $rule, false, $rule);
                    $exceptions[] = $exception;

                    if( $exception instanceof Nested_rule_exception )
                    {
                        $error = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- '.str_replace([':field'], [$clean_field], $exception->getFullMessage()).'<br/>';
                    }
                    else
                    {
                        $error = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- '.str_replace([':field'], [$clean_field], $exception->getExceptionMessage()).'<br/>';
                    }

                    $errorMessage[] = $error;
                }
            }
        }

        $retArr = [
            'check' => $checkRule,
            'exceptions' => $exceptions,
            'errorMessage' => $errorMessage
        ];

        return $retArr;
    }

    public function validate( $value )
    {
        if(
            $this instanceof Abstract_invokable
            ||
            $this instanceof Abstract_anonymous_rule
        )
        {
            $check = $this( $value, [$this->getRules()] );
        }
        else
        {
            $check = $this->run( $value, [$this->getRules()] );
        }

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
    }

    public function assertErr($value, $override = false, $inverseCheck = null)
    {
        $exceptions = $this->assertRules($value, $override);
        $numRules = count($this->getRules());
        $numExceptions = count($exceptions);

        if($numRules !== $numExceptions) 
        {
            throw $this->getExceptionError($value, [], null, $override, $this)->setRelated($exceptions);
        }
        
        return true;
    }
}