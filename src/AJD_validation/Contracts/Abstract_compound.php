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
	* @var $ruleOptions[]
	*/
	public $ruleOptions = [
		'showSubError' => true,
	];

	/**
	* Initializes the rule adding other rules to the stack.
	*/
	public function __construct(Abstract_all ...$rules)
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

		$collectionExceptions = $this->assertCompoundRules($this->rules, $value, $clean_field, true, true);
        $check = ($this->inverseCheck) ? false : true;

        $return = [
            'check' => $check
        ];

        $field_arr = $this->format_field_name($field);

        if(!empty($collectionExceptions))
        {
            $check = ($this->inverseCheck) ? true : false;

            $return['check'] = $check;
            
            $countCollections = count($this->rules);

            $msg = [];

            foreach($collectionExceptions as $parKey => $exceptions)
            {
            	foreach($exceptions['exception'] as $kEx => $exception)
                {
            		$append_error .= $exception->getFullMessage(Validation_helpers::class.'::formatAppendedError', null, $clean_field).'<br/>';
            	}
            }
        }

    	$return = [
    		'check' => $check
    	];

    	if(
    		!empty($append_error)
    		&& $this->ruleOptions['showSubError']
    	)
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
    
}
