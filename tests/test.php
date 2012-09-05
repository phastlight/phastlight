<?php
spl_autoload_register(function ($class) {
  $class = str_replace("\\","/", $class);
  require __DIR__."/../src/$class.php";
});

$system = new \Phastlight\System();

$console = $system->import("console");
$http = $system->import("http");

$http->createServer(function($req, $res) {
  $res->writeHead(200, array('Content-Type' => 'text/plain'));
  $res->end($req->getURL());
})->listen(8000, '127.0.0.1');
$console->log('Server running at http://127.0.0.1:8000/');
