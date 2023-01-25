<?php

namespace App\Craft;

use PDO;

class MatPool
{
    /**
     * @var array<Mat>
     */
    public array $matPool = [];
    private array $solidGroup = [];
    private const solidGroupId = 1023;

    /**
     * @param int $resultItemId
     * @return array<Mat>
     */
    public static function getMatPool(int $resultItemId): array
    {
        $pool = new self();
        $pool->initSolidGroup();
        $pool->initMatPool($resultItemId);
        return $pool->matPool;
    }

    private function initMatPool(int $resultItemId, float $parentNeed = 1): void
    {
        $craft = CraftPool::getPool($resultItemId)->mainCraft;

        foreach ($craft->Mats as $mat){
            if($mat->need < 0)
                continue;

            $mat->initPrice();
            $mat->Price->initAuthor();

            if($mat->craftable && !$mat->isBuyOnly && !in_array($mat->Item->categId, $this->solidGroup)){
                self::initMatPool($mat->id, $mat->need * $parentNeed / $craft->resultAmount);
                continue;
            }
            if(!empty($this->matPool[$mat->id])){
                $mat->need += $mat->need * $parentNeed / $craft->resultAmount;
            }else{
                $mat->need = $mat->need * $parentNeed / $craft->resultAmount;
            }
            $this->matPool[$mat->id] = $mat;

        }
    }

    private function initMatPrices(): void
    {
        $mats = [];
        foreach ($this->matPool as $mat){
            $mat->initPrice();
            $mat->Price->initAuthor();
            $mats[] = $mat;
        }
        $this->matPool = $mats;
    }

    private function initSolidGroup(): void
    {
        $qwe = qwe("
            select id from Categories 
            where parent = :parent",
            ['parent'=> self::solidGroupId]
        );
        $this->solidGroup = $qwe->fetchAll(PDO::FETCH_COLUMN);
    }
}