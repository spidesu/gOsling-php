<?php

namespace Spidesu\Gosling\Model;

class Birthday {

	public function __construct(
		public string $user_id,
		public string $guild_id,
		public int $month,
		public int $day,
	) {
	}

}