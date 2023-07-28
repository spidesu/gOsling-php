<?php

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Guild;
use Discord\Parts\User\Member;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use React\Promise\Promise;
use Spidesu\Gosling\Controller\MessageCreateHandler;
use Spidesu\Gosling\System\Cache\Observer;
use Spidesu\Gosling\System\Config;
use Spidesu\Gosling\Task\BirthdayTask;
use Spidesu\Gosling\Task\SupportRoleTask;

include __DIR__ . "/vendor/autoload.php";
const APP_DIR = __DIR__ ;
const DB_NAME = "main";

$discord = new Discord([
	'token' => Config::instance()->getDiscordBotToken(),
	'intents' => Intents::getDefaultIntents() | Intents::GUILD_MEMBERS,
	'loadAllMembers' => true,
	'pmChannels' => false,
]);

$discord->on('ready', function (Discord $discord) {

	$guild_id_list = $discord->guilds->map(function (Guild $guild) {return $guild->id;})->toArray();
	$guild_list = \Spidesu\Gosling\Repository\GuildRepository::getList((array) $guild_id_list);
	$not_added_guild_list = array_diff($guild_id_list, array_column($guild_list, "guild_id"));

	foreach($not_added_guild_list as $guild_id) {
		\Spidesu\Gosling\Repository\GuildRepository::create($guild_id);
	}

    $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {

        $resolver = function (callable $resolve) use ($message, $discord) {
            $resolve(MessageCreateHandler::process($message, $discord));
        };

        $promise = new Promise($resolver);
        $promise->done(function (array $data) {

            if (!isset($data["command"])) return;
            echo "{$data["command"]} processed successfully for {$data["process_time"]}ms". PHP_EOL;
        });

    });

    $discord->on(Event::GUILD_MEMBER_REMOVE, function (Member $member, Discord $discord) {

        BirthdayTask::removeMemberFromBirthdays($member);
    });

    // действия при обновлении члена гильдии
    $discord->on(Event::GUILD_MEMBER_UPDATE, function (Member $new, Discord $discord, Member $old) {

        $resolver = function (callable $resolve) use ($new, $discord, $old) {
            $resolve(SupportRoleTask::setSupportRole($new, $discord, $old));
        };
        $promise = new Promise($resolver);
        $promise->done(function (array $data) {
            if ($data["process_time"] === 0) return;
            echo "Support role add/remove for member {$data["member_id"]} in guild {$data["guild_id"]} processed successfully for {$data["process_time"]}ms". PHP_EOL;
        });
    });

    $discord->getLoop()->addPeriodicTimer(3600, function () use($discord) { BirthdayTask::sendBirthdayMessage($discord);});
    $discord->getLoop()->addPeriodicTimer(60, function () { Observer::instance()->work();});

});

$discord->run();
