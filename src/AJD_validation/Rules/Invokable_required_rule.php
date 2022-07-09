<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_invokable;

class Invokable_required_rule extends Abstract_invokable
{
    public function __construct()
    {
    }

	public function __invoke($value, $satisfier = NULL, $field = NULL)
    {
        $validator = $this->getValidator();
        
        $check = $validator->required()->validate($value);

        if($this->exception)
        {

            return $this->exception->message($check, [
                $this->exception::ERR_DEFAULT => [
                    $this->exception::STANDARD => 'The :field* field is required.'
                ],
                $this->exception::ERR_NEGATIVE      => [
                    $this->exception::STANDARD          => 'The :field* field is not required.',
                ]
            ]);
            
        }

        return $check;


    }
}

