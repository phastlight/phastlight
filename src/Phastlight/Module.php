<?php
namespace Phastlight;

class Module 
{
  private $system;
  private $event_emitter;
  private $event_loop;

  public function setSystem($system)
  {
    $this->system = $system; 
  }

  public function getSystem()
  {
    return $this->system; 
  }

  public function setEventLoop($event_loop)
  {
    $this->event_loop = $event_loop; 
  }

  public function getEventLoop()
  {
    return $this->event_loop; 
  }
}
