<?php
#**************************************************************************
#  openSIS is a free student information system for public and non-public 
#  schools from Open Solutions for Education, Inc. web: www.os4ed.com
#
#  openSIS is  web-based, open source, and comes packed with features that 
#  include student demographic info, scheduling, grade book, attendance, 
#  report cards, eligibility, transcripts, parent portal, 
#  student portal and more.   
#
#  Visit the openSIS web site at http://www.opensis.com to learn more.
#  If you have question regarding this system or the license, please send 
#  an email to info@os4ed.com.
#
#  This program is released under the terms of the GNU General Public License as  
#  published by the Free Software Foundation, version 2 of the License. 
#  See license.txt.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#***************************************************************************************

DrawBC(""._messaging." > " . ProgramTitle());

if (isset($_REQUEST['del']) && $_REQUEST['del'] == 'true') {
    echo'<div class="alert alert-success alert-bordered"><button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">'._close.'</span></button>'._messageDeletedSucessfully.'</div>';
}
$userName = User('USERNAME');
$user_dt = DBGet(DBQuery('SELECT USER_ID,PROFILE_ID FROM login_authentication WHERE USERNAME=\'' . $userName . '\''));
$user_dt = $user_dt[1];
if (isset($_REQUEST['modfunc']) && $_REQUEST['modfunc'] == 'trash') {
    if (is_countable($_REQUEST['mail']) && count($_REQUEST['mail']) != 0) {
        $count = count($_REQUEST['mail']);
        if ($count != 1)
            $row = "messages";
        else
            $row = "message";
        if (DeleteMail($count . ' ' . $row, 'delete', $_REQUEST['modname'])) {
            $id = array_keys($_REQUEST['mail']);
            $mail_id = implode(',', $id);
            $mail_delete = "UPDATE msg_outbox SET ISTRASH=" . $user_dt['USER_ID'] . " WHERE MAIL_ID IN($mail_id)";
            $mail_delete_ex = DBQuery($mail_delete);
            unset($_REQUEST['modfunc']);
            echo "<script>load_link('Modules.php?modname=messaging/SentMail.php&del=true')</script>";
        }
    } else {
        echo '<BR>';
        PopTable('header', _alertMessage);
        echo "<CENTER><h4>"._pleaseSelectAtleastOneMessageToDelete."</h4><br><FORM action=$PHP_tmp_SELF METHOD=POST><INPUT type=button class='btn btn-primary' name=delete_cancel value="._ok." onclick='window.location=\"Modules.php?modname=messaging/SentMail.php\"'></FORM></CENTER>";
        PopTable('footer');
        return false;
    }
}
if (isset($_REQUEST['modfunc']) && $_REQUEST['modfunc'] == 'body') {
    //PopTable('header', _messageDetails);
    echo '<div class="panel panel-default">';
    echo '<div class="panel-body">';

    $mail_id = $_REQUEST['mail_id'];
    $mail_body = "select mail_body,mail_Subject,to_user,to_cc,to_bcc,mail_datetime,from_user,mail_attachment,to_grpName from msg_outbox where mail_id='$mail_id'";
    $mail_body_info = DBGet(DBQuery($mail_body));

    foreach ($mail_body_info as $k => $v) {
        $to_user = explode(",", $v['TO_USER']);
        $v['TO_USER'] = implode(", ", $to_user);

        echo '<h3 class="no-margin-top"><a href="Modules.php?modname=messaging/SentMail.php" class="btn btn-icon"><i class="icon-square-left"></i></a> &nbsp; &nbsp;' . $v['MAIL_SUBJECT'] . '</h3>';
        echo '<hr class="no-margin-top"/>';

        echo '<div class="media">';
        echo '<div class="media-left"><img class="img-circle" src="assets/images/placeholder.jpg" alt="" /></div>';
        echo '<div class="media-body">';
        echo '<div class="pull-right"><div class="input-group-btn">';
        echo '<a href="javascript:void(0);" class="btn btn-default btn-xs" disabled="disabled"><i class="icon-calendar3"></i> ' . $v['MAIL_DATETIME'] . '</a>';
        echo '</div></div>';

        echo '<h6 class="media-heading text-bold">' . GetNameFromUserName($v['FROM_USER']) . '</h6>';
        echo '<div class="media-annotation dropdown">To : ';

        if (trim($v['TO_GRPNAME']) == "") {
            $toArr = explode(",", $v['TO_USER']);
            if (count($toArr) == 1) {
                echo GetNameFromUserName($v['TO_USER']);
            } else {
                $toMultiple = "";
                foreach ($toArr as $name => $value) {
                    $toName = GetNameFromUserName(trim($value));
                    if ($toMultiple == "")
                        $toMultiple = $toName;
                    else
                        $toMultiple.=", " . $toName;
                }
                echo $toMultiple;
            }
        }
        else {
            echo $v['TO_GRPNAME'];
        }
        if ($v['TO_CC'] != '' || $v['TO_BCC'] != '') {
            echo ' &nbsp;<a href="javascript:void(0)" class="btn btn-default btn-xs dropdown-toggle btn-icon" data-toggle="dropdown"><i class="fa fa-caret-down"></i></a>';
            echo '<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">';
            if ($v['TO_CC'] != '') {
                echo "<li class=\"pl-10 pr-10\"><b>CC:</b> ";
                $TOCC_Arr = explode(",", $v['TO_CC']);
                $toMultipleCC = "";
                foreach ($TOCC_Arr as $name => $value) {
                    $toCCName = GetNameFromUserName(trim($value));
                    if ($toMultipleCC == "")
                        $toMultipleCC = $toCCName;
                    else
                        $toMultipleCC.=", " . $toCCName;
                }
                echo $toMultipleCC;
                echo "</li>";
            }
            if ($v['TO_BCC'] != '') {
                $to_bcc_arr = explode(',', $v['TO_BCC']);
                echo "<li class=\"pl-10 pr-10\"><b>BCC:</b> ";
                $TOBCC_Arr = explode(",", $v['TO_BCC']);
                $toMultipleBCC = "";
                foreach ($TOBCC_Arr as $name => $value) {
                    $toBCCName = GetNameFromUserName(trim($value));
                    if ($toMultipleBCC == "")
                        $toMultipleBCC = $toBCCName;
                    else
                        $toMultipleBCC.=", " . $toBCCName;
                }
                echo $toMultipleBCC;
                echo "</li>";
            }
            echo '</ul>';
        }
        echo '</div>'; //.media-annotation
        
        echo '<div class="mt-20">' . str_replace('<a href=', '<a target="_blank" href=', $v['MAIL_BODY']) . '</div>';
        
        
        if ($v['MAIL_ATTACHMENT'] != '') {
            echo '<hr/>';
            echo '<h6 class="text-bold"><i class="icon-attachment2"></i> '._attachments.'</h6>';
            
//            $attach = explode(',', $v['MAIL_ATTACHMENT']);
            
            $attach= DBGet(DBQuery('SELECT * FROM user_file_upload WHERE ID IN ('.substr($v['MAIL_ATTACHMENT'],0,-1).')'));
            
            foreach ($attach as $user => $img) {
                
                
//                $img_pos = strrpos($img, '/');
//                $img_name[] = substr($img, $img_pos + 1, strlen($img));
//                $pos = strpos($img, '_');
//
//                $img_src[] = substr($img, $pos + 1, strlen($img));
//                for ($i = 0; $i < (count($img_src)); $i++) {
//                    $img1 = $img_src[$i];
//                    $m = array_keys(str_word_count($img1, 2));
//                    $a = $m[0];
//                    $img3[$i] = substr($img1, $a, strlen($img1));
//                }
//            }
//            for ($i = 0; $i < (count($attach)); $i++) {
//                $img_name[$i] = urlencode($img_name[$i]);
//                $img4[$i] = urlencode($img3[$i]);
//
//                echo (($i>0)?', ':'')."<a href='DownloadWindow.php?filename=$img_name[$i]&name=$img4[$i]' target='new' >" . $img3[$i] . "</a>";
//            }
            echo "<a href='DownloadWindow.php?down_id=".$img['DOWNLOAD_ID']."'>".$img['NAME']."</a>";
            echo '<br>&nbsp;&nbsp;&nbsp;<br>';
        }
        }
        
        echo '</div>'; //.media-body
        echo '</div>'; //.media

    }

    echo '</div>'; //.panel-body
    echo '</div>'; //.panel
    //PopTable('footer');
}




