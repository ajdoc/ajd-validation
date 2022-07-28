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
		->filterAllValues($toFiler);

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
	->filterValue('as   ');

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
		->filterAllValues($toFiler);

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
	array(1) {
	  ["test_digit"]=>
	  string(0) ""
	  
	}
*/
$filtered_values = $v->filter_value();

/*
	returns array;
	array(1) {
	  
	  ["test_string"]=>
	  string(0) ""
	}
*/
```

- In example 1 the second paramater `true` in `->Ffilter_sanitize()` will tell the filter to pre filter the value `aa` so the value will be `''` during validation.

- In example 2 the second paramater `false` in `->Ffilter_sanitize()` (and default is false.), will tell the filter to apply filter to the value after validation thats why it did not remove characters `<<`.

## Adding Custom Filters

### Adding Filters Directory and Filters Namespace
- You can add custom filters by adding a new filters directory and add filters namespace
- Filters under the new directory must extend to AJD_validation\Contracts\Abstract_filter
- All new custom filters must have [Custom]`_filter` as a suffix.

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v
	->addFilterDirectory(__DIR__.DIRECTORY_SEPARATOR.'CustomFilters'.DIRECTORY_SEPARATOR)
	->addFilterNamespace('CustomFilters\\');

/*
	Custom Filter class example
*/
namespace CustomFilters;

use AJD_validation\Contracts\Abstract_filter;

class Custom_filter extends Abstract_filter
{
	public function filter( $value, $satisfier = NULL, $field = NULL )
	{
        return $value.'_custom_filter';
	}
}


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
		->Fcustom()
		
			->cacheFilter('field1')
		->Ffilter_sanitize(FILTER_SANITIZE_NUMBER_INT)
		
			->cacheFilter('field2')
		->filterAllValues($toFiler);

/*
	prints 
	array(2) {
	  ["field1"]=>
	  string(54) "AES_DECRYPT(4, UNHEX(SHA2("test", 512)))_custom_filter"
	  ["field2"]=>
	  string(1) "1"
	}
*/
```
### Registering custom filter using `$v->registerExtension()`
	- registering extension not only registers custom rule but can also register custom filtering, custom logics, custom middlewares, custom anonymous class rule.
```php
use AJD_validation\Contracts\Base_extension;
use AJD_validation\AJD_validation;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Contracts\Abstract_anonymous_rule;

$v = new AJD_validation;

class Custom_extension extends Base_extension
{
	/*
		Desired name of the extension
	*/
	public function getName()
	{
		return 'Custom_extension';
	}

	/*
		Adding custom rule method
	*/
	public function getRules()
	{
		return [
			'custom_validation_rule',
			'custom_validation2_rule'
		];
	}

	/*
		Adding custom rule method error message
		When adding custom rule method error message remove _rule suffix for the key
	*/
	public function getRuleMessages()
	{
		return [
			'custom_validation' 	=> 'The :field field is not a a.',
			'custom_validation2' 	=> 'The :field field is not a a 2.',
		];
	}

	/*
		rule method must always have _rule suffix
	*/
	public function custom_validation_rule( $value, $satisfier, $field )
	{
		if( $value == 'a' )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/*
		rule method must always have _rule suffix
	*/
	public function custom_validation2_rule( $value, $satisfier, $field )
	{
		return false;
	}

	/*
		Adding custom rule using anonymous class
	*/
	public function getAnonClass()
	{
		return [
			new class() extends Abstract_anonymous_rule
			{
				public function __invoke($value, $satisfier = NULL, $field = NULL)
				{
					return in_array($value, $satisfier);
				}

				public static function getAnonName() : string
				{
					return 'ext1_anontest';
				}

				public static function getAnonExceptionMessage(Abstract_exceptions $exceptionObj)
				{
					$exceptionObj::$defaultMessages 	= array(
						 $exceptionObj::ERR_DEFAULT 			=> array(
						 	$exceptionObj::STANDARD 			=> 'The :field field is ext1_anontest',
						 ),
						  $exceptionObj::ERR_NEGATIVE 		=> array(
				            $exceptionObj::STANDARD 			=> 'The :field field is not ext1_anontest.',
				        )
					);
				}
			},
			new class() extends Abstract_anonymous_rule
			{
				public function __invoke($value, $satisfier = NULL, $field = NULL)
				{
					return in_array($value, $satisfier);
				}

				public static function getAnonName() : string
				{
					return 'ext2_anontest';
				}

				public static function getAnonExceptionMessage(Abstract_exceptions $exceptionObj)
				{
					$exceptionObj::$defaultMessages 	= array(
						 $exceptionObj::ERR_DEFAULT 			=> array(
						 	$exceptionObj::STANDARD 			=> 'The :field field is ext2_anontest',
						 ),
						  $exceptionObj::ERR_NEGATIVE 		=> array(
				            $exceptionObj::STANDARD 			=> 'The :field field is not ext2_anontest.',
				        )
					);

					$exceptionObj::$localizeMessage 	= [
						Lang::FIL => [
							$exceptionObj::ERR_DEFAULT 			=> array(
							 	$exceptionObj::STANDARD 			=> 'The :field field ay ext2_anontest',
							 ),
							  $exceptionObj::ERR_NEGATIVE 		=> array(
					            $exceptionObj::STANDARD 			=> 'The :field field ay hindi ext2_anontest.',
					        ),
						]
					];
				}
			}
		];
	}

	/*
		Adding custom filters
	*/
	public function getFilters()
	{
		return [
			'custom_string_filter',
		];
	}

	/*
		filter method must always have _filter suffix
	*/
	public function custom_string_filter( $value, $satisfier, $field )
	{
		$value 	= filter_var( $value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES ).'_from_extension';

		return $value;
	}

	/*
		Adding custom middlewares
	*/
	public function getMiddleWares()
	{
		return [];
	}

	/*
		Adding custom logics
	*/
	public function getLogics()
	{
		return [
			'custom_logics_logic'
		];
	}

	/*
		logics method must alwasy have _logic suffix
	*/
	public function custom_logics_logic($value = null, ...$satisfier) : bool
	{
		return $value == $satisfier[0];
	}
}

/*
	1. Register the extension object.
*/
$extension 	= new Custom_extension;
$v->registerExtension($extension);

$toFiler = [
	'field1' => '1aas',
	'field2' => '1'
];

$filteredValues = $v
	->Ffilter_sanitize([FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES])
	->Fextract_length()
	->Fadd_aes_decrypt('test')
	->Fcustom_string()
	
		->cacheFilter('field1')
	->Ffilter_sanitize(FILTER_SANITIZE_NUMBER_INT)
	
		->cacheFilter('field2')
	->filterAllValues($toFiler);

/*
	prints 
	array(2) {
	  ["field1"]=>
	  string(54) "AES_DECRYPT(4, UNHEX(SHA2("test", 512)))_from_extension"
	  ["field2"]=>
	  string(1) "1"
	}
*/

```

See also:
- [Usage](usage.md)
- [Advance Usage](advance_usage/)
- [Filters](filters/)
- [Rules](rules/)
- [Alternative Usage](alternative_usage.md)