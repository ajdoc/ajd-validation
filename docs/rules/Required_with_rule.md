# Required with

- `required_with()`
- `Required_with_rule(array|string $dependetFields, array $dependentValue = [], array $values = [])`

Validates the input only if one of the dependent field is not empty.

This rule must use the `->checkDependent()` method

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$dependent_post_data = ['dependent_field' => '', 'dependent_field2' => '', 'real_field' => ''];
$dependent_fields = ['dependent_field', 'dependent_field2'];
$dependent_values = [];

$v->required_with(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->checkDependent('real_field', $dependent_post_data);  // will not validate because none of the dependent fields is not empty.

$v->getValidator()->required_with(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->validate(''); // false. because none of the dependent fields is not empty.

$dependent_post_data = ['dependent_field' => 'foo', 'dependent_field2' => '', 'real_field' => ''];
$dependent_fields = ['dependent_field', 'dependent_field2'];
$dependent_values = [];

$v->required_with(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->checkDependent('real_field', $dependent_post_data);  // will output error because one of the dependent fields is not empty and real field is empty.

$v->getValidator()->required_with(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->validate(''); // false. one of the dependent field is not empty and real field is empty.

$dependent_post_data = ['dependent_field' => 'foo', 'dependent_field2' => '', 'real_field' => 'a'];
$dependent_fields = ['dependent_field', 'dependent_field2'];
$dependent_values = [];

$v->required_with(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->checkDependent('real_field', $dependent_post_data);  // validation passes because one of the dependent fields is not empty and real field is not empty.

$v->getValidator()->required_with(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->validate('a'); // true. because one of the dependent fields is not empty and real field is not empty.

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
