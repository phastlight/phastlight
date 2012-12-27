<?php
namespace Phastlight\Module\Console;

class Main extends \Phastlight\Module
{
    public function log($message)
    {
        print $message."\n";
    }
}
