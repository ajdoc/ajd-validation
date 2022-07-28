# Filter sanitize

- `Ffilter_sanitize($args)`
- `Filter_sanitize_filter($args)`

Uses php's `filter_var()` function

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->Ffilter_sanitize([FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES], true)->check('field', 'se');
$v->Ffilter_sanitize(FILTER_SANITIZE_NUMBER_INT)->check('field', '1aa');

$pre_filter = $v->pre_filter_value();
/*
	returns array;
	array(1) {
	  ["field"]=>
  		string(2) "se"
	}
*/
$filtered_values = $v->filter_value();

/*
	returns array;
	array(1) {
   		["field"]=>
 		string(1) "1"
	}
*/
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
