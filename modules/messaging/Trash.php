<?php

DrawBC(""._messaging." > " . ProgramTitle());

if (isset($_REQUEST['msg']) && $_REQUEST['msg'] == 4) {
    echo'<p style=color:red>'._messageDeletedSucessfully.'.</p>';
}
$userName = User('USERNAME');
$user_dt = DBGet(DBQuery('SELECT USER_ID,PROFILE_ID FROM login_authentication WHERE USERNAME=\'' . $userName . '\''));
$user_dt = $user_dt[1];
if (isset($_REQUEST['modfunc']) && $_REQUEST['modfunc'] == 'delete') {
    if (is_countable($_REQUEST['mail']) && count($_REQUEST['mail']) != 0) {
        $count = count($_REQUEST['mail']);
        if ($count != 1)
            $row = "messages";
        else
            $row = "message";
        if (DeleteMail($count . ' ' . $row, 'delete', $_REQUEST['modname'], true)) {
            unset($_REQUEST['modfunc']);
            $id = array_keys($_REQUEST['mail']);
            foreach ($id as $idi => $idd) {
                $idd = explode("_", $idd);

                if ($idd[1] == 'SENT')
                    $sent[] = $idd[0];
                else
                    $recieved[] = $idd[0];
            }
            if (is_countable($sent) && count($sent) > 0) {
                $sent = implode(',', $sent);

                $arr = array();
                $qr = 'SELECT (SELECT username FROM login_authentication WHERE USER_ID=ISTRASH AND PROFILE_ID=' . $user_dt['PROFILE_ID'] . ') AS ISTRASH FROM msg_outbox WHERE MAIL_ID IN (' . $sent . ')';
                $fetch = DBGet(DBQuery($qr));
                foreach ($fetch as $key => $value) {
                    $s = $value['ISTRASH'];

                    $arr = explode(',', $s);


                    if (($key = array_search($userName, $arr)) !== false) {
                        unset($arr[$key]);
                        $update_to_user = implode(',', $arr);
                        $query = 'UPDATE msg_outbox SET FROM_USER=\'\' WHERE MAIL_ID IN (' . $sent . ')';
                        $fetch_ex = DBQuery($query);
                    }
                }
            }
            if (is_countable($recieved) && count($recieved) > 0) {
                $recieved = implode(',', $recieved);

                $arr = array();
                $qr = "select istrash from msg_inbox where mail_id IN ($recieved)";
                $fetch = DBGet(DBQuery($qr));

                foreach ($fetch as $key => $value) {
                    $s = $value['ISTRASH'];
                    "<br>";

                    $arr = explode(',', $s);


                    if (($key = array_search($userName, $arr)) !== false) {
                        unset($arr[$key]);
                        $update_to_user = implode(',', $arr);
                        $query = "update msg_inbox set istrash='$update_to_user' where mail_id IN ($recieved)";
                        $fetch_ex = DBQuery($query);
                    }
                }
            }
            echo "<script>window.location.href='Modules.php?modname=messaging/Trash.php&msg=4'</script>";
        }
    } else {
        PopTable('header', _alertMessage);
        echo "<CENTER><h4>"._pleaseSelectAtleastOneMessageToDelete."</h4><br><FORM action=$PHP_tmp_SELF METHOD=POST><INPUT type=button class='btn btn-primary' name=delete_cancel value="._ok." onclick='window.location=\"Modules.php?modname=messaging/Trash.php\"'></FORM></CENTER>";
        PopTable('footer');
        return false;
    }
}
if (isset($_REQUEST['modfunc']) && $_REQUEST['modfunc'] == 'body') {
    PopTable('header', _messageDetails);
    $mail_id = $_REQUEST['mail_id'];
    $mail_body = "select mail_body,to_user,from_user,mail_datetime,mail_attachment from msg_inbox where mail_id='$mail_id'";
    $mail_body_info = DBGet(DBQuery($mail_body));
    foreach ($mail_body_info as $k => $v) {
        echo "<table width='100%' style='width:650px'>
               <tr>
               <td align='left'><b>"._from.":</b> " . GetNameFromUserName($v['FROM_USER']) . "</td>
               <td align='right'><b>"._dateTime.":</b> " . $v['MAIL_DATETIME'] .
        "</tr>";
        if ($v['TO_CC_MULTIPLE'] != '') {
            echo "<tr>
                   <td align='left'>
                   <b>CC:</b> " . $v['TO_CC_MULTIPLE'] . "</td><td></td>
                   </tr>";
        }
        if ($v['TO_BCC_MULTIPLE'] != '') {
            $to_bcc_arr = explode(',', $v['TO_BCC_MULTIPLE']);
            if (in_array($userName, $to_bcc_arr)) {
                echo "<tr>
                        <td align='left'>'"._bcc."': " . $userName . "</td><td></td></tr>";
            }
        }
        if ($v['MAIL_ATTACHMENT'] != '') {
            
            
            
            echo "<tr>
                 <td align='left'>
                  "._attachment.": ";
//            $attach = explode(',', $v['MAIL_ATTACHMENT']);
            $v['MAIL_ATTACHMENT'] = rtrim($v['MAIL_ATTACHMENT'], ", ");
             $attach= DBGet(DBQuery('SELECT * FROM user_file_upload WHERE ID IN ('.$v['MAIL_ATTACHMENT'].')'));
            foreach ($attach as $user => $img) {
                
                
                
//                $img_pos = strrpos($img, '/');
//                $img_name[] = substr($img, $img_pos + 1, strlen($img));
//
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
//
//            for ($i = 0; $i < (count($attach)); $i++) {
//                $img_name[$i] = urlencode($img_name[$i]);
//                $img4[$i] = urlencode($img3[$i]);
//                echo "<a href='DownloadWindow.php?filename=$img_name[$i]&name=$img4[$i]' target='new' >" . $img3[$i] . "</a>";
//
//                echo '<br>&nbsp;&nbsp;&nbsp;<br>';
//            }
        
            
                echo "<a href='DownloadWindow.php?down_id=".$img['DOWNLOAD_ID']."'>".$img['NAME']."</a>";
             
              echo '<br>&nbsp;&nbsp;&nbsp;<br>';
            }
            
            
        }
        echo "<tr><td align='left' colspan='2'><br /><div class='mail_body'>" . str_replace('<a href=', '<a target="_blank" href=', $v['MAIL_BODY']) . "<br /></div></td></tr></table>";
    }
    echo "<p align='center'><a class='btn btn-primary' href='Modules.php?modname=messaging/Trash.php'>"._back."</a></p>";
    PopTable('footer');
}

