<?php

namespace Craft;

class CountData
{
    public ?int           $accountId;
    public ?int           $serverGroup;
    public ?int           $craftId;
    public ?int           $itemId;
    public ?bool          $isBest;
    public ?int           $craftCost;
    public ?string        $datetime;
    public float|int|null $laborTotal;
    public ?int           $spmu;
    public ?string        $allMats;

    public static function byID(int $craftId): self|false
    {
        global $Account;
        $qwe = qwe("
            select * from uacc_crafts 
            where accountId = :accountId
            and serverGroup = :serverGroup
            and craftId = :craftId",
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




}