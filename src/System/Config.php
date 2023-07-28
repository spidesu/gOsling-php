<?php

namespace Spidesu\Gosling\System;

class Config extends Singleton {

	private string $db_host;
	private int $db_port;
	private string $db_username;
	private string $db_password;
	private string $default_language;
	private array $translation;
	private string $discord_bot_token;

	protected function __construct() {

		parent::__construct();

		$config_string = file_get_contents(APP_DIR . "/conf/config.json");
		$config = json_decode($config_string, true);

		$this->db_host = $config["db-host"];
		$this->db_port = $config["db-port"];
		$this->db_username = $config["db-username"];
		$this->db_password = $config["db-password"];
		$this->default_language = $config["default-language"];
		$this->discord_bot_token = $config["discord-bot-token"];

		$translation_string = file_get_contents(APP_DIR . "/conf/translation.json");
		$this->translation = json_decode($translation_string, true);
	}

	public function getDbHost():string {

		return $this->db_host;
	}

	public function getDbPort():string {

		return $this->db_port;
	}

	public function getDbUsername():string {

		return $this->db_username;
	}

	public function getDbPassword():string {

		return $this->db_password;
	}

	public function getDefaultLanguage():string {

		return $this->default_language;
	}

	public function getTranslation():array {

		return $this->translation;
	}

	public function getDiscordBotToken():string {

		return $this->discord_bot_token;
	}

}