<?php

namespace App\Craft;

use App\User\AccSettings;
use Symphograph\Bicycle\PDO\DB;

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
        $AccSets = AccSettings::byGlobal();
        $qwe = qwe("
            select pool 
            from uacc_CraftPool
            where accountId = :accountId 
              and serverGroupId = :serverGroupId
              and itemId = :itemId",
        ['accountId' =>$AccSets->accountId, 'serverGroupId' => $AccSets->serverGroupId, 'itemId' => $resultItemId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        $q = $qwe->fetchColumn();
        return json_decode($q,4);
    }

    private function initAllData(): void
    {
        self::initMatPrices();
        self::initMatPools();
    }

    private function putToDB(): void
    {
        $AccSets = AccSettings::byGlobal();
        $params = [
            'accountId' => $AccSets->accountId,
            'serverGroupId' => $AccSets->serverGroupId,
            'itemId' => $this->mainCraft->resultItemId,
            'pool' => json_encode($this, JSON_FORCE_OBJECT)
        ];
        DB::replace('uacc_CraftPool', $params);
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

    public function  initMatPrices(): void
    {
        $this->mainCraft->initMatPrice();
        $crafts = [];
        foreach ($this->otherCrafts as $craft){
            $craft->initMatPrice();
            $crafts[] = $craft;
        }
        $this->otherCrafts = $crafts;
    }

    public function initMatPools(): void
    {
        $this->mainCraft = self::initMatPool($this->mainCraft);
    }

    private static function initMatPool(Craft $craft): Craft
    {
        $pool = MatPool::getMatPool($craft->resultItemId);
        $craft->matPool = $pool->matPool;
        $craft->trashPool = $pool->trashPool;
        return $craft;
    }
}