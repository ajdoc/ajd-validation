<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Common_invokable_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages 	= array(
		 self::ERR_DEFAULT 			=> array(
		 	self::STANDARD 			=> 'The :field field is correct',
		 ),
		  self::ERR_NEGATIVE 		=> array(
            self::STANDARD 			=> 'The :field field is not correct.',
        )
	);

	public static $localizeFile 	= 'common_invokable_rule_err';

	public function setMessage(array $message = [])
	{
		$messageNew = self::$defaultMessages;

		if(!empty($message))
		{
			foreach($message as $style => $customMessages)
			{
				/*if(isset(self::$defaultMessages[$style]))
				{*/
					foreach($customMessages as $kind => $customMessage)
					{
						$messageNew[$style][$kind] = $customMessage;
					}
					
				// }
			}
		}
		
		static::$defaultMessages = $messageNew;
		return $messageNew;
	}

	public function message($check, array $messages = [])
	{
		if(
			!$check
			||
			($this->hasParam('inverse') && $this->getParam('inverse') && $check)
		)
		{
			
			return $this->setMessage($messages);
		}
	}
}