<?php

$system = new \Phastlight\System();

$console = $system->import("console");
$http = $system->import("http");

$news = array();

$news["us"] = array();

$broadcastingServer = $http->createServer(function($req, $res) use ($port, &$system, &$broadcastingServer, &$news) {
    //// Broadcasting every 1 seconds 
    $channel = str_replace("/","",$req->getURL());

    if (isset($news[$channel])) {
        $timer = $system->import("timer");
        $count = 0;
        $intervalId = $timer->setInterval(function() use (&$count, &$intervalId, &$timer, &$res, &$broadcastingServer, &$news, &$channel){
            $channelNews = $news[$channel];
            $res->write($channelNews[array_rand($channelNews)]);
        }, 1000);
    } else {
        $res->end("No news found on this channel");
    }
});
$broadcastingServer->listen(1337, "127.0.0.1");
$console->log("Server started broadcasting on port 1337");

//new content server
$contentServer = $http->createServer(function($req, $res) use ($port, &$system, &$news) {
    $channel = str_replace("/","",$req->getURL());
    if ($req->getURL() == "/news/add" && $req->getMethod() == "POST") {
        $comps = explode("\n\r", $_SERVER['RAW_HTTP_HEADER']);
        if (strlen($comps[1]) > 0) {
            $news["us"][] = trim($comps[1]);
            $res->end("News is added successfully");
        }
    }
});
$contentServer->listen(1338, "127.0.0.1");
$console->log("Content Server started on port 1338");
