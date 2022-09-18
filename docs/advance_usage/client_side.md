# Client Side

1. In this document we'll see how to use/create ajd-validation Client Side Component.
2. The idea is that when we create a validation it must be somehow sync on the client side. This component somehow does that thru server rendering.
3. The idea is that client side should already validate what it can validate before making a request to the server. So this does not make a request to the server and the server response the validation definitions.

- **Do note that AJD validation doesn't have a real client side/javascript version, it just somewhat sync/port some of its built in rules to the client side thru server side rendering. Currently it does not have support for javascript frameworks like Vue,React,Svelte and the like but this component gives you the ability to create/support such framework. Maybe thru sending the validation array as json response.**
- **Do note client side component currently supports this libraries.**
	- [jqueryvalidation.js](https://jqueryvalidation.org/documentation/)
	- [parsley.js](https://parsleyjs.org/)

- **Do note client side component only supports the following built in rules.**
```php
$rulesClass = [
	'required', 'required_allowed_zero', // required base rules
	'email', 'base_email', 'rfc_email', 'spoof_email', 'no_rfc_email', 'dns_email', // email base rules
	'in', 'date', 'multiple', // rules with client side support
	'alpha', 'alnum', 'digit', // ctype rules
	'regex', 'mac_address', 'consonant', 'mobileno', 'phone', 'vowel', // regex rules
	'maxlength', 'minlength' // length based rules
];
```

## Basic usage
- Client Side Component defaults to parsley.js
- One must be familiar of the arguments the different rules are requiring so please read first [Rules](rules/).
- `#client_[must_be_the_same_with_the_field_name]` 
	- e.g. `$v->required(null, '#client_field1')->check('field1', '');`

```php
use AJD_validation\AJD_validation
$v = new AJD_validation;

$v->required(null, '#client_email')
	->email([], '#client_email')
	->in(['a@test.com', 'b@test.com'], '#client_email')
	->check('email', '');

	$clientSide = $v->getClientSide();

	echo '<pre>';
	print_r($clientSide);

	/*
		prints 
		Array
		(
		     [customJS] =>   
				 	function inRuleArray(value, haystack, identical)
				 	{
				 		for (var i in haystack) 
				 		{ 
				 			if( identical )
				 			{
				 				if (haystack[i] === value) return true; 
				 			}
				 			else
				 			{
				 				if (haystack[i] == value) return true; 
				 			}
				 		}

				 		return false;
				 	}

					window.Parsley.addValidator('inrule', {
						validate: function(value, requirement, obj) {
							var arr 		= requirement.split('|+');
							var identical 	= false;
							var elem 	= $(obj.element);
						 	var msg 	= $(obj.element).attr('data-parsley-in-message');
							
							if( elem.attr('data-parsley-inrule-identical') )
							{
								identical 	= true;
							}

							var check 	= inRuleArray(value, arr, identical);

							if( !check )
							{
								return $.Deferred().reject(msg);
							}

							return inRuleArray(value, arr, identical);
					},
					messages: {
						en: 'Email must be in { "a@test.com", "b@test.com" }.'
					}
				}); 
		    [rules] => Array
		        (
		        )

		    [messages] => Array
		        (
		        )

		    [email] =>                 data-parsley-required="true" 				data-parsley-required-message="The Email field is required" 	            data-parsley-type="email" 				data-parsley-type-message="The Email field must be a valid email." 
		)
	*/

		return $clientSide;
```
- To use with parsley
```html

<script type="text/javascript">
	$(function()
	{
		<?php echo $clientSide['customJs'] ?>
	});
</script>

<input type="text" <?php echo $clientSide['email'] ?> name="email">
```

- Including the message only for parsley validation
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v 
	->required(null, '#client_email', '#client_message_only')
	->check('email', '');

	// This will just include "data-parsley-required-message="The Email field is required" in the array
```

2. JqueryValidation example
```php
use AJD_validation\AJD_validation

$v = new AJD_validation;

$v->required(null, '#client_email')
	->email([], '#client_email')
	->in(['a@test.com', 'b@test.com'], '#client_email')
	->check('email', '');

	echo '<pre>';
	$client = $v->getClientSide(true, \AJD_validation\Helpers\Client_side::JQ_VALIDATION);
	print_r($client);

	// prints
	/*
		Array
		(
		    [customJS] =>   
				 	function inRuleArray(value, haystack, identical)
				 	{
				 		for (var i in haystack) 
				 		{ 
				 			if( identical )
				 			{
				 				if (haystack[i] === value) return true; 
				 			}
				 			else
				 			{
				 				if (haystack[i] == value) return true; 
				 			}
				 		}

				 		return false;
				 	}

				 	jQuery.validator.addMethod('in', function(value, element, params) 
					{
						var arr 		= params[0].split('|+');
						var identical 	= params[1] || false;

						return this.optional(element) || inRuleArray(value, arr, identical);

					}, 'Email must be in { "a@test.com", "b@test.com" }.'); 
		    [rules] => Array
		        (
		            [email] => Array
		                (
		                    [required] => 1
		                    [email] => 1
		                    [in] => Array
		                        (
		                            [0] => a@test.com|+b@test.com
		                            [1] => true
		                        )

		                )

		        )

		    [messages] => Array
		        (
		            [email] => Array
		                (
		                    [required] => The Email field is required
		                    [email] => The Email field must be a valid email.
		                    [in] => Email must be in { "a@test.com", "b@test.com" }.
		                )

		        )

		)
	*/

		return $client
```

- To use with jqueryvalidation
```html
<?php 
	$clientSide = $client;

	unset($clientSide['customJS']);
?>

<script type="text/javascript">
	$().ready(function() {

		<?php 
			if(!empty($client['customJS']))
			{
				echo $client['customJS'];
			}
		?>

		$('#yourForm').validate(<?php echo json_encode($clientSide) ?>);
	});
</script>
```

3. Client Side per rules
```php
use AJD_validation\AJD_validation;

$v->required(null, '#client_email')
	->email([], '#client_email')
	->check('email', '');

	echo '<pre>';
	print_r($v->getClientSide(false));
/*
	prints
Array
(
    [customJS] => Array
        (
            [0] => 
            [1] => 
            [2] => 
		 	function inRuleArray(value, haystack, identical)
		 	{
		 		for (var i in haystack) 
		 		{ 
		 			if( identical )
		 			{
		 				if (haystack[i] === value) return true; 
		 			}
		 			else
		 			{
		 				if (haystack[i] == value) return true; 
		 			}
		 		}

		 		return false;
		 	}

			window.Parsley.addValidator('inrule', {
				validate: function(value, requirement, obj) {
					var arr 		= requirement.split('|+');
					var identical 	= false;
					var elem 	= $(obj.element);
				 	var msg 	= $(obj.element).attr('data-parsley-in-message');
					
					if( elem.attr('data-parsley-inrule-identical') )
					{
						identical 	= true;
					}

					var check 	= inRuleArray(value, arr, identical);

					if( !check )
					{
						return $.Deferred().reject(msg);
					}

					return inRuleArray(value, arr, identical);
			},
			messages: {
				en: 'Email must be in { "a@test.com", "b@test.com" }.'
			}
		});
        )

    [clientSideJson] => Array
        (
        )

    [clientSideJsonMessages] => Array
        (
        )

    [email] => Array
        (
            [required] =>                 data-parsley-required="true" 				data-parsley-required-message="The Email field is required"
            [email] => 	            data-parsley-type="email" 				data-parsley-type-message="The Email field must be a valid email."
            [in] =>                 data-parsley-inrule='a@test.com|+b@test.com'
                data-parsley-inrule-identical='true' 				data-parsley-in-message="Email must be in { "a@test.com", "b@test.com" }."
        )

)

*/
```

4. Using with `->sometimes()`
- Must pass `true` as thrid argument in `->sometines(mixed $sometimes, string $ruleOverride = null, bool $forJs = false)`
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v->required(null, '#client_email')
	->email([], '#client_email')->sometimes('sometimes', null, true)
	->check('email', '');
```

## Practical Example
- Let's say you want to render your validation definition so that it must be somehow sync with the client side.

```php
use AJD_validation\AJD_validation;

class PostController
{
	public function index()
	{
		$data = [];

		try
		{
			$client = $this->validate([], true);

			$data['client'] = $client;

			return passtoview('yourview', $data);
		}
		catch(\Exception $e)
		{
			echo $e->getMessage();
		}
	}

	protected function validate(array $params = [], $forJs = false)
	{
		try
		{
			$v 
				->required(null, '#client_firstname')
				->minlength(2, true, true, '#client_firstname')->sometimes('sometimes', null, $forJs)
				->check('firstname', '');

			$v 
				->required(null, '#client_email')
				->email([], '#client_email')->sometimes('sometimes', null, $forJs)
				->check('email')

			if($forJs)
			{
				return $v->getClientSide();
			}
			else
			{
				$v->assert(false);
			}

		}
		catch(\Exception $e)
		{
			throw $e;
		}
	}

	public function create()
	{
		$msg = '';
		try
		{
			$params = $_POST;

			$this->validate($params);
		}
		catch(\Exception $e)
		{
			$msg = $e->getMessage();
		}

		return response(['msg' => $msg]);
	}
}


// in the view you if you are using parsley js
<input type="text" name="firstname" <?php echo $client['firstname'] ?>>
<input type="text" name="email" <?php echo $client['firstname'] ?>>

<script>
<?php echo $client['customJs'] ?>
// then do your parsley js please see the link for the documentation of parsley js
</script>

// in the view you if you are using jquery validation js
<?php 
	$clientSide = $client;

	unset($clientSide['customJs'])
?>
<form id="yourform">
	<input type="text" name="firstname" id="firstname">
	<input type="text" name="email" id="email">
</form>

<script>
$(function()
{
	<?php echo $client['customJs'] ?>
	// then do your jquery validation js please see the link for the documentation of jquery validation js
	$('#yourform').validate(<?php json_encode($clientSide) ?>)
});
</script>
```
- Here if you removed for example the 'required' in the 'firstname' then refresh it will also reflect on the client side you won't have to remove any 'required' definition on the client side.

## Overriding/Creating client side component
```php
use AJD_validation\AJD_validation

$v = new AJD_validation

$v::registerCustomClientSide('email', function(string $field, string $rule, mixed $satisfier = null, string $error = null, mixed $value = null)
	{
		if( static::$jsTypeFormat == static::$ruleObj::CLIENT_PARSLEY ) 
        {
	 		$js[$field][$rule]['rule'] =   <<<JS
	            data-parsley-type="email"
JS;

			$js[$field][$rule]['message'] = <<<JS
                data-parsley-type-message="This is my override message"
JS;

		}

		$js = static::$ruleObj->processJsArr( $js, $field, $rule, static::$clientMessageOnly );
		
        return $js;
	});

/*this will override the default parsley validation for email rule*/

$v->email([], '#client_email')
->check('email');

echo '<pre>';
print_r($v->getClientSide());
/*
prints 
Array
(
    [email] => 					data-parsley-required="false" 	            data-parsley-type="email"                 data-parsley-type-message="This is my override message" 
    [customJS] => 
    [rules] => Array
        (
        )

    [messages] => Array
        (
        )

)
*/
```
- If you want to override it for a specific field just pass the field name as third argument
```php
use AJD_validation\AJD_validation

$v = new AJD_validation

$v::registerCustomClientSide('email', function(string $field, string $rule, mixed $satisfier = null, string $error = null, mixed $value = null)
	{
		if( static::$jsTypeFormat == static::$ruleObj::CLIENT_PARSLEY ) 
        {
	 		$js[$field][$rule]['rule'] =   <<<JS
	            data-parsley-type="email"
JS;

			$js[$field][$rule]['message'] = <<<JS
                data-parsley-type-message="This is my override message"
JS;

		}

		$js = static::$ruleObj->processJsArr( $js, $field, $rule, static::$clientMessageOnly );
		
        return $js;
	}, 'new_email');

/*this will override the default parsley validation for new_email field*/

$v->email([], '#client_email')
->check('email');

$v->email([], '#client_new_email')
->check('new_email');

echo '<pre>';
print_r($v->getClientSide());
/*
prints 
Array
(
    [email] => 					data-parsley-required="false" 	            data-parsley-type="email" 				data-parsley-type-message="The Email field must be a valid email." 
    [customJS] =>   
    [rules] => Array
        (
        )

    [messages] => Array
        (
        )

    [new_email] => 					data-parsley-required="false" 	            data-parsley-type="email"                 data-parsley-type-message="This is my override message" 
)
*/
```

- This could also be used to create custom client side component for rules that does not have it
```php
use AJD_validation\AJD_validation

$v = new AJD_validation

// Custom Rule Registration
$v->registerRulesMappings([
		\AJD_validationa\Folder_custom_rule::class => \AJD_validationa\Exceptions\Folder_custom_rule_exception::class
	]);

	$v::registerCustomClientSide('folder_custom', function(string $field, string $rule, mixed $satisfier = null, string $error = null, mixed $value = null)
	{
		if( static::$jsTypeFormat == static::$ruleObj::CLIENT_PARSLEY ) 
        {
	 		$js[$field][$rule]['rule'] =   <<<JS
	            data-parsley-$rule="$satisfier[0]"
JS;

			$js[$field][$rule]['message'] = <<<JS
                data-parsley-$rule-message="$error"
JS;

			$js[$field][$rule]['js'] = <<<JS
                function myCustomJsForTheJsLibrary()
                {

                }
JS;

		}

		$js = static::$ruleObj->processJsArr( $js, $field, $rule, static::$clientMessageOnly );
		
        return $js;
	});

/*this will override the default parsley validation for email rule*/

$v->folder_custom(1, '#client_email')
->check('email', '');

echo '<pre>';
print_r($v->getClientSide());
/*
prints 
Array
(
    [email] => 					data-parsley-required="false" 	            data-parsley-folder_custom=""                 data-parsley-folder_custom-message="Email with folder is true." 
    [customJS] =>                 function myCustomJsForTheJsLibrary()
                {

                }
    [rules] => Array
        (
        )

    [messages] => Array
        (
        )

)
*/
```

## Creating Custom Client Side File
- To create a custom client Side Class one must extend to `\AJD_validation\Contracts\AbstractClientSide`
- All custom client side class must have a suffix of the following
	- If using snake case `_client_side`
	- If using camel case `ClientSide`
- One must implement `getCLientSideFormat(string $field, string $rule, mixed $satisfier = null, string $error = null, mixed $value = null) : array` and must return array with key
	- Parsley must have array return with key 
		- 'rule', 'message', (optional) 'js' - customJs

	- Jquery Validation must have array return with key 
		- 'clientSideJson', 'clientSideJsonMessages', (optional) 'js' - customJs

- Extending To \AJD_validation\Contracts\AbstractClientSide you will have access to
 - object `static::$ruleObj` -> rule instance,
 - string `static::$jsTypeFormat` -> what kind of javascript library 
 - bool `static::$clientMessageOnly` -> useful for parsley js checking if client side only requires error message
- You will also have access to related rules
```php
protected static $relatedEmailRules = [
	'email', 'base_email', 'rfc_email', 'spoof_email', 'no_rfc_email', 'dns_email'
];

protected static $relatedRequiredRules = [
	'required', 'required_allowed_zero'
];

protected static $relatedInRule = [
	'in'
];

protected static $relatedDateRule = [
	'date'
];

protected static $relatedMultipleOf = [
	'multiple'
];

protected static $relatedRegex = [
	'regex', 'mac_address', 'consonant', 'mobileno', 'phone', 'vowel', // regex rules
];

protected static $relatedCtype = [
	'alpha', 'alnum', 'digit', // ctype rules
];

protected static $relatedLength = [
	'maxlength', 'minlength', 'min', 'max'
];
```

```php
namespace PackageAjd\ClientSides;

use AJD_validation\Contracts\AbstractClientSide;

class Package_client_side extends AbstractClientSide
{
	public static function getCLientSideFormat(string $field, string $rule, mixed $satisfier = null, string $error = null, mixed $value = null)
	{
		if(static::$jsTypeFormat == \AJD_validationa\Helpers\Client_side::CLIENT_PARSLEY)
		{
			$js[$field][$rule]['rule'] =   <<<JS
                data-parsley-$rule="true"
JS;
			$js[$field][$rule]['message'] = <<<JS
				data-parsley-$rule-message="$error"
JS;
		}
		else if(static::$jsTypeFormat == \AJD_validationa\Helpers\Client_side::JQ_VALIDATION)
		{
			$js['clientSideJson'][$field][$rule] = true;
			$js['clientSideJsonMessages'][$field][$rule] = $error;
		}

		// if you want to create a custom js see the documentation for the current jquery validation in use e.g

		if(static::$jsTypeFormat == \AJD_validationa\Helpers\Client_side::CLIENT_PARSLEY)
		{
			$js[$field][$rule]['js'] =   <<<JS
			window.Parsley.addValidator('multipleof', {
				validateNumber: function(value, requirement) {
					return value % requirement === 0;
			},
			requirementType: 'integer',
			messages: {
				en: '$error'
			}
		});
JS;
		}
		else if(static::$jsTypeFormat == \AJD_validationa\Helpers\Client_side::JQ_VALIDATION)
		{
			$js[$field][$rule]['js'] =   <<<JS

			jQuery.validator.addMethod('multipleof', function(value, element, params) 
			{
				return this.optional(element) || value % params === 0;
			}, '$error');
JS;
		}

		$js = static::$ruleObj->processJsArr( $js, $field, $rule, static::$clientMessageOnly );


		return $js;
	}
}
```

## Registering custom Client Side Class
- We can register custom client side class in two ways

1. Thru `registerClientSideMapping`
	- must specify for which rule we are applying the custom client side so in this instance just remove the suffix `_client_side` or `ClientSide` so its `package`.

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v->registerClientSideMapping(['package' => \PackageAjd\ClientSides\Package_client_side::class]);
```

2. Thru `->addClientSideDirectory()` && `->addClientSideNamespace()`
- here we must register the directory where the client sides class are located and register its namespace.
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v
	->addClientSideDirectory(__DIR__.DIRECTORY_SEPARATOR.'PackageAjd'.DIRECTORY_SEPARATOR.'ClientSides'.DIRECTORY_SEPARATOR)
	->addClientSideNamespace('PackageAjd\\ClientSides\\');
```

## Registering custom client side inside an extension
- We could also register a custom client side inside an extension class
```php
// Example of a extension class
namespace PackageAjd\Extensions;

use AJD_validation\Contracts\Base_extension;
use AJD_validation\AJD_validation;

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
		);
	}

	public function getRuleMessages()
	{
		return array(
			'custom_validation' 	=> 'The :field field is not a a.',
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

/*
	1. Register the extension object.
*/
$extension 	= new Custom_extension;
$v->registerExtension($extension);

$v->custom_validation(null, '#client_email')->check('email', '');

/*prints
array(5) {
  ["emailse"]=>
  string(523) "data-parsley-custom_validation="email" data-parsley-custom_validation-message="The Email field is not a a."                 
  ["customJS"]=>
  string(3) "   "
  ["rules"]=>
  array(0) {
  }
  ["messages"]=>
  array(0) {
  }
}
*/
```

## Registering a new JsValidationLibrary
- To register a new JsValidationLibrary
```php
use AJD_validation\AJD_validation;

AJD_validation::addJSvalidationLibrary('myjs');

$v->getClientSide(true, 'myjs');
```

- **Do note that if you want that the built in rules to work with this new JsValidationLibrary you must create for example `MyjsAjDCommonClientSide.php`**

```php
// create the logic/config for the new js validation library to work
namespace PackageAjd\ClientSides;

use AJD_validation\Contracts\AbstractClientSide;

class MyjsAjdCommonClientSide extends AbstractClientSide
{
	public static function getCLientSideFormat(string $field, string $rule, mixed $satisfier = null, string $error = null, mixed $value = null)
	{
		$js['clientSideJson'][$field][$rule] = true;
		$js['clientSideJsonMessages'][$field][$rule] = $error;

		$js = static::$ruleObj->processJsArr( $js, $field, $rule, static::$clientMessageOnly );

		return $js;
	}
}
```

See also:

- [Filter Usage](filters.md)
- [Advance Usage](advance_usage/)
- [Rules](rules/)
- [Filters](filters/)
- [Alternative Usage](alternative_usage.md)