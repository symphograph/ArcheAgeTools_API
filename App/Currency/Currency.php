<?php

namespace App\Currency;

use App\Currency\Repo\CurrencyRepo;
use App\Item\CurrencyItem;
use App\Item\Item;
use App\Item\ItemList;
use App\Price\Price;
use PDO;
use Symphograph\Bicycle\Helpers\Math;
use Symphograph\Bicycle\PDO\DB;

class Currency
{
    public int     $id;
    public string  $name;
    public ?int    $max;
    public bool    $personal = false;
    public ?Price  $Price;
    public ?string $icon;
    public ?int    $grade;

    /**
     * @var Item[]|null
     */
    public ?array $TradableItems;

    /**
     * @var array<CurrencyItem>
     */
    public array $MonetizationItems = [];
    public array $lost              = [];
    public int   $median            = 0;

    public static function byId(int $id): ?self
    {
        $sql = "
            select i.id,
                   i.name,
                   i.personal,
                   i.icon,
                   if(i.basicGrade, i.basicGrade, 1) as grade,
                   max
            from currency
                inner join items i 
                    on currency.id = i.id
                    and currency.id = :id";
        $params = ['id'=>$id];
        $qwe = DB::qwe($sql, $params);

        return $qwe->fetchObject(self::class) ?: null;
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

    public function initTradeableItems(): static
    {
        $arrIds = CurrencyRepo::getTradeableIds($this->id);
        $this->TradableItems = ItemList::byIds($arrIds)->getList();
        return $this;
    }

    public function initPrice(): static
    {
        $Price = Price::bySaved($this->id);
        if(!$Price) {
            $Price = new Price();
            $Price->icon = $this->icon;
            $Price->grade = $this->grade;
        }

        $Price->initItemProps();
        $this->Price = $Price;
        return $this;
    }

    public function initMedian(): static
    {
        $forMedian = [];
        foreach ($this->MonetizationItems as $item) {
            $forMedian[] = $item->currencyPrice;
        }
        if($median = Math::median($forMedian)){
            $this->median = $median;
        }
        return $this;
    }

    public function initMonetizationItems(): static
    {
        $forMedian = [];
        //$lost = [];
        foreach ($this->TradableItems as $item){
            $curItem = new CurrencyItem($item);

            if((!empty($this->max)) && ($item->priceFromNPC > $this->max)){
                continue;
            }

            if(empty($curItem->Item->Price)){
                $this->initLost($curItem);
                continue;
            }

            if(empty($curItem->currencyPrice)){
                continue;
            }

            $forMedian[] = $curItem->currencyPrice;
            $curItem->Item->Price->initItemProps();
            $this->MonetizationItems[] = $curItem;
        }
        if (empty($forMedian)){
            return $this;
        }
        if(!$median = Math::median($forMedian)){
            return $this;
        }
        $this->median = $median;
        return $this;
    }

    private function initLost(CurrencyItem $curItem): void
    {
        $Price = new Price();
        $Price->name = $curItem->Item->name;
        $Price->icon = $curItem->Item->icon;
        $Price->grade = $curItem->Item->basicGrade;
        $Price->itemId = $curItem->Item->id;
        $this->lost[] = $Price;
    }
}