<?php
namespace Phastlight\Module\ChildProcess;
use Symfony\Component\Process\Process;

class Main extends \Phastlight\Module
{
  public function exec($command, $callback)
  {
    $process = new Process($command);
    $process->run(function ($type, $buffer) use ($callback) {
      $error = null;
      $stdout = null;
      $stderr = null;
      if ('err' === $type) {
        $stderr = $buffer;
        $error = true; //@TODO: need to fine tuned more on the error code...
      } else {
        $stdout = $buffer;
      }
      $callback($error, $stdout, $stderr);
    });
  } 
}
