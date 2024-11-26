<?php

namespace App\Craft\UCraft;

use App\Craft\Craft\CraftDTO;
use App\Craft\LaborData;
use App\User\AccSets;
use Symphograph\Bicycle\DTO\ModelTrait;
use Symphograph\Bicycle\PDO\DB;

class UCraft extends UCraftDTO
{
    use ModelTrait;

    public ?bool          $isUBest;
    public ?LaborData     $LaborData;

    public static function clearAllCrafts(): void
    {
        $params = [
            'accountId'     => AccSets::curId(),
            'serverGroupId' => AccSets::curServerGroupId()
        ];
        $sql = "
            delete from uacc_crafts 
            where accountId = :accountId 
            and serverGroupId = :serverGroupId";
        DB::qwe($sql, $params);

        $sql = "
            delete from uacc_CraftPool
            where accountId = :accountId
                and serverGroupId = :serverGroupId";
        DB::qwe($sql, $params);
    }

    public static function byId(int $id): static|false
    {
        $sql = "
            select uc.*,
                   if(ubC.craftId, 1, 0) as isUBest
            from uacc_crafts uc
            left join uacc_bestCrafts ubC 
                on uc.accountId = ubC.accountId
                and ubC.craftId = uc.craftId
            where uc.accountId = :accountId
                and serverGroupId = :serverGroupId
                and uc.craftId = :craftId";

        $params = [
            'accountId'     => AccSets::curId(),
            'serverGroupId' => AccSets::curServerGroupId(),
            'craftId'       => $id
        ];
        return DB::qwe($sql, $params)->fetchObject(self::class);
    }

    public static function delUBest(int $accountId, int $craftId): void
    {
        $craft = CraftDTO::byId($craftId);
        $sql = "
            delete from uacc_bestCrafts
            where accountId = :accountId
                and itemId = :itemId";
        $params = ['accountId' => $accountId, 'itemId' => $craft->resultItemId];
        DB::qwe($sql, $params);
    }

    public static function byResultItemId(int $resultItemId): self|false
    {
        $sql = "
            select 
                uc.*,
                if(ubC.craftId, 1, 0) as isUBest
            from uacc_crafts uc
            left join uacc_bestCrafts ubC 
                on uc.craftId = ubC.craftId
                and uc.accountId = ubC.accountId
            where uc.itemId = :itemId 
                and uc.accountId = :accountId
                and serverGroupId = :serverGroupId
            order by isUBest desc, isBest desc, spmu, craftCost
            limit 1";

        $params = [
            'itemId'        => $resultItemId,
            'accountId'     => AccSets::$current->accountId,
            'serverGroupId' => AccSets::$current->serverGroupId,
        ];

        return DB::qwe($sql, $params)->fetchObject(self::class);
    }

}