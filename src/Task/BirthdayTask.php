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

	public static function sendBirthdayMessage(Discord $discord):void {

		$guild_id_list = $discord->guilds->map(function (Guild $guild) {return $guild->id;});

		foreach($guild_id_list as $guild_id) {

			$message = "Поздравляем с днем рождения:\n";
			try {

				$task = BirthdayTaskRepository::get($guild_id, time());
				$guild_config = GuildRepository::get($guild_id);
			} catch (RowIsEmpty) {
				continue;
			}
			$user_list = $task->user_list;

			$guild = $discord->guilds->get('id', $guild_id);

			$member_list = $guild->members->filter(function (Member $member) use ($user_list) {
				return in_array($member->id, $user_list);
				});

			foreach ($member_list as $member) {

				$message.="{$member->username}\n";
				if (strlen($guild_config->birthday_role) > 0) {
					$member->addRole($guild_config->birthday_role);
				}
			}

			$birthday_channel = $guild->channels->get('id', $guild_config->birthday_channel);
			$birthday_channel?->sendMessage($message);;

			$new_need_work_at = (new \DateTime())->setDate((int) date('Y') + 1, $task->month, $task->day)->setTime(8,0)->getTimestamp();
			BirthdayTaskRepository::updateNeedWorkAt($guild_id, $task->month, $task->day, $new_need_work_at);
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

}