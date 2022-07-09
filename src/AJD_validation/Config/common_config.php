<?php 

$config_ds 					= defined('CONFIG_DS') ? define( 'CONFIG_DS', DIRECTORY_SEPARATOR ) : DIRECTORY_SEPARATOR;
/*
* This path points where the symfony validator constraints are,
* and is usually this is the path if the symfony validator component is not use as a standalone compent.
* If the symfony component is used as a standalone component please change the path where the symfony validator constraints is located.
*/
$symfony_path 				= dirname( dirname( __DIR__ ) );
$symfony_path 			   .= $config_ds.'..'.$config_ds.'vendor'.$config_ds.'symfony'.$config_ds.'symfony'.$config_ds.'src'.$config_ds.'Symfony'.$config_ds;
$symfony_path 			   .= 'Component'.$config_ds.'Validator'.$config_ds.'Constraints'.$config_ds;

$config 	= array(

	'db_config' => array(
		'driver'	=> 'mysql',
		'host'		=> '127.0.0.1',
		'dbname' 	=> 'try',
		'dbuser' 	=> 'root',
		'dbpass' 	=> ''
	),
	'symfony_path' 	=> $symfony_path

);

return $config;

