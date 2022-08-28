<?php namespace AJD_validation\Contracts;

use AJD_validation\Helpers\Errors;
use AJD_validation\Config\Config;
use AJD_validation\Vefja\Vefja;

use AJD_validation\Contracts\Exception_interface;

abstract class Abstract_exceptions extends Errors implements Exception_interface
{
	protected $params 	= array();
	protected static $config;

 	const ERR_DEFAULT 	= 1;
    const ERR_NEGATIVE 	= 2;
    const STANDARD 		= 0;

 	public static $defaultMessages = array(
        self::ERR_DEFAULT 	=> array(
            self::STANDARD 	=> 'Data validation failed for :field',
        ),
        self::ERR_NEGATIVE 	=> array(
            self::STANDARD 	=> 'Data validation failed for :field',
        ),
    );

    protected $mode 	      = self::ERR_DEFAULT;
    protected $id             = 'validation';
    protected $name           = '';
    protected static $fromRuleName   = '';

    public static $localizeFile;

    public function __construct()
    {
    	static::$config = Vefja::singleton('AJD_validation\\Config\\Config');
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function guessId($idPass = null)
    {
        if( !EMPTY( $this->id ) AND $this->id != 'validation' )
        {
            return $this->id;
        }

        if(!empty($idPass))
        {
            $className = $idPass;
        }
        else
        {
            $className = get_called_class();
        }

        $pieces                 = explode('\\', $className);
        $exceptionShortName     = end($pieces);
        $ruleShortName          = str_replace('Exception', '', $exceptionShortName);

        $ruleName               = lcfirst($ruleShortName);
        
        return $ruleName;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function setMode($mode)
    {
        $this->mode 	= $mode;

        if( $this->mode == self::ERR_NEGATIVE )
        {
            if(!$this->hasParam('inverse'))
            {
                $this->setParam('inverse', true);
            }
        }

        $this->buildMessageErr();

        return $this;
    }

    public function localize()
    {
        $file                       = static::$localizeFile.'.php';
        
        $file_data                  = static::$config->getConfigFile( $file, static::$errDir.static::$lang.DIRECTORY_SEPARATOR );

        $hasLocale = false;

        if( isset(static::$localizeMessage)
            &&
            (
                isset(static::$localizeMessage[static::$lang])
                &&
                !empty(static::$localizeMessage[static::$lang])
            )
        )   
        {
            $hasLocale = true;
            
            static::$defaultMessages = static::$localizeMessage[static::$lang];
        }

        $hasAddedLang = false;

        if(!empty(static::$addLangDir))
        {
            foreach(static::$addLangDir as $lang => $path)
            {
                if(file_exists($path))
                {
                    $customFile = static::$lang.'_lang.php';

                    if(file_exists($path.DIRECTORY_SEPARATOR.$customFile))
                    {
                        $file_data                  = static::$config->getConfigFile( $customFile, $path.DIRECTORY_SEPARATOR );

                        if(!empty($file_data))
                        {
                            $hasAddedLang = true;
                        }
                    }
                }
            }
        }
        
        if($hasAddedLang && !empty($file_data))
        {
            if(!empty($this->id))
            {
                $ruleName = strtolower(str_replace('_rule_exception', '', $this->id));
                

                if(isset($file_data['error_msg'][$ruleName]))
                {
                    static::$defaultMessages = $file_data['error_msg'][$ruleName];
                }
            }
        }
        else
        {
            if( !EMPTY( $file_data ) && !$hasLocale )
            {
                static::$defaultMessages = $file_data;
            }
        }
    }

	public function configure( array $params = array())
    {       
        $idPass = (isset($params['id_pass']) && !empty($params['id_pass'])) ? $params['id_pass'] : null;
        
        $guessId = $this->guessId($idPass);
        $this->setId($guessId);
    	$this->setParams( $params );        
        
    	$this->localize();

    	if( ISSET( $params['inverse'] ) AND !EMPTY( $params['inverse'] ) )
    	{
    		$this->setMode(self::ERR_NEGATIVE);
    	}
    	else
    	{
    		$this->setMode(self::ERR_DEFAULT);	
    	}
    }

    public function setParams( array $params )
    {
    	$this->params 	= $params;
         
    	return $this;
    }

     public function setParam($key, $value)
    {
        $this->params[$key] = $value;

        $this->buildMessageErr();

        return $this;
    }

    public function getExceptionMessage()
    {
    	return $this->buildMessageErr();
    }

    public function hasParam($name)
    {
        return isset($this->params[$name]);
    }

    public function getParam($name)
    {
        return $this->hasParam($name) ? $this->params[$name] : false;
    }

    public function getParams()
    {
        return $this->params;
    }

  	public function chooseMessage()
    {
        return key(static::$defaultMessages[$this->mode]);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name     = $name;
    }

    public static function setFromRuleName($name)
    {
        static::$fromRuleName = $name;
    }

    public static function getFromRuleName()
    {
        return static::$fromRuleName;
    }

    protected function buildMessageErr()
    {
        $messageKey 	= $this->chooseMessage();

        $message_str 	= static::$defaultMessages[$this->mode][$messageKey];

        $message 		= $this->replaceErrorPlaceholder( $this->getParams(), $message_str );

        $append_error   = $this->getParam('append_error');
        
        if( EMPTY( $message ) )
        {
        	$message 	= $message_str;
        }

        if(!empty($append_error))
        {
            $message    .= ' '.$append_error;
        }

        return $message;
    }
}