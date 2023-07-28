<?php

namespace Spidesu\Gosling\System\Cache\Data;

use Spidesu\Gosling\Model\CacheItemData\UserContextItem;
use Spidesu\Gosling\System\Cache\CacheData;
use Spidesu\Gosling\System\Cache\CacheDataInterface;

/**
 * Класс для хранения контекса общения с пользователем
 */
class UserContext extends CacheData implements CacheDataInterface {

     // время протухания элемента кэша
     protected const EXPIRE_TIME = 600;

     // какие объекты храним в data
     protected const CACHE_ITEM_DATA_CLASS = UserContextItem::class;

    public function makeKey(string $member_id, string $guild_id = "", string $channel_id = "") {

        return "$member_id-$guild_id-$channel_id";
    }
}
