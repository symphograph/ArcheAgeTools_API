<?php

namespace Craft;

use PDO;
use Symphograph\Bicycle\DB;

class AccountCraft
{
    public ?int           $accountId;
    public ?int           $serverGroup;
    public ?int           $craftId;
    public ?int           $itemId;
    public ?bool          $isBest;
    public ?bool $isUBest;
    public ?int           $craftCost;
    public ?string        $datetime;
    public float|int|null $laborTotal;
    public ?int           $spmu;
    public ?string        $allMats;
    public ?LaborData $LaborData;

    public function __set(string $name, $value): void
    {
    }

    public static function byID(int $craftId): self|false
    {
        global $Account;
        $qwe = qwe("
            select uc.*,
                   if(ubC.craftId, 1, 0) as isUBest
            from uacc_crafts uc
            left join uacc_bestCrafts ubC 
                on uc.accountId = ubC.accountId
                and ubC.craftId = uc.craftId
            where uc.accountId = :accountId
                and serverGroup = :serverGroup
                and uc.craftId = :craftId",
            [
                'accountId'   => $Account->id,
                'serverGroup' => $Account->AccSets->serverGroup,
                'craftId'     => $craftId
            ]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    public static function byParams(
        int            $accountId,
        int            $serverGroup,
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
        $Craft->serverGroup = $serverGroup;
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

    public function putToDB(): bool
    {
        $params = [
            'accountId'   => $this->accountId,
            'serverGroup' => $this->serverGroup,
            'craftId'     => $this->craftId,
            'itemId'      => $this->itemId,
            'isBest'      => intval($this->isBest),
            'craftCost'   => $this->craftCost,
            'datetime'    => date('Y-m-d H:i:s'),
            'laborTotal'  => $this->laborTotal ?? 0,
            'spmu'        => $this->spmu,
            'allMats'     => $this->allMats
        ];
        return DB::replace('uacc_crafts', $params);
    }

    public static function getCompletedArr(): array
    {
        global $Account;

        $qwe = qwe("
            select itemId
            from uacc_crafts 
            where accountId = :accountId
            and serverGroup = :serverGroup",
        ['accountId'=>$Account->id, 'serverGroup'=>$Account->AccSets->serverGroup]
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function clearAllCrafts(): void
    {
        global $Account;
        qwe("
            delete from uacc_crafts 
            where accountId = :accountId 
            and serverGroup = :serverGroup",
        ['accountId'=>$Account->id, 'serverGroup'=>$Account->AccSets->serverGroup]
        );
    }


}