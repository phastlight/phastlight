<?php
namespace Phastlight\Module\Util;

class Main extends \Phastlight\Module
{
  public function inspect($object)
  {
    return print_r($object,true); 
  }
}
