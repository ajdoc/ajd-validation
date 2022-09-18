# Usage

In this document we'll see how to use ajd-validation

## Basic usage
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;


	$v
		->required()
		->minlength(5)
		->check('firstname', 'value-of-firstname');

	$v
		->required()
		->minlength(5)
		->check('lastname', 
			[
				'lastname' => 'value-of-lastname'
			]
		);

	// validation will automatically validate one dimensional array
	$v
		->required()
		->minlength(5)
		->check('list_of_item', 
			[
				'list_of_item' => [
					'apples',
					'',
					'b'
				]
			]
		);

	if($v->validation_fails())
	{
		var_dump($v->errors()->all());
		echo $v->errors()->toStringErr();
	}

	try 
	{
		$v
			->required()
			->minlength(5)
			->check('firstname', 'value-of-firstname');

		$v
			->required()
			->minlength(5)
			->check('lastname', 
				[
					'lastname' => 'value-of-lastname'
				]
			);

		$v
			->required()
			->minlength(5)
			->check('middlename', 'value-of-middlename');

		$v->assert();
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}

```

You define rules by chaining like in the example, after you define all the rules you can start validation by calling check method which receives the field key or field name as the first paramater and the value or array of values organized as an associative array like in the example above as the second paramater.

If the validation fails, validation_fails method will return true, error messages can be retrieved using `$v->errors()->all()` which will return an associative array of field and rules error messages or you can use `$v->errors()->toStringErr()` which will return formated error messages. 

Or you can wrap your field-rules definition in a try catch and after defining all use `$v->assert()` which will throw an exception of error messages.

Validation will automatically apply all the defined rules on a one dimensional array.

## Some useful method api
	* $v->validation_fails($field_key = null, $array_key = null);
		- validation fails can accept field key if you want to check if field validation fails 
		- validation fails can also accept field key and the specific key in a one dimesional array to check if that specific item in the array fails

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;


	$v
		->required()
		->minlength(5)
		->check('firstname', '');

	$v
		->required()
		->minlength(5)
		->check('lastname', 
			[
				'lastname' => 'value-of-lastname'
			]
		);

	// validation will automatically validate one dimensional array
	$v
		->required()
		->minlength(5)
		->check('list_of_item', 
			[
				'list_of_item' => [
					'apples',
					'',
					'b'
				]
			]
		);

	var_dump($v->validation_fails('firstname')); // will return true
	var_dump($v->validation_fails('lastname')); // will return false

	var_dump($v->validation_fails('list_of_item', 0)); // will return true

	var_dump($v->validation_fails('list_of_item', 1)); // will return false
	var_dump($v->validation_fails('list_of_item', 2)); // will return false

```

* $v->check($field, mixed $value);
		- check can accept field for first paramater
			- field can also be separated with a pipe where the string after the pipe will be the field name to be used in the error message.
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

	$v->required()
		->check('firstname', ''); // Outputs Firstname is required.

	$v->required()
		->check('firstname|First Name', ''); // Outputs First Name is required.
```
		- value 
			- can be a string
			- numeric 
			- array [1,2,3]
			- array [$field => 'field_value'], [$field => 1], [$field => [1,2,3] ]

* $v->assert($addHeaderErrorMessage = true) : \Exception
	- assert will throw an exception containing all the error messages
		- if $addHeaderErrorMessage = true will add 
			"All of the required rules must pass for "[field]"." message

* $v->assertFirst($addHeaderErrorMessage = true) : \Exception
	- assertFirst will throw an exception containing the first error message
		- if $addHeaderErrorMessage = true will add 
			"All of the required rules must pass for "[field]"." message

## Inversing The Result
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

	$v->Notrequired()
		->check('firstname', ''); // doesn't output error.

```

You can inverse a validation by prefixing rule name with `Not` followed by the rule name. Does not output error but if you put a value it will output error below.

```
All of the required rules must pass for "Middlename2".
  - The Middlename2 field is not required.
```

## Using Or logic when defining Rules
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

	$v 
	->oRminlength(2)
	->oRdigit()
	->oRcompare('==', 'b')
	->check('middlename2', 'a');

```

The above example will output the error 

```
All of the required rules must pass for "Middlename2".
  - Middlename2 must be greater than or equal to 2. character(s). 
  - Middlename2 must contain only digits (0-9).
  - Middlename2 must be equal to "b".
```

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

	$v 
	->oRminlength(2)
	->oRdigit()
	->oRcompare('==', 'b')
	->check('middlename2', 'aa');

```

But if the validation passes any of the rules defined it will not output error. Basically this definition meant if any of the rules passes field passes.

## Basic customization of error message
	
If for instance you want to customize the error message per error message this could be achived by `->[rulename](null, '@custom_error_Place your custom error message here')`
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

	$v 
	->minlength(2)
	->digit()
	->compare('==', 'b', '@custom_error_"b" is the value for middlename2 to be accpted.')
	->check('middlename2', 'a');

