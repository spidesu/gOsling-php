<?php

namespace Spidesu\Gosling\Model;

/**
 *
 */
class Guild {

	public function __construct(
		public string $guild_id,
		public array $config,
	) {
	}
}