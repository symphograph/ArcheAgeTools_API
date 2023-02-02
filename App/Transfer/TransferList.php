<?php

namespace App\Transfer;

use PDO;

class TransferList
{
    protected string $randQString;
    protected string $typeOfList = 'getList';
    protected array  $errorFilter = [];

    /**
     * @param int $limit
     * @param bool $readOnly [optional] <p>Set false for saving results to DB</p>
     * @param bool $random
     */
    public function __construct
    (
        protected int  $limit = 1,
        protected bool $readOnly = true,
        protected bool $random = false
    )
    {

        $this->randQString = $this->random ? 'order by rand()' : '';
    }

    protected function getList(string $sql): array
    {
        $qwe = qwe($sql, ['limit' => $this->limit]);
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_COLUMN);
    }

    protected static function resetLast(int $id, string $subject): void
    {
        qwe("
            update transfer_Last 
            set id = :id 
            where lastRec = :subject",
            ['id' => $id, 'subject'=>$subject]
        );
    }
}