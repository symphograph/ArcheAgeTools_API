<?php

namespace App\Packs;

class Freshness
{
    public int    $id       = 0;
    public string $name     = '';
    public ?int   $condType;
    public ?int   $bestLvl;
    public ?int   $worstLvl;
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
        $freshness->initFreshLvls();
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

    private function initBestLvl(): void
    {
        //printr($this->Percents);
        $bestPercent = max($this->Percents);
        $this->bestLvl = array_search($bestPercent, $this->Percents);
    }

    private function initWorstLvl(): void
    {
        $this->worstLvl = array_search(min($this->Percents), $this->Percents);
    }

    public function initFreshLvls(): void
    {
        foreach ($this->Percents as $lvl => $percent){
            $this->FreshLvls[] = FreshLvl::byConstruct($this->condType, $lvl, $percent);
        }
        self::initBestLvl();
        self::initWorstLvl();
    }

}