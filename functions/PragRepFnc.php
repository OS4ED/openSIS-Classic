<?php
function par_rep($match='',$exp='',$sub='')  
{
    return preg_replace($match,$exp,$sub);
}  
function par_rep_all($match='',$sub='',$exp='')
{
     return preg_match_all($match,$sub,$exp);
}
function par_rep_mt($match='',$sub='',$exp='')
{
     return preg_match($match,$sub,$exp);
}

function par_spt($pattern='',$sub='')
{
     return preg_split($pattern,$sub);
}

function par_rep_cb($match='',$exp='',$sub='')  
{
    return preg_replace_callback($match,$exp,$sub);
} 

?>

