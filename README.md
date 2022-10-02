# AJD Validation

Simple php validation and filtering library

## Description

A php validation and filtering library.

## Getting Started

### Dependencies

* egulias/email-validator : 2.1

### Installing

* composer require ajd/ajd-validation

## Authors

Contributors names and contact info

Aj Doc (thedoctorisin17@gmail.com)  

## Version History

* 0.1 (master)
    * Initial Release

## Documentation
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

## Passing multiple fields
- Useful when some fields have the same validation definition
- You can pass an array of fields in `$v->check([fields])` where the validation will try to find and map the field to the value if the value is a key value pair array. If the value is not an array it will just repeat the validation for the fields.
- **Do note this will combine both the promise and validation result for all the fields so if one the field fails the promise and validation result fails.**

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$arr3 = ['field1' => '', 'field2' => ''];
 
$v11 = $v
		->required()
		->minlength(2)
		->check(['field1', 'field2'], $arr3); //prints error
/*
All of the required rules must pass for "Field1".
  - The Field1 field is required
  - Field1 must be greater than or equal to 2. character(s). 
All of the required rules must pass for "Field2".
  - The Field2 field is required
  - Field2 must be greater than or equal to 2. character(s). 
*/
```

## Basic customization of error message
	
If for instance you want to customize the error message per rule this could be achived by `->[rulename](null, '@custom_error_Place your custom error message here')`
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

	$v 
	->minlength(2)
	->digit()
	->compare('==', 'b', '@custom_error_"b" is the value for middlename2 to be accepted.')
	->check('middlename2', 'a');

```
The rule compare will have this output error
```
All of the required rules must pass for "Middlename2".
  - Middlename2 must be greater than or equal to 2. character(s). 
  - Middlename2 must contain only digits (0-9).
  - "b" is the value for middlename2 to be accepted.
```

### Error Customization
- We can customize error per rule by using:
	- `$v->required()
		->getInstance()
		->setCustomErrorMessage([
			'overrideError' => 'override message'
			'appendError' => 'appended message',
		]);
	`
1. using `appendError` will append a message to the default error message.
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;
$v->required()
	->getInstance()
	->setCustomErrorMessage([
		'appendError' => 'appended message',
	])
	->check('test_format', [ 'test_format' => ['', '']]); //prints error
/*
All of the required rules must pass for "Test format".
  - The Test format field is required appended message.  at row 1.
  - The Test format field is required appended message.  at row 2.
*/
```
2. using `overrideError` will override the default error message.
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;
$v->required()
	->getInstance()
	->setCustomErrorMessage([
		'overrideError' => 'override message',
	])
	->check('test_format', [ 'test_format' => ['', '']]); //prints error
/*
All of the required rules must pass for "Test format".
  - override message at row 1.
  - override message at row 2.
*/
```
3. Combining `appendError` and `overrideError`.
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;
$v->required()
	->getInstance()
	->setCustomErrorMessage([
		'overrideError' => 'override message',
		'appendError' => 'appended message',
	])
	->check('test_format', [ 'test_format' => ['', '']]); //prints error
/*
All of the required rules must pass for "Test format".
  - override message appended message.  at row 1.
  - override message appended message.  at row 2.
*/
```
4. When defining `@custom_error_[message]` `overrideError` will not be used.
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;
$v->required(null, '@custom_error_Custom error message')
	->getInstance()
	->setCustomErrorMessage([
		'overrideError' => 'override message',
		'appendError' => 'appended message',
	])
	->check('test_format', [ 'test_format' => ['', '']]); //prints error
/*
All of the required rules must pass for "Test format".
  - Custom error message. appended message.  at row 1.
  - Custom error message. appended message.  at row 2.
*/
```

#### Using formatter to customize error message
- We can customize error using formater per rule by using:
	- `$v->required()
		->getInstance()
		->setFormatter(\Closure|\AJD_validation\Formatter\FormatterInterface::class, optional $formatterOptions);
	`
