Phastlight
===========

Phastlight is an asynchronous, event-driven web server written in PHP 5.3+ enlightened by Node.js

##Installation:

#### install package "phastlight/phastlight" using composer (http://getcomposer.org/)
#### install sockets extension (http://www.php.net/manual/en/sockets.installation.php)
#### install php-uv and php-httpparser
    git clone https://github.com/chobie/php-uv.git --recursive
    cd php-uv/libuv
    make && cp uv.a libuv.a (my experience on both centos 64bit and ubuntu 64bit server is, we need to add -fPIC flag in config.m4)
    cd ..
    phpize
    ./configure
    make && make install (my experience on both centos64bit and ubuntu64bit  server is, we need to add -fPIC flag in config.m4)

    git clone https://github.com/chobie/php-httpparser.git --recursive
    cd php-httpparser
    phpize
    ./configure
    make && make install

    add following extensions to your php.ini
    extension=uv.so
    extension=httpparser.so

### Simple HTTP server
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$node = new \Phastlight\Node();

$console = $node->import("console");
$http = $node->import("http");

$http->createServer(function($req, $res){
  $res->writeHead(200, array('Content-Type' => 'text/plain'));
  $res->end("Requet path is ".$req->getURL());
})->listen(1337, '127.0.0.1');
$console->log('Server running at http://127.0.0.1:1337/');
```

Now in command line, run php server/server.php and go to http://127.0.0.1:1337/ to see the result.

### Server side timer
In the script below, we import the timer module and make the timer run every 1 second, after the counter hits 3, we stop the timer.
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$node = new \Phastlight\Node();

$timer = $node->import("timer");
$count = 0;
$interval_id = $timer->setInterval(function($word) use (&$count, &$interval_id, $timer){
  $count ++;
  if($count <=3){
    echo $count.":".$word."\n";
  }
  else{
    $timer->clearInterval($interval_id); 
  }
}, 1000, "world");
```
