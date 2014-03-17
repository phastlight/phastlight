<?php
namespace Phastlight\Module\Process;

class Main extends \Phastlight\Module
{
    private $tickCallbacks;
    private $idle;
    private $loop;

    public function __construct()
    {
        $this->tickCallbacks = array();
    }

    public function nextTick($callback)
    {
        $this->tickCallbacks[] = $callback;
        if ($this->loop === NULL) {
            $this->loop = $this->getSystem()->getEventLoop();
            $this->idle = uv_idle_init($this->loop);
            uv_idle_start($this->idle,array($this, "idleCallback"));
        }
    }

    public function idleCallback()
    {
        $tickCallback = array_shift($this->tickCallbacks);
        if (is_callable($tickCallback)) {
            $tickCallback();
        } else {
            uv_idle_stop($this->idle);
        }
    }
}
