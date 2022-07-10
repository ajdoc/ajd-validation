<?php namespace AJD_validation\Contracts;

use Exception;
use TypeError;
use AJD_validation\AJD_validation as v;
use AJD_validation\Contracts\Abstract_rule;

abstract class Abstract_enum extends Abstract_rule
{
	private static array $enumInstances = [];
	public $enumType;
	public $backedEnum = true;

	public function __construct($enumType)
    {
    	$this->enumType = $enumType;

    }

    public function run( $value, $satisfier = NULL, $field = NULL )
	{   
		$check 		= FALSE;

		$validator = v::getValidator();

		if(!$validator->required()->validate($value))
		{
			return false;
		}

		if(
			! function_exists('enum_exists') || 
            ! enum_exists($this->enumType)
        )
		{
			return false;
		}

		if( !method_exists($this->enumType, 'cases') )
		{
			return false;
		}

		if(method_exists($this->enumType, 'tryFrom'))
		{
			$this->backedEnum = true;
		}
		else
		{
			$this->backedEnum = false;	
		}
		

        if ($value instanceof $this->enumType) 
        {
            return true;
        }

        try 
        {
        	if($this->backedEnum)
        	{
        		if(method_exists($this->enumType, 'tryFrom'))
        		{
        			return ! is_null($this->enumType::tryFrom($value));
        		}
        	}
        	else
        	{
        		
        		if(method_exists($this->enumType, 'cases'))
        		{
					if( in_array($value, $this->enumType::cases(), true) )
					{
						return true;
					}
					else
					{
						if(is_string($value))
						{
							$cases = $this->enumType::cases();

							if(!empty($cases))
							{
								foreach( $cases as $case )
								{
									if($case->name == $value)
									{
										return true;
									}
								}
							}
						}
					}
        		}
        	}
        } 
        catch (TypeError $e) 
        {
            return false;
        }

        return $check;
	}

    public function validate( $value )
    {
        $check      = $this->run( $value );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
    }

}