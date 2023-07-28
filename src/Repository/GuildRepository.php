<?php

namespace Spidesu\Gosling\Repository;

use Discord\Parts\Channel\Channel;
use Spidesu\Gosling\Exception\RowIsEmpty;
use Spidesu\Gosling\Model\Birthday;
use Spidesu\Gosling\Model\Guild;
use Spidesu\Gosling\System\PDO\PDOConnector;

/**
 * Репозиторий по работе с таблицей guild
 */
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
			json_decode($result["config"], true, 512, JSON_THROW_ON_ERROR),
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
				json_decode($row["config"], true),
			);
		}

		return $output;
	}

	/**
	 * Создать запись гильдии
	 *
	 * @param string $guild_id
	 *
	 * @return void
	 */
	public static function create(string $guild_id):void {

		PDOConnector::instance()->connect(DB_NAME)->insert(
			"INSERT INTO `guilds` (`guild_id`, `created_at`, `config`) VALUES(:guild_id, :created_at, :config)",
			[
				":guild_id"   => $guild_id,
				":created_at" => time(),
				":config"     => json_encode([
					"birthday_channel"     => null,
					"birthday_role"        => null,
					"discord_support_role" => null,
					"support_role"         => null,
				], JSON_THROW_ON_ERROR),
			]
		);
	}

	/**
	 * Установить конфиг для гильдии
	 *
	 * @param string $guild_id
	 * @param array  $config
	 *
	 * @return void
	 */
	public static function setConfig(string $guild_id, array $config):void {

		PDOConnector::instance()->connect(DB_NAME)->update(
			"UPDATE `guilds` SET `config` = :config WHERE `guild_id` = :guild_id",
			[
				":config"   => json_encode($config),
				":guild_id" => $guild_id,
			]
		);
	}

	/**
	 * Установить значения конфига для гильдии
	 *
	 * @param string $guild_id
	 * @param array  $config
	 *
	 * @return void
	 */
	public static function setConfigValues(string $guild_id, array $config_values):void {

		$clause = self::_makeJsonSet($config_values);
		PDOConnector::instance()->connect(DB_NAME)->update(
			"UPDATE `guilds` SET `config` = JSON_SET(`config`, {$clause}) WHERE `guild_id` = :guild_id",
			[
				":guild_id" => $guild_id,
			]
		);
	}

	/**
	 * Костыльный JSON SET
	 *
	 * @param array $values
	 *
	 * @return string
	 */
	protected static function _makeJsonSet(array $values):string {

		$output = "";
		foreach ($values as $k => $v) {
			if (is_int($v)) {
				$output .= "'$.{$k}', $v, ";
				continue;
			}
			$output .= "'$.{$k}', '$v', ";
		}

		$output = substr_replace($output, "", -2);
		print_r($output);
		return $output;
	}
}