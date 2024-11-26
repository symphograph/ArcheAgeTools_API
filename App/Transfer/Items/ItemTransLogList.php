<?php

namespace App\Transfer\Items;

use Symphograph\Bicycle\DTO\AbstractList;

class ItemTransLogList extends AbstractList
{
    const string defaultOrder = 'createdAt desc';

    /**
     * @var ItemTransLog[]
     */
    protected array $list = [];

    public static function getItemClass(): string
    {
        return ItemTransLog::class;
    }

    public static function byErrors(array $errors = [], int $startId = 0, ?string $orderBy = 'createdAt', ?int $limit = null): static
    {
        if(empty($errors)) return static::allErrors($startId, $orderBy, $limit);

        $tableName = self::getTableName();

        $sql = "select * from $tableName
        where id >= :startId
        and error in (:errors)";
        $sql = self::sql($sql, $orderBy, $limit);
        $params = compact('startId','errors');
        return static::bySql($sql, $params);
    }

    protected static function allErrors(int $startId = 0, ?string $orderBy = 'createdAt', ?int $limit = null): static
    {
        $tableName = self::getTableName();
        $sql = "select * from $tableName where id >= :startId and error > ''";
        $sql = self::sql($sql, $orderBy, $limit);
        return static::bySql($sql, compact('startId'));
    }

    /**
     * @return ItemTransLog[]
     */
    public function getList(): array
    {
        return $this->list;
    }
}