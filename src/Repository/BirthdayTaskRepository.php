<?php

namespace Spidesu\Gosling\Repository;

use Spidesu\Gosling\Exception\DuplicateEntry;
use Spidesu\Gosling\Exception\RowIsEmpty;
use Spidesu\Gosling\Model\BirthdayTask;
use Spidesu\Gosling\System\PDO\PDOConnector;

class BirthdayTaskRepository {

	public static function get(string $guild_id, int $month, int $day):BirthdayTask {

		$result = PDOConnector::instance()->connect(DB_NAME)->getOne(
			"SELECT * FROM `birthday_tasks` WHERE `guild_id` = :guild_id AND `month` = :month AND `day` = :day",
			[
				":guild_id" => $guild_id,
				":month"    => $month,
				":day"      => $day,
			],

		);

		if (!isset($result["guild_id"])) {
			throw new RowIsEmpty();
		}

		return new BirthdayTask(
			$result["month"],
			$result["day"],
			$result["type"],
			$result["guild_id"],
			$result["need_work_at"],
			json_decode($result["user_list"], true),
		);
	}

	public static function getByNeedWork(string $guild_id, int $need_work_at):array {

		$result = PDOConnector::instance()->connect(DB_NAME)->getAll(
			"SELECT * FROM `birthday_tasks` WHERE `guild_id` = :guild_id AND `need_work_at` < :need_work_at",
			[
				":guild_id"     => $guild_id,
				":need_work_at" => $need_work_at,
			],

		);

		$output = [];

		foreach ($result as $row) {
			$output[] = new BirthdayTask(
				$row["month"],
				$row["day"],
				$row["type"],
				$row["guild_id"],
				$row["need_work_at"],
				json_decode($row["user_list"], true),
			);
		}

		return $output;
	}

	public static function insert(BirthdayTask $task):bool {

		try {

			return PDOConnector::instance()->connect(DB_NAME)->insert(
				"INSERT INTO `birthday_tasks` VALUES(:month, :day, :type, :guild_id, :need_work_at, :created_at, :updated_at, :user_list) ",
				[
					":month"        => $task->month,
					":day"          => $task->day,
					":type"         => $task->type,
					":guild_id"     => $task->guild_id,
					":need_work_at" => $task->need_work_at,
					":created_at"   => time(),
					":updated_at"   => time(),
					":user_list"    => json_encode($task->user_list),
				]
			);
		} catch (\PDOException $e) {
			if ($e->errorInfo[1] == 1062) {
				throw new DuplicateEntry();
			} else {
				throw $e;
			}
		}
	}

	/**
	 * Добавить пользователя в список на поздравление
	 *
	 * @param string $user_id
	 * @param string $guild_id
	 * @param int    $month
	 * @param int    $day
	 *
	 * @return bool
	 */
	public static function addToUserList(string $user_id, string $guild_id, int $month, int $day):bool {

		return PDOConnector::instance()->connect(DB_NAME)->update(
			"UPDATE `birthday_tasks` SET `user_list` = JSON_ARRAY_APPEND(user_list, '$', :user_id), `updated_at` = :updated_at WHERE `month` = :month AND `day` = :day AND `guild_id` = :guild_id",
			[
				":month"      => $month,
				":day"        => $day,
				":guild_id"   => $guild_id,
				":updated_at" => time(),
				":user_id"    => $user_id,
			]
		);
	}

	/**
	 * Обновить список поздравляемых
	 *
	 * @param array  $user_list
	 * @param string $guild_id
	 * @param int    $month
	 * @param int    $day
	 *
	 * @return bool
	 */
	public static function updateUserList(array $user_list, string $guild_id, int $month, int $day):bool {

		return PDOConnector::instance()->connect(DB_NAME)->update(
			"UPDATE `birthday_tasks` SET `user_list` = :user_list, `updated_at` = :updated_at WHERE `month` = :month AND `day` = :day AND `guild_id` = :guild_id",
			[
				":month"      => $month,
				":day"        => $day,
				":guild_id"   => $guild_id,
				":updated_at" => time(),
				":user_list"  => json_encode($user_list),
			]
		);
	}

	/**
	 * Обновить время работы
	 *
	 * @param string $guild_id
	 * @param int    $month
	 * @param int    $day
	 * @param int    $need_work_at
	 * @param int    $type
	 *
	 * @return bool
	 */
	public static function updateNeedWorkAt(string $guild_id, int $month, int $day, int $need_work_at, int $type):bool {

		return PDOConnector::instance()->connect(DB_NAME)->update(
			"UPDATE `birthday_tasks` SET `need_work_at` = :need_work_at, `type` = :type WHERE `month` = :month AND `day` = :day AND `guild_id` = :guild_id", [
				":month"        => $month,
				":day"          => $day,
				":guild_id"     => $guild_id,
				":need_work_at" => $need_work_at,
				":type"         => $type,
			]
		);
	}
}