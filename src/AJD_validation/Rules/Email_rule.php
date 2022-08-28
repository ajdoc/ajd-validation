<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_compound;

class Email_rule extends Abstract_compound
{
	protected $emailValidator;
	protected $baseEmailValidator;

	protected $emailValidators = [];

	public $ruleOptions = [
		'showSubError' => false,
		'useDns' 	=> false
	];

	public function __construct($ruleOptions = null)
    {
    	if(!empty($ruleOptions))
    	{
    		$this->ruleOptions = array_merge($this->ruleOptions, $ruleOptions);
    	}

    	$this->baseEmailValidator = $this->getValidator()
    									->base_email();

    	$this->emailValidator = $this->baseEmailValidator;

    	if(class_exists('Egulias\\EmailValidator\\EmailValidator'))
    	{
    		$this->emailValidator
    			->rfc_email();

    		if (extension_loaded('intl'))
			{
				$this->emailValidator
    				->spoof_email();
    		}
    	}

    	$this->emailValidators[] = $this->emailValidator;

    	if($this->ruleOptions['useDns'])
    	{
    		if(class_exists('Egulias\\EmailValidator\\EmailValidator'))
    		{
    			$this->emailValidators[] = $this->getValidator()
    										->dns_email();
    		}
    	}

    	parent::__construct(...$this->emailValidators);
    }


    public function getCLientSideFormat( $field, $rule, $jsTypeFormat, $clientMessageOnly = false, $satisfier = null, $error = null, $value = null )
	{
		$rules = $this->baseEmailValidator->getRules();

		foreach($rules as $ruleV)
		{
			return $ruleV->getCLientSideFormat($field, $rule, $jsTypeFormat, $clientMessageOnly, $satisfier, $error, $value);
		}
		
	}
}