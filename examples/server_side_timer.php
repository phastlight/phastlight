<?php
$system = new \Phastlight\System();

$timer = $system->import("timer");
$count = 0;
$intervalId = $timer->setInterval(function($word) use (&$count, &$intervalId, $timer){
    $count ++;
    if($count <=3){
        echo $count.":".$word."\n";
    }
    else{
        $timer->clearInterval($intervalId); 
    }
}, 1000, "world");
