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
  
  } 

  public function on($event, $listener)
  {
  
  }

  public function removeListener($event, $listener)
  {
  
  }

  public function removeAllListeners($event)
  {
  
  }

  public function getListeners($event)
  {
  
  }

  public function emit($event/*,$arg1,$arg2...*/)
  {
  
  }
} 
