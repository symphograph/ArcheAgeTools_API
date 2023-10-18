<?php

namespace App\Transfer\Items;

use App\DTO\ItemDTO;
use App\Transfer\Errors\TransferErr;
use App\Transfer\TransferList;
use Symphograph\Bicycle\PDO\DB;

class TransferItems extends TransferList
{
    protected string $subject = 'item';

    /**
     * @param int $startId [optional]
     * <p>Sets first itemId in list</p>
     * <p>If 0 List will be started from last imported item</p>
     * <p>If you want get allList, set to 1</p>
     * @param int $limit
     * @param bool $readOnly
     * @param bool $random
     */
    public function __construct(
        int  $startId = 0,
        int  $limit = 1,
        bool $readOnly = true,
        bool $random = false,
    )
    {
        parent::__construct($startId, $limit, $readOnly, $random);
    }

    public function transferExistingItems(): void
    {
        $List = ItemDTO::getIdList($this->startId, $this->orderBy, $this->limit);
        self::transferList($List);
    }

    public function transferNewItems(): void
    {
        $List = NewItem::getIdList($this->startId, $this->orderBy, $this->limit);
        self::transferList($List);
    }

    public function transferErrorItems(array $errorFilter = []): void
    {
        if(empty($errorFilter)){
            $List = ItemList::errors($this->startId, $this->orderBy, $this->limit);
        }else{
            $List = ItemList::errorsFiltered($errorFilter, $this->startId, $this->orderBy, $this->limit);
        }

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
        self::resetLast($id);
        $objectDTO = self::getItemDTO($id);
        $Page = new PageItem($objectDTO, $this->readOnly);
        echo "<p>ID: $id - {$Page->ItemDTO->name}</p>";
        $errMsg = '';
        try{
            $Page->executeTransfer();
        } catch (TransferErr $err){
            $errMsg = $err->getMessage();
            echo "<span style='color: red'>$errMsg</span><br>";
        }
        $TransLog = ItemTransLog::create(
            $objectDTO->id,
            $objectDTO->name,
            $errMsg,
            $Page->warnings
        );
        $TransLog->putToDB();

        if(!empty($TransLog->warnings)){
            echo "<span style='color: orange'>$TransLog->warnings</span><br>";
        }
        echo '<hr>';
    }

    private static function putToLog(PageItem $PageItem): void
    {
        $params = [
            'id' => $PageItem->ItemDTO->id,
            'name' => $PageItem->ItemDTO->name,
            'status' => $PageItem->error,
            'datetime' => date('Y-m-d H:i:s')
        ];
        DB::replace('transfer_Items', $params);
    }
}