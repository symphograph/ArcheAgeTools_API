<?php

namespace Craft;

class CraftPool
{
    public Craft $mainCraft;
    /**
     * @var array<Craft>
     */
    public array $otherCrafts;


    public static function getPool(int $resultItemId): self
    {
        $list = Craft::getList($resultItemId);
        $mainCraft = self::findMainCraft($list);
        $otherCrafts = [];
        foreach ($list as $craft){
            if($craft->id === $mainCraft->id){
                continue;
            }
            $otherCrafts[] = $craft;
        }
        $Pool = new self();
        $Pool->mainCraft = $mainCraft;
        $Pool->otherCrafts = $otherCrafts;
        return $Pool;
    }

    /**
     * @param array<Craft> $Crafts
     * @return false|self
     */
    private static function findMainCraft(array $Crafts): Craft|false
    {
        foreach ($Crafts as $craft){
            if($craft->countData->isUBest){
                return $craft;
            }
            if($craft->countData->isBest){
                return $craft;
            }
        }
        return false;
    }

    private static function initMatPrice(Craft $craft): Craft
    {
        $mats = [];
        foreach ($craft->Mats as $mat){
            if(!$mat->initPrice()){
                printr($mat);
                die();
            }
            $mat->initPrice();
            $mat->Price->initAuthor();
            $mats[] = $mat;
        }
        $craft->Mats = $mats;
        return $craft;
    }

    public function  initMatPrices(): void
    {
        $this->mainCraft = self::initMatPrice($this->mainCraft);
        $crafts = [];
        foreach ($this->otherCrafts as $craft){
            $crafts[] = self::initMatPrice($craft);
        }
        $this->otherCrafts = $crafts;
    }

    public function initMatPools(): void
    {
        $this->mainCraft = self::initMatPool($this->mainCraft);
    }

    private static function initMatPool(Craft $craft): Craft
    {
        $craft->matPool = MatPool::getMatPool($craft->resultItemId);
        return $craft;
    }
}