# Mac Address

- `mac_address()`
- `Mac_address_rule()`

Validates the input value is a valid MAC address.

```php
use AJD_validation\AJD_validation as v;

$v = new v;


$v->mac_address()->check('mac_address_field', 'aaaaa'); // will put error on error bag
$v->getValidator()->mac_address()->validate('aaaaa'); // false

$v->mac_address()->check('mac_address_field', '00:11:22:33:44:55'); // validation passes
$v->getValidator()->mac_address()->validate('00:11:22:33:44:55'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
