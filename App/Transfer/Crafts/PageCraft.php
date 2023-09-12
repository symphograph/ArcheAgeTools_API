<?php

namespace App\Transfer\Crafts;

use App\DTO\CraftDTO;
use App\DTO\MatDTO;
use App\Transfer\Errors\CraftErr;
use App\Transfer\Errors\TransferErr;
use App\Transfer\Page;

class PageCraft extends Page
{
    public CraftTargetArea $TargetArea;

    public function __construct(public CraftDTO $CraftDTO, bool $readOnly)
    {
        $this->readOnly = $readOnly;
        self::saveLast($CraftDTO->id, 'craft');
    }

    /**
     * @throws TransferErr
     */
    public function executeTransfer(): void
    {
        self::initContent();
        self::initTargetArea();
        self::checkErrors();
        self::initCraftDTO();
        self::updateDB();
    }

    /**
     * @throws TransferErr
     */
    private function initContent(): void
    {
        $url = self::site . '/ru/recipe/' . $this->CraftDTO->id . '/';
        self::getContent($url);
    }

    private function initTargetArea(): void
    {
        preg_match_all('#<div class="insider"><table class="itemwhite_table">(.+?)</table></div>#is', $this->content, $arr);
        if(empty($arr[0][0])){
            throw new CraftErr('CraftPage is empty');
        }
        $this->TargetArea = new CraftTargetArea($arr[0][0]);
        unset($this->TargetArea->content);
    }

    private function checkErrors(): void
    {
        /*
        $error = match (false){
            empty($this->TargetArea->error) => $this->TargetArea->error,
            empty($this->TargetArea->topSection->error) => $this->TargetArea->topSection->error,
            empty($this->TargetArea->profSection->error) => $this->TargetArea->profSection->error,
            empty($this->TargetArea->resultSection->error) => $this->TargetArea->resultSection->error,
            self::checkMatList() => $this->error,
            default => ''
        };
        if(!empty($error)){
            throw new CraftErr($error);
        }
        */
        $this->warnings = array_merge(
            $this->TargetArea->topSection->warnings,
            $this->TargetArea->profSection->warnings,
            $this->TargetArea->resultSection->warnings
        );
    }

    private function checkMatList(): bool
    {
        if(empty($this->TargetArea->matList)){
            throw new CraftErr('Mats is empty');
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
        $this->CraftDTO->craftTime = $this->TargetArea->topSection->craftTime ?? 0;
        $this->CraftDTO->laborNeed = $this->TargetArea->topSection->laborNeed ?? 0;
        $this->CraftDTO->profId = $this->TargetArea->profSection->profId ?? 25;
        $this->CraftDTO->doodId = $this->TargetArea->profSection->doodId ?? null;
        $this->CraftDTO->resultItemId = $this->TargetArea->resultSection->resultItemId;
        $this->CraftDTO->resultAmount = $this->TargetArea->resultSection->resultAmount ?? 1;
    }

    private function updateDB(): void
    {
        if($this->readOnly) return;
        qwe("START TRANSACTION");
        $this->CraftDTO->putToDB();
        self::saveMatList();
        qwe("COMMIT");
    }

    private function saveMatList(): void
    {
        MatDTO::delAllFromCraft($this->CraftDTO->id);
        foreach ($this->TargetArea->matList as $mat){
            $mat->putToDB($this->CraftDTO->id);
        }
    }
}