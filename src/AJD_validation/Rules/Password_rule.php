<?php 

namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_compound;
use AJD_validation\Contracts\Rule_interface;

class Password_rule extends Abstract_compound
{
    protected static $addedRules = [];

    public function __construct()
    {
        $validators = [];
        $validator = $this->getValidator()
                    ->invokable_required()
                    ->minlength(5, true, true)
                    ->alnum();

        $validator2 = $this->getValidator()
                        ->uncompromised();

        $validators[] = $validator;
        $validators[] = $validator2;
        

        parent::__construct(...$validators);
    }
}