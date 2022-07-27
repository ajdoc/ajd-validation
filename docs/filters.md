# Filters

In this document we'll see how to use ajd-validation filters

## Basic usage
- To use filter check the available filters in src/AJD_validation/Filters or at [Filters](filters/).
- Prefix the filter class name with `F`[filter_classname] and remove suffix `_filter`

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

/*
	Associative array filtering useful for $_POST,$_GET data filtering
*/
$toFiler = [
	'field1' => '1aas',
	'field2' => '1'
];

/*
	->cacheFilter(string $field) must be present in the array
*/
$filteredValues = $v
		->Ffilter_sanitize([FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES])
		->Fextract_length()
		->Fadd_aes_decrypt('test')
		
		
			->cacheFilter('field1')
		->Ffilter_sanitize(FILTER_SANITIZE_NUMBER_INT)
		
			->cacheFilter('field2')
		->filterValues($toFiler);

/*
	This will print 
		array(2) {
		  ["field1"]=>
		  string(40) "AES_DECRYPT(4, UNHEX(SHA2("test", 512)))"
		  ["field2"]=>
		  string(1) "1"
		}
*/

/*
	Single value Filtering Example
*/

$filteredSingle = $v 
	->Ffilter_sanitize([FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES])
	->Fwhite_space_option()
	->Fadd_aes_decrypt('test')
		->cacheFilter('fieldsingle')
	->filterAllValues('as   ');

/*
	This will print 
		string(41) "AES_DECRYPT(as, UNHEX(SHA2("test", 512)))"
*/

```
- It is recommended that you filter that data to be validated first then define the field-rule validation like in the example below

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

/*
	Associative array filtering useful for $_POST,$_GET data filtering
*/
$toFiler = [
	'field1' => '1aas',
	'field2' => '1'
];

/*
	->cacheFilter(string $field) must be present in the array
*/
$filteredValues = $v
		->Ffilter_sanitize([FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES])
		
		
			->cacheFilter('field1')
		->Ffilter_sanitize(FILTER_SANITIZE_NUMBER_INT)
		
			->cacheFilter('field2')
		->filterValues($toFiler);

$v 
	->required()
	->check('field1', $filterValues);

$v 
	->required()
	->digit()
	->check('field2', $filterValues);
```

## Filter and validate at the same time 
- You can also filter and validate at the same time 
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

/*
	Example 1
*/
$v->Ffilter_sanitize(FILTER_SANITIZE_NUMBER_INT, true)
		->required()
		->digit()
		->check('test_digit', 'aa');

/*
	Outputs error 
		All of the required rules must pass for "Test digit".
		  - The Test digit field is required
		  - Test digit must contain only digits (0-9).
*/

/*
	Example 2
*/
$v->Ffilter_sanitize([FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES], false)
		->required()
		->alpha()
		->check('test_string', '<<aa');
/*
	Outputs error
		All of the required rules must pass for "Test string".
		  - Test string must contain only letters (a-z).
*/

/*
	To get the pre_filter values and filtered_values after validation
*/

$pre_filter = $v->pre_filter_value();
/*
	returns array;
	array(5) {
	  ["test_digit"]=>
	  string(0) ""
	  
	}
*/
$filtered_values = $v->filter_value();

/*
	returns array;
	array(6) {
	  
	  ["test_string"]=>
	  string(0) ""
	}
*/
```

- In example 1 the second paramater `true` in `->Ffilter_sanitize()` will tell the filter to pre filter the value `aa` so the value will be `''` during validation.

- In example 2 the second paramater `false` in `->Ffilter_sanitize()` (and default is false.), will tell the filter to apply filter to the value after validation thats why it did not remove characters `<<`.