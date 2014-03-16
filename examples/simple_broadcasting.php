<?php

$system = new \Phastlight\System();

$console = $system->import("console");
$http = $system->import("http");

$ip = '127.0.0.1'; //change this to your ip
$port = 1337;

$http->createServer(function($req, $res) use ($port, &$system) {
    //// Broadcasting every 2 seconds
    $timer = $system->import("timer");
    $count = 0;
    $intervalId = $timer->setInterval(function($word) use (&$count, &$intervalId, &$timer, &$res){
        $count ++;
        if($count <=100){
            $res->write("hello ".$word);
        }
        else{
            $timer->clearInterval($intervalId); 
        }
    }, 2000, "world");
})->listen($port, $ip);
$console->log("Server is running on port $port");
