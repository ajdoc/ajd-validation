# Package Development

In this document we'll see how to create package for ajd-validation

## Package Folder structure
- These are the recommended folder structure:
1. 
```
+-- Rules
|   +-- Custom_rule.php
+-- Exceptions
|   +-- Custom_rule_exception.php
+-- Filters
|   +-- Custom_filter.php
+-- Logics
|   +-- Custom_logic.php
+-- Macros
|   +-- Custom_macro.php
+-- Extensions
|   +-- Custom_extension.php
+-- Validations
|   +-- Custom_validation.php
+-- ClientSide
|   +-- Custom_client_side.php
+-- lang
|   +-- custom_lang.php
|   +-- custom_lang.stubs
+-- ValidatorProvider.php
```

2. 
```
+-- Rules
|   +-- Custom_rule.php
|	+-- Exceptions
|   	+-- Custom_rule_exception.php
+-- Filters
|   +-- Custom_filter.php
+-- Logics
|   +-- Custom_logic.php
+-- Macros
|   +-- Custom_macro.php
+-- Extensions
|   +-- Custom_extension.php
+-- Validations
|   +-- Custom_validation.php
+-- ClientSide
|   +-- Custom_client_side.php
+-- lang
|   +-- custom_lang.php
|   +-- custom_lang.stubs
+-- ValidatorProvider.php
```

- Inside a package you can create your Custom Rules -> Exceptions, Filters, Validators, Macros, Extensions, Client Sides, lang file, lang stubs file and Logics.
	- You can read creating a custom rule class here:
		[Adding Custom Rule](advance_usage/adding_custom_rules.md)
	- You can read creating a custom extension class here:
		[Adding Custom Rule](advance_usage/adding_custom_rules.md), 
		[Filters](filters.md)
	- You can read creating a custom validator class here:
		[Custom validation](custom_validations.md)
	- You can read creating a custom macro class here:
		[Macros](advance_usage/macros.md)
	- You can read creating a custom filter class here:
		[Filters](filters.md)
	- You can read creating a custom logics class here:
		[When](advance_usage/when.md)
	- You can read creating a custom client side class here:
		[When](advance_usage/client_side.md)

## Validator Provider
- All package must have a validator provider class which extends to `\AJD_validation\Contracts\Validation_provider.php`.
- Inside the validator provider class there must be a `register()` method.

```php
namespace PackageAjd\PackageAjd;

use AJD_validation\Contracts\Validation_provider;

class PackageAjdValidatorServiceProvider extends Validation_provider
{
	public function register()
	{
		$this
			->setDefaults([
				'baseDir' => __DIR__,
				'baseNamespace' => __NAMESPACE__
			])
			/*->registerRulesMapping([
				Rules\Package_test_rule::class => 
				Exceptions\Package_test_rule_exception::class
			])
			->registerFiltersMapping([
				Filters\Package_test_filter::class
			])
			->registerLogicsMapping([
				Logics\Package_test_logic::class
			]);*/
			// ->registerRules()
			->registerRulesMapping($this->getRulesMappingDirectory())
			->registerFiltersMapping($this->getFiltersMappingDirectory())
			->registerLogicsMapping($this->getLogicsMappingDirectory());
			// ->registerFilters()
			// ->registerLogics();
	}
}
```

### Registering Rules and Exceptions
- Before registering any Rules -> Exceptions one must 
	`->setDefaults([
		'baseDir' => __DIR__,
		'baseNamespace' => __NAMESPACE__
	])` 

- There are two ways to register rules and exceptions 
	- use `->registerRules()` if you are using folder structure `2` and is not using any autoloading. This will automatically register Rules Directory and Exceptions Directory
```php
	public function register()
	{
		$this
			->setDefaults([
				'baseDir' => __DIR__,
				'baseNamespace' => __NAMESPACE__
			])
			->registerRules();
	}
```
	- use `->registerRulesMapping([Rule::class => ExceptionClass])` if you want to register a key value pair of rules and exceptions and is using autloading.
```php
	public function register()
	{
		$this
			->setDefaults([
				'baseDir' => __DIR__,
				'baseNamespace' => __NAMESPACE__
			])
			// manual registering of rule exception key value pair
			->registerRulesMapping([
				Rules\Package_test_rule::class => 
				Exceptions\Package_test_rule_exception::class
			])

			// use this if you want the provider to try and auto map rules and exceptions class

			->registerRulesMapping($this->getRulesMappingDirectory());
	}
```

### Registering Filters
- Before registering any Filters one must 
	`->setDefaults([
		'baseDir' => __DIR__,
		'baseNamespace' => __NAMESPACE__
	])` 

- There are two ways to register Filters 
	- use `->registerFilters()` if you are using folder structure `2` and is not using any autoloading. This will automatically register Filters under Filters Directory
```php
	public function register()
	{
		$this
			->setDefaults([
				'baseDir' => __DIR__,
				'baseNamespace' => __NAMESPACE__
			])
			->registerFilters();
	}
```
	- use `->registerFiltersMapping([Filter::class])` if you want to register an array of filter classes and is using autloading.
