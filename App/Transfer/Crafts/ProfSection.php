<?php

namespace App\Transfer\Crafts;

use App\Transfer\TargetSection;

class ProfSection extends TargetSection
{
    public int $profId;
    public int $doodId;
    private string $profName;

    public function __construct(string $content)
    {
        parent::__construct($content);
        self::extractData();
        unset($this->content);
    }


    private function extractData(): void
    {
        if(!self::extractDood()){
            return;
        }

        self::initProfId();
    }

    private function extractDood(): bool
    {
        $regExp = '#data-id="doodad--(.+?)data-grade#is';
        if(!preg_match_all($regExp, $this->content, $arr)){
            $this->warnings[] = 'Dood is empty';
            return true;
        }
        if (empty($arr[1][0])){
            $this->warnings[] = 'Dood is empty';
            return true;
        }
        if(!$this->doodId = self::sanitizeInt($arr[1][0])){
            $this->warnings[] = 'Dood is 0';
            return true;
        }
        if(!self::isDoodExist($this->doodId)){
            $this->error = 'Dood does not exist in DB: ' . $this->doodId;
            return false;
        }
        return true;
    }

    private function initProfId(): void
    {
        if(!self::extractProfName()){
            $this->warnings[] = 'ProfName is empty';
            $this->profName = 'Прочее';
        }

        $qwe = qwe("select id from profs where name = :name", ['name' => $this->profName]);
        if (!$qwe || !$qwe->rowCount()) {
            $this->error = 'Prof does not exist in DB';
            return;
        }
        if ($qwe->rowCount() > 1) {
            $this->error = 'Prof having variants';
            return;
        }
        $q = $qwe->fetchObject();
        if ($q->id) {
            $this->profId = $q->id;
            return;
        }
        $this->error = 'Prof unknown error';
    }

    private function extractProfName(): bool
    {
        $regExp = '#Ремесло: (.+?)<br>#is';
        if(!preg_match_all($regExp, $this->content, $arr)){
            $regExp = '#Ремесло: (.+?)</div>#is';
            if(!preg_match_all($regExp, $this->content, $arr)){
                return false;
            }
        }
        if (empty($arr[1][0])){
            return false;
        }
        $this->profName = self::sanitizeItemName($arr[1][0]);
        return !empty($this->profName);
    }

    private static function isDoodExist(int $id): bool
    {
        $qwe = qwe("select id from doods where id = :id", ['id'=>$id]);
        return $qwe && $qwe->rowCount();
    }
}