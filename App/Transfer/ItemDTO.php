<?php

namespace App\Transfer;

use Symphograph\Bicycle\DB;

class ItemDTO
{
    public int     $id           = 0;
    public int     $currencyId   = 0;
    public int     $priceFromNPC = 0;
    public int     $priceToNPC   = 0;
    public bool    $isTradeNPC   = false;
    public ?string $name;
    public ?string $description;
    public ?bool   $onOff;
    public bool    $personal     = false;
    public ?bool   $craftable;
    public ?bool   $isMat;
    public int     $categId      = 1;
    public ?int    $slot;
    public ?int    $rollGroup;
    public ?int    $equipLvl;
    public int     $lvl          = 0;
    public int     $basicGrade   = 1;
    public bool $isGradable = false;
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

    public function initByObject(self $ItemDTO): void
    {
        foreach ($ItemDTO as $k => $v){
            $this->$k = $v;
        }
    }

    public function update(self|NewItem $ItemDTO): bool
    {
        self::initByObject($ItemDTO);
        return self::putToDB();
    }

    private function putToDB(): bool
    {
        $params = [
            'id'           => $this->id,
            'currencyId'   => $this->currencyId,
            'priceFromNPC' => $this->priceFromNPC,
            'priceToNPC'   => $this->priceToNPC,
            'isTradeNPC'   => intval($this->isTradeNPC),
            'name'         => $this->name,
            'description'  => $this->description,
            'onOff'        => intval($this->onOff),
            'personal'     => intval($this->personal),
            'craftable'    => intval($this->craftable),
            'isMat'        => intval($this->isMat),
            'categId'      => $this->categId,
            'slot'         => $this->slot,
            'rollGroup'    => $this->rollGroup,
            'equipLvl'     => $this->equipLvl,
            'basicGrade'   => $this->basicGrade,
            'isGradable'   => intval($this->isGradable),
            'forUpGrade'   => $this->forUpGrade,
            'icon'         => $this->icon,
            'iconMd5'      => $this->iconMd5,
            'lvl'          => $this->lvl
        ];
        return DB::replace('items', $params);
    }


}