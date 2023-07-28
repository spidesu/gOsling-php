<?php

namespace Spidesu\Gosling\Controller;

use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Role;
use Discord\Parts\User\Member;
use Spidesu\Gosling\Repository\GuildRepository;
use Spidesu\Gosling\System\Config;

class YMController extends Controller {

	protected array $_method_list = [
		"help",
		"play",
	];

	protected array $_protected_method_list = [
		"token",
	];

	public function play(Message $message, Discord $discord):void {
		
		//TODO убрать в контекст (ТЕСТ)
        $channel = $message->member->getVoiceChannel();
		$discord->joinVoiceChannel($channel, true);
	}

	/**
	 * Помощь по команде
	 *
	 * @param Message $message
	 * @param Discord $discord
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function help(Message $message, Discord $discord):void {

		$translation = Config::instance()->getTranslation()["ym"][Config::instance()->getDefaultLanguage()];

		$message->channel->sendMessage($translation);
	}

	public function default(Message $message, Discord $discord):void {
		$this->help($message, $discord);
	}
}