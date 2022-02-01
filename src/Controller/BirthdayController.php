<?php

namespace Spidesu\Gosling\Controller;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Guild;
use Discord\Parts\User\Member;
use Spidesu\Gosling\Exception\DuplicateEntry;
use Spidesu\Gosling\Exception\IncorrectBirthday;
use Spidesu\Gosling\Exception\RowIsEmpty;
use Spidesu\Gosling\Model\Birthday;
use Spidesu\Gosling\Repository\BirthdayRepository;
use Spidesu\Gosling\System\Config;

class BirthdayController extends Controller {

	protected array $_method_list = [
		"help",
	];

	protected array $_protected_method_list = [
		"show",
		"add",
	];

	public function default(Message $message, Discord $discord):void {

		$this->help($message, $discord);

	}

	public function show(Message $message, Discord $discord, array $args):void {

		if (count($args) < 1) {

			$message->channel->sendMessage("Не введен пользователь");
			return;
		}

		$username = $args[0];

		$guild = $discord->guilds->pull($message->guild_id);

		$member = $guild->members->find(function (Member $member) use ($username)  {
			return $member->username === $username;
			});

		if (is_null($member)) {

			$message->channel->sendMessage("Пользователь на сервере не найден");
			return;
		}

		try {
			$birthday = BirthdayRepository::get($member->id);
		} catch (RowIsEmpty) {
			$message->channel->sendMessage("Для пользователя не добавлен день рождения");
			return;
		}


		$message->channel->sendMessage("У {$username} день рождения {$birthday->day}.{$birthday->month}");
	}

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
		try {
			BirthdayRepository::insertOrUpdate(new Birthday($member->id, $message->guild_id, $month, $day));
		} catch (DuplicateEntry) {
			$message->channel->sendMessage("Дата для пользователя уже добавлена");
			return;
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
	 * Помощь по команде
	 *
	 * @param Message $message
	 *
	 * @return void
	 */
	public function help(Message $message, Discord $discord):void {

		$translation = Config::instance()->getTranslation()["birthday"][Config::instance()->getDefaultLanguage()];

		$message->channel->sendMessage($translation);
	}
}