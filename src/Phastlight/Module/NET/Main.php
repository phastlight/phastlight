<?php
namespace Phastlight\Module\NET;

class Main extends \Phastlight\Module
{
  public function createServer(/* $options, */$connectionListener = '')
  {
  
  } 

  public function connect($options, $connectionListener = '')
  {
  
  }

  public function createTCPConnection($options = array('host' => '127.0.0.1', 'port' => 1337), $connectionListener = '')
  {
    $c = uv_tcp_init();
    $socket = new \Phastlight\Module\NET\Socket();
    uv_tcp_connect($c, uv_ip4_addr($options['host'],$options['port']), function($stream, $stat) use ($socket){
      if ($stat == 0) {
        $socket->emit("connect"); 
        $socket->setStream($stream);
      }
    });
    return $socket;
  }
}
