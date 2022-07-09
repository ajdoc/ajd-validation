<?php 

use AJD_validation\Contracts\Abstract_exceptions;

$lang 				= array();

$lang['error_msg']  = array(

	'is_array'		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must be a php array.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must not be a php array.'
		)
	),
	'is_numeric'	=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must be numeric.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must not be numeric.'
		)
	),	
	'is_int' 		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must be of the type integer.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must not be of the type integer.'
		)
	),
	'is_float' 		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must be of the type float.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must not be of the type float.'
		)
	),
	'is_string' 		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must be a string.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must not be a string.'
		)
	),
	'is_object' 		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must be an object.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must not be an object.'
		)
	),
	'is_callable' 		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must be a callable.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must not be a callable.'
		)
	),
	'is_bool' 			=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must be a boolean.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must not be a boolean.'
		)
	),
	'is_null' 			=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must be null.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must not be null.'
		)
	),
	'is_resource' 		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must be a resource.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must not be a resource.'
		)
	),
	'is_scalar' 		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must be a scalar value.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must not be a scalar value.'
		)
	),
	'is_finite' 		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must be a finite number.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must not be a finite number.'
		)
	),
	'is_infinite'		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must be an infinite number.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must not be an infinite number.'
		)
	),
	'in_array' 			=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must be in :*.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must not be in :*.'
		)
	),
	'preg_match'		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must validate against ":0".'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must not validate against ":0".'
		)
	),
	'filter_var' 		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must be valid.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field must not be valid.'
		)
	)
);


return $lang;