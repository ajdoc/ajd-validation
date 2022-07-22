# File exists

- `file_exists()`
- `File_exists_rule()`

Validates whether the input value's files or directories exists.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->file_exists()->check('file_exists_field', 'foo.txt'); // will put error in error bag
$v->getValidator()->file_exists()->validate('foo.txt'); // false

$v->file_exists()->check('file_exists_field', new \SplFileInfo('foo.txt')); // will put error in error bag
$v->getValidator()->file_exists()->validate(new \SplFileInfo('foo.txt')); // false

$v->file_exists()->check('file_exists_field', new \SplFileInfo(__DIR__)); // validation passes
$v->getValidator()->file_exists()->validate(new \SplFileInfo(__DIR__)); // true

$v->file_exists()->check('file_exists_field', __FILE__); // validation passes
$v->getValidator()->file()->validate(__FILE__); // true

$v->file_exists()->check('file_exists_field', new \SplFileInfo(__FILE__)); // validation passes
$v->getValidator()->file_exists()->validate(new \SplFileInfo(__FILE__)); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
