<?php 

namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_sequential;
use AJD_validation\Contracts\Rule_interface;

class Sequential_rule extends Abstract_sequential
{
	public function __construct()
    {
    	$rules = func_get_args();

    	foreach($rules as $rule)
    	{
    		if(!$rule instanceof Rule_interface)
    		{
    			throw new \InvalidArgumentException('Invalid Rule.');
    		}
    	}
    	
        parent::__construct(...$rules);
    }
}