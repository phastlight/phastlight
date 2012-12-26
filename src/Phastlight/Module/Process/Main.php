<?php
namespace Phastlight\Module\Process;

class Main extends \Phastlight\Module
{
    private $tickCallbacks = array();
    private $tick = 0;

    public function addTickCallback($callback)
    {
        $this->tick ++;
        $this->tickCallbacks[$this->tick] = $callback;
    }

    public function getTickCallback($tick)
    {
        return $this->tickCallbacks[$tick]; 
    }

    public function removeTickCallback($tick)
    {
        unset($this->tickCallbacks[$tick]); 
    }

    public function getTickCallbacks()
    {
        return $this->tickCallbacks; 
    }

    public function nextTick($callback)
    {
        $loop = $this->getSystem()->getEventLoop();

        $plugin = $this;
        $plugin->addTickCallback($callback);

        $tick = $this->tick;

        if ($tick == 1) {
            $f = function($r, $status) use ($plugin, &$tick) {
                $callback = $plugin->getTickCallback($tick);
                if (is_callable($callback)) {
                    call_user_func($callback);
                    $plugin->removeTickCallback($tick);
                    $tick ++;
                    uv_async_send($r);
                }
                else{
                    uv_close($r); 
                }
            };

            $r = uv_async_init($loop, $f);
            uv_async_send($r);
        }
    }
}
