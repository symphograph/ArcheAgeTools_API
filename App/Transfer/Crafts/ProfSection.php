<?php

namespace App\Transfer\Crafts;

use App\DTO\DoodDTO;
use App\DTO\ProfDTO;
use App\Transfer\Errors\CraftErr;
use App\Transfer\TargetSection;

class ProfSection extends TargetSection
{
    public int $profId;
    public int $doodId;
    private string $profName;

    public function __construct(string $content)
    {
        parent::__construct($content);
        self::extractDood();
        self::initProfId();
        unset($this->content);
    }


    /**
     * @throws CraftErr
     */
    private function extractDood(): void
    {
        $regExp = '#data-id="doodad--(.+?)data-grade#is';
        if(!preg_match_all($regExp, $this->content, $arr)){
            $this->warnings[] = 'Dood is empty';
            return;
        }
        if (empty($arr[1][0])){
            $this->warnings[] = 'Dood is empty';
            return;
        }
        if(!$this->doodId = self::sanitizeInt($arr[1][0])){
            $this->warnings[] = 'Dood is 0';
            return;
        }
        if(!self::isDoodExist($this->doodId)){
            throw new CraftErr('Dood does not exist in DB: ' . $this->doodId);
        }
    }

    /**
     * @throws CraftErr
     */
    private function initProfId(): void
    {
        if(!self::extractProfName()){
            $this->warnings[] = 'ProfName is empty';
            $this->profName = 'Прочее';
        }

        $Profs = ProfDTO::listByName($this->profName);
        if(empty($Profs)){
            throw new CraftErr('Prof does not exist in DB');
        }

        if (count($Profs) > 1) {
            throw new CraftErr('Prof having variants');
        }
        $this->profId = $Profs[0]->id;
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

    private static function isDoodExist(int $doodId): bool
    {
        return !!DoodDTO::byId($doodId);
    }

}