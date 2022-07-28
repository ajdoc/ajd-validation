<?php 
namespace CustomFilters;

use AJD_validation\Contracts\Abstract_filter;

class Custom_filter extends Abstract_filter
{
	public function filter( $value, $satisfier = NULL, $field = NULL )
	{
        return $value.'_custom_filter';
	}
}