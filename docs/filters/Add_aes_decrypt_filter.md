# Add aes decrypt

- `Fadd_aes_decrypt(string $key)`
- `Add_aes_decrypt_filter(string $key)`

Wraps the value in an mysql aes_decrypt function.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->Fadd_aes_decrypt(null, true)->check('field', 'a');
$v->Fadd_aes_decrypt()->check('field', 'a');

$pre_filter = $v->pre_filter_value();
/*
	returns array;
	array(1) {
	  ["field"]=>
  		string(36) "AES_DECRYPT(a, UNHEX(SHA2("", 512)))"
	  
	}
*/
$filtered_values = $v->filter_value();

/*
	returns array;
	array(1) {
	  ["field"]=>
  		string(36) "AES_DECRYPT(a, UNHEX(SHA2("", 512)))"
	}
*/
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
