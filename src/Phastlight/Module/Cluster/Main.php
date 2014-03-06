<?php 
declare(ticks=1);

namespace Phastlight\Module\Cluster;

class Main extends \Phastlight\Module 
{
    private $cluster;

    public function __construct()
    {
        $this->cluster = new \Phastlight\Module\Cluster\Cluster();
    }

    public function fork($workerClosure, $numOfWorkers = 1)
    {
        $this->cluster->fork($workerClosure, $numOfWorkers);
    }

    public function getAllWorkers()
    {
        return $this->cluster->getAllWorkers();
    }
}
