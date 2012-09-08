<?php
namespace Phastlight\Module\HTTP;

class ServerRequest extends \Phastlight\EventEmitter 
{
  private $url;

  public function getURL()
  {
    return $_SERVER['PATH_INFO'];
  }

  public function getMethod()
  {
    return $_SERVER['REQUEST_METHOD']; 
  }

  public function getHeader()
  {
    return $_SERVER['RAW_HTTP_HEADER']; 
  }
} 
