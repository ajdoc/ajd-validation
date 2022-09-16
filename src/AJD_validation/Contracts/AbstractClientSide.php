<?php 

namespace AJD_validation\Contracts;

use AJD_validation\Contracts\ClientSideInterface;

class AbstractClientSide implements ClientSideInterface
{
	protected static $ruleObj;
	protected static $jsTypeFormat;
	protected static $clientMessageOnly;

	public static function boot(Rule_interface $ruleObj, string $jsTypeFormat, bool $clientMessageOnly = false)
	{
		static::$ruleObj = $ruleObj;
		static::$jsTypeFormat = $jsTypeFormat;
		static::$clientMessageOnly = $clientMessageOnly;
	}

	public static function getCLientSideFormat(string $field, string $rule, mixed $satisfier = null, string $error = null, mixed $value = null)
	{
		return [];
	}
}