# Url filter

- `Furl()`
- `Url_filter()`

Uses php's `filter_var($value, FILTER_SANITIZE_URL )` function

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->Furl(null, true)->check('field', 'https://www.examp��le.co�m');
$v->Furl()->check('field', 'https://www.example.com');

$pre_filter = $v->pre_filter_value();
/*
	returns array;
	array(1) {
	  ["field"]=>
  		string(23) "https://www.example.com"
	}
*/
$filtered_values = $v->filter_value();

/*
	returns array;
	array(1) {
   		["field"]=>
 			string(23) "https://www.example.com"
	}
*/
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
