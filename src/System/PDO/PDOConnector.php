<?php

namespace Spidesu\Gosling\System\PDO;

use PDOException;
use Spidesu\Gosling\System\Config;
use Spidesu\Gosling\System\Singleton;

/**
 *
 */
class PDOConnector extends Singleton {

	/**
	 * @var PDOMain[]
	 */
	private array $db_list;

	public function connect(string $db_name):PDOMain {

		$config = Config::instance();
		if (isset($this->db_list[$db_name])) {

			try {
				$this->db_list[$db_name]->query('SELECT 1');
			} catch (PDOException) {
				$this->db_list[$db_name] = new PDOMain("mysql:host={$config->getMysqlHost()}:{$config->getMysqlPort()};dbname=$db_name", $config->getMysqlUsername(), $config->getMysqlPassword());
			}
			return $this->db_list[$db_name];
		}

		return $this->db_list[$db_name] = new PDOMain("mysql:host={$config->getMysqlHost()}:{$config->getMysqlPort()};dbname=$db_name", $config->getMysqlUsername(), $config->getMysqlPassword());
	}
}