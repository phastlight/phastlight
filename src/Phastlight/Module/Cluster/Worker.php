<?php 
namespace Phastlight\Module\Cluster;

class Worker extends \Phastlight\EventEmitter 
{
    public function send($message, $sendHandle = "")
    {

    }

    public function kill($signal = "SIGTERM")
    {

    }
}
