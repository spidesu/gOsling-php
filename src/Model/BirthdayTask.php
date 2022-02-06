<?php

namespace Spidesu\Gosling\Model;

/**
 *
 */
class BirthdayTask {

	public function __construct(
		public int    $month,
		public int    $day,
		public string $guild_id,
		public int    $need_work_at,
		public array  $user_list,
	) {
	}

}