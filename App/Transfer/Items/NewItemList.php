<?php

namespace App\Transfer\Items;

use Symphograph\Bicycle\DTO\AbstractList;
use Symphograph\Bicycle\Env\Server\ServerEnv;

class NewItemList extends AbstractList
{
    /**
     * @var NewItem[]
     */
    public array $list = [];

    public static function getItemClass(): string
    {
        return NewItem::class;
    }

    public static function byFile(string $date): self
    {
        $arr = require dirname(ServerEnv::DOCUMENT_ROOT()) . '/tmp/items_' . $date . '.php';
        $self = new self();
        foreach ($arr as $item){
            $newItem = NewItem::byImportedArr($item);
            $newItem->createdAt = $date;
            $self->list[] = $newItem;
        }
        return $self;
    }

    public static function byDate(string $date): self
    {
        $sql = "select * from NewItems where createdAt = :date";
        return self::bySql($sql, compact('date'));
    }
}