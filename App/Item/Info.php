<?php

namespace App\Item;

use App\Item\Category;
use App\Craft\Craft;
use PDO;

class Info
{
    public int|null $id;
    /**
     * @var array<Craft>|null
     */
    public array|null $Crafts;
    /**
     * @var array<Item>|null
     */
    public array|null $CraftResults;
    public Craft|null $BestCraft;
    public Category|null $Category;

    private function __construct(int $id)
    {
        $this->id = $id;
    }

    private function getResults() : array
    {
        $qwe = qwe("
        select any_value(crafts.resultItemId) as resultItemId 
        from craftMaterials  
        inner join crafts 
            on craftMaterials.craftId = crafts.id
            and crafts.onOff
        inner join items 
            on items.id = crafts.resultItemId
            and items.onOff
            and craftMaterials.itemId = :itemId
        group by crafts.resultItemId
        ",
            ['itemId' => $this->id]
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN,0);

    }

    public function initResults(): void
    {
        $itemIds = self::getResults();
        if(empty($itemIds)){
            return;
        }
        $Results = Item::searchList($itemIds);
        if(!empty($Results)){
            $this->CraftResults = $Results;
        }
    }

    public function initCategory(int $categId): void
    {
        $Category = Category::byId($categId);
        if(!$Category) return;
        $this->Category = $Category;
    }

    public function initCrafts()
    {

    }

    public static function byId(int $id) :  self
    {
        $Info = new self($id);
        $Info->initResults();
        return $Info;
    }
}