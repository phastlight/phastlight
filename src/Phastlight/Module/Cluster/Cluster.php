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
        $this->handleSignals();
    }

    public function fork($workerClosure, $numOfWorkers) {
        if (is_callable($workerClosure)) {
            for ($i = 1; $i <= $numOfWorkers; $i++) {
                $childProcess = System::load("child_process")->fork();
                $pid = $childProcess->getPid();
                if ($pid == -1) {
                    $message = "Error forking child process from parent process {$this->curPid}";
                    $childProcess->emit("error");
                    die();
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

    private function handleSignals()
    {
        $self = $this;
        $signalHandler = function($signo) use ($self) {
            switch ($signo) {
            case SIGTERM:
                // handle shutdown tasks 
                $pid = posix_getpid();
                $worker = $self->getWorkerByPid($pid);
                $worker->emit("exit", $signo);
                exit();
                break;
            case SIGHUP:
                // handle restart tasks 
                $pid = posix_getpid();
                $worker = $self->getWorkerByPid($pid);
                $worker->emit("exit", $signo);
                exit();
                break;
            default:
                // handle all other signals
            }
        }; 

        $signals = array(SIGTERM, SIGHUP, SIGUSR1);
        foreach($signals as $signal) {
            pcntl_signal($signal, $signalHandler);
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
