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
   		$ajd_ins = static::get_ajd_instance();
   		$baseRulesPath = $ajd_ins->get_rules_path();
   		$raw_rule = static::removeWord( $rule, '/^!/' );
        $raw_append_rule = $raw_rule.'_'.static::$rules_suffix;
   		$rule = strtolower( $rule );
   		$clean_rule = $ajd_ins->clean_rule_name( $rule );		
  		$append_rule = ucfirst( $clean_rule['rule'] ).'_'.static::$rules_suffix;
  		$lower_rule = strtolower( $append_rule );
        $raw_append_rule_frommacro = $raw_rule.static::$signatureName.'_'.static::$rules_suffix;
        $append_rule_frommacro = ucfirst( $clean_rule['rule'] ).static::$signatureName.'_'.static::$rules_suffix;
   		$rulesPath = $baseRulesPath.$append_rule.'.php';
   		
        if( !EMPTY( static::$addRuleDirectory ) )
        {
            foreach( static::$addRuleDirectory as $classPath )
            {
                if( file_exists( $classPath.$append_rule.'.php' ) )
                {
                    $rulesPath = $classPath.$append_rule.'.php';
                }   
            }
        }

        $is_class = file_exists( $rulesPath );

        if(!$is_class)
        {
            if(!empty(static::$addRulesMappings))
            {
                if(isset(static::$addRulesMappings[$lower_rule]))
                {
                    $is_class = true;
                }
            }
        }

   		$is_function = function_exists( $rule );
   		$factory = null;
        
   		if( $is_class )
   		{
   			$factory = static::get_factory_instance()->get_instance(true);

            if( !EMPTY( static::$addRuleNamespace ) )
            {
                static::_appendValidRuleNameSpace( $factory );
            }

   			$ruleObj = $factory->rules($rulesPath, $append_rule, $arguments);
            
   			return $ruleObj;
   		} 
        else if( 
            $ajd_ins->isset_empty( static::$ajd_prop['anonymous_class_override'], $append_rule ) 
            ||
            $ajd_ins->isset_empty( static::$ajd_prop['anonymous_class_override'], $raw_append_rule ) 
            ||
            $ajd_ins->isset_empty( static::$ajd_prop['anonymous_class_override'], $append_rule_frommacro ) 
            ||
            $ajd_ins->isset_empty( static::$ajd_prop['anonymous_class_override'], $raw_append_rule_frommacro ) 
        )
        {
            $anon_details = [];

            if(isset(static::$ajd_prop['anonymous_class_override'][$raw_append_rule]))
            {
                $anon_details = static::$ajd_prop['anonymous_class_override'][$raw_append_rule];
            }
            else if(isset(static::$ajd_prop['anonymous_class_override'][$append_rule]))
            {
                $anon_details = static::$ajd_prop['anonymous_class_override'][$append_rule];
            }
            else if(isset(static::$ajd_prop['anonymous_class_override'][$append_rule_frommacro]))
            {
                $anon_details = static::$ajd_prop['anonymous_class_override'][$append_rule_frommacro];
            }
            else if(isset(static::$ajd_prop['anonymous_class_override'][$raw_append_rule_frommacro]))
            {
                $anon_details = static::$ajd_prop['anonymous_class_override'][$raw_append_rule_frommacro];   
            }
            
            if(!empty($anon_details))
            {
                static::$anonRuleExceptions[ spl_object_id($anon_details['obj']) ] = $anon_details['exception'];

                return $anon_details['obj'];
            }
        }
   		
   	}

    private static function _appendValidRuleNameSpace( $factory )
    {
        foreach( static::$addRuleNamespace as $ruleNamespace )
        {
            $factory->append_rules_namespace( $ruleNamespace );
        }
    }
}