<?php
namespace Phastlight\Module\Timer;

class Main extends \Phastlight\Module
{
    private $timeouts; //store all timeouts
    private $intervals; //store all intervals

    public function __construct()
    {
        $this->timeouts = array(); 
        $this->intervals = array(); 
    }

    public function setTimeout($callback, $delay/* ,$arg1, $arg2, ...*/)
    {
        $args = func_get_args();
        array_shift($args); //skip $callback
        array_shift($args); //skip $delay
        $timeout_id = uv_timer_init();
        uv_timer_start($timeout_id, $delay, 0, function($stat) use ($callback, $args) {
            call_user_func_array($callback, $args);
        });
        return $timeout_id; 
    }

    public function clearTimeout($timeout_id)
    {
        uv_timer_stop($timeout_id);
        uv_unref($timeout_id);
    }

    public function setInterval($callback, $delay/* ,$arg1, $arg2, ... */)
    {
        $args = func_get_args();
        array_shift($args); //skip $callback
        array_shift($args); //skip $delay
        $interval_id = uv_timer_init();
        uv_timer_start($interval_id, $delay, $delay, function($stat) use ($callback, $args) {
            call_user_func_array($callback, $args);
        });
        return $interval_id; 
    }

    public function clearInterval($interval_id)
    {
        uv_timer_stop($interval_id);
        uv_unref($interval_id);
    }
}
