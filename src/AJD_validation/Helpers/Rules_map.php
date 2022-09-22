<?php 

namespace AJD_validation\Helpers;

use AJD_validation\Contracts\Rule_interface;
use AJD_validation\Contracts\Exception_interface;

use AJD_validation\Contracts\RulesMappingInterface;

use AJD_validation\AJD_validation as v;

final class Rules_map implements RulesMappingInterface
{
	private static $map = [];
	private static $status = [];

	public static function createRuleSignature($rule)
	{
		$rule = explode('\\', $rule);
		$rule = end($rule);

		$signature = mb_strtolower($rule);
        $signature = str_replace(['_'.v::$rules_suffix, '_'.v::$rules_suffix.'_exception'], '', $signature);
        $signature = str_replace(['_exception'], '', $signature);

        $signature = $signature.'_'.v::$rules_suffix;

        return $signature;
	}

	public static function register($rule)
    {
    	$signature = static::createRuleSignature($rule);
    	$reflectRule = new \ReflectionClass($rule);
    	$interfaces = array_keys($reflectRule->getInterfaces());

    	if(in_array(Rule_interface::class, $interfaces, true))
        {
	    	self::$status[$signature][$rule] = false;
	        self::$map[$signature][$rule] = [];
	    }
    }

    public static function cancel($rule)
    {
    	$signature = static::createRuleSignature($rule);
        self::$status[$signature][$rule] = true;
    }

    public static function setException($rule, $exception)
    {
    	$signature = static::createRuleSignature($rule);
    	$reflectException = new \ReflectionClass($exception);
    	$interfaces = array_keys($reflectException->getInterfaces());

    	if(in_array(Exception_interface::class, $interfaces, true))
        {
        	self::$map[$signature][$rule] = $exception;
        }
    }

    public static function unsetException($rule, $exception)
    {
    	$signature = static::createRuleSignature($rule);
        unset(self::$map[$signature][$rule]);
    }

    public static function getException($rule)
    {
    	$signature = static::createRuleSignature($rule);

        return self::$map[$signature][$rule] ?? null;
    }

    public static function getRule($rule)
    {
    	$signature = static::createRuleSignature($rule);

        return key(self::$map[$signature][$rule]) ?? null;
    }

    public static function unregister($rule)
    {
    	$signature = static::createRuleSignature($rule);

        unset(self::$status[$signature][$rule], self::$map[$signature][$rule]);
    }

    public static function getMappings()
    {
    	return self::$map;
    }

    public static function flush()
    {
        self::$status = [];
        self::$map = [];
    }
}