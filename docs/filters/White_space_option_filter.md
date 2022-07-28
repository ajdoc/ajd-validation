# White space option filter

- `Fwhite_space_option(string $additionalChar = null)`
- `White_space_option(string $additionalChar = null)`

Removes addtional white space.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->Fwhite_space_option(null, true)->check('field', 'john    ');
$v->Fwhite_space_option('*& ', true)->check('field_1', 'john*&   a');
$v->Fwhite_space_option()->check('field', '1aa  a ');

$pre_filter = $v->pre_filter_value();
/*
	returns array;
	array(2) {
	  ["field"]=>
  		string(4) "john"
  	  ["field_1"]=>
  		string(4) "johna"
	}
*/
$filtered_values = $v->filter_value();

/*
	returns array;
	array(1) {
   		["field"]=>
 		string(4) "1aaa"
	}
*/
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
