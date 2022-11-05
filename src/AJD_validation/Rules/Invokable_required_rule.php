<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_invokable;
use AJD_validation\Constants\Lang;
use AJD_validation\Contracts\Abstract_exceptions as ex;

class Invokable_required_rule extends Abstract_invokable
{
    public function __construct()
    {
    }

	public function __invoke($value, $satisfier = NULL, $field = NULL)
    {
        $validator = $this->getValidator();
        
        $check = $validator->required()->validate($value);

        return $this->checks($check, ['default' => 'The :field* field is required.', 'inverse' => 'The :field* field is not required.']);

        /*return $this->checks($check, 
            [
                ex::ERR_DEFAULT => [
                    ex::STANDARD => 'The :field* field is required.'
                ],
                ex::ERR_NEGATIVE      => [
                    ex::STANDARD          => 'The :field* field is not required.',
                ],
                Lang::FIL => [
                    ex::ERR_DEFAULT => [
                        ex::STANDARD => 'The :field* field ay kelangan.'
                    ],
                    ex::ERR_NEGATIVE      => [
                        ex::STANDARD          => 'The :field* field ay hindi kelangan.',
                    ]
                ]
            ]
        );*/
    }
}