```
The rule compare will have this output error
```
All of the required rules must pass for "Middlename2".
  - Middlename2 must be greater than or equal to 2. character(s). 
  - Middlename2 must contain only digits (0-9).
  - "b" is the value for middlename2 to be accpted.
```

## Set error messages language
- To set the error messages Languae `$v->setLang(Lang::FIL);`
```php
use AJD_validation\AJD_validation;
use AJD_validation\Constants\Lang;

$v = new AJD_validation;

$v->setLang(LANG::FIL);

$v 
	->required()
	->check('field', '');

/*
	Outputs error 
		The Field field ay kelangan
*/

```
- Check src\AJD_validation\Constants\Lang.php on what language are currently supported 
- **Note not yet all rules has localization, will gradually add localization support :))**

## Adding custom Lang file
- You may choose to add your own custom lang file
```php
use AJD_validation\AJD_validation;
use AJD_validation\Constants\Lang;

$v = new AJD_validation;

$v->addLangDir('example', __DIR__.DIRECTORY_SEPARATOR.'custom_lang/', true);

$v->setLang('example');

```
- `$v->addLangDir(string $lang, string $fullPath, bool $createWrite = false)`
	- first argument is the language name which is also the lang file name appended with `_lang`. So in the above example the lang file name must be `example_lang.php`
	- second argument is the full path / directory where the lang file is located.
	- third argument is $createWrite defaults to false, if set to true ajd validation will create the $fullPath if not existing, will create the lang file if not existing and will generate currently used error messages for all the rules available, so that you may edit the file as to your requirements.

## checkArr method
- `->checkArr(string $field, array $value)` - method that allows array traversing validation by using dot notation
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v 
->required()
->digit()
->checkArr('arr.*', [
	'arr' => [
		'arr1' => [
			'sub_arr' => 'a',
			'sub_arr2' => ['', '']
		],
		'arr2' => []
	]
]);

/*
	Outputs error
		All of the required rules must pass for "Arr.arr1.sub arr".
		  - Arr.arr1.sub arr must contain only digits (0-9).
		All of the required rules must pass for "Arr.arr1.sub arr2.0".
		  - The Arr.arr1.sub arr2.0 field is required
		  - Arr.arr1.sub arr2.0 must contain only digits (0-9).
		All of the required rules must pass for "Arr.arr1.sub arr2.1".
		  - The Arr.arr1.sub arr2.1 field is required
		  - Arr.arr1.sub arr2.1 must contain only digits (0-9).
		All of the required rules must pass for "Arr.arr2".
		  - The Arr.arr2 field is required
		  - Arr.arr2 must contain only digits (0-9).
*/

$v 
->required()
->digit()
->checkArr('arr.arr1', [
	'arr' => [
		'arr1' => [
			'sub_arr' => 'a',
			'sub_arr2' => ['', '']
		],
		'arr2' => []
	]
]);
/*
	Outputs error
		All of the required rules must pass for "Arr.arr1.sub arr".
		  - Arr.arr1.sub arr must contain only digits (0-9).
		All of the required rules must pass for "Arr.arr1.sub arr2.0".
		  - The Arr.arr1.sub arr2.0 field is required
		  - Arr.arr1.sub arr2.0 must contain only digits (0-9).
		All of the required rules must pass for "Arr.arr1.sub arr2.1".
		  - The Arr.arr1.sub arr2.1 field is required
		  - Arr.arr1.sub arr2.1 must contain only digits (0-9).
*/

$v 
->required()
->digit()
->checkArr('arr.arr1.sub_arr', [
	'arr' => [
		'arr1' => [
			'sub_arr' => 'a',
			'sub_arr2' => ['', '']
		],
		'arr2' => []
	]
]);

/*
	Outputs error
		All of the required rules must pass for "Arr.arr1.sub arr".
  			- Arr.arr1.sub arr must contain only digits (0-9).
*/

$v 
->required()
->digit()
->checkArr('arr.arr1.sub_arr2', [
	'arr' => [
		'arr1' => [
			'sub_arr' => 'a',
			'sub_arr2' => ['', '']
		],
		'arr2' => []
	]
]);
/*
	Outputs error
		All of the required rules must pass for "Arr.arr1.sub arr2.0".
		  - The Arr.arr1.sub arr2.0 field is required
		  - Arr.arr1.sub arr2.0 must contain only digits (0-9).
		All of the required rules must pass for "Arr.arr1.sub arr2.1".
		  - The Arr.arr1.sub arr2.1 field is required
		  - Arr.arr1.sub arr2.1 must contain only digits (0-9).
*/

$v 
->required()
->checkArr('arr.arr2', [
	'arr' => [
		'arr1' => [
			'sub_arr' => '',
			'sub_arr2' => ['', '']
		],
		'arr2' => []
	]
]);
/*
	Outputs error
		All of the required rules must pass for "Arr.arr2".
  			- The Arr.arr2 field is required
*/

```

