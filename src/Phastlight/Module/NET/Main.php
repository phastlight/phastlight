<?php
namespace Phastlight\Module\NET;

class Main extends \Phastlight\Module
{
  public function createTCPServer(/* $options, */$connectionListener = '')
  {
  
  } 

  public function connect($options, $connectionListener = '')
  {
    return $this->createTCPConnection($options, $connectionListener);
  }

  public function createTCPConnection($options = array('host' => '127.0.0.1', 'port' => 1337), $connectionListener = '')
  {
    $c = uv_tcp_init();
    $socket = new \Phastlight\Module\NET\Socket();
    uv_tcp_connect($c, uv_ip4_addr($options['host'],$options['port']), function($stream, $stat) use ($socket, $connectionListener){
      $socket->setType("tcp4");
      if ($stat == 0) {
        $socket->emit("connect"); 
        $socket->setStream($stream);
        call_user_func_array($connectionListener, array($socket));
      }
    });
    return $socket;
  }
}
