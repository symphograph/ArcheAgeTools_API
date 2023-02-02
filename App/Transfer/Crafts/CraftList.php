<?php

namespace App\Transfer\Crafts;

use App\Transfer\Items\PageItem;
use App\Transfer\TransferList;
use Symphograph\Bicycle\DB;

class CraftList extends TransferList
{
    public function __construct(
        private readonly int  $craftId = 0,
        int  $limit = 1,
        bool $readOnly = true,
        bool $random = false){

        parent::__construct($limit, $readOnly, $random);
    }


    public function transferErrorCrafts(): bool
    {
        return self::transferList(__FUNCTION__);
    }

    public function transferExistingCrafts(): bool
    {
        return self::transferList(__FUNCTION__);
    }

    public function transferNewCrafts(): bool
    {
        return self::transferList(__FUNCTION__);
    }

    public function transferAllCrafts(): bool
    {
        return self::transferList(__FUNCTION__);
    }



    private function transferList(string $method): bool
    {
        if($this->craftId){
            self::resetLast($this->craftId, 'craft');
        }
        if(empty($List = self::getList(self::buildSQL($method)))){
            return false;
        }
        foreach ($List as $craftId){
            if($this->limit > 1){
                usleep(500);
            }

            self::transferCraft($craftId);
            echo '<hr>';
        }
        return true;
    }

    private function transferCraft(int $craftId): bool
    {
        self::resetLast($craftId, 'craft');
        $PageCraft = new PageCraft($craftId);
        $PageCraft->executeTransfer($this->readOnly);
        echo "<p>ID: $craftId - {$PageCraft->CraftDTO->craftName}</p>";
        //$PageItem->TargetArea->printSections([/*'top'*/]);
        if(!$this->readOnly){
            self::putToLog($PageCraft) or die('Log Error');
        }
        if(!self::isError($PageCraft)){
            return false;
        }


        //printr($PageItem->ItemDTO);
        //echo $PageItem->ItemDTO->description . '<br>';
        //echo $PageItem->targetArea;

        return true;
    }

    private static function isError(PageCraft $PageCraft): bool
    {
        if(!empty($PageCraft->error)){
            echo "<span style='color: red'>$PageCraft->error</span><br>";
            //echo $PageItem->targetArea;
            return false;
        }
        if(!empty($PageCraft->warnings)){
            $warnings = implode(' | ', $PageCraft->warnings);
            echo "<span style='color: orange'>$warnings</span><br>";
        }
        return true;
    }

    private function buildSQL(string $method): string
    {
        return match ($method){
            'transferErrorCrafts' => self::sqlErrorCrafts(),
            'transferNewCrafts' => self::sqlNewCrafts(),
            'transferExistingCrafts' => self::sqlExistingCrafts(),
            'transferAllCrafts' => self::sqlAllCrafts(),
            default => ''
        };
    }

    private function sqlErrorCrafts(): string
    {
        return "
            select id from transfer_Crafts 
            where id >= (select id from transfer_Last where lastRec = 'craft')
            and error !=''
            $this->randQString
                limit :limit";
    }

    private function sqlNewCrafts(): string
    {
        return "
            select id from `NewCrafts_9.0.1.6` 
            where id >= (select id from transfer_Last where lastRec = 'craft')
                and id not in (select id from crafts)
                $this->randQString
                limit :limit";
    }

    private function sqlExistingCrafts(): string
    {
        return "
            select crafts.id from `NewCrafts_9.0.1.6`
            inner join crafts on `NewCrafts_9.0.1.6`.id = crafts.id
                and !crafts.isMyCraft
            where crafts.id >= (select id from transfer_Last where lastRec = 'craft')
            $this->randQString
                limit :limit";
    }

    private function sqlAllCrafts(): string
    {
        return "
            select id from `NewCrafts_9.0.1.6`
            where id >= (select id from transfer_Last where lastRec = 'craft')
            $this->randQString
                limit :limit";
    }

    private static function putToLog(PageCraft $PageCraft): bool
    {
        if(!empty($PageCraft->warnings)){
            $warnings = implode(' | ', $PageCraft->warnings);
        }
        $params = [
            'id' => $PageCraft->CraftDTO->id,
            'craftName' => $PageCraft->CraftDTO->craftName,
            'error' => $PageCraft->error,
            'warnings' => $warnings ?? '',
            'datetime' => date('Y-m-d H:i:s')
        ];
        return DB::replace('transfer_Crafts', $params);
    }
}