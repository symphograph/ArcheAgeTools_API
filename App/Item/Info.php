<?php

namespace App\Item;

use App\Category;
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
        SELECT any_value(craftMaterials.resultItemId) as resultItemId 
        FROM craftMaterials  
        INNER JOIN items ON items.id = craftMaterials.resultItemId
        AND items.onOff
        AND craftMaterials.itemId = :itemId
        INNER JOIN crafts on craftMaterials.craftId = crafts.id
        AND crafts.onOff
        GROUP BY crafts.resultItemId
        ",
            ['itemId' => $this->id]
        );
        if(!$qwe or !$qwe->rowCount()){
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