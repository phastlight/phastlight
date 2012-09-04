<?php
namespace Phastlight;

class Module 
{
  private $node;

  public function setNode($node)
  {
    $this->node = $node; 
  }

  public function getNode()
  {
    return $this->node; 
  }
}
