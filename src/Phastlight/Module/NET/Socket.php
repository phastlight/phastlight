<?php
namespace Phastlight\Module\NET;

class Socket extends \Phastlight\EventEmitter
{
  private $socket; //the underlying uv socket
  private $type; //the socket type

  public function setSocket($socket)
  {
    $this->socket = $socket; 
  }

  public function getSocket()
  {
    return $this->socket; 
  }

  public function setType($type)
  {
    $this->type = $type; 
  }

  public function getType()
  {
    return $this->type; 
  }

  public function write($data, $callback = '')
  {
    if($this->socket){
      $self = $this;
      $socket = $this->socket;
      uv_write($socket, $data, function($uvSocket, $stat) use ($self,$callback) {
        if($stat == 0){
          uv_read_start($uvSocket, function($uvSocket, $nread, $buffer) use ($self){
            if($nread > 0){
              $self->emit('data', $buffer);
            }
          });
          if(is_callable($callback)){
            $callback(); 
          }
        }
      });
    }  
  }

  public function end($data = null)
  {
    $this->emit('end');
    $self = $this;
    if($data){
      uv_write($this->socket, $data, function($uvSocket, $stat) use ($self){
        uv_close($uvSocket);
        $self->emit('close');
      }); 
    }
  }
}
