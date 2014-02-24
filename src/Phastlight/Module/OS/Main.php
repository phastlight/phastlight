<?php
namespace Phastlight\Module\OS;

class Main extends \Phastlight\Module
{
    public function getEOL()
    {
        return PHP_EOL; 
    }

    public function getCPUInfo()
    {
        return uv_cpu_info(); 
    }

    public function getFreeMemoryInfo()
    {
        return uv_get_free_memory();  
    }

    public function getTotalMemoryInfo()
    {
        return uv_get_total_memory(); 
    }

    public function getUptime() 
    {
        return uv_uptime();
    }

    public function getLoadAverage() 
    {
        return uv_loadavg();
    }
}
