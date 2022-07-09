<?php namespace AJD_validation\Rules;

use DateTime;
use DateTimeInterface;

use AJD_validation\Contracts\Abstract_rule;

class Date_rule extends Abstract_rule
{
    public $format  = NULL;

    public function __construct( $format = NULL )
    {
        $this->format   = $format;
    }

    public function run($value, $satisfier = NULL, $field = NULL)
    {
        $format     = $this->format;

        $check      = FALSE;

        if( $value instanceof DateTimeInterface
            OR $value instanceof DateTime
        ) 
        {
            $check  = TRUE;
        }

        if( !is_scalar($value) ) 
        {
            $check  = FALSE;
        }

        $valueString = (string) $value;

        if( EMPTY( $satisfier ) OR IS_NULL( $satisfier) ) 
        {
            $check  = FALSE !== strtotime($valueString);
        }

        $exceptionalFormats = [
            'c' => 'Y-m-d\TH:i:sP',
            'r' => 'D, d M Y H:i:s O',
        ];

        if ( in_array( $this->format, array_keys( $exceptionalFormats ) ) ) 
        {
            $format = $exceptionalFormats[$this->format];
        }
        
        $info   = date_parse_from_format($format, $valueString);

        $check  = ($info['error_count'] === 0 && $info['warning_count'] === 0);

        return $check;
    }

    public function validate( $value )
    {
        $check          = $this->run( $value );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
    }

    public function getCLientSideFormat( $field, $rule, $jsTypeFormat, $clientMessageOnly = FALSE, $satisfier = NULL, $error = NULL, $value = NULL )
    {
        if( $jsTypeFormat == Abstract_rule::CLIENT_PARSLEY ) 
        {
           /* $haystack   = implode('|+', $this->haystack);
            $identical  = ( !EMPTY( $this->identical ) ) ? $this->identical : false;*/
            
            $js[$field][$rule]['rule'] =   <<<JS
                data-parsley-$rule='true'
                
JS;
            $js[$field][$rule]['message'] =   <<<JS
                    data-parsley-$rule-message='$error'
JS;
            $js[$field][$rule]['js'] =   <<<JS

                window.Parsley.addValidator('$rule', {
                    validate: function(value, requirement, obj) {

                        var msg = $(obj.element).attr('data-parsley-$rule-message');
                        var timestamp = Date.parse(value);

                        if( isNaN(timestamp) )
                        {
                            return $.Deferred().reject(msg);
                        }
                        else
                        {
                            return true;
                        }

                        return isNaN(timestamp) ? false : true;
                },
                messages: {
                    en: '$error'
                }
            });
JS;
        }

        $js                 = $this->processJsArr( $js, $field, $rule, $clientMessageOnly );

        return $js;
    }
}