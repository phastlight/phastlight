<?php 
namespace Phastlight\Module\ChildProcess;

class ChildProcess extends \Phastlight\EventEmitter 
{
    private $pid; # the process id of the child process 

    public function __construct($pid)
    {
        $this->pid = $pid;
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function kill($signal = "SIGHUP") 
    {
        posix_kill($this->pid, $signal);
    }
}
