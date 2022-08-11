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

    protected static $defaultFiltersDir = '/Filters';
    protected static $defaultFiltersNamespace = '\\Filters';

    protected static $defaultLogicsDir = '/Logics';
    protected static $defaultLogicsNamespace = '\\Logics';

    protected static $defaults = [
        'baseDir' => '',
        'baseNamespace' => ''
    ];

    public function setDefaults(array $defaults)
    {
        static::$defaults = array_merge(static::$defaults, $defaults);

        return $this;
    }

    public function getDefault($key)
    {
        if(
            !isset(static::$defaults[$key])
            ||
            empty(static::$defaults[$key])
        )
        {
            throw new \InvalidArgumentException('Invalid Argument [baseDir or baseNamespace is not yet set].');
        }

        return static::$defaults[$key];
    }

    protected static function processDefaults($default, $cleanSuffix, $arg = null)
    {
        $arg = !empty($arg) ? $arg : $default;

        $arg = rtrim($arg, $cleanSuffix).$cleanSuffix;

        return $arg;
    }

    public function registerRules()
    {
        $dir                    = $this->getDefault('baseDir');
        $baseNamespace          = $this->getDefault('baseNamespace');

    	$rulesDir = static::processDefaults(static::$defaultRulesDir, DIRECTORY_SEPARATOR);

    	$rulesNamespace = static::processDefaults(static::$defaultRulesNamespace, '\\');

    	v::addRuleDirectory($dir.$rulesDir)
    		->addRuleNamespace($baseNamespace.$rulesNamespace);

        return $this;
    }

    public function registerFilters()
    {
        $dir                    = $this->getDefault('baseDir');
        $baseNamespace          = $this->getDefault('baseNamespace');

        $filtersDir = static::processDefaults(static::$defaultFiltersDir, DIRECTORY_SEPARATOR);
        

        $filtersNamespace = static::processDefaults(static::$defaultFiltersNamespace, '\\');
        
        v::addFilterDirectory($dir.$filtersDir)
            ->addFilterNamespace($baseNamespace.$filtersNamespace);

        return $this;
    }

    public function registerLogics()
    {
        $dir                    = $this->getDefault('baseDir');
        $baseNamespace          = $this->getDefault('baseNamespace');

        $logicsDir = static::processDefaults(static::$defaultLogicsDir, DIRECTORY_SEPARATOR);

        $logicsNamespace = static::processDefaults(static::$defaultLogicsNamespace, '\\');

        
        v::whenInstance()
            ->addLogicClassPath($dir.$logicsDir)
            ->addLogicNamespace($baseNamespace.$logicsNamespace)
        ->endwhen();

        return $this;
    }

    public function registerRulesDir($rulesDir = null )
    {
        $dir      = $this->getDefault('baseDir');

    	$rulesDir = static::processDefaults(static::$defaultRulesDir, DIRECTORY_SEPARATOR, $rulesDir);
    	
    	v::addRuleDirectory($dir . $rulesDir);

    	return $this;
    }

    public function registerRulesNamespace($rulesNamespace = null )
    {
        $namespace          = $this->getDefault('baseNamespace');

    	$rulesNamespace = static::processDefaults(static::$defaultRulesNamespace, '\\', $rulesNamespace);

    	v::addRuleNamespace($namespace.$rulesNamespace);

        return $this;
    }

    public function registerRulesMapping(array $mappings)
    {
        foreach($mappings as $rule => $exception)
        {
            Rules_map::register($rule);
            Rules_map::setException($rule, $exception);
        }

        return $this;
    }

    public function tryRuleMappingDirectory($rulesDir = null, $exceptionsDir = null)
    {
        $relateMappping = [];
        $mappings = [];

        try
        {
            $dir      = $this->getDefault('baseDir');

            $dir    = rtrim($dir, DIRECTORY_SEPARATOR);

            $baseDir  = dirname($dir);

            $composerJsonPath = $baseDir.DIRECTORY_SEPARATOR . 'composer.json';
            $composerConfig = json_decode(file_get_contents($composerJsonPath));

            $psr4Config = (array) $composerConfig->autoload->{'psr-4'};
            $baseNamespace = key($psr4Config);


            $rulesDir = null;
            $rulesDir = static::processDefaults(static::$defaultRulesDir, DIRECTORY_SEPARATOR, $rulesDir);

            $exceptionsDir = null;
            $exceptionsDir = static::processDefaults(static::$defaultExceptionDir, DIRECTORY_SEPARATOR, $exceptionsDir);

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