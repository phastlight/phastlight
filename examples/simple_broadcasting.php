<?php

$system = new \Phastlight\System();

$console = $system->import("console");
$http = $system->import("http");

$ip = '127.0.0.1'; //change this to your ip
$port = 1337;

$server = $http->createServer(function($req, $res) use ($port, &$system, &$server) {
    //// Broadcasting every 1 seconds
    $timer = $system->import("timer");
    $count = 0;
    $intervalId = $timer->setInterval(function() use (&$count, &$intervalId, &$timer, &$res, &$server){
        $count ++;
        if($count <=100){
            $numOfConnections = $server->getNumberOfConnections();
            $res->write("numOfConnections: $numOfConnections");
        }
        else{
            $timer->clearInterval($intervalId); 
        }
    }, 1000);
});
$server->listen($port, $ip);
$console->log("Server is running on port $port");
