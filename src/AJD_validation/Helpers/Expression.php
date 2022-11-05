<?php

namespace AJD_validation\Helpers;

use Closure;
use Exception;

if (!function_exists('str_contains')) 
{
    function str_contains($haystack, $needle) 
    {
        return $needle !== '' && \mb_strpos($haystack, $needle) !== false;
    }
}

if (!function_exists('str_starts_with')) 
{
    function str_starts_with($haystack, $needle) 
    {
        return (string)$needle !== '' && \strncmp($haystack, $needle, \strlen($needle)) === 0;
    }
}

if (!function_exists('str_ends_with')) 
{
    function str_ends_with($haystack, $needle) 
    {
        return $needle !== '' && \substr($haystack, -\strlen($needle)) === (string)$needle;
    }
}

class Expression
{
    const backtickToDoubleQuotes = [
        '"' => '\\"', // escape all double quotes
        "\\`" => "[%{BACKTICK}%]", // to be unescaped
        "`" => '"', // for surrounding
        "[%{BACKTICK}%]" => "`", // restore backticks
    ];

    const singleToDoubleQuotes = [
        '"' => '\\"', // escape all double quotes
        "\\'" => "[%{SINGLE_QUOTE}%]", // to be unescaped
        "'" => '"', // for surrounding
        "[%{SINGLE_QUOTE}%]" => "'", // restore single quotes
    ];

    protected $vars = [];
    protected $fromName = '_expression';

    public function __construct(array $vars = []) 
    {
        if(!empty($vars) && !$this->isAssoc($vars))
        {
            throw new \InvalidArgumentException('Argument must be an associative array.');
        }

        $this->vars = $vars;

        $this->registerDefaultFunction();
    }

