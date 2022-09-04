<?php namespace AJD_validation\Rules;

use Exception;
use AJD_validation\Contracts\Abstract_related;
use AJD_validation\Contracts\Rule_interface;

class Key_rule extends Abstract_related
{
	public function __construct($relation, Rule_interface $referenceValidator = null, $mandatory = true)
    {
        if( !is_scalar($relation) || '' === $relation ) 
        {
            throw new Exception('Invalid array key name');
        }

        parent::__construct($relation, $referenceValidator, $mandatory);
    }

	public function getRelationValue($value)
	{
		return $value[$this->relation];
	}

	public function hasRelation($value)
	{
		$check 	= ( is_array($value) && array_key_exists($this->relation, $value) );
		
		return $check;
	}
}

