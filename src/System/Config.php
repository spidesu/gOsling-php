<?php

namespace Spidesu\Gosling\System;

class Config extends Singleton {

	private string $mysql_host;
	private int $mysql_port;
	private string $mysql_username;
	private string $mysql_password;
	private string $default_language;
	private array $translation;

	public function __construct() {

		parent::__construct();

		$config_string = file_get_contents(APP_DIR . "/conf/config.json");
		$config = json_decode($config_string, true);

		$this->mysql_host = $config["mysql-host"];
		$this->mysql_port = $config["mysql-port"];
		$this->mysql_username = $config["mysql-username"];
		$this->mysql_password = $config["mysql-password"];
		$this->default_language = $config["default-language"];

		$translation_string = file_get_contents(APP_DIR . "/conf/translation.json");
		$this->translation = json_decode($translation_string, true);
	}

	public function getMysqlHost():string {

		return $this->mysql_host;
	}

	public function getMysqlPort():string {

		return $this->mysql_port;
	}

	public function getMysqlUsername():string {

		return $this->mysql_username;
	}

	public function getMysqlPassword():string {

		return $this->mysql_password;
	}

	public function getDefaultLanguage():string {

		return $this->default_language;
	}

	public function getTranslation():array {

		return $this->translation;
	}

}