    public function isAssoc(array $array)
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }

    protected function registerDefaultFunction()
    {
        $that = $this;

        $this->vars['empty'] = function($string)
        {
            return empty($string);
        };

        $this->vars['isset'] = function($string)
        {
            return isset($string);
        };

        $this->vars['is_null'] = function($string)
        {
            return is_null($string);
        };

        $this->vars['constant'] = function($constant)
        {
            return constant($constant);
        };

        $this->vars['in'] = function($needle, array $haystack, $inclusive = false)
        {
            return in_array($needle, $haystack, $inclusive);
        };

        $this->vars['contains'] = function($haystack, $needle)
        {
            return str_contains($haystack, $needle);
        };

        $this->vars['starts_with'] = function($haystack, $needle)
        {
            return str_starts_with($haystack, $needle);
        };

        $this->vars['ends_with'] = function($haystack, $needle)
        {
            return str_ends_with($haystack, $needle);
        };

        $this->vars['when'] = function($evaluator, $then = null, $otherwise = null) use ($that)
        {
            return $that->when($evaluator, $then, $otherwise);
        };
    }

    protected function when($evaluator, $then = null, $otherwise = null)
    {
        if($evaluator)
        {
            if(is_null($then))
            {
                return $evaluator;
            }

            if(is_callable($then))
            {
                return call_user_func($then);
            }

            return $then;
        }

        if(is_null($otherwise))
        {
            return $evaluator;
        }
        
        if(is_callable($otherwise))
        {
            return call_user_func($otherwise);
        }

        return $otherwise;
    }

    protected function evalauteSingleOrMultiExpression(string $expression, array $vars = [])
    {
        $expressions = [];
        $currentExpression = 0;
        $results = [];
        $inQuotes = false;
        $quotes = ["\"", "'", "`"];
        $expressionLen = strlen($expression);

        for ($i = 0; $i < $expressionLen; $i++) 
        {
            $char = $expression[$i];

            if(in_array($char, $quotes)) 
            {
                if($inQuotes === $char) 
                {
                    $inQuotes = false;
                } 
                else 
                {
                    $inQuotes = $char;
                }
            }

            if(!$inQuotes && $char == ';') 
            {
                $currentExpression++;
                continue;
            }

            if(!isset($expressions[$currentExpression])) 
            {
                $expressions[$currentExpression] = '';
            }

            $expressions[$currentExpression] .= $char;
        }

        $countExpressions = count($expressions);

        if($countExpressions == 1)
        {
            return $this->runEvaluate($expression, $vars);
        }

        $cacheVars = $vars;
        
        $result = null;
        
        foreach ($expressions as $key => $expression) 
        {
            $checkExpression = preg_replace('/\s*/m', '', $expression);
            
            if(empty($checkExpression))
            {
                continue;
            }

            $result = $this->runEvaluate($expression, $cacheVars);
            
            $realKey = $key + 1;
            $cacheVars[$this->fromName.'_'.$realKey] = $result;
        }

        return $result;
    }

    protected function runEvaluate(string $expression, array $vars = [])
    {
        if (empty($vars)) 
        {
            return $this->eval($expression);
        }

        $instance = clone $this;

        $instance->vars = array_merge($instance->vars, $vars);
        $result = $instance->eval($expression);
        unset($instance);

        return $result;
    }

    public function evaluate(string $expression, array $vars = [])
    {
        return $this->evalauteSingleOrMultiExpression($expression, $vars);
    }

    protected function inputTernary($input, $contains)
    {
        $ternary = $this->parse($input, $contains);
        $cntTernary = count($ternary);

        if($cntTernary > 2)
        {
            throw new Exception('unexpected `?`');
        }

        if($cntTernary === 2) 
        {
            $values = $this->parse($ternary[1], ':');

            if(count($values) > 2)
            {
                throw new Exception('unexpected `:`');
            }

            return $this->eval($ternary[0]) ? $this->eval($values[0]) : $this->eval($values[1] ?? '');
        }
    }

    protected function inputNullCoalesce($input, $contains)
    {
        $coalesce = $this->parse($input, $contains);
        $cntCoalesce = count($coalesce);

        if($cntCoalesce < 2)
        {
            throw new Exception('unexpected `??`');
        }

        $that = $this;

        $result = array_reduce($coalesce, function($carry, $item) use ($that)
        {
            $carry =  $carry ?? $that->eval($item);
            
            return $carry;
            
        }, null);

        return $result;
    }

    protected function inputShortTernary($input, $contains)
    {
        $ternary = $this->parse($input, $contains);
        $cntTernary = count($ternary);

        if($cntTernary > 1) 
        {
            foreach($ternary as $index => $part) 
            {
                $part = $this->eval($part);

                if ($part || $index === $cntTernary - 1) 
                {
                    return $part;
                }
            }
        }
    }

    protected function processForwarding($input, $contains)
    {
        $parts = $this->parse($input, $contains);
        $result = null;
        foreach($parts as $part)
        {
            if(str_contains($part, '$$'))
            {
                if($result === false)
                {
                    $result = 'false';
                }
                else if($result === true)
                {
                    $result = 'true';
                }
                else if($result === null)
                {
                    $result = 'null';
                }
                else
                {
                    $result = "'".$result."'";
                }

                $part = str_replace('$$', $result, $part);
            }
            
            $result = $this->eval($part);
        }

        return $result;
    }

    protected function processCallable($input, $contains)
    {
        $parts = $this->parse($input, '(', false);

        // parse the input delimiting by opening bracket
        // parameter string is in last part, without trailing and leading brackets
        $parts = $this->parse($input, '(', false);
        $params = substr(array_pop($parts), 1, -1);
        $before = implode('', $parts);

        $callable = $this->eval($before);

        if(!is_callable($callable)) 
        {
            throw new Exception("`$before` is not callable");
        }

        $params = $this->evaluateList($params);

        return call_user_func_array($callable, $params);
    }

    protected function inputLogical($input, $contains)
    {
        $logic = $this->parse($input, $contains);
        $countLogic = count($logic);

        switch($contains)
        {
            case '&&':
                $defaultReturn = true;
            break;

            case 'xor':
                $defaultReturn = false;
            break;

            case '||':
                $defaultReturn = false;
            break;
        }

        if($countLogic > 1) 
        {
            $xorCheck = [];

            foreach($logic as $item) 
            {
                switch($contains)
                {
                    case '&&':
                        if(!$this->eval($item)) 
                        {
                            return false;
                        }
                    break;

                    case 'xor':
                        $xorCheck[] = $this->eval($item);
                    break;

                    case '||':
                        if($this->eval($item)) 
                        {
                            return true;
                        }
                    break;
                }
            }

            if(!empty($xorCheck))
            {
                $resultCache = array_reduce($xorCheck, function($carry, $item)
                {
                    $carry = ($carry xor $item);

                    return $carry;
                }, false);

                return $resultCache;
            }

            return $defaultReturn;
        }
    }

    protected function inputComparison($input, $contains)
    {
        $cmp = $this->parse($input, $contains);
        $cntCmp = count($cmp);

        $defaultReturn = true;

        if($cntCmp > 1) 
        {
            $first = $this->eval(array_shift($cmp));

            foreach ($cmp as $item)
            {
                $evalItem = $this->eval($item);

                switch($contains)
                {
                    case '===':
                        if($evalItem !== $first) 
                        {
                            return false;
                        }
                    break;

                    case '!==':
                        if($evalItem === $first) 
                        {
                            return false;
                        }
                    break;

                    case '==':
                        if($evalItem != $first) 
                        {
                            return false;
                        }
                    break;

                    case '!=':
                        if($evalItem == $first) 
                        {
                            return false;
                        }
                    break;

                    case '<=>':
                        return $first <=> $evalItem;
                    break;

                    case '>=':
                        if($evalItem > $first) 
                        {
                            return false;
                        }
                    break;

                    case '<=':
                        if($evalItem < $first) 
                        {
                            return false;
                        }
                    break;

                    case '>':
                        if($evalItem >= $first) 
                        {
                            return false;
                        }
                    break;

                    case '<':
                        if($evalItem <= $first) 
                        {
                            return false;
                        }
                    break;
                }
            }

            return $defaultReturn;
        }
    }

    protected function inputArithmeticAndConcat($input, $contains)
    {
        $result = $this->parse($input, $contains);
        $cntResult = count($result);
        $that = $this;
        $thirdArg = '';

        $passResult = $result;

        switch($contains)
        {
            case '~':
                $thirdArg = '';
            break;
            case '+':
                $thirdArg = 0;
            break;
            case '*':
                $thirdArg = 1;
            break;
        }

        if($cntResult > 1)
        {
            if($contains === '-' || $contains === '/')
            {
                $thirdArg = $this->eval(array_shift($result));
            }

            if($contains === '%')
            {
                $last = $this->eval(array_pop($result));

                return $this->eval(implode('%', $result)) % $last;
            }
            else if($contains === '**')
            {
                return $this->eval(array_shift($result)) ** $this->eval(implode('**', $result));
            }
            else
            {
                return array_reduce($result, function($carry, $item) use ($that, $contains)
                {
                    switch($contains)
                    {
                        case '~':
                            return $carry . $that->eval($item);    
                        break;

                        case '+':
                            return $carry + $that->eval($item);
                        break;

                        case '-':
                            return $carry - $that->eval($item);
                        break;

                        case '*':
                            return $carry * $that->eval($item);
                        break;

                        case '/':
                            return $carry / $that->eval($item);
                        break;
                    }
                    
                }, $thirdArg);
            }
        }
    }

    protected function inputBackTickSingleQuote($input, $contains)
    {
        if(!str_ends_with($input, $contains))
        {
            throw new Exception('unexpected end of string');
        }

        if($contains == "`")
        {
            $rules = static::backtickToDoubleQuotes;
        }
        else if($contains == "'")
        {
            $rules = static::singleToDoubleQuotes;
        }

        $input = str_replace(array_keys($rules), array_values($rules), $input);

        return $input;
    }

    protected function inputContainment($input, $contains)
    {
        if($contains === '(')
        {
            $endContain = ')';
        }
        else if($contains === '[')
        {
            $endContain = ']';   
        }
        else if($contains === '{')
        {
            $endContain = '}';
        }
        
        if(!str_ends_with($input, $endContain)) 
        {
            throw new Exception("expected closing `".$endContain."`");
        }

        if($contains === '(')
        {
            return $this->eval(substr($input, 1, -1));
        }
        else if($contains === '[' || $contains === '{')
        {
            $input = substr($input, 1, -1);

            if($contains === '[')
            {
                return $this->evaluateList($input, true);
            }

            return (object) $this->evaluateList($input); // keys are not evaluated
        }
    }

    protected function eval(string $input)
    {
        $input = trim($input);

        if($input === '') 
        {
            return null;
        }

        if(is_numeric($input)) 
        {
            return $input + 0;
        }

        $lowered = strtolower($input);

        if($lowered === 'null') 
        {
            return null;
        }

        if($lowered === 'true') 
        {
            return true;
        }

        if($lowered === 'false') 
        {
            return false;
        }

        // all the str_contains()s are for performance reasons
        // even though they might give false positives (e.g. operators in strings)

        // short ternary expression
        if(str_contains($input, '?:'))
        {
            $part = $this->processInput($input, '?:', [$this, 'inputShortTernary']);
            return $part;
        }

        // null coalesce expression
        if(str_contains($input, '??'))
        {
            $nullCoalescePart = $this->processInput($input, '??', [$this, 'inputNullCoalesce']); 
            return $nullCoalescePart;
        }

        // ternary expression
        if(str_contains($input, '?'))
        {
            $ternaryPart = $this->processInput($input, '?', [$this, 'inputTernary']);
            return $ternaryPart;
        }

        // || logic
        $or = $this->processInput($input, '||', [$this, 'inputLogical']);
        if(!is_null($or))
        {
            return $or;
        }

        $xor = $this->processInput($lowered, 'xor', [$this, 'inputLogical']);
        if(!is_null($xor))
        {
            return $xor;
        }

        // && logic
        $and = $this->processInput($input, '&&', [$this, 'inputLogical']);
        if(!is_null($and))
        {
            return $and;
        }

        // comparison operators
        $compIdent = $this->processInput($input, '===', [$this, 'inputComparison']);
        if(!is_null($compIdent)) 
        {
            return $compIdent;
        }

        $compNotIdent = $this->processInput($input, '!==', [$this, 'inputComparison']);
        if(!is_null($compNotIdent)) 
        {
            return $compNotIdent;
        }

        $compEquals = $this->processInput($input, '==', [$this, 'inputComparison']);
        if(!is_null($compEquals)) 
        {
            return $compEquals;
        }

        $compNotEquals = $this->processInput($input, '!=', [$this, 'inputComparison']);
        if(!is_null($compNotEquals)) 
        {
            return $compNotEquals;
        }

        $forwarding = $this->processInput($input, '->', [$this, 'processForwarding'] );
        
        if (!is_null($forwarding)) 
        {
            return $forwarding;
        }

        $spaceship = $this->processInput($input, '<=>', [$this, 'inputComparison']);
        if(!is_null($spaceship)) 
        {
            return $spaceship;
        }

        $compGE = $this->processInput($input, '>=', [$this, 'inputComparison']);
        if(!is_null($compGE)) 
        {
            return $compGE;
        }

        $compLE = $this->processInput($input, '<=', [$this, 'inputComparison']);
        if(!is_null($compLE)) 
        {
            return $compLE;
        }

        $compGreater = $this->processInput($input, '>', [$this, 'inputComparison']);
        if(!is_null($compGreater)) 
        {
            return $compGreater;
        }

        $compLess = $this->processInput($input, '<', [$this, 'inputComparison']);
        if(!is_null($compLess)) 
        {
            return $compLess;
        }

        // string concatenation
        $concatVal = $this->processInput($input, '~', [$this, 'inputArithmeticAndConcat']);
        if(!is_null($concatVal)) 
        {
            return $concatVal;
        }

        // arithmetic operators
        $addVal = $this->processInput($input, '+', [$this, 'inputArithmeticAndConcat']);
        if(!is_null($addVal)) 
        {
            return $addVal;
        }

        $minusVal = $this->processInput($input, '-', [$this, 'inputArithmeticAndConcat']);
        if(!is_null($minusVal)) 
        {
            return $minusVal;
        }

        $moduloVal = $this->processInput($input, '%', [$this, 'inputArithmeticAndConcat']);
        if(!is_null($moduloVal)) 
        {
            // IMPORTANT: in Expression modulo is parsed like this: a*b%c*d == (a*b)%(c*d)
            return $moduloVal;
        }

        $powVal = $this->processInput($input, '**', [$this, 'inputArithmeticAndConcat']);
        if(!is_null($powVal)) 
        {
            // IMPORTANT: in Expression pow is parsed like this: a*b**c*d == (a*b)**(c*d)
            return $powVal;
        }

        $multiVal = $this->processInput($input, '*', [$this, 'inputArithmeticAndConcat']);
        if(!is_null($multiVal)) 
        {
            return $multiVal;
        }

        $divideVal = $this->processInput($input, '/', [$this, 'inputArithmeticAndConcat']);
        if(!is_null($divideVal)) 
        {
            return $divideVal;
        }

        // backtick string
        $backTickVal = $this->processInput($input, "`", [$this, 'inputBackTickSingleQuote'], str_starts_with($input, "`") );
        if(!is_null($backTickVal))
        {
            $input = $backTickVal;
        }

        // single quote string
        $singleQuoteVal = $this->processInput($input, "'", [$this, 'inputBackTickSingleQuote'], str_starts_with($input, "'") );
        if(!is_null($singleQuoteVal))
        {
            $input = $singleQuoteVal;
        }

        // double quote string
        if(str_starts_with($input, '"')) 
        {
            $output = json_decode($input);

            if(json_last_error() !== 0)
            {
                throw new Exception('unexpected end of string');
            }

            return $output;
        }

        // expression is negated
        if(str_starts_with($input, '!')) 
        {
            return !$this->eval(substr($input, 1));
        }

        // if the expression is just wrapped in brackets, remove them
        $containParenthesis = $this->processInput($input, "(", [$this, 'inputContainment'], str_starts_with($input, "(") );
        if(!is_null($containParenthesis))
        {
            // check if the input ends with a trailing round bracket
            return $containParenthesis;
        }

        // array definition
        $containBracket = $this->processInput($input, "[", [$this, 'inputContainment'], str_starts_with($input, "[") );
        if(!is_null($containBracket))
        {
            // check if the input ends with a trailing block bracket and get rid of both brackets
            return $containBracket;
        }

        // hash definition
        $containCurly = $this->processInput($input, "{", [$this, 'inputContainment'], str_starts_with($input, "{") );
        if(!is_null($containCurly))
        {
            // check if the input ends with a trailing curly bracket and get rid of both brackets
            return $containCurly;
        }

        // callable call
        // if the expression ends with a round bracket (and it doesn't start with one)
        $callable = $this->processInput($input, ')', [$this, 'processCallable'], str_ends_with($input, ')') );
        if (!is_null($callable)) 
        {
            return $callable;
        }

        // nested var [] access
        // if the expression ends with a block bracket (and it doesn't start with one)
        if (str_ends_with($input, ']')) 
        {
            $parts = $this->parse($input, '[', false);
            $prop = substr(array_pop($parts), 1, -1);
            $before = implode('', $parts);

            return $this->resolveProperty($before, $this->eval($prop));
        }

        // nested var . access
        if (str_contains($input, '.')) 
        {
            $parts = $this->parse($input, '.');
            $after = array_pop($parts);
            $before = implode('.', $parts);

            return $this->resolveProperty($before, $after);
        }

        // finally, if expression doesn't match any conditions above, assume it's a var
        if(!array_key_exists($input, $this->vars)) 
        {
            if(defined($input))
            {
                return constant($input);
            }

            throw new Exception("var `$input` not defined");
        }

        return $this->vars[$input];
    }

    protected function processInput($input, $contains, callable $closure, $checking = null, ...$args)
    {
        if(!is_null($checking))
        {
            if(is_bool($checking))
            {
                $checks = $checking;
            }
        }
        else
        {
            $checks = str_contains($input, $contains);
        }

        if($checks)
        {
            return $closure($input, $contains, ...$args);
        }
    }

    protected function evaluateList(string $params, bool $evalKeys = false)
    {
        $params = $this->parse($params, ',');

        foreach($params as $param) 
        {
            if(!str_starts_with($param, '...')) 
            {
                $paramParts = $this->parse($param, ':');
                $cntParams = count($paramParts);

                switch($cntParams)
                {
                    case 1:
                        [$key, $value] = [ null, $this->eval(array_shift($paramParts)) ];
                    break;
                    case 2:
                        [$key, $value] = [
                            $evalKeys ? $this->eval(array_shift($paramParts)) : array_shift($paramParts),
                            $this->eval(array_shift($paramParts))
                        ];
                    break;
                    default:
                        throw new Exception("unexpected `:`");
                    break;
                }

                /*match (count($paramParts)) 
                {
                    default => throw new Exception("unexpected `:`"),
                    1 => [$key, $value] = [ null, $this->eval(array_shift($paramParts)) ],
                    2 => [$key, $value] = [
                        $evalKeys ? $this->eval(array_shift($paramParts)) : array_shift($paramParts),
                        $this->eval(array_shift($paramParts))
                    ],
                };*/
                
                if($key !== null) 
                {
                    if(!is_string($key))
                    {
                        throw new Exception("key must be a string");
                    }
                    
                    $output[$key] = $value;
                } 
                else 
                {
                    $output[] = $value;
                }

                continue;
            }

            $packed = $this->eval(substr($param, 3));

            if(!is_array($packed))
            {
                throw new Exception("can't unpack `$param` - not an array");
            }

            foreach($packed as $item) 
            {
                $output[] = $item;
            }
        }

        return $output ?? [];
    }

    protected function resolveProperty(string $expression, string $prop)
    {
        $var = $this->eval($expression);

        if( is_array($var) && array_key_exists($prop, $var) )
        {
            return $var[$prop];
        }
        else if(
            is_object($var) 
            && 
            (property_exists($var, $prop) || method_exists($var, '__get'))
        )
        {
            return $var->$prop;
        }
        else if(
            is_object($var) 
            && 
            (method_exists($var, $prop) || method_exists($var, '__call'))
        )
        {
            return Closure::fromCallable([$var, $prop]);
        }
        else
        {
            throw new Exception("element `$prop` doesn't exist in `$expression`");
        }

        /*return match (true) {
            is_array($var) && array_key_exists($prop, $var) => $var[$prop],
            is_object($var) && (property_exists($var, $prop) || method_exists($var, '__get')) => $var->$prop,
            is_object($var) && (method_exists($var, $prop) || method_exists($var, '__call')) => Closure::fromCallable([$var, $prop]),
            default => throw new Exception("element `$prop` doesn't exist in `$expression`"),
        };*/
    }

    protected function parse(string $input, string $delimiter, bool $omitDelimiter = true)
    {
        $inQuotes = false;
        $depthRound = $depthBlock = $depthCurly = 0;
        $outputIndex = 0;
        $lenInput = strlen($input);

        for ($i = 0; $i < $lenInput; $i++) 
        {
            $write = true;
            $char = $input[$i];
            $charLeft = $i > 0 ? $input[$i - 1] : null;

            if ($char === '"' && $charLeft !== '\\') 
            {
                if($inQuotes === '"') 
                {
                    $inQuotes = false;
                } 
                else if(!$inQuotes) 
                {
                    $inQuotes = '"';
                }
            }

            if($char === "'" && $charLeft !== '\\') 
            {
                if($inQuotes === "'") 
                {
                    $inQuotes = false;
                } 
                else if(! $inQuotes) 
                {
                    $inQuotes = "'";
                }
            }

            if($char === "`" && $charLeft !== '\\') 
            {
                if($inQuotes === "`") 
                {
                    $inQuotes = false;
                } 
                else if(!$inQuotes) 
                {
                    $inQuotes = "`";
                }
            }

            if(!$inQuotes) 
            {
                // skip whitespaces, tabs and newlines
                if(in_array($char, [' ', "\n", "\r", "\t"])) 
                {
                    continue;
                }

                if($char === ')' && $depthRound === 0)
                {
                    throw new Exception('unexpected `)`');
                }

                if($char === ']' && $depthBlock === 0)
                {
                    throw new Exception('unexpected `]`');
                }

                if($char === '}' && $depthCurly === 0)
                {
                    throw new Exception('unexpected `}`');
                }

                if($depthRound === 0 && $depthBlock === 0 && $depthCurly === 0) 
                {
                    $match = true;

                    $delimLem = strlen($delimiter);

                    for($j = 0; $j < $delimLem; $j++) 
                    {
                        if($input[$i + $j] !== $delimiter[$j]) 
                        {
                            $match = false;
                            break;
                        }
                    }

                    if ($match) 
                    {
                        $outputIndex++;
                        $write = !$omitDelimiter;

                        if($omitDelimiter) 
                        {
                            $i += strlen($delimiter) - 1;
                        }
                    }
                }

                switch($char)
                {
                    case '(':
                        $depthRound++;
                    break;
                    case ')':
                        $depthRound--;
                    break;

                    case '[':
                        $depthBlock++;
                    break;
                    case ']':
                        $depthBlock--;
                    break;

                    case '{':
                        $depthCurly++;
                    break;
                    case '}':
                        $depthCurly--;
                    break;
                }

                /*match ($char) 
                {
                    '(' => $depthRound++, ')' => $depthRound--,
                    '[' => $depthBlock++, ']' => $depthBlock--,
                    '{' => $depthCurly++, '}' => $depthCurly--,
                    default => null,
                };*/
            }

            if($write) 
            {
                $output[$outputIndex] ??= '';
                $output[$outputIndex] .= $char;
            }
        }

        if($inQuotes)
        {
            throw new Exception("expected closing `$inQuotes`");
        }

        if($depthRound > 0)
        {
            throw new Exception("expected closing `)`");
        }

        if($depthBlock > 0)
        {
            throw new Exception("expected closing `]`");
        }

        if($depthCurly > 0)
        {
            throw new Exception("expected closing `}`");
        }

        return $output ?? [];
    }
}