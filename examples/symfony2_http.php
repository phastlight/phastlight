<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$system = new \Phastlight\System();

$console = $system->import("console");
$http = $system->import("http");

$http->createServer(function($req, $res){
    $res->writeHead(200, array('Content-Type' => 'text/html'));
    $request = Request::createFromGlobals();
    $response = Response::create("<h1>Hello World</h1>");
    $res->end($response->getContent());
})->listen(1337, '127.0.0.1');
$console->log('Server running at http://127.0.0.1:1337/');
