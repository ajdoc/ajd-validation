<?php namespace AJD_validation\Filters;
use AJD_validation\Contracts\Abstract_filter;

class Date_filter extends Abstract_filter
{
	protected $dateFormat;

	public function __construct( $dateFormat = 'Y-m-d' )
	{
		$this->dateFormat = $dateFormat;
	}

	public function filter( $value, $satisfier = NULL, $field = NULL )
	{
		$filtValue = date_format( date_create( $value ), $this->dateFormat );

		return $filtValue;
	}
}