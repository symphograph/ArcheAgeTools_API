<?php

namespace App\Transfer\Crafts;

use App\Item\Item;
use App\Transfer\Items\GradeArea;
use App\Transfer\Items\ItemTargetArea;
use App\Transfer\Page;

class PageCraft extends Page
{
    public CraftDTO|false $CraftDTO;
    public CraftTargetArea $TargetArea;
    private bool $readOnly = true;

    public function __construct(public readonly int $craftId)
    {
        self::saveLast($craftId, 'craft');
    }

    public function executeTransfer(bool $readOnly = true): bool
    {
        $this->readOnly = $readOnly;
        if(!$this->CraftDTO = CraftDTO::byDB($this->craftId)){
            $this->error = 'Item does not exist in List';
            return false;
        }

        if(!self::parseData()){
            return false;
        }
        self::initCraftDTO();
        if(empty($this->error) && !$this->readOnly){
            self::updateDB();
        }
        return true;
    }

    private function parseData(): bool
    {
        return match (false){
            self::initContent() => false,
            self::initTargetArea() => false,
            self::checkErrors() => false,
            default => true
        };
    }

    private function initContent(): string|false
    {
        $url = self::site . '/ru/recipe/' . $this->craftId . '/';
        return self::getContent($url);
    }

    private function initTargetArea(): bool
    {
        preg_match_all('#<div class="insider"><table class="itemwhite_table">(.+?)</table></div>#is', $this->content, $arr);
        if(empty($arr[0][0])){
            $this->error = 'CraftPage is empty';
            return false;
        }
        $this->TargetArea = new CraftTargetArea($arr[0][0]);
        unset($this->TargetArea->content);
        //printr($this->TargetArea);
        return true;
    }

    private function checkErrors(): bool
    {
        $this->error = match (false){
            empty($this->TargetArea->error) => $this->TargetArea->error,
            empty($this->TargetArea->topSection->error) => $this->TargetArea->topSection->error,
            empty($this->TargetArea->profSection->error) => $this->TargetArea->profSection->error,
            empty($this->TargetArea->resultSection->error) => $this->TargetArea->resultSection->error,
            self::checkMatList() => $this->error,
            default => ''
        };
        if(!empty($this->error)){
            return false;
        }
        $this->warnings = array_merge(
            $this->TargetArea->topSection->warnings,
            $this->TargetArea->profSection->warnings,
            $this->TargetArea->resultSection->warnings
        );
        return true;
    }

    private function checkMatList(): bool
    {
        if(empty($this->TargetArea->matList)){
            $this->error = 'Mats is empty';
            return false;
        }
        foreach ($this->TargetArea->matList as $mat){
            if(!empty($mat->error)){
                $this->error =  $mat->error;
                return false;
            }
        }
        return true;
    }

    private function initCraftDTO(): void
    {
        $this->CraftDTO->craftTime = $this->TargetArea->topSection->craftTime ?? null;
        $this->CraftDTO->laborNeed = $this->TargetArea->topSection->laborNeed ?? null;
        $this->CraftDTO->profId = $this->TargetArea->profSection->profId ?? null;
        $this->CraftDTO->doodId = $this->TargetArea->profSection->doodId ?? null;
        $this->CraftDTO->resultItemId = $this->TargetArea->resultSection->resultItemId ?? null;
        $this->CraftDTO->resultAmount = $this->TargetArea->resultSection->resultAmount ?? null;
    }

    private function saveMatList(): bool
    {
        self::deleteMats();
        foreach ($this->TargetArea->matList as $mat){
            if(!$mat->putToDB($this->craftId)){
                self::deleteMats();
                return false;
            }
        }
        return true;
    }

    private function updateDB(): bool
    {

        $this->error = match (false){
            $this->CraftDTO->putToDB() => 'CraftDB error',
            self::saveMatList() => 'MatsDB error',
            default => ''
        };
        return empty($this->error);
    }

    private function deleteMats()
    {
        qwe("delete from craftMaterials where craftId = :craftId and itemId !=500", ['craftId'=> $this->craftId]);
    }
}