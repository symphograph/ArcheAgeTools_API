<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';
use App\DBServices\ItemFixer;

ItemFixer::craftableCol();
echo 'craftableCol fixed<br>';