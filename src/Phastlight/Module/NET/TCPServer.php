<?php
namespace Phastlight\Module\NET;

class TCPServer extends \Phastlight\EventEmitter
{
  private $port;
  private $host;
  private $backlog;
  private $connectionListener;
  private $listeningListener;

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

  public function listen($options = array(
    'port' => 1337, 
    'host' => '127.0.0.1', 
    'backlog' => 100
  ), $listeningListener)
  {
     $this->setPort($options['port']);
     $this->setHost($options['host']);
     $this->setBacklog($options['backlog']);
     $this->setListeningListener($listeningListener);
  }
}
