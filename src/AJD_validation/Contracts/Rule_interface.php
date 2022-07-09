<?php namespace AJD_validation\Contracts;

interface Rule_interface 
{
	public function run( $value, $satisfier = NULL, $field = NULL );

	public function validate( $value );

	public function assertErr( $value, $override = FALSE );

	public function getName();

	public function setName($name);

	public function getCLientSideFormat( $field, $rule, $jsTypeFormat, $clientMessageOnly = FALSE, $satisfier = NULL, $error = NULL, $value = NULL );
}