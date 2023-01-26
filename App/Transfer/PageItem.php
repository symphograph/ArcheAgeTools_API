<?php

namespace App\Transfer;

use App\Item\Category;
use App\Test\Test;
use function Symfony\Component\String\s;

class PageItem extends Page
{
    public ItemDTO|false $ItemDB;
    public ?ItemDTO $ItemDTO;
    public string $targetArea;

    public function __construct(public int $itemId)
    {
    }

    public function executeTransfer(): bool
    {
        if(!$this->ItemDB = ItemDTO::byDB($this->itemId)){
            $this->error = 'Item does not exist in DB';
            return false;
        }
        $this->ItemDTO = new ItemDTO();

        if(!self::parseData()){
            return false;
        }
        return true;
    }

    private function parseData(): bool
    {
        $result = match (false){
            self::initContent() => false,
            self::initTargetArea() => false,
            self::initItemId() => false,
            self::initItemName() => false,
            self::initIsPersonal() => false,
            self::initGrade() => false,
            self::initCategory() => false,
            self::initDescription() => false,
            self::initPrices() => false,
            default => true
        };

        self::initIsTradeNPC();

        return $result;
    }

    private function initContent(): string|false
    {
        $result = self::curl(self::site . 'item/' . $this->itemId . '/', self::options);
        if($result->err || $result->http_code !== 200 || empty($result->content)){
            $this->error = 'content not received';
            return false;
        }
        $this->content = $result->content;
        return true;
    }

    private function initTargetArea(): bool
    {
        preg_match_all('#<td colspan="2">ID:(.+?)<div class="addon_info">#is', $this->content, $arr);
        if(empty($arr[0][0])){
            $this->error = 'ItemPage is empty';
            return false;
        }

        $this->targetArea = $arr[0][0];
        return true;
    }

    private function initItemId(): bool
    {
        if(!ItemTargetArea::checkItemId($this->targetArea, $this->itemId)){
            $this->error = 'Invalid Item Id';
            return false;
        }
        return true;
    }

    private function initItemName(): bool
    {
        $name = ItemTargetArea::extractItemName($this->targetArea);
        if(empty($name)){
            $this->error = 'ItemName is empty';
            return false;
        }
        if(ItemTargetArea::isUnnecessary($name)){
            $this->error = 'Item is unnecessary';
            return false;
        }
        $this->ItemDTO->name = $name;
        return true;
    }

    private function initIsPersonal(): bool
    {
        $this->ItemDTO->personal = str_contains($this->targetArea, 'Персональный предмет');
        return true;
    }

    private function initGrade(): bool
    {
        $this->ItemDTO->basicGrade = ItemTargetArea::extractGrade($this->targetArea);
        return true;
    }

    private function initCategory(): bool
    {
        if ($this->ItemDB->categId > 1){
            $this->ItemDTO->categId = $this->ItemDB->categId;
            return true;
        }
        $categoryName = ItemTargetArea::extractCategoryName($this->targetArea);

        $error = match (true){
            empty($categoryName) => 'Category is empty',
            preg_match(
                '/deprecated|test|тестовый|NO_NAME|Не используется/ui',
                $categoryName
            ) => 'Category is unnecessary',
            empty($Categories = Category::byName($categoryName)) => 'Category does not exist in DB',
            count($Categories) > 1 => 'Category having variants',
            default => ''
        };
        if(!empty($error)){
            $this->error = $error;
            return false;
        }

        $this->ItemDTO->categId = $Categories[0]->id;

        return true;
    }

    private function initDescription(): bool
    {
        $description = DescriptionExtract::extract($this->targetArea);
        if(empty($description)){
            $this->error = 'Description is empty';
            return false;
        }
        $this->ItemDTO->description = $description;
        return true;
    }

    private function initPrices(): bool
    {
        self::initPriceFromNPC();
        self::initPriceToNPC();
        return self::initCurrencyId();
    }

    private function initIsTradeNPC(): void
    {
        $this->ItemDTO->isTradeNPC = match (true){
            str_contains($this->content, 'Можно приобрести') => true,
            str_contains($this->content, 'Продаётся у NPC') => true,
            $this->ItemDTO->currencyId && $this->ItemDTO->currencyId !==500 => true,
            default => false
        };
    }

    //----------------------------------------------------------------------
    private function initPriceFromNPC(): void
    {
        $price = ItemTargetArea::extractPrice($this->targetArea, '#Цена покупки:(.+?)</tr>#is');
        if(!$price){
            return;
        }
        $this->ItemDTO->priceFromNPC = $price;
    }

    private function initPriceToNPC(): void
    {
        if(str_contains($this->targetArea, 'не нужен торговцам')) {
            return;
        }
        $price = ItemTargetArea::extractPrice($this->targetArea, '#Цена продажи:(.+?)</tr>#is');
        if(!$price){
            return;
        }
        $this->ItemDTO->priceToNPC = $price;
    }

    private function initCurrencyId(): bool
    {
        $currencyId = ItemTargetArea::extractCurrencyId($this->targetArea);
        if (!$currencyId){
            if(str_contains($this->content, 'Можно приобрести') || str_contains($this->content, 'Продаётся у NPC')){
                $this->error = 'Currency not defined';
                return false;
            }
            if(!$this->ItemDTO->personal){
                $currencyId = 500;
            }
        }
        $this->ItemDTO->currencyId = $currencyId;
        return true;
    }
}