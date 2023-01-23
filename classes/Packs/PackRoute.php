<?php

namespace Packs;

use PDO;

class PackRoute
{
    public int  $itemId;
    public int  $zoneFromId;
    public int  $zoneToId;
    public int  $dbPrice;
    public int  $currencyId;
    public int  $mul;
    public ?Pack $Pack;
    public ?Zone $ZoneFrom;
    public ?Zone $ZoneTo;
    public ?Freshness $Freshness;

    /**
     * @return array<self>
     */
    public static function getList(int $side, bool $initCraftPrice = false): array
    {
        $rotes = self::getRoutes($side);
        $arr = [];
        foreach ($rotes as $route)
        {
            $route->initZones();
            $route->initPack();
            if($initCraftPrice){
                $route->Pack->initCraftPrice();
            }
            $route->initFreshness();
            $arr[] = $route;
        }
        return $arr;
    }

    /**
     * @return array<self>|false
     */
    private static function getRoutes(int $side): array|false
    {
        $qwe = qwe("
            select pr.* from packRoutes pr
            inner join zones z 
                on pr.zoneFromId = z.id
                and z.side = :side
                inner join items
                on pr.itemId = items.id
                and items.onOff",
        ['side' => $side]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    private function initZones(): void
    {
        $this->ZoneFrom = Zone::byId($this->zoneFromId);
        $this->ZoneTo = Zone::byId($this->zoneToId);
    }

    private function initPack(): void
    {
        if($pack = Pack::byId($this->itemId, $this->zoneFromId)){
            $this->Pack = Pack::byId($this->itemId, $this->zoneFromId);
        }else{
            printr($this);
            die();
        }

    }

    private function initFreshness(): void
    {
        $Freshness = Freshness::byId($this->Pack->freshId);
        $Freshness->initFreshLvls();
        $this->Freshness = $Freshness;
    }

}