- `\Closure` and `(\AJD_validation\Formatter\FormatterInterface::class)->format()` will receive
	- `string $message` - The default rule error message.
	- `\AJD_validation\Contracts\Abstract_exceptions::class $exception` - The rule exception class.
	- `string $field = null` - the current field.
	- `array $satisfier = null` - the rule satisfier.
	- `mixed $value = null` - the current value given.
- `\Closure` and `(\AJD_validation\Formatter\FormatterInterface::class)->format()` could access additional formatterOptions thru `$this->getOptions()`
- Default options are:
	- `string cus_err` - The `@custom_error_[message]` passed.
	- `int valueKey` - The current index of the value.
	- `string clean_field` - The current formatted field name.
	- `string orig_field` - The current pre formatted field name.

1. `\Closure` example.
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;
$v->required()
	->getInstance()
	->setFormatter(function($message, $exceptionObj, $field, $satisfier = null, $value = null)
	{
		$realMessage = $this->getOptions()['cus_err'] ?: $message;
		
		return $realMessage.' custom at '.$this->getOptions()['valueKey'] + 1;
	})
	->check('test_format', [ 'test_format' => ['', '']]); //prints error
/*
All of the required rules must pass for "Test format".
  - The Test format field is required custom at 1 at row 1.
  - The Test format field is required custom at 2 at row 2.
*/
```
2. Using `Formatter Class` with Formatter Options and `$satisfier` example.
```php
use AJD_validation\AJD_validation;

namespace AJD_validation\Formatter;

use AJD_validation\Formatter\AbstractFormatter;
use AJD_validation\Contracts\Abstract_exceptions;

class RequiredFormatter extends AbstractFormatter
{
	public function format(string $messages, Abstract_exceptions $exception, $field = null, $satisfier = null, $value = null)
	{
		$options = $this->getOptions();
		$cnt = $options['valueKey'] ?? 0;

		$satis_str = $satisfier[0] ?? '';
		
		$cnt = $cnt + 1;
		$addtional_option = $options['addtional'] ?? '';
		
		$message = 'This :field is required at row {cnt} with a satisfier of. '.$satis_str.' '.$addtional_option.'.';
		$message = $exception->replaceErrorPlaceholder(['cnt' => $cnt], $message);

		return $message;
	}
}

$v = new AJD_validation;
$v->required(1)
	->getInstance()
	->setFormatter(\AJD_validation\Formatter\RequiredFormatter::class, ['addtional' => 'addtional'])
	->check('test_format', [ 'test_format' => ['', '']]); //prints error
/*
All of the required rules must pass for "Test format".
  - This Test format is required at row 1 with a satisfier of. 1 addtional. at row 1.
  - This Test format is required at row 2 with a satisfier of. 1 addtional. at row 2.
*/
```
3. Combining with `appnedError`.
```php
use AJD_validation\AJD_validation;

namespace AJD_validation\Formatter;

use AJD_validation\Formatter\AbstractFormatter;
use AJD_validation\Contracts\Abstract_exceptions;

class RequiredFormatter extends AbstractFormatter
{
	public function format(string $messages, Abstract_exceptions $exception, $field = null, $satisfier = null, $value = null)
	{
		$options = $this->getOptions();
		$cnt = $options['valueKey'] ?? 0;

		$satis_str = $satisfier[0] ?? '';
		
		$cnt = $cnt + 1;
		$addtional_option = $options['addtional'] ?? '';
		
		$message = 'This :field is required at row {cnt} with a satisfier of. '.$satis_str.' '.$addtional_option.'.';
		$message = $exception->replaceErrorPlaceholder(['cnt' => $cnt], $message);

		return $message;
	}
}

$v = new AJD_validation;
$v->required(1)
	->getInstance()
	->setFormatter(\AJD_validation\Formatter\RequiredFormatter::class, ['addtional' => 'addtional'])
	->setCustomErrorMessage([
		'appendError' => 'append message'
	])
	->check('test_format', [ 'test_format' => ['', '']]); //prints error
