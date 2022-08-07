<?php 

namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_email;

class Rfc_email_rule extends Abstract_email
{
	public $emailOptions = [
		'showSubError' => true
	];

	public function __construct()
	{
		parent::__construct(null, 'rfc');
	}
}