```php
	public function register()
	{
		$this
			->setDefaults([
				'baseDir' => __DIR__,
				'baseNamespace' => __NAMESPACE__
			])
			// manual registering of filters
			->registerFiltersMapping([
				Filters\Package_test_filter::class
			])

			// use this if you want the provider to try and get all the filters under filters directory 

			->registerFiltersMapping($this->getFiltersMappingDirectory());
	}
```

### Registering Logics
- Before registering any Logics one must 
	`->setDefaults([
		'baseDir' => __DIR__,
		'baseNamespace' => __NAMESPACE__
	])` 

- There are two ways to register Logics 
	- use `->registerLogics()` if you are using folder structure `2` and is not using any autoloading. This will automatically register Logics under Logics Directory
```php
	public function register()
	{
		$this
			->setDefaults([
				'baseDir' => __DIR__,
				'baseNamespace' => __NAMESPACE__
			])
			->registerLogics();
	}
```
	- use `->registerLogicsMapping([Logic::class])` if you want to register an array of logic classes and is using autloading.
```php
	public function register()
	{
		$this
			->setDefaults([
				'baseDir' => __DIR__,
				'baseNamespace' => __NAMESPACE__
			])
			// manual registering of logics
			->registerLogicsMapping([
				Logics\Package_test_logic::class
			])

			// use this if you want the provider to try and get all the logics under logics directory 

			->registerLogicsMapping($this->getLogicsMappingDirectory());
	}
```

### Registering Client Sides
- Before registering any Client Sides one must 
	`->setDefaults([
		'baseDir' => __DIR__,
		'baseNamespace' => __NAMESPACE__
	])` 

- There are two ways to register Client Sides 
	- use `->registerClientSides()` if you are using folder structure `2` and is not using any autoloading. This will automatically register Client Sides under ClientSides Directory
```php
	public function register()
	{
		$this
			->setDefaults([
				'baseDir' => __DIR__,
				'baseNamespace' => __NAMESPACE__
			])
			->registerClientSides();
	}
```
	- use `->registerClientSideMapping([ClientSideInterface::class])` if you want to register an array of client sides classes and is using autloading.
```php
	public function register()
	{
		$this
			->setDefaults([
				'baseDir' => __DIR__,
				'baseNamespace' => __NAMESPACE__
			])
			// manual registering of client sides
			->registerClientSideMapping([
				ClientSides\Package_client_side::class
			])

			// use this if you want the provider to try and get all the client sides under ClientSides directory 

			->registerClientSideMapping($this->getClientSideMappingDirectory());
	}
```

### Registering Custom Validations
- Before registering any Validations one must 
	`->setDefaults([
		'baseDir' => __DIR__,
		'baseNamespace' => __NAMESPACE__
	])` 

- There is only one way to register custom validation
	- use `->registerValidationsMapping([Validation_interface::class])` if you want to register an array of custom validation classes and is using autloading.
```php
	public function register()
	{
		$this
			->setDefaults([
				'baseDir' => __DIR__,
				'baseNamespace' => __NAMESPACE__
			])
			// manual registering of custom validations
			->registerValidationsMapping([
				Validations\PackageValidation::class
			])

			// use this if you want the provider to try and get all the custom validations under validations directory 

			->registerValidationsMapping($this->getValidationsMappingDirectory());
	}
```
- You can read more about custom validations here:
	- [Custom validation](custom_validations.md)

### Registering Custom Macros
- Before registering any Macros one must 
	`->setDefaults([
		'baseDir' => __DIR__,
		'baseNamespace' => __NAMESPACE__
	])` 

- Two way of registering/creating macros:
	- You can read more about macros here: [Macros](advance_usage/macros.md)
	- inline registration/creation use `->macro(string $name, Closure $macro)`.
	- class based registration/creation `->mixin(\PackageAjd\Macros\Package_macro::class, $replace = true, ...$args)`.
```php
// Example of a macro class
namespace PackageAjd\Macros;

use AJD_validation\Contracts\CanMacroInterface;

class Package_macro implements CanMacroInterface
{
	public function getMacros()
	{
		return [
			'package_macro_class',
			'package_macro_class2'
		];
	}	

	public function package_macro_class()
	{
		return function()
		{
			echo 'package_macro_class';

			return $this;
		};
	}

	public function package_macro_class2()
	{
		return function()
		{
			echo 'package_macro_class2';

			return $this;
		};
	}
}

	public function register()
	{
		$this
			->setDefaults([
				'baseDir' => __DIR__,
				'baseNamespace' => __NAMESPACE__
			])
			// inline registration/creation
			->macro('package_macro', function()
			{
				echo 'package_macro';

				return $this;
			})
			// class based registration/creation
			->mixin(\PackageAjd\Macros\Package_macro::class);
	}
```

### Registering Custom Extensions
- Before registering any Extensions one must 
	`->setDefaults([
		'baseDir' => __DIR__,
		'baseNamespace' => __NAMESPACE__
	])` 
