<?php
namespace Phastlight\Module\HTTP;

class ServerResponse extends \Phastlight\EventEmitter
{
    private $data = "";
    private $statusCode = 200;
    private $reasonPhrase = "OK";
    private $headers = array();
    private $shouldClose = false;

    public function writeHead()
    {
        $args = func_get_args();
        $argsCount = count($args);

        if($argsCount == 2){
            $this->statusCode = $args[0];
            $this->headers = $args[1]; 
        }
        else if($argsCount == 3){
            $this->statusCode = $args[0];
            $this->reasonPhrase = $args[1];
            $this->headers = $args[2]; 
        }
    }

    public function write($data, $encoding = "UTF-8")
    {
        $this->data = $data;
    }

    public function end($data, $encoding = "UTF-8")
    {
        $this->write($data."\r\n");
        $this->shouldClose = true;
    }

    public function shouldClose()
    {
        return $this->shouldClose;
    }

    public function getStatusCode()
    {
        return $this->statusCode; 
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getReasonPhrase()
    {
        return $this->reasonPhrase; 
    }

    public function getData()
    {
        return $this->data; 
    }
} 
