<?php

namespace App\Craft;

use App\AppStorage;
use App\Craft\UCraft\UCraft;
use App\User\AccSets;
use Symphograph\Bicycle\DTO\BindTrait;

class BufferSecond
{
    use BindTrait;
    public int $accountId;
    public int $craftId;
    public int $craftCost;
    public int $spm;
    public int $resultItemId;


    public static function clearStorage(): void
    {
        AppStorage::getSelf()->CraftsSecond = [];
    }

    public static function byItemId(int $resultItemId): self|false
    {
        $CraftsSecond = AppStorage::getSelf()->CraftsSecond;
        foreach ($CraftsSecond as $BufferSecond){
            if($BufferSecond->resultItemId === $resultItemId)
                return $BufferSecond;
        }
        return false;
    }

    private static function putToStorage(BufferFirst $bufferFirst): void
    {
        $bufferSecond = self::byBind($bufferFirst);
        AppStorage::getSelf()->CraftsSecond[] = $bufferSecond;
    }

    public static function saveCrafts(): void
    {
        $firstBuffer = BufferFirst::getCounted();

        self::putToStorage($firstBuffer[0]);

        foreach ($firstBuffer as $k => $buffCraft){
            $AccCraft = UCraft::newInstance(
                AccSets::curId(),
                AccSets::curServerGroupId(),
                $buffCraft->craftId,
                $buffCraft->resultItemId,
                intval($k === 0),
                $buffCraft->craftCost,
                null,
                $buffCraft->spmu,
                null
            );
            $AccCraft->putToDB();
        }
    }
}