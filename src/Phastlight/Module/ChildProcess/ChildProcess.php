<?php 
namespace Phastlight\Module\ChildProcess;

class ChildProcess extends \Phastlight\EventEmitter 
{
    private $pid; # the process id of the child process 

    private $signalsMap;

    public function __construct($pid)
    {
        $this->pid = $pid;

        $self = $this;
        $signalHandler = function($signo) use ($self) {
            $self->emit("close", $self->getSignalName($signo));
            exit();
        };

        //set up signals map
        $this->signalsMap = array();

        $signals = array("SIGTERM", "SIGHUP", "SIGUSR1", "SIGQUIT", "SIGINT");
        foreach($signals as $signal) {
            $signalNumber = constant($signal);
            $this->signalsMap[$signalNumber] = $signal;
            pcntl_signal($signalNumber, $signalHandler);
        }
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function getSignalName($signal)
    {
        return $this->signalsMap[$signal];
    }

    public function kill($signal = "SIGHUP") 
    {
        posix_kill($this->pid, constant($signal));
        pcntl_signal_dispatch();
    }


}
