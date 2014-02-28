<?php
$system = new \Phastlight\System();

$console = $system->import("console");
$process = $system->import("process");

$count = 0;
$sum = 0;
$system->method("sumFromOneToOneHundred", function() use ($system, &$count, &$sum){
    $console = $system->import("console"); //use the console module
    $count ++;
    if($count <= 100000){
        $sum += $count;
        $process = $system->import("process"); //use the process module
        $process->nextTick(array($system,"sumFromOneToOneHundred"));
    }
    else{
        $console->log("Sum is $sum"); 
    }
});

$system->sumFromOneToOneHundred();

$console->log("Start Computing Sum From 1 to 100000...");
