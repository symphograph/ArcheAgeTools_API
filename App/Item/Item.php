<?php

namespace App\Item;

use App\DTO\ItemDTO;
use App\User\AccSettings;
use PDO;
use Symphograph\Bicycle\DTO\ModelTrait;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Helpers;

class Item extends ItemDTO
{
    use ModelTrait;
    public ?Info    $Info;
    public ?Price   $Price;
    public ?Pricing $Pricing;
    public int $grade = 1;
    public bool $isBuyOnly  = false;
    public bool $isPack;

    /**
     * @return bool|array<self>
     */
    public static function searchList(array $ItemIds = []): bool|array
    {

        if(empty($ItemIds)){
            $qwe = qwe("select *, 
            if(basicGrade,basicGrade,1) as grade 
            from items where onOff
            order by name, craftable desc, personal, grade"
            );

        }else
        {
            if(!Helpers::isArrayIntList($ItemIds)){
                return false;
            }

            $ItemIds = '('.implode(',',$ItemIds).')';
            $qwe = qwe("select *, 
            if(basicGrade,basicGrade,1) as grade 
            from items where onOff
                       and id in $ItemIds
            order by name, craftable desc, personal, grade"
            );
        }
        if(!$qwe || !$qwe->rowCount()) {
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function byId(int|string $id) : self
    {
        $ItemDTO = ItemDTO::byId($id)
            or throw new AppErr("item $id does not exist in DB");
        $Item = new self();
        $Item->bindSelf($ItemDTO);
        $Item->grade = $ItemDTO->basicGrade;
        $Item->isPack = Category::isPack($Item->categId);
        return $Item;
    }

    public function initInfo(): void
    {
        $this->Info = Info::byId($this->id);
    }

    public function initPrice(): bool
    {
        $Price = Price::bySaved($this->id);
        if(!$Price) return false;
        $this->Price = $Price;
        return true;
    }

    public function initPricing(): void
    {
        if($Pricing = Pricing::byItemId($this->id)){
            $this->Pricing = $Pricing;
        }
    }

    /**
     * @return int[]
     */
    public static function privateItems(): array
    {
        global $privateItems;
        if(isset($privateItems)){
            return $privateItems;
        }


        $qwe = qwe("
            SELECT id FROM items 
            WHERE 
            (
                (
                    !isTradeNPC
                    AND ismat
                    AND !craftable
                    AND onOff
                    AND personal
                )
                OR id IN (SELECT id FROM currency)
            )
            AND id != 500
	    ");
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        $privateItems = $qwe->fetchAll(PDO::FETCH_COLUMN);
        return $privateItems;
    }

    public function initIsBuyOnly(): void
    {
        $this->isBuyOnly = self::isBuyOnly();
    }

    private function isBuyOnly(): bool
    {
        if(!$this->craftable || $this->personal){
            return false;
        }
        $AccSets = AccSettings::byGlobal();
        $qwe = qwe("
            select * from uacc_buyOnly 
            where itemId = :itemId 
            and accountId = :accountId",
            ['itemId' => $this->id, 'accountId' => $AccSets->accountId]
        );
        return $qwe && $qwe->rowCount();
    }
}