# Ends with

- `ends_with()`
- `Ends_with_rule($endValue, $identical = false)`

Validates only if the value is at the end of the input.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->ends_with('ipsum')->check('ends_with_field', 'lorem'); // will put error in error bag
$v->getValidator()->ends_with('ipsum')->validate('lorem'); // false

$v->ends_with('ipsum')->check('ends_with_field', 'lorem ipsum'); // validation passes
$v->getValidator()->ends_with('ipsum')->validate('lorem ipsum'); // true

$v->ends_with('ipsum')->check('ends_with_field', ['ends_with_field' => ['aa', 'ipsum']]); // validation fails for row 1, validation passes for row 2.
/*
	Outputs error
		All of the required rules must pass for "Ends with field".
  			- Ends with field must end with "ipsum". at row 1.
*/
$v->getValidator()->ends_with('ipsum')->validate(['lorem', 'ipsum']); // true
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
