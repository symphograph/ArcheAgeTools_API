<?php

namespace App\Transfer;

class Page
{
    public string $content = '';
    public string $error = '';
    public array $warnings = [];

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

    protected function getContent(string $url, array $options = []): bool
    {
        $result = self::curl($url, $options);
        if($result->err || $result->http_code !== 200 || empty($result->content)){
            $this->error = 'content not received';
            return false;
        }
        $this->content = $result->content;
        return true;
    }
}