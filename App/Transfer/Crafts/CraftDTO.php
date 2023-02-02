<?php

namespace App\Transfer\Crafts;

use Symphograph\Bicycle\DB;

class CraftDTO
{
    public int    $id;
    public string $craftName;
    public int    $profNeed;
    public int    $resultAmount;

    public ?int  $doodId;
    public ?int  $resultItemId;
    public ?int  $laborNeed;
    public ?int  $profId;
    public ?bool $onOff;
    public ?bool $isBottom;
    public ?int  $deep;
    public ?bool $isMyCraft;
    public ?int  $craftTime;
    public ?int  $grade;
    public ?int  $mins;
    public ?int  $spm;

    public static function byDB(int $craftId): self|bool
    {
        $qwe = qwe("
            select NewCr.*,
                doodId,
                resultItemId,
                laborNeed,
                profId,
                onOff,
                isBottom,
                deep,
                isMyCraft,
                craftTime,
                grade,
                mins,
                spm
            from `NewCrafts_9.0.1.6` NewCr
            left join crafts
                on NewCr.id = crafts.id
            where NewCr.id = :craftId",
            ['craftId' => $craftId]
        );
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    public function putToDB(): bool
    {
        $params = [];
        foreach ($this as $k => $v){
            if($v === null) continue;
            $v = is_bool($this->$k) ? intval($v) : $v;
            $params[$k] = $v;
        }
        return match (true){
            empty($this->resultItemId),
            empty($this->resultAmount) => false,
            default => DB::replace('crafts', $params)
        };
    }
}