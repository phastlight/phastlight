<?php
namespace Phastlight;

class Module 
{
  private $system;
  private $event_emitter;

  public function setSystem($system)
  {
    $this->system = $system; 
  }

  public function getSystem()
  {
    return $this->system; 
  }
}
