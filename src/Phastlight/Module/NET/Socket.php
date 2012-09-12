<?php
namespace Phastlight\Module\NET;

class Socket extends \Phastlight\EventEmitter
{
  private $stream;
  private $type; //the socket type

  public function setStream($stream)
  {
    $this->stream = $stream; 
  }

  public function getStream()
  {
    return $this->stream; 
  }

  public function setType($type)
  {
    $this->type = $type; 
  }

  public function getType()
  {
    return $this->type; 
  }

  public function write($data, $callback)
  {
    if($this->stream){
      $self = $this;
      uv_write($this->stream, $data, function($stream, $stat) use ($self,$callback){
        $self->setStream($stream);
        $callback();       
      });
    }  
  }

  public function end($data = null)
  {
    if($this->stream){
      $this->emit('end');
      if($data){
        uv_write($this->stream, $data); 
      }
      uv_close($this->stream); 
      $this->emit('close');
    }
  }
}
