<?php 
function p($name) {
    return \Phastlight\system::getInstance()->import($name);
}

p('console')->log("from system singleton");

$count = 0;
$intervalId = p('timer')->setInterval(function($word) use (&$count, &$intervalId){
    $count ++;
    if($count <=3){
        echo $count.":".$word."\n";
    }
    else{
        p('timer')->clearInterval($intervalId); 
    }
}, 1000, "world");