## Reuse rule definition
- Reuse or store rule defintions by:
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v1 = $v
	->required()
	->minlength(3)
	->maxlength(30)
	->setUpValidation()
	->getValidationDefinition();

$v2 = $v
			->required()
  		->minlength(2)
  		->setUpValidation()
			->getValidationDefinition();

$v1()->check('field1', 'e');
$v2()->check('field2', '');

$v1()->digit()->check('field3', '');
```
- We could reuse the rule definition by using `$v->setUpValidation((optional)'[unique-identfier]')->getValidationDefinition()` storing it in a variable the calling the variable like function the chain as shown in the example above.
- We could define another rule definition not in the storage for a specific field like in 'field3'.

## Any 
- use this to simulate an `or` logic between two or more validation defintion.
- validation will pass if `any` of the validation passes.
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v->any(
	$v->required()->minlength(2)->check('or_field1',['or_field1' => ['', '']]), 
	$v->required()->check('or_field2',['or_field2' => ['']]),
	$v->required()->check('or_field3',''),
); // validation fails 
/*
	Outputs error 
	All of the required rules must pass for "Or field1".
	  - The Or field1 field is required at row 1.
	  - The Or field1 field is required at row 2.
	  - Or field1 must be greater than or equal to 2. character(s).  at row 1.
	  - Or field1 must be greater than or equal to 2. character(s).  at row 2.
	All of the required rules must pass for "Or field2".
	  - The Or field2 field is required at row 1.
	All of the required rules must pass for "Or field3".
	  - The Or field3 field is required
*/

$v->any(
	$v->required()->minlength(2)->check('or_field1',['or_field1' => ['', '']]), 
	$v->required()->check('or_field2',['or_field2' => ['']]),
	$v->required()->check('or_field3','a'),
); // validation passes 
/*
	Note: in first validation even if one item in or_field1 passes it will still fail and print error, to make this validation pass both item in or_field1 must pass
*/

/*
	Example 2
*/
$v->any(
	$v->required()->check('group_and_single1', ''),

	$v 
		->Srequired(null, AJD_validation::LOG_OR)
			->field('group_and_single2')

			->field('group_and_single3')
				->minlength(2)
		->eSrequired()
		->checkGroup(
			[
				'group_and_single2' => '',
				'group_and_single3' => '',
			]
		)
); // validation fails 
/*
	Outputs error
	All of the required rules must pass for "Group and single1".
	  - The Group and single1 field is required
	All of the required rules must pass for "Group and single2".
	  - The Group and single2 field is required
	All of the required rules must pass for "Group and single3".
	  - The Group and single3 field is required
	  - Group and single3 must be greater than or equal to 2. character(s). 
*/

$v->any(
	$v->required()->check('group_and_single1', ''),

	$v 
		->Srequired(null, AJD_validation::LOG_OR)
			->field('group_and_single2')

			->field('group_and_single3')
				->minlength(2)
		->eSrequired()
		->checkGroup(
			[
				'group_and_single2' => '',
				'group_and_single3' => 'aa',
			]
		)
); // validation passes
/*
	Note: since we define in `->Srequired(null, AJD_validation::LOG_OR)` that `group_and_single2` or `group_and_single3` passes required and field `group_and_single3` passes minlength(2) this any validation passes.
*/ 
```

## Trigger When
- use this feature to trigger a validation if the condition is true.
- use this if you don't like writing if condition.
- when triggerWhen returns true it will run the validation if false it will not run validation.

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

/*
	Instead of 
*/
if(!empty($test))
{
	$v 
		->required()
		->minlength(2)
		->check('trigger_when', '');
}

/*
	you can write
*/
$v 
->required()
->minlength(2)
->triggerWhen(!empty($test))
->check('trigger_when', '');

/*
	triggerWhen can receive the following arguments
*/

// 1. booleans
$v 
->required()
->minlength(2)
->triggerWhen(!empty($test)) // boolean
->check('trigger_when', '');

$v 
->required()
->minlength(2)
->triggerWhen($v->getValidator()->validate('')) // boolean
->check('trigger_when', '');

$v 
->required()
->minlength(2)
->triggerWhen($v->Lgfirst(true)->runLogics('')) // boolean
->check('trigger_when', '');

// 2. callables
$v 
->required()
->minlength(2)
->triggerWhen(function($ajdInstance)
{
	return true;
}) // callable
->check('trigger_when', '');

class Test
{
	public function handle($ajdInstance, mixed...$ags)
	{
		/*
			$ags[0] = 1
			$ags[1] = 2
		*/

		return true;
	}
}

