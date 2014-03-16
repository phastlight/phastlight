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
     * push request from the right
     */
    public function rpush($request) 
    {
        $this->queue[] = $request;
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
