<?php

namespace Spidesu\Gosling\System\Cache;
use Exception;
use Spidesu\Gosling\Exception\CacheItemNotFound;
use Spidesu\Gosling\Model\CacheItem;
use Spidesu\Gosling\System\Cache\Observer\Storage;
use Spidesu\Gosling\System\Singleton;

class CacheData extends Singleton implements CacheDataInterface {

    /** @var CacheItem[] $cached_item_list */
    private array $cached_item_list = [];

    // время протухания элемента кэша
    protected const EXPIRE_TIME = 3600;

    // какие объекты храним в data
    protected const CACHE_ITEM_DATA_CLASS = "";

    /**
     * Конструктор
     * @throws Exception
     */
    protected function __construct() {
    
        parent::__construct();

        // не разрешаем создавать этот класс
        if (get_class($this) === CacheData::class) {
            throw new Exception();
        }

        // добавляем в обсерв
        Storage::instance()->add($this::class);
    }

    /**
     * @inheritDoc
     * @throws CacheItemNotFound
     */
    public function get(string $key):CacheItem {

        if (!isset($this->cached_item_list[$key])) {
            throw new CacheItemNotFound();
        }

        return $this->cached_item_list[$key];
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function set(string $key, mixed $data):void {

        if (get_class($data) !== static::CACHE_ITEM_DATA_CLASS) {
            throw new Exception("invalid object class for cache");
        }

        $current_time = time();

        // если такого элемента нет, то создаем новый
        if (isset($this->cached_item_list[$key])) {

            $this->cached_item_list[$key] = new CacheItem($current_time, $current_time + static::EXPIRE_TIME, $data);
            return;
        }

        $this->cached_item_list[$key]->expires_at = $current_time + static::EXPIRE_TIME;
        $this->cached_item_list[$key]->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key):void {

        unset($this->cached_item_list[$key]);
    }

    public function deleteExpired():void {

        foreach ($this->cached_item_list as $key => $cached_item) {

            if ($cached_item->expires_at < time()) {
                unset($this->cached_item_list[$key]);
            }

        }
    }
}
