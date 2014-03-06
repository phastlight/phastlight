<?php  
$system = new \Phastlight\System();
$cluster = $system->import("cluster");

$cluster->fork(function($worker) {
    echo "This is worker ".$worker->getProcess()->getPid()."\n"; 
    $worker->on("close", function($signal) use (&$worker) {
        echo "Worker ".$worker->getProcess()->getPid()." is closed now with signal: $signal\n";
    });
    for(;;) {
        $worker->kill();
    }
}, 5);

$workers = $cluster->getAllWorkers();
foreach($workers as $pid => $woker) {
    print "forked worker with pid: ".$pid."\n";
}
