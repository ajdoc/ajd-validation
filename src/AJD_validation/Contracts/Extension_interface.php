<?php namespace AJD_validation\Contracts;

interface Extension_interface 
{
	public function getRules();

	public function getRuleMessages();

	public function runRules( $rule, $value, $satisfer, $field );

	public function getName();

	public function getMiddleWares();

	public function getFilters();

	public function getLogics();

}