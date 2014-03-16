<?php

$system = new \Phastlight\System();

$console = $system->import("console");
$http = $system->import("http");

$ip = '127.0.0.1'; //change this to your ip
$port = 1337;

$numOfConnections = 0;
$http->createServer(function($req, $res) use ($port, &$system, &$numOfConnections) {
    $numOfConnections ++;
    //// Broadcasting every 2 seconds
    $timer = $system->import("timer");
    $count = 0;
    $intervalId = $timer->setInterval(function() use (&$count, &$intervalId, &$timer, &$res, &$numOfConnections){
        $count ++;
        if($count <=100){
            $res->write("numOfConnections: $numOfConnections");
        }
        else{
            $timer->clearInterval($intervalId); 
        }
    }, 2000);
})->listen($port, $ip);
$console->log("Server is running on port $port");
