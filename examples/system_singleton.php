<?php 
function p($name) {
    return \Phastlight\system::getInstance()->import($name);
}

p('console')->log("from system singleton");
