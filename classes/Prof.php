<?php

class Prof
{
    public int         $id;
    public string|null $name;
    public int|null    $lvl;
    public int|null    $save_or;
    public int|null    $save_time;

    public function __set(string $name, $value): void{}

    public static function byNeed(int $id, int $need = 0): self|bool
    {

        $qwe = qwe("select * from profs
            inner join prof_lvls on id = :id
            and :need between prof_lvls.min and prof_lvls.max
            order by lvl desc limit 1
            ",
        ['id'=>$id, 'need'=>$need]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    public static function byLvl(int $id, int $lvl = 1): self|bool
    {
        $qwe = qwe("select * from profs
            inner join prof_lvls on id = :id
            and prof_lvls.lvl = :lvl
            ",
            ['id'=>$id, 'lvl'=>$lvl]
        );
        if(!$qwe || $qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

}