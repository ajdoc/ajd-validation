<?php namespace AJD_validation\Contracts;

require dirname( dirname(__FILE__) ).DIRECTORY_SEPARATOR.'Rules'.DIRECTORY_SEPARATOR.'All_rule.php';

use AJD_validation\Rules\All_rule;

class Validator extends All_rule
{
  	public static function __callStatic($method, array $arguments)
    {
      
        if('all' === $method) 
        {
            return static::buildRule($method, $arguments);
        }

        $validator = new static();

        return $validator->__call($method, $arguments);
    }

    public function __call($method, array $arguments)
    {
        return $this->addRuleValidator( static::buildRule($method, $arguments) );
    }

    public static function buildRule($rule, $arguments = array())
   	{

   		return static::processRules( $rule, $arguments );
   	}

   	protected static function processRules( $rule, $arguments = array() )
   	{
   		$ajd_ins 		    = static::get_ajd_instance();
   		$baseRulesPath 	    = $ajd_ins->get_rules_path();
   		$raw_rule 			= static::removeWord( $rule, '/^!/' );
   		$rule 				= strtolower( $rule );
   		$clean_rule 		= $ajd_ins->clean_rule_name( $rule );		
  		$append_rule 		= ucfirst( $clean_rule['rule'] ).'_'.static::$rules_suffix;
  		$lower_rule 		= strtolower( $append_rule );

   		$rulesPath 			= $baseRulesPath.$append_rule.'.php';
   		

        if( !EMPTY( static::$addRuleDirectory ) )
        {
            foreach( static::$addRuleDirectory as $classPath )
            {
                if( file_exists( $classPath.$append_rule.'.php' ) )
                {
                    $rulesPath     = $classPath.$append_rule.'.php';
                }   
            }
        }

      $is_class           = file_exists( $rulesPath );

   		$is_function 		    = function_exists( $rule );

   		$factory 			      = NULL;

   		if( $is_class )
   		{
   			$factory 		= static::get_factory_instance()->get_instance( TRUE );

            if( !EMPTY( static::$addRuleNamespace ) )
            {
                static::_appendValidRuleNameSpace( $factory );
            }

   			$ruleObj 		= $factory->rules($rulesPath, $append_rule, $arguments);
            // var_dump($ruleObj);
   			return $ruleObj;
   		} 
   		/*else if( $is_function )
   		{
   			$factory 		= static::get_factory_instance()->get_instance( FALSE, TRUE );

   			if( $factory->func_valid( $lower_rule ) )
   			{
   				$funcReflect 	= $factory->rules( $lower_rule,  array() );

   				return $funcReflect;
   			}
   		}*/
   		
   	}

    private static function _appendValidRuleNameSpace( $factory )
    {
        foreach( static::$addRuleNamespace as $ruleNamespace )
        {
            $factory->append_rules_namespace( $ruleNamespace );
        }
    }
}