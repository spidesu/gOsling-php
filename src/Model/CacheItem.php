<?php

namespace Spidesu\Gosling\Model;

class CacheItem {

	public function __construct(
		public int $created_at,
		public int $expires_at,
		public mixed $data,
	) {
	}

}