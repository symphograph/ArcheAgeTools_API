<?php

namespace User;

use Symphograph\Bicycle\DB;

class Sess
{
    public string   $id;
    public int|null $account_id;
    public string   $token;
    public string   $first_ip;
    public string   $last_ip;
    public string   $first_time;
    public string   $last_time;
    public string   $platform;
    public string   $browser;
    public string   $device_type;
    public int     $ismobiledevice;

    public function __set(string $name, $value): void
    {
    }

    public static function cookOpts(
        int         $expires = 0,
        string      $path = '/',
        string|null $domain = null,
        bool        $secure = true,
        bool        $httponly = true,
        string      $samesite = 'Strict', // None || Lax  || Strict
        bool        $debug = false
    ): array
    {
        if (!$expires) {
            $expires = time() + 60 * 60 * 24 * 30;
        }
        //$domain = $domain ?? $_SERVER['SERVER_NAME'];

        if ($debug) {
            return [
                'expires'  => $expires,
                'path'     => '/',
                'domain'   => null,
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'None'
            ];
        }
        return [
            'expires'  => $expires,
            'path'     => $path,
            'domain'   => $domain,
            'secure'   => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite // None || Lax  || Strict
        ];
    }

    public static function byToken(string $token, bool $refresh = true): self|bool
    {
        $sql = 'select * from user_sessions 
         where token = :token 
           and last_time > now() - interval 1 hour ';
        $args = ['token' => $token];
        return self::getSess($sql, $args, $refresh);
    }

    public static function byId(string $id, bool $refresh = true): self|bool
    {
        if (empty($id)){
            return false;
        }
        $sql = 'select * from user_sessions where id = :id';
        $args = ['id' => $id];
        return self::getSess($sql, $args, $refresh);
    }

    private static function getSess(string $sql, array $args, bool $refresh): self|bool
    {
        $qwe = qwe($sql, $args);
        if (!$qwe || !$qwe->rowCount()) {
            return false;
        }

        $Sess = $qwe->fetchObject(self::class);
        $className = self::class;
        if (!($Sess instanceof $className)) {
            return false;
        }
        if ($refresh) {
            $Sess->refreshTime();
        }
        return $Sess;
    }

    private static function newToken(): string
    {
        $token = random_bytes(12);
        return bin2hex($token);
    }

    public static function newSess(int $account_id): self|bool
    {
        $agent = get_browser();

        $Sess = new self();
        $Sess->id = self::newToken();
        $Sess->token = self::newToken();
        $Sess->account_id = $account_id;
        $Sess->first_ip = $_SERVER['REMOTE_ADDR'];
        $Sess->last_ip = $_SERVER['REMOTE_ADDR'];
        $Sess->platform = $agent->platform;
        $Sess->browser = $agent->browser;
        $Sess->device_type = $agent->device_type;
        $Sess->ismobiledevice = intval($agent->ismobiledevice);
        $Sess->first_time = date('Y-m-d H:i:s');
        $Sess->last_time = date('Y-m-d H:i:s');

        if(!$Sess->putToDB()){
            return false;
        }

        $Sess->setCook();
        return $Sess;
    }

    private function putToDB(): bool
    {
        $params = [
            'id'             => $this->id,
            'account_id'     => $this->account_id,
            'token'          => $this->token,
            'first_ip'       => $this->first_ip,
            'last_ip'        => $this->last_ip,
            'platform'       => $this->platform,
            'browser'        => $this->browser,
            'device_type'    => $this->device_type,
            'ismobiledevice' => $this->ismobiledevice,
            'first_time'     => $this->first_time,
            'last_time'      => $this->last_time
        ];

        return DB::replace('user_sessions', $params);
    }

    public function refreshTime(): bool
    {
        $qwe = qwe("
            update user_sessions set 
                last_ip = :ip, 
                last_time = now() 
            where id = :id",
            ['ip' => $_SERVER['REMOTE_ADDR'], 'id' => $this->id]
        );
        if(!$qwe){
            return false;
        }
        return self::setCook();
    }

    public function setCook(): bool
    {
        global $env;
        return setcookie('sessId', $this->id, self::cookOpts(debug: $env->debug));
    }

    public function goToClient(): void
    {
        global $env;
        $spaUrl = $env->sites[$_SERVER['SERVER_NAME']];
        header("Location: https://$spaUrl/auth?#{$this->token}");
    }

    public function refresh(): bool
    {
        $this->last_ip = $_SERVER['REMOTE_ADDR'];
        $this->last_time = date("Y-m-d H:i:s");
        $this->token = self::newToken();
        if(!self::putToDB()){
            printr($this);
            return false;
        }
        return self::setCook();
    }

    public static function checkOrigin(): void
    {
        if (empty($_SERVER['HTTP_ORIGIN'])){
            die(http_response_code(401));
        }
        global $env;
        $adr = 'https://' . $env->sites[$_SERVER['SERVER_NAME']];
        if($_SERVER['HTTP_ORIGIN'] !== $adr){
            echo $env->sites[$_SERVER['SERVER_NAME']];
            die(http_response_code(403));
        }
    }

}