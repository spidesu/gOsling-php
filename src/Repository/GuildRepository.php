<?php

namespace Spidesu\Gosling\Repository;

use Discord\Parts\Channel\Channel;
use Spidesu\Gosling\Exception\RowIsEmpty;
use Spidesu\Gosling\Model\Birthday;
use Spidesu\Gosling\Model\Guild;
use Spidesu\Gosling\System\PDO\PDOConnector;

class GuildRepository {

	public static function get(string $guild_id):Guild {

		$result = PDOConnector::instance()->connect(DB_NAME)->getOne(
			"SELECT * FROM `guilds` WHERE `guild_id` = :guild_id",
			[":guild_id" => $guild_id]
		);

		if (!isset($result["guild_id"])) {

			throw new RowIsEmpty();
		}

		return new Guild(
			$result["guild_id"],
			$result["birthday_role"],
			$result["birthday_channel"],
		);
	}

	public static function getList(array $guild_id_list):array {

		$output = [];
		$clause = implode(',', array_fill(0, count($guild_id_list), '?'));

		$result = PDOConnector::instance()->connect(DB_NAME)->getAll(
			"SELECT * FROM `guilds` WHERE `guild_id` IN ({$clause})",
			array_values($guild_id_list)
		);

		foreach ($result as $row) {

			$output[] = new Guild(
				$row["guild_id"],
				$row["birthday_role"],
				$row["birthday_channel"],
			);
		}

		return $output;
	}

	/**
	 * Установить канал для поздравлений
	 *
	 * @param string $channel_id
	 * @param string $guild_id
	 *
	 * @return void
	 */
	public static function setBirthdayChannel(string $channel_id, string $guild_id) {

		PDOConnector::instance()->connect(DB_NAME)->update(
			"UPDATE `guilds` SET `birthday_channel` = :channel_id WHERE `guild_id` = :guild_id",
			[
				":channel_id" => $channel_id,
				":guild_id" => $guild_id
			]
		);
	}

	/**
	 * Установить роль для поздравлений
	 * @param string $role_id
	 * @param string $guild_id
	 *
	 * @return void
	 */
	public static function setBirthdayRole(string $role_id, string $guild_id) {

		PDOConnector::instance()->connect(DB_NAME)->update(
			"UPDATE `guilds` SET `birthday_role` = :role_id WHERE `guild_id` = :guild_id",
			[
				":role_id" => $role_id,
				":guild_id" => $guild_id
			]
		);
	}

	public static function create(string $guild_id) {

		PDOConnector::instance()->connect(DB_NAME)->insert(
			"INSERT INTO `guilds` (`guild_id`, `created_at`) VALUES(:guild_id, :created_at)",
			[
				":guild_id" => $guild_id,
				":created_at" => time(),
			]
		);
	}

}