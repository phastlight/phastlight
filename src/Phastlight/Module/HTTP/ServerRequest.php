<?php
namespace Phastlight\Module\HTTP;

class ServerRequest extends \Phastlight\EventEmitter 
{
    private $url;
    private $socket; //the socket object that binds to this request
    private $buffer; 
    private $nread; 

    public function setBuffer($buffer)
    {
        $this->buffer = $buffer;
    }

    public function setNRead($nread)
    {
        $this->nread = $nread;
    }

    public function setSocket($socket) 
    {
        $this->socket = $socket;
    }

    public function getSocket()
    {
        return $this->socket;
    }

    public function getBuffer()
    {
        return $this->buffer;
    }

    public function getNRead()
    {
        return $this->nread;
    }

    public function getURL()
    {
        return $_SERVER['PATH_INFO'];
    }

    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD']; 
    }

    public function getHeader()
    {
        return $_SERVER['RAW_HTTP_HEADER']; 
    }
} 
