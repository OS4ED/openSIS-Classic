<?php
error_reporting(0);
if($_REQUEST['modfunc'] != 'logout'){
    
include('lang/supportedLanguages.php');

if(isset($_REQUEST['language']) && in_array($_REQUEST['language'], array_keys($supportedLanguages))){
    $langCode = $_REQUEST['language'];
} elseif(isset($_SESSION['language'])){
    $langCode = $_SESSION['language'];
    } elseif (isset($_COOKIE['remember_me_lang'])){
        if(in_array($_COOKIE['remember_me_lang'], array_keys($supportedLanguages))){
            $langCode = $_COOKIE['remember_me_lang'];
}
    }

include "lang/lang_".$langCode.".php";

    $_SESSION['language'] = $langCode;

}

?>