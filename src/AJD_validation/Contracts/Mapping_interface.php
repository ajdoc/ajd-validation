<?php 

namespace AJD_validation\Contracts;

interface Mapping_interface 
{
	public static function register( $value );

	public static function cancel( $value );

	public static function getMappings();

	public static function unregister($value);

	public static function flush();
}