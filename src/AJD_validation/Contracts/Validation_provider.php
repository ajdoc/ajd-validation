<?php 

namespace AJD_validation\Contracts;

use AJD_validation\Contracts\ValidationProviderInterface;
use AJD_validation\AJD_validation as v;
use AJD_validation\Helpers\Rules_map;

use AJD_validation\Contracts\Rule_interface;
use AJD_validation\Contracts\Exception_interface;

abstract class Validation_provider implements ValidationProviderInterface
{
	protected static $defaultRulesDir = '/Rules';
    protected static $defaultExceptionDir = '/Exceptions';
	protected static $defaultRulesNamespace = '\\Rules';
    protected static $defaultExceptionNamespace = '\\Exceptions';

	protected static function processRulesDir($rulesDir = null)
	{
		$rulesDir = !empty($rulesDir) ? $rulesDir : static::$defaultRulesDir;

		$rulesDir = rtrim($rulesDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

		return $rulesDir;
	}

    protected static function processExceptionsDir($exceptionsDir = null)
    {
        $exceptionsDir = !empty($exceptionsDir) ? $exceptionsDir : static::$defaultExceptionDir;

        $exceptionsDir = rtrim($exceptionsDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        return $exceptionsDir;
    }

	protected static function processRulesNamespace($rulesNamespace = null)
	{
		$rulesNamespace = !empty($rulesNamespace) ? $rulesNamespace : static::$defaultRulesNamespace;

		$rulesNamespace = rtrim($rulesNamespace, '\\').'\\';

		return $rulesNamespace;
	}

    protected static function processExceptionsNamespace($defaultExceptionNamespace = null)
    {
        $exceptionsNamespace = !empty($exceptionsNamespace) ? $rulesNamespace : static::$defaultExceptionNamespace;

        $exceptionsNamespace = rtrim($exceptionsNamespace, '\\').'\\';

        return $exceptionsNamespace;
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

    public function registerRulesMapping(array $mappings)
    {
        foreach($mappings as $rule => $exception)
        {
            Rules_map::register($rule);
            Rules_map::setException($rule, $exception);
        }

        return Rules_map::getMappings();
    }

    public function tryRuleMappingDirectory($dir, $rulesDir = null, $exceptionsDir = null)
    {
        $relateMappping = [];
        $mappings = [];

        try
        {
            $dir    = rtrim($dir, DIRECTORY_SEPARATOR);

            $baseDir  = dirname($dir);

            $composerJsonPath = $baseDir.DIRECTORY_SEPARATOR . 'composer.json';
            $composerConfig = json_decode(file_get_contents($composerJsonPath));

            $psr4Config = (array) $composerConfig->autoload->{'psr-4'};
            $baseNamespace = key($psr4Config);

            $rulesDir = null;
            $rulesDir = static::processRulesDir($rulesDir);

            $exceptionsDir = null;
            $exceptionsDir = static::processExceptionsDir($exceptionsDir);

            $allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
            $phpFiles = new \RegexIterator($allFiles, '/\.php$/');

            $exceptionsDirClean = str_replace(['/', '\\'], '', $exceptionsDir);
            $rulesDirClean = str_replace(['/', '\\'], '', $rulesDir);

            foreach ($phpFiles as $phpFile) 
            {
                $parentInfo = $phpFile->getPathInfo();
                $parentRealPath = $parentInfo->getRealPath();
                $segments = explode(DIRECTORY_SEPARATOR, $parentRealPath);
                $lastSegment = end($segments);

                if(
                    $lastSegment == $rulesDirClean
                    || $lastSegment == $exceptionsDirClean
                )
                {
                    $qualifiedClass = substr($phpFile->getFileName(), 0, -4);

                    $qualifiedRuleClass = $baseNamespace.$rulesDirClean.'\\'.$qualifiedClass;

                    $qualifiedExceptionClass = $baseNamespace.$exceptionsDirClean.'\\'.$qualifiedClass;

                    $qualifiedRuleExceptionClass = $baseNamespace.$rulesDirClean.'\\'.$exceptionsDirClean.'\\'.$qualifiedClass;

                    $signature   = Rules_map::createRuleSignature($qualifiedClass);

                    if(class_exists($qualifiedRuleClass))
                    {
                        $classExists = $qualifiedRuleClass;
                    }

                    if(class_exists($qualifiedExceptionClass))
                    {
                        $classExists = $qualifiedExceptionClass;
                    }

                    if(class_exists($qualifiedRuleExceptionClass))
                    {
                        $classExists = $qualifiedRuleExceptionClass;
                    }

                    $reflection = new \ReflectionClass($classExists);

                    $interfaces  = array_keys($reflection->getInterfaces());

                    if(in_array(Rule_interface::class, $interfaces, true))
                    {
                        $mappings[$signature]['rule'] = $classExists;
                    }

                    if(in_array(Exception_interface::class, $interfaces, true))
                    {
                        $mappings[$signature]['exception'] = $classExists;
                    }
                }
            }
            
            foreach($mappings as $signature => $maps)
            {
                $rule = $maps['rule'] ?? null;
                $exception = $maps['exception'] ?? null;

                if(!empty($rule) && !empty($exception))
                {
                    $relateMappping[$rule] = $exception;    
                }   
            }
        }
        catch(\Exception $e)
        {
            throw $e;
        }

        return $relateMappping;

    }

    abstract public function register();
}