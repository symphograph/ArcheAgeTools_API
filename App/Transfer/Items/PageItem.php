<?php

namespace App\Transfer\Items;

use App\Item\Category;
use App\Transfer\Errors\ItemErr;
use App\Transfer\Page;
use App\DTO\ItemDTO;

class PageItem extends Page
{

    public ItemTargetArea $TargetArea;
    public ?GradeArea $GradeArea;

    public function __construct(
        public ItemDTO $ItemDTO,
        bool $readOnly = true
    )
    {
        $this->readOnly = $readOnly;
        self::saveLast($ItemDTO->id, 'item');
    }

    /**
     * @throws ItemErr
     */
    public function executeTransfer(): void
    {
        self::initContent();
        self::initTargetArea();
        self::initItemId();
        self::initItemName();
        self::isNecessary();
        self::initCategory();
        self::initDescription();
        self::initPrices();
        self::loadIcon();


        self::initItemLvl();
        self::initIsPersonal();
        self::initGrade();
        self::initIsTradeNPC();
        self::initIsGradable();
        self::initExpiresDate();

        self::updateDB();
    }

    //Required params________________________________

    /**
     * @throws ItemErr
     */
    private function initContent(): void
    {
        $url = self::site . '/ru/item/' . $this->ItemDTO->id . '/';
        self::getContent($url);
    }

    /**
     * @throws ItemErr
     */
    private function initTargetArea(): void
    {
        preg_match_all('#<td colspan="2">ID:(.+?)<div class="addon_info">#is', $this->content, $arr);
        if(empty($arr[0][0])){
            throw new ItemErr('ItemPage is empty');
        }
        $this->TargetArea = new ItemTargetArea($arr[0][0]);
    }

    /**
     * @throws ItemErr
     */
    private function initItemId(): void
    {
        $this->TargetArea->checkItemId($this->ItemDTO->id)
            or throw new ItemErr('Invalid Item Id');
    }

    /**
     * @throws ItemErr
     */
    private function initItemName(): void
    {
        $name = $this->TargetArea->extractItemName();
        if(empty($name)){
            throw new ItemErr('ItemName is empty');
        }
        if($name !== $this->ItemDTO->name){
            $this->warnings[] = "dif itemName: $name";
        }
    }

    /**
     * @throws ItemErr
     */
    private function isNecessary(): void
    {
        if($this->TargetArea->isUnnecessary($this->ItemDTO->name)){
            throw new ItemErr('Item is unnecessary');
        }
    }

    /**
     * @throws ItemErr
     */
    private function initCategory(): void
    {
        $categoryName = $this->TargetArea->extractCategoryName();
        $error = match (true) {
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

        if (!empty($error)) {
            throw new ItemErr($error);
        }

        $this->ItemDTO->categId = $Categories[0]->id ?? 0;
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
        if(!empty($this->itemDTO->categId)){
            return false;
        }
        return true;
    }

    /**
     * @throws ItemErr
     */
    private function initDescription(): void
    {
        $description = DescriptionExtract::extract($this->TargetArea->content);
        if(empty($description)){
            throw new ItemErr('Description is empty');
        }
        $this->ItemDTO->description = $description;
    }

    /**
     * @throws ItemErr
     */
    private function initPrices(): void
    {
        self::initPriceFromNPC();
        self::initPriceToNPC();
        self::initCurrencyId();
    }

    /**
     * @throws ItemErr
     */
    private function loadIcon(): void
    {
        $iconFileName = $this->TargetArea->extractIconSRC();
        $IconPage = new PageIcon($iconFileName, $this->ItemDTO->id);
        $IconPage->executeTransfer($this->readOnly);
        $this->ItemDTO->icon = $IconPage->newSRC;
        $this->ItemDTO->iconMD5 = $IconPage->iconMD5;
    }


    //Other params______________________________________
    private function initItemLvl(): void
    {
        $lvl = $this->TargetArea->extractItemLvl();
        if($lvl === false){
            $lvl = $this->itemDTO->lvl ?? 0;
        }
        $this->ItemDTO->lvl = $lvl;
    }

    private function initIsPersonal(): void
    {
        $this->ItemDTO->personal = str_contains($this->TargetArea->content, 'Персональный предмет');
    }

    private function initGrade(): void
    {
        $this->ItemDTO->basicGrade = $this->TargetArea->extractGrade();
    }

    private function initIsTradeNPC(): void
    {
        $this->ItemDTO->isTradeNPC = match (true){
            str_contains($this->content, 'Можно приобрести') => true,
            str_contains($this->content, 'Продаётся у NPC') => true,
            !empty($this->ItemDTO->currencyId) && $this->ItemDTO->currencyId !==500 => true,
            default => false
        };
    }

    private function initIsGradable(): void
    {
        $this->ItemDTO->isGradable = self::initGradeArea();
    }

    private function initExpiresDate(): void
    {
        if($this->ItemDTO->expiresAt = $this->TargetArea->extractExpiresAt()){
            $this->ItemDTO->onOff = 0;
        }
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

    private function initCurrencyId(): void
    {
        if($currencyId = $this->TargetArea->extractCurrencyId()){
            $this->ItemDTO->currencyId = $currencyId;
            return;
        }
        if(empty($this->ItemDTO->priceFromNPC) && empty($this->ItemDTO->priceToNPC)){
            if($this->ItemDTO->personal){
                $this->ItemDTO->currencyId = 500;
            }
            return;
        }

        if(str_contains($this->content, 'Можно приобрести') || str_contains($this->content, 'Продаётся у NPC')){
            throw new ItemErr('Currency not defined');
        }
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

    private function updateDB(): void
    {
        if($this->readOnly) return;
        qwe("START TRANSACTION");
        $this->ItemDTO->putToDB();
        $this->TargetArea->putSectionsToDB($this->ItemDTO->id);
        qwe("COMMIT");
    }
}