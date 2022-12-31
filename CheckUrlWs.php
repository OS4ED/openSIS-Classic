<?php
$url = $_REQUEST['url'];
$data = array();
$exists = '';
$file_headers = '';
$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
$sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
$protocol = substr($sp, 0, strpos($sp, "/")) . $s;
$out=$protocol . "://" . $url;
$file_headers = @get_headers($out);

if(count($file_headers)>1)
{
    if($file_headers[0]!='' && strpos($file_headers[0], '404')) {
        $exists = 0;
    }
    else {
        $exists = 1;
    }
}
else 
{
    $exists = 0;
}
if($exists==1)
{
    $data['success']=1;
    $data['msg']=_validUrl;
    $data['host'] = $out;
}
else 
{
    $data['success']=0;
    $data['msg']=_invalidUrl;
    $data['host'] = $out;
}
echo json_encode($data);