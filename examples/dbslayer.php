<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$net = $system->import("net");

$client = $net->connect(array('host' => '127.0.0.1', 'port' => 9090), function() use (&$client) {
    $client->on('data', function($data) use (&$client) {
        print_r($data);
    });

    $crlf = "\r\n";

    $sql = "SELECT NOW()";

    $sqlEncodedObject = urlencode(json_encode(array("SQL" => $sql)));

    $msg = "GET /db?$sqlEncodedObject HTTP/1.1".$crlf."Accept:application/json".$crlf.$crlf;

    $client->write($msg);
});
