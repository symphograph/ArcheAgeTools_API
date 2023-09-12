<?php

namespace App\Packs;

use App\DTO\PackRouteDTO;
use PDO;
use Symphograph\Bicycle\Errors\AppErr;

class PackRoute extends PackRouteDTO
{

    public ?string $name;
    public Pack|string|null $Pack;
    public Zone|string|null $ZoneFrom;
    public Zone|string|null $ZoneTo;
    public Freshness|string|null $Freshness;
    public ?array $Mats;

    /**
     * @return self[]
     */
    public static function getList(int $side, bool $forProfit = false): array
    {
        $list = self::getFlatList($side);
        if($forProfit){
            $list = self::initProfitInList($list);
        }
        return $list;
    }

    /**
     * @return self[]
     */
    public static function getFlatList(int $side): array
    {
        $routes = self::getRoutes($side)
            or throw new AppErr('PackList is error', 'Паки не загрузились');

        return self::initDataInList($routes);
    }

    /**
     * @param self[] $List
     * @return self[]
     */
    private static function initDataInList(array $List): array
    {
        $arr = [];
        foreach ($List as $selfObject){
            $selfObject->initData();
            $arr[] = $selfObject;
        }
        return $arr;
    }

    /**
     * @param self[] $List
     * @return self[]
     */
    private static function initProfitInList(array $List): array
    {
        $arr = [];
        foreach ($List as $selfObject){
            $selfObject->initProfit();
            $arr[] = $selfObject;
        }
        return $arr;
    }

    private function initData(): void
    {
        self::initZones();
        self::initPack();
        self::initFreshness();
    }

    private function initProfit(): void
    {
        $this->Pack->initCraftData();
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
        $this->Pack = Pack::byIds($this->itemId, $this->zoneFromId)
        or throw new AppErr(
            "Pack $this->itemId from $this->zoneFromId does not exist",
            'Пак не найден'
        );
        $this->name = $this->Pack->name;
    }

    private function initFreshness(): void
    {
        $Freshness = Freshness::byId($this->Pack->freshId);
        $this->Freshness = $Freshness;
    }

}