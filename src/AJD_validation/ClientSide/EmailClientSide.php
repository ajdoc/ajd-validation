<?php 
namespace AJD_validation\ClientSide;

use AJD_validation\Contracts\AbstractClientSide;

class EmailClientSide extends AbstractClientSide
{
	public static function getCLientSideFormat(string $field, string $rule, mixed $satisfier = null, string $error = null, mixed $value = null)
	{
		if( static::$jsTypeFormat == static::$ruleObj::CLIENT_PARSLEY ) 
        {
	 		$js[$field][$rule]['rule'] =   <<<JS
	            data-parsley-type="email"
JS;

			$js[$field][$rule]['message'] = <<<JS
                data-parsley-type-message="$error"
JS;

		}

		$js = static::$ruleObj->processJsArr( $js, $field, $rule, static::$clientMessageOnly );
		
        return $js;
	}
}