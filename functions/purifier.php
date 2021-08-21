<?php
require_once 'libraries/htmlpurifier/library/HTMLPurifier.auto.php';
require_once 'utils/walkRecursive.php';
$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);

function purifyString(){
    return function ($obj) {
        global $purifier;
        return is_string($obj) ? $purifier->purify($obj) : $obj;
    };
};

function purify($obj){
    global $purify;
    return walk_recursive($obj, purifyString());
}

// $_REQUEST['USERNAME'] = $purify($_REQUEST['USERNAME']);
// $_POST['USERNAME'] = $purify($_POST['USERNAME']);
