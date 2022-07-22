# Subdivision code

- `subdivision_code()`
- `Subdivision_code_rule(string $countryCode)`
- supports 'PH' only

Validates the input's subdivision country codes according to ISO 3166-2.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->subdivision_code('PH')->check('subdivision_code_field', 1); // will put error in error bag
$v->getValidator()->subdivision_code('PH')->validate(1); // false

$v->subdivision_code('PH')->check('subdivision_code_field', 'CEB'); // validation passes
$v->getValidator()->subdivision_code('PH')->validate('CEB'); // true

$v->subdivision_code('PH')->check('subdivision_code_field', 10); // will put error in error bag
$v->getValidator()->subdivision_code('PH')->validate(10); // false

$v->subdivision_code('PH')->check('subdivision_code_field', '10'); // validation passes
$v->getValidator()->subdivision_code('PH')->validate('10'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
