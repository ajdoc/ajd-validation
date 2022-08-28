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
+-- ValidatorProvider.php
```

- Inside a package you can create your Custom Rules -> Exceptions, Filters, Validators, Macros, Extensions and Logics.
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
	- You can read more about macros here: [Macros]((advance_usage/macros.md)
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
- Custom Extension is a way to add Rules,Exceptions,Filters,Logics and Macros in one class.
- It is recommended to add Rules,Exceptions,Filters,Logics and Macros by clasess.
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