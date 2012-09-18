<?php
spl_autoload_register(function ($class) {
  $class = str_replace("\\","/", $class);
  require __DIR__."/../src/$class.php";
});

$system = new \Phastlight\System();

$os = $system->import("os");
print_r($os->getCPUInfo());
