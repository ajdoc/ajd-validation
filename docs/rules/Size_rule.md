# Size

- `size()`
- `Size_rule(int $size)`

Validates the input value's size/length.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

/*
	Third paramater in ->checks('size_field', [1,2], false) disables auto array checking
*/
$v->size(1)->check('size_field', [1,2], false); // will put error on error bag
$v->getValidator()->size(1)->validate([1,2]); // false

$v->size(1)->check('size_field', [1], false); // validation passes
$v->getValidator()->size(1)->validate([1]); // true

$v->size(1)->check('size_field', 'aa'); // will put error on error bag
$v->getValidator()->size(1)->validate('aa'); // false

$v->size(1)->check('size_field', 'a'); // validation passes
$v->getValidator()->size(1)->validate('a'); // true

$v->size(1)->check('size_field', 11); // will put error on error bag
$v->getValidator()->size(1)->validate(11); // false

$v->size(1)->check('size_field', 1); // validation passes
$v->getValidator()->size(1)->validate(1); // true

$obj = new StdClass;
$obj->foo = '1';
$obj->bar = '1';

$v->size(1)->check('size_field', $obj); // will put error on error bag
$v->getValidator()->size(1)->validate($obj); // false

$obj = new StdClass;
$obj->foo = '1';

$v->size(1)->check('size_field', $obj); // validation passes
$v->getValidator()->size(1)->validate($obj); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
