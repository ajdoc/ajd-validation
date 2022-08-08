<?php 

namespace AJD_validation\Helpers;

use AJD_validation\Helpers\Exporter;

class VarExport
{
	public const ADD_RETURN = 1 << 0;

    
    public const ADD_TYPE_HINTS = 1 << 1;

    
    public const SKIP_DYNAMIC_PROPERTIES = 1 << 2;

    public const NO_SET_STATE = 1 << 3;

    public const NO_SERIALIZE = 1 << 4;

    public const NOT_ANY_OBJECT = 1 << 5;

    public const NO_CLOSURES = 1 << 6;

    public const INLINE_SCALAR_LIST = 1 << 7;

    public const INLINE_NUMERIC_SCALAR_ARRAY = self::INLINE_SCALAR_LIST;

    public const CLOSURE_SNAPSHOT_USES = 1 << 8;

    public const TRAILING_COMMA_IN_ARRAY = 1 << 9;

    public const NO_ENUMS = 1 << 10;

    public const INLINE_ARRAY = 1 << 11;

    public const FORCED_SHOW_ARRAY_KEY = 1 << 12;

    /**
     * @param mixed $var       The variable to export.
     * @param int   $options   A bitmask of options. Possible values are `VarExporter::*` constants.
     *                         Combine multiple options with a bitwise OR `|` operator.
     * @param int $indentLevel The base output indentation level.
     *
     * @return string
     *
     * @throws ExportException
     */
    public static function export($var, int $options = 0, int $indentLevel = 0)
    {
        $exporter = new Exporter($options, $indentLevel);
        $lines = $exporter->export($var, [], []);

        if ($indentLevel < 1 || count($lines) < 2) 
        {
            $export = implode(PHP_EOL, $lines);
        } 
        else 
        {
            $firstLine = array_shift($lines);
            $lines = array_map(function ($line) use ($indentLevel) 
            {
                return str_repeat('    ', $indentLevel) . $line;
            }, $lines);

            $export = $firstLine . PHP_EOL . implode(PHP_EOL, $lines);
        }

        if ($options & self::ADD_RETURN) 
        {
            return 'return ' . $export . ';' . PHP_EOL;
        }

        return $export;
    }
}