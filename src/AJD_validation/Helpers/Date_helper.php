<?php namespace AJD_validation\Helpers;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use AJD_validation\Contracts\Abstract_common;

class Date_helper extends DateTime
{
	const MOCK_DATETIME_FORMAT 		= 'Y-m-d H:i:s.u';
	const DEFAULT_TO_STRING_FORMAT 	= 'Y-m-d H:i:s';

	protected static $testNow;
	protected static $lastErrors;

	protected static $microsecondsFallback = TRUE;

	public function __construct($time = NULL, $tz = NULL)
    {    	
        $isNow = EMPTY($time) OR $time === 'now';

		if( static::hasTestNow() AND ( $isNow OR static::hasRelativeKeywords($time) ) ) 
		{
			$testInstance 	= clone static::getTestNow();

        	if( $tz !== NULL AND $tz !== static::getTestNow()->getTimezone() ) 
        	{
        		$testInstance->setTimezone($tz);
        	}
        	else
        	{
        		$timezone 	= $testInstance->getTimezone();
        	}

        	if( static::hasRelativeKeywords($time) ) 
        	{
        		$testInstance->modify($time);
        	}

        	$time 			= $testInstance->format(static::MOCK_DATETIME_FORMAT);
		}

		$timezone 			= static::safeCreateDateTimeZone($tz);

	 	if ($isNow AND !ISSET($testInstance) AND static::isMicrosecondsFallbackEnabled() AND (
                version_compare(PHP_VERSION, '7.1.0-dev', '<')
                OR
                version_compare(PHP_VERSION, '7.1.3-dev', '>=') AND version_compare(PHP_VERSION, '7.1.4-dev', '<')
            )
        ) 
        {
        	// Get microseconds from microtime() if "now" asked and PHP < 7.1 and PHP 7.1.3 if fallback enabled.
            list($microTime, $timeStamp) = explode(' ', microtime());

            $dateTime 		= new DateTime('now', $timezone);
            $dateTime->setTimestamp($timeStamp); // Use the timestamp returned by microtime as now can happen in the next second

            $time = $dateTime->format(static::DEFAULT_TO_STRING_FORMAT).substr($microTime, 1, 7);
        }

        if(strpos((string) .1, '.') === FALSE) 
        {
            $locale = setlocale(LC_NUMERIC, '0');
            setlocale(LC_NUMERIC, 'C');
        }

        parent::__construct($time, $timezone);

        if( ISSET( $locale ) ) 
        {
            setlocale(LC_NUMERIC, $locale);
        }

        static::setLastErrors( parent::getLastErrors() );
    }

    protected static function safeCreateDateTimeZone($object)
    {
        if($object === NULL) 
        {
            // Don't return null... avoid Bug #52063 in PHP <5.3.6
            return new DateTimeZone(date_default_timezone_get());
        }

        if($object instanceof DateTimeZone) 
        {
            return $object;
        }

        if(is_numeric($object)) 
        {
            $timezoneName 	= timezone_name_from_abbr(NULL, $object * 3600, TRUE);

            if($timezoneName === FALSE) 
            {
                throw new InvalidArgumentException('Unknown or bad timezone ('.$object.')');
            }

            $object 		= $timezoneName;
        }

        $timezone = @timezone_open($object = (string) $object);

        if($timezone !== FALSE) 
        {
            return $timezone;
        }

        // Work-around for a bug fixed in PHP 5.5.10 https://bugs.php.net/bug.php?id=45528
        // See: https://stackoverflow.com/q/14068594/2646927
        // @codeCoverageIgnoreStart
        if(strpos($object, ':') !== FALSE) 
        {
            try 
            {
                return static::createFromFormat('O', $object)->getTimezone();
            } 
            catch (InvalidArgumentException $e) 
            {
                //
            }
        }
        // @codeCoverageIgnoreEnd

        throw new InvalidArgumentException('Unknown or bad timezone ('.$object.')');
    }

    private static function createFromFormatAndTimezone($format, $time, $timezone)
    {
        return $timezone !== NULL
            ? parent::createFromFormat($format, $time, static::safeCreateDateTimeZone($timezone))
            : parent::createFromFormat($format, $time);
    }

    public static function isMicrosecondsFallbackEnabled()
    {
        return static::$microsecondsFallback;
    }

     private static function setLastErrors(array $lastErrors)
    {
        static::$lastErrors = $lastErrors;
    }

    public static function hasRelativeKeywords($time)
    {
        if( strtotime($time) === FALSE ) 
        {
            return FALSE;
        }

        $date1 	= new DateTime('2000-01-01T00:00:00Z');
        $date1->modify($time);
        $date2 	= new DateTime('2001-12-25T00:00:00Z');
        $date2->modify($time);

        return $date1 != $date2;
    }

    public static function hasTestNow()
    {
        return static::getTestNow() !== NULL;
    }

    public static function getTestNow()
    {
        return static::$testNow;
    }
}