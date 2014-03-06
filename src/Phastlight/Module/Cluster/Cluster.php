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
        declare(ticks=1);
        $this->curPid = posix_getpid();
        pcntl_signal(SIGTERM, array($this, "handleSignal"));
        pcntl_signal(SIGHUP, array($this, "handleSignal"));
    }

    public function fork($workerClosure, $numOfWorkers) {
        if (is_callable($workerClosure)) {
            for ($i = 1; $i <= $numOfWorkers; $i++) {
                $childProcess = System::load("child_process")->fork();
                $pid = $childProcess->getPid();
                if ($pid == -1) {
                    die("count not fork");
                } else if ($pid > 0) { //Successfully forked a worker process 
                    $process = new ChildProcess($pid);
                    $this->workers[$pid] = new Worker($process);
                } else if ($pid == 0) { //we are now in the worker process
                    $pid = posix_getpid();
                    $process = new ChildProcess($pid);
                    $worker = new Worker($process);
                    call_user_func_array($workerClosure, array($worker));
                }
            }
        }
    }

    private function handleSignal($signo)
    {
        switch ($signo) {
        case SIGTERM:
            // handle shutdown tasks 
            $pid = posix_getpid();
            echo "process $pid is killed\n";
            exit();
            break;
        case SIGHUP:
            // handle restart tasks 
            echo "detected sigup\n";
            break;
        default:
            // handle all other signals
        }
    }

    public function getProcessId()
    {
        return $this->curPid;
    }

    public function getAllWorkers() 
    {
        return $this->workers;
    }
}
