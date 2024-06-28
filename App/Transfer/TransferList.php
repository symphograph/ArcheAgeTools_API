<?php

namespace App\Transfer;

use PDO;

class TransferList
{
    protected string $orderBy;
    protected array  $errorFilter = [];
    protected string $subject;

    /**
     * @param int $limit
     * @param bool $readOnly [optional] <p>Set false for saving results to DB</p>
     * @param bool $random
     */
    public function __construct
    (
        protected int  $startId = 0,
        protected int  $limit = 1,
        protected bool $readOnly = true,
        protected bool $random = false
    )
    {
        $this->orderBy = $this->random ? 'rand()' : 'id';
        if(!$this->startId){
            $this->startId = $this->getLast();
        }
    }



    protected function getLast(): int
    {
        $qwe = qwe("select * from transfer_Last where lastRec = :subject", ['subject' => $this->subject]);
        return $qwe->fetchObject()->id;
    }

    protected function resetLast(int $id): void
    {
        qwe("
            update transfer_Last 
            set id = :id 
            where lastRec = :subject",
            ['id' => $id, 'subject'=>$this->subject]
        );
    }
}