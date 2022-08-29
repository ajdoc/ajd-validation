# Macros
- macros are a way to extend the functionality of ajd validation.
- macros are a way to create dynamic compounded rules for ajd validation.
- macros can also be similar to Reusable rules definition refer to [Usage](../usage.md) jump to **Reuse rule definition**.
- macros is also a way to create a new rule/s.

## How to register/create a macro.

### Create a macro using `->macro`
- To register/create use `->macro($name, \CLosure $macro);`
- After registering/creating a macro we could now call it by `$v->custom_macro()` or `$v::custom_macro();` which uses php's `__call` and `__callStatic`

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v->macro('mymacro', function($minlength = 2)
{
	$this
		->required()
		->minlength($minlength)
		;

	return $this;
	
});

$v->mymacro()->check('macrofield', '');

// Outputs error 
/*
	All of the required rules must pass for "Macrofield".
	  - The Macrofield field is required
	  - Macrofield must be greater than or equal to 2. character(s). 
*/

$v->macro('mymacro2', function($minlength, array $data)
{
	$obj = $this->Srequired()
			->Sminlength($minlength);

	if(!empty($data))
	{
		foreach($data as $field => $val)
		{
			$obj->field($field);
		}

	}
				
	$obj->eSminlength()
		->eSrequired()
		->checkGroup($data)
		;

	return $obj;
	
});

$macr1 = [
	'fieldmacrogroup1' => '',
	'fieldmacrogroup2' => '',
];

$v->mymacro2(2, $macr1);

// Outputs error 
/*
	All of the required rules must pass for "Fieldmacrogroup1".
	  - The Fieldmacrogroup1 field is required
	  - Fieldmacrogroup1 must be greater than or equal to 2. character(s). 
	All of the required rules must pass for "Fieldmacrogroup2".
	  - The Fieldmacrogroup2 field is required
	  - Fieldmacrogroup2 must be greater than or equal to 2. character(s). 
*/

```
### Class based macro using `->mixin`
- To register/create use class based macro we use `->mixin(string \AJD_validation\Contracts\CanMacroInterface::class| object \AJD_validation\Contracts\CanMacroInterface $mixin, $replace = true, ...$args);`
	1. `$mixin` - first argument is a qualified class that implements ` \AJD_validation\Contracts\CanMacroInterface::class` or an object that implements ` \AJD_validation\Contracts\CanMacroInterface::class`.
	2. `$replace = true`  - second argument defaults to `true` if set to `false` if the macro name already exists in the macro collection it will not replace the one in the collection.
	3. `...$args` - third argument are arguments that could be passed if the `$mixin` is a qualified class string.
- After registering/creating a macro we could now call it by `$v->custom_macro()` or `$v::custom_macro();` which uses php's `__call` and `__callStatic`

```php
use AJD_validation\AJD_validation;
use AJD_validation\Contracts\CanMacroInterface;

class Custom_macro implements CanMacroInterface
{
	public $testarg;

	public function __construct($testarg = null)
	{		
		$this->testarg = $testarg;
	}

	public function getMacros() 
	{
		return [
			'mymacro_class'
		];
	}

	public function mymacro_class()
	{
		$that = $this;
		return function($minlength = 2) use ($that)
		{
			echo $that->testarg;

			$this
				->required()
				->minlength($minlength)
				;

			return $this;
		};
	}
}

$v->mixin(Custom_macro::class, true, '1' );

$v->mymacro_class(3)->check('macrofield', '');

/*
	Outputs error and echoes "1"
	All of the required rules must pass for "Macrofield".
	  - The Macrofield field is required
	  - Macrofield must be greater than or equal to 3. character(s). 
*/

```

### Creating macro and registering rule/rules
- In this example we will create a custom rule validation if the input is positive.
- To create a rule inside a macro use `->registerAsRule(\Closure $validator, array $errorMessages);`
	1. \Closure $validator - contains the validation logic and must return a `bool`.
	2. array $errorMessages - contains the `$errorMessages['default'] = 'Message'` and Inverse `$errorMessages['inverse'] = 'inverse Message'`
- If your rule needs arguments use `->setArguments([argumets])`

```php
use AJD_validation\AJD_validation;
use AJD_validation\Contracts\CanMacroInterface;

$testmacroPositive = function($args = null)
{
	if($args)
	{
		$this->setArguments([$args]);
	}


	$this->registerAsRule(function($value, $satisfier = null)
	{
		if (!is_numeric($value)) 
        {
            return false;
        }

        return $value > 0;

		
	}, ['default' => 'Value :field must be positive', 'inverse' => 'Value :field must not be positive']);

	return $this;
};

/*
	Here we could either use `macro` or `mixin`
*/

$v->macro('positive', $testmacroPositive);
// or
class Custom_macro implements CanMacroInterface
{
	protected $testmacroPositive;

	public function __construct($testmacroPositive = null)
	{		
		$this->testmacroPositive = $testmacroPositive;
	}

	public function getMacros() 
	{
		return [
			'positive',
		];
	}

	public function positive()
	{
		return $this->testmacroPositive;
	}
}

$v->mixin(Custom_macro::class, true, $testmacroPositive);

$v->positive()
	->check('macrofield');
/*
	Outputs error
		All of the required rules must pass for "Macrofield".
		 - Value Macrofield must be positive
*/

$v->Notpositive()
	->check('macrofield', '2');

/*
	Outputs error
		All of the required rules must pass for "Macrofield".
		 - Value Macrofield must not be positive
*/

