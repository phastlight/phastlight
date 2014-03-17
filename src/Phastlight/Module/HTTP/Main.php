<?php
namespace Phastlight\Module\HTTP;

class Main extends \Phastlight\Module
{
    private $server;

    public function createServer($requestListener)
    {
        $this->server = new Server($requestListener);
        return $this->server;
    } 
}
