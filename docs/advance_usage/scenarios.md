# Scenarios
- Rule or Field scenario could be trigger by a specific scenario.

## On
- When defined a rule or field will only do the validation once the scenario is trigger.

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

	try 
	{
		$v->trigger('add');	
		/* example 1 */
		$v

		->Srequired()
			->field('username')->on('edit')
				->minlength(2)
				->alpha()
			->field('fname')
				->minlength(1)->on('edit')
		->eSrequired()

		->checkGroup([
			'username' => 'a',
			'fname' => '',
			
		]);

		/* example 2 */

		$v 
			->required()->on('edit')
			->minlength(1)
			->check('middlename');

		$v->assert();
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}

```

Example 1 will not output any error message because we defined that username field validation will ony trigger once edit and for field fname minlength validation will only trigger when edit if we change `$v->on('add')`. Error Output will be

```
All of the required rules must pass for "Username".
  - Username must be greater than or equal to 2. character(s). 
All of the required rules must pass for "Fname".
  - Fname must be greater than or equal to 1. character(s). 
```
Example 2 will out put erorr.
```
All of the required rules must pass for "Middlename".
  - Middlename must be greater than or equal to 1. character(s). 
```

When we change to `$v->on('add')` error output will be.

```
All of the required rules must pass for "Middlename".
  - The Middlename field is required
  - Middlename must be greater than or equal to 1. character(s). 
```
## Sometimes
- When defined a rule or field will only be validated if the value is present and not empty
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

	try 
	{
		/* example 1 */
		$v

		->Srequired()->sometimes()
			->field('username2')->sometimes()
				->minlength(2)
				->alpha()
			->field('fname2')
				->minlength(1)->sometimes()
		->eSrequired()

		->checkGroup([
			'username2' => '',
			'fname2' => '',
			
		]);

		/* example 2 */

		$v 
			->required()
			->minlength(1)->sometimes()
			->check('middlename');

		$v->assert();
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}

```

Example 1 will not output any error because we define rule grouping required will only be validated when field value is present and not empty.

But if we remove `->sometimes on ->Srequired()` error output will be.
```
All of the required rules must pass for "Fname2".
  - The Fname2 field is required
```
This is because we define that field username will only be validated if the value is present and not empty and fname field minlength rule will only be validated if value is present and not empty

Example 2 error out put will be 
```
All of the required rules must pass for "Middlename2".
  - The Middlename2 field is required
```
This is because we defined that minlength rule will only be validated if value is present and not empty

### Sometimes using with a closure
- We can also pass a closure inside `->sometimes()` to make a custom logic to initiate a validation. Closure must return a boolean true/false.

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

	try 
	{
		/* example 1 */
		$v

		->Srequired()
			->field('username2')->sometimes(function($value = null, $field, $details = [])
				{
					return $value == 'a';
				})
				->minlength(2)
				->alpha()
			->field('fname2')
				->minlength(1)->sometimes(function($value = null, $satisfier = null, $field)
				{
					return true;
				})
		->eSrequired()

		->checkGroup([
			'username2' => 'a',
			'fname2' => '',
			
		]);

		/* example 2 */

		$v 
			->required()
			->minlength(3)->sometimes(function($value = null, $satisfier = null, $field)
				{
					return strlen($value) == 2;
				})
			->check('middlename2', 'aa');

		$v->assert();
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}

```
* ->sometimes(string|\Closure)
	
	Will trigger once the value is present and not empty or closure returns true.

		- Rule sometimes
			1. [Closure] for first paramater. Closure will receive the following
				- $value - current value.
				- $satisfier - current rule satisfier e.g. minlength's allowed length value.
				- $field - current field.

		- Field sometimes
			1. [Closure] for first paramater. Closure will receive the following
				- $value - current value.
				- $field - current field.
				- $details - current field details. 

Example 1 will output errors
```
All of the required rules must pass for "Username2".
  - Username2 must be greater than or equal to 2. character(s). 
All of the required rules must pass for "Fname2".
  - The Fname2 field is required
  - Fname2 must be greater than or equal to 1. character(s). 
```
This is because we defined on field username 2 `->sometimes()` that we will validate the field if `$value == 'a'` and minlength error only prints because required and alpha rule passess

Example 2 will output error becase we defined that we will validate minlength rule if value `strlen($value) == 2` which passes
```
All of the required rules must pass for "Middlename2".
  - Middlename2 must be greater than or equal to 3. character(s). 
```

See also:
- [Async](async.md)
- [Alternative Usage](../alternative_usage.md)
- [Usage](../usage.md)
- [Scenarios](scenarios.md)
- [Event and Promise](events_promises.md)