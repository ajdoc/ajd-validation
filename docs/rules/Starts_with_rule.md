# Starts with

- `starts_with()`
- `Starts_with_rule($startValue, $identical = false)`

Validates only if the value is at the start of the input.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->starts_with('ipsum')->check('starts_with_field', 'lorem'); // will put error in error bag
$v->getValidator()->starts_with('ipsum')->validate('lorem'); // false

$v->starts_with('ipsum')->check('starts_with_field', 'ipsum lorem '); // validation passes
$v->getValidator()->starts_with('ipsum')->validate(' ipsum lorem'); // true

$v->starts_with('ipsum')->check('starts_with_field', ['starts_with_field' => ['aa', 'ipsum']]); // validation fails for row 1, validation passes for row 2.
/*
	Outputs error
		All of the required rules must pass for "Starts with field".
  			- Starts with field must start with "ipsum". at row 1.
*/
$v->getValidator()->starts_with('ipsum')->validate(['ipsum', 'lorem']); // true
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
