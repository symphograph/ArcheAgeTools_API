<?php

namespace App\Transfer;

class ItemDTO
{
    public int     $id;
    public int     $currencyId   = 0;
    public int     $priceFromNPC = 0;
    public int     $priceToNPC   = 0;
    public bool    $isTradeNPC   = false;
    public ?string $name;
    public ?string $description;
    public bool    $onOff        = true;
    public bool    $personal     = false;
    public bool    $craftable    = false;
    public bool    $isMat        = false;
    public int     $categId      = 1;
    public ?int    $categPId;
    public ?int    $slot;
    public ?int    $rollGroup;
    public ?int    $lvl;
    public int     $basicGrade   = 1;
    public ?int    $forUpGrade;
    public ?string $icon;
    public ?string $iconMd5;

    public static function byDB(int $id): self|false
    {
        $qwe = qwe("select * from items where id = :id",['id' => $id]);
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }


}