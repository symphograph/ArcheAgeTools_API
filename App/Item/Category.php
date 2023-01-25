<?php

namespace App\Item;

use PDO;
use Symphograph\Bicycle\Helpers;

class Category
{
    public int|null $id;
    public int|null $index;
    public string|null $name;
    public int|null $deep;
    public string|null $description;
    public bool $selectable = true;

    /**
     * @var array<self>|null
     */
    public array|null  $children;
    public int|null    $parent;
    public string|null $icon;

    public function __set(string $name, $value): void{}

    public static function byId(int $id) : self|bool
    {
        $qwe = qwe("select * from item_categories where id = :id",['id'=>$id]);
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

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
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }

        $List = $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
        return Helpers::colAsKey($List, 'id');
    }

    /**
     * @param array<self> $categories
     * @return array<self>
     */
    private static function treeFromList(array $List) {
        $tree = [] ;
        $categories = $List;
        foreach($categories as $id => &$node) {
            if(empty($node->parent)){
                $tree[] = &$node ;
            }else{
                $categories[$node->parent]->children[] = &$node ;
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
        if(!empty($this->icon)){
            $this->icon = 'img:https://' . $_SERVER['SERVER_NAME'] . '/img/icons/80/' . $this->icon;
        }
        if(!empty($this->children)){
            $this->selectable = false;
        }
    }

    /**
     * @param array<self> $List
     * @return array<self>
     */
    private static function initChildren(array $List) : array
    {
        $arr = [];
        foreach ($List as $node) {
            if(!empty($node->children)) {
                $node->children = self::initChildren($node->children);
            }
            $node->initData();
            $arr[] = $node;
        }
        return $arr;
    }

}