<?php

namespace User;

use PDO;

class ProfLvls
{
    public int $lvl;
    public int $min;
    public int $max;
    public int $laborBonus;
    public int $timeBonus;
    public string $label;

    /**
     * @return array<self>|bool
     */
    public static function getList(): array|bool
    {
        $qwe = qwe("select *,
           concat(round(min/1000),'k - ',round(max/1000),'k') as label
            from profLvls"
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }
}