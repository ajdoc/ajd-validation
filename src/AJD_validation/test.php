<?php 

// error_reporting(0);

require 'AJD_validation.php';

use AJD_validation\Contracts\Base_extension;
use AJD_validation\AJD_validation as v;

try
{
	$v 	= new v;
	
	$validator = $v->getValidator();

	$v->addFilterNamespace('')
		->addFilterDirectory( dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Filters'.DIRECTORY_SEPARATOR );

	$v->addRuleNamespace('')
		->addRuleDirectory(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'AA'.DIRECTORY_SEPARATOR);

	$v->add_rule_msg('test_custom', 'sup');
	$v->registerClass('test_custom', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Rules', 'Rules\\' );

	$v->addDbConnection('name', new PDO('mysql:host=127.0.0.1;port=3306;dbname=my_db', 'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)));

	$v->required()->email_available('name')->check('email', 'thedoctorisin17@gmail.com');

	$v->key('foo', $validator->digit()->odd())->check('key_test', array('foo' => 'bar'), FALSE);

	$v->when()
		->Givrequired()
		->Givdigit()
		->endgiven('alle', '1')
		->Givrequired()
		->endgiven('a2', 'aa')
		->Threquired()
		->endthen('then')
		->Othrequired()
		->endotherwise('otherwise')
	->endwhen();

	$v->Ftest_custom()
		->required()
		->alpha()
		->test_custom()
		->check('aa|Al', '');

	$v->required()
		->digit()
		->check('ab', '1.00');

	$v->required()
		->contains('ipsuma')
		->check('orme', ['lorem', 'ipsu'], FALSE);

	$v->assert();
}
catch( PDOException $e )
{
	echo $e->getMessage();
}
catch( Exception $e )
{
	echo $e->getMessage();
}