<?php

namespace App\Item;

use PDO;
use Symphograph\Bicycle\Helpers;

class Item
{
    public ?int     $id;
    public ?string  $name;
    public ?int     $priceFromNPC;
    public ?int     $priceToNPC;
    public ?int     $currencyId;
    public ?string  $icon;
    public ?int     $grade;
    public ?int     $categId;
    public ?Info    $Info;
    public ?Price   $Price;
    public ?Pricing $Pricing;

    public bool $craftable  = false;
    public bool $personal   = false;
    public bool $isTradeNPC = false;
    public bool $isMat      = false;
    public bool $isBuyOnly  = false;

    public function __set(string $name, $value): void{}

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

    public static function byId(int $id) : self|bool
    {
        $qwe = qwe("select *, 
            if(basicGrade,basicGrade,1) as grade 
            from items where onOff
                       and id = :id",
        ['id' => $id]
        );
        if(!$qwe || !$qwe->rowCount()) {
            return false;
        }
        return $qwe->fetchObject(get_class());
    }

    public function initInfo(): void
    {
        $this->Info = Info::byId($this->id);
    }

    public function initPrice(): bool
    {
        global $Account;
        $Price = Price::bySaved($this->id);
        if($Price){
            $this->Price = $Price;
            return true;
        }
        return false;
    }

    public function initPricing(): void
    {
        if($Pricing = Pricing::byItemId($this->id)){
            $this->Pricing = $Pricing;
        }
    }

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
        if(!$qwe or !$qwe->rowCount()){
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
        global $Account;
        $qwe = qwe("
            select * from uacc_buyOnly 
            where itemId = :itemId 
            and accountId = :accountId",
            ['itemId' => $this->id, 'accountId' => $Account->id]
        );
        return $qwe && $qwe->rowCount();
    }

}