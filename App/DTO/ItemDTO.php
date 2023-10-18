<?php

namespace App\DTO;

use Symphograph\Bicycle\DTO\DTOTrait;

class ItemDTO
{
    use DTOTrait;
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

}