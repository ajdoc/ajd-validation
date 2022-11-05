<?php 

namespace AJD_validation\Formatter;

use AJD_validation\Formatter\AbstractFormatter;
use AJD_validation\Contracts\Abstract_exceptions;

class RequiredFormatter extends AbstractFormatter
{
	public function format(string $messages, Abstract_exceptions $exception, $field = null, $satisfier = null, $value = null)
	{
		$options = $this->getOptions();
		$cnt = $options['valueKey'] ?? 0;

		$satis_str = $satisfier[0] ?? '';
		
		$cnt = $cnt + 1;
		$addtional_option = $options['addtional'] ?? '';
		
		$message = 'This :field is required at row {cnt} with a satisfier of. '.$satis_str.' '.$addtional_option.'.';
		$message = $exception->replaceErrorPlaceholder(['cnt' => $cnt], $message);

		return $message;
	}
}