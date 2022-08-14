<?php 

namespace AJD_validation\Helpers;

use AJD_validation\Contracts\Filter_interface;
use AJD_validation\Helpers\AJD_filter as f;
use AJD_validation\Contracts\FiltersMappingInterface;

final class Filters_map implements FiltersMappingInterface
{
	private static $map   	= [];
	private static $status 	= [];

	public static function createFilterSignature($filter)
	{
		$filter 	= explode('\\', $filter);
		$filter 	= end($filter);

		$signature = mb_strtolower($filter);
        $signature = str_replace(['_'.f::$filter_suffix], '', $signature);

        $signature = $signature.'_'.f::$filter_suffix;

        return $signature;
	}

	public static function register($filter)
    {
    	$signature 	 = static::createFilterSignature($filter);
    	$reflectFilter = new \ReflectionClass($filter);
    	$interfaces  = array_keys($reflectFilter->getInterfaces());

    	if(in_array(Filter_interface::class, $interfaces, true))
        {
	    	self::$status[$signature][$filter] = false;
	        self::$map[$signature][$filter] = [];
	    }
    }

    public static function cancel($filter)
    {
    	$signature 	 = static::createFilterSignature($filter);
        self::$status[$signature][$filter] = true;
    }

    public static function setFilter($filter)
    {
    	$signature 	 = static::createFilterSignature($filter);
    	
        if(isset(self::$map[$signature][$filter]))
        {
            self::$map[$signature][$filter] = $filter;
        }
    }

    public static function unsetFilter($filter)
    {
    	$signature 	 = static::createFilterSignature($filter);
        unset(self::$map[$signature][$filter]);
    }

    public static function getFilter($filter)
    {
    	$signature 	 = static::createFilterSignature($filter);

        return self::$map[$signature][$filter] ?? null;
    }

    public static function unregister($filter)
    {
    	$signature 	 = static::createFilterSignature($filter);

        unset(self::$status[$signature][$filter]);
    }

    public static function getMappings()
    {
    	return self::$map;
    }
}