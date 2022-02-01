<?php

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Spidesu\Gosling\Controller\MessageCreateHandler;

include __DIR__ . "/vendor/autoload.php";
const APP_DIR = __DIR__ ;

$discord = new Discord([
	'token' => 'NjcwMzE0NTQ4ODU0Nzg0MDAw.XislEg.yWvjHi5xjFXcurmLzHcwif6Vtyk',
	'intents' => Intents::getDefaultIntents() | Intents::GUILD_MEMBERS,
	'loadAllMembers' => true,
	'pmChannels' => false,
]);
$config_string = file_get_contents(APP_DIR . "/conf/config.json");
$config = json_decode($config_string, true);

$discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {

	MessageCreateHandler::instance()->process($message, $discord);

});
$discord->run();
