<?php
namespace Phastlight\Module\Cluster;

class Main extends \Phastlight\Module 
{
    private $cluster;

    public function __construct()
    {
        $this->cluster = new \Phastlight\Module\Cluster\Cluster();
    }

    public function isMaster()
    {
        return $this->cluster->isMaster();
    }

    public function isWorker()
    {
        return $this->cluster->isWorker();
    }

    public function fork()
    {
        return $this->cluster->fork();
    }
}
