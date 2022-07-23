<?php namespace AJD_validation\Helpers;

use AJD_validation\Helpers\When;

final class Logics_map
{
	protected $when;
	protected $logics;

	public function __construct(When $when, array $logics)
	{
		$this->when = $when;
		$this->logics = $logics;
	}

	public function deferToWhen()
	{
		return $this->when;
	}
}