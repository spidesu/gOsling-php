<?php

namespace Spidesu\Gosling\Controller;

use Discord\Discord;
use Spidesu\Gosling\System\Singleton;

class MessageCreateHandler extends Singleton {

	/**
	 * @var array|Controller[]
	 */
	private array $_command_list = [
		"birthday" => BirthdayController::class,
	];

	public function process(\Discord\Parts\Channel\Message $message, Discord $discord):void {

		if (mb_substr($message->content, 0, 2) !== "g!") {
			return;
		}

		$exploded = explode(" ", $message->content);
		$command = explode("g!", array_shift($exploded))[1];

		if (!in_array($command, array_keys($this->_command_list))) {
			return;
		}

		if (count($exploded) > 0) {

			$method = array_shift($exploded);

			$this->_command_list[$command]::instance()->work($message, $discord, $method, $exploded);
			return;
		}

		$this->_command_list[$command]::instance()->work($message, $discord);
	}
}