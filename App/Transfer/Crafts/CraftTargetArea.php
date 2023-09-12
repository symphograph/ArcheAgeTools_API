<?php

namespace App\Transfer\Crafts;

use App\Transfer\Errors\CraftErr;
use App\Transfer\Items\ItemTargetSection;
use App\Transfer\TargetArea;

class CraftTargetArea extends TargetArea
{


    public TopSection    $topSection;
    public ProfSection   $profSection;
    public ResultSection $resultSection;

    /**
     * @var CraftTargetSection[]|null
     */
    public ?array $sections;

    /**
     * @var MatSection[]
     */
    public array $matList = [];


    public function __construct(string $content)
    {
        parent::__construct($content);
        self::initSections();
        unset($this->sections);
    }

    /**
     * @throws CraftErr
     */
    private function initSections(): void
    {
        $sections = explode('<tr><td><hr class="hr_long"></td></tr>', $this->content);
        $sections = str_replace(
            ['<tr><td>', '</td></tr>', '<tr><td width="100%">'],
            ['<div>', '</div>', '</div>'],
            $sections
        );

        foreach ($sections as $section) {
            $section = new CraftTargetSection($section);
            match ($section->type) {
                'top' => self::initTopSection($section->content),
                'prof' => self::initProfSection($section->content),
                'mats' => $this->matList = $section->matList,
                'result' => self::initResultSection($section->content),
                default => null
            };
        }
        $error = match (true) {
            empty($this->topSection) => 'TopSection does not exist',
            empty($this->profSection) => 'ProfSection does not exist',
            empty($this->matList) => 'MatList does not exist',
            empty($this->resultSection) => 'ResultSection does not exist',
            default => ''
        };
        if(!empty($error)){
            throw new CraftErr($error);
        }
    }

    private function initResultSection(string $content): void
    {
        $this->resultSection = new ResultSection($content);
    }

    private function initTopSection(string $content): void
    {
        $this->topSection = new TopSection($content);
    }

    private function initProfSection(string $content): void
    {
        $this->profSection = new ProfSection($content);
    }

    public function printSections(array $types = []): void
    {
        foreach ($this->sections as $section) {
            if (!empty($types) && !in_array($section->type, $types)) {
                continue;
            }
            echo $section->type . '<br>';
            echo $section->content . '<hr>';
        }
    }

    /**
     * @return CraftTargetSection[]|null
     */
    public function getSectionsByType(string $type): ?array
    {
        return array_filter($this->sections, function ($section, $type) {
            return $section->type === $type;
        });
    }
}