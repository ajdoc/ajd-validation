# Amount

- `amount()`
- `Amount_rule($decimalPlace = null)`

Validates whether the input is a valid amount.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v ->amount()->check('amount_rule', '2,000'); // validation passes
$v->getValidator()->amount()->validate('2,000'); // true

$v ->amount()->check('amount_rule', '2,000.00'); // will put error in error bag
$v->getValidator()->amount()->validate('2,000.00'); // false

$v ->amount(2)->check('amount_rule', '2,000.00'); // validation passes
$v->getValidator()->amount(2)->validate('2,000.00'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
