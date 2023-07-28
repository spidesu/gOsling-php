<?php

namespace Spidesu\Gosling\Controller\Context;

use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\Member;
use Spidesu\Gosling\Controller\Context;
use Spidesu\Gosling\Model\CacheItemData\UserContextItem;
class YMContextController extends Context {

	protected array $_action_list = [
		"play",
	];

	public function play(Message $message, Discord $discord, UserContextItem $context):void {
		
		$member_id = $message->member->id;

		$channel = $message->member->guild->channels->find(function (Channel $channel) use ($member_id) {
			
			$found_member_id = $channel->members->find(function (Member $member) use ($member_id) {

				return $member_id === $member->id;
			});

			return $member_id === $found_member_id;
			});

		$discord->joinVoiceChannel($channel, true);
	}
}