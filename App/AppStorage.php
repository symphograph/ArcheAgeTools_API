<?php

namespace App;

use App\Craft\BufferFirst;
use App\Craft\BufferSecond;
use App\User\AccSettings;
use PDO;


class AppStorage extends \Symphograph\Bicycle\AppStorage
{
    /**
     * @var BufferFirst[]
     */
    public array $CraftsFirst = [];
    /**
     * @var BufferSecond[]
     */
    public array $CraftsSecond = [];
    public array $uBestCrafts  = [];
    public array $buyOnlyItems = [];


    public static function getSelf(): self
    {
        global $AppStorage;
        if(isset($AppStorage)){
            return $AppStorage;
        }
        $AppStorage = new self();
        $AppStorage->initUPrefCrafts();
        $AppStorage->initBuyOnlyItems();
        return $AppStorage;
    }

    private function initUPrefCrafts(): void
    {
        $AccSets = AccSettings::byGlobal();
        $qwe = qwe("
            select itemId, craftId 
            from uacc_bestCrafts 
            where accountId = :accountId",
            ['accountId' => $AccSets->accountId]
        );
        foreach ($qwe as $q){
            $this->uBestCrafts[$q['itemId']] = $q['craftId'];
        }
    }

    private function initBuyOnlyItems(): void
    {
        $AccSets = AccSettings::byGlobal();
        $qwe = qwe("
            select itemId 
            from uacc_buyOnly 
            where accountId = :accountId",
            ['accountId' => $AccSets->accountId]
        );
        $this->buyOnlyItems = $qwe->fetchAll(PDO::FETCH_COLUMN) ?? [];
    }
}