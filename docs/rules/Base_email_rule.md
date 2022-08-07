# Base email

- `base_email()`
- `Base_email_rule()`


Validates whether the input's value is a valid email uses php's filter_var($email, FILTER_VALIDATE_EMAIL).


```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->base_email()->check('email_field', 'a'); // will put error in error bag
$v->getValidator()->base_email()->validate('a'); // false

$v->base_email()->check('email_field', 'johndoe@yopmail.com'); // validation passes
$v->getValidator()->base_email()->validate('johndoe@yopmail.com'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release, Also uses "egulias/emailvalidator" for email validation

***
