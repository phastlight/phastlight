<?php

$system = new \Phastlight\System();

$console = $system->import("console");
$http = $system->import("http");

$ports = array(1337, 8000, 9000, 9001, 9002);
$ip = '127.0.0.1'; //change this to your ip

foreach ($ports as $port) {
    $http->createServer(function($req, $res) use ($port) {
        $res->writeHead(200, array('Content-Type' => 'text/plain'));
        $res->end("Server is running on port $port");
    })->listen($port, $ip);
}

