<?php

namespace Spidesu\Gosling\System\Cache\Observer;
use Exception;
use Spidesu\Gosling\System\Singleton;

/**
 * Обсервер кэша
 */
class Storage extends Singleton {

    /** @var array $observe_class_list */
    protected array $observe_class_list = [];

    /**
     * Добавить класс кэша в обсерв
     */
    public function add(string $cache_data_class):void {

        $this->observe_class_list[$cache_data_class] = true;
    }

    /**
     * Вернуть список всех классов
     */
    public function getAll():array {
        return $this->observe_class_list;
    }
}