<?php namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Rule_interface;
use AJD_validation\AJD_validation;
use AJD_validation\Contracts\Abstract_invokable;
use AJD_validation\Contracts\Abstract_anonymous_rule;
use AJD_validation\Helpers\Errors;

abstract class Abstract_rule extends AJD_validation implements Rule_interface
{
    public $inverseCheck;

    protected $name;

    protected static $anonRuleExceptions = [];

	public function __invoke($value, $satisfier = null, $field = null)
    {
        return $this->run($value, $satisfier, $field);
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

        if(
            $this instanceof Abstract_invokable
            ||
            $this instanceof Abstract_anonymous_rule
        )
        {
            $response = $this( $value );

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
            
                if( file_exists($exceptionPath) AND EMPTY( $search ) )
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
                $ruleObj::getAnonExceptionMessage($currentRule);
                return $currentRule;
            }
        }
    }

    public function getCLientSideFormat( $field, $rule, $jsTypeFormat, $clientMessageOnly = false, $satisfier = null, $error = null, $value = null )
    {
        return [];
    }

    protected function processJsArr( array $js, $field, $rule, $clientMessageOnly = false )
    {
        $newJsFormat = '';
        $newJsArr = [
            'customJS' => ''
        ];

        if( $clientMessageOnly )
        {
            if( ISSET( $js[$field][$rule][$clientMessageOnly] ) )
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
            if( ISSET($js[$field][$rule]['js']) )
            {
                $newJsArr['customJS'] .= $js[$field][$rule]['js'];
                unset($js[$field][$rule]['js']);
            }

            $newJsFormat = implode(' ', $js[$field][$rule]);
        }

        $newJsArr[$field][$rule] = $newJsFormat;

        return $newJsArr;
    }
}