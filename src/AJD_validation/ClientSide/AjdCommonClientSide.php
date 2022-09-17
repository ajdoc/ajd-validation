<?php 
namespace AJD_validation\ClientSide;

use AJD_validation\Contracts\AbstractClientSide;

class AjdCommonClientSide extends AbstractClientSide
{
	protected static $relatedEmailRules = [
		'email', 'base_email', 'rfc_email', 'spoof_email', 'no_rfc_email'
	];

	protected static $relatedRequiredRules = [
		'required', 'required_allowed_zero'
	];

	protected static $relatedInRule = [
		'in'
	];

	protected static $relatedDateRule = [
		'date'
	];

	public static function getCLientSideFormat(string $field, string $rule, mixed $satisfier = null, string $error = null, mixed $value = null)
	{
		if( in_array( $rule, static::$relatedRequiredRules, true ) )
		{
			return static::commonRequiredClientSide($field, $rule, $satisfier, $error, $value);
		}
		else if( in_array( $rule, static::$relatedEmailRules, true ) )
		{
			return static::commonEmailClientSide($field, $rule, $satisfier, $error, $value);
		}
		else if( in_array( $rule, static::$relatedInRule, true ) )
		{
			return static::commonInClientSide($field, $rule, $satisfier, $error, $value);	
		}
		else if( in_array( $rule, static::$relatedDateRule, true ) )
		{
			return static::commonDateClientSide($field, $rule, $satisfier, $error, $value);	
		}
	}

	public static function commonDateClientSide(string $field, string $rule, mixed $satisfier = null, string $error = null, mixed $value = null)
	{
		$js = static::{static::$jsTypeFormat.'Common'}($field, $rule, $satisfier, $error, $value);

		$js = static::$ruleObj->processJsArr( $js, $field, $rule, static::$clientMessageOnly );
		
        return $js;
	}

	public static function commonInClientSide(string $field, string $rule, mixed $satisfier = null, string $error = null, mixed $value = null)
	{
		$js = static::{static::$jsTypeFormat.'Common'}($field, $rule, $satisfier, $error, $value);

		$js = static::$ruleObj->processJsArr( $js, $field, $rule, static::$clientMessageOnly );
		
        return $js;
	}

	public static function commonEmailClientSide(string $field, string $rule, mixed $satisfier = null, string $error = null, mixed $value = null)
	{
		$js = static::{static::$jsTypeFormat.'Common'}($field, $rule, $satisfier, $error, $value);

		$js = static::$ruleObj->processJsArr( $js, $field, $rule, static::$clientMessageOnly );
		
        return $js;
	}

	public static function commonRequiredClientSide(string $field, string $rule, mixed $satisfier = null, string $error = null, mixed $value = null)
	{
		$js = static::{static::$jsTypeFormat.'Common'}($field, $rule, $satisfier, $error, $value);

        $js = static::$ruleObj->processJsArr( $js, $field, $rule, static::$clientMessageOnly );

        return $js;
	}

	protected static function parsleyCommon(string $field, string $rule, mixed $satisfier = null, string $error = null, mixed $value = null)
	{
		$typedRule = [];
		$typedRule = array_merge($typedRule, static::$relatedDateRule);
		$hasCustomFunction = array_merge(static::$relatedInRule, static::$relatedDateRule);

		if( in_array($rule, static::$relatedRequiredRules, true) )
		{
			$js[$field][$rule]['rule'] =   <<<JS
                data-parsley-required="true"
JS;
		}
		else if( in_array( $rule, static::$relatedEmailRules, true ) )
		{
			$js[$field][$rule]['rule'] =   <<<JS
	            data-parsley-type="email"
JS;
		}
		else if(in_array( $rule, $typedRule, true ))
		{
			$js[$field][$rule]['rule'] =   <<<JS
                data-parsley-$rule='true'
                
JS;
		}
		else if( in_array( $rule, static::$relatedInRule, true ) )
		{
			$haystack = implode('|+', static::$ruleObj->haystack);
        	$identical = ( !empty( static::$ruleObj->identical ) ) ? 'true' : 'false';

			$js[$field][$rule]['rule'] =   <<<JS
                data-parsley-inrule='$haystack'
                data-parsley-inrule-identical='$identical'
JS;
		}

		$js[$field][$rule]['message'] = <<<JS
			data-parsley-type-message="$error"
JS;
		if( in_array($rule, $hasCustomFunction, true) )
		{
			if( in_array($rule, static::$relatedInRule, true) )
			{
				$js = static::{static::$jsTypeFormat.'InCustomJsCommon'}($js, $field, $rule, $satisfier, $error, $value);	
			}
			else if( in_array($rule, static::$relatedDateRule, true) )
			{
				$js = static::{static::$jsTypeFormat.'DateCustomJsCommon'}($js, $field, $rule, $satisfier, $error, $value);		
			}
			
		}

		return $js;

	}

	protected static function parsleyInCustomJsCommon(array $js, string $field, string $rule, mixed $satisfier = null, string $error = null, mixed $value = null)
	{
		$js[$field][$rule]['js'] =   <<<JS

			 	function inRuleArray(value, haystack, identical)
			 	{
			 		for (var i in haystack) 
			 		{ 
			 			if( identical )
			 			{
			 				if (haystack[i] === value) return true; 
			 			}
			 			else
			 			{
			 				if (haystack[i] == value) return true; 
			 			}
			 		}

			 		return false;
			 	}

				window.Parsley.addValidator('inrule', {
					validate: function(value, requirement, obj) {
						var arr 		= requirement.split('|+');
						var identical 	= false;
						var elem 	= $(obj.element);
					 	var msg 	= $(obj.element).attr('data-parsley-$rule-message');
						
						if( elem.attr('data-parsley-inrule-identical') )
						{
							identical 	= true;
						}

						var check 	= inRuleArray(value, arr, identical);

						if( !check )
						{
							return $.Deferred().reject(msg);
						}

						return inRuleArray(value, arr, identical);
				},
				messages: {
					en: '$error'
				}
			});
JS;
		return $js;
	}

	protected static function parsleyDateCustomJsCommon(array $js, string $field, string $rule, mixed $satisfier = null, string $error = null, mixed $value = null)
	{
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
		return $js;
	}
}