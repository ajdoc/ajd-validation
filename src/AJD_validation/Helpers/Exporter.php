<?php 

namespace AJD_validation\Helpers;

use AJD_validation\Helpers\VarExport;

class Exporter
{
	public $inlineArray;

	public $trailingCommaInArray;

	public $inlineScalarList;

	public $forcedShowArrayKey;

	public $indentLevel;

	public function __construct(int $options, int $indentLevel = 0)
	{
		$this->inlineArray              = (bool) ($options & VarExport::INLINE_ARRAY);
	    $this->inlineScalarList         = (bool) ($options & VarExport::INLINE_SCALAR_LIST);
	    
	    $this->trailingCommaInArray     = (bool) ($options & VarExport::TRAILING_COMMA_IN_ARRAY);

	    $this->forcedShowArrayKey     = (bool) ($options & VarExport::FORCED_SHOW_ARRAY_KEY);

	    $this->indentLevel = $indentLevel;
	}

    public function export($var, array $path, array $parentIds)
    {
        switch ($type = gettype($var)) 
        {
            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
                return [var_export($var, true)];

            case 'NULL':
                // lowercase null
                return ['null'];

            case 'array':
                /** @var array $var */
                return $this->exportArray($var, $path, $parentIds);

            

            default:
                // resources
                throw new \Exception(sprintf('Type "%s" is not supported.', $type), $path);
    	}
    }

   	public function exportArray(array $array, array $path, array $parentIds)
    {
        if (! $array) 
        {
            return ['[]'];
        }

        $result = [];

        $count = count($array);

        if($this->forcedShowArrayKey)
        {
        	$isList = false;
        }
        else
        {
        	$isList = array_keys($array) === range(0, $count - 1);	
        }
        

        $current = 0;

        $inline = $this->inlineArray || ($this->inlineScalarList && $isList && $this->isScalarList($array));

        foreach ($array as $key => $value) 
        {
            $isLast = (++$current === $count);

            $newPath = $path;
            $newPath[] = (string) $key;

            $exported = $this->export($value, $newPath, $parentIds);

            if ($inline) 
            {
                if ($isList) 
                {
                    $result[] = $exported[0];
                } 
                else 
                {
                    $result[] = var_export($key, true) . ' => ' . $exported[0];
                }
            } 
            else 
            {
                $prepend = '';
                $append = '';

                if (! $isList) 
                {
                    $prepend = var_export($key, true) . ' => ';
                }

                if (! $isLast || $this->trailingCommaInArray) 
                {
                    $append = ',';
                }

                $exported = $this->wrap($exported, $prepend, $append);
                $exported = $this->indent($exported);

                $result = array_merge($result, $exported);
            }
        }

        if ($inline) 
        {
            return ['[' . implode(', ', $result) . ']'];
        }

        array_unshift($result, '[');
        $result[] = ']';

        return $result;
    }

    /**
     * Returns whether the given array only contains scalar values.
     *
     * Types considered scalar here are int, bool, float, string and null.
     * If the array is empty, this method returns true.
     *
     * @param array $array
     *
     * @return bool
     */
    private function isScalarList(array $array)
    {
        foreach ($array as $value) 
        {
            if ($value !== null && ! is_scalar($value)) 
            {
                return false;
            }
        }

        return true;
    }

 	public function indent(array $lines) : array
    {
        foreach ($lines as & $value) 
        {
            if ($value !== '') 
            {
                $value = '    ' . $value;
            }
        }

        return $lines;
    }

    public function wrap(array $lines, string $prepend, string $append)
    {
        $lines[0] = $prepend . $lines[0];
        $lines[count($lines) - 1] .= $append;

        return $lines;
    }
}