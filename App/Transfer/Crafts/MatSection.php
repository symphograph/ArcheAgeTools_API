<?php

namespace App\Transfer\Crafts;

use App\Transfer\Errors\CraftErr;
use App\Transfer\TargetSection;
use Symphograph\Bicycle\PDO\DB;
use Symphograph\Bicycle\Helpers;

class MatSection extends TargetSection
{
    public int $matId;
    public int $need;
    public int $grade;

    public function __construct(string $content)
    {
        parent::__construct($content);
        self::extractData();
    }

    private function extractData(): void
    {
        $error = match (false) {
            self::extractId() => 'MatId is empty',
            self::isItemExist($this->matId) => 'Mat does not exist in DB: ' . $this->matId,
            self::extractNeed() => 'MatNeed is empty: ' . $this->matId,
            self::extractGrade() => 'MatGrade is invalid: ' . $this->matId,
            default => ''
        };
        if(!empty($error)){
            throw new CraftErr($error);
        }
        unset($this->content);
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
        $this->matId = self::sanitizeInt($arr[1][0]);
        return !!$this->matId;
    }

    private function extractNeed(): bool
    {
        $regExp = '#class="qtooltip item_grade(.+?)</div>#is';
        if(!preg_match_all($regExp, $this->content, $arr)){
            return false;
        }
        if (empty($arr[1][0])){
            return false;
        }
        $arr = explode(' x ', $arr[1][0]);
        $this->need = self::sanitizeInt($arr[1] ?? 0);
        return !!$this->need;
    }

    private function extractGrade(): bool
    {
        $regExp = '#data-grade="(.+?)" class#is';
        if(!preg_match_all($regExp, $this->content, $arr)){
            return false;
        }
        if (empty($arr[1][0])){
            return false;
        }
        $grade = self::sanitizeInt($arr[1][0]);
        if(!Helpers::isIntInRange($grade, 0, 12)){
            return false;
        }
        $this->grade = $grade;
        return true;
    }

    public function putToDB(int $craftId): bool
    {
        $params = [
            'craftId' => $craftId,
            'itemId' => $this->matId,
            'need' => $this->need,
            'matGrade' => $this->grade
        ];
        return DB::replace('craftMaterials', $params);
    }
}