<?php 

namespace AJD_validation\Formatter;

use AJD_validation\Formatter\FormatterInterface;
use AJD_validation\Contracts\Abstract_exceptions;

abstract class AbstractFormatter implements FormatterInterface
{
	protected $options = [];

	public function format(string $messages, Abstract_exceptions $exception, $field = null, $satisfier = null, $value = null)
	{
		return null;
	}

	public function setOptions(array $options)
	{
		$this->options = $options;
	}

	public function appendOptions(array $options)
	{
		$this->options = array_merge($this->options, $options);
	}

	public function getOptions()
	{
		return $this->options;
	}
}