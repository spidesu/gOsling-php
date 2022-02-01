<?php

namespace Spidesu\Gosling\System;

/**
 *
 */
class Singleton {

	/**
	 * @var Singleton[]
	 */
	private static array $instances = [];

	public function __construct() {}

	public function __clone():void {}

	public static function instance():static {

		$cls = static::class;
		if (!isset(self::$instances[$cls])) {
			self::$instances[$cls] = new static();
		}

		return self::$instances[$cls];
	}

}