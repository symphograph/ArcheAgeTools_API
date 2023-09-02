<?php

namespace App\DTO;

use Symphograph\Bicycle\DB;

class ItemDTO extends DTO
{
    const tableName = 'items';
    public int     $id;
    public int     $currencyId;
    public int     $priceFromNPC;
    public int     $priceToNPC;
    public bool    $isTradeNPC = false;
    public ?string $name;
    public ?string $description;
    public bool    $onOff      = true;
    public bool    $personal   = false;
    public bool    $craftable  = false;
    public bool    $isMat      = false;
    public ?int    $categId    = 1;
    public ?int    $slot;
    public int     $rollGroup;
    public int     $equipLvl;
    public int     $basicGrade = 1;
    public ?int    $forUpGrade;
    public ?string $icon;
    public ?string $iconMD5;
    public int     $lvl;
    public bool    $isGradable = false;
    public ?string $expiresAt;
    public bool    $isLock     = false;

    public static function byId(int $id): self|bool
    {
        $qwe = qwe("select * from items where id = :id", ['id' => $id]);
        return $qwe->fetchObject(self::class);
    }

    public function putToDB(): void
    {
        $params = DB::initParams($this);
        DB::replace(self::tableName, $params);
    }
}