<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_all;
use AJD_validation\Vefja\Vefja;
use AJD_validation\Exceptions\Nested_rule_exception;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Contracts\Abstract_invokable;

class None_rule extends Abstract_all
{
	public function run( $value, $satisfier = NULL, $field = NULL, $clean_field = NULL )
	{
		$check 			= TRUE;
		$append_error 	= "";

		if( is_array( $satisfier ) )
		{
			if( ISSET( $satisfier[0] ) )
			{
				$details	= $this->processRules( $satisfier[0], $value, $field, $clean_field );
                
				if( !EMPTY( $details['check'] ) AND in_array( TRUE, $details['check'] ) )
				{
					$check 	= FALSE;
				}

				if( !EMPTY( $details['exceptions'] ) )
				{
					$append_error 	= implode('', $details['errorMessage']);
					$append_error 	= rtrim( $append_error, '<br/>' );
				}

			}
		}

		return array(
			'check'			=> $check,
			'append_error'	=> '<br/>'.$append_error
		);
	}

 	protected function processRules( $rules, $value, $field = NULL, $clean_field = NULL )
    {
        $retArr         = array();
        $checkRule      = array();
        $exceptions     = array();
        $errorMessage   = array();

        if( !EMPTY( $rules ) )
        {
            if( is_array( $rules ) )
            {
                foreach( $rules as $subRule )
                {
                    foreach( $subRule->getRules() as $rule )
                    {
                        if($rule instanceof Abstract_invokable)
                        {
                            $check  = $rule( $value, NULL, $field );    
                        }
                        else
                        {
                            $check  = $rule->run( $value, NULL, $field );       
                        }

                        if( $check )
                        {
                            $checkRule[]    = TRUE;
                        }
                        
                        $exception          = $this->getExceptionError($value, array(), $rule, false, $rule);
                        $exceptions[]       = $exception;
                        if( $exception instanceof Nested_rule_exception )
                        {
                            $error              = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- '.str_replace(array(':field'), array($clean_field), $exception->getFullMessage()).'<br/>';
                        }
                        else
                        {
                            $error              = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- '.str_replace(array(':field'), array($clean_field), $exception->getExceptionMessage()).'<br/>';
                        }
                        $errorMessage[]     = $error;
                    }
                }
            }
            else
            {
                foreach( $rules->getRules() as $rule )
                {
                    if($rule instanceof Abstract_invokable)
                    {
                        $check  = $rule( $value, NULL, $field );
                    }
                    else
                    {
                        $check  = $rule->run( $value, NULL, $field );
                    }
                    
                    if( $check )
                    {
                        $checkRule[]    = TRUE;
                    }
                    
                    $exception          = $this->getExceptionError($value, array(), $rule, false, $rule);
                    $exceptions[]       = $exception;

                    if( $exception instanceof Nested_rule_exception )
                    {
                        $error              = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- '.str_replace(array(':field'), array($clean_field), $exception->getFullMessage()).'<br/>';
                    }
                    else
                    {
                        $error              = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- '.str_replace(array(':field'), array($clean_field), $exception->getExceptionMessage()).'<br/>';
                    }

                    $errorMessage[]     = $error;
                }
            }
        }

        $retArr     = array(
            'check'         => $checkRule,
            'exceptions'    => $exceptions,
            'errorMessage'  => $errorMessage
        );

        return $retArr;
    }

    public function validate( $value )
    {
        if($this instanceof Abstract_invokable)
        {
            $check              = $this( $value, array( $this->getRules() ) );
        }
        else
        {
            $check              = $this->run( $value, array( $this->getRules() ) );
        }

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
    }

    public function assertErr($value, $override = FALSE)
    {
        $exceptions     = $this->assertRules($value, $override);
        $numRules       = count($this->getRules());
        $numExceptions  = count($exceptions);

        if($numRules !== $numExceptions) 
        {
            throw $this->getExceptionError($value, array(), NULL, $override, $this)->setRelated($exceptions);
        }

        return TRUE;
    }
}