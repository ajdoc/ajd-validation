<?php 
namespace AJD_validation\ClientSide;

use AJD_validation\Contracts\AbstractClientSide;

class JqvalidationAjdCommonClientSide extends AbstractClientSide
{
	public static function getCLientSideFormat(string $field, string $rule, $satisfier = null, string $error = null,  $value = null)
	{
		return static::commonClientSide($field, $rule, $satisfier, $error, $value);
	}

	public static function commonClientSide(string $field, string $rule, $satisfier = null, string $error = null,  $value = null)
	{
		$js = [];

		$js = static::{static::$jsTypeFormat.'Common'}($field, $rule, $satisfier, $error, $value);

		if(empty($js))
		{
			return $js;
		}

		$js = static::$ruleObj->processJsArr( $js, $field, $rule, static::$clientMessageOnly );
		
        return $js;
	}

	protected static function jqvalidationCommon(string $field, string $rule,  $satisfier = null, string $error = null,  $value = null)
	{
		$relateRegex = array_merge(static::$relatedRegex, static::$relatedCtype);

		$commonJson = [];
		$commonJson = array_merge($commonJson, static::$relatedRequiredRules, static::$relatedEmailRules, static::$relatedDateRule);

		$hasCustomFunction = [];
		$hasCustomFunction = array_merge($hasCustomFunction, $relateRegex, static::$relatedInRule, static::$relatedMultipleOf);

		if( in_array($rule, $commonJson, true) )
		{
			$js['clientSideJson'][$field][$rule] = true;
			$js['clientSideJsonMessages'][$field][$rule] = $error;
		}
		else if( in_array($rule, $relateRegex, true) )
		{
			$method = 'getRegex';

			if(in_array($rule, static::$relatedCtype, true))
			{
				$method = 'getRegexString';
			}

			$error  = preg_replace('/\"/', '', $error);
			$reflect = new \ReflectionMethod(get_class(static::$ruleObj), $method);
			$reflect->setAccessible(true);

			$regex = $reflect->invoke(static::$ruleObj);
			
			$regex = str_replace('/', '', $regex);

			$js['clientSideJson'][$field][$rule] = $regex;
			$js['clientSideJsonMessages'][$field][$rule] = $error;
		}
		else if( in_array($rule, static::$relatedLength, true) )
		{
			$isString = !empty( $satisfier[2] ) ? '1' : '0';
			$js['clientSideJson'][$field][$rule] = $satisfier[0];
			$js['clientSideJsonMessages'][$field][$rule] = $error;

		}
		else if( in_array( $rule, static::$relatedInRule, true ) )
		{
			$haystack = implode('|+', static::$ruleObj->haystack);
        	$identical = ( !empty( static::$ruleObj->identical ) ) ? 'true' : 'false';

        	$js['clientSideJson'][$field][$rule] = [ $haystack, $identical ];
			$js['clientSideJsonMessages'][$field][$rule] = $error;
		}
		else if(in_array( $rule, static::$relatedMultipleOf, true ))
		{
			$multipleof = static::$ruleObj->multipleof;

			$js['clientSideJson'][$field][$rule] = $multipleof;
			$js['clientSideJsonMessages'][$field][$rule] = $error;
		}

		if( in_array($rule, $hasCustomFunction, true) )
		{
			if(in_array( $rule, $relateRegex, true ))
			{
				$js = static::{static::$jsTypeFormat.'RegexCustomJsCommon'}($js, $field, $rule, $satisfier, $error, $value);				
			}
			else if(in_array( $rule, static::$relatedInRule, true ))
			{
				$js = static::{static::$jsTypeFormat.'InCustomJsCommon'}($js, $field, $rule, $satisfier, $error, $value);	
			}
			else if(in_array( $rule, static::$relatedMultipleOf, true ))
			{
				$js = static::{static::$jsTypeFormat.'MultipleOfCustomJsCommon'}($js, $field, $rule, $satisfier, $error, $value);			
			}
		}

		return $js;
	}

	protected static function jqvalidationInCustomJsCommon(array $js, string $field, string $rule, $satisfier = null, string $error = null,  $value = null)
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

		 	jQuery.validator.addMethod('$rule', function(value, element, params) 
			{
				var arr 		= params[0].split('|+');
				var identical 	= params[1] || false;

				return this.optional(element) || inRuleArray(value, arr, identical);

			}, '$error');
JS;
		return $js;
	}

	protected static function jqvalidationMultipleOfCustomJsCommon(array $js, string $field, string $rule, $satisfier = null, string $error = null,  $value = null)
	{
		$js[$field][$rule]['js'] =   <<<JS

			jQuery.validator.addMethod('$rule', function(value, element, params) 
			{
				return this.optional(element) || value % params === 0;
			}, '$error');
JS;
		return $js;
	}

	protected static function jqvalidationRegexCustomJsCommon(array $js, string $field, string $rule, $satisfier = null, string $error = null,  $value = null)
	{
		$js[$field][$rule]['js'] =   <<<JS

			jQuery.validator.addMethod('$rule', function(value, element, params) 
			{
				return this.optional(element) || new RegExp(params).test(value);

			}, '$error');
JS;
		return $js;
	}
}