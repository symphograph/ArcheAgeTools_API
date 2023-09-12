<?php

namespace App\Transfer\Crafts;

use App\DTO\CraftDTO;
use App\Transfer\Errors\TransferErr;
use App\Transfer\TransferList;

class TransferCrafts extends TransferList
{
    protected string $subject = 'craft';
    public function __construct(
        int  $startId = 0,
        int  $limit = 1,
        bool $readOnly = true,
        bool $random = false
    ){

        parent::__construct($startId, $limit, $readOnly, $random);
    }


    public function transferErrorCrafts(): void
    {
        if(empty($errorFilter)){
            $List = CraftList::errors($this->startId, $this->orderBy, $this->limit);
        }else{
            $List = CraftList::errorsFiltered($errorFilter, $this->startId, $this->orderBy, $this->limit);
        }

        self::transferList($List);
    }

    public function transferExistingCrafts(): void
    {
        $List = CraftDTO::getIdList($this->startId, $this->orderBy, $this->limit);
        self::transferList($List);
    }

    public function transferNewCrafts(): void
    {
        $List = NewCraft::getIdList($this->startId, $this->orderBy, $this->limit);
        self::transferList($List);
    }

    protected function transferList(array $List): void
    {
        foreach ($List as $id){
            if($this->limit > 1){
                usleep(500);
            }
            self::transfer($id);
        }
    }


    private static function getCraftDTO(int $craftId): CraftDTO
    {
        if($CraftDTO = CraftDTO::byId($craftId)){
            return $CraftDTO;
        }
        $NewCraft = NewCraft::byId($craftId);
        $CraftDTO = new CraftDTO();
        $CraftDTO->bindSelf($NewCraft);
        return $CraftDTO;
    }

    private function transfer(int $id): void
    {
        self::resetLast($id);
        $objectDTO = self::getCraftDTO($id);
        $Page = new PageCraft($objectDTO, $this->readOnly);

        echo "<p>ID: $id - {$Page->CraftDTO->craftName}</p>";
        $errMsg = '';
        try{
            $Page->executeTransfer();
        } catch (TransferErr $err){
            $errMsg = $err->getMessage();
            echo "<span style='color: red'>$errMsg</span><br>";
        }

        //$PageItem->TargetArea->printSections([/*'top'*/]);
        $TransLog = CraftTransLog::create(
            $objectDTO->id,
            $objectDTO->craftName,
            $errMsg,
            $Page->warnings
        );
        $TransLog->putToDB();

        if(!empty($TransLog->warnings)){
            echo "<span style='color: orange'>$TransLog->warnings</span><br>";
        }
        echo '<hr>';
    }

}