<?php 
$system = new \Phastlight\System();
$cluster = $system->import("cluster");

$cluster->fork(function($worker) {
   echo "This is worker ".$worker->getProcess()->getPid()."\n"; 
}, 5);
