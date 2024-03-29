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
- It is recommended that you filter the data to be validated first then define the field-rule validation like in the example below

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
	->check('field1', $filteredValues);

$v 
	->required()
	->digit()
	->check('field2', $filteredValues);
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
		- pre_filter values means filtered values before validation.
		- filter_value means filtered values after validation.
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

### Registering filters collection 
	- If your app is already using some sort of autoloading you can use this to register a collection/array of filters.
	- Use this if you dont want to register the whole filters directory and just want to register specific filters.

```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;

$v ->registerFiltersMappings([
	\CustomNamespace\Filters\Custom_test_filter::class
]);

$v->Fcustom_test()->check('custom', 'a');

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
	public function getName()
	{
		return 'Custom_extension';
	}

	public function getRules()
	{
		return array(
			'custom_validation',
			'custom_validation2'
		);
	}

	public function getRuleMessages()
	{
		// it is recommended to define the inverse message also for the custom rule.
		// but if you don't define an inverse message ajd validation will just output the same message.
		return array(
			'custom_validation' 	=> ['default' => 'The :field field is not a a.', 'inverse' => 'Not The :field field is not a a.'],
			'custom_validation2' 	=> 'The :field field is not a a 2.',
		);
	}

	public function custom_validation( $value, $satisfier, $field )
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

	public function custom_validation2( $value, $satisfier, $field )
	{

		return false;
		
	}

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

	public function getLogics()
	{
		return [
			'custom_logics_logic'
		];
	}

	public function custom_logics_logic($value = null, ...$satisfier) : bool
	{
		
		return $value == $satisfier[0];
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
		Adding custom macros
	*/
	public function getMacros()
	{
		return [
			'extension_macro',
			'extension_macro2'
		];
	}

	/*
		filter method must always have _filter suffix
	*/
	public function custom_string_filter( $value, $satisfier, $field )
	{
		$value 	= filter_var( $value, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES ).'_from_extension';

		return $value;
	}


	public function extension_macro()
	{
		return function()
		{
			$this->required()
				->minlength(7);

			return $this;
		};
	}

	public function extension_macro2($args = null)
	{
		return function($args = null)
		{
			if($args)
			{
				$this->setArguments([$args]);
			}


			$this->registerAsRule(function($value, $satisfier = null)
			{
				if (!is_numeric($value)) 
		        {
		            return false;
		        }

		        return $value > 0;

				
			}, ['default' => 'Value :field must be positive ext :*', 'inverse' => 'Value :field must not be positive ext :*']);

			return $this;
		};
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