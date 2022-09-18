<?php 

namespace AJD_validation\Contracts;

use AJD_validation\Contracts\ValidationProviderInterface;
use AJD_validation\AJD_validation as v;
use AJD_validation\Helpers\Rules_map;
use AJD_validation\Helpers\Filters_map;
use AJD_validation\Helpers\LogicsAddMap;
use AJD_validation\Helpers\Client_side;

use AJD_validation\Contracts\Rule_interface;
use AJD_validation\Contracts\Exception_interface;
use AJD_validation\Contracts\Extension_interface;
use AJD_validation\Contracts\Filter_interface;
use AJD_validation\Contracts\Logic_interface;
use AJD_validation\Contracts\Validation_interface;
use AJD_validation\Contracts\ClientSideInterface;

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

    protected static $defaultValidationsDir = '/Validations';
    protected static $defaultValidationsNamespace = '\\Validations';

    protected static $defaultClientSideDir = '/ClientSides';
    protected static $defaultClientSideNamespace = '\\ClientSides';

    protected static $defaults = [
        'baseDir' => '',
        'baseNamespace' => ''
    ];

    protected static $validationsCollection = [];
    protected static $clientSideCollection = [];

    private function getAjdInstance()
    {
        return v::get_ajd_instance();
    }

    public static function getValidationsCollection()
    {
        return static::$validationsCollection;
    }

    public static function getClientSideCollection()
    {
        return static::$clientSideCollection;
    }

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

    public function registerClientSides()
    {
        $dir                    = $this->getDefault('baseDir');
        $baseNamespace          = $this->getDefault('baseNamespace');

        $clientSideDir = static::processDefaults(static::$defaultClientSideDir, DIRECTORY_SEPARATOR);
        $clientSideNamespace = static::processDefaults(static::$defaultClientSideNamespace, '\\');
        
        v::addClientSideDirectory($dir.$clientSideDir)
            ->addClientSideNamespace($baseNamespace.$clientSideNamespace);

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

    public function registerFiltersMapping(array $mappings)
    {
        foreach($mappings as $signature => $filters)
        {
            if(is_array($filters))
            {
                foreach($filters as $filter)
                {
                    Filters_map::register($filter);
                    Filters_map::setFilter($filter);
                }    
            }
            else
            {
                Filters_map::register($filters);
                Filters_map::setFilter($filters);
            }
        }

        return $this;
    }

    public function registerLogicsMapping(array $mappings)
    {
        foreach($mappings as $signature => $logics)
        {
            if(is_array($logics))
            {
                foreach($logics as $logic)
                {
                    LogicsAddMap::register($logic);
                    LogicsAddMap::setLogic($logic);
                }
            }
            else
            {
                LogicsAddMap::register($logics);
                LogicsAddMap::setLogic($logics);
            }
        }

        return $this;
    }

    public function getRulesMappingDirectory($rulesDir = null, $exceptionsDir = null)
    {
        return $this->tryMappingDirectory($rulesDir, $exceptionsDir)['rules_exceptions'];
    }

    public function getFiltersMappingDirectory($filtersDir = null)
    {
        return $this->tryMappingDirectory(null, null, $filtersDir)['filters'];
    }

    public function getLogicsMappingDirectory($logicsDir = null)
    {
        return $this->tryMappingDirectory(null, null, null, $logicsDir)['logics'];
    }

    public function registerValidationsMapping(array $mappings)
    {
        foreach($mappings as $signature => $validations)
        {
            if(is_array($validations))
            {
                foreach($validations as $validation)
                {
                    static::$validationsCollection[$signature][$validation] = $validation;
                }
            }
            else
            {
                static::$validationsCollection[$signature][$validations] = $validations;
            }
        }
        
        return $this;
    }

    public function registerClientSideMapping(array $mappings)
    {
        foreach($mappings as $signature => $clientSides)
        {
            if(is_array($clientSides))
            {
                foreach($clientSides as $clientSide)
                {
                    static::$clientSideCollection[$signature] = $clientSide;
                }
            }
            else
            {
                static::$clientSideCollection[$signature] = $clientSides;
            }
        }
        
        return $this;
    }

    public function getValidationsMappingDirectory($validationsDir = null)
    {
        return $this->tryMappingDirectory(null, null, null, null, $validationsDir)['validations'];
    }

    public function getClientSideMappingDirectory($clientSideDir = null)
    {
        return $this->tryMappingDirectory(null, null, null, null, null, $clientSideDir)['clientSide'];
    }

    public function tryMappingDirectory($rulesDir = null, $exceptionsDir = null, $filtersDir = null, $logicsDir = null, $validationsDir = null, $clientSideDir = null)
    {
        $relateMappping = [];
        $mappings = [];

        $filtersMapping = [];
        $logicsMapping = [];
        $validationsMapping = [];
        $clientSideMapping = [];

        try
        {
            $dir      = $this->getDefault('baseDir');

            $dir    = rtrim($dir, DIRECTORY_SEPARATOR);

            $baseDir  = dirname($dir);

            $composerJsonPath = $baseDir.DIRECTORY_SEPARATOR . 'composer.json';
            $composerConfig = json_decode(file_get_contents($composerJsonPath));

            $psr4Config = (array) $composerConfig->autoload->{'psr-4'};
            $baseNamespace = key($psr4Config);

            $rulesDir = static::processDefaults(static::$defaultRulesDir, DIRECTORY_SEPARATOR, $rulesDir);

            $exceptionsDir = static::processDefaults(static::$defaultExceptionDir, DIRECTORY_SEPARATOR, $exceptionsDir);

            $filtersDir     = static::processDefaults(static::$defaultFiltersDir, DIRECTORY_SEPARATOR, $filtersDir);

            $logicsDir     = static::processDefaults(static::$defaultLogicsDir, DIRECTORY_SEPARATOR, $logicsDir);

            $validationsDir     = static::processDefaults(static::$defaultValidationsDir, DIRECTORY_SEPARATOR, $validationsDir);

            $clientSideDir     = static::processDefaults(static::$defaultClientSideDir, DIRECTORY_SEPARATOR, $clientSideDir);

            $allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
            $phpFiles = new \RegexIterator($allFiles, '/\.php$/');

            $exceptionsDirClean = str_replace(['/', '\\'], '', $exceptionsDir);
            $rulesDirClean = str_replace(['/', '\\'], '', $rulesDir);
            $filtersDirClean = str_replace(['/', '\\'], '', $filtersDir);
            $logicsDirClean = str_replace(['/', '\\'], '', $logicsDir);
            $validationsDirClean = str_replace(['/', '\\'], '', $validationsDir);
            $clientSideDirClean = str_replace(['/', '\\'], '', $clientSideDir);

            foreach ($phpFiles as $phpFile) 
            {
                $parentInfo = $phpFile->getPathInfo();
                $parentRealPath = $parentInfo->getRealPath();
                $segments = explode(DIRECTORY_SEPARATOR, $parentRealPath);
                $lastSegment = end($segments);

                $classExists = null;

                if(
                    $lastSegment == $rulesDirClean
                    || $lastSegment == $exceptionsDirClean
                )
                {
                    $qualifiedClass = substr($phpFile->getFileName(), 0, -4);

                    $qualifiedRuleClass = $baseNamespace.$rulesDirClean.'\\'.$qualifiedClass;

                    $qualifiedExceptionClass = $baseNamespace.$exceptionsDirClean.'\\'.$qualifiedClass;

                    $qualifiedRuleExceptionClass = $baseNamespace.$rulesDirClean.'\\'.$exceptionsDirClean.'\\'.$qualifiedClass;

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

                    if(!empty($classExists))
                    {
                        $signature   = Rules_map::createRuleSignature($qualifiedClass);

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
                else if($lastSegment == $filtersDirClean)
                {
                    $filtersMapping = $this->processClassMapping($phpFile, $baseNamespace, $filtersDirClean, Filters_map::class.'::createLogicSignature', Filter_interface::class, $filtersMapping);
                }
                else if($lastSegment == $logicsDirClean)
                {
                    $logicsMapping = $this->processClassMapping($phpFile, $baseNamespace, $logicsDirClean, LogicsAddMap::class.'::createLogicSignature', Logic_interface::class, $logicsMapping);
                }
                else if($lastSegment == $validationsDirClean)
                {
                    $validationsMapping = $this->processClassMapping($phpFile, $baseNamespace, $validationsDirClean, [$this, 'createSignature'], Validation_interface::class, $validationsMapping);
                }
                else if($lastSegment == $clientSideDirClean)
                {
                    $clientSideMapping = $this->processClassMapping($phpFile, $baseNamespace, $clientSideDirClean, [$this, 'createClientSideSignature'], ClientSideInterface::class, $clientSideMapping);
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

        return [
            'rules_exceptions' => $relateMappping,
            'filters' => $filtersMapping,
            'logics' => $logicsMapping,
            'validations' => $validationsMapping,
            'clientSide' => $clientSideMapping
        ];

    }

    protected function processClassMapping($phpFile, $baseNamespace, $dirClean, $signatureObjectMethod, $interfaceCheck, array $arrayMapping)
    {
        $qualifiedClass = substr($phpFile->getFileName(), 0, -4);

        $qualifiedRealClass = $baseNamespace.$dirClean.'\\'.$qualifiedClass;

        if(class_exists($qualifiedRealClass))
        {
            $classExists = $qualifiedRealClass;
        }
        else
        {
            require $phpFile->getRealPath();

            if(class_exists($qualifiedRealClass))
            {
                $classExists = $qualifiedRealClass;
            }
        }

        if(!empty($classExists))
        {
            $signature = null;

            if(is_callable($signatureObjectMethod))
            {
                $signature = call_user_func_array($signatureObjectMethod, [$qualifiedClass]);
            }

            $reflection = new \ReflectionClass($classExists);

            $interfaces  = array_keys($reflection->getInterfaces());

            if(in_array($interfaceCheck, $interfaces, true))
            {
                 $arrayMapping[$signature][$classExists] = $classExists;
            }
        }

        return $arrayMapping;
    }

    protected function createSignature($qualifiedClass)
    {
        $class   = explode('\\', $qualifiedClass);
        $class   = end($class);

        $signature = mb_strtolower($class);

        return $signature;
    }

    protected function createClientSideSignature($qualifiedClass)
    {
        $class   = explode('\\', $qualifiedClass);
        $class   = end($class);

        $signature = mb_strtolower($class);
        $signature = str_replace([Client_side::$clientSideSuffix, Client_side::$altClientSideSuffix], '', $signature);

        return $signature;
    }

    public function registerExtension(Extension_interface $extension)
    {
        v::registerExtension($extension);

        return $this;
    }

    public function macro($name, $macro)
    {
        v::macro($name, $macro);

        return $this;
    }

    public function mixin($mixin, $replace = true, ...$args)
    {
        v::mixin($mixin, $replace, ...$args);

        return $this;
    }

    public function addLangDir($lang, $path, $create_write = false)
    {
        v::addLangDir($lang, $path, $create_write);

        return $this;
    }

    public function addLangStubs($stubs)
    {
        v::addLangStubs($stubs);
        
        return $this;
    }

    public function addJSvalidationLibrary($jsValidationLibrary)
    {
        v::addJSvalidationLibrary($jsValidationLibrary);

        return $this;
    }

    abstract public function register();
}