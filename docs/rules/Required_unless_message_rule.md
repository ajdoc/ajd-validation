# Required unless message

- `required_unless_message()`
- `Required_unless_message_rule(array|string $dependetFields, array $dependentValue = [], array $values = [])`

Validates the input only if all of the dependent field is equals to the dependent value and prints dependent field error.

This rule must use the `->checkDependent()` method

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$dependent_post_data = ['dependent_field' => 's', 'dependent_field2' => '', 'real_field' => ''];
$dependent_fields = ['dependent_field', 'dependent_field2'];
$dependent_values = ['dependent_field' => 'foo', 'dependent_field2' => 'bar'];

$v->required_unless_message(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->checkDependent('real_field', $dependent_post_data);  // will not validate because none of the dependent_post_data is equals to the dependent_values.

/*
	Outputs error
	All of the required rules must pass for "Real field".
  	- Real field is required when all "dependent_field, dependent_field2" is in "foo, bar".
*/

$v->getValidator()->required_unless_message(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->validate(''); // false. because none of the dependent_post_data is equals to the dependent_values.

$dependent_post_data = ['dependent_field' => 'foo', 'dependent_field2' => 'bar', 'real_field' => ''];
$dependent_fields = ['dependent_field', 'dependent_field2'];
$dependent_values = ['dependent_field' => 'foo', 'dependent_field2' => 'bar'];

$v->required_unless_message(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->checkDependent('real_field', $dependent_post_data);  // will output error because all of the dependent field is equal and real field is empty.

/*
	Outputs error
	All of the required rules must pass for "Real field".
  	- The "Real field" field is required
*/

$v->getValidator()->required_unless_message(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->validate(''); // false. because all of the dependent field is equal and real field is empty.

$dependent_post_data = ['dependent_field' => 'foo', 'dependent_field2' => 'bar', 'real_field' => 'a'];
$dependent_fields = ['dependent_field', 'dependent_field2'];
$dependent_values = ['dependent_field' => 'foo', 'dependent_field2' => 'bar'];

$v->required_unless_message(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->checkDependent('real_field', $dependent_post_data);  // validation passes because all of the dependent field is equal and real field is not empty.

$v->getValidator()->required_unless_message(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->validate('a'); // true. because all of the dependent field is equal and real field not empty.

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
