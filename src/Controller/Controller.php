<?php

namespace Spidesu\Gosling\Controller;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Spidesu\Gosling\System\Singleton;

abstract class Controller extends Singleton {

	protected array $_method_list = [];
	protected array $_protected_method_list = [];

	public function work(Message $message, Discord $discord, string|false $method = false, array $args = []):void {

		if (!$method || !$message->guild_id) {
			$this->default($message, $discord);
			return;
		}

		// выполняем метод, если он доступен для всех
		if (in_array($method, $this->_method_list)) {
			$this->$method($message, $discord, $args);
			return;
		}

		// проверяем, что метод есть в методах только для админов
		if (in_array($method, $this->_protected_method_list)) {

			// проверяем, что пользователь админ, и выполняем метод
			if ($message->member->getPermissions()->manage_roles) {

				$this->$method($message, $discord, $args);
				return;
			}

			$message->channel->sendFile(APP_DIR . "/media/papey-gavna-original.jpg");
		}
	}

	/**
	 * Дефолтное действие, когда передали команду без параметров
	 *
	 * @param Message $message
	 * @param Discord                        $discord
	 *
	 * @return void
	 */
	abstract public function default(Message $message, Discord $discord):void;


	abstract public function help(Message $message, Discord $discord):void;

}