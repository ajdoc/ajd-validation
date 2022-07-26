## When
- Emulates an `if else` like statement.
	1. To use `when` start the chain by `$v->when()`
	2. To start the conditional statement prefix the rules `Giv` then the rulename
		- e.g. `->Givrequired()->Givdigit()` meaning given the field is required and a digit.
	3. To end `Giv` use `->endgiven(string $field, mixed $value);`
	4. After that you can define `Then`s meaning if the `Giv`en validation all passes then it will do validations under `Then`s. One can define `Then`s by prefixing rulename with `Th`
		e.g. `->Threquired()` meaning it will do this validation if `Giv`en passes
	5. To end `Then`s use `->endthend(string $field, mixed $value);`
	6. After `Then`s you can define `Otherwise`s which is like the else
	7. One can define `Otherwise`s by prefixing rulename with `Oth`.
		e.g. `->Othrequired()` meaning it will do this validation if `Giv`en fails
	8. To end `when` use `->endwhen();` **don't forget to `->endwhen()`**.

**Note: All the field-rule definition under `Giv` won't print error. Promise doesn't work when using when.**

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;
	$v 
		->when()

				->Givrequired()
				->Givdigit()
			->endgiven('field1', '')

				->Threquired()
			->endthend('thenprintthis', '')

				->Othrequired()
			->endotherwise('elseprintthis', '')
		->endwhen();
```		
- The rule definition above means, "Given that 'field1' is required and a digit then do is 'thenprintthis' required? otherwise do is 'elseprintthis' required? "

### Or operation in rules
- You could also define or rules with the following:
	1. `->OrGiv[rulename]`
	2. `->OrTh[rulename]`
	3. `->OrOth[rulename]`

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;
	$v 
		->when()

				->OrGivrequired()
				->OrGivdigit()
			->endgiven('field1', '')

				->OrThrequired()
				->OrThDigit()
			->endthend('thenprintthis', '')

				->Othrequired()
			->endotherwise('elseprintthis', '')
		->endwhen();
```
- The rule definition above means, "Given that 'field1' is required or a digit then do is 'thenprintthis' required or a digit? otherwise do is 'elseprintthis' required? ".

### You could define multiple `Giv` and a different operator.
- Valid operators:
	1. AJD_validation::LOG_AND - Default
	2. AJD_validation::LOG_OR
	3. AJD_validation::LOG_XOR

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;
	$v 
		->when()

				->OrGivrequired()
				->OrGivdigit()
			->endgiven('field1', '')

				->Givrequired()
			->endgiven('field2', '', AJD_validation::LOG_OR)

				->OrThrequired()
				->OrThDigit()
			->endthend('thenprintthis1', '')

				->ThDigit()
			->endthend('thenprintthis2', '')

				->Othrequired()
			->endotherwise('elseprintthis', '')
		->endwhen();
```
- The rule definition above means, "Given that ( 'field1' is required or a digit ) or ( 'field2' is required ) then do is 'thenprintthis1' required or a digit?, 'thenprintthis2' is required? otherwise do is 'elseprintthis' required? ".

### Using Logics in `Giv`
- You can also use Logics under `Giv`. Logics are reusable classes that returns either true or false.
- You can use Logics by prefixing logic class name with `Lg` and removing the suffix `_logic` in the logic class name 
	e.g. Lgfirst - First_logic.php is located in src/AJD_validation/Logics
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;
	$v 
		->when()

				->Givrequired()
				->Lgfirst(true)
			->endgiven('field1', '')

				->OrThrequired()
				->OrThDigit()
			->endthend('thenprintthis1', '')

				->Othrequired()
			->endotherwise('elseprintthis', '')
		->endwhen();
```
- The rule definition above means, "Given that ( 'field1' is required and First_logic.php returns true [which in this case it does] ) then do is 'thenprintthis1' required or a digit? otherwise do is 'elseprintthis' required? ".

### Adding custom Logics 
1. We can add Custom Logic by using this:.
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

/*
	Define the directory and namespace you want for your Custom logic 
*/

$v
	->when(true)
	->addLogicClassPath(__DIR__.DIRECTORY_SEPARATOR.'CustomLogics'.DIRECTORY_SEPARATOR)
	->addLogicNamespace('CustomLogics\\')
	->endwhen();
```
2. Logic class must extend to \AJD_validation\Contracts\Abstract_logic and all logic class name must have [Custom]`_logic` as suffix;
```php
namespace CustomLogics;

use AJD_validation\Contracts\Abstract_logic;


class Db_example_logic extends Abstract_logic
{
	protected $mainDb;
	public function __construct($mainDb = null, $validator = null)
	{
		if(!empty($mainDb) && $this->checkDbInstance($mainDb))
		{
			$this->mainDb = $mainDb;
		}
	}

	public function logic( $value, mixed ...$paramaters ) : bool
	{
		$db = null;

		if(!empty($this->mainDb))
		{
			if($this->mainDb  )
			$db = $this->mainDb;
		}
		else 
		{
			if(isset($this->db))
			{
				$db = $this->db;
			}
		}

		if(!empty($db))
		{
			$query = "
				SELECT 	a.*
				FROM 	requests a
				WHERE 	a.request_id = ?
			";
			
			$result = $db->rawQuery($query, [$value]);

			return (!empty($result));
		}
		else
		{
			return false;	
		}
	}
}
```
3. You can use your custom logics by prefixing your custom logic class name with `Lg` and removing `_logic` suffix. 
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v->LgDb_example();
```

4. You can use your custom logic inside `->when()` `->endwhen()` or independently.
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v->LgDb_example()->runLogics('value'); // returns bool (true/false)
/* or */
$v
	->when()
			->Lgfirst(true)
		->endgiven('field1')

			->Threquired()
		->endthen('thenprintthis1')

			->Othrequired()
		->endotherwise('elseprintthis')
	->endwhen();
```
5. You could add logics by using extensions refer to this [Adding custom rules](adding_custom_rules.md). Jump to **Registering custom rule using `$v->registerExtension()`**.

### Difference of `$v->when()` and `Async::when()`
1. `$v->when()` doesn't use php fiber `Async::when()` does.
2. Promises doesn't work in `$v->when()`, Promises works in `Async::when()`.
3. The conditional logic `Giv` doesn't print errors, All field-rule definition inside `Async::when()` prints out errors
4. Only `AJD_validation::LOG_AND` is supported when using `Async::when()`, `, AJD_validation::LOG_OR`, `AJD_validation::LOG_XOR` is supported when using `$v->when()`. Meaning all the field-rule definition inside `Async::when()` is treated as `AJD_validation::LOG_AND`
5. `Async::when()` emulates more of jquery's `$.when()` while `$v->when()` emulates an `if else` statement.

See also:

- [Async](async.md)
- [Adding custom rules](adding_custom_rules.md)
- [Events and Promises](events_promises.md)
- [Scenarios](scenarios.md)
- [Usage](../usage.md)