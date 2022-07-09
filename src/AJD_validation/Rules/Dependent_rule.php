<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_dependent;
use AJD_validation\Contracts\Rule_interface;

class Dependent_rule extends Abstract_dependent
{
	public function __construct($dependentFields, Rule_interface $checkValidator, Rule_interface $validator, array $dependentValue = array(), array $values = array() )
	{
		$this->dependetFields 	= $dependentFields;
		$this->checkValidator 	= $checkValidator;
		$this->validator 		= $validator;
		$this->dependentValue 	= $dependentValue;
		$this->values 			= $values;

		$this->showSubError 	= TRUE;

		if( !EMPTY( $dependentValue ) )
		{
			$this->needsComparing 	= TRUE;
		}

		parent::__construct( $dependentFields, $dependentValue, $values, $checkValidator, $validator );
	}
}