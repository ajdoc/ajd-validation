# Spoof email

- `spoof_email()`
- `Spoof_email_rule()`


Validates whether the input's value is a valid email uses \Egulias\EmailValidator\Validation\SpoofCheckValidation.


```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->spoof_email()->check('email_field', 'johndoe@yopmail.com'); // validation passes
$v->getValidator()->spoof_email()->validate('johndoe@yopmail.com'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release, Also uses "egulias/emailvalidator" for email validation

***
