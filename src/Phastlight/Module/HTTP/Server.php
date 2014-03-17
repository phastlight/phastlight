<?php 
/**
 * A class representing a http server
 */
namespace Phastlight\Module\HTTP;

class Server extends \Phastlight\EventEmitter 
{
    private $port;
    private $host;
    private $requestListener;
    private $requestQueue;
    private $httpParser;
    private $system;
    private $process;
    private $server;
    private $numOfConnections;

    public function __construct($requestListener = "")
    {
        $this->requestListener = $requestListener;
        $this->requestQueue = new RequestQueue();
        $this->httpParser = new HTTPParser();
        $this->system = \Phastlight\System::getInstance();
        $this->numOfConnections = 0;
    }

    public function listen($port, $host = '127.0.0.1', $backlog = 100)
    {
        $this->port = $port;
        $this->host = $host;
        $this->server = uv_tcp_init();
        uv_tcp_nodelay($this->server, true);
        uv_tcp_bind($this->server, uv_ip4_addr($host, $port));
        uv_listen($this->server, $backlog, array($this, "onListenCallback"));
        $this->process = $this->system->import("process");
        $this->process->nextTick(array($this, "processRequestQueue"));
    }

    public function onListenCallback($server)
    {
        $client = uv_tcp_init();
        uv_accept($server, $client);
        uv_read_start($client, array($this, "onReadStartCallback"));
    }

    public function onReadStartCallback($socket, $nread, $buffer)
    {
        $request = new ServerRequest();
        $response = new ServerResponse();
        $request->setBuffer($buffer); //TODO: maybe this should bind to the socket object...?
        $request->setNRead($nread);
        $request->setSocket($socket);

        //add to request queue...
        $this->requestQueue->rpush(array($request, $response, 0)); //0 means the request listener is not being called yet 
    }

    public function processRequestQueue()
    {
        $entry = $this->requestQueue->lpop();
        if ($entry !== NULL) {

            $request = $entry[0];
            $response = $entry[1];

            if ($request->getNRead() == -1) { //no more data coming in 
                $this->numOfConnections --;
                $response->end();
            } else {
                //call requestListener only once 
                if ($entry[2] == 0){
                    $this->numOfConnections ++;
                    $entry[2] = 1; //now the request handler is called 
                    $buffer = $request->getBuffer();
                    $socket = $request->getSocket();
                    $this->generateServerVariables($buffer, $socket);
                    call_user_func_array($this->requestListener, array($request, $response));
                }

                $data = $response->getData();

                $dataCount = count($data); 

                if ($dataCount > 0) { 
                    $statusCode = $response->getStatusCode();
                    $statusMessage = $response->getReasonPhrase();
                    $headers = $response->getHeaders();
                    $header = "";
                    if (count($headers) > 0) {
                        foreach ($headers as $key => $val) {
                            $header .= $key.": ".$val."\r\n";
                        }
                    } 

                    $message = "HTTP/1.1 $statusCode $statusMessage\r\n$header\r\n".implode("", $data)."\r\n";
                    uv_write($request->getSocket(), $message);
                    $response->flushData();
                    $entry[0] = $request;
                    $entry[1] = $response;
                }
            }

            //should this response end? 
            if ($response->shouldClose()) {
                uv_close($request->getSocket());
            } else { //this request should not end, add it back 
                $this->requestQueue->rpush($entry);
            }
        }
        $this->process->nextTick(array($this, "processRequestQueue"));
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

    public function getNumberOfConnections()
    {
        return $this->numOfConnections;
    }
}
