<?php

namespace Spidesu\Gosling\Model;

class Birthday {

	public function __construct(
		public int $user_id,
		public int $guild_id,
		public int $month,
		public int $day,
	) {
	}

}