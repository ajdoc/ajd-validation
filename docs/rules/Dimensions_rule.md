# Dimensions

- `dimensions()`
- `Dimensions_rule(array $options)`
- valid array key [
	'width' => (numeric),
	'height' => (numeric),
	'maxHeight' => (numeric),
	'maxWidth' => (numeric),
	'minWidth' => (numeric),
	'minHeight' => (numeric),
	'ratio' => e.g. 3/2 or float like 1.5
]
- Works similarly with laravels dimensions validation rule

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->dimensions(['ratio' => '3/2'])->check('dimensions_field', '/path_to_your_image');  // valdation passes
$v->getValidator()->dimensions(['ratio' => '3/2'])->validate('/path_to_your_image') // true

$v->dimensions(['minWidth' => '100', 'minHeight' => '100'])->check('dimensions_field', '/path_to_your_image');  // valdation passes
$v->getValidator()->dimensions(['minWidth' => '100', 'minHeight' => '100'])->validate('/path_to_your_image') // true
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