if (!isset($_REQUEST['modfunc'])) {
    //PopTable('header', 'Sent Message');
    echo "<FORM name=sav id=sav action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=trash method=POST>";
    $link = array();
    $extra = array();
    $outbox = "SELECT CASE WHEN to_cc is null THEN to_user ELSE concat( to_user, ' ', to_cc ) END AS TO1, msg_outbox.* FROM msg_outbox where from_user='$userName' AND istrash is NULL order by(mail_id) desc";
    $outbox_info = DBGet(DBQuery($outbox));
    foreach ($outbox_info as $id => $value) {
        if (trim($outbox_info[$id]['TO_GRPNAME']) != "") {
            if (trim($outbox_info[$id]['TO_CC']) == "")
                $outbox_info[$id]['TO1'] = $outbox_info[$id]['TO_GRPNAME'];
            else
                $outbox_info[$id]['TO1'] = $outbox_info[$id]['TO_GRPNAME'] . ',' . $outbox_info[$id]['TO_CC'];
        }
        else {
            $user = explode(' ', $outbox_info[$id]['TO1']);
            if ($user[1]) {
                $outbox_info[$id]['TO1'] = $user[0] . ',' . $user[1];
            }
        }
        $TOMULTIPLE = "";
        $TOARR = explode(",", $outbox_info[$id]['TO1']);
        foreach ($TOARR as $key => $v) {
            $add;
            if ($v == $outbox_info[$id]['TO_GRPNAME']) {
                $add = $v;
            } else {
                $add = GetNameFromUserName($v);
            }
            if ($TOMULTIPLE == "")
                $TOMULTIPLE = $add;
            else
                $TOMULTIPLE.= " ," . $add;
        }
        $outbox_info[$id]['TO1'] = $TOMULTIPLE;
        if ($value['MAIL_ATTACHMENT'] != '') {
            $outbox_info[$id]['MAIL_SUBJECT'] = $outbox_info[$id]['MAIL_SUBJECT'] . "<img align='right' src='./assets/attachment.png'>";
        }
    }
    echo '<div id="students" class="panel panel-default" >';
    $columns = array('TO1' => _to,
     'MAIL_SUBJECT' => _subject,
     'MAIL_DATETIME' => _dateTime,
    );
    $extra['SELECT'] = ",Concat(NULL) AS CHECKBOX";
    $extra['LO_group'] = array('MAIL_ID');
    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAll(this.form,this.form.controller.checked,\'mail\');"><A>');
    $extra['new'] = true;
    if (is_array($extra['columns_before'])) {
        $LO_columns = $extra['columns_before'] + $columns;
        $columns = $LO_columns;
    }
    $link['MAIL_SUBJECT']['link'] = "Modules.php?modname=messaging/SentMail.php&modfunc=body";
    $link['MAIL_SUBJECT']['variables'] = array('mail_id' => 'MAIL_ID');
    $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=trash";
    $link['MAIL_SUBJECT']['variables'] += array('mail_id' => 'MAIL_ID');
    foreach ($outbox_info as $id => $value) {
        $extra['columns_before']['CHECKBOX'] = "<INPUT type=checkbox name=mail[" . $value['MAIL_ID'] . "] value=Y>";
        $outbox_info[$id] = $extra['columns_before'] + $value;
    }
    if (count($outbox_info) != 0) {
        if (isset($userName)){
            $custom_header = '<h6 class="panel-title text-pink text-uppercase">'._sentMessage.'</h6><div class="heading-elements"><button type=submit class="btn btn-default heading-btn" onclick=\'formload_ajax("sav");\' ><i class="fa fa-trash-o"></i> '._delete.'</button></div>';
            //echo '<table align="center" width="94%"><tr><td align="right"><INPUT type=submit class=delete_mail value='._delete.' onclick=\'formload_ajax("sav");\' ></td></tr></table>';
        }
    }
    echo "";
    ListOutput($outbox_info, $columns, '', '', $link, array(), array('search' =>false), TRUE, $custom_header);
    echo "</div>";
    echo '</FORM>';
    
    //PopTable('footer');
}




