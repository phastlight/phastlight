<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$net = $system->import("net");

$client = $net->connect(array('host' => '127.0.0.1', 'port' => 9090), function() use (&$client) {
    $client->on('data', function($data) use (&$client) {
        print_r($data);
    });
    $sql = urlencode('{"SQL":"SELECT VERSION();"}');
    $msg = "GET /db?$sql HTTP/1.1\r\nHost: 127.0.0.1\r\nUser-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:17.0) Gecko/20100101 Firefox/17.0\r\nAccept:application/json\r\nAccept-Language: en-US,en;q=0.5\r\n\r\n";

    $client->write($msg);
});


