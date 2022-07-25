# Required without all message

- `required_without_all_message()`
- `Required_without_all_message_rule(array|string $dependetFields, array $dependentValue = [], array $values = [])`

Validates the input only if all of the dependent field is empty and prints dependent field error.

This rule must use the `->checkDependent()` method

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$dependent_post_data = ['dependent_field' => '', 'dependent_field2' => 'a', 'real_field' => ''];
$dependent_fields = ['dependent_field', 'dependent_field2'];
$dependent_values = [];

$v->required_without_all_message(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->checkDependent('real_field', $dependent_post_data);  // will not validate because not all of the dependent fields is empty.
/*
	Outputs error
	All of the required rules must pass for "Real field".
  	- Real field is required when none "dependent_field, dependent_field2" are present.
*/

$v->getValidator()->required_without_all_message(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->validate(''); // false. because not all of the dependent fields is empty.

$dependent_post_data = ['dependent_field' => '', 'dependent_field2' => '', 'real_field' => ''];
$dependent_fields = ['dependent_field', 'dependent_field2'];
$dependent_values = [];

$v->required_without_all_message(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->checkDependent('real_field', $dependent_post_data);  // will output error because all of the dependent fields is empty and real field is empty.
/*
	Outputs error
	All of the required rules must pass for "Real field".
  	- The "Real field" field is required
*/

$v->getValidator()->required_without_all_message(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->validate(''); // false. because all of the dependent field is empty and real field is empty.

$dependent_post_data = ['dependent_field' => '', 'dependent_field2' => '', 'real_field' => 'a'];
$dependent_fields = ['dependent_field', 'dependent_field2'];
$dependent_values = [];

$v->required_without_all_message(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->checkDependent('real_field', $dependent_post_data);  // validation passes because all of the dependent fields is empty and real field is not empty.

$v->getValidator()->required_without_all_message(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->validate('a'); // true. because all of the dependent fields is empty and real field is not empty.

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
