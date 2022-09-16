<?php 

namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Rule_interface;

interface ClientSideInterface
{
	public static function boot(Rule_interface $ruleObj, string $jsTypeFormat, bool $clientMessageOnly = false);

	public static function getCLientSideFormat(string $field, string $rule, mixed $satisfier = null, string $error = null, mixed $value = null);
}