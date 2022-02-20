<?php

namespace Spidesu\Gosling\Model;


/**
 *
 */
class BirthdayTask {

	public const CONGRATULATION_TYPE = 1;
	public const REMOVE_ROLE_TYPE = 2;

	public function __construct(
		public int    $month,
		public int    $day,
		public int    $type,
		public string $guild_id,
		public int    $need_work_at,
		public array  $user_list,
	) {
	}

}