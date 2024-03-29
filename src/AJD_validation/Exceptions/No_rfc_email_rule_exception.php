<?php 
namespace AJD_validation\Exceptions;

use AJD_validation\Contracts\Abstract_exceptions;

class No_rfc_email_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages = [
		 self::ERR_DEFAULT => [
		 	self::STANDARD => 'The :field field must be a valid rfc warning email.',
		 ],
		  self::ERR_NEGATIVE => [
            self::STANDARD => 'The :field field must not be a valid rfc warning email.',
        ]
	];

	public static $localizeFile = 'no_rfc_email_rule_err';
}