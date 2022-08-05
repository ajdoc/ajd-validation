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

### Sometimes using with a logic or validator
- We can also pass an ajd validation logic or ajd validation validator in sometimes 
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

	try 
	{
		/* example 1 */
		$v

		->Srequired()
			->field('username2')->sometimes($v->Lgfirst(true)->wrapLogic())
				->minlength(2)
				->alpha()
			->field('fname2')
				->minlength(1)->sometimes($v->getValidator()->digit())
		->eSrequired()

		->checkGroup([
			'username2' => 'a',
			'fname2' => '',
			
		]);

		/* example 2 */

		$v 
			->required()
			->minlength(3)->sometimes($v->getValidator()->required_allowed_zero()->digit())
			->check('middlename2', 'aa');

		$v->assert();
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}

```
* Example 1 will only output if field username2 sometimes logic returns true it will run the field validation because that First_logic returns true you may check src/AJD_validation/Logics/First_logic.php

* Example 1's field fname2 minlength rule will only run if value is digit.

* Example 2's field middlename2 minlength rule will only run if value is required but allows zero meaning if value zero required_allowed_zero is true and if value is digit, in this case since the value is `aa` minlength rule will not run

### Groupings
- We can also group set of rules and tell the validator to run a specific group only
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v 
	->required(null, '@custom_error_Field is required.')->groups(['t1'])
	->minlength(3)->groups(['t1'])


	->maxlength(5)->groups('t2')
	->alnum(['*', '&'])->groups('t2')

	->uncompromised()->groups('t3')

	->useGroupings(['t2'])
	->check('grouping_field', ''); // validation fails
/*
	Outputs error
	All of the required rules must pass for "Grouping field".
  		- Grouping field must contain only letters (a-z), digits (0-9) and ""*&"".

*/
```
- In the above example alnum and maxlength validation only run since we told that only use groups `t2`.

Example using alternative syntax

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v 
->Srequired()->groups('t1')
	->Sminlength(2)->groups('t2')
		->field('field_group1')
		->field('field_group2')
	->eSminlength()
->eSrequired()
->useGroupings(['t2'])
->checkGroup([
	'field_group1' => '',
	'field_group2' => '',
]); // validation fails

/*
	Outputs error
	All of the required rules must pass for "Field group1".
	  - Field group1 must be greater than or equal to 2. character(s). 
	All of the required rules must pass for "Field group2".
	  - Field group2 must be greater than or equal to 2. character(s). 

*/
```
### Grouping Sequence
- you could also define the sequence of how the grouping will run.
- this mean that the validator will run the group sequentially and if one grouping fails validation will stop there.

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

/*
	example 1
*/
$v 
		->required(null, '@custom_error_Field is required.')->groups(['t1'])
		->minlength(3)->groups(['t1'])

		
		->maxlength(5)->groups('t2')
		->alnum(['*', '&'])->groups('t2')

		->uncompromised()->groups('t3')

		->useGroupings($v->createGroupSequence(['t1', 't2', 't3']))
		->check('grouping_field', ''); // validation fails 
/*
	Outputs error 
	All of the required rules must pass for "Grouping field".
	  - Field is required.
	  - Grouping field must be greater than or equal to 3. character(s). 
*/

/*
	example 2
*/
$v 
		->required(null, '@custom_error_Field is required.')->groups(['t1'])
		->minlength(3)->groups(['t1'])

		
		->maxlength(5)->groups('t2')
		->alnum(['*', '&'])->groups('t2')

		->uncompromised()->groups('t3')

		->useGroupings($v->createGroupSequence(['t1', 't2', 't3']))
		->check('grouping_field', 'aa***--'); // validation fails 
/*
	Outputs error 
	All of the required rules must pass for "Grouping field".
	  - Grouping field must be less than or equal to 5. character(s). 
	  - Grouping field must contain only letters (a-z), digits (0-9) and ""*&"".
*/

/*
	example 3
*/
$v 
		->required(null, '@custom_error_Field is required.')->groups(['t1'])
		->minlength(3)->groups(['t1'])

		
		->maxlength(5)->groups('t2')
		->alnum(['*', '&'])->groups('t2')

		->uncompromised()->groups('t3')

		->useGroupings($v->createGroupSequence(['t1', 't2', 't3']))
		->check('grouping_field', 'aaaaa'); // validation fails 
/*
	Outputs error 
	All of the required rules must pass for "Grouping field".
  		- The Grouping field field has appeared in a data leak.
*/

/*
	example 4
*/
$v 
		->required(null, '@custom_error_Field is required.')->groups(['t1'])
		->minlength(3)->groups(['t1'])

		
		->maxlength(5)->groups('t2')
		->alnum(['*', '&'])->groups('t2')

		->uncompromised()->groups('t3')

		->useGroupings($v->createGroupSequence(['t1', 't2', 't3']))
		->check('grouping_field', 'ame*&'); // validation passes 

```

