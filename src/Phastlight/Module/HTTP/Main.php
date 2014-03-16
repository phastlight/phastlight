<?php
namespace Phastlight\Module\HTTP;

class Main extends \Phastlight\Module
{
    private $httpParser;
    private $server;
    private $requestListener;
    private $port;
    private $host;
    private $requestQueue;

    private $process;

    public function __construct()
    {
        $this->httpParser = new HTTPParser();
        $this->requestQueue = new RequestQueue();
    }

    public function createServer($requestListener)
    {
        $this->requestListener = $requestListener;
        $server = clone($this); //tricky, we need to create a clone of the server object 
        $this->process = $this->getSystem()->import("process");
        $this->process->nextTick(array($this, "processRequestQueue"));
        return $server;    
    } 

    public function listen($port, $host = '127.0.0.1', $backlog = 100)
    {
        $this->port = $port;
        $this->host = $host;

        $self = $this;

        $system = $this->getSystem();

        $this->server = uv_tcp_init();
        uv_tcp_nodelay($this->server, true);

        $requestListener = $self->getRequestListener();

        uv_tcp_bind($this->server, uv_ip4_addr($host, $port));

        uv_listen($this->server,$backlog, function($server) use ($self, &$requestListener) {
            $client = uv_tcp_init();
            uv_accept($server, $client);

            uv_read_start($client, function($socket, $nread, $buffer) use ($self, &$requestListener) {
                $request = new ServerRequest();
                $response = new ServerResponse();
                $request->setBuffer($buffer);
                $request->setNRead($nread);
                $request->setSocket($socket);
                $request->on("request", $requestListener);

                //add to request queue...
                $self->getRequestQueue()->rpush(array($request, $response));

            });

        });
    }

    public function getRequestListener()
    {
        return $this->requestListener; 
    }

    public function getProtocol()
    {
        return $this->protocol; 
    }

    public function generateServerVariables($buffer, $socket) {
        $_SERVER['SERVER_PORT'] = $this->port;
        $_SERVER['SERVER_ADDR'] = $this->host;

        $socketInfo = uv_tcp_getpeername($socket);

        if (isset($socketInfo['address'])) {
            $_SERVER['REMOTE_ADDR'] = $socketInfo['address']; 
        }

        $result = array();

        $_SERVER['RAW_HTTP_HEADER'] = $buffer;

        $result = $this->httpParser->parse($buffer);
        if (isset($result['HEADERS']['HOST'])) {
            $_SERVER['HTTP_HOST'] = $result['HEADERS']['HOST']; 
        }
        $requestMethod = $result['REQUEST_METHOD'];

        //constructing server variables
        $_SERVER['REQUEST_METHOD'] = $result['REQUEST_METHOD'];
        if (isset($result['PATH'])) {
            $_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'] = $result['PATH'];
        }
        if (isset($result['HEADERS']['USER_AGENT'])) {
            $_SERVER['HTTP_USER_AGENT'] = $result['HEADERS']['USER_AGENT'];
        }
    }

    public function getParser()
    {
        return $this->httpParser;
    }

    public function getRequestQueue()
    {
        return $this->requestQueue;
    }

    public function getEventEmitter()
    {
        return $this->eventEmitter;
    }

    public function processRequestQueue()
    {
        $pair = $this->requestQueue->lpop();

        if ($pair !== NULL) {

            $request = $pair[0];
            $response = $pair[1];

            $socket = $request->getSocket();
            $request->emit("socket", $socket);
            
            $buffer = $request->getBuffer();
            $this->generateServerVariables($buffer, $socket);

            $request->emit("request", $request, $response);

            $status_code = $response->getStatusCode();
            $statusMessage = $response->getReasonPhrase();
            $headers = $response->getHeaders();
            $header = "";
            if (count($headers) > 0) {
                foreach ($headers as $key => $val) {
                    $header .= $key.": ".$val."\r\n";
                }
            } 
            $data = $response->getData();

            //$message = "HTTP/1.1 $status_code $statusMessage\r\n$header\r\n$data";
            $dataCount = count($data); 

            if ($dataCount > 0) {
                $message = implode("", $data)."\r\n";
                uv_write($request->getSocket(), $message);
                $request->removeAllListeners("request");
                $response->flushData();
                $pair[0] = $request;
                $pair[1] = $response;
            }

            //should this response end? 
            if ($response->shouldClose()) {
                uv_close($request->getSocket());
            } else { //this request should not end, add it back 
                $this->requestQueue->rpush($pair);
            }
        }
        $this->process->nextTick(array($this, "processRequestQueue"));
    }
}
