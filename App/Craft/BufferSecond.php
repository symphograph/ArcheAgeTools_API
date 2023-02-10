<?php

namespace App\Craft;

use App\Item\Item;
use App\User\Account;

class BufferSecond
{
    public int $accountId;
    public int $craftId;
    public int $craftCost;
    public int $spm;
    public int $resultItemId;

    public static function clearDB(): void
    {
        $Account = Account::getSelf();;
        qwe("
            delete from craftBuffer2 
                   where accountId = :accountId",
            ['accountId' => $Account->id]
        );
    }

    public static function byCraftId(int $craftId): self|false
    {
        $Account = Account::getSelf();;
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
        $Account = Account::getSelf();;
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

    public static function putToDB(int $craftId, int $resultItemId, int $craftCost, int $spm): bool
    {
        $Account = Account::getSelf();;
        $qwe = qwe("
            replace into craftBuffer2 
                (accountId, craftId, resultItemId, craftCost, spm) 
            VALUES 
                (:accountId, :craftId, :resultItemId, :craftCost, :spm)", [
                'accountId'    => $Account->id,
                'craftId'      => $craftId,
                'resultItemId' => $resultItemId,
                'craftCost'    => $craftCost,
                'spm'          => $spm
            ]
        );
        return boolval($qwe);
    }

    public static function saveCrafts(int $resultItemId): void
    {
        $Account = Account::getSelf();;
        $firstBuffer = BufferFirst::getCounted($resultItemId);
        $i = 0;
        foreach ($firstBuffer as $buffCraft){
            $i++;
            $isBest = intval($buffCraft->isUBest);
            if($i === 1){
                self::putToDB($buffCraft->craftId, $buffCraft->resultItemId, $buffCraft->craftCost, $buffCraft->spm);
                if(!$isBest){
                    $isBest = 1;
                }
            }else{
                $isBest = 0;
            }
            $AccCraft = AccountCraft::byParams(
                $Account->id,$Account->AccSets->serverGroup,
                $buffCraft->craftId,
                $buffCraft->resultItemId,
                $isBest,
                $buffCraft->craftCost,
                date('Y-m-d H:i:s'),
                null,
                $buffCraft->spmu,
                null
            );
            $AccCraft->putToDB();

        }
    }
}