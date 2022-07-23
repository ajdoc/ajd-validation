<?php namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Logic_interface;
use AJD_validation\AJD_validation;
use AJD_validation\Helpers\Db_instance;

abstract class Abstract_logic implements Logic_interface
{
	protected $properties = [];

	public function __invoke($value)
    {
    	$this->logic($value);
    }

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

   	public function checkDbInstance($db, $obj = null)
   	{
   		$objCheck = Db_instance::class;

   		if(!empty($obj))
   		{
   			$objCheck = $obj::class;
   		}

   		return $db instanceof $objCheck;
   	}
}