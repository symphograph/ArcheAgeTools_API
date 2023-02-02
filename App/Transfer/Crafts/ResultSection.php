<?php

namespace App\Transfer\Crafts;

use App\Item\Item;
use App\Transfer\TargetSection;

class ResultSection extends TargetSection
{
    public int $resultItemId;
    public int $resultAmount;

    public function __construct(string $content)
    {
        parent::__construct($content);
        self::extractData();
        unset($this->content);
    }

    private function extractData(): void
    {
        $this->error = match (false){
            self::extractId() => 'ResultItemId is empty',
            self::isItemExist($this->resultItemId) => 'ResultItem does not exist in DB: ' . $this->resultItemId,
            self::extractResultAmount() => 'ResultAmount is empty',
            default => ''
        };
    }

    private function extractId(): bool
    {
        $regExp = '#<a href="/ru/item/(.+?)/#is';
        if(!preg_match_all($regExp, $this->content, $arr)){
            return false;
        }
        if (empty($arr[1][0])){
            return false;
        }
        $this->resultItemId = self::sanitizeInt($arr[1][0]);
        return !!$this->resultItemId;
    }

    private function extractResultAmount(): bool
    {
        $regExp = '#<div class="reward_counter_big">(.+?)</div>#is';
        if(!preg_match_all($regExp, $this->content, $arr)){
            return false;
        }
        if (empty($arr[1][0])){
            return false;
        }
        $arr = explode(' x ', $arr[1][0]);

        $this->resultAmount = self::sanitizeInt($arr[1] ?? 0);
        return !!$this->resultAmount;
    }

}