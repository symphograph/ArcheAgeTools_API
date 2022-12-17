<?php

namespace Item;

use PDO;

class Item
{
    public int|null $id;
    public string|null $name;
    public bool $craftable = false;
    public bool $personal = false;
    public bool $isTradeNPC = false;
    public int|null $valut_id;
    public string|null $icon;
    public int|null $grade;
    public int|null $categ_id;
    public Info|null $Info;
    public Price|null $Price;
    public Pricing|null $Pricing;

    public function __set(string $name, $value): void{}

    /**
     * @return bool|array<self>
     */
    public static function searchList(array $ItemIds = []): bool|array
    {

        if(empty($ItemIds)){
            $qwe = qwe("select *, 
            item_id as id, 
            item_name as name, 
            if(basic_grade,basic_grade,1) as grade 
            from items where on_off
            order by name, craftable desc, personal, grade"
            );

        }else
        {
            if(!\MyHelpers::isArrayIntList($ItemIds)){
                return false;
            }

            $ItemIds = '('.implode(',',$ItemIds).')';
            $qwe = qwe("select *, 
            item_id as id, 
            item_name as name, 
            if(basic_grade,basic_grade,1) as grade 
            from items where on_off
                       and item_id in $ItemIds
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
            item_id as id, 
            item_name as name, 
            if(basic_grade,basic_grade,1) as grade 
            from items where on_off
                       and item_id = :id",
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
        $Price = Price::byAccount($Account->id, $this->id, $Account->AccSets->server_group);
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
            SELECT item_id FROM items 
            WHERE 
            (
                (
                    !isTradeNPC
                    AND ismat
                    AND !craftable
                    AND on_off
                    AND personal
                )
                OR item_id IN (SELECT valut_id FROM valutas)
            )
            AND item_id != 500
	    ");
        if(!$qwe or !$qwe->rowCount()){
            return [];
        }
        $privateItems = $qwe->fetchAll(PDO::FETCH_COLUMN);
        return $privateItems;
    }

}