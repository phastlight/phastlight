<?php
namespace Phastlight\Module\HTTP;

class RequestQueue 
{
    private $queue;

    public function __construct()
    {
        $this->queue = array();
    }

    /**
     * push element from the right
     */
    public function rpush($element) 
    {
        $this->queue[] = $element;
    }

    /**
     * pop request from the left
     */
    public function lpop()
    {
        return array_shift($this->queue);
    }

    /**
     * get the queue
     */
    public function get()
    {
        return $this->queue;
    }
}
