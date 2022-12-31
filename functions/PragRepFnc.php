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
     if($match[0] == '/' && $match[strlen($match) - 1] == '/')
     {
         $match = '~'.trim($match, '/').'~';
     }
     else
     {
          $match = $match;
     }
    return preg_replace_callback($match,'exp_match',$sub);
    //return $sub;
    //return preg_replace($match,$exp,$sub);
}

function exp_match($matches)
{
  // as usual: $matches[0] is the complete match
  // $matches[1] the match for the first subpattern
  // enclosed in '(...)' and so on
  //return $matches[1].($matches[2]+1);
  return $matches[0];
}
?>