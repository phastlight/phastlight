<?php 
namespace Phastlight\Module\HTTP;

class HTTPParser
{
    private $parser;

    public function __construct()
    {
        $this->parser = http_parser_init(); //set up http parser
    }

    public function parse($content)
    {
        $result = array();
        http_parser_execute($this->parser, $content, $result);
        return $result;
    }
} 
