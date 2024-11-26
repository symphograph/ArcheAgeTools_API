<?php

namespace App\Mat;

use App\Craft\CraftPool;
use App\Mat\Repo\RepoDB;
use PDO;

class MatPool
{
    /**
     * @var array<Mat>
     */
    public array $matPool = [];
    public array $trashPool = [];

    public static function getMatPool(int $resultItemId): self
    {
        $pool = new self();
        $pool->initMatPool($resultItemId);
        $pool->initTrashPool($resultItemId);
        return $pool;
    }

    private function initTrashPool(int $resultItemId, float $parentNeed = 1): void
    {
        $craft = CraftPool::getPool($resultItemId)->mainCraft;

        foreach ($craft->Mats as $mat){

            if($mat->initPrice()){
                $mat->Price->initAuthor();
            }
            //printr($mat->Item->name);

            if($mat->need < 0){
                if(!empty($this->trashPool[$mat->id])){
                    $mat->need += $mat->need * $parentNeed / $craft->resultAmount;
                }else{
                    $mat->need = $mat->need * $parentNeed / $craft->resultAmount;
                }
                $this->trashPool[$mat->id] = $mat;
                continue;
            }

            if($mat->craftable && !$mat->isBuyOnly){
                self::initTrashPool($mat->id, $mat->need * $parentNeed / $craft->resultAmount);
            }

        }
    }

    private function initMatPool(int $resultItemId, float $parentNeed = 1): void
    {
        $craft = CraftPool::getPool($resultItemId)->mainCraft;
        $solidIds = RepoDB::getSolidIds();

        foreach ($craft->Mats as $mat){
            if($mat->need < 0){
                continue;
            }

            $mat->initPrice();
            $mat->Price->initAuthor();

            if($mat->craftable && !$mat->isBuyOnly && !in_array($mat->Item->categId, $solidIds)){
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

}