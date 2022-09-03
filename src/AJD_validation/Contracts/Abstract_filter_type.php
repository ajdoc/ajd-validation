<?php namespace AJD_validation\Contracts;

use AJD_validation\AJD_validation as v;
use AJD_validation\Contracts\Abstract_rule;

abstract class Abstract_filter_type extends Abstract_rule
{
	public $additionalChars = '';

	abstract protected function validateValue($value);

    abstract protected function getRegexString();

	public function __construct($additionalChars = '')
    {
        if ( !EMPTY( $additionalChars ) && ( !is_string( $additionalChars ) && !is_array($additionalChars) ) ) 
        {
            throw new \Exception('Invalid list of additional characters to be loaded.');
        }

        if( is_string( $additionalChars ) )
        {
            $this->additionalChars .= $additionalChars;
        }
        else
        {
            $this->additionalChars .= implode('', $additionalChars);
        }
    }

    protected function filter($value)
    {
    	$new_value = v::Fwhite_space_option($this->additionalChars)
                        ->cacheFilter( 'value' )
                        ->filterSingleValue( $value, true );
        
        return $new_value;
    }

    protected function str_split_unicode($str, $l = 0) 
    {
        if ($l > 0) 
        {
            $ret = [];
            $len = mb_strlen($str, "UTF-8");

            for ($i = 0; $i < $len; $i += $l) 
            {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }
            
            return $ret;
        }

        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    protected function processAddtionalCharRegex()
    {
        $addCharReg = '';

        if( !EMPTY( $this->additionalChars ) )
        {
            $addChar = $this->str_split_unicode($this->additionalChars);
            $addCharReg = '\\'.implode('\\', $addChar);
        }

        return $addCharReg;
    }

    public function run($value, $satisfier = null, $field = null)
    {
        if ( !is_scalar($value) ) 
        {
            return false;
        }
        
        if( empty( $this->additionalChars ) && !empty( $satisfier ) )
        {
            if( is_array( $satisfier ) )
            {
                if( isset( $satisfier[0] ) )
                {
                     $this->additionalChars = $satisfier[0];
                }
            }
            else
            {
        	   $this->additionalChars = $satisfier;
            }
        }

        $stringInput = (string) $value;

        if ( '' === $stringInput ) 
        {
            return false;
        }

        $cleanInput = $this->filter($stringInput);
        
        return $cleanInput === '' || $this->validateValue($cleanInput);
    }

    public function validate( $value )
    {
        $satisfier = [$this->additionalChars];

        $check  = $this->run( $value, $satisfier );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
    }

     public function getCLientSideFormat( $field, $rule, $jsTypeFormat, $clientMessageOnly = false, $satisfier = null, $error = null, $value = null )
    {
        $regex = $this->getRegexString();

        if( !EMPTY( $regex ) )
        {
            $error = preg_replace('/\"/', '', $error);
            
            if( $jsTypeFormat == Abstract_regex::CLIENT_PARSLEY ) 
            {
                $js[$field][$rule]['rule'] =   <<<JS
                    data-parsley-$rule=$regex
JS;
                $js[$field][$rule]['message'] =   <<<JS
                    data-parsley-$rule-message='$error'
JS;

                $js[$field][$rule]['js'] =   <<<JS

                    window.Parsley.addValidator('$rule', {
                        validate: function(value, requirement, e) {

                            var msg = $(e.element).attr('data-parsley-$rule-message');

                            if( !requirement.test(value) )
                            {
                                return $.Deferred().reject(msg);
                            }
                            else
                            {
                                return true;
                            }
                    },
                    requirementType : 'regexp',
                    messages: {
                        en: '$error'
                    }
                });
JS;
               
            }
        }

        $js = $this->processJsArr( $js, $field, $rule, $clientMessageOnly );

        return $js;
    }
}