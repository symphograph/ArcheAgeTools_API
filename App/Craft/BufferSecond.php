<?php

namespace App\Craft;

use App\AppStorage;
use App\User\AccSettings;
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
        $bufferSecond = new self();
        $bufferSecond->bindSelf($bufferFirst);
        AppStorage::getSelf()->CraftsSecond[] = $bufferSecond;
    }

    public static function saveCrafts(): void
    {
        $AccSets = AccSettings::byGlobal();
        $firstBuffer = BufferFirst::getCounted();

        self::putToStorage($firstBuffer[0]);

        foreach ($firstBuffer as $k => $buffCraft){
            $AccCraft = AccountCraft::byParams(
                $AccSets->accountId,
                $AccSets->serverGroupId,
                $buffCraft->craftId,
                $buffCraft->resultItemId,
                intval($k === 0),
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