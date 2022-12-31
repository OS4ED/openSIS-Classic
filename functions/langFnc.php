<?php
error_reporting(0);

function langDirection(){
    include 'lang/supportedLanguages.php';
    return $supportedLanguages[$_SESSION['language']]['direction'];
}
?>