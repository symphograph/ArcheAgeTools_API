<?php

namespace App\Transfer;

use PDO;
use Symphograph\Bicycle\DB;

class ItemList
{
    const newItemTable = '`NewItems_8.0.2.7_9.0.1.6`';


    /**
     * @param int $limit
     * @param int $itemId [optional]
     * <p>Sets first itemId in list</p>
     * <p>If 0 List will be started from last imported item</p>
     * <p>If you want get allList, set to 1</p>
     * @param bool $readOnly [optional] <p>Set false for saving results to DB</p>
     * @param bool $random
     * @param bool $onlyNew
     */
    public function __construct(
        private readonly int  $limit = 1,
        private readonly int  $itemId = 0,
        private readonly bool $readOnly = true,
        private readonly bool $random = false,
        private readonly bool $onlyNew = false,
        private readonly bool $onlyErrors = false
    )
    {
    }


    public function transferList(): void
    {
        if($this->itemId){
            self::resetLast();
        }
        $List = self::getList();

        /*
        $List = match (true){
            $typeOfList === 'getNewList' => self::getNewList(),
            default => self::getList()
        };
        */
        foreach ($List as $itemId){
            if($this->limit > 1){
                usleep(500);
            }
            if(!self::transferItem($itemId)){
                //break;
            }
            echo '<hr>';
        }
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

    private function getNewList(): array
    {
        $newItemTable = self::newItemTable;
        $rand = $this->random ? 'order by rand()' : '';

        $qwe = qwe("
            select id from $newItemTable 
            where id >= (select id from transfer_Last where lastRec = 'item')
            $rand
            limit :limit",
            ['limit' => $this->limit]
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getList(): array
    {
        $newItemTable = self::newItemTable;
        $rand = $this->random ? 'order by rand()' : '';
        $andOnlyNew = $this->onlyNew ? "and id in (select id from $newItemTable)" : '';

        $andOnlyErrors = $this->onlyErrors ? "            and id in (
                        select id from transfer_Items 
                        where status != '' 
                          and status not in (
                          'ItemPage is empty', 
                          'Item is overdue', 
                          'Item is unnecessary', 
                          'Category is unnecessary'
                          )
                      )" : '';

        /*
        $andOnlyErrors = $this->onlyErrors ? "            and id in (
                        select id from transfer_Items 
                        where datetime > '2023-01-29 13:00:00'
                      )" : '';
        */
        $qwe = qwe("
            select id from items 
            where id >= (select id from transfer_Last where lastRec = 'item')
            $andOnlyErrors
            $andOnlyNew
            $rand
            limit :limit",
        ['limit' => $this->limit]
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN);
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



    private function resetLast(): void
    {
        qwe("update transfer_Last set id = :itemId where lastRec = 'item'", ['itemId' => $this->itemId]);
    }


}