<?php
spl_autoload_register(function ($class) {
  $class = str_replace("\\","/", $class);
  require __DIR__."/../src/$class.php";
});

$node = new \Phastlight\System();

$console = $node->import("console");
$http = $node->import("http");

$http->createServer(function($req, $res) {
  $res->writeHead(200, array('Content-Type' => 'text/plain'));
  $res->end($req->getURL());
})->listen(8000, '127.0.0.1');
$console->log('Server running at http://127.0.0.1:8000/');
