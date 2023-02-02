<?php

namespace App\Transfer\Crafts;

use App\Transfer\Items\ItemTargetSection;
use App\Transfer\TargetArea;

class CraftTargetArea extends TargetArea
{

    /**
     * @var array<CraftTargetSection>|null
     */
    public ?array $sections;
    public TopSection $topSection;
    public ProfSection $profSection;
    public ResultSection $resultSection;
    /**
     * @var array<MatSection>
     */
    public array $matList = [];


    public function __construct(string $content)
    {
        parent::__construct($content);
        self::initSections();
        unset($this->sections);
    }

    private function initSections()
    {
        $sections = explode('<tr><td><hr class="hr_long"></td></tr>', $this->content);
        $sections = str_replace(
            ['<tr><td>', '</td></tr>', '<tr><td width="100%">'],
            ['<div>', '</div>', '</div>'],
            $sections
        );

        foreach ($sections as $section){
            $section = new CraftTargetSection($section);
            match ($section->type){
                'top' => self::initTopSection($section->content),
                'prof' => self::initProfSection($section->content),
                'mats' => $this->matList = $section->matList,
                'result' => self::initResultSection($section->content),
                default => null
            };
        }
        $this->error = match (true){
            empty($this->topSection) => 'TopSection is not exist',
            empty($this->profSection) => 'ProfSection is not exist',
            empty($this->matList) => 'MatList is not exist',
            empty($this->resultSection) => 'ResultSection is not exist',
            default => ''
        };
    }

    private function initResultSection(string $content)
    {
        $this->resultSection = new ResultSection($content);
    }

    private function initTopSection(string $content)
    {
        $this->topSection = new TopSection($content);
    }

    private function initProfSection(string $content)
    {
        $this->profSection = new ProfSection($content);
    }

    public function printSections(array $types = [])
    {
        foreach ($this->sections as $section){
            if(!empty($types) && !in_array($section->type, $types)){
                continue;
            }
            echo $section->type . '<br>';
            echo $section->content . '<hr>';
        }
    }

    /**
     * @return array<CraftTargetSection>|null
     */
    public function getSectionsByType(string $type): ?array
    {
        return array_filter($this->sections,function($section, $type){
            return $section->type === $type;
        });
    }
}