$v 
->required()
->minlength(2)
->triggerWhen([new Test, 'handle', 1, 2]) // callable
->check('trigger_when', '');

// 3. Validator instance 
// will validate value
$v 
->required()
->minlength(2)
->triggerWhen($v->getValidator()->required()) // Validator instance
->check('trigger_when', '');


// 4. Logics_map instance 
// will validate value
$v 
->required()
->minlength(2)
->triggerWhen($v->Lgfirst(true)->wrapLogic()) // Logics_map instance 
->check('trigger_when', '');

```
- `$v->triggerWhen(bool|callable|\AJD_validation\Helpers\Logics_map|\AJD_validation\Contracts\Validator)` 

## The Validation Result Object
- The Validation Result is an object in which it is possible to get the validation results such as errors, values, the validation definition and also allows to do some processings on the results:

1. To get the Validation Result 
```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;

// 1 During setup
$v 
	->required()
	->setUpValidation() // returns validation result object

// 2 after validation
$v 
	->required()
	->minlength(2)
	->check('field1', '')
	->getValidationResult() // returns validation result object
```

2. Mapping Errors
- Use `->mapErrors(\Closure(string $errors, self $that) : array)` 
- `mapErrors` will only trigger if validation fails
```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;

$v
	->required()
	->minlength(2)
	->check('field1', '')
	->getValidationResult()
	->mapErrors(function($errors, $self)
	{
		echo '<pre>';
		print_r($errors);
		return $errors;
	});

	// prints 
	/*
	Array
	(
	    [required] => Array
	        (
	            [0] => The Field1 field is required
	        )

	    [minlength] => Array
	        (
	            [0] => Field1 must be greater than or equal to 2. character(s). 
	        )

	)
	*/
```
- Overriding Message in `mapErrors`
```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;

$v
	->required()
	->minlength(2)
	->check('field1', '')
	->getValidationResult()
	->mapErrors(function($errors, $self)
	{
		$mm = '';
		$mArr = [];
		foreach($errors as $rule => $mess)
		{
			foreach($mess as $k => $m)
			{
				$mm .= '&nbsp;- '.$m.' Custom error new<br>';

				$self->overrideErrorMessage($mm, $rule, $k);
			}
		}
		return $errors;
	});

	// prints errors
	/*
	All of the required rules must pass for "Field1".
  -  - The Field1 field is required Custom error new
 - Field1 must be greater than or equal to 2. character(s). Custom error new
	*/
```
- Throwing Error in `mapErrors`
```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;

$v
	->required()
	->minlength(2)
	->check('field1', '')
	->getValidationResult()
	->mapErrors(function($errors, $self)
	{
		$mm = '';
		$mArr = [];
		foreach($errors as $rule => $mess)
		{
			foreach($mess as $k => $m)
			{
				$mm .= '&nbsp;- '.$m.' Custom error new<br>';

				$self->overrideErrorMessage($mm, $rule, $k);
			}
		}
		
		return $self->throwErrors($mm);
	})->otherwise(function($e)
	{
		echo $e->getMessage().'from otherwise throw';
	});

	// prints errors
	/*
	- The Field1 field is required Custom error new
 	- Field1 must be greater than or equal to 2. character(s). Custom error new
		from otherwise throw
	*/
```

3. Mapping Values
- Use `->mapValue(\Closurea(mixed $values, self $that) : mixed)`
- `mapValue` will only trigger if validation passes
```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;

$v
	->required()
	->minlength(2)
	->check('field1', 'aa')
	->getValidationResult()
	->mapValue(function($value, $self)
	{
		echo '<pre>';
		print_r($value);
		return $value;
	});

	// prints value
	/*
	Array
	(
	    [0] => aa
	)
	*/

// Do some processing
echo '<pre>';
print_r($v
	->required()
	->minlength(2)
	->check('field1', 'aa')
	->getValidationResult()
	->mapValue(function($value, $self)
	{
		
		return ['field1' => $value[0]];
	})->getFieldValue()); 
/*
	returns 
	Array
	(
	    [field1] => aa
	)
*/
```
- Forward Resolution
```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;

$v
	->required()
	->minlength(2)
	->check('field1', 'aa')
	->getValidationResult()
	->mapValue(function($value, $self)
	{
		
		$val = ['field1' => $value[0]];

		return \AJD_validation\Async\PromiseHelpers::resolve($val);
	})->done(function($value)
	{
		print_r($value);
	})->then(function($v)
	{
		echo 'test forward resolution'.var_export($v, true);
	});
/*
	prints 
	Array
	(
	    [field1] => aa
	)
	test forward resolutionarray (
	  'field1' => 'aa',
	)
*/
```

4. Getting The Field Value
- Use `->getFieldValue() : mixed`
- `getFieldValue` will only trigger if validation passes
```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;

