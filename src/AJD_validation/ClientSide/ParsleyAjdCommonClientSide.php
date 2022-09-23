<?php 
namespace AJD_validation\ClientSide;

use AJD_validation\Contracts\AbstractClientSide;

class ParsleyAjdCommonClientSide extends AbstractClientSide
{
	public static function getCLientSideFormat(string $field, string $rule, $satisfier = null, string $error = null, $value = null)
	{
		return static::commonClientSide($field, $rule, $satisfier, $error, $value);
	}

	public static function commonClientSide(string $field, string $rule, $satisfier = null, string $error = null, $value = null)
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

	protected static function parsleyCommon(string $field, string $rule, $satisfier = null, string $error = null, $value = null)
	{
		$typedRule = [];
		$typedRule = array_merge($typedRule, static::$relatedDateRule);
		$relateRegex = array_merge(static::$relatedRegex, static::$relatedCtype);
		$hasCustomFunction = array_merge(static::$relatedInRule, static::$relatedDateRule, static::$relatedMultipleOf, $relateRegex);

		if( in_array($rule, static::$relatedRequiredRules, true) )
		{
			$js[$field][$rule]['rule'] =   <<<JS
                data-parsley-required="true"
JS;
			$js[$field][$rule]['message'] = <<<JS
				data-parsley-required-message='$error'
JS;
		}
		else if( in_array( $rule, static::$relatedEmailRules, true ) )
		{

			$js[$field][$rule]['rule'] =   <<<JS
	            data-parsley-type="email"
JS;
			$js[$field][$rule]['message'] = <<<JS
				data-parsley-type-message='$error'
JS;

		}
		else if( in_array($rule, static::$relatedLength, true) )
		{
			$js[$field][$rule]['rule'] =   <<<JS
	            data-parsley-$rule="{$satisfier[0]}"
JS;
			$js[$field][$rule]['message'] = <<<JS
                data-parsley-$rule-message='$error'
JS;
		}
		else if(in_array( $rule, $typedRule, true ))
		{
			$js[$field][$rule]['rule'] =   <<<JS
                data-parsley-$rule='true'
                
JS;
			$js[$field][$rule]['message'] = <<<JS
				data-parsley-$rule-message='$error'
JS;

		}
		else if(in_array( $rule, static::$relatedMultipleOf, true ))
		{
			$multipleof = static::$ruleObj->multipleof;
			$js[$field][$rule]['rule'] =   <<<JS
                data-parsley-multipleof="$multipleof"
JS;
		}
		else if(in_array( $rule, $relateRegex, true ))
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

			$js[$field][$rule]['rule'] =   <<<JS
            	data-parsley-$rule=$regex
JS;

            $js[$field][$rule]['message'] =   <<<JS
            	data-parsley-$rule-message='$error'
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
			$js[$field][$rule]['message'] = <<<JS
				data-parsley-$rule-message='$error'
JS;

		}

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
			else if(in_array( $rule, static::$relatedMultipleOf, true ))
			{
				$js = static::{static::$jsTypeFormat.'MultipleOfCustomJsCommon'}($js, $field, $rule, $satisfier, $error, $value);			
			}
			else if(in_array( $rule, $relateRegex, true ))
			{
				$js = static::{static::$jsTypeFormat.'RegexCustomJsCommon'}($js, $field, $rule, $satisfier, $error, $value);				
			}	
		}

		return $js;
	}

	protected static function parsleyInCustomJsCommon(array $js, string $field, string $rule, $satisfier = null, string $error = null,  $value = null)
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

	protected static function parsleyDateCustomJsCommon(array $js, string $field, string $rule, $satisfier = null, string $error = null,  $value = null)
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

	protected static function parsleyMultipleOfCustomJsCommon(array $js, string $field, string $rule, $satisfier = null, string $error = null,  $value = null)
	{
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
		return $js;
	}

	protected static function parsleyRegexCustomJsCommon(array $js, string $field, string $rule, $satisfier = null, string $error = null,  $value = null)
	{
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

					return requirement.test(value);
			},
			requirementType : 'regexp',
			messages: {
				en: '$error'
			}
		});
JS;
		return $js;
	}
}