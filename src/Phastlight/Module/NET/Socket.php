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
      $self = $this;
      uv_write($this->stream, $data, function($stream, $stat) use ($self){
        $self->setStream($stream);
        $callback();        
      });
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
