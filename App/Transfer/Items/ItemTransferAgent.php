<?php

namespace App\Transfer\Items;

use App\Item\ItemDTO;
use App\Transfer\Errors\ItemErr;
use App\Transfer\Errors\TransferErr;
use App\Transfer\TransferAgent;
use App\Transfer\TransferStatus;
use App\Transfer\TransParams;

class ItemTransferAgent extends TransferAgent
{
    public function __construct(TransParams $params)
    {
        parent::__construct($params);
    }

    protected function getLast(): int
    {
        $lastLog = ItemTransLog::last();
        return $lastLog->id ?? 1;
    }

    public function transferExistingItems(): void
    {
        $params = $this->params;
        $list = \App\Item\ItemList::all($params->orderBy, $params->limit, $params->startId);
        $ids = $list->getIds();

        $this->transferList($ids);
    }

    public function transferEmptyIcons(): void
    {
        $params = $this->params;
        $list = \App\Item\ItemList::emptyIcons($params->orderBy, $params->limit, $params->startId);
        $ids = $list->getIds();

        $this->transferList($ids);
    }

    public function transferNewItems(): void
    {
        $params = $this->params;
        $NewItemList = NewItemList::all($params->startId, $params->orderBy, $params->limit);
        $ids = $NewItemList->getIds();
        $this->transferList($ids);
    }

    public function transferErrorItems(array $errorFilter = []): void
    {
        $params = $this->params;
        $list = ItemTransLogList::byErrors($errorFilter, $params->startId, $params->orderBy, $params->limit);
        $ids = $list->getIds();
        $this->transferList($ids);
    }

    protected function transferList(array $ids): void
    {
        foreach ($ids as $id){
            if($this->params->limit > 1){
                usleep(1000);
            }
            $this->transfer($id);
        }
    }

    private static function getItemDTO(int $itemId): ItemDTO
    {
        if($ItemDTO = ItemDTO::byId($itemId)){
            return $ItemDTO;
        }

        $NewItem = NewItem::byId($itemId);
        $ItemDTO = new ItemDTO();
        $ItemDTO->bindSelf($NewItem);
        return $ItemDTO;
    }

    private function transfer(int $id): void
    {
        $objectDTO = self::getItemDTO($id);
        $TransLog = ItemTransLog::newInstance(
            $objectDTO->id,
            $objectDTO->name
        );
        $TransLog->putToDB();


        $Page = new PageItem($objectDTO, $this->params->readOnly);
        echo "<p>ID: $id - {$Page->ItemDTO->name}</p>";

        try{
            if($objectDTO->isLock) throw new ItemErr('Item is locked');
            $Page->executeTransfer();
        } catch (TransferErr $err){
            $errMsg = $err->getMessage();
            $TransLog->setError($errMsg);
            $this->printError($errMsg);
        }

        if(!empty($Page->warnings)){
            $TransLog->initWarnings($Page->warnings);
            echo "<span style='color: orange'>$TransLog->warnings</span><br>";
        }
        if(empty($TransLog->error)){
            $TransLog->setStatus(TransferStatus::Completed);
        }
        $TransLog->putToDB();
        echo '<hr>';
    }

    private function printError(string $errMsg): void
    {
        echo "<span style='color: red'>$errMsg</span><br>";
    }
}