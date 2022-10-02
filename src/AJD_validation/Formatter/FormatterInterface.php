<?php 

namespace AJD_validation\Formatter;
use AJD_validation\Contracts\Abstract_exceptions;

interface FormatterInterface 
{
	public function format(string $messages, Abstract_exceptions $exception, $field = null, $satisfier = null, $value = null);

	public function setOptions(array $options);

	public function appendOptions(array $options);

	public function getOptions();
}