- Custom Extension is a way to add Rules,Exceptions,Filters,Logics,Client Sides and Macros in one class.
- It is recommended to add Rules,Exceptions,Filters,Logics,Client Sides and Macros by clasess.
- To register an extension use `->registerExtension(new \AJD_validation\Contracts\Base_extension $extension)`.
- You can read more about extension class here:
	[Adding Custom Rule](advance_usage/adding_custom_rules.md)
	[Filters](filters.md)
- You could also check out `\src\AJD_validation\Contracts\Extension_interface.php`

```php
// Example of a extension class
namespace PackageAjd\Extensions;

use AJD_validation\Contracts\Base_extension;

class Package_extension extends Base_extension
{
	public function getName()
	{
		return self::class;
	}

	public function getRules()
	{
		return array(
			'custom_validation_rule',
			'custom_validation2_rule'
		);
	}

	public function getRuleMessages()
	{
		return array(
			'custom_validation' 	=> 'The :field field is not a a.',
			'custom_validation2' 	=> 'The :field field is not a a 2.',
		);
	}

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

	public function custom_validation2_rule( $value, $satisfier, $field )
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
		filter method must always have _filter suffix
	*/
	public function custom_string_filter( $value, $satisfier, $field )
	{
		$value 	= filter_var( $value, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES ).'_from_extension';

		return $value;
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

	public function getClientSides()
	{
		return [
			'custom_validation' => [
				'clientSide' => function(string $field, string $rule, mixed $satisfier = null, string $error = null, mixed $value = null)
				{
					$js[$field][$rule]['rule'] =   <<<JS
	            		data-parsley-$rule="emailaass"
JS;

					$js[$field][$rule]['message'] = <<<JS
                		data-parsley-$rule-message="$error"
JS;
					return $js;
				},
				// optional
				'field' => 'specific_field'
			]
		];
	}
}

	public function register()
	{
		$this
			->setDefaults([
				'baseDir' => __DIR__,
				'baseNamespace' => __NAMESPACE__
			])
			->registerExtension(new \PackageAjd\Extensions\Package_extension);
	}
```

### Registering Custom lang file
- It is not necessary but it is recommended to set defaults first before registering custom lang file. 
	`->setDefaults([
		'baseDir' => __DIR__,
		'baseNamespace' => __NAMESPACE__
	])` 

- There is only one way to register custom lang file
	- use `->addLangDir('custom', $directory_where_lang_file_is)`
		1. $langName = first argument is language name.
		2. $path = second argument is the absolute path of the directory where the language file is located and it is necessary that all language file will have an `_lang` suffix so for this example `custom_lang.php` will be the filename.
```php
	public function register()
	{
		$this
			->setDefaults([
				'baseDir' => __DIR__,
				'baseNamespace' => __NAMESPACE__
			])
			// register package's custom lang file
			->addLangDir('custom', __DIR__.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR);
	}

	// now to use the custom lang file
	// 1. register the package to ajd validation first

	AJD_validation::addPackages([
		\PackageAjd\PackageAjd\PackageAjdValidatorServiceProvider::class
	]);

	// 2. the set the lang file to custom
	$v->setLang('custom');
```

### Registering Custom lang stubs
- It is not necessary but it is recommended to set defaults first before registering custom lang stubs file. 
	`->setDefaults([
		'baseDir' => __DIR__,
		'baseNamespace' => __NAMESPACE__
	])` 

- There is only one way to register custom lang stub file
	- what are lang.stubs file this will be the template generated for custom lang file.
	- if you want your custom rules lang to be included in the template add your custom lang stubs file. 
	- use `->addLangStubs($path_to_lang_stubs)`
		1. $path = first argument is the path to your custom lang stubs file.
```php
	public function register()
	{
		$this
			->setDefaults([
				'baseDir' => __DIR__,
				'baseNamespace' => __NAMESPACE__
			])
			// register package's custom lang stubs file
			->addLangStubs(__DIR__.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'lang.stubs');
	} // after registering ajd validation will automatically include your lang stubs file in the template
	
	// example of a custom lang stubs file
	use AJD_validation\Contracts\Abstract_exceptions as ex;

	use AJD_validation\Exceptions as Assert;

	$lang = [];

	$lang['error_msg']  = [
		'package_custom'		=> array(
			ex::ERR_DEFAULT 	=> array(
				ex::STANDARD 	=> ':field must be a custom stubs file example.'
			),
			ex::ERR_NEGATIVE 	=> array(
				ex::STANDARD 	=> ':field must not be a custom stubs file example.'
			)
		),
	];

	return $lang;
```

### Adding package/s to ajd-validation
1. `composer require` the package to your project.
2. To add package/s use `AJD_validation::addPackages([Package::class])`
3. After adding you can now use all the rules -> exceptions/filters/logics in that package

```php
use AJD_validation\AJD_validation;

AJD_validation::addPackages([
	\PackageAjd\PackageAjd\PackageAjdValidatorServiceProvider::class
]);

```