<?php namespace AJD_validation\Contracts;

use InvalidArgumentException;
use Exception;
use DateTime;
use DateTimeInterface;
use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Contracts\Rule_interface;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Helpers\Date_helper;

abstract class Abstract_date extends Abstract_rule
{
	public $dateFormat;
	public $compareDate;
	public $inclusive;

	public $operator;

	public function __construct( $compareDate, $dateFormat = null, $operator = null )  
	{
        $this->compareDate = $compareDate;
        $this->dateFormat = $dateFormat;
        $this->operator = $operator;
	}

	public function run( $value, $satisfier = null, $field = null )
	{
		$check = $this->compareDates( $value, $this->operator );

		return $check;
	}

	public function validate( $value )
	{
		$check = $this->run( $value );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
	}

	protected function compareDates( $value, $operator )
	{
		if( empty( $value ) || empty( $this->compareDate ) )
		{
			return false;
		}
		
		if( !is_string( $value ) && !is_numeric( $value ) && !$value instanceof DateTimeInterface )
		{
			return false;
		}

		if( $this->dateFormat )
		{
			return $this->checkDateTimeOrder($this->dateFormat, $value, $this->compareDate, $operator);
		}

		if( !$date = $this->getDateTimestamp($this->compareDate) ) 
		{
            $date = $this->getDateTimestamp( $this->compareDate );
        }

        return $this->compare( $this->getDateTimestamp($value), $date, $operator );
	}

	protected function getDateTimestamp($value)
    {
        if( $value instanceof DateTimeInterface ) 
        {
            return $value->getTimestamp();
        }

        if( $this->isRelativeDateTime($value) ) 
        {
            $date = $this->getDateTime($value);

            if( !is_null( $date ) ) 
            {
                return $date->getTimestamp();
            }
        }

        return strtotime($value);
    }

	protected function checkDateTimeOrder($format, $first, $second, $operator)
    {
    	$firstDate = $this->getDateTimeWithOptionalFormat($format, $first);

    	if( !$secondDate = $this->getDateTimeWithOptionalFormat($format, $second) ) 
    	{
            $secondDate = $this->getDateTimeWithOptionalFormat($format, $second);
        }

        return ($firstDate && $secondDate) && ($this->compare($firstDate, $secondDate, $operator));
    }

 	protected function getDateTimeWithOptionalFormat($format, $value)
    {
        if($date = DateTime::createFromFormat('!'.$format, $value)) 
        {
            return $date;
        }

        return $this->getDateTime($value);
    }

    protected function getDateTime($value)
    {
        try 
        {
            if($this->isRelativeDateTime($value)) 
            {
                return new Date_helper($value);
            }

            return new DateTime($value);
        } 
        catch(Exception $e) 
        {
            //
        }
    }

    protected function isRelativeDateTime($value)
    {
        return Date_helper::hasTestNow() && is_string($value) && (
            $value === 'now' || Date_helper::hasRelativeKeywords($value)
        );
    }

 	protected function compare($first, $second, $operator)
    {
        switch ($operator) 
        {
            case '<':
                return $first < $second;
            case '>':
                return $first > $second;
            case '<=':
                return $first <= $second;
            case '>=':
                return $first >= $second;
            case '=':
                return $first == $second;
            default:
                throw new InvalidArgumentException;
        }
    }
}