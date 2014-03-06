<?php 
namespace Phastlight\Module\Cluster;

class Worker extends \Phastlight\EventEmitter 
{
    private $process; #the child process object that this worker binds to 

    public function __construct($process)
    {
        $this->process = $process;
    }

    public function getProcess()
    {
        return $this->process;
    }
    
    public function kill($signal = SIGHUP)
    {
        $this->process->kill($signal);
    }
}
