<?php

namespace App\Transfer\Crafts;

use App\Transfer\TargetSection;

class CraftTargetSection extends TargetSection
{
    public string $type;
    /**
     * @var array<MatSection>
     */
    public array $matList = [];



    public function __construct(string $content)
    {
        parent::__construct($content);

        $this->type = match (true) {
            str_contains($this->content, '<div>ID:') => 'top',
            str_contains($this->content, 'Ремесло:') => 'prof',
            str_contains($this->content, 'Приспособление:') => 'prof',
            str_contains($this->content, 'Материалы:') => 'mats',
            str_contains($this->content, 'Результат работы:') => 'result',
            self::isEmptyTag($this->content) => 'empty',
            default => 'any'
        };
        if ($this->type === 'mats') {
            self::explodeMatSections();
        }
    }

    private function explodeMatSections(): void
    {
        $matTargetList = explode('<br>', $this->content);
        foreach ($matTargetList as $matSection) {
            if(!str_contains($matSection, 'qtooltip item_grade_')){
                continue;
            }
            $this->matList[] = new MatSection($matSection);
        }
        //printr($this->matList);
    }

}