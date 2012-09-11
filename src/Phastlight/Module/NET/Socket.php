<?php
namespace Phastlight\Module\NET;

class Socket extends \Phastlight\EventEmitter
{
  private $stream;

  public function setStream($stream)
  {
    $this->stream = $stream; 
  }

  public function getStream()
  {
    return $this->stream; 
  }

  public function write($data, $callback)
  {
    if($this->stream){
      uv_write($this->stream, $data, $callback);
    }  
  }

  public function close()
  {
    if($this->stream){
      uv_close($this->stream); 
      $this->emit('close');
    }
  }
}
