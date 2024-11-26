<?php

namespace App\Craft;

use App\Craft\Craft\Craft;
use App\Craft\Craft\CraftList;
use App\Craft\Craft\Repo\CraftRepo;
use App\Mat\MatPool;
use App\User\AccSets;
use Symphograph\Bicycle\PDO\DB;

class CraftPool
{
    public Craft $mainCraft;
    /**
     * @var Craft[]
     */
    public array $otherCrafts;

    public static function getPoolWithAllData(int $resultItemId): self|false
    {
        if(!$Pool = CraftPool::getPool($resultItemId)){
            return false;
        }
        $Pool->initData();
        $Pool->putToDB();

        return $Pool;
    }

    public static function getByCache(int $resultItemId): ?array
    {
        $sql = "
            select pool 
            from uacc_CraftPool
            where accountId = :accountId 
              and serverGroupId = :serverGroupId
              and itemId = :itemId";

        $params = ['accountId' =>AccSets::curId(),
                   'serverGroupId' => AccSets::curServerGroupId(),
                   'itemId' => $resultItemId];

        $q = DB::qwe($sql, $params)->fetchColumn();
        if(empty($q)){
            return null;
        }
        return json_decode($q,4);
    }

    private function initData(): static
    {
        $this->initMatPrices();
        $this->initMatPools();
        return $this;
    }

    private function putToDB(): void
    {
        $params = [
            'accountId' => AccSets::curId(),
            'serverGroupId' => AccSets::curServerGroupId(),
            'itemId' => $this->mainCraft->resultItemId,
            'pool' => json_encode($this, JSON_FORCE_OBJECT)
        ];
        DB::replace('uacc_CraftPool', $params);
    }

    public static function getPool(int $resultItemId): self
    {
        $crafts = CraftRepo::getList($resultItemId);
        $crafts = new CraftList($crafts)
            ->initData()
            ->getList();

        $mainCraft = self::findMainCraft($crafts);
        $otherCrafts = [];
        foreach ($crafts as $craft){
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
     * @param Craft[] $Crafts
     * @return Craft|false
     */
    private static function findMainCraft(array $crafts): false|Craft
    {

        foreach ($crafts as $craft){
            if($craft->countData->isUBest){
                return $craft;
            }
        }

        foreach ($crafts as $craft){
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