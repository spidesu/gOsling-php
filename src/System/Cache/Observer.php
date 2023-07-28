<?php

namespace Spidesu\Gosling\System\Cache;

use Spidesu\Gosling\System\Singleton;
use Spidesu\Gosling\System\Cache\Observer\Storage;

/**
 * Обсервер кэша
 */
class Observer extends Singleton {

	/**
	 * Отработать по кэшу
	 */
	public function work():void {

		echo("clearing cache...");
		/**
		 * @var CacheData $class
		 * @var           $_
		 */
		foreach (Storage::instance()->getAll() as $class => $_) {
			$class::instance()->deleteExpired();
		}
	}
}