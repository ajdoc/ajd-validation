<?php 
namespace AJD_validation\Contracts;

use AJD_validation\AJD_validation;
use AJD_validation\Contracts\Rule_interface;
use AJD_validation\Contracts\Abstract_all;
use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Helpers\Validation_helpers;

/**
* Abstract class for compounded rules or rules composed by other rules.
*
*/
class Abstract_compound extends Abstract_all
{
	/**
	* @var Rule_interface[]
	*/
	protected $rules = [];

	/**
	* Initializes the rule adding other rules to the stack.
	*/
	public function __construct(Abstract_all $rules = null)
	{
		$this->rules = $rules;
	}

	/**
	* Run all the validation rules
	*/
    public function run($value, $satisfier = null, $field = null, $clean_field = null)
    {
    	$check 			= false;
		$append_error 	= "";
    	
    	$details = $this->processRules( $value, $field, $clean_field );
    	
    	if( !EMPTY( $details['check'] ) AND !in_array( false, $details['check'] ) )
		{
			$check = true;
		}
    	else
    	{
			try
			{
				$rules = $this->rules;

				$rules->setName($clean_field)->assertErr( $value, true );
			}
			catch( Abstract_exceptions $e )
			{
				$append_error   = $e->getFullMessage(Validation_helpers::class.'::formatAppendedError', null, $clean_field);

			}
    	}

    	$return = [
    		'check' => $check
    	];

    	if(!empty($append_error))
    	{
    		$return['append_error'] = $append_error;
    	}

    	return $return;
    }

    /*
	* Validate all the rules
    */
    public function validate( $value )
    {
        $satisfier  = null;

        $check  = $this->run( $value, $satisfier );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
    }

	/**
	* Returns all the rules in the stack.
	*
	* @return Rule_interface[]
	*/
    public function getRules(): array
    {
        return $this->rules->getRules();
    }


    /**
     * Process all the rules.
     *
     *
     *
     * @return array[]
     */
    protected function processRules( $value, $field = NULL, $clean_field = NULL )
    {
        $retArr         = array();
        $checkRule      = array();
        $exceptions     = array();
        $errorMessage   = array();

        $rules = $this->getRules();

        if( !EMPTY( $rules ) )
        {
            if( is_array( $rules ) )
            {
                foreach( $rules as $rule )
                {
                    if($rule instanceof Abstract_invokable)
                    {
                        $check  = $rule( $value, NULL, $field );
                    }
                    else
                    {
                        $check  = $rule->run( $value, NULL, $field );
                    }

                    if(is_array($check))
                    {
                        if($check['check'])
                        {
                            $checkRule[]    = true;
                        }
                        else
                        {
                        	$checkRule[] 	= false;
                        }
                    }
                    else
                    {
                        if( $check )
                        {
                            $checkRule[]    = true;
                        }
                        else
                        {
                        	$checkRule[] 	= false;
                        }
                    }
                    
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
    
}
