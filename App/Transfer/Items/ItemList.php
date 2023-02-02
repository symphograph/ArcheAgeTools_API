<?php

namespace App\Transfer\Items;

use App\Transfer\TransferList;
use PDO;
use Symphograph\Bicycle\DB;

class ItemList extends TransferList
{
    const newItemTable = '`NewItems_8.0.2.7_9.0.1.6`';

    private string $onlyNewQString;

    /**
     * @param int $itemId [optional]
     * <p>Sets first itemId in list</p>
     * <p>If 0 List will be started from last imported item</p>
     * <p>If you want get allList, set to 1</p>
     * @param bool $onlyNew
     */
    public function __construct(
        private readonly int  $itemId = 0,
        private readonly bool $onlyNew = false,
        int                   $limit = 1,
        bool                  $readOnly = true,
        bool                  $random = false
    )
    {

        parent::__construct($limit, $readOnly, $random);
        $newItemTable = self::newItemTable;
        $this->onlyNewQString = $this->onlyNew ? "and id in (select id from $newItemTable)" : '';
    }

    public function transferItems(): bool
    {
        $this->typeOfList = '';
        return self::transferList();
    }

    public function transferErrorItems(array $errorFilter = []): bool
    {
        $this->typeOfList = 'errorList';
        $this->errorFilter = $errorFilter;
        return self::transferList();
    }


    private function transferList(): bool
    {
        $this->random = true;
        if($this->itemId){
            self::resetLast($this->itemId, 'item');
        }
        if(empty($List = self::getList(self::buildSQL()))){
            return false;
        }
        foreach ($List as $itemId){
            if($this->limit > 1){
                usleep(500);
            }
            self::transferItem($itemId);
            echo '<hr>';
        }
        return true;
    }

    public function transferItem(int $itemId): bool
    {

        $PageItem = new PageItem($itemId);
        $PageItem->executeTransfer($this->readOnly);
        echo "<p>ID: $itemId - {$PageItem->ItemDB->name}</p>";
        //$PageItem->TargetArea->printSections([/*'top'*/]);
        if(!$this->readOnly){
            self::putToLog($PageItem);
        }



        //printr($PageItem->ItemDTO);
        //echo $PageItem->ItemDTO->description . '<br>';
        //echo $PageItem->targetArea;
        if(!empty($PageItem->error)){
            echo "<span style='color: red'>$PageItem->error</span><br>";
            //echo $PageItem->targetArea;
            return false;
        }
        return true;
    }

    private function buildSQL(): string
    {
        return match ($this->typeOfList){
            'errorList' => self::errorListSql(),
            default => self::listSQL()
        };
    }

    private function errorListSql(): string
    {
        $errFilterQString =
            !empty($this->errorFilter)
                ? "and status in ('" . implode("','", $this->errorFilter) . "') "
                : '';
        return "
            select id from items 
            where id >= (select id from transfer_Last where lastRec = 'item')
                and !items.isLock
                and id in (
                            select id from transfer_Items 
                            where status != '' 
                              $errFilterQString
                $this->onlyNewQString                
                $this->randQString
            limit :limit";
    }

    private function listSQL(): string
    {
        return "
            select id from items 
            where id >= (select id from transfer_Last where lastRec = 'item')
            and !items.isLock
            $this->onlyNewQString
            $this->randQString
            limit :limit";
    }

    private static function putToLog(PageItem $PageItem): void
    {
        $params = [
            'id' => $PageItem->ItemDB->id,
            'name' => $PageItem->ItemDTO->name ?? $PageItem->ItemDB->name,
            'status' => $PageItem->error,
            'datetime' => date('Y-m-d H:i:s')
        ];
        DB::replace('transfer_Items', $params);
    }
}