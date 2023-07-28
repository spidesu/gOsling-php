<?php

namespace Spidesu\Gosling\Model\CacheItemData\UserContext;

class ContextData {

    
	public function __construct(

        public string $type,
        public string $action,
        public array  $extra = [],
	) {
	}
}