<?php

namespace Spidesu\Gosling\Repository;

use Spidesu\Gosling\Exception\DuplicateEntry;
use Spidesu\Gosling\Exception\RowIsEmpty;
use Spidesu\Gosling\Model\Birthday;
use Spidesu\Gosling\System\PDO\PDOConnector;

class BirthdayRepository {

	/**
	 * Получить запись
	 *
	 * @param string $user_id
	 * @param string $guild_id
	 *
	 * @return Birthday
	 * @throws RowIsEmpty
	 */
	public static function get(string $user_id, string $guild_id):Birthday {

		$result = PDOConnector::instance()->connect(DB_NAME)->getOne(
			"SELECT * FROM `birthdays` WHERE `user_id` = :user_id AND `guild_id` = :guild_id",
			[
				":user_id"  => $user_id,
				":guild_id" => $guild_id,
			]
		);

		if (!isset($result["user_id"])) {

			throw new RowIsEmpty();
		}

		return new Birthday(
			$result["user_id"],
			$result["guild_id"],
			$result["month"],
			$result["day"]
		);
	}

	/**
	 * Добавить или обновить запись
	 *
	 * @param Birthday $birthday
	 *
	 * @return bool
	 */
	public static function insertOrUpdate(Birthday $birthday):bool {

		return PDOConnector::instance()->connect(DB_NAME)->insert(
			"INSERT INTO `birthdays` VALUES(:month, :day, :user_id, :guild_id, :created_at, :updated_at) 
				ON DUPLICATE KEY UPDATE `month` = :month, `day` = :day, `updated_at` = :updated_at",
			[
				":user_id"    => $birthday->user_id,
				":guild_id"   => $birthday->guild_id,
				":month"      => $birthday->month,
				":day"        => $birthday->day,
				":created_at" => time(),
				":updated_at" => time(),
			]
		);
	}

	/**
	 * Удалить запись
	 *
	 * @param string $user_id
	 * @param string $guild_id
	 *
	 * @return void
	 */
	public static function delete(string $user_id, string $guild_id):void {

		PDOConnector::instance()->connect(DB_NAME)->delete(
			"DELETE FROM `birthdays` WHERE `user_id` = :user_id AND `guild_id` = :guild_id",
			[
				":user_id"  => $user_id,
				":guild_id" => $guild_id,
			]
		);
	}
}
