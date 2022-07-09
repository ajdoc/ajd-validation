<?php namespace AJD_validation\Contracts;

interface Filter_interface 
{
	public function filter( $value, $satisfier = NULL, $field = NULL );
}