<?php

namespace App\Transfer\Items;

use App\Transfer\TargetArea;

class ItemTargetSection extends TargetArea
{
    public string $type;
    const ignoredTypes = [
        'empty'
    ];

    public function __construct(string $content)
    {
        parent::__construct($content);

        $this->type = match (true){
            str_starts_with($this->content, '<td colspan="2">ID:') => 'top',
            str_contains($this->content,'Цена покупки:') => 'prices',
            str_contains($this->content,'Цена продажи:') => 'prices',
            str_contains($this->content,'Этот предмет не нужен торговцам') => 'prices',
            str_contains($this->content,'Ячейки для гравировки') => 'grav',
            str_contains($this->content,'Скорость планирования:') => 'glider',
            str_contains($this->content,'Можно применить эфенский куб') => 'isEphenCube',
            str_contains($this->content,'Дополнительный эффект') => 'addEffect',
            str_contains($this->content,'Эффекты комплекта') => 'addEffect',
            str_contains($this->content,'Экипировка<br>') => 'addEffect',
            str_contains($this->content,'Рейтинг экипировки:') => 'gs',
            str_contains($this->content,'Требуемый уровень:') => 'needLvl',
            str_contains($this->content,'Уровень предмета:') => 'itemLvl',
            str_contains($this->content,'Защита:') => 'equipStat',
            str_contains($this->content,'<td width="50%">Ловкость</td>') => 'equipStat',
            str_contains($this->content,'Сноровка:') => 'equipStat',
            str_contains($this->content,'Урон:') => 'equipStat',
            str_contains($this->content,'Прочность:') => 'equipStat',
            str_contains($this->content,'временно') => 'equipStat',
            str_contains($this->content,'Эффекты синтеза') => 'synth',
            str_contains($this->content,'С помощью <span class="blue_text">синтеза</span> из этого предмета') => 'synth',
            str_contains($this->content,'<span class="blue_text">Содержимое</span>') => 'contains',
            str_contains($this->content,'Предметы в сундуке:') => 'contains',
            str_contains($this->content,'Открыв набор, вы сможете') => 'contains',
            str_contains($this->content,'вы сможете выбрать один из следующих предметов:') => 'contains',
            str_contains($this->content,'Содержимое:') => 'contains',
            str_contains($this->content,'вы получите следующие предметы:') => 'contains',
            str_contains($this->content,'Использование<br>') => 'usage',
            str_contains($this->content,'Распыляется на:') => 'atomization',
            str_contains($this->content,'<span class="orange_text">Изготовление</span>') => 'craft',
            str_contains($this->content,'<span style="color: #FF9C27">Изготовление</span>') => 'craft',
            str_contains($this->content,'Площадь фундамента:') => 'building',
            str_contains($this->content,'Размер основания:') => 'building',
            str_contains($this->content,'Размещение:') => 'placing',
            str_contains($this->content,'Время роста:') => 'seed',
            str_contains($this->content,'<span class="toggle_text buff_text">Полный текст</span>') => 'bookText',
            str_contains($this->content,'Груз, оставленный на земле, исчезает') => 'pack',



            self::isEmptyTag($this->content) => 'empty',


            default => 'any'
        };
    }

    public function isIgnored(): bool
    {
        return in_array($this->type, self::ignoredTypes);
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
            $section = (object) ['content' => ''];
            foreach ($type as $section){
                $combined .= $section->content;
            }
            $combined .= '</div>';
            $section->content = $combined;
            $sections[] = $section;
        }
        return $sections;
    }

    public function putToDB(int $itemId): bool
    {
        $qwe = qwe("
            replace into itemDescriptions 
                (itemId, sectionTypeId, sectionTypeName, content) 
            VALUES (
                :itemId, (
                     select id 
                     from itemDescrSectionTypes 
                     where name = :sectionTypeName
                ), 
                :sectionTypeName2, 
                :content
            )",
            ['itemId'=>$itemId,
             'sectionTypeName'=>$this->type,
             'sectionTypeName2'=>$this->type,
             'content'=>$this->content]
        );
        return boolval($qwe);
    }



}