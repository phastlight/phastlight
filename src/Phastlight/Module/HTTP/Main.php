<?php
namespace Phastlight\Module\HTTP;

class Main extends \Phastlight\Module
{
    public function createServer($requestListener)
    {
        $server = new Server($requestListener);
        return $server;
    } 
}
