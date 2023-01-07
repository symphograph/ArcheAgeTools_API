<?php

namespace Craft;

class MatPool
{
    /**
     * @var array<Mat>
     */
    public array $matPool = [];

    /**
     * @param int $resultItemId
     * @return array<Mat>
     */
    public static function getMatPool(int $resultItemId): array
    {
        $pool = new self();
        $pool->initMatPool($resultItemId);
        $pool->initMatPrices();
        return $pool->matPool;
    }

    private function initMatPool(int $resultItemId): void
    {
        $craft = CraftPool::getPool($resultItemId)->mainCraft;
        foreach ($craft->Mats as $mat){
            if(empty($this->matPool[$mat->id])){
                $this->matPool[$mat->id] = $mat;
            }
            if($mat->craftable){
                self::initMatPool($mat->id);
            }
            $this->matPool[$mat->id]->need += $mat->need;
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
}