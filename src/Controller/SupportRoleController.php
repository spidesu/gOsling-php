<?php

namespace Spidesu\Gosling\Controller;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Role;
use Spidesu\Gosling\Repository\GuildRepository;
use Spidesu\Gosling\System\Config;

class SupportRoleController extends Controller {

	protected array $_protected_method_list = [
		"role",
	];

	/**
	 * Установить роль для саппортов
	 *
	 * @param Message $message
	 * @param Discord $discord
	 * @param array   $args
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function role(Message $message, Discord $discord, array $args):void {

		if (count($args) < 1) {

			$message->channel->sendMessage("Не введена роль");
			return;
		}

		$role_id = $args[1];
		$discord_role_id = $args[0];

		$discord_role = $message->channel->guild->roles->get('id', $role_id);

		if (!$discord_role ) {
			$message->channel->sendMessage("Введеная роль не является ролью Server Booster");
			return;
		}

		$support_role = $message->channel->guild->roles->get('id', $role_id);

		if (!$support_role) {
			$message->channel->sendMessage("Введеная роль не найдена");
			return;
		}

		GuildRepository::setConfigValues($message->guild_id, ["discord_support_role" => $discord_role_id, "support_role" => $role_id]);
		$message->channel->sendMessage("Роль саппорта установлена");

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

		$translation = Config::instance()->getTranslation()["support"][Config::instance()->getDefaultLanguage()];

		$message->channel->sendMessage($translation);
	}

	public function default(Message $message, Discord $discord):void {
		$this->help($message, $discord);
	}
}