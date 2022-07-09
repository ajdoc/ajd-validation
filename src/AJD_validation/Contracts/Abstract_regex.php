<?php namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Abstract_filter_type;
use AJD_validation\AJD_validation as v;

abstract class Abstract_regex extends Abstract_filter_type
{
	abstract protected function getRegex();

	protected function getRegexString()
    {
        return '';
    }

	public function validateValue($value)
    {
        return preg_match($this->getRegex(), $value);
    }

    public function getCLientSideFormat( $field, $rule, $jsTypeFormat, $clientMessageOnly = FALSE, $satisfier = NULL, $error = NULL, $value = NULL )
    {
        $regex  = $this->getRegex();

        if( !EMPTY( $regex ) )
        {
        
            $error  = preg_replace('/\"/', '', $error);
            
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

							return requirement.test(value);
					},
					requirementType : 'regexp',
					messages: {
						en: '$error'
					}
				});
JS;
               
            }
        }

        $js                 = $this->processJsArr( $js, $field, $rule, $clientMessageOnly );

        return $js;
    }
}