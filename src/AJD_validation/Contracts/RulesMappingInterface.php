<?php 

namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Mapping_interface;

interface RulesMappingInterface extends Mapping_interface
{
	public static function createRuleSignature($rule);

	public static function setException($rule, $exception);

	public static function unsetException($rule, $exception);

	public static function getException($rule);

	public static function getRule($rule);
}