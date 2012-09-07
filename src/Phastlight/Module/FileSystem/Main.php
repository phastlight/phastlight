<?php
namespace Phastlight\Module\FileSystem;

class Main extends \Phastlight\Module
{

  public function rename($old_path, $new_path, $callback)
  {
    uv_fs_rename(uv_default_loop(), $old_path, $new_path, $callback);
  }

  public function unlink($path, $callback)
  {
    uv_fs_unlink(uv_default_loop(), $path, $callback);
  }

  public function lstat($path, $callback)
  {
    uv_fs_lstat(uv_default_loop(), $path, $callback);
  }

  public function readDir($path, $callback)
  {
    uv_fs_readdir(uv_default_loop(), $path, 0, $callback);
  }

  public function open($file_path, $callback)
  {
    uv_fs_open(uv_default_loop(), $file_path, \UV::O_RDONLY, 0, $callback);  
  }

  public function read($fd, $callback)
  {
    uv_fs_read(uv_default_loop(), $fd, $callback);   
  }

  public function close($fd, $callback)
  {
    uv_fs_close(uv_default_loop(), $fd, $callback);
  }
}
