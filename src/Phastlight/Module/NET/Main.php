<?php
namespace Phastlight\Module\NET;

class Main extends \Phastlight\Module
{
  public function createTCPServer(/* $options*/$connectionListener = '')
  {
    $args = func_get_args();
    $argsCount = count($args);
    $options = array();
    if($argsCount == 2){
      $options = $args[0];
      $connectionListener = $args[1];
    }
    $server = new \Phastlight\Module\NET\TCPServer();  
    $server->setConnectionListener($connectionListener);
    return $server;
  } 

  public function connect($options, $connectionListener = '')
  {
    return $this->createTCPConnection($options, $connectionListener);
  }

  public function createTCPConnection($options = array('host' => '127.0.0.1', 'port' => 1337), $connectionListener = '')
  {
    $c = uv_tcp_init();
    $socket = new \Phastlight\Module\NET\Socket();
    uv_tcp_connect($c, uv_ip4_addr($options['host'],$options['port']), function($uvSocket, $stat) use ($socket, $connectionListener){
      $socket->setType("tcp4");
      if ($stat == 0) {
        $socket->setSocket($uvSocket);
        $socket->emit("connect"); 
        if(is_callable($connectionListener)){
          $connectionListener();
        }
      }
    });
    return $socket;
  }
}
