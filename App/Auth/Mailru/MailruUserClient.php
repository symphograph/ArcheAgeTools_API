<?php

namespace App\Auth\Mailru;


use App\Transfer\User\MailruOldUser;
use Symphograph\Bicycle\Api\CurlAPI;
use Symphograph\Bicycle\Auth\Mailru\MailruUser;
use Symphograph\Bicycle\Errors\NoContentErr;
use Symphograph\Bicycle\Token\CurlToken;

class MailruUserClient extends MailruUser
{
    const url = '/api/user/mailru.php';
    const apiName = 'AuthServer';
    public ?string $avaFilename;

    public static function byAccountId(int $accountId): self|bool
    {
        $jwt = CurlToken::create([1]);
        $curl = new CurlAPI(
            self::apiName,
            self::url,
            ['method' => 'getById', 'accountId' => $accountId],
            $jwt
        );
        $response = $curl->post();
        $MailruUser = new self();
        $MailruUser->bindSelf($response->data);

        return $MailruUser;
    }

    public static function byEmail(string $email): self|bool
    {
        $jwt = CurlToken::create([1]);
        $curl = new CurlAPI(
            self::apiName,
            self::url,
            ['method' => 'getByEmail', 'email' => $email],
            $jwt
        );

        try {
            $response = $curl->post();
        } catch (NoContentErr $err) {
            return false;
        }

        $MailruUser = new self();
        $MailruUser->bindSelf($response->data);

        return $MailruUser;
    }

    public function putToAuthServer($createdAt, $visitedAt): self
    {
        $jwt = CurlToken::create([1]);
        $curl = new CurlAPI(
            self::apiName,
            self::url,
            [
                'method' => 'create',
                'MailruUser' => json_encode($this),
                'createdAt' => $createdAt,
                'visitedAt' => $visitedAt
            ],
            $jwt
        );
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