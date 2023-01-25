<?php

namespace App\Item;

use PDO;
use Symphograph\Bicycle\Helpers;

class Currency
{
    public int     $id;
    public string  $name;
    public ?int $max;
    public bool    $personal = false;
    public ?Price  $Price;
    public ?string $icon;
    public ?int    $grade;

    /**
     * @var array<Item>|null
     */
    public ?array $TradableItems;

    /**
     * @var array<CurrencyItem>
     */
    public array $MonetisationItems = [];
    public array $lost = [];
    public int   $median            = 0;

    public static function byId(int $id): self|false
    {
        $qwe = qwe("
            select i.id,
                   i.name,
                   i.personal,
                   i.icon,
                   if(i.basicGrade, i.basicGrade, 1) as grade,
                   max
            from currency
                inner join items i 
                    on currency.id = i.id
                    and currency.id = :id",
            ['id'=>$id]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    /**
     * @return array<self>|false
     */
    public static function getList(): array|false
    {
        $qwe = qwe("
            select i.id,
                   i.name,
                   i.personal,
                   i.icon,
                   if(i.basicGrade, i.basicGrade, 1) as grade,
                   max
            from currency
                inner join items i 
                    on currency.id = i.id"
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function getTradableIds(int $currencyId): array
    {
        $qwe = qwe("
            select id 
            from items 
            where currencyId = :currencyId
              and onOff
            and !personal",
        ['currencyId'=> $currencyId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN);
    }

    public function initTradableItems(): void
    {
        $arrIds = self::getTradableIds($this->id);
        $this->TradableItems = Item::searchList($arrIds);
    }

    public function initPrice(): bool
    {
        if(!$Price = Price::bySaved($this->id)){
            return false;
        }
        $Price->initItemProps();
        $this->Price = $Price;
        return true;
    }

    public function initMonetisationItems(): void
    {
        $forMedian = [];
        //$lost = [];
        foreach ($this->TradableItems as $item){
            $curItem = new CurrencyItem($item);

            if((!empty($this->max)) && ($item->priceFromNPC > $this->max)){
                continue;
            }

            if(empty($curItem->Item->Price)){
                self::initLost($curItem);
                continue;
            }

            if(empty($curItem->currencyPrice)){
                continue;
            }

            $forMedian[] = $curItem->currencyPrice;
            $curItem->Item->Price->initItemProps();
            $this->MonetisationItems[] = $curItem;
        }
        if (empty($forMedian)){
            return;
        }
        if(!$median = Helpers::median($forMedian)){
            return;
        }
        $this->median = $median;
    }

    private function initLost(CurrencyItem $curItem): void
    {
        $Price = new Price();
        $Price->name = $curItem->Item->name;
        $Price->icon = $curItem->Item->icon;
        $Price->grade = $curItem->Item->grade;
        $Price->itemId = $curItem->Item->id;
        $this->lost[] = $Price;
    }
}