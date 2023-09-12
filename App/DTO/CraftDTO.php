<?php

namespace App\DTO;

use App\DTO\DTO;
use Symphograph\Bicycle\DTO\DTOTrait;

class CraftDTO extends DTO
{
    use DTOTrait;
    const tableName = 'crafts';

    public int       $id;
    public ?string   $craftName;
    public ?int      $doodId;
    public int       $resultItemId;
    public int       $laborNeed;
    public int       $profId       = 25;
    public int       $profNeed;
    public int|float $resultAmount = 1;
    public bool      $onOff;
    public bool      $isBottom;
    public int       $deep;
    public bool      $isMyCraft;
    public int       $craftTime;
    public ?int      $grade;
    public int       $mins;
    public int       $spm;


}