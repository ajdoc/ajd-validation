<?php namespace AJD_validation\Rules;

use ReflectionProperty;
use Exception;
use AJD_validation\Contracts\Abstract_related;
use AJD_validation\Contracts\Rule_interface;

class Attribute_rule extends Abstract_related
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
		$propertyReflection = new ReflectionProperty($value, $this->relation);
		$propertyReflection->setAccessible(true);
        
		return $propertyReflection->getValue($value);
	}

	public function hasRelation($value)
    {
    	$check = ( is_object($value) && property_exists($value, $this->relation) );

        return $check;
    }
}

