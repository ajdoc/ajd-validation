# One or

- `one_or()`
- `One_or_rule(array|Rule_interface $validators)`

Validates the input if one of the given validators validate.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->one_or([$v->getValidator()->digit(), $v->getValidator()->email()])
->check('none_field', 'a'); // outputs error in error bag
/*
	Outputs error
	All of the required rules must pass for "None field".
		-   At least one of these rules must pass for "None field".
      		- "None field" must contain only digits (0-9).
      		- The "None field" field must be valid email.
*/

$v 
->getValidator()
->one_or([$v->getValidator()->digit(), $v->getValidator()->email()])
->validate('a'); // false

$v->one_or([$v->getValidator()->digit(), $v->getValidator()->email()])
->check('none_field', '1'); // validation passes

$v 
->getValidator()
->one_or([$v->getValidator()->digit(), $v->getValidator()->email()])
->validate('a@test.com'); // true


```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
