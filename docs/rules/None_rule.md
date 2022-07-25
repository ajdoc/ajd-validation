# None

- `none()`
- `None_rule(array|Rule_interface $validators)`

Validates the input if none of the given validators validate.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->none([$v->getValidator()->digit(), $v->getValidator()->email()])
->check('none_field', 'a@test.com'); // outputs error in error bag
/*
	Outputs error
	All of the required rules must pass for "None field".
	  - None of these rules must pass for None field. 
	      - None field must contain only digits (0-9).
	      - The None field field must be valid email.. 
*/

$v 
->getValidator()
->none([$v->getValidator()->digit(), $v->getValidator()->email()])
->validate('a@test.com'); // false

$v->none([$v->getValidator()->digit(), $v->getValidator()->email()])
->check('none_field', 'a'); // validation passes

$v 
->getValidator()
->none([$v->getValidator()->digit(), $v->getValidator()->email()])
->validate('a'); // true


```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
