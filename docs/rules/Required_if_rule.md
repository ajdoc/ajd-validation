# Required if

- `required_if()`
- `Required_if_rule(array|string $dependetFields, array $dependentValue = [], array $values = [])`

Validates the input only if one of the dependent field is equals to the dependent value.

This rule must use the `->checkDependent()` method

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$dependent_post_data = ['dependent_field' => 's', 'dependent_field2' => '', 'real_field' => ''];
$dependent_fields = ['dependent_field', 'dependent_field2'];
$dependent_values = ['dependent_field' => 'foo', 'dependent_field2' => 'bar'];

$v->required_if(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->checkDependent('real_field', $dependent_post_data);  // will not validate because none of the dependent_post_data is equals to the dependent_values.

$v->getValidator()->required_if(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->validate(''); // true. because none of the dependent_post_data is equals to the dependent_values.

$dependent_post_data = ['dependent_field' => 'foo', 'dependent_field2' => '', 'real_field' => ''];
$dependent_fields = ['dependent_field', 'dependent_field2'];
$dependent_values = ['dependent_field' => 'foo', 'dependent_field2' => 'bar'];

$v->required_if(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->checkDependent('real_field', $dependent_post_data);  // will output error because dependent_field is equals to $dependent_post_data['dependent_field'] which is foo and real field is empty.

$v->getValidator()->required_if(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->validate(''); // false. because dependent_field is equals to $dependent_post_data['dependent_field'] which is foo and real field is empty.

$dependent_post_data = ['dependent_field' => 'foo', 'dependent_field2' => '', 'real_field' => 'a'];
$dependent_fields = ['dependent_field', 'dependent_field2'];
$dependent_values = ['dependent_field' => 'foo', 'dependent_field2' => 'bar'];

$v->required_if(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->checkDependent('real_field', $dependent_post_data);  // validation passes because dependent_field is equals to $dependent_post_data['dependent_field'] which is foo and real field is not empty.

$v->getValidator()->required_if(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->validate('a'); // true. because dependent_field is equals to $dependent_post_data['dependent_field'] which is foo and value is not empty.

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
