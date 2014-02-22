<?php 
namespace Phastlight\Module\HTTP;

class HTTPParser
{
  public function parse($content)
  {
    $result = array();
    $parser = uv_http_parser_init(\UV::HTTP_REQUEST); //set up http parser
    uv_http_parser_execute($parser, $content, $result);
    return $result;
  }
} 