/*
All of the required rules must pass for "Test format".
  - This Test format is required at row 1 with a satisfier of. 1 addtional. append message.  at row 1.
  - This Test format is required at row 2 with a satisfier of. 1 addtional. append message.  at row 2.
*/
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
- We could reuse the rule definition by using `$v->setUpValidation((optional)'[unique-identfier]')->getValidationDefinition()` storing it in a variable then calling the variable like function the chain as shown in the example above.
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

6. Casting the valid value/values to a specific type.
```php
use AJD_validation\AJD_validation;

$v1 = $v
	->required()
	->minlength(2)
	->check('field1', 'aa')
	->getValidationResult()
	->castValueTo('int')
	->getFieldValue();

	print_r($v1);
	/*
		prints to 
		Array ( [0] => 0 )
	*/

	$result1 = $v
	->required()
	->minlength(2, true, true)
	->check('field1', '2022-01-01')
	->getValidationResult();

	$v1 = $result1->getValidationDefinition();

	$result1->castValueTo('DateTime')->getValue($storage);

	$v1()->check('field2', '')
		->getValidationResult()
		->getValue($storage);

	$v1()->check('field3', 'true')
		->getValidationResult()
		->castValueTo('bool')
		->getValue($storage);

	print_r($storage);

	/*
		prints 
		Array
		(
		    [field1] => DateTime Object
		        (
		            [date] => 2022-01-01 00:00:00.000000
		            [timezone_type] => 3
		            [timezone] => Europe/Berlin
		        )

		    [field3] => 1
		)
	*/

```

7. Check if the validation is valid.
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
All of the required rules must pass for "Field3".
  - The Field3 field is required at row 1.
  - The Field3 field is required at row 2.
  - The Field3 field must be a valid email. at row 1.
  - The Field3 field must be a valid email. at row 2.
All of the required rules must pass for "Field1".
  - The Field1 field is required at row 1.
  - The Field1 field is required at row 2.
  - Field1 must be greater than or equal to 2. character(s).  at row 1.
  - Field1 must be greater than or equal to 2. character(s).  at row 2.
All of the required rules must pass for "Field2".
  - The Field2 field is required at row 1.
  - The Field2 field is required at row 2.
  - Field2 must be greater than or equal to 3. character(s).  at row 1.
  - Field2 must be greater than or equal to 3. character(s).  at row 2.
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
- **Do note that AJD validation doesn't have a real client side/javascript version, it just somewhat sync/port some of its built in rules to the client side thru server side rendering. Currently it does not have support for javascript frameworks like Vue,React,Svelte and the like but this component gives you the ability to create/support such framework. Maybe thru sending the validation array as json response.**
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
	- [Client Side](docs/advance_usage/client_side.md)

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
		- [Rules](docs/rules/)
		- `->sometimes()` - [Scenarios](docs/advance_usage/scenarios.md)

## Conditionals
- Use this if you want to conditionally run a rule or a field-rule validation without breaking the chain.
- `->runif(bool|Closure $condtion, callable $callback = null, callable $default = null)` - if condition is true it will run the callback or continue the chain.
- `->runelse(bool|Closure $condtion, callable $callback = null, callable $default = null)` - if condition is false it will run the callback or continue the chain.
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v ->required()
	->runif(false)
		->minlength(2)
	->check('field1', '');
/*
	prints error
	All of the required rules must pass for "Field1".
  	- The Field1 field is required
*/

$v ->required()
	->runif(true)
		->minlength(2)
	->check('field1', '');

/*
	prints error
	All of the required rules must pass for "Field1".
  - The Field1 field is required
  - Field1 must be greater than or equal to 2. character(s).
*/

$v ->required()
	->runelse(function()
	{
		return true;
	})
		->minlength(2)
	->check('field1', '');
/*
	prints error
	All of the required rules must pass for "Field1".
  	- The Field1 field is required
*/