## Using alternative syntax in Group Sequence
**Note: Currently using Group Sequence in alternative syntax does not work well. You can't mix and match field. So it is recommended to use the normal/basic syntax for group sequencing.**

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

/*
	Example 1
*/
$v 
->Srequired(null,  AJD_validation::LOG_AND)->groups('t1')
	->Sminlength(2, AJD_validation::LOG_AND)->groups('t2')
		->field('field_group1')
			->alpha()->groups('t3')
		->field('field_group2')
	->eSminlength()
->eSrequired()

->useGroupings($v->createGroupSequence(['t1', 't2', 't3']))
->checkGroup([
	'field_group1' => ['field_group1' => ['a', 'a']],
	'field_group2' => ['field_group2' => ['', '']],
]); // validation fails
/*
	Outputs error
	All of the required rules must pass for "Field group1".
	  - The Field group1 field is required at row 1.
	  - The Field group1 field is required at row 2.
	All of the required rules must pass for "Field group2".
	  - The Field group2 field is required at row 1.
	  - The Field group2 field is required at row 2.
*/

/*
	Example 2
*/
$v 
->Srequired(null,  AJD_validation::LOG_AND)->groups('t1')
	->Sminlength(2, AJD_validation::LOG_AND)->groups('t2')
		->field('field_group1')
			->alpha()->groups('t3')
		->field('field_group2')
	->eSminlength()
->eSrequired()

->useGroupings($v->createGroupSequence(['t1', 't2', 't3']))
->checkGroup([
	'field_group1' => ['field_group1' => ['a', 'a-']],
	'field_group2' => ['field_group2' => ['', 'a-']],
	
]); // validation fails
/*
	Outputs error 
	All of the required rules must pass for "Field group1".
	  - Field group1 must be greater than or equal to 2. character(s).  at row 1.
	  - Field group1 must contain only letters (a-z). at row 2.
	All of the required rules must pass for "Field group2".
	  - The Field group2 field is required at row 1.
*/

```
- In example 1 only `required`error prints because we told that sequence will group `t1` will be first.

- In example 2 `field group 1 row 1` triggers `minlength` error, `field group 1 row 2` triggers `alpha error`, `field group 2 row 1` trigger `required` error because of the sequence and `field group 2 row 2` passes because there is no `alpha` rule for `field group 2`.

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;
/*
	Example 3
	this will not output the desired result does not output anything
*/
$v 
->Srequired(null,  AJD_validation::LOG_OR)->groups('t1')
	->Sminlength(2, AJD_validation::LOG_AND)->groups('t2')
		->field('field_group1')
			->alpha()->groups('t3')
		->field('field_group2')
	->eSminlength()
->eSrequired()

->useGroupings($v->createGroupSequence(['t1', 't2', 't3']))
->checkGroup([
	'field_group1' => ['field_group1' => ['a', 'a']],
	'field_group2' => ['field_group2' => ['', '']],
]); 
```
- Example 3 is an example where group sequence will not work with alternative syntax because of different rule logics in the parent rules `required` and `minlength`.
- But if both rule `required` and `minlength` are `AJD_validation::LOG_OR` or `AJD_validation::LOG_AND` group sequencing works.

See also:
- [Async](async.md)
- [Alternative Usage](../alternative_usage.md)
- [Usage](../usage.md)
- [Event and Promise](events_promises.md)