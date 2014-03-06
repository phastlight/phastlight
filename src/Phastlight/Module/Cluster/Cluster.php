<?php  
namespace Phastlight\Module\Cluster;

use Phastlight\System as system;
use Phastlight\Module\ChildProcess\ChildProcess as ChildProcess;

class Cluster extends \Phastlight\EventEmitter 
{
    private $curPid; //the current process id
    private $workers; //all workers in this cluster

    public function __construct()
    {
        $this->curPid = posix_getpid();
    }

    public function fork($workerClosure, $numOfWorkers) {
        if (is_callable($workerClosure)) {
            for ($i = 1; $i <= $numOfWorkers; $i++) {
                $childProcess = System::load("child_process")->fork();
                $pid = $childProcess->getPid();
                if ($pid == -1) {
                    $childProcess->emit("error", $pid);
                    exit();
                } else if ($pid > 0) { //Successfully forked a worker process 
                    $process = new ChildProcess($pid);
                    $this->workers[$pid] = new Worker($process);
                } else if ($pid == 0) { //we are now in the worker process 
                    $pid = posix_getpid();
                    $process = new ChildProcess($pid);
                    $worker = new Worker($process);
                    $this->workers[$pid] = $worker; //immediately record workers in the queue
                    call_user_func_array($workerClosure, array($worker));
                }
            }
        }
    }

    public function getPId()
    {
        return $this->curPid;
    }

    public function getAllWorkers() 
    {
        return $this->workers;
    }

    public function getWorkerByPid($pid) 
    {
        return $this->workers[$pid];
    }

}
