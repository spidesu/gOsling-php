<?php

namespace Spidesu\Gosling\Task;

use Discord\Discord;
use Discord\Parts\User\Member;
use JetBrains\PhpStorm\ArrayShape;
use Spidesu\Gosling\Repository\GuildRepository;

/**
 * Методы для работы тасков с ролью саппорта
 */
class SupportRoleTask {

	/**
	 * Установить роль саппорта, когда дали другую роль
	 *
	 * @param Member  $new
	 * @param Discord $discord
	 * @param Member  $old
	 *
	 * @return array
	 * @throws \Spidesu\Gosling\Exception\RowIsEmpty
	 */
	public static function setSupportRole(Member $new, Discord $discord, Member $old):array {

		$start_time = round(microtime(true) * 1000);

		$guild = GuildRepository::get($new->guild_id);

		if (!$guild->config["discord_support_role"] || !$guild->config["support_role"]) {
			return self::_postTask($new->id, $new->guild_id, $start_time);
		}

		$discord_support_role = $new->guild->roles->get('id', $guild->config["discord_support_role"]);

		// если саппорт роль дискорда куда то исчезла - обнуляем в конфиге у гильдии
		if (!$discord_support_role) {

			GuildRepository::setConfigValues($new->guild_id, ["discord_support_role" => null, "support_role" => null]);
			return self::_postTask($new->id, $new->guild_id, $start_time);
		}

		// узнаем, есть ли такая роль еще в гильдии
		$support_role = $new->guild->roles->get('id', $guild->config["support_role"]);

		// если саппорт роль куда то исчезла - обнуляем в конфиге у гильдии
		if (!$support_role) {

			GuildRepository::setConfigValues($new->guild_id, ["discord_support_role" => null, "support_role" => null]);
			return self::_postTask($new->id, $new->guild_id, $start_time);
		}

		$old_discord_support_role = $old->roles->get('id', $guild->config["discord_support_role"]);
		$new_discord_support_role = $new->roles->get('id', $guild->config["discord_support_role"]);
		$new_support_role         = $new->roles->get('id', $guild->config["support_role"]);

		// если был саппортом и остался саппортом - ниче не делаем
		if ($old_discord_support_role && $new_discord_support_role && $new_support_role) {
			return self::_postTask($new->id, $new->guild_id, $start_time);
		}

		// если роль бустера появилась - ставим гильдейскую
		if ($new_discord_support_role) {
			$new->addRole($support_role);
		}

		// если роль бустера пропала - убираем гильдейскую
		if ($old_discord_support_role && !$new_discord_support_role) {
			$new->removeRole($support_role);
		}


		return self::_postTask($new->id, $new->guild_id, $start_time);
	}

	/**
	 * Пост процессинг таска
	 *
	 * @param string $member_id
	 * @param string $guild_id
	 * @param int    $process_time
	 *
	 * @return array
	 */
	#[ArrayShape(["member_id" => "string", "guild_id" => "string", "process_time" => "int"])]
	private static function _postTask(string $member_id, string $guild_id, int $start_time):array {

		$process_time = round(microtime(true) * 1000) - $start_time;

		return [
			"member_id"    => $member_id,
			"guild_id"     => $guild_id,
			"process_time" => $process_time,
		];
	}
}