# String filter

- `Fstring()`
- `String_filter()`

Uses php's `filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES )` function

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->Fstring(null, true)->check('field', '<s>');
$v->Fstring()->check('field', '1aa');

$pre_filter = $v->pre_filter_value();
/*
	returns array;
	array(1) {
	  ["field"]=>
  		string(0) ""
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
