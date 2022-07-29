<?php 

namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_sequential;
use AJD_validation\Contracts\Rule_interface;

class Sequential_rule extends Abstract_sequential
{
	public function __construct(Rule_interface $rules)
    {
        parent::__construct($rules);
    }
}