<?php

namespace App\Item;

use App\DTO\CategoryDTO;
use Symphograph\Bicycle\DTO\ModelTrait;
use Symphograph\Bicycle\Errors\AppErr;
use PDO;
use Symphograph\Bicycle\Helpers;

class Category extends CategoryDTO
{
    use ModelTrait;
    public ?int    $index;
    public bool    $selectable = true;
    /**
     * @var array<self>|null
     */
    public ?array  $children;

    /**
     * @return array<self>
     */
    private static function getList(): array
    {

        $qwe = qwe("
        WITH RECURSIVE cte AS
            (
              SELECT id, name, parent, description, icon, 0 AS deep FROM Categories WHERE parent IS NULL
                AND visible
              UNION ALL
              SELECT c.id, c.name, c.parent, c.description, c.icon, cte.deep+1 FROM Categories c JOIN cte
                ON cte.id=c.parent
            )
            SELECT * FROM cte
            ORDER BY deep"
        );
        if (!$qwe || !$qwe->rowCount()) {
            throw new AppErr('Categories is empty');
        }

        $List = $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
        return Helpers::colAsKey($List, 'id');
    }

    /**
     * @param array<self> $List
     * @return array<self>
     */
    private static function treeFromList(array $List)
    {
        $tree = [];
        $categories = $List;
        foreach ($categories as $id => &$node) {
            if (empty($node->parent)) {
                $tree[] = &$node;
            } else {
                $categories[$node->parent]->children[] = &$node;
            }
        }
        return $tree;
    }

    /**
     * @return array<self>
     */
    public static function getTree(): array
    {
        $List = self::getList();
        $List = self::treeFromList($List);
        return self::initChildren($List);
    }

    private function initData(): void
    {
        if (!empty($this->icon)) {
            $this->icon = 'img:/img/category/' . $this->icon;
        }
        if (!empty($this->children)) {
            $this->selectable = false;
        }
    }

    /**
     * @param array<self> $List
     * @return array<self>
     */
    private static function initChildren(array $List): array
    {
        $arr = [];
        foreach ($List as $node) {
            if (!empty($node->children)) {
                $node->children = self::initChildren($node->children);
            }
            $node->initData();
            $arr[] = $node;
        }
        return $arr;
    }

    /**
     * @return array<self>|false
     */
    public static function byName(string $categoryName): array|false
    {
        $qwe = qwe("
            select * from Categories 
            where name = :name 
            and deep = 3",
            ['name' => $categoryName]
        );
        if (!$qwe || !$qwe->rowCount()) {
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function isPack(int $categId): bool
    {
        return in_array($categId, [122, 133, 171]);
    }
}