<?php namespace AJD_validation\Rules;

use DateTime;
use DateTimeInterface;

use AJD_validation\Contracts\Abstract_rule;

class Date_rule extends Abstract_rule
{
    public $format = null;

    public function __construct( $format = null )
    {
        $this->format = $format;
    }

    public function run($value, $satisfier = null, $field = null)
    {
        $format = $this->format;

        $check = false;

        if( $value instanceof DateTimeInterface
            || $value instanceof DateTime
        ) 
        {
            $check = true;
        }

        if( !is_scalar($value) ) 
        {
            $check = false;
        }

        $valueString = (string) $value;

        if( empty( $satisfier ) || is_null( $satisfier) ) 
        {
            $check = false !== strtotime($valueString);
        }

        $exceptionalFormats = [
            'c' => 'Y-m-d\TH:i:sP',
            'r' => 'D, d M Y H:i:s O',
        ];

        if ( in_array( $this->format, array_keys( $exceptionalFormats ) ) ) 
        {
            $format = $exceptionalFormats[$this->format];
        }
        
        $info = date_parse_from_format($format, $valueString);

        $check = ($info['error_count'] === 0 && $info['warning_count'] === 0);

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
}