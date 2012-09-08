<?php
namespace Phastlight;

class Module 
{
  private $system;
  private $eventEmitter;
  private $eventLoop;

  public function setSystem($system)
  {
    $this->system = $system; 
  }

  public function getSystem()
  {
    return $this->system; 
  }

  public function setEventLoop($eventLoop)
  {
    $this->eventLoop = $eventLoop; 
  }

  public function getEventLoop()
  {
    return $this->eventLoop; 
  }
}
