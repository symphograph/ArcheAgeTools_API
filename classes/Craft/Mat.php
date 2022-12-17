<?php

namespace Craft;
use Item\Item;
use PDO;

class Mat
{
    public int $id;
    public int|null $craft_id;
    public int|null $result_item_id;
    public int|null $grade;
    public int|float|null $need;
    public Item|null $Item;

    public function __set(string $name, $value): void{}

    public static function byIds(int $itemId, int $craftId) : self|bool
    {

    }

    /**
     * @return array<self>|bool
     */
    public static function getList(int $craft_id) : array|bool
    {
        $qwe = qwe("select *, item_id id, mat_grade grade, mater_need need from craft_materials where craft_id = :craft_id",['craft_id' => $craft_id]);
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        /** @var array<self> $arr */
        $arr = $qwe->fetchAll(PDO::FETCH_CLASS, get_class());
        $List = [];
        foreach ($arr as $mat){
            $mat->initItem();
            $List[] = $mat;
        }

        return $List;
    }

    public function initItem(): void
    {
        $this->Item = Item::byId($this->id);
    }
}