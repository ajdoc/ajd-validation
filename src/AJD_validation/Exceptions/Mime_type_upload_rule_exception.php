<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Mime_type_upload_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages 	= array(
		self::ERR_DEFAULT 			=> array(
			self::STANDARD 			=> 'Invalid mime type for :field.'
		),
		self::ERR_NEGATIVE 			=> array(
			self::STANDARD 			=> 'Valid mime type for :field.'
		)
	);

	public static $localizeFile     = 'mime_type_upload_rule_rule_err';
}