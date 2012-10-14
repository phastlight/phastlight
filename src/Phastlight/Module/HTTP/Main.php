<?php
namespace Phastlight\Module\HTTP;

class Main extends \Phastlight\Module
{
  private $http_parser;
  private $serverRequest;
  private $serverResponse;
  private $server;
  private $requestListener;
  private $port;
  private $host;

  public function __construct()
  {
    $this->http_parser = uv_http_parser_init(\UV::HTTP_REQUEST); //set up http parser
  }

  public function createServer($requestListener)
  {
    $this->requestListener = $requestListener;
    $this->serverRequest = new ServerRequest();
    $this->serverResponse = new ServerResponse();
    $server = clone($this); //tricky, we need to create a clone of the server object
    return $server;    
  } 

  public function listen($port, $host = '127.0.0.1', $backlog = 100)
  {
    $this->port = $port;
    $this->host = $host;

    $self = $this;

    $system = $this->getSystem();

    $this->server = uv_tcp_init();

    $request = $self->getServerRequest();
    $response = $self->getServerResponse();
    $requestListener = $self->getRequestListener();

    uv_tcp_bind($this->server, uv_ip4_addr($host, $port));

    uv_listen($this->server,$backlog, function($server) use ($self, &$request, &$response, &$requestListener) {
      $client = uv_tcp_init();
      uv_accept($server, $client);

      uv_read_start($client, function($socket, $nread, $buffer) use ($self, &$request, &$response, &$requestListener){

        $self->generateServerVariables($buffer, $socket);

        call_user_func_array($requestListener, array($request, $response));

        $status_code = $response->getStatusCode();
        $statusMessage = $response->getReasonPhrase();
        $headers = $response->getHeaders();
        $header = "";
        if(count($headers) > 0){
          foreach($headers as $key => $val){
            $header .= $key.": ".$val."\r\n";
          }
        } 
        $output = $response->getData();
        $buffer = "HTTP/1.1 $status_code $statusMessage\r\n$header\r\n$output";
        uv_write($socket, $buffer);
        uv_close($socket);
      });

    });
  }

  public function getRequestListener()
  {
    return $this->requestListener; 
  }

  public function getServerRequest()
  {
    return $this->serverRequest; 
  }

  public function getServerResponse()
  {
    return $this->serverResponse; 
  }

  public function getProtocol()
  {
    return $this->protocol; 
  }

  public function generateServerVariables($buffer, $socket) {
    $_SERVER['SERVER_PORT'] = $this->port;
    $_SERVER['SERVER_ADDR'] = $this->host;

    $socketInfo = uv_tcp_getpeername($socket);

    if(isset($socketInfo['address'])){
      $_SERVER['REMOTE_ADDR'] = $socketInfo['address']; 
    }

    $result = array();

    $_SERVER['RAW_HTTP_HEADER'] = $buffer;

    uv_http_parser_execute($this->http_parser, $buffer, $result);

    if(isset($result['HEADERS']['HOST'])){
      $_SERVER['HTTP_HOST'] = $result['HEADERS']['HOST']; 
    }
    $requestMethod = $result['REQUEST_METHOD'];

    //constructing server variables
    $_SERVER['REQUEST_METHOD'] = $result['REQUEST_METHOD'];
    if(isset($result['PATH'])){
      $_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'] = $result['PATH'];
    }
    if(isset($result['HEADERS']['USER_AGENT'])){
      $_SERVER['HTTP_USER_AGENT'] = $result['HEADERS']['USER_AGENT'];
    }

    //constructing global variables
    if($requestMethod == 'GET'){
      if(isset($result['QUERY'])){
        $result['HEADERS']['body'] = $result['QUERY']; //bind body to query if it is a get request
      }
    }

    if(isset($result['HEADERS']['body'])){
      $GLOBALS["_$requestMethod"] = explode("&", $result['HEADERS']['body']); 
    }
  }
}
