<?php namespace AJD_validation\Filters;

use AJD_validation\Contracts\Abstract_filter;
use DateTime;

class Interval_filter extends Abstract_filter
{
	public function filter( $value, $satisfier = null, $field = null )
	{
		if ( !is_string($value) || is_numeric($value) || EMPTY($value) ) 
        {
            return $value;
        }

        if (strlen($value) == 1) 
        {
            if( !is_numeric( $value ) )
            {
                return strlen( $value );
            }
            else
            {
                return $value;
            }
        }

        try 
        {
            $checkDate  = date_create( $value );

            if( !$checkDate )
            {
                // return $value;
            }
            else
            {
                $check_value    = strtolower( preg_replace('/[^a-zA-Z0-9]/', "", $value) );

                $exceptionalFormats = [
                    'c' => 'Y-m-d\TH:i:sP',
                    'r' => 'D, d M Y H:i:s O',
                ];

                if ( !in_array( $check_value, array_keys( $exceptionalFormats ) ) ) 
                {
                    $dateObj    = new DateTime($value);
                    
                    return $dateObj;
                }
                else
                {
                    return strlen( $value );
                }
            }

        } 
        catch (Exception $e) 
        {
            // Exception Handling
        }

        if( !is_numeric( $value ) AND is_string( $value ) AND !(bool)strtotime($value) )
        {
            return strlen( $value );
        }

        return $value;
	}
}