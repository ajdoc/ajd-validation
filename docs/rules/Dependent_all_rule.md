# Dependent all

- `dependent_all()`
- `Dependent_all_rule($dependentFields, Rule_interface $checkValidator, Rule_interface $validator, array $dependentValue = array(), array $values = array())`

Validates the input using the `$validator` only if all of the dependent field passes the `$checkValidator`.

This rule must use the `->checkdependent_all()` method

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$dependent_arr1 = [
	'dependent_field' => '',
	'dependent_field2' => '',
	'check_dependent_field' => 'a'
];

$dependent_field1 = [
	'dependent_field', 'dependent_field2'
];

$dependent_values1 = [];

$checkValidator1 = $v->getValidator()->required()->digit();

$fieldValidator1 = $v->getValidator()->required()->alpha();

$v 
	->dependent_all(
		$dependent_field1, 
		$checkValidator1,
		$fieldValidator1,
		$dependent_values1, 
		$dependent_arr1
	)
	->checkDependent('check_dependent_field', $dependent_arr1) // validation of check_dependent_field will not run because not all of the dependent field passes the $checkValidator.

/*
	Outputs error
	All of the required rules must pass for "Check dependent field".
	  - Check dependent field is validated when all "dependent_field, dependent_field2" passes all the required rules. 
	   - The "dependent_field" field is required
	    - "dependent_field" must contain only digits (0-9).
	    - 
	    - The "dependent_field2" field is required
	    - "dependent_field2" must contain only digits (0-9).. 
*/

$v 
->getValidator()
->dependent_all(
	$dependent_field1, 
	$checkValidator1,
	$fieldValidator1,
	$dependent_values1, 
	$dependent_arr1
)
->validate(''); // false. because not all of the dependent field passes the $checkValidator.

$dependent_arr1 = [
	'dependent_field' => '1',
	'dependent_field2' => '1',
	'check_dependent_field' => ''
];

$dependent_field1 = [
	'dependent_field', 'dependent_field2'
];

$dependent_values1 = [];

$checkValidator1 = $v->getValidator()->required()->digit();

$fieldValidator1 = $v->getValidator()->required()->alpha();

$v 
	->dependent_all(
		$dependent_field1, 
		$checkValidator1,
		$fieldValidator1,
		$dependent_values1, 
		$dependent_arr1
	)
	->checkDependent('check_dependent_field', $dependent_arr1) // validation of check_dependent_field will run because all of the dependent field passes the $checkValidator.

/*
	Outputs error
	All of the required rules must pass for "Check dependent field".
	  - Data validation failed for "Check dependent field"
	    - The "Check dependent field" field is required
	    - "Check dependent field" must contain only letters (a-z).

	If check_dependent_field value is 'a' validation passes and no error will be printed
*/

$v 
->getValidator()
->dependent_all(
	$dependent_field1, 
	$checkValidator1,
	$fieldValidator1,
	$dependent_values1, 
	$dependent_arr1
)
->validate(''); // false. but value is empty. but if value is 'a' returns true.

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
