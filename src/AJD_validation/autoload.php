<?php 

require_once ( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'Autoload'.DIRECTORY_SEPARATOR.'Autoload.php' );

define( 'SEP', DIRECTORY_SEPARATOR );

$dir_root   = dirname( __FILE__ );

$auto       = new \AJD_Autoload\Loady( $dir_root );

$auto->setAuto( array(
    'Factory',
    'Vefja',
    'Observer',
    'Helpers',
    'Config',
    'Contracts',
    'Extensions',
    'Exceptions',
    'Third_party',
    'Uncompromised'
) );

$auto->register(TRUE, TRUE);