<?php namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Rule_interface;
use AJD_validation\AJD_validation;
use AJD_validation\Contracts\{
    Abstract_invokable, Abstract_anonymous_rule, Abstract_exceptions
};

use AJD_validation\Helpers\Errors;
use AJD_validation\Formatter\{
    FormatterInterface, AbstractFormatter
};

use Closure;
use ReflectionClass;

abstract class Abstract_rule extends AJD_validation implements Rule_interface
{
    public $inverseCheck;

    protected $name;
    protected $whenInstance;
    protected $customErrorMessage  = [
        'appendError' => '',
        'overrideError' => '',
        'formatter' => null,
        'formatterOptions' => []
    ];

    protected static $anonRuleExceptions = [];
    protected static $ruleArguments = [];


    public function __invoke($value, $satisfier = null, $field = null)
    {
        return $this->run($value, $satisfier, $field);
    }

    public function setCustomErrorMessage(array $customErrorMessage)
    {
        $this->customErrorMessage['appendError'] = $customErrorMessage['appendError'] ?? '';
        $this->customErrorMessage['overrideError'] = $customErrorMessage['overrideError'] ?? '';
        $this->customErrorMessage['formatterOptions'] = $customErrorMessage['formatterOptions'] ?? [];

        if(isset($customErrorMessage['formatter']) && !empty($customErrorMessage['formatter']))
        {
            $formatter = $customErrorMessage['formatter'];

            if(is_string($formatter))
            {
                $reflectFormatter = new ReflectionClass($formatter);

                $interfaces  = array_keys($reflectFormatter->getInterfaces());

                if(in_array(FormatterInterface::class, $interfaces, true))
                {
                    $this->customErrorMessage['formatter'] = new $formatter;
                }
            }
            else if($formatter instanceof Closure)
            {
                $this->customErrorMessage['formatter'] = $this->createFormatter($formatter);
            }
            else if($formatter instanceof FormatterInterface)
            {
                $this->customErrorMessage['formatter'] = $formatter;   
            }

            $this->customErrorMessage['formatter']->setOptions($this->customErrorMessage['formatterOptions']);
        }

        return $this->getReturn();
    }

    public function setFormatter($formatter, array $formatterOptions = [])
    {
        return $this->setCustomErrorMessage([
            'formatter' => $formatter,
            'formatterOptions' => $formatterOptions
        ]);
    }

    public function createFormatter(Closure $formatter)
    {
        $anonClassFormatter = new class($formatter) extends AbstractFormatter
        {
            protected $formatter;

            public function __construct($formatter)
            {
                $this->formatter = $formatter;
            }

            public function format(string $messages, Abstract_exceptions $exception, $field = null, $satisfier = null, $value = null)
            {
                if(empty($this->formatter))
                {
                    return null;
                }

                $closure = $this->formatter->bindTo($this, self::class);

                return \call_user_func_array($closure, func_get_args());
            }
        };

        return $anonClassFormatter;
    }

    public function getCustomErrorMessage()
    {
        return $this->customErrorMessage;
    }

    public function setWhenInstance($whenInstance)
    {
        $this->whenInstance = $whenInstance;
    }

