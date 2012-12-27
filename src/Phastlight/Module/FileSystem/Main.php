<?php
namespace Phastlight\Module\FileSystem;

class Main extends \Phastlight\Module
{

    private $fileFlags;

    public function __construct()
    {
        $this->fileFlags = array(
            'r' => \UV::O_RDONLY, //read only
            'w' => \UV::O_WRONLY | \UV::O_CREAT, //write only
            'a' => \UV::O_APPEND | \UV::O_CREAT | \UV::O_WRONLY, //apend
            'w+' => \UV::O_RDONLY | \UV::O_WRONLY //read and write
        ); 
    }

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

    public function open($file_path, $flag, $callback)
    {
        if (isset($this->fileFlags[$flag])) {
            uv_fs_open($this->getEventLoop(), $file_path, $this->fileFlags[$flag], 0, $callback);  
        }
    }

    public function read($fd, $callback)
    {
        uv_fs_read($this->getEventLoop(), $fd, $callback);   
    }

    public function write($fd, $buffer, $position, $callback)
    {
        uv_fs_write($this->getEventLoop(), $fd, $buffer, $position, $callback); 
    }

    public function close($fd, $callback)
    {
        uv_fs_close($this->getEventLoop(), $fd, $callback);
    }
}