```
### Multiple rules creation example.
- The only difference is that on `registerAsRule(\Closure $validator, array $errorMessages, $ruleName)` we must define a unique rule name and setting arguments also needs the unique rule name.

```php
use AJD_validation\AJD_validation;
use AJD_validation\Contracts\CanMacroInterface;

$testmacroPositive = function($args = null)
{
	if($args)
	{
		$this->setArguments([$args], 'positive1');
		$this->setArguments([$args], 'positive2');
	}


	$this->registerAsRule(function($value, $satisfier = null)
	{
		if (!is_numeric($value)) 
        {
            return false;
        }

        return $value > 0;

		
	}, ['default' => 'Value :field must be positive', 'inverse' => 'Value :field must not be positive'], 'positive1');

	$this->registerAsRule(function($value, $satisfier = null)
	{
		if (!is_numeric($value)) 
        {
            return false;
        }

        return $value > 0;

		
	}, ['default' => 'Value :field must be positive2', 'inverse' => 'Value :field must not be positive2'], 'positive2');

	return $this;
};

/*
	Here we could either use `macro` or `mixin`
*/

$v->macro('positive', $testmacroPositive);
// or
class Custom_macro implements CanMacroInterface
{
	protected $testmacroPositive;

	public function __construct($testmacroPositive = null)
	{		
		$this->testmacroPositive = $testmacroPositive;
	}

	public function getMacros() 
	{
		return [
			'positive',
		];
	}

	public function positive()
	{
		return $this->testmacroPositive;
	}
}

$v->mixin(Custom_macro::class, true, $testmacroPositive);

$v->positive()
	->check('macrofield');
/*
	Outputs error
		All of the required rules must pass for "Macrofield".
		  - Value Macrofield must be positive
  		  - Value Macrofield must be positive2
*/

$v->Notpositive()
	->check('macrofield', '2');

/*
	Outputs error
		All of the required rules must pass for "Macrofield".
		 - Value Macrofield must not be positive
  		 - Value Macrofield must not be positive2
*/
```

### Complex example.
- In this example we could check if the macro call is inverse by calling `$this::getInverse()`.
```php
use AJD_validation\AJD_validation;
use AJD_validation\Contracts\CanMacroInterface;

$testmacroPositive = function($args = null)
{
	$rules = [
		'required',
		'minlength'
	];

	foreach($rules as $rule)
	{
		$ruleName = $rule;

		if($this::getInverse())
		{
			$ruleName = 'Not'.$rule;
		}

		if($rule == 'minlength')
		{
			$this->{$ruleName}(2);	
		}
		else
		{
			$this->{$ruleName}();
		}
		
	}

	if($args)
	{
		$this->setArguments([$args]);
	}


	$this->registerAsRule(function($value, $satisfier = null)
	{
		if (!is_numeric($value)) 
        {
            return false;
        }

        return $value > 0;

		
	}, ['default' => 'Value :field must be positive', 'inverse' => 'Value :field must not be positive']);

	return $this;
};

/*
	Here we could either use `macro` or `mixin`
*/

$v->macro('positive', $testmacroPositive);
// or
class Custom_macro implements CanMacroInterface
{
	protected $testmacroPositive;

	public function __construct($testmacroPositive = null)
	{		
		$this->testmacroPositive = $testmacroPositive;
	}

	public function getMacros() 
	{
		return [
			'positive',
		];
	}

	public function positive()
	{
		return $this->testmacroPositive;
	}
}

$v->mixin(Custom_macro::class, true, $testmacroPositive);

$v->positive()
	->check('macrofield');
/*
	Outputs error
		All of the required rules must pass for "Macrofield".
		  - The Macrofield field is required
		  - Macrofield must be greater than or equal to 2. character(s). 
		  - Value Macrofield must be positive
*/

$v->Notpositive()
	->check('macrofield', '2');

/*
	Outputs error
		All of the required rules must pass for "Macrofield".
		  - The Macrofield field is not required.
		  - Macrofield must not be greater than or equal to 2.
		  - Value Macrofield must not be positive
*/

```

### Macros could also be added via Extension Classes
```php
use AJD_validation\AJD_validation;
use AJD_validation\Contracts\Base_extension;

class Custom_extension extends Base_extension
{
	public function getName()
	{
		return 'Custom_extension';
	}

	/*
		Adding custom macros
	*/
	public function getMacros()
	{
		return [
			'extension_macro',
			'extension_macro2'
		];
	}


	public function extension_macro()
	{
		return function()
		{
			$this->required()
				->minlength(7);

			return $this;
		};
	}

	public function extension_macro2($args = null)
	{
		return function($args = null)
		{
			if($args)
			{
				$this->setArguments([$args]);
			}


			$this->registerAsRule(function($value, $satisfier = null)
			{
				if (!is_numeric($value)) 
		        {
		            return false;
		        }

		        return $value > 0;

				
			}, ['default' => 'Value :field must be positive ext :*', 'inverse' => 'Value :field must not be positive ext :*']);

			return $this;
		};
	}
}


$v->registerExtension(new Custom_extension);

$v
	->extension_macro()
	->extension_macro2(2)
	->check('extension_macro1', '');
/*
	Outputs Error
	All of the required rules must pass for "Extension macro1".
	  - The Extension macro1 field is required
	  - Extension macro1 must be greater than or equal to 7. character(s). 
	  - Value Extension macro1 must be positive ext 2
*/

```