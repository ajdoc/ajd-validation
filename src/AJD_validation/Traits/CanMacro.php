<?php

namespace AJD_validation\Traits;

use AJD_validation\Contracts\CanMacroInterface;
use BadMethodCallException;
use InvalidArgumentException;
use Closure;
use ReflectionClass;
use ReflectionMethod;

trait CanMacro
{
    protected static $currentMacroName;
    protected static $inverse = false;

    /**
     * The registered string macros.
     *
     * @var array
     */
    protected static $macroCollection = [];

    /**
     * Register a custom macro.
     *
     * @param  string  $name
     * @param  object|callable  $macro
     * @return void
     */
    public static function macro($name, $macro)
    {
        static::$currentMacroName = $name;
        static::$macroCollection[$name] = $macro;
    }

    /**
     * Mix another object into the class.
     *
     * @param  object  $mixin
     * @param  bool  $replace
     * @return void
     *
     * @throws \ReflectionException|\InvalidArgumentException
     */
    public static function mixin($mixin, $replace = true, ...$args)
    {
        $object = null; 
        $className = '';

        if(is_string($mixin) && !is_object($mixin))
        {
            $className = $mixin;

            if(class_exists($mixin))
            {
                $reflection = new \ReflectionClass($mixin);

                $interfaces =  array_keys($reflection->getInterfaces());
                

                if(
                    in_array(CanMacroInterface::class, $interfaces, true)
                )
                {
                    $object = new $mixin(...$args);
                }
            }
        }
        else if($mixin instanceof CanMacroInterface)
        {
            $object = $mixin;
        }
        
        if(is_object($mixin))
        {
            $className = $mixin::class;
        }

        if(!$object)
        {
            throw new InvalidArgumentException(
                sprintf(
                    'Class %s is not an accepted macro class.', $className
                )
            );

            return;
        }

        $methods = $object->getMacros();

        if(
            !empty($methods)
            && is_array($methods)
        )
        {
            foreach ($methods as $method) 
            {   
                if(
                    method_exists($object, $method)
                    && 
                    ($replace || ! static::hasMacro($method)) 
                )
                {
                    $value = $object->{$method}();

                    if(is_callable($value))
                    {
                        static::macro($method, $value);
                    }
                }
            }
        }
    }


    /**
     * Remove certain word from macro.
     *
     * @param  string  $name
     * @param  string  $pattern
     * @param  string  $replacement
     * @return string
     */
    protected static function removeWord( $name, $pattern, $replacement = '' )
    {
        return trim( preg_replace( $pattern , $replacement, $name ) );
    }

    /**
     * Checks if macro is registered.
     *
     * @param  string  $name
     * @return bool
     */
    public static function hasMacro($name)
    {
        if( preg_match('/^Not/', $name ) )
        {
            $name   = static::removeWord( $name, '/^Not/' );
        }

        return isset(static::$macroCollection[$name]);
    }

     /**
     * Get macro Collection.
     *
     * @return array
     */
    public static function getMacroCollection()
    {
        return static::$macroCollection;
    }

     /**
     * Get current macro name.
     *
     * @return string
     */
    public static function getCurrentMacroName()
    {
        return static::$currentMacroName;
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public static function __callStatic($method, $parameters)
    {
        return static::processCall($method, $parameters);
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        return static::processCall($method, $parameters);
    }

    /**
     * Returns inverse static property.
     *
     * @return bool
     *
     */
    public static function getInverse()
    {
        return static::$inverse;
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param  string  $method
     * @param  bool   $isStatic
     * @param  array  $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    protected static function processCall($method, $parameters)
    {
        if (! static::hasMacro($method)) 
        {
            throw new BadMethodCallException(
                sprintf(
                    'Method %s::%s does not exist.', static::class, $method
                )
            );
        }

        if( preg_match('/^Not/', $method ) )
        {
            $method   = static::removeWord( $method, '/^Not/' );

            static::$inverse = true;
        }

        $self = new static;

        $macro = static::$macroCollection[$method];

        static::$currentMacroName = $method;

        if ($macro instanceof Closure) 
        {
            $macro = $macro->bindTo($self, static::class);
        }

        $call = call_user_func_array($macro, $parameters);

        static::$inverse = false;

        return $call ?? $self;
    }
}
