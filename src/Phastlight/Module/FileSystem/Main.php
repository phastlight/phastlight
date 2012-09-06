<?php
namespace Phastlight\Module\FileSystem;

class Main extends \Phastlight\Module
{
  public function rename($old_path, $new_path, $callback)
  {
    uv_fs_rename($this->getEventLoop(), $old_path, $new_path, $callback);
  }
}
