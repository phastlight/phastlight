<?php 
use Phastlight\system as p;
$cluster = p::load("cluster");

if ($cluster->isMaster()) {
    $cluster->fork();
    $cluster->fork();
    $cluster->fork();
    $cluster->fork();
}
