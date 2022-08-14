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
+-- ValidatorProvider.php
```

- Inside a package you can create your Custom Rules -> Exceptions, Filters and Logics.
	- You can read creating a custom rule class here:
		[Adding Custom Rule](advance_usage/adding_custom_rule.md)
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
	1. use `->registerRules()` if you are using folder structure `2` and is not using any autoloading. This will automatically register Rules Directory and Exceptions Directory
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
	2. use `->registerRulesMapping([Rule::class => ExceptionClass])` if you want to register a key value pair of rules and exceptions and is using autloading.
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
	1. use `->registerFilters()` if you are using folder structure `2` and is not using any autoloading. This will automatically register Filters under Filters Directory
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
	2. use `->registerFiltersMapping([Filter::class])` if you want to register an array of filter classes and is using autloading.
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
	1. use `->registerLogics()` if you are using folder structure `2` and is not using any autoloading. This will automatically register Logics under Logics Directory
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
	2. use `->registerLogicsMapping([Logic::class])` if you want to register an array of logic classes and is using autloading.
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

### Adding package/s to ajd-validation
1. `composer require` the package to your project.
2. To add package/s use AJD_validation::addPackages([Package::class])
3. After adding you can now use all the rules -> exceptions/filters/logics in that package

```php
use AJD_validation\AJD_validation;

AJD_validation::addPackages([
	\PackageAjd\PackageAjd\PackageAjdValidatorServiceProvider::class
]);

```