<?php

namespace Spidesu\Gosling\Model;

/**
 *
 */
class Guild {

	public function __construct(
		public string $guild_id,
		public string $birthday_role,
		public string $birthday_channel,
	) {
	}
}