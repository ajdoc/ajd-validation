<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Mime_type_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages 	= array(
		self::ERR_DEFAULT 			=> array(
			self::STANDARD 			=> ':field must have {mimetype} mimetype.'
		),
		self::ERR_NEGATIVE 			=> array(
			self::STANDARD 			=> ':field must not have {mimetype} mimetype.'
		)
	);

	public static $localizeFile     = 'mime_type_rule_rule_err';
}