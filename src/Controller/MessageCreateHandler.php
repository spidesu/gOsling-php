<?php

namespace Spidesu\Gosling\Controller;

use Discord\Discord;
use Discord\Parts\Channel\Message;

class MessageCreateHandler {

	/**
	 * @var array|Controller[]
	 */
	private static array $_command_list = [
		"birthday" => BirthdayController::class,
	];

	/**
	 * Точка входа для всех команд
	 *
	 * @param Message $message
	 * @param Discord $discord
	 *
	 * @return array
	 */
	public static function process(Message $message, Discord $discord):array {

		$start_time = round(microtime(true) * 1000);
		if (mb_substr($message->content, 0, 2) !== "g!") {
			return [];
		}

		$exploded = explode(" ", $message->content);
		$command  = explode("g!", array_shift($exploded))[1];

		if (!in_array($command, array_keys(self::$_command_list))) {
			return self::_endProcess($start_time);
		}

		if (count($exploded) > 0) {

			$method = array_shift($exploded);

			self::$_command_list[$command]::instance()->work($message, $discord, $method, $exploded);
			return self::_endProcess($start_time, $command);
		}

		self::$_command_list[$command]::instance()->work($message, $discord);
		return self::_endProcess($start_time, $command);
	}

	/**
	 * Закончить процесс обработки
	 * @param int    $start_time
	 * @param string $command
	 *
	 * @return array
	 */
	private static function _endProcess(int $start_time, string $command = ""):array {

		$end_time     = round(microtime(true) * 1000);
		$process_time = $end_time- $start_time;
		return ["command" => $command, "process_time" => $process_time];
	}
}