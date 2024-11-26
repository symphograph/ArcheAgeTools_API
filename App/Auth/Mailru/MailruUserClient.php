<?php

namespace App\Auth\Mailru;


use App\Transfer\User\MailruOldUser;
use Symphograph\Bicycle\Api\CurlAPI;
use Symphograph\Bicycle\Auth\Mailru\MailruUser;
use Symphograph\Bicycle\Env\Services\Service;
use Symphograph\Bicycle\Errors\NoContentErr;

class MailruUserClient extends MailruUser
{
    const string path    = '/api/user/mailru.php';
    public ?string $avaFilename;

    private static function getUrl(): string
    {
        $serviceUrl = Service::byName('auth')->getUrl();
        return "$serviceUrl" . self::path;
    }

    public static function byAccountId(int $accountId): self|bool
    {
        $params = [
            'method' => 'getById',
            'accountId' => $accountId
        ];

        $curl = new CurlAPI(self::getUrl(), $params);

        try {
            $response = $curl->post();
        } catch (NoContentErr $err) {
            return false;
        }

        $MailruUser = new self();
        $MailruUser->bindSelf($response->data);

        return $MailruUser;
    }

    public static function byEmail(string $email): self|bool
    {
        $params = [
            'method' => 'getByEmail',
            'email' => $email
        ];

        $curl = new CurlAPI(self::getUrl(),$params);

        try {
            $response = $curl->post();
        } catch (NoContentErr) {
            return false;
        }

        $MailruUser = new self();
        $MailruUser->bindSelf($response->data);

        return $MailruUser;
    }

    public function putToAuthServer($createdAt, $visitedAt): self
    {
        $params = [
            'method' => 'create',
            'MailruUser' => json_encode($this),
            'createdAt' => $createdAt,
            'visitedAt' => $visitedAt
        ];

        $curl = new CurlAPI(self::getUrl(), $params);

        $response = $curl->post();
        $MailUser = new self();
        $MailUser->bindSelf($response->data->newMailruUser);
        $MailUser->avaFilename = $response->data->avaFilename;
        return $MailUser;
    }

    public static function byOld(MailruOldUser $OldUser): self
    {
        $newUser = new self();
        $newUser->email = $OldUser->email;
        $newUser->first_name = $OldUser->first_name;
        $newUser->last_name = $OldUser->last_name;
        $newUser->nickname = $OldUser->mailnick;
        $newUser->name = $OldUser->mailnick;
        $newUser->image = $OldUser->avatar;
        return $newUser;
    }
}