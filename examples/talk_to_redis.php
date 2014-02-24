<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$net = $system->import("net");

$client = $net->connect(array('host' => '127.0.0.1', 'port' => 6379));

$client->on('connect',  function() use (&$client) {
    print "connected to redis server, sending key value over...\n";
    $crlf = "\r\n";
    $client->write("SET mykey myvalue234$crlf"); //we can set the value of a key in one simple command over tcp
    $client->write("GET mykey$crlf"); //we can set the value of a key in one simple command over tcp

});

$client->on('data', function($data) use (&$client) {
    print_r($data);
    $client->end();
});
