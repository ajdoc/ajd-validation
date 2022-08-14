<?php 

namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Mapping_interface;

interface FiltersMappingInterface extends Mapping_interface
{
	public static function createFilterSignature($filter);

	public static function setFilter($filter);

	public static function unsetFilter($filter);

	public static function getFilter($filter);
}