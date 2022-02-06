<?php

namespace Spidesu\Gosling\Controller;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Role;
use Discord\Parts\User\Member;
use Spidesu\Gosling\Exception\DuplicateEntry;
use Spidesu\Gosling\Exception\IncorrectBirthday;
use Spidesu\Gosling\Exception\RowIsEmpty;
use Spidesu\Gosling\Model\Birthday;
use Spidesu\Gosling\Model\BirthdayTask;
use Spidesu\Gosling\Repository\BirthdayRepository;
use Spidesu\Gosling\Repository\BirthdayTaskRepository;
use Spidesu\Gosling\Repository\GuildRepository;
use Spidesu\Gosling\System\Config;

class BirthdayController extends Controller {

	protected array $_method_list = [
		"help",
	];

	protected array $_protected_method_list = [
		"show",
		"add",
		"channel",
		"role",
	];

	/**
	 * Дефолтное действие
	 *
	 * @param Message $message
	 * @param Discord $discord
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function default(Message $message, Discord $discord):void {

		$this->help($message, $discord);

	}

	/**
	 * Показать день рождения для пользователя
	 * @param Message $message
	 * @param Discord $discord
	 * @param array   $args
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function show(Message $message, Discord $discord, array $args):void {

		if (count($args) < 1) {

			$message->channel->sendMessage("Не введен пользователь");
			return;
		}

		$username = $args[0];

		$guild = $message->channel->guild;

		$member = $guild->members->find(function (Member $member) use ($username)  {
			return $member->username === $username;
			});

		if (is_null($member)) {

			$message->channel->sendMessage("Пользователь на сервере не найден");
			return;
		}

		try {
			$birthday = BirthdayRepository::get($member->id, $guild->id);
		} catch (RowIsEmpty) {
			$message->channel->sendMessage("Для пользователя не добавлен день рождения");
			return;
		}

		$day = sprintf("%02d", $birthday->day);
		$month = sprintf("%02d", $birthday->month);

		$message->channel->sendMessage("У {$username} день рождения {$day}.{$month}");
	}

	/**
	 * Добавить день рождения
	 * @param Message $message
	 * @param Discord $discord
	 * @param array   $args
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function add(Message $message, Discord $discord, array $args):void {

		if (count($args) < 1) {

			$message->channel->sendMessage("Не введены данные для добавления дня рождения");
			return;
		}
		$username = $args[0];

		$member = $message->channel->guild->members->find(function (Member $member) use ($username)  {
			return $member->username === $username;
		});

		if (is_null($member)) {

			$message->channel->sendMessage("Пользователь на сервере не найден");
			return;
		}

		$day_month = $args[1];

		try {
			[$day, $month] = self::_parseDate($day_month);
		} catch (IncorrectBirthday) {
			$message->channel->sendMessage("Передан неверный формат даты");
			return;
		}

		BirthdayRepository::insertOrUpdate(new Birthday($member->id, $message->guild_id, $month, $day));

		$need_work_date = new \DateTime();
		$need_work_date->setDate((int)date("Y"), (int) $month, (int) $day);

		$current_date = new \DateTime();
		if (((int) $current_date->format('n') > $month) || ((int) $current_date->format('n') == $month && (int) $current_date->format('j') >= $day)) {
			$need_work_at = $need_work_date->setTime(8,0)->getTimestamp();
		} else {
			$need_work_at = $current_date->setDate((int)date("Y") + 1, (int) $month, (int) $day)->setTime(8,0)->getTimestamp();
		}

		try {
			BirthdayTaskRepository::insert(
				new BirthdayTask(
					$month,
					$day,
					$message->guild_id,
					$need_work_at,
					[$member->id]
				)
			);
		} catch (DuplicateEntry) {
			BirthdayTaskRepository::addToUserList($member->id, $message->guild_id, $month, $day);
		}




		$message->channel->sendMessage("Дата рождения добавлена/изменена");
	}

	protected static function _parseDate(string $day_month):array {

		$day_month = explode(".", $day_month);

		if (count($day_month) != 2) {
			throw new IncorrectBirthday();
		}

		$month = filter_var(
			(int) $day_month[1],
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'min_range' => 1,
					'max_range' => 12,
				)
			)
		);

		$day = filter_var(
			(int) $day_month[0],
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'min_range' => 1,
					'max_range' => 31,
				)
			)
		);

		if (!$month || !$day) {
			throw new IncorrectBirthday();
		}

		return [$day, $month];

	}

	/**
	 * Установить канал для поздравлений
	 *
	 * @param Message $message
	 * @param Discord $discord
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function channel(Message $message, Discord $discord):void {

		GuildRepository::setBirthdayChannel($message->channel_id, $message->guild_id);
		$message->channel->sendMessage("Текущий канал добавлен для поздравлений");
	}

	public function role(Message $message, Discord $discord, array $args):void {

		if (count($args) < 1) {

			$message->channel->sendMessage("Не введены данные для добавления дня рождения");
			return;
		}

		$role_name = $args[0];

		$role = $message->channel->guild->roles->find(function (Role $role) use ($role_name)  {
			return $role->name === $role_name;
		});

		if (!$role) {
			$message->channel->sendMessage("Такой роли не существует");
			return;
		}

		GuildRepository::setBirthdayRole($role->id, $message->guild_id);
		$message->channel->sendMessage("Роль для поздравлений установлена");

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

		$translation = Config::instance()->getTranslation()["birthday"][Config::instance()->getDefaultLanguage()];

		$message->channel->sendMessage($translation);
	}
}