# Required without all

- `required_without_all()`
- `Required_without_all_rule(array|string $dependetFields, array $dependentValue = [], array $values = [])`

Validates the input only if all of the dependent field is empty.

This rule must use the `->checkDependent()` method

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$dependent_post_data = ['dependent_field' => '', 'dependent_field2' => 'a', 'real_field' => ''];
$dependent_fields = ['dependent_field', 'dependent_field2'];
$dependent_values = [];

$v->required_without_all(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->checkDependent('real_field', $dependent_post_data);  // will not validate because not all of the dependent fields is empty.

$v->getValidator()->required_without_all(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->validate(''); // false. because not all of the dependent fields is empty.

$dependent_post_data = ['dependent_field' => '', 'dependent_field2' => '', 'real_field' => ''];
$dependent_fields = ['dependent_field', 'dependent_field2'];
$dependent_values = [];

$v->required_without_all(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->checkDependent('real_field', $dependent_post_data);  // will output error because all of the dependent fields is empty and real field is empty.

$v->getValidator()->required_without_all(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->validate(''); // false. because all of the dependent field is empty and real field is empty.

$dependent_post_data = ['dependent_field' => '', 'dependent_field2' => '', 'real_field' => 'a'];
$dependent_fields = ['dependent_field', 'dependent_field2'];
$dependent_values = [];

$v->required_without_all(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->checkDependent('real_field', $dependent_post_data);  // validation passes because all of the dependent fields is empty and real field is not empty.

$v->getValidator()->required_without_all(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->validate('a'); // true. because all of the dependent fields is empty and real field is not empty.

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
