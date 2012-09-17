Phastlight
===========

Phastlight is an asynchronous, event-driven command line tool and web server written in PHP 5.3+ inspired by Node.js

Phastlight is built on top of libuv, the same library used behind Node.js.

[Install Phastlight](#installation)

[Benchmark against Node.js](#simple-http-server-benchmarked-with-php-546-and-nodejs-v088)

[Benchmark against React](#below-is-the-benchmark-on-phastlight-vs-react-on-a-simple-hello-world-response-we-simulate-10k-request-with-500-concurrent-requests)

At this time, Phastlight is on its very early development phrases,it currently supports:

+ [Async HTTP Server](#simple-http-server-benchmarked-with-php-546-and-nodejs-v088)
+ [Dynamic method creation](#dynamic-method-creation)
+ [Module Creation](#module-creation)
+ [Event Emitting](#event-emitting)
+ [Async Timer](#server-side-timer) similar to http://nodejs.org/api/timers.html
+ ["Tick" in Process](#process-next-tick) similar to http://nodejs.org/api/process.html#process_process_nexttick_callback
+ [Log message to the console](#console-log-like-javascript) similar to Console.log in Javascript
+ File System: 
  + [read content of directory asynchronously](#file-system--reads-the-contents-of-a-directory-in-async-fashion)
  + [open file asynchronously](#file-system-create-a-new-file-and-write-content-to-it)
  + [read file asynchronously](#file-system--reads-the-contents-of-a-directory-in-async-fashion)
  + [write file asynchronously](#file-system-create-a-new-file-and-write-content-to-it)
  + [close file asynchronously](#file-system-on-each-http-request-append-a-message-to-a-file-named-weblog-in-async-fashion)
  + [rename file asynchronously](#rename-file-asynchronously)
  + remove file asynchronously
  + get file stat asynchronously
+ Asynchronous Network Wrapper
  + [TCP Server](#tcp-server)
  + [TCP Connection](#tcp-connection)
+ PHP server variables simulation

  Phastlight simulates the following PHP server variables:
  + $_SERVER['SERVER_PORT']
  + $_SERVER['SERVER_ADDR']
  + $_SERVER['REMOTE_ADDR']
  + $_SERVER['HTTP_HOST']
  + $_SERVER['REQUEST_METHOD']
  + $_SERVER['REQUEST_URI']
  + $_SERVER['PATH_INFO'], 
  + $_SERVER['HTTP_USER_AGENT']

More features will be on the way, stay tuned...

Phastlight Application Examples:

+ [Simple Microframework on top of Phalcon PHP Framework Routing Component](#integrating-phastlight-with-phalcon-php-framework-routing-component)
+ [Working with Symfony2 HTTP Foundation Request and Response component](#output-html-with-symfony2-http-foundation-component)
+ [Simple asynchronous Memcache get and set](#asynchronous-memcache-get-and-set)
+ [Simple asynchronous Redis get and set](#asynchronous-redis-get-and-set)

At this phrase, phastlight is good for high concurrency, low data transfer, non cpu intensive web/mobible applications.

##Installation:

### Make sure you have superuser access

### Option 1: Run the installation script (Centos Server Only)###

#### install package "phastlight/phastlight" using composer (http://getcomposer.org/)
#### sh vendor/phastlight/phastlight/scripts/install.sh  (this will install php 5.4.7 plus php-uv.so and httpparser.so)
#### When running server, do: /usr/local/phastlight/bin/php -c /usr/local/phastlight/php.ini [server file full path]

### Option 2: Manual install with existing PHP source (Centos and MacOS only)###

#### install package "phastlight/phastlight" using composer (http://getcomposer.org/)
#### install sockets extension (http://www.php.net/manual/en/sockets.installation.php)
#### install php-uv and php-httpparser
    export CFLAGS='-fPIC'

    git clone https://github.com/chobie/php-uv.git --recursive
    cd php-uv/libuv
    make && cp uv.a libuv.a
    cd ..
    phpize
    ./configure
    make && make install

    git clone https://github.com/chobie/php-httpparser.git --recursive
    cd php-httpparser
    phpize
    ./configure
    make && make install

    add following extensions to your php.ini
    extension=uv.so
    extension=httpparser.so
#### When running server, do php [server file full path]


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

Below is the benchmark performed with Apache AB again the following Node.js script, the operating system is CENTOS 6 64bit, we simulate 200k requests and 5k concurrent requests.
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

### Below is the benchmark on Phastlight v.s React on a simple "hello world" response, we simulate 10k request with 500 concurrent requests

Phastlight server script:
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$system = new \Phastlight\System();

$console = $system->import("console");
$http = $system->import("http");

$http->createServer(function($req, $res){
  $res->writeHead(200, array('Content-Type' => 'text/plain'));
  $res->end("Hello World");
})->listen(1337, '127.0.0.1');
$console->log('Server running at http://127.0.0.1:1337/');
```

React serve script
```php
<?php
require __DIR__.'/../vendor/autoload.php';
$stack = new React\Espresso\Stack(function ($request, $response) {
  $response->writeHead(200, array('Content-Type' => 'text/plain'));
  $response->end("Hello World\n");
});
echo "Server running at http://127.0.0.1:1337\n";
$stack->listen(1337, '127.0.0.1');
```

Benchmark result:

Phastlight Server:

    Concurrency Level:      500
    Time taken for tests:   1.544 seconds
    Complete requests:      10000
    Failed requests:        0
    Write errors:           0
    Total transferred:      571368 bytes
    HTML transferred:       112233 bytes
    Requests per second:    6476.25 [#/sec] (mean)
    Time per request:       77.205 [ms] (mean)
    Time per request:       0.154 [ms] (mean, across all concurrent requests)
    Transfer rate:          361.36 [Kbytes/sec] received

React Server:

    Concurrency Level:      500
    Time taken for tests:   7.053 seconds
    Complete requests:      10000
    Failed requests:        0
    Write errors:           0
    Total transferred:      1220000 bytes
    HTML transferred:       220000 bytes
    Requests per second:    1417.88 [#/sec] (mean)
    Time per request:       352.639 [ms] (mean)
    Time per request:       0.705 [ms] (mean, across all concurrent requests)
    Transfer rate:          168.93 [Kbytes/sec] received

Result shows Phastlight is much faster than React

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
  $clrf = "\r\n";

  $client->on('data', function($data) use ($key, $clrf, &$client){
    //according to the protocol, when server returns "STORED\r\n", we know that the key is successfully stored
    if($data == "STORED$clrf"){       
      $client->removeAllListeners('data'); //we unbind the previous 'data' event listeners
      //we know re-add a new listener for event 'data'
      $client->on('data', function($data) use(&$client){
        print $data; //here we can see the details of the key that we just stored  
        $client->end(); //we now close the connection
      });
      $client->write("get $key$clrf"); //getting the memcache key is another simple command over tcp
    }
  });

  $client->write("set $key 0 $duration $valueLength$clrf$value$clrf"); //setting the memcache key is a simple command over tcp
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
  $clrf = "\r\n";

  $client->on('data', function($data) use ($key, $clrf, &$client){
    if($data == "+OK$clrf"){ //from the protocol, we know that now the key is successfully stored
      $client->removeAllListeners('data'); 
      $client->on('data', function($data) use(&$client){
        print $data; //here we can see the details of the key that we just stored  
        $client->end(); //close the connection
      });
      $client->write("GET mykey$clrf"); //we can get the key in one simple command over tcp
    }
  });

  $client->write("SET mykey myvalue234$clrf"); //we can set the value of a key in one simple command over tcp
});
```
