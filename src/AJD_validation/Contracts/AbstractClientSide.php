<?php 

namespace AJD_validation\Contracts;

use AJD_validation\Contracts\ClientSideInterface;

class AbstractClientSide implements ClientSideInterface
{
	protected static $ruleObj;
	protected static $jsTypeFormat;
	protected static $clientMessageOnly;

	protected static $relatedEmailRules = [
		'email', 'base_email', 'rfc_email', 'spoof_email', 'no_rfc_email', 'dns_email'
	];

	protected static $relatedRequiredRules = [
		'required', 'required_allowed_zero'
	];

	protected static $relatedInRule = [
		'in'
	];

	protected static $relatedDateRule = [
		'date'
	];

	protected static $relatedMultipleOf = [
		'multiple'
	];

	protected static $relatedRegex = [
		'regex', 'mac_address', 'consonant', 'mobileno', 'phone', 'vowel', // regex rules
	];

	protected static $relatedCtype = [
		'alpha', 'alnum', 'digit', // ctype rules
	];

	protected static $relatedLength = [
		'maxlength', 'minlength', 'min', 'max'
	];

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