<?php 
use \Phastlight\System;

$console = System::getInstance()->import("console");
$console->log("from system singleton");
