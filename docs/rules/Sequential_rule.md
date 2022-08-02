# Sequential

- `sequential()`
- `Sequential_rule(Rule_interface ...$rules)`

Validates the input sequentially, if first validation fails validation stops and prints the error and so on.

Can also validate set of rules by group please refer to example set 2.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v 
->sequential(
	$v->getValidator()
		->required()
		->minlength(2)
		->alpha('')
)
->check('sequential_field', ''); // validation fails
/*
	Outputs error 
	All of the required rules must pass for "Sequential field".
  	- The "Sequential field" field is required
*/
$v 
->getValidator()
->sequential(
	$v->getValidator()->invokable_required()->minlength(2)
)
->validate(''); // false


$v 
->sequential(
	$v->getValidator()
		->required()
		->minlength(2)
		->alpha()
)
->check('sequential_field', 'a'); // validation fails
/*
	Outputs error 
	All of the required rules must pass for "Sequential field".
  	- Sequential field" must be greater than or equal to 2.
*/


$v 
->sequential(
	$v->getValidator()
		->required()
		->minlength(2)
		->alpha()
)
->check('sequential_field', 'aa'); // validation passes

$v 
->getValidator()
->sequential(
	$v->getValidator()->required()->minlength(2)
)
->validate('aa'); // true


/*
	example set 2
*/
$v 
->sequential(
	$v
		->getValidator()
		->invokable_required()
		->minlength(2),
		
	$v
		->getValidator()
		->maxlength(5)
		->alpha(''),

	$v
		->getValidator()
		->uncompromised(),			
)
->check('sequential_check', ''); // validation fails
	
/*
	Outputs error
	All of the required rules must pass for "Sequential field".
	  -  The "Sequential field"* field is required.
	  - "Sequential field" must be greater than or equal to 2.
*/

/*
	example set 2
*/
$v 
->sequential(
	$v
		->getValidator()
		->invokable_required()
		->minlength(2),
		
	$v
		->getValidator()
		->maxlength(5)
		->alpha(''),

	$v
		->getValidator()
		->uncompromised(),			
)
->check('sequential_field', 'aa*aaa'); // validation fails

/*
	Outputs error
	All of the required rules must pass for "Sequential field".
	  -  "Sequential field" must be less than or equal to 5.
	  - "Sequential field" must contain only letters (a-z).
*/

/*
	example set 2
*/
$v 
->sequential(
	$v
		->getValidator()
		->invokable_required()
		->minlength(2),
		
	$v
		->getValidator()
		->maxlength(5)
		->alpha(''),

	$v
		->getValidator()
		->uncompromised(),			
)
->check('sequential_field', 'aa'); // validation fails

/*
	Outputs error
	All of the required rules must pass for "Sequential field".
  	-  The "Sequential field" field has appeared in a data leak.
*/

/*
	example set 2
*/
$v 
->sequential(
	$v
		->getValidator()
		->invokable_required()
		->minlength(2),
		
	$v
		->getValidator()
		->maxlength(5)
		->alpha(''),

	$v
		->getValidator()
		->uncompromised(),			
)
->check('sequential_field', 'ameac'); // validation passes

$v 
->getValidator()
->sequential(
	$v
		->getValidator()
		->invokable_required()
		->minlength(2),
		
	$v
		->getValidator()
		->maxlength(5)
		->alpha(''),

	$v
		->getValidator()
		->uncompromised(),			
)
->validate('ameac'); // returns true
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
