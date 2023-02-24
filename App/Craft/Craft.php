<?php

namespace App\Craft;

use Symphograph\Bicycle\Errors\AppErr;
use App\User\Account;
use PDO;
use App\User\Prof;

class Craft
{
    public int $id;
    /**
     * @var array<Mat>|null
     */
    public ?array        $Mats;
    public ?int          $resultItemId;
    public ?string       $itemName;
    public ?string $craftName;
    public int|float     $resultAmount = 1;
    public ?int          $doodId;
    public ?string       $doodName;
    public int           $profId       = 25;
    public ?int          $profNeed;
    public ?int          $laborNeed;
    public ?Prof         $Prof;
    public ?AccountCraft $countData;
    /**
     * @var array<Mat>|null
     */
    public ?array $matPool;
    /**
     * @var array<Mat>|null
     */
    public ?array $trashPool;
    public string $error = '';

    public function __set(string $name, $value): void{}

    public static function byId(int $id) : self|bool
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
        if(!$countData = AccountCraft::byID($this->id)){
            return false;
        }
        $this->countData = $countData;
        $this->countData->LaborData = LaborData::byCraft($this);
        return true;
    }

    public function initAllData(): bool
    {
        $this->error = match (false){
            self::initMats() => 'Mats is empty',
            self::initProf() => 'Prof data is empty',
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
     * @return array<array<self>>
     */
    public static function allPotentialCrafts(int $itemId): array
    {
        $craftIDs = self::getCraftIDs($itemId);
        $craftIDsImpl = implode(',', $craftIDs);
        $allMats = Mat::allPotentialMats($itemId);
        if (empty($allMats)){
            $allMats[] = 0;
        }
        $allMatsImpl = implode(',', $allMats);

        $qwe = qwe("
            select crafts.*,
                   items.name as itemName,
                   doods.name as  doodName 
            from crafts 
                 inner join items on crafts.resultItemId = items.id
                    and items.onOff
                    and crafts.onOff
                    and (
                            resultItemId in ($allMatsImpl) 
                            or crafts.id in ($craftIDsImpl)
                        )
                 left join doods on doods.id = crafts.doodId
            order by deep desc, resultItemId"
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
        $Account = Account::getSelf();;
        $qwe = qwe("
            select * from uacc_crafts 
            where accountId = :accountId
            and serverGroup = :serverGroup
            and itemId = :resultItemId",
            ['accountId'    => $Account->id,
             'serverGroup'  => $Account->AccSets->serverGroup,
             'resultItemId' => $resultItemId]
        );
        return boolval($qwe);
    }

    public function initMatPrice(): void
    {
        $mats = [];
        foreach ($this->Mats as $mat){
            $mat->initPrice();
            $mats[] = $mat;
        }
        $this->Mats = $mats;
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

    public static function getMain(int $resultItemId)
    {

    }
}