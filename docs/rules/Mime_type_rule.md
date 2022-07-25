# Mime Type

- `mime_type()`
- `Mime_type_rule($mime_type, finfo $fileInfo = null)`
- Validates if the input is a file and if its MIME type matches the expected one

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->mime_type('image/png')->check('mime_type_field', '/path_to_your_image');  // valdation passes
$v->getValidator()->mime_type('image/png')->validate('/path_to_your_image') // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
