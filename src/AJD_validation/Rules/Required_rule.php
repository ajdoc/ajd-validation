<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;

class Required_rule extends Abstract_rule
{
	public function run( $value, $satisfier = NULL, $field = NULL )
	{   
		$check 		= FALSE;
        
		if( is_numeric( $value ) ) 
		{
            $check 	= $value != 0;
        }

        if( is_string( $value ) ) 
        {
            $value = $this->Ftrim()
            			->cacheFilter('value')
            			->filterSingleValue( $value, TRUE );
        }

        if ($value instanceof stdClass) 
        {
             $value = $this->Fstd_to_array()
            			->cacheFilter('value')
            			->filterSingleValue( $value, TRUE );
        }

        $check 	= ( !EMPTY( $value ) );

		return $check;
	}

    public function validate( $value )
    {
        $check      = $this->run( $value );

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
            $js[$field][$rule]['rule'] =   <<<JS
                data-parsley-required="true"
JS;

            $js[$field][$rule]['message'] = <<<JS
                data-parsley-required-message="$error"
JS;
        }

        $js                 = $this->processJsArr( $js, $field, $rule, $clientMessageOnly );

        return $js;
    }
}

