<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_all;
use AJD_validation\Vefja\Vefja;
use AJD_validation\Exceptions\Nested_rule_exception;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Contracts\Abstract_invokable;
use AJD_validation\Contracts\Abstract_anonymous_rule;

class One_or_rule extends Abstract_all
{
	public function run( $value, $satisfier = null, $field = null, $clean_field = null )
	{
		$check = false;
		$append_error = "";

		if( is_array( $satisfier ) )
		{
			if( isset( $satisfier[0] ) )
			{
				$details = $this->processRules( $satisfier[0], $value, $field, $clean_field );

				if( !empty( $details['check'] ) && in_array( true,  $details['check'] ) )
				{
					$check = true;
				}
                else
                {
                    try
                    {
                        $this->setName($clean_field)->assertErr( $value, true );
                    }
                    catch( Abstract_exceptions $e )
                    {
                        $append_error = $e->getFullMessage(function($messages)
                        {
                            $firstMessage = str_replace('-', '', $messages[0]);
                            $realMessage = [];
                            $messages[0] = $firstMessage;

                            foreach( $messages as $key => $message )
                            {
                                if( preg_match('/Data validation failed for/', $message) )
                                {
                                    continue;
                                }

                                if( $key != 0 )
                                {
                                    $message = '<br/>&nbsp;&nbsp;&nbsp;&nbsp;'.$message;
                                }
                                else
                                {
                                    $message = preg_replace('/^[\s]/', '', $message);   
                                    $message = '&nbsp;&nbsp;'.$message;
                                }

                                $realMessage[$key] = $message;
                            }

                            return implode('', $realMessage);

                        });
                    }
                }
			}
		}

		return [
			'check' => $check,
			'msg' => $append_error
		];
	}

 	protected function processRules( $rules, $value, $field = null, $clean_field = null )
    {
        $retArr = [];
        $checkRule = [];
        $exceptions = [];
        $errorMessage = [];

        if( !EMPTY( $rules ) )
        {
            if( is_array( $rules ) )
            {
                foreach( $rules as $subRule )
                {
                    foreach( $subRule->getRules() as $rule )
                    {
                        $args = [];
                        if(
                            $rule instanceof Abstract_invokable
                            ||
                            $rule instanceof Abstract_anonymous_rule
                        )
                        {
                            $args = static::$ruleArguments[\spl_object_id($rule)] ?? [];

                            $check = $rule( $value, $args, $field );
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

                      /*  try
                        {
                            $rule->setName($clean_field)->assertErr($value, TRUE);
                        }
                        catch( Nested_rule_exception $e )
                        {
                            $exception          = $e;
                            $exceptions[]       = $exception;
                            $error              = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- '.$exception->getFullMessage().'<br/>';
                            $errorMessage[]     = $error;
                        }
                        catch( Abstract_exceptions $e )
                        {
                            $exception          = $e;
                            $exceptions[]       = $exception;
                            $error              = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- '.$exception->getExceptionMessage().'<br/>';
                            $errorMessage[]     = $error;
                        }*/
                    }
                }
            }
            else
            {
                foreach( $rules->getRules() as $rule )
                {
                    $args = [];
                    if(
                        $rule instanceof Abstract_invokable
                        ||
                        $rule instanceof Abstract_anonymous_rule
                    )
                    {
                        $args = static::$ruleArguments[\spl_object_id($rule)] ?? [];

                        $check = $rule( $value, $args, $field );
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
                    
                   /* try
                    {
                        $rule->setName($clean_field)->assertErr($value, TRUE);
                    }
                    catch( Nested_rule_exception $e )
                    {
                        $exception          = $e;
                        $exceptions[]       = $exception;
                        $error              = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- '.$exception->getFullMessage().'<br/>';
                        $errorMessage[]     = $error;
                    }
                    catch( Abstract_exceptions $e )
                    {
                        $exception          = $e;
                        $exceptions[]       = $exception;
                        $error              = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- '.$exception->getExceptionMessage().'<br/>';
                        $errorMessage[]     = $error;
                    }*/
                    
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
        $validators = $this->getRules();
        $exceptions = $this->assertRules($value, $override, $inverseCheck);
        $numRules = count($validators);
        $numExceptions = count($exceptions);

        if($numExceptions === $numRules) 
        {
            throw $this->getExceptionError($value, [], null, $override, $this)->setRelated($exceptions);
        }

        return true;
    }
}