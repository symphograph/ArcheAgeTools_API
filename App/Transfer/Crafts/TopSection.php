<?php

namespace App\Transfer\Crafts;

use App\Transfer\TargetSection;

class TopSection extends TargetSection
{
    public string $craftName;
    public int    $laborNeed = 0;
    public int    $craftTime = 0;

    public function __construct(string $content)
    {
        parent::__construct($content);
        self::extractData();
    }

    private function extractData(): void
    {
        if(!self::extractCraftName()){
            $this->warnings[] = 'CraftName is empty';
        }
        if(!self::extractLaborNeed()){
            $this->warnings[] = 'LaborNeed is empty';
        }
        if(!self::extractCraftTime()){
            $this->warnings[] = 'CraftTime is empty';
        }
        unset($this->content);
    }

    private function extractCraftName(): bool
    {
        $regExp = '#<span class="item_title">(.+?)</span>#is';
        if(!preg_match_all($regExp, $this->content, $arr)){
            return false;
        }
        if (empty($arr[1][0])){
            return false;
        }
        $this->craftName = self::sanitizeItemName($arr[1][0]);
        return !empty($this->craftName);
    }

    private function extractLaborNeed(): bool
    {
        $regExp = '#Очки работы: (.+?)<br>#is';
        if(!preg_match_all($regExp, $this->content, $arr)){
            return false;
        }
        if (!isset($arr[1][0])){
            return false;
        }
        $this->laborNeed = self::sanitizeInt($arr[1][0]);
        return true;
    }

    private function extractCraftTime(): bool
    {
        $regExp = '#Время производства: (.+?)с#is';
        if(!preg_match_all($regExp, $this->content, $arr)){
            return false;
        }
        if (empty($arr[1][0])){
            return false;
        }
        $this->craftTime = self::sanitizeInt($arr[1][0]);
        return !!$this->craftTime;
    }

}