<?php
require_once 'functions/UrlFnc.php';

$encoded_url = encode_url($_REQUEST['link_url']);
echo $encoded_url;
?>

