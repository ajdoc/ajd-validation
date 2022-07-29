<?php 
namespace AJD_validation\Contracts;

use AJD_validation\AJD_validation;
use AJD_validation\Contracts\Rule_interface;
use AJD_validation\Contracts\Abstract_all;
use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Helpers\Validation_helpers;


/**
* Abstract class for sequentially validated rules.
*
*/
class Abstract_sequential extends Abstract_all
{
	/**
	* @var Rule_interface[]
	*/
	protected $sequentialRules = [];

	/**
	* Initializes the rule adding other rules to the stack.
	*/
	public function __construct(Abstract_all $rules = null)
	{
		$this->sequentialRules = $rules;
	}

	/**
	* Run all the validation rules
	*/
    public function run($value = null, $satisfier = null, $field = null, $clean_field = null)
    {
    	$check 			= false;
		$append_error 	= "";
        $msg            = "";

        $exceptions = $this->assertSequenceRules($value, $clean_field, true);
        $check = true;

        $return = [
            'check' => $check
        ];

        if(!empty($exceptions))
        {
            $check = false;

            $return['check'] = $check;
            
            foreach($exceptions as $key => $exception)
            {
                $msg = $exception->getExceptionMessage();

                if(!empty($msg))
                {
                    $return['msg'] = $msg;
                }

                return $return;
            }
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
    public function getRules()
    {
        return $this->sequentialRules->getRules();
    }
    
}
