<?php

namespace App\Packs;

use PDO;
use Symphograph\Bicycle\{DB, JsonDecoder};

class PackRoute
{
    public int $id;
    public int  $itemId;
    public int  $zoneFromId;
    public int  $zoneToId;
    public int  $dbPrice;
    public int  $currencyId;
    public ?int  $mul;
    public ?int $side;
    public ?Pack $Pack;
    public ?Zone $ZoneFrom;
    public ?Zone $ZoneTo;
    public ?Freshness $Freshness;

    /**
     * @return array<self>|false
     */
    public static function getList(int $side, bool $forProfit = false): array|false
    {
        if(!$forProfit){
            return self::getFlatList($side);
        }
        return self::getListForProfit($side);
    }

    /**
     * @return array<self>|false
     */
    public static function getListForProfit(int $side): array|false
    {
        if(!$List = self::getList($side)){
            return false;
        }

        $arr = [];
        foreach ($List as $route){
            $route->Pack->initCraftData();
            $arr[] = $route;
        }
        return $arr;
    }

    /**
     * @return array<self>
     */
    public static function getFlatList(int $side): array|false
    {

        if($rotes = self::getListByCache($side)){
            return $rotes;
        }

        if(!$rotes = self::getRoutes($side)){
            return false;
        }

        $arr = [];
        foreach ($rotes as $route)
        {
            $route->initZones();
            $route->initPack();
            $route->initFreshness();
            $route->putToDB($side);

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
        $this->Freshness = $Freshness;
    }

    private function putToDB(int $side): bool
    {
        $params = [
            'id'         => $this->id,
            'itemId'     => $this->itemId,
            'zoneFromId' => $this->zoneFromId,
            'zoneToId'   => $this->zoneToId,
            'side'       => $side,
            'dbPrice'    => $this->dbPrice,
            'currencyId' => $this->currencyId,
            'Pack'       => json_encode($this->Pack, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT),
            'ZoneFrom'   => json_encode($this->ZoneFrom, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT),
            'ZoneTo'     => json_encode($this->ZoneTo, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT),
            'Freshness'  => json_encode($this->Freshness, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT)
        ];
        return DB::replace('uacc_RoutePool', $params);
    }

    public static function getListByCache(int $side): array|false
    {
        $qwe = qwe("select * from uacc_RoutePool where side = :side", ['side'=>$side]);
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        $arr = $qwe->fetchAll(PDO::FETCH_CLASS);
        $list = [];
        foreach ($arr as $pRoute){
            //printr($pRoute);
            $pRoute->Pack = json_decode($pRoute->Pack,4);
            $pRoute->ZoneFrom = json_decode($pRoute->ZoneFrom,4);
            $pRoute->ZoneTo = json_decode($pRoute->ZoneTo,4);
            $pRoute->Freshness = json_decode($pRoute->Freshness, 4);
            $pRoute = JsonDecoder::cloneFromAny($pRoute, self::class);
            $list[] = $pRoute;
        }
        return $list;
    }

}