<?php 

namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_email;

class Spoof_email_rule extends Abstract_email
{
	public $emailOptions = [
		'showSubError' => true
	];

	public function __construct()
	{
		if (extension_loaded('intl'))
		{
			parent::__construct(null, 'spoof');
		}

	}
}