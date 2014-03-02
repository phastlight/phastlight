<?php 
$system = new \Phastlight\System();
$cpu_info = $system->import("os")->getCPUInfo();

$num_of_cpus = count($cpu_info);

print $num_of_cpus."\n";
