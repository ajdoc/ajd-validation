<?php 

namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_email;

class Base_email_rule extends Abstract_email
{
	public $emailOptions = [
		'showSubError' => false
	];

	public function __construct()
	{
		parent::__construct(null, 'default');
	}
}