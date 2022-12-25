<?php

namespace Craft;

class BufferSecond
{
    public int $accountId;
    public int $craftId;
    public int $craftCost;
    public int $spm;
    public int $resultItemId;

    public static function clearDB(): void
    {
        global $Account;
        qwe("
            delete from craftBuffer2 
                   where accountId = :accountId",
            ['accountId' => $Account->id]
        );
    }

    public static function byCraftId(int $craftId): self|false
    {
        global $Account;
        $qwe = qwe("
            select * from craftBuffer2 
            where accountId = :accountId 
            and craftId = :craftId",
            ['accountId' => $Account->id, 'craftId' => $craftId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    public static function byItemId(int $resultItemId): self|false
    {
        global $Account;
        $qwe = qwe("
            select * from craftBuffer2 
            where accountId = :accountId 
            and resultItemId = :resultItemId",
            ['accountId' => $Account->id, 'resultItemId' => $resultItemId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }
}