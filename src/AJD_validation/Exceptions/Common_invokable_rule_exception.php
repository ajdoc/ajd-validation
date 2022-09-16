<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Constants\Lang;

class Common_invokable_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages 	= [
		 self::ERR_DEFAULT => [
		 	self::STANDARD => 'The :field field is correct',
		 ],
		  self::ERR_NEGATIVE => [
            self::STANDARD => 'The :field field is not correct.',
        ]
	];

	public static $localizeMessage = [
		Lang::FIL => [
			self::ERR_DEFAULT => [
			 	self::STANDARD => 'The :field field ay mali',
			 ],
			  self::ERR_NEGATIVE => [
	            self::STANDARD => 'The :field field ay hindi mali.',
	        ],
		]
	];

	public static $localizeFile = 'common_invokable_rule_err';

	public function setMessage(array $message = [])
	{
		$messageNew = self::$defaultMessages;

		if(!empty($message))
		{
			if(isset($message[static::$lang])
				&& !empty($message[static::$lang])
			)
			{
				$message = $message[static::$lang];
			}

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