<?php

namespace App\Craft;

use App\DTO\CraftDTO;
use App\User\AccSets;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\PDO\DB;

class AccountCraft
{
    public ?int           $accountId;
    public ?int           $serverGroupId;
    public ?int           $craftId;
    public ?int           $itemId;
    public ?bool          $isBest;
    public ?bool          $isUBest;
    public ?int           $craftCost;
    public ?string        $datetime;
    public ?int           $spmu;
    public ?string        $allMats;
    public ?LaborData     $LaborData;
    public float|int|null $laborTotal;

    public static function byParams(
        int            $accountId,
        int            $serverGroupId,
        int            $craftId,
        int            $itemId,
        int            $isBest,
        int            $craftCost,
        string         $datetime,
        int|float|null $laborTotal,
        int            $spmu,
        ?string        $allMats
    ): self
    {
        $Craft = new self();
        $Craft->accountId = $accountId;
        $Craft->serverGroupId = $serverGroupId;
        $Craft->craftId = $craftId;
        $Craft->itemId = $itemId;
        $Craft->isBest = $isBest;
        $Craft->craftCost = $craftCost;
        $Craft->datetime = date('Y-m-d H:i:s');
        $Craft->laborTotal = $laborTotal ?? 0;
        $Craft->spmu = $spmu;
        $Craft->allMats = $allMats;

        return $Craft;
    }

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

    public static function setUBest(int $accountId, int $craftId): void
    {
        $craft = CraftDTO::byId($craftId);
        qwe("
            replace into uacc_bestCrafts 
                (accountId, itemId, craftId) 
            VALUES 
                (:accountId, :itemId, :craftId)",
            ['accountId' => $accountId, 'itemId' => $craft->resultItemId, 'craftId' => $craftId]
        ) or throw new AppErr('error on Replace uBestCraft', 'Не сохранилось');
    }

    public static function byId(int $craftId): self|false
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
            'craftId'       => $craftId
        ];
        return DB::qwe($sql, $params)
            ->fetchObject(self::class);
    }

    public static function delUBest(int $accountId, int $craftId): bool
    {
        $craft = CraftDTO::byId($craftId);
        $sql = "
            delete from uacc_bestCrafts
            where accountId = :accountId
                and itemId = :itemId";
        $params = ['accountId' => $accountId, 'itemId' => $craft->resultItemId];
        return DB::qwe($sql, $params)
            or throw new AppErr(
                "delUBest err - accountId: $accountId, craftId: $craftId",
                'Не удалилось'
            );

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
            'accountId'     => AccSets::curId(),
            'serverGroupId' => AccSets::curServerGroupId()
        ];

        return DB::qwe($sql, $params)
            ->fetchObject(self::class);
    }

    public function __set(string $name, $value): void
    {
    }

    public function putToDB(): void
    {
        $params = [
            'accountId'     => $this->accountId,
            'serverGroupId' => $this->serverGroupId,
            'craftId'       => $this->craftId,
            'itemId'        => $this->itemId,
            'isBest'        => intval($this->isBest),
            'craftCost'     => $this->craftCost,
            'datetime'      => date('Y-m-d H:i:s'),
            'laborTotal'    => $this->laborTotal ?? 0,
            'spmu'          => $this->spmu,
            'allMats'       => $this->allMats
        ];
        DB::replace('uacc_crafts', $params);
    }

}