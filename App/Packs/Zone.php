<?php

namespace App\Packs;

use PDO;

class Zone
{
    public int    $id;
    public string $name;
    public ?int    $side;
    public ?int   $isGet;
    public ?int   $getWest;
    public ?int   $getEast;
    public ?int   $freshType;
    public ?array $ZonesTo;

    public static function byId(int $id): self|false
    {
        $qwe = qwe("select * from zones where id = :id", ['id' => $id]);
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    /**
     * @return array<self>|false
     */
    public static function bySide(int $side): array|false
    {
        $qwe = qwe("select * from zones where side = :side", ['side' => $side]);
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    /**
     * @return array<self>|false
     */
    public static function getAllFrom(): array|false
    {
        $qwe = qwe("
            select z.* from zones z
            inner join (select distinct zoneFromId
                            from packRoutes pr
                            inner join items i on pr.itemId = i.id
                            and i.onOff
                            ) as pzFI
            on z.id = pzFI.zoneFromId"
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function getFromsGroupBySide(): array
    {
        $zones = self::getAllFrom();
        return self::groupBySide($zones);
    }

    /**
     * @param array<self> $zones
     * @return array
     */
    private static function groupBySide(array $zones): array
    {
        $arr = [];
        $empty = new self();
        $empty->id = 0;
        $empty->name = 'Все';
        foreach ($zones as $zone){
            $zone->initZonesTo();
            $zone->ZonesTo[] = $empty;
            sort($zone->ZonesTo);
            $arr[$zone->side][] = $zone;
        }
        ksort($arr);
        return $arr;
    }

    private function initZonesTo(): void
    {
        $qwe = qwe("
            select z.* from zones z
            inner join (select distinct zoneToId 
                            from packRoutes pr
                            inner join items i on pr.itemId = i.id
                            and i.onOff
                            where zoneFromId = :zoneFromId
                        ) as pzTI
            on z.id = pzTI.zoneToId",
        ['zoneFromId' => $this->id]
        );
        if(!$qwe || !$qwe->rowCount()){
            return;
        }
        $this->ZonesTo = $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }
}