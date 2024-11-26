<?php

namespace App\Item;

use App\Craft\Craft\Craft;
use App\Craft\Craft\CraftList;

class Info
{
    public int|null $id;

    /**
     * @var Craft[]
     */
    public ?array $Crafts;

    /**
     * @var Item[]
     */
    public ?array    $CraftResults;
    public ?Craft    $BestCraft;
    public ?Category $Category;

    private function __construct(int $id)
    {
        $this->id = $id;
    }

    public function initResults(): void
    {
        $itemIds = CraftList::getResultItemIds($this->id);
        if (empty($itemIds)) {
            return;
        }
        $Results = ItemList::byIds($itemIds)->getList();
        if (!empty($Results)) {
            $this->CraftResults = $Results;
        }
    }

    public function initCategory(int $categId): void
    {
        $Category = Category::byId($categId);
        if (!$Category) return;
        $this->Category = $Category;
    }

    public static function byId(int $id): self
    {
        $Info = new self($id);
        $Info->initResults();
        return $Info;
    }
}