if (isset($_REQUEST['modfunc']) && $_REQUEST['modfunc'] == 'save') {
    $mod_file = $_REQUEST['name'];
    $_REQUEST['filename'] = str_replace("\\", " ", $_REQUEST['filename']);
    $mod_file = str_replace("\\", " ", $mod_file);
    if (isset($_REQUEST['filename'])) {
        set_time_limit(0);
        $file_path = './assets/' . $_REQUEST['filename'];
        output_file($file_path, '' . $_REQUEST['filename'] . '', 'text/plain', $mod_file);
    }
}

function output_file($file, $name, $mime_type = '', $mod_file) {
    if (!is_readable($file))
        die(''._fileNotFoundOrInaccessible.'!');

    $size = filesize($file);
    $name = rawurldecode($name);
    $known_mime_types = array(
        "pdf" => "application/pdf",
        "txt" => "text/plain",
        "html" => "text/html",
        "htm" => "text/html",
        "exe" => "application/octet-stream",
        "zip" => "application/zip",
        "doc" => "application/msword",
        "docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "xls" => "application/vnd.ms-excel",
        "ppt" => "application/vnd.openxmlformats-officedocument.presentationml.presentation", //application/vnd.ms-powerpoint",
        "pptx" => "application/vnd.openxmlformats-officedocument.presentationml.presentation", //application/vnd.ms-powerpoint",
        "gif" => "image/gif",
        "png" => "image/png",
        "jpeg" => "image/jpg",
        "jpg" => "image/jpg",
        "php" => "text/plain"
    );
    if ($mime_type == '') {
        $file_extension = strtolower(substr(strrchr($file, "."), 1));
        if (array_key_exists($file_extension, $known_mime_types)) {
            $mime_type = $known_mime_types[$file_extension];
        } else {
            $mime_type = "application/force-download";
        };
    };

    @ob_end_clean();


    if (ini_get('zlib.output_compression'))
        ini_set('zlib.output_compression', 'Off');
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . $mod_file . '"');
    header("Content-Transfer-Encoding: binary");
    header('Accept-Ranges: bytes');
    header("Cache-control: private");
    header('Pragma: private');
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    if (isset($_SERVER['HTTP_RANGE'])) {
        list($a, $range) = explode("=", $_SERVER['HTTP_RANGE'], 2);
        list($range) = explode(",", $range, 2);
        list($range, $range_end) = explode("-", $range);
        $range = intval($range);
        if (!$range_end) {
            $range_end = $size - 1;
        } else {
            $range_end = intval($range_end);
        }
        $new_length = $range_end - $range + 1;
        header("HTTP/1.1 206 Partial Content");
        header("Content-Length: $new_length");
        header("Content-Range: bytes $range-$range_end/$size");
    } else {
        $new_length = $size;
        header("Content-Length: " . $size);
    }
    $chunksize = 1 * (1024 * 1024);
    $bytes_send = 0;
    if ($file = fopen($file, 'r')) {
        if (isset($_SERVER['HTTP_RANGE']))
            fseek($file, $range);

        while (!feof($file) &&
        (!connection_aborted()) &&
        ($bytes_send < $new_length)
        ) {
            $buffer = fread($file, $chunksize);
            print($buffer);
            flush();
            $bytes_send += strlen($buffer);
        }
        fclose($file);
    } else
        die('Error - can not open file.');
    die();
}
?>

