<?php

namespace App\Transfer;

use App\Item\Category;

class PageItem extends Page
{
    public ItemDTO|false $ItemDB;
    public ?ItemDTO $ItemDTO;
    public ItemTargetArea $TargetArea;
    public ?GradeArea $GradeArea;
    private $readOnly = true;

    public function __construct(public int $itemId)
    {
        self::saveLast();
    }

    public function executeTransfer(bool $readOnly = true): bool
    {
        $this->readOnly = $readOnly;
        if(!$this->ItemDB = ItemDTO::byDB($this->itemId)){
            $this->error = 'Item does not exist in DB';
            return false;
        }
        $this->ItemDTO = new ItemDTO();

        if(!self::parseData()){
            return false;
        }
        if(empty($this->error) && !$readOnly){
            if(!self::updateDB()){
                $this->error = 'updateDB error';
            }
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
            self::isNecessary() => false,
            self::initItemLvl() => false,
            self::initIsPersonal() => false,
            self::initGrade() => false,
            self::initCategory() => false,
            self::initDescription() => false,
            self::initPrices() => false,
            self::loadIcon() => false,
            default => true
        };
        if(!$result){
            return false;
        }

        self::initIsTradeNPC();
        self::initIsGradable();
        self::initExpiresDate();

        return true;
    }

    private function initContent(?int $grade = null): string|false
    {
        $query = $grade ? 'grade=' . $grade : '';
        $url = self::site . '/ru/item/' . $this->itemId . '/' . $query;
        return self::getContent($url);
    }

    private function initTargetArea(): bool
    {
        preg_match_all('#<td colspan="2">ID:(.+?)<div class="addon_info">#is', $this->content, $arr);
        if(empty($arr[0][0])){
            $this->error = 'ItemPage is empty';
            return false;
        }
        $this->TargetArea = new ItemTargetArea($arr[0][0]);
        return true;
    }

    private function initItemId(): bool
    {
        if(!$this->TargetArea->checkItemId($this->itemId)){
            $this->error = 'Invalid Item Id';
            return false;
        }
        $this->ItemDTO->id = $this->itemId;
        return true;
    }

    private function initItemName(): bool
    {
        $name = $this->TargetArea->extractItemName();
        if(empty($name)){
            $this->error = 'ItemName is empty';
            return false;
        }

        $this->ItemDTO->name = $name;
        return true;
    }

    private function isNecessary(): bool
    {
        if($this->TargetArea->isUnnecessary($this->ItemDTO->name)){
            $this->error = 'Item is unnecessary';
            return false;
        }
        return true;
    }

    private function initExpiresDate(): void
    {
        if($this->ItemDTO->expiresAt = $this->TargetArea->extractExpiresAt()){
            $this->ItemDTO->onOff = 0;
        }
    }

    private function initIsPersonal(): bool
    {
        $this->ItemDTO->personal = str_contains($this->TargetArea->content, 'Персональный предмет');
        return true;
    }

    private function initItemLvl(): bool
    {
        $lvl = $this->TargetArea->extractItemLvl();
        if($lvl === false){
            $lvl = $this->ItemDB->lvl ?? 0;
        }
        $this->ItemDTO->lvl = $lvl;
        return true;
    }

    private function initGrade(): bool
    {
        $this->ItemDTO->basicGrade = $this->TargetArea->extractGrade();
        return true;
    }

    private function initCategory(): bool
    {
        $categoryName = $this->TargetArea->extractCategoryName();
        $error = match (true){
            empty($categoryName) => 'Category is empty',
            $categoryName === 'тест',
            !!preg_match(
                '/deprecated|test|TEST|тестовый|NO_NAME|Не используется/ui',
                $categoryName
            ) => 'Category is unnecessary',
            empty($Categories = Category::byName($categoryName)) => 'Category does not exist in DB: ' . $categoryName,
            self::isVariableCategory($Categories) => 'Category having variants: ' . $categoryName,
            default => ''
        };

        if(!empty($error)){
            $this->error = $error;
            return false;
        }

        $this->ItemDTO->categId = $Categories[0]->id;

        return true;
    }

    /**
     * @param array<Category> $Categories
     * @return bool
     */
    private function isVariableCategory(array $Categories): bool
    {
        if(!(count($Categories) > 1)){
            return false;
        }
        if(!empty($this->ItemDB->categId)){
            $this->ItemDTO->categId = $this->ItemDB->categId;
            return false;
        }
        return true;
    }

    private function initDescription(): bool
    {
        $description = DescriptionExtract::extract($this->TargetArea->content);
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

    private function initIsGradable(): void
    {
        $this->ItemDTO->isGradable = self::initGradeArea();
    }

    private function loadIcon(): bool
    {
        $iconFileName = $this->TargetArea->extractIconSRC();
        $IconPage = new PageIcon($iconFileName, $this->itemId);
        if(!$IconPage->executeTransfer($this->readOnly)){
            $this->error = 'Icon: ' . $IconPage->error;
            return false;
        }
        $this->ItemDTO->icon = $IconPage->newSRC;
        $this->ItemDTO->iconMD5 = $IconPage->iconMD5;
        return true;
    }

    //----------------------------------------------------------------------

    private function initPriceFromNPC(): void
    {
        $price = $this->TargetArea->extractPrice('#Цена покупки:(.+?)</tr>#is');
        if(!$price){
            return;
        }
        $this->ItemDTO->priceFromNPC = $price;
    }

    private function initPriceToNPC(): void
    {
        if(str_contains($this->TargetArea->content, 'не нужен торговцам')) {
            return;
        }
        $price = $this->TargetArea->extractPrice('#Цена продажи:(.+?)</tr>#is');
        if(!$price){
            return;
        }
        $this->ItemDTO->priceToNPC = $price;
    }

    private function initCurrencyId(): bool
    {
        if($currencyId = $this->TargetArea->extractCurrencyId()){
            $this->ItemDTO->currencyId = $currencyId;
            return true;
        }
        if(!$this->ItemDTO->priceFromNPC && !$this->ItemDTO->priceToNPC){
            if($this->ItemDTO->personal){
                $this->ItemDTO->currencyId = 500;
            }
            return true;
        }

        if(str_contains($this->content, 'Можно приобрести') || str_contains($this->content, 'Продаётся у NPC')){
            $this->error = 'Currency not defined';
            printr($this->ItemDTO);
            return false;
        }

        return true;
    }

    private function initGradeArea(): bool
    {
        if(!$GradeArea = GradeArea::extractSelf($this->content)){
            return false;
        }
        $this->GradeArea = $GradeArea;
        return true;
    }


    //------------------------------------------------------------------------

    private function saveLast()
    {
        qwe("update transfer_Last set id = :itemId where lastRec = 'item'", ['itemId' => $this->itemId]);
    }

    private function updateDB(): bool
    {
        return match (false){
            $this->ItemDB->update($this->ItemDTO) => false,
            $this->TargetArea->putSectionsToDB($this->itemId) => false,
            default => true
        };
    }
}