if (!isset($_REQUEST['modfunc'])) {

    //PopTable('header', 'Trash');
    $link = array();
    $id = array();
    $arr = array();

    $qr = 'SELECT ISTRASH,MAIL_ID FROM msg_inbox';
    $fetch = DBGet(DBQuery($qr));

    foreach ($fetch as $key => $value) {
        $s = $value['ISTRASH'];

        $arr = explode(',', $s);


        if (in_array($userName, $arr)) {
            array_push($id, $value['MAIL_ID']);
        }
    }
    $count = count($id);
    if ($count > 0)
        $to_user_id = implode(',', $id);
    else
        $to_user_id = 'null';
    echo "<FORM name=sav id=sav action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=delete method=POST onsubmit=\'return a();\'>";
    $trash = 'SELECT *,\'RECIEVED\' AS STATUS FROM msg_inbox WHERE MAIL_ID IN (' . $to_user_id . ') ORDER BY MAIL_ID DESC';
    $trash_info = DBGet(DBQuery($trash));

    foreach ($trash_info as $key => $value) {
        if ($value['MAIL_ATTACHMENT'] != '') {
            $trash_info[$key]['MAIL_SUBJECT'] = $trash_info[$key]['MAIL_SUBJECT'] . "<img align='right' src='./assets/attachment.png'>";
        }
        $from_User = $value['FROM_USER'];
        $fromProfile = DBGet(DBQuery("Select * from login_authentication where username='$from_User'"));
        $fromProfileId = $fromProfile[1]['PROFILE_ID'];
        $fromUserId = $fromProfile[1]['USER_ID'];
        if ($fromProfileId != 3 || $fromProfileId != 4) {
            $nameQuery = 'SELECT CONCAT(FIRST_NAME,\' \', LAST_NAME) NAME FROM staff WHERE PROFILE_ID=' . $fromProfileId . ' AND STAFF_ID=' . $fromUserId;
        }
        if ($fromProfileId == 3) {
            $nameQuery = 'SELECT CONCAT(FIRST_NAME,\' \', LAST_NAME) NAME FROM students WHERE STUDENT_ID=' . $fromUserId;
        }
        if ($fromProfileId == 4) {
            $nameQuery = 'SELECT CONCAT(FIRST_NAME,\' \', LAST_NAME) NAME FROM people WHERE PROFILE_ID=' . $fromProfileId . ' and staff_id=' . $fromUserId;
        }
        $name = DBGet(DBQuery($nameQuery));
        $name = $name[1]['NAME'];
        $trash_info[$key]['FROM_USER'] = $name;
    }


//////////////////////////////////
//For Outbox                    //
///////////////////////////////// 
    $qr1 = 'SELECT (SELECT username FROM login_authentication WHERE USER_ID=ISTRASH AND PROFILE_ID=\'' . $user_dt['PROFILE_ID'] . '\') as ISTRASH,MAIL_ID FROM msg_outbox WHERE FROM_USER=\'' . $userName . '\' ';
    $fetch1 = DBGet(DBQuery($qr1));

    $id = '';
    foreach ($fetch1 as $key1 => $value1) {
        $s1 = $value1['ISTRASH'];

        $arr1 = explode(',', $s1);


        if (in_array($userName, $arr1)) {
            $id.=$value1['MAIL_ID'] . ',';
        }
    }
    if ($id != '')
        $to_user_id = substr($id, 0, -1);
    else
        $to_user_id = 'null';

    $trash1 = 'SELECT *,\'SENT\' AS STATUS FROM msg_outbox WHERE MAIL_ID IN (' . $to_user_id . ') ORDER BY MAIL_ID DESC';
    $trash_info1 = DBGet(DBQuery($trash1));

    foreach ($trash_info1 as $key1 => $value1) {
        if ($value1['MAIL_ATTACHMENT'] != '') {
            $trash_info1[$key1]['MAIL_SUBJECT'] = $trash_info1[$key1]['MAIL_SUBJECT'] . "<img align='right' src='./assets/attachment.png'>";
        }
        $fromProfile = DBGet(DBQuery('SELECT * FROM login_authentication WHERE USERNAME=\'' . $value1['FROM_USER'] . '\' '));
        $fromProfileId = $fromProfile[1]['PROFILE_ID'];
        if ($fromProfileId != 3 || $fromProfileId != 4) {
            $nameQuery = 'SELECT CONCAT(FIRST_NAME,\' \', LAST_NAME) NAME FROM staff WHERE PROFILE_ID=\'' . $fromProfileId . '\' AND STAFF_ID=\'' . $fromProfile[1]['USER_ID'] . '\'  ';
        }
        if ($fromProfileId == 3) {
            $nameQuery = 'SELECT CONCAT(FIRST_NAME,\' \', LAST_NAME) NAME FROM students WHERE STUDENT_ID=\'' . $fromProfile[1]['USER_ID'] . '\'  ';
        }
        if ($fromProfileId == 4) {
            $nameQuery = 'SELECT CONCAT(FIRST_NAME,\' \', LAST_NAME) NAME FROM people WHERE PROFILE_ID=\'' . $fromProfileId . '\' AND STAFF_ID=\'' . $fromProfile[1]['USER_ID'] . '\'  ';
        }
        $name = DBGet(DBQuery($nameQuery));
        $name = $name[1]['NAME'];
        $trash_info1[$key1]['FROM_USER'] = $name;
    }
    unset($key1);
    unset($value1);
    foreach ($trash_info1 as $key1 => $value1) {
        if (is_countable($trash_info) && count($trash_info) > 0)
            $trash_info[] = $value1;
        else
            $trash_info[1] = $value1;
    }

    echo "<div id='students' class='panel panel-default'>";

    $columns = array('FROM_USER' => _from,
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
    $link['MAIL_SUBJECT']['link'] = "Modules.php?modname=messaging/Trash.php&modfunc=body";
    $link['MAIL_SUBJECT']['variables'] = array('mail_id' => 'MAIL_ID');
    $link['remove']['link'] = "Modules.php?modname=messaging/Trash.php&modfunc=delete";

    foreach ($trash_info as $id => $value) {
        $extra['columns_before']['CHECKBOX'] = "<INPUT type=checkbox name=mail[" . $value['MAIL_ID'] . "_" . $value['STATUS'] . "] value=Y>";
        $trash_info[$id] = $extra['columns_before'] + $value;
    }
    if (is_countable($trash_info) && count($trash_info) != 0) {
        if (isset($userName)){
            $custom_header = '<h6 class="panel-title text-pink">'._trash.'</h6><div class="heading-elements"><button type=submit class="btn btn-default heading-btn" onclick=\'formload_ajax("sav");\' ><i class="fa fa-trash-o"></i> '._delete.'</button></div>';
        }
    }

    ListOutput($trash_info, $columns, '', '', $link, array(), array('search' =>false), TRUE, $custom_header);

    echo "</div>";
    echo '</FORM>';
    //PopTable('footer');
}
?>

