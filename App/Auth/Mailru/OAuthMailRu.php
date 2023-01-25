<?php

namespace App\Auth\Mailru;

use App\Api;
use JetBrains\PhpStorm\NoReturn;


class OAuthMailRu
{
    const URL_AUTHORIZE = 'https://oauth.mail.ru/login';
    const URL_GET_TOKEN = 'https://oauth.mail.ru/token';
    const URL_API       = 'https://oauth.mail.ru/userinfo';

    private static $token;
    public static  $user_id;
    public static  $userData;

    #[NoReturn] public static function goToAuth($secret): void
    {
        $uri_callback = 'https://' . $_SERVER['SERVER_NAME'] . '/auth/mailru.php';
        $url = self::URL_AUTHORIZE .
            '?client_id=' . $secret->app_id .
            '&response_type=code' .
            '&redirect_uri=' . urlencode($uri_callback) .
            '&state=' . '12345';

        self::redirect($url);
    }

    public static function getToken($code, $secret): bool
    {
        $uri_callback = 'https://' . $_SERVER['SERVER_NAME'] . '/auth/mailru.php';
        $data = [
            'client_id'     => $secret->app_id,
            'client_secret' => $secret->app_secret,
            'grant_type'    => 'authorization_code',
            'code'          => trim($code),
            'redirect_uri'  => $uri_callback
        ];

        // формируем post-запрос
        $opts = ['http' =>
                          [
                              'method'  => 'POST',
                              'header'  => "Content-Type: application/x-www-form-urlencodedrn" .
                                  "Accept: */*rn",
                              'content' => http_build_query($data)
                          ]
        ];
        $response = Api::curl(self::URL_GET_TOKEN, $data);

        $result = @json_decode($response);
        if (empty($result)) {
            return false;
        }

        self::$token = $result->access_token;
        //self::$user_id = $result->x_mailru_vid;
        return true;
    }

    public static function getUser(): MailruUser|bool
    {
        return MailruUser::byMailruToken(self::$token);
    }

    #[NoReturn] public static function redirect($uri = ''): void
    {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: ".$uri, TRUE, 302);
        exit;
    }


}