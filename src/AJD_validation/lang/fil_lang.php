<?php 

use AJD_validation\Contracts\Abstract_exceptions;

$lang 				= array();

$lang['error_msg']  = array(


	'is_array'		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay dapat isang tipo ng php array.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay di dapat isang tipo ng php array.'
		)
	),
	'is_numeric'	=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay dapat isang tipo ng numero.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay di dapat isang tipo ng numero.'
		)
	),	
	'is_int' 		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay dapat isang tipo ng integer.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay di dapat isang tipo ng integer.'
		)
	),
	'is_float' 		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay dapat isang tipo ng float.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay di dapat isang tipo ng float.'
		)
	),
	'is_string' 		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay dapat isang tipo ng string.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay di dapat isang tipo ng string.'
		)
	),
	'is_object' 		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay dapat isang tipo ng object.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay di dapat isang tipo ng object.'
		)
	),
	'is_callable' 		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay dapat isang tipo ng callable.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay di dapat isang tipo ng callable.'
		)
	),
	'is_bool' 			=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay dapat isang tipo ng boolean.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay di dapat isang tipo ng boolean.'
		)
	),
	'is_null' 			=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay dapat null.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay di dapat null.'
		)
	),
	'is_resource' 		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay dapat isang resource.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay di dapat isang resource.'
		)
	),
	'is_scalar' 		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay dapat isang scalar.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay di dapat isang scalar.'
		)
	),
	'is_finite' 		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay dapat isang finite na numero.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay di dapat isang finite na numero.'
		)
	),
	'is_infinite'		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay dapat isang infinite na numero.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay di dapat isang finite na numero.'
		)
	),
	'in_array' 			=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay dapat na sa :*.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay dapat wala sa :*.'
		)
	),
	'preg_match'		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay dapat ":0".'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay di dapat ":0".'
		)
	),
	'filter_var' 		=> array(
		Abstract_exceptions::ERR_DEFAULT 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay dapat tama.'
		),
		Abstract_exceptions::ERR_NEGATIVE 	=> array(
			Abstract_exceptions::STANDARD 	=> ':field ay di dapat tama.'
		)
	)

);


return $lang;