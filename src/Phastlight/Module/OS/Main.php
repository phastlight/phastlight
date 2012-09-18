<?php
namespace Phastlight\Module\OS;

class Main extends \Phastlight\Module
{
  public function getEOL()
  {
    return PHP_EOL; 
  }

  public function getCPUInfo()
  {
    return uv_cpu_info(); 
  }
}
