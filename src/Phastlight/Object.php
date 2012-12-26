<?php
namespace Phastlight;

class Object
{
    private $methods; //store all undeclared object methods
    private $storage; //built-in object storage

    public function __construct()
    {
        $this->methods = array();
        $this->storage= array();
    }

    public function __call($method_name, $params)
    {
        if (isset($this->methods[$method_name])) {
            return call_user_func_array($this->methods[$method_name], $params); 
        }
    }

    final public function method($name, $callback)
    {
        $this->methods[$name] = $callback;
    }

    final public function storageSet($key, $val)
    {
        $this->storage[$key] = $val;
    }

    final public function storageGet($key)
    {
        $result = null;
        if (isset($this->storage[$key])) {
            $result = $this->storage[$key];
        }
        return $result;
    }
}