$v1 = $v
	->required()
	->minlength(2)
	->check('field1', 'aa')
	->getValidationResult()
	->getFieldValue();

	print_r($v1);
/*
	prints/returns
	Array
	(
	    [0] => aa
	)
*/

// if validation fails

$v1 = $v
	->required()
	->minlength(2)
	->check('field1', '')
	->getValidationResult()
	->getFieldValue();

	print_r($v1);
/*
	prints/returns
	Array
	()
*/
```

5. getting all the valid values
- Use `->getValue(array &$storage) : array`
- `getValue` will only trigger if validation passes
```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;
echo '<pre>';

	$result1 = $v
	->required()
	->minlength(2)
	->check('field1', 'a1')
	->getValidationResult();

	$v1 = $result1->getValidationDefinition();

	$result1->getValue($storage);

	$v1()->check('field2', '')
		->getValidationResult()
		->getValue($storage);

	$v1()->check('field3', 'a3')
		->getValidationResult()
		->getValue($storage);

	print_r($storage);
	/*
		In the above example only 'field1' and 'field3' was included in the storage because 'field2' fails.
	*/
	/*
		prints
		Array
		(
		    [field1] => a1
		    [field3] => a3
		)
	*/
```
6. Check if the validation is valid.
- Use `->isValid() : bool`
```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;

$result1 = $v
	->required()
	->minlength(2)
	->check('field1', 'a1')
	->getValidationResult()
	->isValid();
	
	var_dump($result1);

	/*
		prints/returns
		bool(true)
	*/
```

## The Combinators
- Combinators will allow you to combine field-rule validation definition which will also allow to create an error message for the combined validation definition, will allow to check the set of rules in sequence, will allow to check the set of field-rule in sequence, which will allow to check the combined validation definition associatively.:

1. Combining Field Rule definition
```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;

// first way
$combined = $v->combinator(
		$v->required()
		->minlength(2)
		->check('field1', '')
		->getValidationResult(),

		$v->required()
		->minlength(3)
		->check('field2', '')
		->getValidationResult(),		
	);

	var_dump($combined->check('')
	->getValidationResult()
	->isValid()); // returns/prints false

	// prints error
	/*
		All of the required rules must pass for "Field1".
		  - The Field1 field is required
		  - Field1 must be greater than or equal to 2. character(s).
		All of the required rules must pass for "Field2".
		  - The Field2 field is required
		  - Field2 must be greater than or equal to 3. character(s).
	*/

	// second way
  // only difference from the first way is that with this way it won't run the validation and remove error message.
  $combined = $v->combinator(
		$v->required()
		->minlength(2)
		->setUpValidation('field1'),

		$v->required()
		->minlength(3)
		->setUpValidation('field2'),		
	);

	var_dump($combined->check('')
	->getValidationResult()
	->isValid()); // returns/prints false

	// prints error
	/*
		All of the required rules must pass for "Field1".
		  - The Field1 field is required
		  - Field1 must be greater than or equal to 2. character(s).
		All of the required rules must pass for "Field2".
		  - The Field2 field is required
		  - Field2 must be greater than or equal to 3. character(s).
	*/

```

2. Combining Error messages for combined field rule validation.
```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;
$combined = $v->combinator(
	$v->required()
	->minlength(2)
	->setUpValidation('field1'),

	$v->required()
	->minlength(3)
	->setUpValidation('field2'),		
);

$combined->setCombineErrorMessage('field 1 and field 2 is required and field 1 min length is 2 while field 2 min length is 3.')->check('');

$v->assert();

// prints error
	/*
		field 1 and field 2 is required and field 1 min length is 2 while field 2 min length is 3.
	*/

```

3. Array Values for combined validation
```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;
$combined = $v->combinator(
	$v->required()
	->minlength(2)
	->setUpValidation('field1'),

	$v->required()
	->minlength(3)
	->setUpValidation('field2'),		
);

$combined->check(['fieldcom' => ['', '']], 'fieldcom');

$v->assert();
// prints error
/*
All of the required rules must pass for "Field1".
  - The Field1 field is required at row 1.
  - The Field1 field is required at row 2.
  - Field1 must be greater than or equal to 2. character(s). at row 1.
  - Field1 must be greater than or equal to 2. character(s). at row 2.
All of the required rules must pass for "Field2".
  - The Field2 field is required at row 1.
  - The Field2 field is required at row 2.
  - Field2 must be greater than or equal to 3. character(s). at row 1.
  - Field2 must be greater than or equal to 3. character(s). at row 2.
*/
```

4. Combining with another combinator
```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;
$combined = $v->combinator(
	$v->required()
	->minlength(2)
	->setUpValidation('field1'),

	$v->required()
	->minlength(3)
	->setUpValidation('field2'),		
);

$combined2 = $v->combinator(
	$combined,
	$v->required()
		->email()
		->setUpValidation('field3')
);

