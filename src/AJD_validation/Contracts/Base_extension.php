<?php namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Extension_interface;

abstract class Base_extension implements Extension_interface
{
	protected $properties = [];

	public function __set($name, $value)
    {
    	$this->properties[$name] = $value;
    }

    public function __get($name)
    {
    	if (array_key_exists($name, $this->properties)) 
    	{
            return $this->properties[$name];
        }

        return null;
    }

    public function __isset($name)
   	{
   		return isset($this->properties[$name]);
   	}

	public function getRules()
	{
		return array();
	}

	public function getRuleMessages()
	{
		return array();
	}

	public function runRules( $rule, $value, $satisfier, $field )
	{
		if( method_exists( $this , $rule ) )
		{
			return $this->{ $rule }( $value, $satisfier, $field );
		}
		else 
		{	
			return call_user_func_array( $rule , array( $value, $satisfier, $field ) );
		}
	}

	public function getMiddleWares()
	{
		return array();
	}

	public function getFilters()
	{
		return array();
	}

	public function getLogics()
	{
		return array();
	}

	public function getAnonClass()
	{
		return [];
	}
}