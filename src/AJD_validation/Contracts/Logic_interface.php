<?php namespace AJD_validation\Contracts;

interface Logic_interface 
{
	public function logic( $value );

	public function __set($name, $value);

	public function __get($name);

	public function __isset($name);

	public function checkDbInstance($db, $obj = null);

	public function getLogicValue($value = null, array $paramaters = []);
}