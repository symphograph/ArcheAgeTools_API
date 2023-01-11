<?php

namespace Craft;

use Symphograph\Bicycle\DB;
use Symphograph\Bicycle\JsonDecoder;

class CraftPool
{
    public Craft $mainCraft;
    /**
     * @var array<Craft>
     */
    public array $otherCrafts;

    public static function getPoolWithAllData(int $resultItemId): self|false
    {
        if(!$Pool = CraftPool::getPool($resultItemId)){
            return false;
        }
        $Pool->initAllData();
        $Pool->putToDB();

        return $Pool;
    }

    public static function getByCache(int $resultItemId)
    {
        global $Account;
        $qwe = qwe("
            select pool 
            from uacc_CraftPool
            where accountId = :accountId 
              and serverGroup = :serverGroup
              and itemId = :itemId",
        ['accountId'=>$Account->id, 'serverGroup' => $Account->AccSets->serverGroup, 'itemId' => $resultItemId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        $q = $qwe->fetchColumn(0);
        return json_decode($q,4);
    }

    private function initAllData(): void
    {
        self::initMatPrices();
        self::initMatPools();
    }

    private function putToDB(): bool
    {
        global $Account;
        $params = [
            'accountId' => $Account->id,
            'serverGroup' => $Account->AccSets->serverGroup,
            'itemId' => $this->mainCraft->resultItemId,
            'pool' => json_encode($this, JSON_FORCE_OBJECT)
        ];
        return DB::replace('uacc_CraftPool', $params);
    }

    public static function getPool(int $resultItemId): self
    {
        $list = Craft::getList($resultItemId);
        $mainCraft = self::findMainCraft($list);
        $otherCrafts = [];
        foreach ($list as $craft){
            if($craft->id === $mainCraft->id){
                continue;
            }
            $otherCrafts[] = $craft;
        }
        $Pool = new self();
        $Pool->mainCraft = $mainCraft;
        $Pool->otherCrafts = $otherCrafts;
        return $Pool;
    }

    /**
     * @param array<Craft> $Crafts
     * @return false|self
     */
    private static function findMainCraft(array $Crafts): Craft|false
    {
        foreach ($Crafts as $craft){
            if($craft->countData->isUBest){
                return $craft;
            }
            if($craft->countData->isBest){
                return $craft;
            }
        }
        return false;
    }

    private static function initMatPrice(Craft $craft): Craft
    {
        $mats = [];
        foreach ($craft->Mats as $mat){
            if(!$mat->initPrice()){
                printr($craft);
                die();
            }
            //$mat->initPrice();
            $mat->Price->initAuthor();
            $mats[] = $mat;
        }
        $craft->Mats = $mats;
        return $craft;
    }

    public function  initMatPrices(): void
    {
        $this->mainCraft = self::initMatPrice($this->mainCraft);
        $crafts = [];
        foreach ($this->otherCrafts as $craft){
            $crafts[] = self::initMatPrice($craft);
        }
        $this->otherCrafts = $crafts;
    }

    public function initMatPools(): void
    {
        $this->mainCraft = self::initMatPool($this->mainCraft);
    }

    private static function initMatPool(Craft $craft): Craft
    {
        $craft->matPool = MatPool::getMatPool($craft->resultItemId);
        return $craft;
    }
}