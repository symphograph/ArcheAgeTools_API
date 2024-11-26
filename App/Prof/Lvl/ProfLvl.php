<?php

namespace App\Prof\Lvl;

use App\Prof\Lvl\Repo\ProfLvlRepo;
use Symphograph\Bicycle\DTO\ModelTrait;
use Symphograph\Bicycle\Errors\AppErr;

class ProfLvl extends ProfLvlDTO
{
    use ModelTrait;

    public string $label;

    public function initLabel(): static
    {
        $min = round($this->min/1000);
        $max = round($this->max/1000);
        $this->label = "{$min}k - {$max}k";
        return $this;
    }

    public static function byNeed(int $val): ProfLvl
    {
        $profLvls = ProfLvlRepo::getList();

        foreach ($profLvls as $profLvl) {
            if ($val >= $profLvl->min && $val <= $profLvl->max) {
                return $profLvl;
            }
        }
        throw new AppErr('Invalid prof value');
    }
}