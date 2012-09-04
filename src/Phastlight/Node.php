<?php

/**
 * Node -- the base to simulate the behavior of Node.js
 */

namespace Phastlight;

class Node extends Object
{

  private $event_loop;
  private $module_map; //the modules map
  private $modules; //the module instances

  public function __construct()
  {

    register_shutdown_function(function(){
      uv_run();
    });

    // start the event loop
    $this->event_loop = uv_default_loop();

    //set up the core module map
    $module_prefix = "\\Phastlight\\Module\\";
    $this->module_map = array(
      'console' => $module_prefix.'Console\\Main',
      'process' => $module_prefix.'Process\\Main',
      'os' => $module_prefix.'OS\\Main', 
      'http' => $module_prefix.'HTTP\\Main',
      'timer' => $module_prefix.'Timer\\Main',
    );

    $this->modules = array();
  }

  /**
   * import a module based on name, similar to node.js's require
   */
  public function import($name)
  {
    $object = null;
    if(!isset($this->modules[$name])){
      if(isset($this->module_map[$name])){
        $object_class = $this->module_map[$name];
        $object = new $object_class();
        $object->setNode($this);
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
    $this->module_map[$module_name] = $module_classname; 
  }

  /**
   * get current event loop
   */
  public function getEventLoop()
  {
    return $this->event_loop;
  }
}
