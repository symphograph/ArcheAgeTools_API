<?php

namespace App\Transfer;

use App\Transfer\Errors\TransferErr;

class Page
{
    public string $content = '';
    public string $error = '';
    public array $warnings = [];
    public bool $readOnly = true;

    const site = 'https://archeagecodex.com';
    const options = [
        CURLOPT_HEADER => 0,
        CURLOPT_FAILONERROR => 1,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.1.5) Gecko/20091102 Firefox/3.5.5 GTB6",
    ];

    public static function curl(string $url, array $options = []): object
    {
        if(empty($options)){
            $options = self::options;
        }
        $options[CURLOPT_URL] = $url;
        $ch = curl_init();
        curl_setopt_array($ch, $options);

        $content = curl_exec($ch);
        $err     = curl_errno($ch);
        $errmsg  = curl_error($ch) ;

        $result  = (object) curl_getinfo($ch);
        $result->errmsg = $errmsg;
        $result->err = $err;
        $result->content = $content;

        curl_close($ch);
        return $result;
    }

    /**
     * @throws TransferErr
     */
    protected function getContent(string $url, array $options = []): void
    {
        $result = self::curl($url, $options);
        if($result->err || $result->http_code !== 200 || empty($result->content)){
            throw new TransferErr('content not received');
        }
        $this->content = $result->content;
    }

    protected static function saveLast(int $id, string $subject): bool
    {
        return
            !!qwe("
                update transfer_Last 
                set id = :id 
                where lastRec = :lastRec",
                    ['id' => $id, 'lastRec' => $subject]
            );
    }
}