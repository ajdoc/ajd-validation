<?php namespace AJD_validation\Filters;

use AJD_validation\Contracts\Abstract_filter;

class Cast_to_filter extends Abstract_filter
{
	private static $builtInTypes = [
		'string' => 1, 'int' => 1, 'float' => 1, 'bool' => 1, 'array' => 1, 'object' => 1,
		'boolean' => 1, 'integer' => 1, 'double' => 1, 'null' => 1,
	];

	public function filter( $value, $satisfier = null, $field = null )
	{
		return static::castTo($value, $satisfier);
	}

	public static function castTo($value, string $type)
	{
		if($type) 
		{
			if(static::isBuiltInType($type))
			{
				settype($value, $type);
			}
			elseif (in_array($type, [\DateTime::class, \DateTimeImmutable::class], true)) 
			{
				$value = new ($type)($value);
			}
			else 
			{
				if(class_exists($type))
				{
					$value = static::toObject($value, new $type);
				}
			}
		}

		return $value;
	}

	public static function isBuiltInType(string $type)
	{
		return isset( static::$builtInTypes[strtolower($type)] );
	}

	public static function toObject(iterable $array, object $object)
	{
		foreach ($array as $k => $v) 
		{
			$object->$k = $v;
		}

		return $object;
	}
}