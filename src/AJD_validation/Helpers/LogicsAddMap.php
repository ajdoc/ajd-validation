<?php 

namespace AJD_validation\Helpers;

use AJD_validation\Contracts\Logic_interface;
use AJD_validation\Helpers\When as w;
use AJD_validation\Contracts\LogicsMappingInterface;

final class LogicsAddMap implements LogicsMappingInterface
{
	private static $map = [];
	private static $status = [];

	public static function createLogicSignature($logic)
	{
		$logic = explode('\\', $logic);
		$logic = end($logic);

		$signature = mb_strtolower($logic);
        $signature = str_replace(['_'.w::$testSuffix], '', $signature);

        $signature = $signature.'_'.w::$testSuffix;

        return $signature;
	}

	public static function register($logic)
    {
    	$signature = static::createLogicSignature($logic);
    	$reflectLogic = new \ReflectionClass($logic);
    	$interfaces = array_keys($reflectLogic->getInterfaces());

    	if(in_array(Logic_interface::class, $interfaces, true))
        {
	    	self::$status[$signature][$logic] = false;
	        self::$map[$signature][$logic] = [];
	    }
    }

    public static function cancel($logic)
    {
    	$signature = static::createLogicSignature($logic);
        self::$status[$signature][$logic] = true;
    }

    public static function setLogic($logic)
    {
    	$signature = static::createLogicSignature($logic);
    	
        if(isset(self::$map[$signature][$logic]))
        {
            self::$map[$signature][$logic] = $logic;
        }
    }

    public static function unsetLogic($logic)
    {
    	$signature = static::createLogicSignature($logic);
        unset(self::$map[$signature][$logic]);
    }

    public static function getLogic($logic)
    {
    	$signature = static::createLogicSignature($logic);

        return self::$map[$signature][$logic] ?? null;
    }

    public static function unregister($logic)
    {
    	$signature = static::createLogicSignature($logic);

        unset(self::$status[$signature][$logic]);
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