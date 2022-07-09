<?php namespace AJD_validation\Contracts;

interface Invokable_rule_interface 
{
	public function __invoke( $value, $satisfier = NULL, $field = NULL );
}