$combined2->check(['fieldcom' => ['', '']], 'fieldcom');

$v->assert();
// prints error
/*
All of the required rules must pass for "Field1".
  - The Field1 field is required at row 1.
  - The Field1 field is required at row 2.
  - Field1 must be greater than or equal to 2. character(s). at row 1.
  - Field1 must be greater than or equal to 2. character(s). at row 2.
All of the required rules must pass for "Field2".
  - The Field2 field is required at row 1.
  - The Field2 field is required at row 2.
  - Field2 must be greater than or equal to 3. character(s). at row 1.
  - Field2 must be greater than or equal to 3. character(s). at row 2.
*/
```

5. Checking Combined Validation in sequence
- This means that if the first validation passes it will go to the next validation
```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;
$combined = $v->combinator(
	$v->required()
	->minlength(2)
	->setUpValidation('field1'),

	$v->required()
	->minlength(3)
	->setUpValidation('field2'),		
);

$combined->sequence('');

$v->assert();
// prints error
/*
	All of the required rules must pass for "Field1".
  - The Field1 field is required
  - Field1 must be greater than or equal to 2. character(s).
*/

$combined = $v->combinator(
	$v->required()
	->minlength(2)
	->setUpValidation('field1'),

	$v->required()
	->minlength(3)
	->setUpValidation('field2'),		
);

$combined->sequence('aa');

$v->assert();
// prints error
/*
	All of the required rules must pass for "Field2".
  - Field2 must be greater than or equal to 3. character(s).
*/
```

6. Checking Combined Validation in association.
```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;

$combined = $v->combinator(
	$v->required()
	->minlength(2)
	->setUpValidation('field1'),

	$v->required()
	->minlength(3)
	->setUpValidation('field2'),		
);

$combined->associative([
	'field1' => 'aa',
	'field2' => ''
]);

$v->assert();

// prints error
/*
	All of the required rules must pass for "Field2".
  - The Field2 field is required
  - Field2 must be greater than or equal to 3. character(s).
*/
```

7. Checking Combined Validation in association and in sequence.
```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;

$combined = $v->combinator(
	$v->required()
	->minlength(2)
	->setUpValidation('field1'),

	$v->required()
	->minlength(3)
	->setUpValidation('field2'),		
);

$combined->assocSequence([
	'field1' => '',
	'field2' => ''
]);

$v->assert();

// prints error
/*
	All of the required rules must pass for "Field1".
  - The Field1 field is required
  - Field1 must be greater than or equal to 2. character(s).
*/

$combined->assocSequence([
	'field1' => 'aa',
	'field2' => ''
]);

$v->assert();

// prints error
/*
	All of the required rules must pass for "Field2".
  - The Field2 field is required
  - Field2 must be greater than or equal to 3. character(s).
*/
```

8. Checking Combined Validation in association and in grouped sequence.
- This will be useful for a multi step form.
```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;

$combined = $v->combinator(
		$v->required()
		->minlength(2)
		->setUpValidation('field1'),

		$v->required()
		->minlength(3)
		->setUpValidation('field2'),		

		$v->required()
		->minlength(5)
		->setUpValidation('field3'),	
	);

	$combined->assocSequence([
		
		'basic_info_group' => [
			'field1' => '',
			'field2' => ''
		],
		'account_details_group' => [
			'field3' => ''
		]
	
	]);

$v->assert();

// prints error
/*
	All of the required rules must pass for "Field1".
  - The Field1 field is required
  - Field1 must be greater than or equal to 2. character(s).
	All of the required rules must pass for "Field2".
  - The Field2 field is required
  - Field2 must be greater than or equal to 3. character(s).
*/

$combined->assocSequence([
		
		'basic_info_group' => [
			'field1' => 'aa',
			'field2' => 'aaa'
		],
		'account_details_group' => [
			'field3' => ''
		]
	
	]);

$v->assert();
// prints error
/*
	All of the required rules must pass for "Field3".
  - The Field3 field is required
  - Field3 must be greater than or equal to 5. character(s).
*/
```
**Do note that all combinators returns a promise in which you could also get validation result through `->getValidationResult()`.**

## The Client Side Component
- **Do note client side component currently supports this libraries.**
	- [jqueryvalidation.js](https://jqueryvalidation.org/documentation/)
	- [parsley.js](https://parsleyjs.org/)

- **Do note client side component only supports the following built in rules.**
```php
$rulesClass = [
	'required', 'required_allowed_zero', // required base rules
	'email', 'base_email', 'rfc_email', 'spoof_email', 'no_rfc_email', 'dns_email', // email base rules
	'in', 'date', 'multiple', // rules with client side support
	'alpha', 'alnum', 'digit', // ctype rules
	'regex', 'mac_address', 'consonant', 'mobileno', 'phone', 'vowel', // regex rules
	'maxlength', 'minlength' // length based rules
];
```
- To use the client side component
- Client Side Component defaults to parsley.js
- One must be familiar of the arguments the different rules are requiring so please read first [Rules](rules/).
- `#client_[must_be_the_same_with_the_field_name]` 
	- e.g. `$v->required(null, '#client_field1')->check('field1', '');`

