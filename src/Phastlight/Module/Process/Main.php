<?php
namespace Phastlight\Module\Process;

class Main extends \Phastlight\Module
{
  private $tick_callbacks = array();
  private $tick = 0;

  public function addTickCallback($callback)
  {
    $this->tick ++;
    $this->tick_callbacks[$this->tick] = $callback;
  }

  public function getTickCallback($tick)
  {
    return $this->tick_callbacks[$tick]; 
  }

  public function removeTickCallback($tick)
  {
    unset($this->tick_callbacks[$tick]); 
  }

  public function getTickCallbacks()
  {
    return $this->tick_callbacks; 
  }

  public function nextTick($callback)
  {
    $loop = $this->getNode()->getEventLoop();

    $plugin = $this;
    $plugin->addTickCallback($callback);

    $tick = $this->tick;

    if($tick == 1){
      $f = function($r, $status) use ($plugin, &$tick){
        $callback = $plugin->getTickCallback($tick);
        if(is_callable($callback)){
          $callback();
          $plugin->removeTickCallback($tick);
          $tick ++;
          uv_async_send($r);
        }
        else{
          uv_close($r); 
        }
      };

      $r = uv_async_init($loop, $f);
      uv_async_send($r);
    }
  }
}
