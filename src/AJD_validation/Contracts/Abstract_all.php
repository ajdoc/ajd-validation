<?php namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Contracts\Abstract_exceptions;

abstract class Abstract_all extends Abstract_rule
{
	protected $rules 	= array();

	public function __construct()
	{
		$this->addRules(func_get_args());
	}

    public function setName($name)
    {
        $parentName     = $this->getName();

        foreach ($this->rules as $rule) 
        {
            $ruleName   = $rule->getName();

            if( $ruleName && $parentName !== $ruleName) 
            {
                continue;
            }

            $rule->setName($name);
        }

        return parent::setName($name);
    }

 	public function removeRules()
    {
        $this->rules = array();
    }

	public function addRules(array $rules)
    {
    	foreach ($rules as $key => $rule) 
    	{
    		if($rule instanceof Rule_interface) 
    		{
    			$this->appendRule($rule);
    		}
    		else if( is_numeric( $key ) AND is_array( $rule ) )
    		{
    			$this->addRules( $rule );
    		}
    		else if( is_array( $rule ) )
    		{
    			$this->addRuleValidator( $key, $rule );
    		}
    		else
    		{
    			$this->addRuleValidator( $rule );
    		}

    	}

    	return $this;
    }

    public function addRuleValidator( $rule, $arguments = array() )
    {
    	if( !$rule instanceof Rule_interface )
    	{

    	}
    	else
    	{
    		$this->appendRule( $rule );
    	}

    	return $this;
    }
	
	protected function appendRule(Rule_interface $rule)
    {
    	 $this->rules[spl_object_hash($rule)] = $rule;
    }

    public function getRules()
    {
        return $this->rules;
    }

    protected function assertRules($value, $override = FALSE)
    {
        $validators     = $this->getRules();
        $exceptions     = array();
        
        foreach( $validators as $v )
        {
            try
            {
                $v->assertErr( $value, $override );
            }
            catch( Abstract_exceptions $e )
            {
                $exceptions[] = $e;
            }
        }

        return $exceptions;
    }


    protected function assertSequenceRules($value, $clean_field = null, $override = FALSE)
    {
        $validators     = $this->getRules();
        $exceptions     = array();
        
        foreach( $validators as $v )
        {
            try
            {
                if(!empty($clean_field))
                {
                    $v->setName($clean_field);
                }
                $v->assertErr( $value, $override );
            }
            catch( Abstract_exceptions $e )
            {
                $exceptions[] = $e;

                return $exceptions;
            }
        }

        return $exceptions;
    }
}