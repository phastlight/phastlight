<?php
namespace Phastlight\Module\HTTP;

class ServerRequest
{
  private $url;

  public function getURL()
  {
    return $_SERVER['PATH_INFO'];
  }
} 
