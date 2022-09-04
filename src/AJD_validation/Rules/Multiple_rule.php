<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;

class Multiple_rule extends Abstract_rule
{
	public $multipleof;

	public function __construct($multipleof)
	{
		if( !is_numeric( $multipleof ) )
		{
			throw new \Exception('Invalid Multiplier.');
		}

		$this->multipleof = $multipleof;
	}

	public function run( $value, $satisfier = null, $field = null )
	{
		$check = false;

		if( $this->multipleof == 0 )
		{
			$check = ( $value == 0 );
		}
		else
		{
			if( is_numeric( $value ) )
			{
				$check = ( $value % $this->multipleof == 0 );
			}
		}

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

    public function getCLientSideFormat( $field, $rule, $jsTypeFormat, $clientMessageOnly = false, $satisfier = null, $error = null, $value = null )
    {
    	if( $jsTypeFormat == Abstract_rule::CLIENT_PARSLEY ) 
        {
        	 $js[$field][$rule]['rule'] =   <<<JS
                data-parsley-multipleof="$this->multipleof"
JS;
			 $js[$field][$rule]['js'] =   <<<JS
				window.Parsley.addValidator('multipleof', {
					validateNumber: function(value, requirement) {
						return value % requirement === 0;
				},
				requirementType: 'integer',
				messages: {
					en: '$error'
				}
			});
JS;
        }

        $js = $this->processJsArr( $js, $field, $rule, $clientMessageOnly );

        return $js;
    }
}