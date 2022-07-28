# String filter

- `Ftrim(string $additionalChar = null)`
- `Trim_filter(string $additionalChar = null)`

Uses php's `trim($value, $additionalChar)` function

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->Ftrim('n', true)->check('field', 'john');
$v->Ftrim()->check('field', '1aa   ');

$pre_filter = $v->pre_filter_value();
/*
	returns array;
	array(1) {
	  ["field"]=>
  		string(3) "joh"
	}
*/
$filtered_values = $v->filter_value();

/*
	returns array;
	array(1) {
   		["field"]=>
 		string(3) "1aa"
	}
*/
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
