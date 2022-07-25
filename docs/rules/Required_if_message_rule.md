# Required if message

- `required_if_message()`
- `Required_if_message_rule(array|string $dependetFields, array $dependentValue = [], array $values = [])`

Validates the input only if one of the dependent field is equals to the dependent value and prints dependent field error.

This rule must use the `->checkDependent()` method

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$dependent_post_data = ['dependent_field' => 's', 'dependent_field2' => '', 'real_field' => ''];
$dependent_fields = ['dependent_field', 'dependent_field2'];
$dependent_values = ['dependent_field' => 'foo', 'dependent_field2' => 'bar'];

$v->required_if_message(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->checkDependent('real_field', $dependent_post_data);  // will not validate because none of the dependent_post_data is equals to the dependent_values but will output error.

/*
	Outputs error
	All of the required rules must pass for "Real field".
	  - Real field is required when either "dependent_field, dependent_field2" is "foo, bar". 
	   - The "dependent_field2" field is required
*/

$v->getValidator()->required_if_message(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->validate(''); // true. because none of the dependent_post_data is equals to the dependent_values.

$dependent_post_data = ['dependent_field' => 'foo', 'dependent_field2' => '', 'real_field' => ''];
$dependent_fields = ['dependent_field', 'dependent_field2'];
$dependent_values = ['dependent_field' => 'foo', 'dependent_field2' => 'bar'];

$v->required_if_message(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->checkDependent('real_field', $dependent_post_data);  // will output error because dependent_field is equals to $dependent_post_data['dependent_field'] which is foo and real field is empty.

/*
	Output error
	All of the required rules must pass for "Real field".
		- The "Real field" field is required

*/

$v->getValidator()->required_if_message(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->validate(''); // false. because dependent_field is equals to $dependent_post_data['dependent_field'] which is foo and real field is empty.

$dependent_post_data = ['dependent_field' => 'foo', 'dependent_field2' => '', 'real_field' => 'a'];
$dependent_fields = ['dependent_field', 'dependent_field2'];
$dependent_values = ['dependent_field' => 'foo', 'dependent_field2' => 'bar'];

$v->required_if_message(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->checkDependent('real_field', $dependent_post_data);  // validation passes because dependent_field is equals to $dependent_post_data['dependent_field'] which is foo and real field is not empty.

$v->getValidator()->required_if_message(
	$dependent_fields, $dependent_values, $dependent_post_data
)
->validate('a'); // true. because dependent_field is equals to $dependent_post_data['dependent_field'] which is foo and value is not empty.

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
