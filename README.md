Phastlight
===========

Phastlight is an asynchronous, event-driven command line tool and web server written in PHP 5.3+ enlightened by Node.js

At this time, Phastlight is on its very early development phrases,it currently supports:

+ Async HTTP server
+ Async Timer, similar to http://nodejs.org/api/timers.html
+ "Tick" in Process, similar to http://nodejs.org/api/process.html#process_process_nexttick_callback

More features will be on the way, stay tuned.

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

### Simple HTTP server, benchmarked with PHP 5.4.6 and Node.js v0.8.8
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$console = $system->import("console");
$http = $system->import("http");

$http->createServer(function($req, $res){
  $res->writeHead(200, array('Content-Type' => 'text/plain'));
  $res->end($req->getURL());
})->listen(1337, '127.0.0.1');
$console->log('Server running at http://127.0.0.1:1337/');
```

Now in command line, run php server/server.php and go to http://127.0.0.1:1337/ to see the result.

Below is the benchmark performed with Apache AB again the following Node.js script, the operating system is CENTOS 6 64bit, we simulate 200k requests and 5k concurrent requests

Node.js script
```javascript
var http = require('http');
http.createServer(function (req, res) {
  res.writeHead(200, {'Content-Type': 'text/plain'});
    res.end(req.url);
  }).listen(1337, '127.0.0.1');
console.log('Server running at http://127.0.0.1:1337/');
```

The PHP HTTP Server

    Concurrency Level:      5000
    Time taken for tests:   49.206 seconds
    Complete requests:      200000
    Failed requests:        0
    Write errors:           0
    Total transferred:      11414535 bytes
    HTML transferred:       2403060 bytes
    Requests per second:    4064.58 [#/sec] (mean)
    Time per request:       1230.139 [ms] (mean)
    Time per request:       0.246 [ms] (mean, across all concurrent requests)
    Transfer rate:          226.54 [Kbytes/sec] received

Node.js

    Concurrency Level:      5000
    Time taken for tests:   54.519 seconds
    Complete requests:      200000
    Failed requests:        0
    Write errors:           0
    Total transferred:      20454060 bytes
    HTML transferred:       200530 bytes
    Requests per second:    3668.43 [#/sec] (mean)
    Time per request:       1362.982 [ms] (mean)
    Time per request:       0.273 [ms] (mean, across all concurrent requests)
    Transfer rate:          366.38 [Kbytes/sec] received

### Server side timer
In the script below, we import the timer module and make the timer run every 1 second, after the counter hits 3, we stop the timer.
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$timer = $system->import("timer");
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

### Process next tick
We can distribute some heavy tasks into every "tick" of the server and make it non-blocking for other tasks.

In the script below, we do sum from 1 to 100 keeping track of the counter value, we distribute the sum operation into every "tick"
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$console = $system->import("console");
$process = $system->import("process");

$count = 0;
$sum = 0;
$system->method("sumFromOneToOneHundred", function() use ($system, &$count, &$sum){
  $console = $system->import("console"); //use the console module
  $count ++;
  if($count <= 100){
    $sum += $count;
    $process = $system->import("process"); //use the process module
    $process->nextTick(array($system,"sumFromOneToOneHundred"));
  }
  else{
    $console->log("Sum is $sum"); 
  }
});

$system->sumFromOneToOneHundred();

$console->log("Start Computing Sum From 1 to 100...");
```

Now in the command line, run php server/server.php, we should see:

    Start Computing Sum From 1 to 100...
    Sum is 5050

### Multi tasking in one single event loop
Using process next tick technique, we can perform mult-tasking in one single event loop.

In the script below, we perform a heavy task for suming 1 to 1 million, while also setting up a http server listening to port 1337
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$console = $system->import("console");
$process = $system->import("process");

$count = 0;
$sum = 0;
$n = 1000000;
$system->method("heavySum", function() use ($system, &$count, &$sum, $n){
  $console = $system->import("console"); //use the console module
  $count ++;
  if($count <= $n){
    $sum += $count;
    $process = $system->import("process"); //use the process module
    $process->nextTick(array($system,"heavySum"));
  }
  else{
    $console->log("Sum is $sum"); 
  }
});

$system->heavySum();

$console->log("Start Computing Sum From 1 to $n...");

$http = $system->import("http");
$http->createServer(function($req, $res){
  $res->writeHead(200, array('Content-Type' => 'text/plain'));
  $res->end("Requet path is ".$req->getURL());
})->listen(1337, '127.0.0.1');
$console->log('Server running at http://127.0.0.1:1337/');
```

### Integrating with Symfony2 HTTP Foundation component
We can easily integrate Symonfy2's HTTP Foundation component with Phastlight
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

$system = new \Phastlight\System();

$console = $system->import("console");
$http = $system->import("http");

$http->createServer(function($req, $res){
  $res->writeHead(200, array('Content-Type' => 'text/plain'));
  $request = Request::createFromGlobals();

  $res->end(
    $request->getRequestUri()."\n"
    .$request->getHttpHost()."\n"
  );
})->listen(1337, '127.0.0.1');
$console->log('Server running at http://127.0.0.1:1337/');
```

### Output HTML with Symfony2 HTTP Foundation component
The following example shows how to use Symfony2 HTTP Foundation component and phastlight to output HTML
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

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
```

### Integrate with Silex PHP Microframework
The following example shows how to integrate Phastlight with Silex PHP Microframework
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

$system = new \Phastlight\System();

$console = $system->import("console");
$http = $system->import("http");

$app = new Silex\Application(); 

$app->get('/hello/{name}', function($name) use($app) { 
    return 'Hello '.$app->escape($name); 
}); 

$http->createServer(function($req, $res) use ($app){
  $request = Request::createFromGlobals();
  $response = $app->handle($request);
  $app->terminate($request, $response);
  $res->writeHead(200, array('Content-Type' => 'text/html'));
  $res->end($response->getContent());
})->listen(1337, '127.0.0.1');
$console->log('Server running at http://127.0.0.1:1337/');
```
