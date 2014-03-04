<?php
class Main extends \Phastlight\Module 
{
    public function __construct()
    {
        $cluster = new \Phastlight\Module\Cluster\Cluster();
        return $cluster;
    }
}
