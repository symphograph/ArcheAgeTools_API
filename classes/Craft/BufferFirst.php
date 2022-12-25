<?php

namespace Craft;

class BufferFirst
{
    public int $accountId;
    public int $craftId;
    public int $craftCost;
    public int $matSPM;

    public static function clearDB(): void
    {
        global $Account;
        qwe("
            delete from craftBuffer 
                   where accountId = :accountId",
            ['accountId' => $Account->id]
        );
    }

    public static function putToDB(int $craftId, int $craftCost, int $matSPM): bool
    {
        global $Account;
        $qwe = qwe("
            replace into craftBuffer 
                (accountId, craftId, craftCost, matSPM) 
            VALUES 
                (:accountId, :craftId, :craftCost, :matSPM)", [
                'accountId' => $Account->id,
                'craftId'   => $craftId,
                'craftCost' => $craftCost,
                'matSPM'    => $matSPM
            ]
        );
        return boolval($qwe);
    }
}