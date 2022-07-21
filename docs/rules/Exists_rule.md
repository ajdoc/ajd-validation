# Exists

- `exists()`
- `Exists_rule('db_config_key|table=tablename|primary_id=column_primary_id')`

Validates whether the input value exists in the defined database table. **Currently supports mysql database only**. **must add database connection first else validation will throw an error message database is required.**

```php
use AJD_validation\AJD_validation as v;

$v = new v;

// adding a database connection

/*
	We have two ways of adding a database connection and must be added first before doing any validation.
*/

/*
	Example 1 adding using array config
		- first paramater is schema_name or a unique key name in identifying the connection
		- second paramater is an array of config 
			- first key is the connection as you would like in instatiating a new PDO object
			- second key is db user
			- third key is db password 
			- fourth is an optional array of extra configs
*/
$v->addDbConnection(
	'must_be_the_schema_name_or_unique_key_name', 
	[
		'mysql:host=127.0.0.1;port=3306;dbname=schema_name',
		'root',
		'default'
	]
);

/*
	Example 2 is passing the PDO object itself. This is useful in using ajd_validation library in a framework
*/
$v->addDbConnection(
	'must_be_the_schema_name_or_unique_key_name', 
	new PDO(
		'mysql:host=127.0.0.1;port=3306;dbname=schema_name',
		'root',
		'default'
	)
);

/*
	Example 2.1 adding db connection for example in laravel framework
*/
$v->addDbConnection(
	'must_be_the_schema_name_or_unique_key_name', 
	\DB::connection('your_database_config_key_in_laravels_config_database.php')->getPdo()
);

/*
	After you have added your db connection you can use exists rule
*/

$v->exists('must_be_the_schema_name_or_unique_key_name|table=tablename|primary_id=column_primary_id')->check('exists_field', 3); // will put error in error bag if value doesn't exists in table
$v->getValidator()->exists('must_be_the_schema_name_or_unique_key_name|table=tablename|primary_id=column_primary_id')->validate(3); // false if value doesn't exists in table

$v->exists('must_be_the_schema_name_or_unique_key_name|table=tablename|primary_id=column_primary_id')->check('exists_field', 5); // validation passes if value exists in table
$v->getValidator()->exists('must_be_the_schema_name_or_unique_key_name|table=tablename|primary_id=column_primary_id')->validate(5); // true if value exists in table

/*
	When excluding a specific row in checking if exists
*/
	

$v->exists('must_be_the_schema_name_or_unique_key_name|table=tablename|primary_id=column_primary_id|exclude_id=exclued_id|exclude_value=2')->check('exists_field', 5); // validation passes if value exists in table
$v->getValidator()->exists('must_be_the_schema_name_or_unique_key_name|table=tablename|primary_id=column_primary_id|exclude_id=exclued_id|exclude_value=2')->validate(5); // true if value exists in table

/*
	A good example is if the email already exists in the users table so in these example we will also inverse the validation which is discussed in usage.md
*/

$v 
	->required()
	->email()->sometimes()
	->Notexists(
		'must_be_the_schema_name_or_unique_key_name|table=users|primary_id=email_column|exclude_id=user_id|exclude_value=1', 
		'@custom_error_The email address entered has already been used. Please use a different email.'
	)->sometimes()
	
	->check('email', 'test@yopmail.com');

	// will put error in error bag if value already exists in users table but at the same time we will exclude user_id = 1 in checking, so in this scenario the action is most likely update is exclude_value is not null or empty.

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***

See also:
- [Usage](../usage.md)
- [Scenarios](scenarios.md)