$v ->required()
	->runelse(function()
	{
		return false;
	})
		->minlength(2)
	->check('field1', '');

/*
	prints error
	All of the required rules must pass for "Field1".
	  - The Field1 field is required
	  - Field1 must be greater than or equal to 2. character(s).
*/
```

2. Conditionally assigning rules.
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v
->runif(true, 
	function($ajd)
	{
		$ajd->required();
	},
	function($ajd)
	{
		$ajd->minlength(2);
	}
)->check('field1', '');

/*
	prints error
	All of the required rules must pass for "Field1".
  	- The Field1 field is required
*/

$v
->runif(false, 
	function($ajd)
	{
		$ajd->required();
	},
	function($ajd)
	{
		$ajd->minlength(2);
	}
)->check('field1', '');

/*
	prints error
	All of the required rules must pass for "Field1".
  	- Field1 must be greater than or equal to 2. character(s).
*/
```
3. Conditionally running validations with events/promise example. 
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v->runif(true, function($ajd)
	{
		return $ajd->required()->check('field1', '');
	},
	function($ajd)
	{
		return $ajd->required()->check('field2', '');
	}
)
->passed(function()
{
	echo 'passed event ';
})
->fails(function($ajd, $field)
	{
		echo $field.' fails event <br/>';
	})
->done(function()
{
	echo 'promise resolved';
})
->otherwise(function()
{
	echo 'promise rejected <br/>';
});
/*
	prints error
	field1 fails event
	promise rejected
	All of the required rules must pass for "Field1".
	  - The Field1 field is required
*/

$v->runif(false, function($ajd)
	{
		return $ajd->required()->check('field1', '');
	},
	function($ajd)
	{
		return $ajd->required()->check('field2', '');
	}
)
->passed(function()
{
	echo 'passed event ';
})
->fails(function($ajd, $field)
{
	echo $field.' fails event <br/>';
})
->done(function()
{
	echo 'promise resolved';
})
->otherwise(function()
{
	echo 'promise rejected <br/>';
});

/*
	prints error
	field2 fails event
	promise rejected
	All of the required rules must pass for "Field2".
	  - The Field2 field is required
*/
```

4. Using with Validator Object
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$result = $v 
		->getValidator()
		->runif(false)
			->email()
		->runif(true)
			->minlength(50)
		->validate('a@t.com'); // returns false because it evaluated minlength(50) only

$result = $v 
		->getValidator()
		->runif(true)
			->email()
		->runif(false)
			->minlength(50)
		->validate('a@t.com'); // returns true because it evaluated email only and value is a valid email.

```

## Each

1. Use this if you want to manually traverse and apply the validation on a simple or nested array.
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$arr = [
	'first' => '',
	'second' => [
		'third' => '',
		'fourth' => [
			'',
			''
		],
		'fifth' => [
			'sixth'  => '',
			'seventh' => ''
		]
	]
];

