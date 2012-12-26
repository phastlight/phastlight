Phastlight
===========

Phastlight is an asynchronous, event-driven command line tool and web server written in PHP 5.3+ inspired by Node.js

Phastlight is built on top of libuv, the same library used behind Node.js.

[Install Phastlight](#installation)

[Benchmark against Node.js](#simple-http-server-benchmarked-with-php-546-and-nodejs-v088)

At this time, Phastlight is on its very early development phrases,it currently supports the following features:

+ [Dynamic method creation](#dynamic-method-creation)
+ [Module Creation](#module-creation)
+ Event
  + [Event Emitting](#event-emitting)
+ Error and Exception Handling
  + [Error Handling and system.error event](#error-handling)
  + [Exception Handling and system.exception event](#exception-handling)
+ HTTP
  + [Async HTTP Server](#simple-http-server-benchmarked-with-php-546-and-nodejs-v088)
  + PHP server variables simulation on HTTP Request

    Phastlight simulates the following PHP server variables on each HTTP Request:
    + $_SERVER['SERVER_PORT']
    + $_SERVER['SERVER_ADDR']
    + $_SERVER['REMOTE_ADDR']
    + $_SERVER['HTTP_HOST']
    + $_SERVER['REQUEST_METHOD']
    + $_SERVER['REQUEST_URI']
    + $_SERVER['PATH_INFO'], 
    + $_SERVER['HTTP_USER_AGENT']
+ Timer
  + [Async Timer](#server-side-timer) similar to http://nodejs.org/api/timers.html
+ Process
  + [Next Tick](#process-next-tick) similar to http://nodejs.org/api/process.html#process_process_nexttick_callback
+ Child Process
  + [executing command](#execute-command-in-child-process)
+ Console
  + [Log message to the console](#console-log-like-javascript) similar to Console.log in Javascript
+ File System: 
  + [read content of directory asynchronously](#file-system--reads-the-contents-of-a-directory-in-async-fashion)
  + [open file asynchronously](#file-system-create-a-new-file-and-write-content-to-it)
  + [read file asynchronously](#file-system--reads-the-contents-of-a-directory-in-async-fashion)
  + [write file asynchronously](#file-system-create-a-new-file-and-write-content-to-it)
  + [close file asynchronously](#file-system-on-each-http-request-append-a-message-to-a-file-named-weblog-in-async-fashion)
  + [rename file asynchronously](#rename-file-asynchronously)
  + [remove file asynchronously](#remove-file-asynchronously)
  + [get file stat asynchronously](#get-file-stat-asynchronously)
+ Asynchronous Network Wrapper
  + [TCP Server](#tcp-server)
  + [TCP Connection](#tcp-connection)
+ Operating System
  + [Get CPU Information](#operating-system-information)
  + [Get Memory Information](#operating-system-information)

More features will be on the way, stay tuned...

Phastlight Application Examples:

+ [Mult-Tasking in one single event loop](#handle-multiple-tasks-in-one-single-event-loop)
+ [Simple Microframework on top of Phalcon PHP Framework Routing Component](#integrating-phastlight-with-phalcon-php-framework-routing-component)
+ [Working with Symfony2 HTTP Foundation Request and Response component](#output-html-with-symfony2-http-foundation-component)
+ [Simple asynchronous MYSQL query through dbslayer](#simple-asynchronous-mysql-query-through-dbslayer)
+ [Simple asynchronous Memcache get and set](#asynchronous-memcache-get-and-set)
+ [Simple asynchronous Redis get and set](#asynchronous-redis-get-and-set)

At this phrase, phastlight is good for high concurrency, low data transfer, non cpu intensive web or moble applications.

##Installation:

Tested on:
+ Ubuntu 11.04 64bit with gcc 4.4.x
+ Ubuntu 12.04 64bit with gcc 4.4.x
+ CentOS 6.2 64bit with gcc 4.4.x
+ Mac OS 10.8 with gcc 4.2.1

### Make sure you have superuser access

### Option 1: Run the installation script 

#### install package "phastlight/phastlight" using composer (http://getcomposer.org/)
#### sh vendor/phastlight/phastlight/scripts/install.sh  (this will install php 5.4.10 plus php-uv.so and httpparser.so)
#### To run the server, do: /usr/local/phastlight/bin/php -c /usr/local/phastlight/php.ini [server file full path]

### Option 2: Manual install with existing PHP source (Linux and MacOS only)###

#### install package "phastlight/phastlight" using composer (http://getcomposer.org/)
#### install sockets extension (http://www.php.net/manual/en/sockets.installation.php)
#### install php-uv and httpparser extension
    export CFLAGS='-fPIC' 
    git clone https://github.com/chobie/php-uv.git --recursive
    cd php-uv/libuv 
    make clean
    make 
    cp uv.a libuv.a
    cd ..
    $dir/bin/phpize
    ./configure --with-php-config=$dir/bin/php-config
    make
    make install

    git clone https://github.com/chobie/php-httpparser.git --recursive
    cd php-httpparser
    $dir/bin/phpize
    ./configure --with-php-config=$dir/bin/php-config
    make clean
    make 
    make install

    add uv.so and httpparser.so in php.ini

#### To run the server, do php [server file full path]

### Dynamic method creation
Phastlight object allows dynamic method creation, in the example below, we create a hello method in the system object
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();
$system->method("hello", function($word){
  echo "Hello $word\n";
});
$system->hello("world");
```

### Module creation
Phastlight supports a flexible module system with export and import, the following example shows how to create a simple
module that can print "hello world"
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

class MyModule extends \Phastlight\Module
{
  public function hello($word)
  {
    echo "hello $word";
  }
}

$system = new \Phastlight\System();
$system->export("mymodule", "\MyModule"); //we first export the MyModule module
$module = $system->import("mymodule"); //now we can import it
$module->hello("world");
```

### Event Emitting
Event Emitter is a core component in phastlight, we can use it to emit and handle an event
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$eventEmitter = new \Phastlight\EventEmitter();

$eventEmitter->on("test", function(){
  echo "hello in test\n";
});

$eventEmitter->emit("test");
```

### Error Handling
When error occured, phastlight will emit system.error event, and we can use this event to further 
polish the error handling
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();
$system->on("system.error", function($error){
  print $error->getFilePath()."\n";
  print $error->getMessage()."\n";
  print $error->getLine()."\n";
  print $error->getSeverity()."\n";
});

$i = 12/0; //we purposely divide an integer by 0
```

### Exception Handling
When exception occurred, phastlight will emit system.exception event, and we can use this event
to further polish the exception handling
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();
$system->on("system.exception", function($exception){
  print $exception->getMessage()."\n";
  print $exception->getCode()."\n";
  print $exception->getLine()."\n";
});

throw new Exception('Uncaught Exception');
```

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

Below is the benchmark performed with Apache AB against the following Node.js script, the operating system is CENTOS 6 64bit, we simulate 200k requests and 5k concurrent requests.
Result shows phastlight is faster than Node.js.

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
    Time taken for tests:   38.994 seconds
    Complete requests:      200000
    Failed requests:        0
    Write errors:           0
    Total transferred:      9057240 bytes
    HTML transferred:       201272 bytes
    Requests per second:    5129.06 [#/sec] (mean)
    Time per request:       974.838 [ms] (mean)
    Time per request:       0.195 [ms] (mean, across all concurrent requests)
    Transfer rate:          226.83 [Kbytes/sec] received

Node.js

    Concurrency Level:      5000
    Time taken for tests:   53.565 seconds
    Complete requests:      200000
    Failed requests:        0
    Write errors:           0
    Total transferred:      20451000 bytes
    HTML transferred:       200500 bytes
    Requests per second:    3733.75 [#/sec] (mean)
    Time per request:       1339.136 [ms] (mean)
    Time per request:       0.268 [ms] (mean, across all concurrent requests)
    Transfer rate:          372.85 [Kbytes/sec] received

### Server side timer
In the script below, we import the timer module and make the timer run every 1 second, after the counter hits 3, we stop the timer.
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$timer = $system->import("timer");
$count = 0;
$intervalId = $timer->setInterval(function($word) use (&$count, &$intervalId, $timer){
  $count ++;
  if($count <=3){
    echo $count.":".$word."\n";
  }
  else{
    $timer->clearInterval($intervalId); 
  }
}, 1000, "world");
```

### console log like javascript
Phastlight can do console logging like javascript
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$console = $system->import("console");
$console->log("a message log to the console");
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

### Execute Command In Child Process
Phastlight can create child processes to execute a command.
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();
$childProcess = $system->import("child_process");
$childProcess->exec("ls -latr", function($error, $stdout, $stderr){
  if($error !== null){
    print "error occured\n"; 
  }
  else{
    print $stdout."\n"; 
  }
});
```

### File System : reads the contents of a directory in async fashion
The example belows show how to read the content of the current directory in the async fashion
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$fs = $system->import("fs");
$fs->readDir(".",function($result, $data){
  print_r($data);
});
```

### File System: create a new file and write content to it
The example below will create a file named "test" and write the string "hello world" in it in async fashion
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();
$fs = $system->import("fs");

$fs->open("test", "w", function($fd) use ($fs) {
  $fs->write($fd, "hello world", 0, function(){
    echo "done!\n"; 
  }); 
});
```

### File System: on each http request, append a message to a file named "weblog" in async fashion
The example below shows how to log a message into weblog in async fashion when there is a http request comes in
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$console = $system->import("console");
$http = $system->import("http");
$fs = $system->import("fs");

$http->createServer(function($req, $res) use ($fs) {
  $fs->open("weblog", "a", function($fd) use ($fs) {
    $time_string = microtime(true);
    $msg = "request coming in at $time_string\n";
    $fs->write($fd, $msg, null, function($fd) use ($fs) { //when the position is null, we append the message after the current position
      $fs->close($fd, function(){
      });
    }); 
  });
  $res->writeHead(200, array('Content-Type' => 'text/plain'));
  $res->end("hello, you are connected");
})->listen(1337, '127.0.0.1');
$console->log('Server running at http://127.0.0.1:1337/');
```

### Rename file asynchronously
In the example below, we rename a file named "test" in the current directory to "test2"
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$fs = $system->import("fs");
$console = $system->import("console");

$fs->rename("test","test2",function($result) use ($console){
  if($result == 0){
    $console->log("rename ok!"); 
  }
});
```

### Remove file asynchronously
In the example below, we remove a filed named "test" in the current directory
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$fs = $system->import("fs");
$fs->unlink("test",function($result){
  if($result == 0){
    echo "File test is successfully removed.\n"; 
  }
});
```

### Get file stat asynchronously 
In the example below, we will monitor the php script itself and see its information in async fashion
```php
<?php

//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();
$fs = $system->import("fs");

$fs->lstat(__FILE__, function($result, $data){
  if($result == 0){
    print_r($data);
  }
});
```

### TCP Server 
Below is an example to create a TCP server using the network module
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$net = $system->import("net");

$net->createTCPServer(function($socket){
  $output = "<h1>hello jim</h1>";
  $buffer = "HTTP/1.1 200 OK\r\nContent-Type: text/html\r\n\r\n$output";
  $socket->end($buffer);
})->listen(array(
  'port' => 8888,
  'host' => '127.0.0.1'
));
```

### TCP Connection
Below is an example to show how to create a tcp connection. 
We first create a tcp server listening to port 8888 in 127.0.0.1
When a tcp connection is created and starts writing to the server, we then can see what is coming back
from the server.
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$net = $system->import("net");

$net->createTCPServer(function($client){
  $output = "<h1>hello jim</h1>";
  $buffer = "HTTP/1.1 200 OK\r\nContent-Type: text/html\r\n\r\n$output";
  $client->end($buffer);
})->listen(array(
  'port' => 8888,
  'host' => '127.0.0.1'
));

$client = $net->connect(array('host' => '127.0.0.1', 'port' => 8888), function() use (&$client){
  $client->write('world!\r\n');
  $client->end();
});

$client->on('data', function($data){
  print $data;
});
```

### Operating System Information
Phastlight has the os module to return some data related to the operating system, like cpu and memory information
```php
<?php
/**
 * OS Information in phastlight
 */

//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$os = $system->import("os");
$console = $system->import("console");
$util= $system->import("util");

$console->log($os->getFreeMemoryInfo());
$console->log($os->getTotalMemoryInfo());
$console->log($util->inspect($os->getCPUInfo()));
```

### Handle multiple tasks in one single event loop
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

### Integrating phastlight with Phalcon PHP Framework Routing Component
Phalcon is a web framework delivered as a C extension providing high performance and low resource consumption, the example below shows a basic micro framework integrating phastlight with
Phalcon's routing component. The benchmark is quite good, benchark on ab -n 200000 -c 5000 shows 4593.84 requests per second in centos 6 server with 512MB memory.  

```php
<?php
class ClosureRouter extends \Phalcon_Router_Regex
{
  private $routes;

  public function addRoute($route, $closure)
  {
    $this->routes[$route] = $closure;
    $routeComps = explode("/", $route);
    $routeCompsCount = count($routeComps);
    $params = array('_closure' => $closure);
    if($routeCompsCount > 0){
      for($k = 1; $k < $routeCompsCount; $k++){
        if($routeComps[$k][0] == ":"){
          $name = $routeComps[$k];
          $name[0] = "";
          $name = trim($name);
          $params[$name] = $k;
          $routeComps[$k] = "([a-zA-Z0-9_-].+)"; //include -,_ and .
        }
      }
    }

    $route = implode("/", $routeComps);

    $this->add($route, $params);
  }
}

//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$router = new \ClosureRouter();

$router->addRoute('/news/:year/:month/:day', function($req, $res, $params){
  return json_encode($params);
});

$front = \Phalcon_Controller_Front::getInstance();
$front->setRouter($router);

///////////////////////////// Start The Server ///////////////////////////////

$system = new \Phastlight\System();

$console = $system->import("console");
$http = $system->import("http");

$http->createServer(function($req, $res) use (&$router, &$front) {
  $res->writeHead(200, array('Content-Type' => 'application/json'));
  $_GET['_url'] = $req->getURL();
  $router->handle();
  $params = $router->getParams();
  $content = "";
  $route = $router->getCurrentRoute();
  $closure = $route['paths']['_closure'];
  unset($params['_closure']);
  $content = $closure($req, $res, $params);
  $res->end($content);
})->listen(8000, '127.0.0.1');
$console->log('Server running at http://127.0.0.1:8000/');
```

### Output HTML with Symfony2 HTTP Foundation component
The following example shows how to use Symfony2 HTTP Foundation component and phastlight to output HTML

The benchmark is not bad, ab -n 10000 -c 500 shows 3245.07 reqs/second in centos 6 server with 512MB memory.

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

### Simple asynchronous MYSQL query through dbslayer 
[DBSlayer](http://code.nytimes.com/projects/dbslayer) is a lightweight database abstraction layer allowing sql queries to MYSQL database through REST and JSON.
With the net module in phastlight, we can now perform sql queries asynchronously over TCP through DBSlayer.
Assuming DBSlayer is running at host 127.0.0.1 and port 9090, the example below shows how to get the current database time in mysql asynchronously.

```php 
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$net = $system->import("net");

$client = $net->connect(array('host' => '127.0.0.1', 'port' => 9090), function() use (&$client) {
    $client->on('data', function($data) use (&$client) {
        //we can now see the details of the data
        print_r($data);
    });

    $crlf = "\r\n";

    $sql = "SELECT NOW()";

    $sqlEncodedObject = urlencode(json_encode(array("SQL" => $sql)));

    $msg = "GET /db?".$sqlEncodedObject." HTTP/1.1".$crlf."Accept:application/json".$crlf.$crlf;

    $client->write($msg);
});
```

### Asynchronous Memcache Get and Set
With the net module in phastlight, we can now do some interesting things with memcache over TCP.
The example below shows how to set a key in memcache asynchronously over TCP, and then when the key is successfully stored, 
we read the details of the key.

For more details on the memcached tcp protocol, please click [here](http://memcachedb.googlecode.com/svn/trunk/doc/protocol.txt), we can now create some async memcache libraries just by following the protocol.

```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$net = $system->import("net");

$client = $net->connect(array('host' => '127.0.0.1', 'port' => 11211), function() use (&$client){
  $key = "samplekey";
  $duration = 3600; //duration of 1 hour
  $value = 250;
  $valueLength = strlen($value); 
  $crlf = "\r\n";

  $client->on('data', function($data) use ($key, $crlf, &$client){
    //according to the protocol, when server returns "STORED\r\n", we know that the key is successfully stored
    if($data == "STORED$crlf"){       
      $client->removeAllListeners('data'); //we unbind the previous 'data' event listeners
      //we know re-add a new listener for event 'data'
      $client->on('data', function($data) use(&$client){
        print $data; //here we can see the details of the key that we just stored  
        $client->end(); //we now close the connection
      });
      $client->write("get $key$crlf"); //getting the memcache key is another simple command over tcp
    }
  });

  $client->write("set $key 0 $duration $valueLength$crlf$value$crlf"); //setting the memcache key is a simple command over tcp
});
```

### Asynchronous Redis Get and Set
With the net module in phastlight, we can now do some interesting things with Redis over TCP.
The example below shows how to set a key in redis asynchronously over TCP, and then when the key is successfully stored, 
we read the details of the key.

For more details on the redis tcp protocol, please click [here](http://redis.io/topics/protocol), we can now create some async redis libraries just by following the protocol.

```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$net = $system->import("net");

$client = $net->connect(array('host' => '127.0.0.1', 'port' => 6379), function() use (&$client){
  $crlf = "\r\n";

  $client->on('data', function($data) use ($key, $crlf, &$client){
    if($data == "+OK$crlf"){ //from the protocol, we know that now the key is successfully stored
      $client->removeAllListeners('data'); 
      $client->on('data', function($data) use(&$client){
        print $data; //here we can see the details of the key that we just stored  
        $client->end(); //close the connection
      });
      $client->write("GET mykey$crlf"); //we can get the key in one simple command over tcp
    }
  });

  $client->write("SET mykey myvalue234$crlf"); //we can set the value of a key in one simple command over tcp
});
```
