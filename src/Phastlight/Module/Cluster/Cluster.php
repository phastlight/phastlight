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
        if ($pid > 0) {
            if (posix_getpid() != $this->curPid) {
                echo "Successfully fork child process $pid\n";
            } else {
                echo "This is the master process with pid {$this->curPid}\n";
            }
            exit();
        } else if ($pid == 0) {
            if (posix_getppid() != $this->curPid) {
                $this->isMaster = false;
                $this->isWorker = true;
                echo "this is child process ".posix_getpid().", and forked from parent process: ".posix_getppid()."\n";
                // loop forever performing tasks
                while (1) {

                    // do something interesting here

                }
            }
        }
        return $worker; 
    }
}