$v->each([
	$v 
		->required()
		->minlength(2)
		->setUpValidation('first'),

	$v 
		->is_array()
		->setUpValidation('second'),

	$v->each(
		[
			$v 
				->required()
				->minlength(3)
				->setUpValidation('third'),

			$v 
				->is_array()
				->setUpValidation('fourth'),

			$v
				->each([
					$v 
						->required()
						->setUpValidation('0'),

					$v 
						->required()
						->minlength(4)
						->setUpValidation('1'),
				]),

			$v 
				->is_array()
				->setUpValidation('fifth'),

			$v->each([
				$v->required()
					->digit()
					->setUpValidation('sixth'),

				$v->required()
					->email()
					->setUpValidation('seventh')
			])
		]
	)
])->check($arr); //prints error 
/*
All of the required rules must pass for "First".
  - The First field is required.
  - First must be greater than or equal to 2. character(s). 
All of the required rules must pass for "Second.third".
  - The Second.third field is required.
  - Second.third must be greater than or equal to 3. character(s). 
All of the required rules must pass for "Fourth.0".
  - The Fourth.0 field is required.
  - Fourth.0 must be greater than or equal to 2. character(s). 
All of the required rules must pass for "Fourth.1".
  - The Fourth.1 field is required.
  - Fourth.1 must be greater than or equal to 2. character(s). 
All of the required rules must pass for "Fifth.sixth".
  - The Fifth.sixth field is required.
  - Fifth.sixth must contain only digits (0-9).
All of the required rules must pass for "Fifth.seventh".
  - The Fifth.seventh field is required.
  - The Fifth.seventh field must be a valid email.
*/
```
- `$v->setUpValidation('same_array_key_on_array')` name must have the corresponding array key on the array.
- every time you call/nest `$v->each()` it will traverse down a level on the array.

2. Using closures inside `$v->each()`.
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$arr = [
	'first' => '',
	'second' => [
		'third' => '',
		'fourth' => [
			'',
			''
		],
		'fifth' => [
			'sixth'  => '',
			'seventh' => ''
		]
	]
];

$v->each([
	$v 
		->required()
		->minlength(2)
		->setUpValidation('first'),

	$v 
		->is_array()
		->setUpValidation('second'),

	$v->each(
		[
			$v 
				->required()
				->minlength(3)
				->setUpValidation('third'),

			$v 
				->is_array()
				->setUpValidation('fourth'),

			$v
			->each([
				function($ajd, $val, $field)
				{
					$options = $this->getOptions();
					$parentField = $options['parentField'];
					$realField = $options['realField'];
					
					if($parentField == 'fourth')
					{
						return $ajd 
						->required()
						->minlength(2)
						->check($realField, $val);
					}
				}
			]),

			$v 
				->is_array()
				->setUpValidation('fifth'),

			$v->each([
				$v->required()
					->digit()
					->setUpValidation('sixth'),

				$v->required()
					->email()
					->setUpValidation('seventh')
			])
		]
	)
])->check($arr); //prints error
/*
All of the required rules must pass for "First".
  - The First field is required.
  - First must be greater than or equal to 2. character(s). 
All of the required rules must pass for "Second.third".
  - The Second.third field is required.
  - Second.third must be greater than or equal to 3. character(s). 
All of the required rules must pass for "Fourth.0".
  - The Fourth.0 field is required.
  - Fourth.0 must be greater than or equal to 2. character(s). 
All of the required rules must pass for "Fourth.1".
  - The Fourth.1 field is required.
  - Fourth.1 must be greater than or equal to 2. character(s). 
All of the required rules must pass for "Fifth.sixth".
  - The Fifth.sixth field is required.
  - Fifth.sixth must contain only digits (0-9).
All of the required rules must pass for "Fifth.seventh".
  - The Fifth.seventh field is required.
  - The Fifth.seventh field must be a valid email.
*/
```
- Using closure will automatically iterate the array on that level.
- Closures will receive 
	1. `$ajd` - \AJD_validation\AJD_validation instance.
	2. `$val` - current array value.
	3. `$field` - current array key.
- Closures can return 
	1. `$v->each()` which is a `\AJD_validation\Combinators\Each` instance.
	2. `$v->required()->check()` which is  `\AJD_validation\Asycn\PromiseValidator` instance.
	3. `$v->required()->setUpValidation('same_array_key_on_array')` which is  `\AJD_validation\Asycn\ValidationResult` instance.
- inside a Closure you could use the following method
	1. `$this->getParent()` which will return the parent `\AJD_validation\Combinators\Each` instance.
	2. `$this->getOptions()` which will return an array of useful variables.
		- `$realField` - the concatenated field name of `parent field/parent array key.'.'.current field/current array key.`
		- `$cnt` - the current index of the array.
		- `$ruleIndex` - the current rule index.
		- `$values` - the parent array.
		- `$parentField` - the parent array key/field.

