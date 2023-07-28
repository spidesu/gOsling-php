<?php

namespace Spidesu\Gosling\Controller;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Spidesu\Gosling\Model\CacheItemData\UserContextItem;
use Spidesu\Gosling\System\Singleton;

abstract class Context extends Singleton {

	protected array $_action_list = [];

	public function work(Message $message, Discord $discord, UserContextItem $context):void {

		// выполняем метод, если он доступен для всех
		if (in_array($context->context_data->action, $this->_action_list)) {

			$action = $context->context_data->action;
			$this->$action($message, $discord, $context);
		}
	}
}