```php
use AJD_validation\AJD_validation
$v = new AJD_validation;

$v->required(null, '#client_email')
	->email([], '#client_email')
	->in(['a@test.com', 'b@test.com'], '#client_email')
	->check('email', '');

	$clientSide = $v->getClientSide();

	echo '<pre>';
	print_r($clientSide);

	/*
		prints 
		Array
		(
		     [customJS] =>   
				 	function inRuleArray(value, haystack, identical)
				 	{
				 		for (var i in haystack) 
				 		{ 
				 			if( identical )
				 			{
				 				if (haystack[i] === value) return true; 
				 			}
				 			else
				 			{
				 				if (haystack[i] == value) return true; 
				 			}
				 		}

				 		return false;
				 	}

					window.Parsley.addValidator('inrule', {
						validate: function(value, requirement, obj) {
							var arr 		= requirement.split('|+');
							var identical 	= false;
							var elem 	= $(obj.element);
						 	var msg 	= $(obj.element).attr('data-parsley-in-message');
							
							if( elem.attr('data-parsley-inrule-identical') )
							{
								identical 	= true;
							}

							var check 	= inRuleArray(value, arr, identical);

							if( !check )
							{
								return $.Deferred().reject(msg);
							}

							return inRuleArray(value, arr, identical);
					},
					messages: {
						en: 'Email must be in { "a@test.com", "b@test.com" }.'
					}
				}); 
		    [rules] => Array
		        (
		        )

		    [messages] => Array
		        (
		        )

		    [email] =>                 data-parsley-required="true" 				data-parsley-required-message="The Email field is required" 	            data-parsley-type="email" 				data-parsley-type-message="The Email field must be a valid email." 
		)
	*/

		return $clientSide;
```
- To use with parsley
```html

<script type="text/javascript">
	$(function()
	{
		<?php echo $clientSide['customJs'] ?>
	});
</script>

<input type="text" <?php echo $clientSide['email'] ?> name="email">
```

2. JqueryValidation example
```php
use AJD_validation\AJD_validation

$v = new AJD_validation;

$v->required(null, '#client_email')
	->email([], '#client_email')
	->in(['a@test.com', 'b@test.com'], '#client_email')
	->check('email', '');

	echo '<pre>';
	$client = $v->getClientSide(true, \AJD_validation\Helpers\Client_side::JQ_VALIDATION);
	print_r($client);

	// prints
	/*
		Array
		(
		    [customJS] =>   
				 	function inRuleArray(value, haystack, identical)
				 	{
				 		for (var i in haystack) 
				 		{ 
				 			if( identical )
				 			{
				 				if (haystack[i] === value) return true; 
				 			}
				 			else
				 			{
				 				if (haystack[i] == value) return true; 
				 			}
				 		}

				 		return false;
				 	}

				 	jQuery.validator.addMethod('in', function(value, element, params) 
					{
						var arr 		= params[0].split('|+');
						var identical 	= params[1] || false;

						return this.optional(element) || inRuleArray(value, arr, identical);

					}, 'Email must be in { "a@test.com", "b@test.com" }.'); 
		    [rules] => Array
		        (
		            [email] => Array
		                (
		                    [required] => 1
		                    [email] => 1
		                    [in] => Array
		                        (
		                            [0] => a@test.com|+b@test.com
		                            [1] => true
		                        )

		                )

		        )

		    [messages] => Array
		        (
		            [email] => Array
		                (
		                    [required] => The Email field is required
		                    [email] => The Email field must be a valid email.
		                    [in] => Email must be in { "a@test.com", "b@test.com" }.
		                )

		        )

		)
	*/

		return $client
```
- To use with jqueryvalidation
```html
<?php 
	$clientSide = $client;

	unset($clientSide['customJS']);
?>

<script type="text/javascript">
	$().ready(function() {

		<?php 
			if(!empty($client['customJS']))
			{
				echo $client['customJS'];
			}
		?>

		$('#yourForm').validate(<?php echo json_encode($clientSide) ?>);
	});
</script>
```

