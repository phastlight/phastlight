<?php
namespace Phastlight\Module\NET;

class Main extends \Phastlight\Module
{
    public function createTCPServer(/* $options*/$connectionListener = '')
    {
        $args = func_get_args();
        $argsCount = count($args);
        $options = array();
        if ($argsCount == 2) {
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
        $socket = new \Phastlight\Module\NET\Socket();
        $socket->connect($options['port'],$options['host'], $connectionListener);
        return $socket;
    }
}
