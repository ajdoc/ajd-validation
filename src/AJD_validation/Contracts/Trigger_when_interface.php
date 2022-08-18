<?php 

namespace AJD_validation\Contracts;
use AJD_validation\Async\PromiseNull;

interface Trigger_when_interface 
{
	public function checker($value = null, $checker = null);

	public function getPromiseNul() : PromiseNull;
}