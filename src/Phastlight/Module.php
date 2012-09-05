<?php
namespace Phastlight;

class Module 
{
  private $node;
  private $event_emitter;

  public function setNode($node)
  {
    $this->node = $node; 
  }

  public function getNode()
  {
    return $this->node; 
  }
}
