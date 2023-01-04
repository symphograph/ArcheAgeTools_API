<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/config.php';
use DBServices\ItemFixer;

ItemFixer::craftableCol();
echo 'craftableCol fixed<br>';