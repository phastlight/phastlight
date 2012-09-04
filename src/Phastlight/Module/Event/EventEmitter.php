<?php
namespace Phastlight\Module\Event;

class EventEmitter extends \Phastlight\Object
{
  private $event_listeners;

  public function __construct()
  {
    $this->event_listeners = array();
  } 

  public function addListener($event, $listener) 
  {
    if(!isset($this->event_listeners[$even])){
      $this->event_listeners[$event] = array();
    }  
  } 
 

  /**
   * add a listener to the end of the listeners array for the specified event
   */
  public function on($event, $listener)
  {
    if(!isset($this->event_listeners[$even])){
      $this->event_listeners[$event] = array();
    }  
    $this->event_listeners[$event][] = $listener;
  }

  public function removeListener($event, $listener)
  {
    // TO DO...
  }

  public function removeAllListeners($event)
  {
    if(!isset($this->event_listeners[$even])){
      unset($this->event_listeners[$event]);
    }
  }

  public function getListeners($event)
  {
    $listeners = array();
    if(!isset($this->event_listeners[$event])){
      $listeners = $this->event_listeners[$event];
    }
    return $listeners;
  }

  public function emit($event/*,$arg1,$arg2...*/)
  {
    if(isset($this->event_listeners[$event])){
      $listener_count = count($this->event_listeners);
      $args = func_get_args();
      array_shift($args); //skip $event
      for($k = 0; $k < $listener_count; $k++){
        call_user_func_array($this->event_listeners[$k], $args);
      }
    }
  }
} 
