<?php

namespace Spidesu\Gosling\Model\CacheItemData;
use Spidesu\Gosling\Model\CacheItemData\UserContext\ContextData;

class UserContextItem {

	public function __construct(
		public string $member_id,
		public string $guild_id,
		public string $channel_id,
		public ContextData $context_data,
	) {
	}

}