3. Client Side per rules
```php
use AJD_validation\AJD_validation;

$v->required(null, '#client_email')
	->email([], '#client_email')
	->check('email', '');

	echo '<pre>';
	print_r($v->getClientSide(false));
/*
	prints
Array
(
    [customJS] => Array
        (
            [0] => 
            [1] => 
            [2] => 
		 	function inRuleArray(value, haystack, identical)
		 	{
		 		for (var i in haystack) 
		 		{ 
		 			if( identical )
		 			{
		 				if (haystack[i] === value) return true; 
		 			}
		 			else
		 			{
		 				if (haystack[i] == value) return true; 
		 			}
		 		}

		 		return false;
		 	}

			window.Parsley.addValidator('inrule', {
				validate: function(value, requirement, obj) {
					var arr 		= requirement.split('|+');
					var identical 	= false;
					var elem 	= $(obj.element);
				 	var msg 	= $(obj.element).attr('data-parsley-in-message');
					
					if( elem.attr('data-parsley-inrule-identical') )
					{
						identical 	= true;
					}

					var check 	= inRuleArray(value, arr, identical);

					if( !check )
					{
						return $.Deferred().reject(msg);
					}

					return inRuleArray(value, arr, identical);
			},
			messages: {
				en: 'Email must be in { "a@test.com", "b@test.com" }.'
			}
		});
        )

    [clientSideJson] => Array
        (
        )

    [clientSideJsonMessages] => Array
        (
        )

    [email] => Array
        (
            [required] =>                 data-parsley-required="true" 				data-parsley-required-message="The Email field is required"
            [email] => 	            data-parsley-type="email" 				data-parsley-type-message="The Email field must be a valid email."
            [in] =>                 data-parsley-inrule='a@test.com|+b@test.com'
                data-parsley-inrule-identical='true' 				data-parsley-in-message="Email must be in { "a@test.com", "b@test.com" }."
        )

)

*/
```
- You can read more about client side here: 
	- [Client Side](advance_usage/client_side.md)

## The Validator Object
- To get the validator object use:
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v->getValidator();

$v->getValidator()->required()->validate(''); // returns false
$v->getValidator()->required()->assertErr(''); // throws an Exception
```
- When using the Validator Object it exposes 
	* `->validate(mixed $value)` method which returns true/false if the rule or rules passes or not
	* `->assertErr(mixed $value)` method which will throw an Exception if the rule or rules validation fails
	* This object works similarly with [respect/validation](https://github.com/Respect/Validation)
	* This is useful for other rules and when using inside `->sometimes()` method
		- [Rules](rules/)
		- `->sometimes()` - [Scenarios](advance_usage/scenarios.md)

## An Advance example
- Let's say you're validating inputs of email but only the first input must be required, All must be a valid email address and must not repeat and you want to run valid email validation and not repeating validation if the user inputs a value. Then you want to change field name 'email' to 'Emails'
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v->required()
	->sometimes(function($value, $satisfier, $orig_field, $arrKey)
	{
		return $arrKey == 0;
	})
	->email()->sometimes()
  ->distinct()->sometimes()
  ->check('email|Emails', [
  	'email' => ['', '', '']
  ]);


/*prints error
	All of the required rules must pass for "Emails".
  - The Emails field is required at row 1.
*/

$v->required()
	->sometimes(function($value, $satisfier, $orig_field, $arrKey)
	{
		return $arrKey == 0;
	})
	->email()->sometimes()
  ->distinct()->sometimes()
  ->check('email|Emails', [
  	'email' => ['a', '', '']
  ]);

/*prints error
	All of the required rules must pass for "Emails".
  - The Emails field must be a valid email. at row 1.
*/

$v->required()
	->sometimes(function($value, $satisfier, $orig_field, $arrKey)
	{
		return $arrKey == 0;
	})
	->email()->sometimes()
  ->distinct()->sometimes()
  ->check('email|Emails', [
  	'email' => ['a@t.com', 'a', 's']
  ]);
/*prints error
	All of the required rules must pass for "Emails".
  - The Emails field must be a valid email. at row 2.
  - The Emails field must be a valid email. at row 3.
*/

$v->required()
	->sometimes(function($value, $satisfier, $orig_field, $arrKey)
	{
		return $arrKey == 0;
	})
	->email()->sometimes()
  ->distinct()->sometimes()
  ->check('email|Emails', [
  	'email' => ['a@t.com', 'a@t.com', 's']
  ]);
/*prints error
	All of the required rules must pass for "Emails".
  - The Emails has a duplicate value. at row 1.
  - The Emails has a duplicate value. at row 2.
  - The Emails field must be a valid email. at row 3.
*/

$v->required()
	->sometimes(function($value, $satisfier, $orig_field, $arrKey)
	{
		return $arrKey == 0;
	})
	->email()->sometimes()
  ->distinct()->sometimes()
  ->check('email|Emails', [
  	'email' => ['a@t.com', 'b@t.com', 's@t.com']
  ]);
/*prints none, validation passes
*/

$v->assert();
```

See also:

- [Filter Usage](filters.md)
- [Advance Usage](advance_usage/)
- [Rules](rules/)
- [Filters](filters/)
- [Alternative Usage](alternative_usage.md)
- [Client Side](advance_usage/client_side.md)