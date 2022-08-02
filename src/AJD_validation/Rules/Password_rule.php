<?php 

namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_compound;

class Password_rule extends Abstract_compound
{
	public function __construct()
    {
        $validator = $this->getValidator()
                        ->invokable_required()
                        ->minlength(5, true, true)
                        ->alnum();

        $validator2 = $this->getValidator()
                        ->uncompromised();

        parent::__construct($validator, $validator2);
    }
}