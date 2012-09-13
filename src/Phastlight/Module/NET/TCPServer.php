<?php
namespace Phastlight\Module\NET;

class TCPServer extends \Phastlight\EventEmitter
{
  private $port;
  private $host;
  private $backlog;
  private $connectionListener;
  private $listeningListener;
  private $server;

  public function setPort($port)
  {
    $this->port = $port; 
  }

  public function setHost($host)
  {
    $this->host = $host;  
  }

  public function setBacklog($backlog)
  {
    $this->backlog = $backlog; 
  }

  public function setConnectionListener($connectionListener)
  {
    $this->connectionListener = $connectionListener;
  }

  public function setListeningListener($listeningListener)
  {
    $this->listeningListener = $listeningListener;
  }

  public function getPort()
  {
    return $this->port; 
  }

  public function getHost()
  {
    return $this->host; 
  }

  public function getBacklog()
  {
    return $this->backlog; 
  }

  public function getConnectionListener()
  {
    return $this->connectionListener; 
  }

  public function getListeningListener()
  {
    return $this->listeningListener; 
  }

  public function listen($options = array(), $listeningListener = '')
  {

    $default_options = array(
      'port' => 1337, 
      'host' => '127.0.0.1', 
      'backlog' => 100,
    );

    $options = array_merge($default_options, $options);

    $this->setPort($options['port']);
    $this->setHost($options['host']);
    $this->setBacklog($options['backlog']);
    $this->setListeningListener($listeningListener);

    $this->server = uv_tcp_init(); //very tricky, must use $this->server, using local variable will lead to segmentation fault
    uv_tcp_bind($this->server, uv_ip4_addr($options['host'],$options['port']));

    $self = $this;
    uv_listen($this->server,$options['backlog'],function($server) use ($self){
      $client = uv_tcp_init();
      uv_accept($server, $client);
      $socket = new \Phastlight\Module\NET\Socket();
      uv_read_start($client, function($uvSocket, $nread, $buffer) use ($self, $socket){
        if($nread > 0){
          $socket->setSocket($uvSocket);
          call_user_func_array($self->getConnectionListener(), array($socket));
          $socket->emit('data', $buffer);
        }
      });
    }); 
  }
}