3. Example of using closure for auto iteration and some conditional validation.
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$arr2 = [
		'first' => [
			[
				'0',
				'0'
			],
			[
				'0'
			]
		],
		'second' => [
			[
				'',
				'no'
			],
			[
				'yes'
			]
		]
	];

$v->each([
	function($ajd, $val, $field)
	{
		return $ajd
			->required()
			->is_array()
			->check($field, $val, false);
	},
	$v->each([
		function($ajd, $val, $field)
		{
			return $ajd
			->required()
			->is_array()
			->check($field, $val, false);
		},
		$v->each([function($ajd, $val, $field)
		{
			$firstParent = $this->getParent()->getOptions()['parentField'];
			$parentOpt = $this->getParent()->getOptions();
			$parentCnt = $parentOpt['cnt'];
			$option = $this->getOptions();
			$cnt = $option['cnt'];
			$realField = $option['realField'];
			$arr2 = $this->getOrigValue();

			$checkSecond = $arr2['second'][$parentCnt][$cnt];
		
			return $ajd->required()
						->sometimes(function() use($checkSecond, $firstParent)
						{
							return $checkSecond == 'yes' || $firstParent == 'second';
						})
						->check($firstParent.'.'.$realField, $val);
			
		}])
	])
])->check($arr2); // prints error
/*
All of the required rules must pass for "First.1.0".
  - The First.1.0 field is required.
All of the required rules must pass for "Second.0.0".
  - The Second.0.0 field is required.
*/
```
- On the first rule/closure we are simply  validating if array keys `first` and `second` are indeed an array and are not empty.
- On the next rule/closure we go one level down and now iterating the two arrays in `first` and `second` and again validating if they are indeed an array and are not empty.
- On the next rule/closure we again go one level down using `$v->each()` and now iterating inside the two array for each `first` and `second`. Inside this level we then stated that we would require each element if `second` array values is `yes` or if that element comes from `second` array.
- Since `Second.1.0` == `yes` and `First.1.0` is zero we print error.
- Since `Secnod.0.0` == `0` we print error.

4. Appending error
- Useful when you want to show on which row the error happened.
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;
$v->each([
	function($ajd, $val, $field)
	{
		$options = $this->getOptions();

		return $ajd->required()
				->getInstance()
				->setCustomErrorMessage([
					'appendError' => 'at row '.($options['cnt'] + 1)
				])
			->minlength(2)
				->getInstance()
				->setCustomErrorMessage([
					'appendError' => 'at row '.($options['cnt'] + 1)
				])
			->check('test.'.$field, $val);
	}
])
->check([
'', '', ''
]); // prints error
/*
All of the required rules must pass for "Test.0".
  - The Test.0 field is required at row 1. 
  - Test.0 must be greater than or equal to 2. character(s) at row 1. 
All of the required rules must pass for "Test.1".
  - The Test.1 field is required at row 2. 
  - Test.1 must be greater than or equal to 2. character(s) at row 2. 
All of the required rules must pass for "Test.2".
  - The Test.2 field is required at row 3. 
  - Test.2 must be greater than or equal to 2. character(s) at row 3. 
*/
```

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
* [Filter Usage](docs/filters.md)

* See also:
	- [Rules](docs/rules/)
	- [Filters](docs/filters/)
	- [Advance Usage](docs/advance_usage/)
	- [Custom validation](docs/custom_validations.md)
	- [Client Side](docs/advance_usage/client_side.md)
	- [Package Development](docs/package_development.md)
	- [Alternative Usage](docs/alternative_usage.md)
	

## Packages
* [AJD Validation additonal rules](https://github.com/ajdoc/ajd-validation-additional-rules)
* [AJD Validation metadata](https://github.com/ajdoc/ajd-validation-metadata)
* [AJD Validation respect adapter](https://github.com/ajdoc/ajd-validation-respect-adapter)

## Acknowledgments

Inspiration, code snippets, etc.
* [respect/validation](https://github.com/Respect/Validation)
* [reactphp/promise](https://github.com/reactphp/promise)
