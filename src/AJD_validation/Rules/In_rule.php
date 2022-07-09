<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_search;

class In_rule extends Abstract_search
{
	public $haystack;
	public $identical;

	public function __construct( $haystack = NULL, $identical = NULL )
	{
		$this->haystack 	= $haystack;
		$this->identical 	= $identical;
	}

	public function getCLientSideFormat( $field, $rule, $jsTypeFormat, $clientMessageOnly = FALSE, $satisfier = NULL, $error = NULL, $value = NULL )
    {
    	if( $jsTypeFormat == Abstract_search::CLIENT_PARSLEY ) 
        {
        	$haystack 	= implode('|+', $this->haystack);
        	$identical  = ( !EMPTY( $this->identical ) ) ? $this->identical : false;
        	
    	 	$js[$field][$rule]['rule'] =   <<<JS
                data-parsley-inrule='$haystack'
                data-parsley-inrule-identical='$identical'
JS;
			$js[$field][$rule]['message'] =   <<<JS
                    data-parsley-$rule-message='$error'
JS;

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
        }

        $js                 = $this->processJsArr( $js, $field, $rule, $clientMessageOnly );

        return $js;
    }
}