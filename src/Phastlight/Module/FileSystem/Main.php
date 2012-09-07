<?php
namespace Phastlight\Module\FileSystem;

class Main extends \Phastlight\Module
{
  public function rename($old_path, $new_path, $callback)
  {
    uv_fs_rename($this->getEventLoop(), $old_path, $new_path, $callback);
  }

  public function unlink($path, $callback)
  {
    uv_fs_unlink($this->getEventLoop(), $path, $callback);
  }

  public function lstat($path, $callback)
  {
    uv_fs_lstat($this->getEventLoop(), $path, $callback);
  }

  public function readDir($path, $callback)
  {
    uv_fs_readdir($this->getEventLoop(), $path, 0, $callback);
  }

  public function open($file_path, $callback)
  {
    uv_fs_open($this->getEventLoop(), $file_path, \UV::O_RDONLY, 0, $callback);  
  }

  public function read($fd, $callback)
  {
    uv_fs_read($this->getEventLoop(), $fd, $callback);   
  }
}