    public function getReturn()
    {
        if(!empty($this->whenInstance))
        {
            return $this->whenInstance;
        }

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getExceptionError($value, array $extraParams = [], $rule = null, $overrideName = false, $ruleObj = null, $inverse = false)
    {
        $currentClass = $this;
        $currentObj = $this;
        
        if( !EMPTY( $rule ) )
        {
            $currentClass = $rule;
            $currentObj = $rule;
        }

        $exception = $this->createException($rule, $ruleObj);
        $name = $this->name ?: Errors::stringify($value);

        $params = array_merge(
            get_class_vars( get_class($currentClass) ),
            get_object_vars($currentObj),
            $extraParams,
            compact('value')
        );

        if( $overrideName )
        {
            $params['field'] = $name;
        }

        if($inverse)
        {
            $params['inverse'] = true;
        }

        $exception->configure($params);

        $exception->setName($name);

        if($ruleObj instanceof Abstract_invokable)
        {
            $ruleObj->setException($exception);
            $ruleObj($value);
        }

        return $exception;
    }

    public function assertErr( $value, $override = false, $inverseCheck = null )
    {
        if(!is_null($inverseCheck))
        {
            $this->inverseCheck = $inverseCheck;
        }

        $response = null;
        $args = [];

        if(
            $this instanceof Abstract_invokable
            ||
            $this instanceof Abstract_anonymous_rule
        )
        {
            $args = static::$ruleArguments[\spl_object_id($this)] ?? [];

            $response = $this( $value, $args, $this->name );

            if($this->inverseCheck)
            {
                if( !$response )
                {
                    return true;
                }
            }
            else
            {
                if( $response )
                {
                    return true;
                }
            }
        }
        else
        {
            if(property_exists($this, 'makeValidateReturnArr'))
            {
                $this->makeValidateReturnArr = true;
            }

            $response = $this->validate( $value );

            $checkResponse = $response;

            if(is_array($checkResponse))
            {
                $checkResponse = (isset($checkResponse['check'])) ? $checkResponse['check'] : false;
            }

            if($this->inverseCheck)
            {
                if( !$checkResponse )
                {
                    return true;
                }
            }
            else
            {
                if( $checkResponse )
                {
                    return true;
                }
            }
        }

        $extraParams = [];

        if($this->inverseCheck)
        {
            $extraParams = ['inverse' => true];
        }

        if(is_array($response))
        {
            if(isset($response['append_error']))
            {
                $extraParams['append_error'] = $response['append_error'];
            }
        }

        $exceptions = $this->getExceptionError( $value, $extraParams, null, $override, $this );

        if($this->inverseCheck)
        {
            $exceptions->setMode(Abstract_exceptions::ERR_NEGATIVE);
        }
        
        throw $exceptions;
    }

    protected function createException($rule = null, $ruleObj = null)
    {
        $err = static::get_errors_instance();
        $ruleStr = null;

        if( !EMPTY( $rule ) )
        {
            $currentRule = get_class( $rule );
            $ruleStr = get_class( $rule );
        }
        else
        {
            $currentRule = get_called_class();
            $ruleStr = get_called_class();
        }

        $currentRule = str_replace('\\Rules\\', '\\Exceptions\\', $currentRule);
        $currentRule .= '_exception';

        if($ruleObj instanceof Abstract_invokable)
        {
            $currentRule = 'AJD_validation\\Exceptions\\Common_invokable_rule_exception';
        }
        else if($ruleObj instanceof Abstract_anonymous_rule)
        {
            if(isset( static::$anonRuleExceptions[spl_object_id($ruleObj)] ))
            {
                $currentRule = static::$anonRuleExceptions[spl_object_id($ruleObj)];
            }
        }
        
        if( !EMPTY( Errors::getExceptionDirectory() ) )
        {
            foreach( Errors::getExceptionDirectory() as $key => $directory )
            {
                $namespace = '';
                $addExceptionNamespace = Errors::getExceptionNamespace();

                if( ISSET( $addExceptionNamespace[ $key ] ) )
                {
                    $namespace = $addExceptionNamespace[ $key ];
                }

                $exceptionPath = $directory.$ruleStr.'_exception.php';
                $requiredFiles = get_required_files();

                $search = array_search($exceptionPath, $requiredFiles);
            
                if( file_exists($exceptionPath) && EMPTY( $search ) )
                {
                    $currentRule = $namespace.$ruleStr.'_exception';
                    $check = require $exceptionPath;
                }
            }
        }

        if(class_exists($currentRule))
        {
            return new $currentRule();
        }
        else if($ruleObj instanceof Abstract_anonymous_rule)
        {
            if($currentRule)
            {
                $currentRule::setFromRuleName($ruleObj->getAnonName());
                $ruleObj::getAnonExceptionMessage($currentRule, $ruleObj);
                return $currentRule;
            }
        }
    }

    public function getCLientSideFormat( $field, $rule, $jsTypeFormat, $clientMessageOnly = false, $satisfier = null, $error = null, $value = null )
    {
        return [];
    }

    public function processJsArr( array $js, $field, $rule, $clientMessageOnly = false )
    {
        $newJsFormat = '';
        $newJsArr = [
            'customJS' => '',
            'clientSideJson' => [],
            'clientSideJsonMessages' => []
        ];

        if( $clientMessageOnly )
        {
            if( isset( $js[$field][$rule][$clientMessageOnly] ) )
            {
                $newJsFormat = $js[$field][$rule][$clientMessageOnly];
            }
            else
            {
                $newJsFormat = $js[$field][$rule]['message'];
            }
        }
        else
        {
            if( isset($js['clientSideJson'][$field][$rule]) )
            {
                $newJsArr['clientSideJson'] = array_merge($newJsArr['clientSideJson'], $js['clientSideJson']);

                unset($js['clientSideJson'][$field][$rule]);
            }

            if( isset($js['clientSideJsonMessages'][$field][$rule]) )
            {
                $newJsArr['clientSideJsonMessages'] = array_merge($newJsArr['clientSideJsonMessages'], $js['clientSideJsonMessages']);
                unset($js['clientSideJsonMessages'][$field][$rule]);
            }

            if( isset($js[$field][$rule]['js']) )
            {
                $newJsArr['customJS'] .= $js[$field][$rule]['js'];
                unset($js[$field][$rule]['js']);
            }

            if(isset($js[$field][$rule]))
            {
                $newJsFormat = implode(' ', $js[$field][$rule]);
            }
        }

        if(!empty($newJsFormat))
        {
            $newJsArr[$field][$rule] = $newJsFormat;
        }
        
        return $newJsArr;
    }
}