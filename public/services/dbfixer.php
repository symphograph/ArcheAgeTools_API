<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';
use App\DBServices\ItemFixer;
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>test</title>
</head>
<body style="color: white; background-color: #262525">
<?php
//ItemFixer::craftableCol();
//echo 'craftableCol fixed<br>';
ItemFixer::renameIcons();

?>
</body>
</html>
