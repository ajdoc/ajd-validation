<?php 

namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Validation_provider_interface;
use AJD_validation\AJD_validation as v;

abstract class Validation_provider implements Validation_provider_interface
{
	protected static $defaultRulesDir = '/Rules';
	protected static $defaultRulesNamespace = '\\Rules';

	protected static function processRulesDir($rulesDir = null)
	{
		$rulesDir = !empty($rulesDir) ? $rulesDir : static::$defaultRulesDir;

		$rulesDir = rtrim($rulesDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

		return $rulesDir;
	}

	protected static function processRulesNamespace($rulesNamespace = null)
	{
		$rulesNamespace = !empty($rulesNamespace) ? $rulesNamespace : static::$defaultRulesNamespace;

		$rulesNamespace = rtrim($rulesNamespace, '\\').'\\';

		return $rulesNamespace;
	}


	public static function getRuleShortnames($dir, $rulesDir = null)
    {
    	$rulesDir = static::processRulesDir($rulesDir);

        return array_map(function ($filename) 
        {
            return mb_strtolower(substr(str_replace('_'.v::$rules_suffix, '', $filename), 0, -4));
        }, array_diff(scandir($dir . $rulesDir), ['.', '..']));
    }

    public function registerRules($dir, $baseNamespace )
    {
    	$rulesDir = static::processRulesDir();
    	$rulesNamespace = static::processRulesNamespace();
    	
    	v::addRuleDirectory($dir.$rulesDir)
    		->addRuleNamespace($baseNamespace.$rulesNamespace);
    }

    public function registerRulesDir($dir, $rulesDir = null )
    {
    	$rulesDir = static::processRulesDir($rulesDir);
    	
    	v::addRuleDirectory($dir . $rulesDir);

    	return $this;
    }

    public function registerRulesNamespace($namespace, $rulesNamespace = null )
    {
    	$rulesNamespace = static::processRulesNamespace($rulesNamespace);

    	v::addRuleNamespace($namespace.$rulesNamespace);
    }

    abstract public function register();
}