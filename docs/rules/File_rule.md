# File

- `file()`
- `File_rule()`

Validates whether the input value is a valid file.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->file()->check('file_field', __DIR__); // will put error in error bag
$v->getValidator()->file()->validate(__DIR__); // false

$v->file()->check('file_field', new \SplFileInfo(__DIR__)); // will put error in error bag
$v->getValidator()->file()->validate(new \SplFileInfo(__DIR__)); // false

$v->file()->check('file_field', __FILE__); // validation passes
$v->getValidator()->file()->validate(__FILE__); // true

$v->file()->check('file_field', new \SplFileInfo(__FILE__)); // validation passes
$v->getValidator()->file()->validate(new \SplFileInfo(__FILE__)); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
