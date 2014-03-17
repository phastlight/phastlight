<?php
namespace Phastlight\Module\HTTP;

class Main extends \Phastlight\Module
{
    private $server;

    public function createServer($requestListener, $config = array())
    {
        $this->server = new Server($requestListener, $config);
        return $this->server;
    } 
}
