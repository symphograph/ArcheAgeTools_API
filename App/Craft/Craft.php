<?php

namespace App\Craft;

use App\AppStorage;
use App\DTO\CraftDTO;
use App\Errors\CraftCountErr;
use App\User\AccSettings;
use Symphograph\Bicycle\DB;
use Symphograph\Bicycle\Errors\AppErr;
use PDO;
use App\User\Prof;

class Craft extends CraftDTO
{
    public ?string       $itemName;
    public ?string       $doodName;
    public ?Prof         $Prof;
    public string        $error = '';
    public ?AccountCraft $countData;

    /**
     * @var array<Mat>|null
     */
    public ?array $Mats;

    /**
     * @var array<Mat>|null
     */
    public ?array $matPool;

    /**
     * @var array<Mat>|null
     */
    public ?array $trashPool;

    public static function byId(int|string $id) : self|bool
    {
        $qwe = qwe("
            select crafts.*, 
                   doods.id as doodId,
                   doods.name as doodName
            from crafts 
            inner join items on items.id = crafts.resultItemId
                and items.onOff
                and crafts.onOff                     
                and crafts.id = :id
            left join doods on doods.id = crafts.doodId",
            ['id' => $id]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        $Craft = $qwe->fetchObject(self::class);
        $Craft->id = $id;
        $Craft->initAllData();
        return $Craft;
    }

    /**
     * @throws AppErr
     */
    private function initMats(): bool
    {
        $Mats = Mat::getCraftMats($this->id);
        if(empty($Mats)){
            throw new AppErr('Craft ' . $this->id . ' не нашел материалы');
        }
        $this->Mats = $Mats;
        return true;
    }

    private function initProf(): bool
    {
        if(!$Prof = Prof::byNeed($this->profId, $this->profNeed))
            return false;
        return !!$this->Prof = $Prof;
    }

    private function initCountData(): bool
    {
        if(!$countData = AccountCraft::byId($this->id)){
            return false;
        }
        $this->countData = $countData;
        $this->countData->LaborData = LaborData::byCraft($this);
        return true;
    }

    public function initAllData(): bool
    {
        $this->error = match (false){
            self::initMats() => throw new CraftCountErr('Mats is empty'),
            self::initProf() => throw new CraftCountErr('Prof data is empty'),
            self::initCountData() => '',
            default => ''
        };
        return empty($this->error);
    }

    /**
     * @param array<self> $Crafts
     * @return array<self>
     */
    private static function initDataInList(array $Crafts): array
    {
        $List = [];
        foreach ($Crafts as $craft){
            $craft->initAllData();
            $List[] = $craft;
        }
        return $List;
    }

    public static function getCraftIDs(int $resultItemId): array
    {
        $qwe = qwe("
            select id from crafts 
            where resultItemId = :resultItemId",
            ['resultItemId'=>$resultItemId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return array<self>|bool
     */
    public static function getList(int $itemId) : array|bool
    {
        $qwe = qwe("select crafts.*, 
                   doods.id as doodId,
                   doods.name as doodName
            from crafts 
            inner join items on items.id = crafts.resultItemId
                and items.onOff
                and crafts.onOff                   
                and crafts.resultItemId = :itemId
            left join doods 
                on doods.id = crafts.doodId",
        ['itemId'=>$itemId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        $Crafts = $qwe->fetchAll(PDO::FETCH_CLASS,self::class);

        return self::initDataInList($Crafts);
    }

    /**
     * @return self[][]
     */
    public static function allPotentialCrafts(int $itemId): array
    {
        $craftIDs = self::getCraftIDs($itemId);
        $matIds = Mat::allPotentialMats($itemId);
        if (empty($matIds)) $matIds[] = 0;

        $matIdsPH = DB::pHolders($matIds);
        $craftIdsPH = DB::pHolders($craftIDs,2);

        $qwe = qwe("
            select crafts.*,
                   items.name as itemName,
                   doods.name as  doodName 
            from crafts 
                 inner join items on crafts.resultItemId = items.id
                    and items.onOff
                    and crafts.onOff
                    and (
                            crafts.resultItemId in ($matIdsPH) 
                            or crafts.id in ($craftIdsPH)
                        )
                 left join doods on doods.id = crafts.doodId
            order by deep desc, resultItemId",
        [DB::pHoldsArr($matIds), DB::pHoldsArr($craftIDs,2)]
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        $Crafts = $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
        $Crafts = self::initDataInList($Crafts);
        $List = [];
        foreach ($Crafts as $craft){
            $List[$craft->resultItemId][] = $craft;
        }
        return $List;
    }

    public static function isCountedItem(int $resultItemId): bool
    {
        $AccSets = AccSettings::byGlobal();
        $qwe = qwe("
            select * from uacc_crafts 
            where accountId = :accountId
            and serverGroupId = :serverGroupId
            and itemId = :resultItemId",
            ['accountId'    => $AccSets->accountId,
             'serverGroupId'  => $AccSets->serverGroupId,
             'resultItemId' => $resultItemId]
        );
        return boolval($qwe);
    }

    public function initMatPrice(): bool
    {
        $mats = [];
        foreach ($this->Mats as $mat){
            if(!$mat->initPrice()){
                return false;
            }
            $mat->Price->initAuthor();
            $mats[] = $mat;
        }
        $this->Mats = $mats;
        return true;
    }

    public static function getAllResultItems(): false|array
    {
        $qwe = qwe("
            select distinct resultItemId 
            from crafts
            inner join items i 
                on crafts.resultItemId = i.id
                and i.onOff
            where crafts.onOff"
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function getUBest(int $resultItemId): int|false
    {
        $AppStorage = AppStorage::getSelf();
        return $AppStorage->uBestCrafts[$resultItemId] ?? false;
    }
}