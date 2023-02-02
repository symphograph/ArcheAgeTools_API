<?php

namespace App\Transfer;

use App\Item\Item;

class TargetSection extends TargetArea
{
    public string $error = '';
    /**
     * @var array<string>
     */
    public array $warnings = [];
    public function __construct(string $content)
    {
        parent::__construct($content);
    }

    /**
     * @param array<array<self>> $types
     * @return array<self>
     */
    public static function combine(array $types): array
    {
        $sections = [];
        foreach ($types as $type){
            if(empty($type)) continue;
            $combined = '<div>';

            foreach ($type as $section){
                $combined .= $section->content;
            }
            $combined .= '</div>';
            $section->content = $combined;
            $sections[] = $section;
        }
        return $sections;
    }

    protected static function isItemExist(int $id): bool
    {
        $qwe = qwe("select id from items where id = :id", ['id' => $id]);
        return $qwe && $qwe->rowCount();
    }
}