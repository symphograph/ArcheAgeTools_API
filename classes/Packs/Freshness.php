<?php

namespace Packs;

class Freshness
{
    public int    $id       = 0;
    public string $name     = '';
    public ?int   $condType;
    public string $implodedPercents;
    public array  $Percents = [];
    /**
     * @var array<FreshLvl>|null
     */
    public ?array $FreshLvls;



    public static function byId(int $freshId): self|false
    {
        $qwe = qwe("
            select * from freshTypes 
            where id = :freshId",
            ['freshId'=>$freshId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }

        $freshness = $qwe->fetchObject(self::class);
        if(!$freshness->initPercents()){
            return false;
        }
        return $freshness;
    }

    private function initPercents(): bool
    {
        $arr = explode('|',$this->implodedPercents);
        if(empty($arr)){
            return false;
        }

        foreach ($arr as $lvl => $v){
            $this->Percents[$lvl] = $v;
        }
        return !empty($this->Percents);
    }

    public function initFreshLvls(): void
    {
        $this->FreshLvls[] = self::getLvlByPercent(max($this->Percents));
        $this->FreshLvls[] = self::getLvlByPercent(min($this->Percents));
    }

    private function getLvlByPercent(int $percent): FreshLvl
    {
        $lvl = array_search($percent, $this->Percents);
        return new FreshLvl($this->condType, $lvl, $percent);
    }

}