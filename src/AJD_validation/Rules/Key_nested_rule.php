<?php namespace AJD_validation\Rules;

use Exception;
use AJD_validation\Contracts\Abstract_related;
use AJD_validation\Contracts\Rule_interface;

class Key_nested_rule extends Abstract_related
{
	public function hasRelation($value)
	{
		try 
		{
			$this->getRelationValue($value);
		} 
		catch(Exception $cex) 
		{
			return FALSE;
		}

		return TRUE;
	}

	private function getRelationPieces()
    {
        return explode('.', rtrim($this->relation, '.'));
    }

    private function getValueFromArray($array, $key)
    {
    	if( !array_key_exists( $key, $array ) ) 
    	{
			$message 	= sprintf('Cannot select the key %s from the given array', $this->relation);

			throw new Exception( $message );
    	}

    	return $array[ $key ];
    }

    private function getValueFromObject($object, $property)
    {
    	if( EMPTY($property) OR !property_exists($object, $property) ) 
    	{
            $message 	= sprintf('Cannot select the property %s from the given object', $this->relation);

            throw new Exception($message);
        }

        return $object->{$property};
    }

    private function getValue($value, $key)
    {
    	if ( is_array($value) OR $value instanceof ArrayAccess) 
    	{
			return $this->getValueFromArray($value, $key);
        }

        if (is_object($value)) 
        {
            return $this->getValueFromObject($value, $key);
        }

        $message = sprintf('Cannot select the property %s from the given data', $this->relation);

        throw new Exception($message);
    }

 	public function getRelationValue($value)
    {
		if( is_scalar( $value ) ) 
		{
			$message = sprintf('Cannot select the %s in the given data', $this->relation);

			throw new Exception($message);
		}

		$keys 		= $this->getRelationPieces();
		$value 		= $value;

		while ( !IS_NULL( $key = array_shift($keys) ) ) 
		{
			$value 	= $this->getValue($value, $key);
		}

		return $value;
    }
}

