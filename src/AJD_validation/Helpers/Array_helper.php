<?php namespace AJD_validation\Helpers;

use AJD_validation\Contracts\Abstract_common;

class Array_helper
{
	public static function accessible($value)
	{
		$check = is_array( $value ) OR $value instanceof ArrayAccess;

		return $check;
	}

	public static function get($array, $key, $default = null)
    {
    	if( !static::accessible( $array ) )
    	{
    		return Abstract_common::invokeClosure($default);
    	}

    	if( IS_NULL( $key ) )
    	{
    		return $array;
    	}

		if( static::exists($array, $key) ) 
		{
			return $array[$key];
		}

		if( strpos($key, '.') === false) 
		{
			return ( isset( $array[$key]  ) ) ? $array[$key]  : Abstract_common::invokeClosure($default);
		}

		foreach (explode('.', $key) as $segment)
		{
			if( static::accessible($array) AND static::exists($array, $segment) ) 
			{
				$array = $array[$segment];
			}
			else
			{
				return Abstract_common::invokeClosure($default);
			}
		}

		return $array;
    }

    public static function set(&$array, $key, $value)
    {
		if( IS_NULL( $key ) ) 
		{
			return $array = $value;
		}

		$keys = explode('.', $key);

		while ( count($keys) > 1 ) 
		{
			$key = array_shift($keys);

			if ( !isset( $array[$key] ) || !is_array( $array[$key] ) ) 
			{
				$array[$key] = [];
			}

			$array = &$array[$key];
		}

		$array[array_shift($keys)] = $value;

		return $array;
    }

	public static function exists($array, $key)
	{
		if( $array instanceof ArrayAccess ) 
		{
		    return $array->offsetExists($key);
		}

		return array_key_exists($key, $array);
	}

	public static function add($array, $key, $value)
	{
		if( is_null( static::get($array, $key) ) ) 
		{
		    static::set($array, $key, $value);
		}

		return $array;
	}

	public static function dot($array, $prepend = '')
    {
        $results = [];

        foreach ($array as $key => $value) 
        {
			if( is_array( $value ) AND !EMPTY( $value ) ) 
			{
				$results = array_merge( $results, static::dot($value, $prepend.$key.'.') );
			} 
			else 
			{
				$results[$prepend.$key] = $value;
			}
        }

        return $results;
    }

    public static function dataSet(&$target, $key, $value, $overwrite = true)
    {
    	$segments = ( is_array( $key ) ) ? $key : explode('.', $key);

    	if( ( $segment = array_shift( $segments ) ) === '*' )
    	{
    		if( !static::accessible( $target ) )
    		{
    			$target = [];
    		}

    		if( $segments )
    		{
    			foreach( $target as &$inner )
    			{
    				static::dataSet($inner, $segments, $value, $overwrite);
    			}
    		}
    		else if( $overwrite )
    		{
    			foreach( $target as &$inner )
    			{
    				$inner = $value;
    			}
    		}
    	}
    	else if( static::accessible( $target ) )
    	{
    		if( $segments )
    		{
    			if( !static::exists( $target, $segment ) )
    			{
    				$target[$segment] = [];
    			}

    			static::dataSet( $target[$segment], $segments, $value, $overwrite );
    		}
    		else if( $overwrite || !static::exists( $target, $segment ) )
    		{
    			$target[$segment] = $value;
    		}
    	}
    	else if( is_object( $target ) )
    	{
    		if( $segment )
    		{
    			if( !ISSET( $target->{ $segment } ) )
    			{
    				$target->{ $segment } = array();
    			}

    			static::dataSet( $target->{$segment}, $segments, $value, $overwrite );
    		}
    		elseif( $overwrite || !ISSET( $target->{$segment} ) ) 
    		{
                $target->{$segment} = $value;
            }
    	}
    	else
    	{
    		$target = [];

    		if( $segments )
    		{
    			static::dataSet( $target[$segment], $segments, $value, $overwrite );
    		}
    		else if( $overwrite )
    		{
    			$target[$segment] = $value;
    		}
    	}

    	return $target;
    }

    public static function dataGet($target, $key, $default = NULL)
    {
    	if( IS_NULL( $key ) ) 
    	{
            return $target;
        }

        $key = ( is_array( $key ) ) ? $key : explode('.', $key);

        while( !is_null( $segment = array_shift( $key ) ) ) 
        {
        	if( $segment === '*' )
        	{
        		if( !is_array($target) )
        		{
        			return Abstract_common::invokeClosure($default);
        		}

        		$result = static::pluck( $target, $key );

        		return ( in_array('*', $key ) ) ? static::collapse($result) : $result;
        	}

        	if( static::accessible( $target ) && static::exists( $target, $segment ) )
        	{
        		$target = $target[$segment];
        	}
        	else if( is_object( $target ) && ISSET( $target->{ $segment } ) )
        	{
        		$target = $target->{ $segment };
        	}
        	else 
        	{
        		return Abstract_common::invokeClosure($defaults);
        	}
        }

        return $target;
    }

    public static function collapse($array)
    {
		$results = [];

		foreach( $array as $values ) 
		{
			if( !is_array( $values) ) 
			{
				continue;
			}

			$results = array_merge($results, $values);
		}

		return $results;
    }

    public static function pluck($array, $value, $key = null)
    {
    	$results = [];

    	list($value, $key) = static::explodePluckParameters($value, $key);

    	foreach ($array as $item)
    	{
    		$itemValue = static::dataGet($item, $value);

    		if( is_null($key) ) 
    		{
    			$results[] = $itemValue;
    		}
    		else
    		{
    			$itemKey = static::dataGet( $item, $key );

    			if( is_object( $itemKey ) AND method_exists($itemKey, '__toString' ) )
    			{
    				$itemKey = ( string ) $itemKey;
    			}

    			$results[$itemKey] = $itemValue;
    		}
    	}

    	return $results;
    }

	protected static function explodePluckParameters($value, $key)
    {
        $value = ( is_string($value) ) ? explode('.', $value) : $value;

        $key = ( is_null($key) || is_array($key) ) ? $key : explode('.', $key);

        return [$value, $key];
    }

 	public static function where($array, callable $callback)
    {
    	return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param  iterable  $array
     * @param  int  $depth
     * @return array
     */
    public static function flatten($array, $depth = INF, $retainKey = false)
    {
        $result = [];

        foreach($array as $k => $item) 
        {   
            if( !is_array($item)) 
            {
                if($retainKey)
                {
                    $result[$k] = $item;    
                }
                else
                {
                    $result[] = $item;
                }
            } 
            else 
            {
                $values = $depth === 1
                    ? ($retainKey) ? $item : array_values($item)
                    : static::flatten($item, $depth - 1);

                foreach ($values as $kv => $value) 
                {
                    if($retainKey)
                    {
                        $result[$kv] = $value;
                    }
                    else
                    {
                        $result[] = $value;   
                    }
                }
            }
        }

        return $result;
    }

    public static function isAssoc(array $array)
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }
}