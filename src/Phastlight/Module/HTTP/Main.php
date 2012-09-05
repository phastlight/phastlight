<?php
namespace Phastlight\Module\HTTP;

class Main extends \Phastlight\Module
{
  private $http_parser;
  private $server_request;
  private $server_response;
  private $server;
  private $request_listener;

  public function createServer($request_listener)
  {
    $this->request_listener = $request_listener;
    $this->server_request = new ServerRequest();
    $this->server_response = new ServerResponse();
    $server = clone($this); //tricky, we need to create a clone of the server object
    return $server;    
  } 

  public function listen($port, $host = '127.0.0.1', $backlog = 100)
  {
    $_SERVER['SERVER_PORT'] = $port;
    $_SERVER['SERVER_ADDR'] = $host;

    $self = $this;

    $system = $this->getSystem();
    $process = $system->import("process");

    $this->server = uv_tcp_init();

    $parser = http_parser_init();
    $request = $self->getServerRequest();
    $response = $self->getServerResponse();
    $request_listener = $self->getRequestListener();

    uv_tcp_bind($this->server, uv_ip4_addr($host, $port));

    uv_listen($this->server,$backlog, function($server) use ($self, $parser, $process, &$request, &$response, &$request_listener) {
      $client = uv_tcp_init();
      uv_accept($server, $client);

      uv_read_start($client, function($socket, $nread, $buffer) use ($self, $parser, $process, &$request, &$response, &$request_listener){
        $result = array();
        http_parser_execute($parser, $buffer, $result);

        $request_method = $result['REQUEST_METHOD'];

        //constructing server variables
        $_SERVER['REQUEST_METHOD'] = $result['REQUEST_METHOD'];
        $_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'] = $result['path'];
        $_SERVER['HTTP_USER_AGENT'] = $result['headers']['User-Agent'];

        //constructing global variables
        if($request_method == 'GET'){
          $result['headers']['body'] = $result['query']; //bind body to query if it is a get request
        }

        $GLOBALS["_$request_method"] = explode("&", $result['headers']['body']); 

        call_user_func_array($request_listener, array($request, $response));

        $status_code = $response->getStatusCode();
        $status_message = $response->getReasonPhrase();
        $headers = $response->getHeaders();
        $header = "";
        if(count($headers) > 0){
          foreach($headers as $key => $val){
            $header .= $key.": ".$val."\r\n";
          }
        } 
        $output = $response->getData();
        $buffer = "HTTP/1.1 $status_code $status_message\r\n$header\r\n$output";
        uv_write($socket, $buffer);
        uv_close($socket);
      });

    });
  }

  public function getRequestListener()
  {
    return $this->request_listener; 
  }

  public function getServerRequest()
  {
    return $this->server_request; 
  }

  public function getServerResponse()
  {
    return $this->server_response; 
  }

  public function getProtocol()
  {
    return $this->protocol; 
  }
}
