<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

use Symphograph\Bicycle\Api\Response;
use App\Craft\AccountCraft;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\MyErrors;
use Symphograph\Bicycle\Errors\ValidationErr;
use App\Item\Item;
use App\User\Account;
$Account = Account::byToken();

$itemId = intval($_POST['itemId'] ?? 0)
or throw new ValidationErr('itemId', 'Ошибка данных');

$buyable = intval($_POST['buyable'] ?? 0);
$Item = Item::byId($itemId)
or throw new AppErr("Item::byId: $itemId does not exist", 'Предмет не найден');

if (!$buyable){
    $sql = "delete from uacc_buyOnly where accountId = :accountId and itemId = :itemId";
}else{
    if (!$Item->craftable){
        throw new AppErr("Item $Item->id must be craftable", 'Предмет не крафтабельный', 400);
    }

    if ($Item->personal){
        throw new AppErr("Item $Item->id must be not personal", 'Персональный предмет', 400);
    }

    $sql = "replace into uacc_buyOnly (accountId, itemId) VALUES (:accountId, :itemId)";
}

$qwe = qwe($sql,
    [
        'accountId'   => $Account->id,
        'itemId'      => $itemId,
    ]
) or throw new AppErr("set buyOnly err accountId: $Account->id, itemId: $itemId");
AccountCraft::clearAllCrafts();

Response::success();