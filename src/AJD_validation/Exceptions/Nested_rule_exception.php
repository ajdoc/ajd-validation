<?php namespace AJD_validation\Exceptions;

use IteratorAggregate;
use RecursiveIteratorIterator;
use SplObjectStorage;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Exceptions\Recursive_rule_exception;
use AJD_validation\Vefja\Vefja;

class Nested_rule_exception extends Abstract_exceptions implements IteratorAggregate
{
    /**
     * @var SplObjectStorage
     */
    private $exceptions = array();

    /**
     * @param Abstract_exceptions $exception
     *
     * @return self
     */
    public function addRelated(Abstract_exceptions $exception)
    {
        $this->getRelated()->attach($exception);

        return $this;
    }

    /**
     * @param string $path
     * @param Abstract_exceptions $exception
     *
     * @return ValidationException
     */
    private function getExceptionForPath($path, Abstract_exceptions $exception)
    {
        if($path === $exception->guessId()) 
        {
            return $exception;
        }

        if(!$exception instanceof self) 
        {
            return $exception;
        }

        foreach( $exception as $subException ) 
        {
            return $subException;
        }

        return $exception;
    }

    /**
     * @param array $paths
     *
     * @return self
     */
    public function findMessages(array $paths)
    {
        $messages       = array();

        foreach($paths as $key => $value) 
        {
            $numericKey     = is_numeric($key);
            $path           = $numericKey ? $value : $key;

            if( !( $exception = $this->getRelatedByName($path) ) ) 
            {
                $exception  = $this->findRelated($path);
            }

            $path           = str_replace('.', '_', $path);

            if(!$exception) 
            {
                $messages[$path] = '';
                continue;
            }

            $exception          = $this->getExceptionForPath($path, $exception);

            /*if( !$numericKey ) 
            {
                $exception->setTemplate($value);
            }*/

            $messages[$path]    = $exception->getExceptionMessage();
        }

        return $messages;
    }

    /**
     * @return Exception
     */
    public function findRelated($path)
    {
        $target     = $this;
        $pieces     = explode('.', $path);

        while( !EMPTY( $pieces ) AND $target )  
        {
            $piece  = array_shift($pieces);
            $target = $target->getRelatedByName($piece);
        }

        return $target;
    }

    /**
     * @return RecursiveIteratorIterator
     */
    private function getRecursiveIterator()
    {
        $recursive_rule_exception   = new Recursive_rule_exception($this);

        $exceptionIterator          = $recursive_rule_exception;
        $recursiveIteratorIterator  = new RecursiveIteratorIterator(
            $exceptionIterator,
            RecursiveIteratorIterator::SELF_FIRST
        );

        return $recursiveIteratorIterator;
    }

    /**
     * @return SplObjectStorage
     */
    public function getIterator()
    {
        $childrenExceptions         = new SplObjectStorage();

        $recursiveIteratorIterator  = $this->getRecursiveIterator();
        $exceptionIterator          = $recursiveIteratorIterator->getInnerIterator();

        $lastDepth                  = 0;
        $lastDepthOriginal          = 0;
        $knownDepths                = array();

        foreach( $recursiveIteratorIterator as $childException ) 
        {
            if( $childException instanceof self
                AND $childException->getRelated()->count() > 0
                AND $childException->getRelated()->count() < 2
            ) 
            {
                continue;
            }

            $currentDepth           = $lastDepth;
            $currentDepthOriginal   = $recursiveIteratorIterator->getDepth() + 1;

            if( ISSET( $knownDepths[$currentDepthOriginal] ) ) 
            {
                $currentDepth       = $knownDepths[$currentDepthOriginal];

            } 
            elseif( $currentDepthOriginal > $lastDepthOriginal
                AND ( $exceptionIterator->count() != 1 ) 
            ) 
            {
                ++$currentDepth;
            }

            if( !ISSET( $knownDepths[$currentDepthOriginal] ) ) 
            {
                $knownDepths[$currentDepthOriginal] = $currentDepth;
            }

            $lastDepth          = $currentDepth;
            $lastDepthOriginal  = $currentDepthOriginal;

            $childrenExceptions->attach(
                $childException,
                array(
                    'depth'                     => $currentDepth,
                    'depth_original'            => $currentDepthOriginal,
                    'previous_depth'            => $lastDepth,
                    'previous_depth_original'   => $lastDepthOriginal,
                )
            );
        }

        return $childrenExceptions;
    }

    /**
    * @return array
    */
    public function getMessages()
    {
        $messages       = array($this->getExceptionMessage());

        foreach( $this as $exception ) 
        {
            $messages[] = $exception->getExceptionMessage();
        }

        if( count($messages) > 1 ) 
        {
            array_shift($messages);
        }

        return $messages;
    }

    /**
     * @return string
     */
    public function getFullMessage($callable = NULL)
    {
        $marker     = '-';
        $messages   = array();
        $exceptions = $this->getIterator();

        if ( count($exceptions) != 1 ) 
        {
            $messages[] = sprintf('%s %s', $marker, $this->getExceptionMessage());
        }

        foreach( $exceptions as $exception ) 
        {
            $depth      = $exceptions[$exception]['depth'];
            $prefix     = str_repeat('&nbsp;', $depth * 2);

            $messages[] = sprintf('%s%s %s', $prefix, $marker, $exception->getExceptionMessage());
        }

        if( !EMPTY( $callable ) AND is_callable( $callable ) )
        {
            return call_user_func_array($callable, array($messages));
        }
        else
        {
            return implode('<br/>', $messages);
        }
    }

    /**
     * @return SplObjectStorage
     */
    public function getRelated()
    {
        if( !$this->exceptions instanceof SplObjectStorage ) 
        {
            $this->exceptions = new SplObjectStorage();
        }

        return $this->exceptions;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return self
     */
    public function setParam($name, $value)
    {
        if( 'translator' === $name ) 
        {
            foreach($this->getRelated() as $exception) 
            {
                $exception->setParam($name, $value);
            }
        }

        parent::setParam($name, $value);

        return $this;
    }

    /**
    * @return bool
    */
    private function isRelated($name, Abstract_exceptions $exception)
    {
        return ($exception->getId() === $name OR $exception->getName() === $name);
    }

    /**
    * @return ValidationException
    */
    public function getRelatedByName($name)
    {
        if( $this->isRelated($name, $this) ) 
        {
            return $this;
        }

        foreach($this->getRecursiveIterator() as $exception) 
        {
            if( $this->isRelated($name, $exception) ) 
            {
                return $exception;
            }
        }
    }

    /**
    * @param array $exceptions
    *
    * @return self
    */
    public function setRelated(array $exceptions)
    {
        foreach($exceptions as $exception) 
        {
            $this->addRelated($exception);
        }

        return $this;
    }
}
