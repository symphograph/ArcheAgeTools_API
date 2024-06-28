<?php

namespace App;

use App\Craft\BufferFirst;
use App\Craft\BufferSecond;
use App\User\AccSets;
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
    /**
     * @var int[]
     */
    public array $buyOnlyItems = [];


    public static function getSelf(): self
    {
        if(isset(self::$self)){
            return self::$self;
        }
        self::$self = new self();
        self::$self->initUPrefCrafts();
        self::$self->initBuyOnlyItems();
        return self::$self;
    }

    private function initUPrefCrafts(): void
    {
        $qwe = qwe("
            select itemId, craftId 
            from uacc_bestCrafts 
            where accountId = :accountId",
            ['accountId' => AccSets::curId()]
        );
        foreach ($qwe as $q){
            $this->uBestCrafts[$q['itemId']] = $q['craftId'];
        }
    }

    private function initBuyOnlyItems(): void
    {
        $qwe = qwe("
            select itemId 
            from uacc_buyOnly 
            where accountId = :accountId",
            ['accountId' => AccSets::curId()]
        );
        $this->buyOnlyItems = $qwe->fetchAll(PDO::FETCH_COLUMN) ?? [];
    }
}