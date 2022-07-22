# Country Code

- `country_code()`
- `Country_code_rule(string $set)`
- ISO 3166-1 alpha-2 ('alpha-2')
- ISO 3166-1 alpha-3 ('alpha-3')
- ISO 3166-1 numeric ('numeric')

Validates whether the input is a country code in ISO 3166-1 standard.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->country_code('alpha-2')->check('country_code_field', 'AAA'); // will put error in error bag
$v->getValidator()->country_code('alpha-2')->validate('AAA'); // false

$v->country_code('alpha-2')->check('country_code_field', 'AD');   // validation passes
$v->getValidator()->country_code('alpha-2')->validate('AD'); // true

$v->country_code('alpha-2')->check('country_code_field', 'AND'); // will put error in 
$v->getValidator()->country_code('alpha-2')->validate('AND'); // false

$v->country_code('alpha-3')->check('country_code_field', 'AND');   // validation passes
$v->getValidator()->country_code('alpha-3')->validate('AND'); // true

$v->country_code('numeric')->check('country_code_field', '608');   // validation passes
$v->getValidator()->country_code('numeric')->validate('608'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
