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

See also:

- [Advance Usage](advance_usage/)
- [Rules](rules/)