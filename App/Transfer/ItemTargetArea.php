<?php

namespace App\Transfer;

use Symphograph\Bicycle\Helpers;

class ItemTargetArea extends TargetArea
{
    /**
     * @var array<ItemTargetSection>|null
     */
    public ?array $sections;

    public function __construct(string $content)
    {
        parent::__construct($content);
        self::initSections();
    }

    public function checkItemId(int $itemId): bool
    {
        $regExp = '#ID: (.+?)td>#is';
        if(!preg_match_all($regExp, $this->content, $arr)){
            return false;
        }
        return $itemId === self::sanitizeInt($arr[1][0] ?? '');
    }

    public function extractItemName(): string
    {
        $regExp = '#id="item_name"(.+?)</span>#is';
        if(!preg_match_all($regExp, $this->content, $arr)){
            return false;
        }
        return self::sanitizeItemName($arr[1][0] ?? '');
    }

    public function isUnnecessary(string $itemName): bool
    {
       return !!preg_match('/deprecated|test|Тест: |тестовый|NO_NAME|Не используется/ui', $itemName);
    }

    public function isBeforeTimeOut(): bool
    {
        $regExp = '#Действует до:(.+?)</td>#is';
        if(!preg_match_all($regExp, $this->content, $arr)){
            return false;
        }
        if (empty($arr[1][0])){
            return false;
        }
        $beforeTime = self::sanitizeDateTime($arr[1][0]);
        if(empty($beforeTime)){
            return false;
        }
        return $beforeTime < date('Y-m-d H:i:s');
    }

    public function extractItemLvl(): int|false
    {
        $regExp = '#Уровень предмета:(.+?)</td>#is';
        if(!preg_match_all($regExp, $this->content, $arr)){
            return false;
        }
        return self::sanitizeInt($arr[1][0] ?? '');
    }

    public function extractGrade(): int
    {
        $regExp = '#item_grade_(.+?)id#is';
        if(!preg_match_all($regExp, $this->content, $arr)){
            return 1;
        }
        $grade = GradeArea::sanitizeGrade($arr[1][0] ?? '');

        if(Helpers::isIntInRange($grade, 0, 12)){
            return intval($grade);
        }
        return 1;
    }

    public function extractCategoryName(): string
    {
        $regExp = '#<td class="item-icon">(.+?)<br>#is';
        if(!preg_match_all($regExp, $this->content, $arr)){
            return false;
        }
        return self::sanitizeItemName($arr[1][0] ?? '');
    }

    public function extractPrice(string $regExp): false|int
    {
        if(!preg_match_all($regExp, $this->content, $arr)){
            return false;
        }
        return self::sanitizeInt($arr[1][0] ?? '');
    }

    public function extractCurrencyId(): false|int
    {
        $regExp = '#Цена покупки:(.+?)</tr>#is';
        if(!preg_match_all($regExp, $this->content, $arr)){
            return false;
        }
        if (empty($arr[1][0])){
            return false;
        }
        $priceTypeArea = $arr[1][0];
        return match (true){
            str_contains($priceTypeArea, 'alt="bronze"') => 500,        // gold
            str_contains($priceTypeArea, 'alt="lp"') => 3,              // Ремесленная репутация
            str_contains($priceTypeArea, 'alt="honor_point') => 4,      // Честь
            str_contains($priceTypeArea, 'item--23633') => 23633,       // Дельфийская звезда
            str_contains($priceTypeArea, 'item--25816') => 25816,       // Коллекционная монета «Джин»
            str_contains($priceTypeArea, 'item--26921') => 26921,       // Звездный ролл
            str_contains($priceTypeArea, 'item--8001661') => 8001661,   // Арткоин
            str_contains($priceTypeArea, 'item--41138') => 41138,       // Монета ArcheAge
            str_contains($priceTypeArea, 'item--35817') => 35817,       // Сертификат претендента
            str_contains($priceTypeArea, 'item--25960') => 25960,       // Сертификат на покупку минеральной воды
            str_contains($priceTypeArea, 'item--26880') => 26880,       // Обрезанный соверен
            str_contains($priceTypeArea, 'item--41017') => 41017,       // Рекомендательный жетон крепостной фермы
            str_contains($priceTypeArea, 'item--36978') => 36978,       // Знак Нуи
            default => 0
        };
    }

    private function initSections()
    {
        $sections = explode('<hr class="hr_long">', $this->content);
        $combined = [];
        foreach ($sections as $section){
            $section = new ItemTargetSection($section);
            $combined[$section->type][] = $section;
        }
        $this->sections = ItemTargetSection::combine($combined);
        //self::printSections();
    }

    public function printSections(array $types = [])
    {
        foreach ($this->sections as $section){
            if(!empty($types) && !in_array($section->type, $types)){
                continue;
            }
            echo $section->type . '<br>';
            echo $section->content . '<hr>';
        }
    }

    /**
     * @return array<ItemTargetSection>|null
     */
    public function getSectionsByType(string $type): ?array
    {
        return array_filter($this->sections,function($section, $type){
            return $section->type === $type;
        });
    }

    public function extractIconSRC(): false|string
    {
        $regExp = '#<td class="item-icon"><div style="position: relative; left: 0; top: 0;"><img src="//archeagecodex.com/items/(.+?)\.png" style#is';

        if(!preg_match_all($regExp, $this->content, $arr)){
            return false;
        }
        if(empty($arr[1][0])){
            return false;
        }
        return str_replace("\\",'/', $arr[1][0]) . '.png';
    }


    public function putSectionsToDB(int $itemId): bool
    {
        self::deleteSectionsFromDB($itemId);
        foreach ($this->sections as $section){
            if($section->isIgnored()) continue;

            if(!$section->putToDB($itemId)){
                self::deleteSectionsFromDB($itemId);
                return false;
            }
        }
        return true;
    }

    private static function deleteSectionsFromDB(int $itemId)
    {
        qwe("delete from itemDescriptions where itemId = :itemId",['itemId'=> $itemId]);
    }
}