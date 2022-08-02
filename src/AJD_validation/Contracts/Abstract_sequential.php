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
	public function __construct(Abstract_all ...$rules)
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

        $collectionExceptions = $this->assertSequenceRules($this->sequentialRules, $value, $clean_field, true);
        $check = true;

        $return = [
            'check' => $check
        ];

        $field_arr = $this->format_field_name($field);
        $errors = static::get_errors_instance();

        if(!empty($collectionExceptions))
        {
            $check = false;

            $return['check'] = $check;
            
            $countCollections = count($this->sequentialRules);
            
            foreach($collectionExceptions as $parKey => $exceptions)
            {
                $msg = [];

                if($countCollections > 1)
                {

                    foreach($exceptions['exception'] as $kEx => $exception)
                    {
                        $rule = $exceptions['rule'][$kEx];

                        $ruleName = $rule::class;
                        $ruleName = explode('\\', $ruleName);
                        $ruleName = end($ruleName);
                        $ruleName = str_replace('_'.static::$rules_suffix, '', $ruleName);
                        $ruleName = strtolower($ruleName);
                        
                        $msg[$parKey][$kEx] = [
                            'errors' => $exception->getExceptionMessage(),
                            'clean_field' => $clean_field
                        ];    
                    }

                    if(!empty($msg))
                    {
                        $msgStr = $errors->toStringErr($msg, true);

                        $msgStr = explode('-', $msgStr);

                        unset($msgStr[0]);

                        $msgStr = implode('-', $msgStr);
                        $msgStr = str_replace(["\r", "\n"], '', $msgStr);
                        
                        $return['msg'] = $msgStr;
                    }
                }
                else
                {
                    $return['msg'] = $exceptions['exception'][0]->getExceptionMessage();
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
        return $this->sequentialRules;
    }
    
}
