<?php 

namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Mapping_interface;

interface LogicsMappingInterface extends Mapping_interface
{
	public static function createLogicSignature($logic);

	public static function setLogic($logic);

	public static function unsetLogic($logic);

	public static function getLogic($logic);
}