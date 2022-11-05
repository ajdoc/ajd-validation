<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Contracts\ExpressionRuleInterface;
use AJD_validation\Helpers\Errors;
use AJD_validation\Helpers\Expression;

class Expression_rule extends Abstract_rule implements ExpressionRuleInterface
{
    public $expression = '';
    protected $expressionArguments = [];
    protected $expressionObj;
    public $debug = false;

    protected $expressionCallback = null;

    public function __construct($expression = '', $expressionArguments = null, $debug = null)
    {
        $this->checkExpression($expression);

        $this->expression = $expression;

        if(is_array($expressionArguments))    
        {
            $this->expressionArguments = $expressionArguments;
        }

        if(is_bool($debug))    
        {
            $this->debug = $debug;
        }
    }

    public function callback($callback)
    {
        if(is_callable($callback))
        {
            $this->expressionCallback = $callback;
        }

        return $this->getReturn();
    }

    protected function checkExpression($expression)
    {
        if(!is_string($expression))
        {
            throw new \InvalidArgumentException('Expression must be a string.');
        }
    }

    public function setExpressionArguments(array $arguments = [])
    {
        $this->expressionArguments = $arguments;
    }

	public function run( $value, $satisfier = null, $field = null, $clean_field = null, $origValue = null )
	{   
		$check = false;

        $expression = $this->expression;
        $expressionArguments = $this->expressionArguments;

        $field_arr = $this->format_field_name( $field );

        if(isset($satisfier[0]) && !empty($satisfier[0]))
        {
            $expression = $satisfier[0];
        }

        if(isset($satisfier[1]) && !empty($satisfier[1]) && is_array($satisfier[1]))
        {
            $expressionArguments = $satisfier[1];
        }

        if(!empty($origValue) && is_array($origValue))
        {
            if(empty($expressionArguments))
            {
                $expressionArguments = $origValue;    
            }
            else
            {
                $expressionArguments = array_merge($origValue, $expressionArguments);
            }
        }

        $expressionArguments['value'] = $value;
        $expressionArguments['validator'] = $this->getValidator();

        if(empty($expression))
        {
            return true;
        }

        if(!empty($this->expressionCallback) && is_callable($this->expressionCallback))
        {
            $expressionReturn = \call_user_func_array($this->expressionCallback, [$expression, $this]);

            if(!empty($expressionReturn) && is_string($expressionReturn))
            {
                $expression = $expressionReturn;
            }
        }

        $expression = static::replaceExrpressionPlaceholder(
            [
                'valueKey' => $this->valueKey,
                'orig_field' => $field_arr['orig'] ?? ''
            ], 
            $expression, 
            false
        );
        
        $this->expression = $expression;
        $this->expressionArguments = $expressionArguments;

        $this->checkExpression($expression);

        $this->expressionObj = new Expression($expressionArguments);

        $append_error = '';

        try
        {
            $evaluate = $this->expressionObj->evaluate($expression);

            $check = $evaluate;

            if(!is_bool($evaluate))
            {
                $check = false;
            }
        }
        catch(\Exception $e)
        {
            $check = false;
            $append_error = $e->getMessage();
        }

		return [
            'check' => $check,
            'append_error' => $append_error
        ];
	}

    public function validate( $value )
    {
        $check = $this->run( $value, [$this->expression, $this->expressionArguments] );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
    }
}

