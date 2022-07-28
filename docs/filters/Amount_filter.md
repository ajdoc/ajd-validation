# Amount

- `Famount(string $additionalChartoTrim = null)`
- `Amount_filter(string $additionalChartoTrim = null)`

Remove an amount value comma separator.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->Famount('a', true)->check('field', '1,000a');
$v->Famount('a')->check('field', '1,000a');

$pre_filter = $v->pre_filter_value();
/*
	returns array;
	array(1) {
	  ["field"]=>
  		string(4) "1000"
	  
	}
*/
$filtered_values = $v->filter_value();

/*
	returns array;
	array(1) {
	  ["field"]=>
  		string(4) "1000"
	}
*/
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
