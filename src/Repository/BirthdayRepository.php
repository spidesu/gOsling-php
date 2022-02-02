<?php

namespace Spidesu\Gosling\Repository;

use Spidesu\Gosling\Exception\DuplicateEntry;
use Spidesu\Gosling\Exception\RowIsEmpty;
use Spidesu\Gosling\Model\Birthday;
use Spidesu\Gosling\System\PDO\PDOConnector;

const DB_NAME = "main";

class BirthdayRepository {

	public static function get(int $user_id):Birthday {

		$result = PDOConnector::instance()->connect(DB_NAME)->getOne(
			"SELECT * FROM `birthdays` WHERE `user_id` = :user_id",
			[":user_id" => $user_id]
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

	public static function insertOrUpdate(Birthday $birthday):bool {

		return PDOConnector::instance()->connect(DB_NAME)->insert(
			"INSERT INTO `birthdays` VALUES(:user_id, :guild_id, :month, :day, :created_at, :updated_at) 
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
}
