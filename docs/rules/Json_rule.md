# Json

- `json()`
- `Json_rule()`

Validates whether the input value is a valid JSON.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->json()->check('json_field', '{"foo":bar}'); // will put error in error bag
$v->getValidator()->json()->validate('{"foo":bar}'); // false

$v->json()->check('json_field', '{"foo":"bar"}'); // validation passes
$v->getValidator()->json()->validate('{"foo":"bar"}'); // true

$v->json()->check('json_field', '{"foo":1}'); // validation passes
$v->getValidator()->json()->validate('{"foo":1}'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
