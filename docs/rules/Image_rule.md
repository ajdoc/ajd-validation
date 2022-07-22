# Image

- `image()`
- `Image_rule()`
- Relies on fileinfo PHP extension.
- If the input is not a valid file or of the MIME doesn't match with the file extension, validation will put error in error or bag or will return false.

Validates whether the input's value is a valid image by checking its MIME type.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->image()->check('image_field', 'image.png'); // validation passes
$v->getValidator()->image()->validate('image.png'); // true

$v->image()->check('image_field', 'image.gif'); // validation passes
$v->getValidator()->image()->validate('image.gif'); // true

$v->image()->check('image_field', 'image.jpg'); // validation passes
$v->getValidator()->image()->validate('image.jpg'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
