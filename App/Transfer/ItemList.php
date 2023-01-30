<?php

namespace App\Transfer;

use PDO;
use Symphograph\Bicycle\DB;

class ItemList
{
    const newItemTable = '`NewItems_8.0.2.7_9.0.1.6`';

    private string $randQString;
    private string $typeOfList = 'getList';
    private array  $errorFilter = [];
    private string $onlyNewQString;

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
        private readonly bool $onlyNew = false
    )
    {
        $this->randQString = $this->random ? 'order by rand()' : '';
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
        if($this->itemId){
            self::resetLast();
        }
        if(empty($List = self::getList())){
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

    private function getList(): array
    {
        $qwe = qwe(self::buildSQL(), ['limit' => $this->limit]
        );
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN);
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
                and id in (
                            select id from transfer_Items 
                            where status != '' 
                              {$errFilterQString}
                {$this->onlyNewQString}                
                {$this->randQString}
            limit :limit";
    }

    private function listSQL(): string
    {
        return "
            select id from items 
            where id >= (select id from transfer_Last where lastRec = 'item')
                {$this->onlyNewQString}
                {$this->randQString}
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

    private function resetLast(): void
    {
        qwe("update transfer_Last set id = :itemId where lastRec = 'item'", ['itemId' => $this->itemId]);
    }


}