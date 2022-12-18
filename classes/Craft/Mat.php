<?php

namespace Craft;
use Item\Item;
use PDO;

class Mat
{
    public int  $id;
    public ?int $craftId;
    public ?int $resultItemId;
    public ?int $grade;
    public int|float|null $need;
    public ?Item          $Item;

    public function __set(string $name, $value): void{}

    public static function byIds(int $itemId, int $craftId) : self|bool
    {

    }

    /**
     * @return array<self>|bool
     */
    public static function getList(int $craftId) : array|bool
    {
        $qwe = qwe("
            select *, 
                   itemId as id, 
                   matGrade as grade 
            from craftMaterials 
            where craftId = :craftId",
            ['craftId' => $craftId]
        );
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