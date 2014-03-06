<?php 
$system = new \Phastlight\System();
$cluster = $system->import("cluster");

$cluster->fork(function($worker) {
    echo "This is worker ".$worker->getProcess()->getPid()."\n"; 
    for(;;) {

    }
}, 5);

$workers = $cluster->getAllWorkers();
foreach($workers as $pid => $woker) {
    print "forked worker with pid: ".$pid."\n";
}
