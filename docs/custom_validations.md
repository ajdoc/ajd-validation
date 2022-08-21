# Custom Validations

In this document we'll see how to create a custom validation for ajd-validation

## What are custom validations?
- Custom validation classes are classes that will allow you to integrate additional behavior/function while validating.
- Take for example the built in custom validation `\AJD_validation\Validations\DebugValidation`, if you use this custom validation this will allow you to call `->getCollectedData()` or `->printCollectedData()` which will `return` or `print` a `debug_backtrace`.
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v 
	->required()
	->minlength(2)
	->useValidation(\AJD_validation\Validations\DebugValidation::class)
	->check('custom_validation', '')
	->printCollectedData();

/*
	Outputs error
		All of the required rules must pass for "Custom validation".
		  - The Custom validation field is required
		  - Custom validation must be greater than or equal to 2. character(s).

	Also outputs debug_backtrace
	Array
	(
	    [0] => Array
	        (
	            [caller] => Array
	                (
	                    [0] => Array
	                        (
	                            [name] => DebugValidation.php
	                            [file] => C:\xampp8.1\htdocs\ajd-validation\src\AJD_validation\Validations\DebugValidation.php
	                            [line] => 98
	                            [class] => AJD_validation\Validations\DebugValidation
	                        )

	                    [1] => Array
	                        (
	                            [name] => example.php
	                            [file] => C:\xampp8.1\htdocs\ajd-validation\example.php
	                            [line] => 301
	                            [class] => AJD_validation\Validations\DebugValidation
	                        )

	                )

	            [context] => Array
	                (
	                    [value] => 
	                    [field] => custom_validation
	                )

	        )

	)
*/
```

## How to use custom validation
- There are two ways of using custom validation 
	1. `->useValidation(\AJD_validation\Contracts\Validation_interface::class)` 
		- `useValidation` method must be called after all the rules has been set. After which you know have access to all of its public method.
		```php
			use AJD_validation\AJD_validation;

			$v = new AJD_validation;

			$v 
				->required()
				->minlength(2)
				->useValidation(\AJD_validation\Validations\DebugValidation::class)
				->check('custom_validation', '')
				->printCollectedData();
		```
		- `useValidation` method must receive a class that implements `\AJD_validation\Contracts\Validation_interface`.
		- To get the promiseValidator object one must call `->getPromise()`
		```php
			use AJD_validation\AJD_validation;

			$v = new AJD_validation;

			$v 
				->required()
				->minlength(2)
				->useValidation(\AJD_validation\Validations\DebugValidation::class)
				->check('custom_validation', '')
				->printCollectedData()
				->getPromise(); // returns PromiseValidtor object
		```

	2. `->setValidation(\AJD_validation\Contracts\Validation_interface::class)`
		- `setValidation` will force all `->check(), ->checkAsync(), ->checkDependent(), ->checkArr(), ->checkGroup(), ->middleware(), ->checkAllMiddleware()` to use the custom validation set. So no need to use `->useValidation(\AJD_validation\Contracts\Validation_interface::class)` to access the custom validation method.
		```php
			use AJD_validation\AJD_validation;

			$v = new AJD_validation;

			$v->setValidation(\AJD_validation\Validations\DebugValidation::class);

			$v 
				->required()
				->minlength(2)
				->check('custom_validation', '')
				->printCollectedData()
				->getPromise(); // returns PromiseValidtor object
		```
		- `setValidation` method must receive a class that implements `\AJD_validation\Contracts\Validation_interface`.
		- **Note: Be very careful when setting a custom validation for all as this might break some of your validation definition because if that custom validation object is not auto returning a promiseValidator object your `->then(), ->catch(), ->otherwise()` definition will cause an error and other function such as Async::when() might not work properly as you will need to call `->getPromise()`.**
		```php
			use AJD_validation\AJD_validation;

			$v = new AJD_validation;

			$v->setValidation(\AJD_validation\Validations\DebugValidation::class);

			$v 
				->required()
				->minlength(2)
				->check('custom_validation', '')
				->printCollectedData()
				->then(function()
				{
					echo 'aa'
				}); // will cause an error, to fix this one must use `->getPromise()` first.

			$v 
				->required()
				->minlength(2)
				->check('custom_validation', '')
				->printCollectedData()
				->getPromise()
				->then(function()
				{
					echo 'then';
				}); // will not cause an error.

			Async::when(
				$v
					->makeAsync()
					->required()
					->check('field1', '')
					->getPromise(),

				$v
					->makeAsync()
					->required()
					->check('field2', '')					
					->getPromise()

			)->promise()->then(function()
			{
				echo 'then';
			}); // needs to call `->getPromise()` to work properly
		```
## Example of a custom validation
- You could also see `src\AJD_validation\Validations\DebugValidation.php` for another example.
```php
namespace Validations;

use AJD_validation\AJD_validation;
use AJD_validation\Contracts\Validation_interface;
use AJD_validation\Async\PromiseValidator;

class CustomValidation implements Validation_interface
{
	protected $ajd;
	protected $currentPromise;

	public function __construct(AJD_validation $ajd)
	{
		$this->ajd = $ajd;
	}

	public function customAction()
	{
		echo 'customAction.';
	}

	public function check($field, $value = null, $check_arr = true)
	{
		$this->ajd->resetGlobalValidation();

		return $this->setPromise($this->ajd->check($field, $value, $check_arr));
	}

	public function checkAsync($field, $value = null, $function = null, $check_arr = true)
	{
		$this->ajd->resetGlobalValidation();

		return $this->setPromise($this->ajd->checkAsync($field, $value, $function, $check_arr));
	}

	public function checkDependent($field, $value = null, $origValue = null, array $customMessage = [], $check_arr = true)
	{
		$this->ajd->resetGlobalValidation();

		return $this->setPromise($this->ajd->checkDependent($field, $value, $origValue, $customMessage, $check_arr));
	}

	public function checkArr($field, $value, array $customMesage = [], $check_arr = true)
	{
		$this->ajd->resetGlobalValidation();

		return $this->setPromise($this->ajd->checkArr($field, $value, $customMesage, $check_arr));
	}

	public function checkGroup(array $data)
	{
		$this->ajd->resetGlobalValidation();

		return $this->setPromise($this->ajd->checkGroup($data));
		
	}

	public function middleware($name, $field, $value = null, $check_arr = true)
	{
		$this->ajd->resetGlobalValidation();

		return $this->setPromise($this->ajd->middleware($name, $field, $value, $check_arr));
		
	}

	public function checkAllMiddleware($field, $value = null, array $customMesage = [], $check_arr = true)
	{		
		$this->ajd->resetGlobalValidation();

		return $this->setPromise($this->ajd->checkAllMiddleware($field, $value, $customMesage, $check_arr));

	}

	public function setPromise(PromiseValidator $promise)
	{
		$this->currentPromise = $promise;

		return $this;
	}

	public function getPromise() : PromiseValidator
	{
		return $this->currentPromise;
	}
}

use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v->required()
	->useValidation(\Validations\CustomValidation::class)
	->customAction()
	->check('custom_validation', '');
/*
	Outputs error 
		All of the required rules must pass for "Custom validation".
		  - The Custom validation field is required

	Also prints
	"customAction"
*/
```
