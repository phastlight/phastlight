<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$net = $system->import("net");
$http = $system->import("http");

$net->createTCPServer(function($socket) use (&$system, &$http){
  $socket->on('data', function($data) use (&$system, &$socket, &$http){
    $http->generateServerVariables($data, $socket->getSocket());
    $output = "Your ip is <h1>".$_SERVER['REMOTE_ADDR']."</h1>";
    $buffer = "HTTP/1.1 200 OK\r\nContent-Type: text/html\r\n\r\n$output";
    $socket->end($buffer);
  });
})->listen(array(
  'port' => 1337,
  'host' => '127.0.0.1' //use your server's ip address
));
