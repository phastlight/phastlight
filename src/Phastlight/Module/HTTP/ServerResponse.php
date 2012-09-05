<?php
namespace Phastlight\Module\HTTP;

class ServerResponse extends \Phastlight\EventEmitter
{
  private $data = "";
  private $status_code = 200;
  private $reason_phrase = "OK";
  private $headers = array();

  public function writeHead()
  {
    $args = func_get_args();
    $args_count = count($args);

    if($args_count == 2){
      $this->status_code = $args[0];
      $this->headers = $args[1]; 
    }
    else if($args_count == 3){
      $this->status_code = $args[0];
      $this->reason_phrase = $args[1];
      $this->headers = $args[2]; 
    }
  }

  public function end($data, $encoding = "UTF-8")
  {
    if(strlen($data) > 0){
      $this->data = $data; 
    }
    else{
      $this->data = "\r\n";
    }
  }

  public function getStatusCode()
  {
    return $this->status_code; 
  }

  public function getHeaders()
  {
    return $this->headers;
  }

  public function getReasonPhrase()
  {
    return $this->reason_phrase; 
  }

  public function getData()
  {
    return $this->data; 
  }
} 
