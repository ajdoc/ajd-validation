# Url

- `url()`
- `Url_rule($schemes = null)`
- valid schemes = ['verybasic', 'jdbc', 'mailto']

Validates whether the input is a url.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->url()->check('url_field', 'foo'); // will put error in error bag
$v->getValidator()->url()->validate('foo'); // false

$v->url()->check('url_field', 'http://foo.com/'); // validation passes
$v->getValidator()->url()->validate('http://foo.com/'); // true

$v->url()->check('url_field', 'foo.com'); // will put error in error bag
$v->getValidator()->url()->validate('foo.com'); // false

$v->url(['verybasic'])->check('url_field', 'foo.com'); // validation passes
$v->getValidator()->url(['verybasic'])->validate('foo.com'); // true

$v->url()->check('url_field', 'ldap://[::1]'); // validation passes
$v->getValidator()->url()->validate('ldap://[::1]'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
