<?php

/**
 * The main system class
 */

namespace Phastlight;

class System extends EventEmitter
{

    private $eventLoop;
    private $moduleMap; //the modules map
    private $modules; //the module instances

    public function __construct()
    {

        $system = $this;

        //set up the event handler
        set_error_handler(function($severity, $message, $filePath, $line) use (&$system) {
            $error = new Error($severity, $message, $filePath, $line);
            $system->emit("system.error",$error);
        });

        //set up exception handler
        set_exception_handler(function($exception) use (&$system) {
            $system->emit("system.exception",$exception);
        });

        //on shutdown, run the event loop
        register_shutdown_function(function() {
            uv_run();
        });

        // start the event loop
        $this->eventLoop = uv_default_loop();

        //set up the core module map
        $modulePrefix = "\\Phastlight\\Module\\";
        $this->moduleMap = array(
            'console' => $modulePrefix.'Console\\Main',
            'process' => $modulePrefix.'Process\\Main',
            'os' => $modulePrefix.'OS\\Main', 
            'http' => $modulePrefix.'HTTP\\Main',
            'timer' => $modulePrefix.'Timer\\Main',
            'util' => $modulePrefix.'Util\\Main',
            'fs' => $modulePrefix.'FileSystem\\Main',
            'net' => $modulePrefix.'NET\\Main',
            'child_process' => $modulePrefix.'ChildProcess\\Main',
        );

        $this->modules = array();
    }

    /**
     * import a module based on name, similar to node.js's require
     */
    public function import($name)
    {
        $object = null;
        if (!isset($this->modules[$name])) {
            if (isset($this->moduleMap[$name])) {
                $object_class = $this->moduleMap[$name];
                $object = new $object_class();
                $object->setSystem($this);
                $object->setEventLoop($this->eventLoop);
                $this->modules[$name] = $object;
            }
        }
        else{
            $object = $this->modules[$name]; 
        }

        return $object;

    }

    /**
     * export a module to the map
     */
    public function export($module_name, $module_classname)
    {
        $this->moduleMap[$module_name] = $module_classname; 
    }

    /**
     * get current event loop
     */
    public function getEventLoop()
    {
        return $this->eventLoop;
    }

    /**
     * get event emitter
     */
    public function getEventEmitter()
    {
        return $this->eventEmitter; 
    }
}
