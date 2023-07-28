<?php

namespace Spidesu\Gosling\System\Cache;
use Spidesu\Gosling\Model\CacheItem;

interface CacheDataInterface {

	/**
	 * Получить значение по ключу
	 */
	public function get(string $key):CacheItem;

	/**
	 * Удалить значение по ключу
	 */
	public function delete(string $key):void;

	/**
	 * Изменить значение
	 */
	public function set(string $key, mixed $data):void;
}