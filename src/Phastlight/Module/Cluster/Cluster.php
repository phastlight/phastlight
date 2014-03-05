<?php 
namespace Phastlight\Module\Cluster;

use Phastlight\System as system;

class Cluster extends \Phastlight\EventEmitter 
{
    private $curPid; //store the current process id
    private $childProcess; 
    private $isMaster;
    private $isWorker; 

    public function __construct()
    {
        $this->curPid = posix_getpid();
        $this->childProcess = system::load("child_process");
        $this->isMaster = true;
        $this->isWorker = false;
    }

    public function isMaster()
    {
        return $this->isMaster;
    }

    public function isWorker()
    {
       return $this->isWorker; 
    }

    /**
     * fork a worker
     */
    public function fork()
    {
        $worker = new Worker();
        $childProcess = $this->childProcess->fork();
        $pid = $childProcess->getPid();
        if ($pid == -1){
            die("could not fork"); 
        } else if ($pid > 0) {
            echo "Successfully fork child process $pid\n";
            exit();
        } 
        return $worker; 
    }
}
