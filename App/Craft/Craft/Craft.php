<?php

namespace App\Craft\Craft;

use App\AppStorage;
use App\Craft\Errors\EmptyMatsErr;
use App\Craft\LaborData;
use App\Craft\UCraft\Repo\UCraftRepo;
use App\Craft\UCraft\UCraft;
use App\Mat\Mat;
use App\Mat\Repo\MatRepo;
use App\Prof\Prof;
use App\User\AccSets;
use PDO;

class Craft extends CraftDTO
{
    public ?string $itemName;
    public ?string $doodName;
    public ?Prof   $Prof;
    public ?UCraft $countData;

    /**
     * @var ?Mat[]
     */
    public ?array $Mats;

    /**
     * @var ?Mat[]
     */
    public ?array $matPool;

    /**
     * @var ?Mat[]
     */
    public ?array $trashPool;



    public static function byId(int $id) : static|false
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

        return $qwe->fetchObject(self::class);
        /*
        $Craft->id = $id;
        $Craft->initData();
        return $Craft;
        */
    }

    public function initMats(): static
    {
        $Mats = MatRepo::listByCraft($this->id);
        if(empty($Mats)){
            throw new EmptyMatsErr($this->id);
        }
        $this->Mats = $Mats;
        return $this;
    }

    private function initProf(): static
    {
        $Prof = Prof::byNeed($this->profId, $this->profNeed);
        $this->Prof = $Prof;
        return $this;
    }

    private function initCountData(): static
    {
        if(!$uCraft = UCraftRepo::byId($this->id)){
            return $this;
        }
        $this->countData = $uCraft;
        $this->countData->LaborData = LaborData::byCraft($this);
        return $this;
    }

    public function initData(): static
    {
        $this->initMats();
        $this->initProf();
        $this->initCountData();
        return $this;
    }

    public function initMatPrice(): bool
    {
        $mats = [];
        foreach ($this->Mats as $mat){
            if(!$mat->initPrice()){
                return false;
            }
            //$mat->Price->initAuthor();
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