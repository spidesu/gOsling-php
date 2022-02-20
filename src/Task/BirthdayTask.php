<?php

namespace Spidesu\Gosling\Task;

use Discord\Discord;
use Discord\Parts\Guild\Guild;
use Discord\Parts\User\Member;
use Spidesu\Gosling\Exception\RowIsEmpty;
use Spidesu\Gosling\Repository\BirthdayRepository;
use Spidesu\Gosling\Repository\BirthdayTaskRepository;
use Spidesu\Gosling\Repository\GuildRepository;

/**
 *
 */
class BirthdayTask {

	/**
	 * Отправить сообщение о дней рождении
	 *
	 * @param Discord $discord
	 *
	 * @return void
	 */
	public static function sendBirthdayMessage(Discord $discord):void {

		$guild_id_list = $discord->guilds->map(function (Guild $guild) {return $guild->id;});

		foreach($guild_id_list as $guild_id) {

			try {

				$task_list = BirthdayTaskRepository::getByNeedWork($guild_id, time());
				$guild_config = GuildRepository::get($guild_id);
			} catch (RowIsEmpty) {
				continue;
			}

			foreach ($task_list as $task) {

				if ($task->type == \Spidesu\Gosling\Model\BirthdayTask::CONGRATULATION_TYPE) {
					self::_congratulateMembers($task, $discord, $guild_config);
				}

				if ($task->type == \Spidesu\Gosling\Model\BirthdayTask::REMOVE_ROLE_TYPE) {
					self::_removeBirthdayRole($task, $discord, $guild_config);
				}
			}
		}
	}

	/**
	 * Удалить пользователя из списка поздравляемых
	 *
	 * @param Member $member
	 *
	 * @return void
	 * @throws RowIsEmpty
	 */
	public static function removeMemberFromBirthdays(Member $member):void {
		try {

			$birthday = BirthdayRepository::get($member->id, $member->guild_id);
			BirthdayRepository::delete($member->id, $member->guild_id);
		} catch (RowIsEmpty) {
			return;
		}

		$birthday_task = BirthdayTaskRepository::get($member->guild_id, $birthday->month, $birthday->day);

		if (($key = array_search($member->id, $birthday_task->user_list)) !== false) {
			unset($birthday_task->user_list[$key]);
		}

		$birthday_task->user_list = array_values($birthday_task->user_list);

		BirthdayTaskRepository::updateUserList($birthday_task->user_list, $member->guild_id, $birthday->month, $birthday->day);
	}

	/**
	 * Поздравляем участников
	 *
	 * @param \Spidesu\Gosling\Model\BirthdayTask $task
	 * @param Discord                             $discord
	 * @param \Spidesu\Gosling\Model\Guild        $guild_config
	 *
	 * @return void
	 */
	private static function _congratulateMembers(\Spidesu\Gosling\Model\BirthdayTask $task, Discord $discord, \Spidesu\Gosling\Model\Guild $guild_config):void {

		$message = "Поздравляем с днем рождения🎂". PHP_EOL;

		$user_list = $task->user_list;

		$guild = $discord->guilds->get('id', $task->guild_id);

		$member_list = $guild->members->filter(function (Member $member) use ($user_list) {
			return in_array($member->id, $user_list);
		});

		foreach ($member_list as $member) {

			$message.="{$member}". PHP_EOL;
			if (strlen($guild_config->config["birthday_role"]) > 0) {
				$member->addRole($guild_config->config["birthday_role"]);
			}
		}

		$birthday_channel = $guild->channels->get('id', $guild_config->config["birthday_channel"]);
		$birthday_channel?->sendMessage($message);;

		$new_need_work_at = (new \DateTime())->setDate((int) date('Y'), $task->month, $task->day + 1)->setTime(1,0)->getTimestamp();
		BirthdayTaskRepository::updateNeedWorkAt($task->guild_id, $task->month, $task->day, $new_need_work_at, \Spidesu\Gosling\Model\BirthdayTask::REMOVE_ROLE_TYPE);

	}

	/**
	 * На следующий день удаляем роль с поздравлением
	 *
	 * @param \Spidesu\Gosling\Model\BirthdayTask $task
	 * @param Discord                             $discord
	 * @param \Spidesu\Gosling\Model\Guild        $guild_config
	 *
	 * @return void
	 */
	private static function _removeBirthdayRole(\Spidesu\Gosling\Model\BirthdayTask $task, Discord $discord, \Spidesu\Gosling\Model\Guild $guild_config):void {

		$user_list = $task->user_list;

		$guild = $discord->guilds->get('id', $task->guild_id);

		$member_list = $guild->members->filter(function (Member $member) use ($user_list) {
			return in_array($member->id, $user_list);
		});

		foreach ($member_list as $member) {

			if (strlen($guild_config->config["birthday_role"]) > 0) {
				$member->removeRole($guild_config->config["birthday_role"]);
			}
		}

		$new_need_work_at = (new \DateTime())->setDate((int) date('Y') + 1, $task->month, $task->day)->setTime(8,0)->getTimestamp();
		BirthdayTaskRepository::updateNeedWorkAt($task->guild_id, $task->month, $task->day, $new_need_work_at, \Spidesu\Gosling\Model\BirthdayTask::CONGRATULATION_TYPE);
	}
}