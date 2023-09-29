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

include('../../RedirectModulesInc.php');
DrawBC(""._users." > " . ProgramTitle());
if (clean_param($_REQUEST['values'], PARAM_NOTAGS) && ($_POST['values'] || $_REQUEST['ajax'])) {
    if (clean_param($_REQUEST['tab'], PARAM_ALPHAMOD) == 'password') {
        //print_r($_REQUEST);exit;
        $column_name = 'PASSWORD';
        $pass_current = paramlib_validation($column_name, $_REQUEST['values']['current']);
        $pass_new = paramlib_validation($column_name, $_REQUEST['values']['new']);
        $pass_verify = paramlib_validation($column_name, $_REQUEST['values']['verify']);

        /*$pass_new_after = md5($pass_new);*/
        $pass_new_after = $pass_new;

        $profile_RET = DBGet(DBQuery('SELECT s.PROFILE FROM staff s , staff_school_relationship ssr WHERE s.STAFF_ID=ssr.STAFF_ID AND s.STAFF_ID=\'' . User('STAFF_ID') . '\' AND ssr.SYEAR=\'' . UserSyear() . '\''));

        if (User('PROFILE') == 'parent')
        {
            /*$sql = DBQuery('SELECT l.PASSWORD FROM people p,login_authentication l WHERE l.USER_ID=\'' . User('STAFF_ID') . '\' AND l.USER_ID=p.STAFF_ID AND l.password=\'' . $pass_new_after . '\' AND l.PROFILE_ID=p.PROFILE_ID');*/

            //code for checking password in table
            $parentlist = DBGet(DBQuery('SELECT l.PASSWORD FROM people p,login_authentication l WHERE l.USER_ID=\'' . User('STAFF_ID') . '\' AND l.USER_ID=p.STAFF_ID  AND l.PROFILE_ID=p.PROFILE_ID'));
            $user_password_count=0;
            foreach($parentlist as $val)
            {
                $parent_password = $val['PASSWORD'];
                $password_status = VerifyHash($pass_new_after,$parent_password);
                if($password_status==1) 
                    { 
                       $user_password_count=$user_password_count+1; 
                    }
            }
            //end 
        }
        else
        {
            /*$sql = DBQuery('SELECT l.PASSWORD FROM staff s , staff_school_relationship ssr,login_authentication l where l.USER_ID=\'' . User('STAFF_ID') . '\' AND l.USER_ID=s.STAFF_ID AND l.password=\'' . $pass_new_after . '\'  AND ssr.STAFF_ID=s.STAFF_ID AND ssr.SYEAR=\'' . UserSyear() . '\' AND l.PROFILE_ID=s.PROFILE_ID');*/

            //code for checking password in table
            $userslist = DBGet(DBQuery('SELECT l.PASSWORD FROM staff s , staff_school_relationship ssr,login_authentication l where l.USER_ID=\'' . User('STAFF_ID') . '\' AND l.USER_ID=s.STAFF_ID AND ssr.STAFF_ID=s.STAFF_ID AND ssr.SYEAR=\'' . UserSyear() . '\' AND l.PROFILE_ID=s.PROFILE_ID'));
            $user_password_count=0;
            foreach($userslist as $val)
            {
                $parent_password = $val['PASSWORD'];
                $password_status = VerifyHash($pass_new_after,$parent_password);
                if($password_status==1) 
                    { 
                       $user_password_count=$user_password_count+1; 
                    }
            }
            //end
        }

        /*$number = $sql->num_rows;*/
        $number = $user_password_count;

        if ($pass_new != $pass_verify)
            $error = ''._yourNewPasswordsDidNotMatch.'.';

        elseif ($number > 0) {
                $error = ''._thisPasswordIsAlredyTake.'n';
        } else {
            if (User('PROFILE') == 'parent') {
                $password_RET = DBGet(DBQuery('SELECT l.PASSWORD FROM people p,login_authentication l WHERE l.USER_ID=\'' . User('STAFF_ID') . '\' AND l.USER_ID=p.STAFF_ID AND l.PROFILE_ID=p.PROFILE_ID'));
            } else {
                $password_RET = DBGet(DBQuery('SELECT l.PASSWORD FROM staff s , staff_school_relationship ssr,login_authentication l where l.USER_ID=\'' . User('STAFF_ID') . '\' AND l.USER_ID=s.STAFF_ID AND ssr.STAFF_ID=s.STAFF_ID AND ssr.SYEAR=\'' . UserSyear() . '\' AND l.PROFILE_ID=s.PROFILE_ID'));
            }

            $user_old_password = $password_RET[1]['PASSWORD'];
            $old_password_status = VerifyHash($pass_current,$user_old_password);

            /*if ($pass_current != '' && $password_RET[1]['PASSWORD'] != md5($pass_current))*/
            if ($pass_current != '' && $old_password_status==0)
                {
                    $error = ''._yourCurrentPasswordWasIncorrect.'.';
                }
            elseif ($pass_current == '')
                {
                    $error = ''._yourCurrentPasswordCanNotBeBlank.'.';
                }
            else 
                {
                    DBQuery('UPDATE login_authentication SET PASSWORD=\'' . GenerateNewHash($pass_new) . '\' WHERE USER_ID=\'' . User('STAFF_ID') . '\' AND PROFILE_ID=\'' . User('PROFILE_ID') . '\' ');
                    $note = ''._yourNewPasswordWasSaved.'.';
                }
        }
    } else {
        $current_RET = DBGet(DBQuery('SELECT TITLE,VALUE,PROGRAM FROM program_user_config WHERE USER_ID=\'' . User('STAFF_ID') . '\' AND PROGRAM IN (\'' . 'Preferences' . '\',\'' . 'StudentFieldsSearch' . '\',\'' . 'StudentFieldsView' . '\') '), array(), array('PROGRAM', 'TITLE'));

        if ($_REQUEST['tab'] == 'student_listing' && $_REQUEST['values']['Preferences']['SEARCH'] != 'Y')
            $_REQUEST['values']['Preferences']['SEARCH'] = 'N';
        if ($_REQUEST['tab'] == 'student_listing' && $_REQUEST['values']['Preferences']['DEFAULT_FAMILIES'] != 'Y')
            $_REQUEST['values']['Preferences']['DEFAULT_FAMILIES'] = 'N';
        if ($_REQUEST['tab'] == 'student_listing' && $_REQUEST['values']['Preferences']['DEFAULT_ALL_SCHOOLS'] != 'Y')
            $_REQUEST['values']['Preferences']['DEFAULT_ALL_SCHOOLS'] = 'N';
        if ($_REQUEST['tab'] == 'display_options' && $_REQUEST['values']['Preferences']['HIDDEN'] != 'Y')
            $_REQUEST['values']['Preferences']['HIDDEN'] = 'N';
        if ($_REQUEST['tab'] == 'display_options' && $_REQUEST['values']['Preferences']['HIDE_ALERTS'] != 'Y')
            $_REQUEST['values']['Preferences']['HIDE_ALERTS'] = 'N';
        if ($_REQUEST['tab'] == 'display_options' && $_REQUEST['values']['Preferences']['THEME'] != $current_RET['Preferences']['THEME'][1]['VALUE']) {
//            echo '<script language=JavaScript>';
//            echo 'parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;';
//            echo "parent.help.location='Bottom.php?modcat=users&modname=$_REQUEST[modname]';";
//            echo '</script>';
            $flag = 1;
        }
        if (clean_param($_REQUEST['tab'], PARAM_ALPHAMOD) == 'student_fields') {
            DBQuery('DELETE FROM program_user_config WHERE USER_ID=\'' . User('STAFF_ID') . '\' AND PROGRAM IN (\'' . 'StudentFieldsSearch' . '\',\'' . 'StudentFieldsSearchable' . '\',\'' . 'StudentFieldsView' . '\')');

            foreach ($_REQUEST['values'] as $program => $values) {
                foreach ($values as $name => $value) {
                    if (isset($value))
                        DBQuery('INSERT INTO program_user_config (USER_ID,PROGRAM,TITLE,VALUE) values(\'' . User('STAFF_ID') . '\',\'' . $program . '\',\'' . $name . '\',\'' . $value . '\')');
                }
            }
        }
        else {
            foreach ($_REQUEST['values'] as $program => $values) {
                foreach ($values as $name => $value) {
                    if (!$current_RET[$program][$name] && $value != '')
                        DBQuery('INSERT INTO program_user_config (USER_ID,PROGRAM,TITLE,VALUE) values(\'' . User('STAFF_ID') . '\',\'' . $program . '\',\'' . $name . '\',\'' . $value . '\')');
                    elseif ($value != '')
                        DBQuery('UPDATE program_user_config SET VALUE=\'' . $value . '\' WHERE USER_ID=\'' . User('STAFF_ID') . '\' AND PROGRAM=\'' . $program . '\' AND TITLE=\'' . $name . '\'');
                    else
                        DBQuery('DELETE FROM program_user_config WHERE USER_ID=\'' . User('STAFF_ID') . '\' AND PROGRAM=\'' . $program . '\' AND TITLE=\'' . $name . '\'');
                }
            }
        }
        // So Preferences() will get the new values
        unset($_openSIS['Preferences']);
    }
    unset($_REQUEST['values']);
    unset($_SESSION['_REQUEST_vars']['values']);
//        if($flag==1)
//        {
////            echo "<script>document.forms[0].submit();</script>";
//             echo "<script>window.location.href='Modules.php?modname=users/Preferences.php'</script>";               
//    
//            }
//        else 
    //echo "<script>window.location.href='Modules.php?modname=users/Preferences.php'</script>";
//            header("Location:Modules.php?modname=users/Preferences.php");
}

unset($_REQUEST['search_modfunc']);
unset($_SESSION['_REQUEST_vars']['search_modfunc']);

if (!$_REQUEST['modfunc']) {
    echo '<input type=hidden id=json_encoder value=' . json_encode(array("family", "all_school")) . ' />';
    $current_RET = DBGet(DBQuery('SELECT TITLE,VALUE,PROGRAM FROM program_user_config WHERE USER_ID=\'' . User('STAFF_ID') . '\' AND PROGRAM IN (\'' . 'Preferences' . '\',\'' . 'StudentFieldsSearchable' . '\',\'' . 'StudentFieldsSearch' . '\',\'' . 'StudentFieldsView' . '\') '), array(), array('PROGRAM', 'TITLE'));

    if (!$_REQUEST['tab'])
        $_REQUEST['tab'] = 'display_options';

    echo "<FORM class=\"form-horizontal\" name=perf_form id=perf_form action=Modules.php?modname=$_REQUEST[modname]&amp;tab=$_REQUEST[tab] method=POST onload='document.forms[0].submit;'>";


    if (User('PROFILE') == 'admin' || User('PROFILE') == 'teacher')
        $tabs = array(array('title' => ''._displayOptions.'', 'link' => "Modules.php?modname=$_REQUEST[modname]&amp;tab=display_options"), array('title' => ''._studentListing.'', 'link' => "Modules.php?modname=$_REQUEST[modname]&amp;tab=student_listing"), array('title' => ''._password.'', 'link' => "Modules.php?modname=$_REQUEST[modname]&amp;tab=password"), array('title' => _studentFields, 'link' => "Modules.php?modname=$_REQUEST[modname]&amp;tab=student_fields"));
    elseif (User('PROFILE') == 'parent')
        $tabs = array(array('title' => ''._displayOptions.'', 'link' => "Modules.php?modname=$_REQUEST[modname]&amp;tab=display_options"), array('title' => ''._password.'', 'link' => "Modules.php?modname=$_REQUEST[modname]&amp;tab=password"));
    else
        $tabs = array(array('title' => ''._displayOptions.'', 'link' => "Modules.php?modname=$_REQUEST[modname]&amp;tab=display_options"), array('title' => ''._password.'', 'link' => "Modules.php?modname=$_REQUEST[modname]&amp;tab=password"), array('title' => _studentFields, 'link' => "Modules.php?modname=$_REQUEST[modname]&amp;tab=student_fields"));

    $_openSIS['selected_tab'] = "Modules.php?modname=$_REQUEST[modname]&amp;tab=" . $_REQUEST['tab'];
    PopTable('header', $tabs);



    
    if (clean_param($_REQUEST['tab'], PARAM_ALPHAMOD) == 'student_listing') {
        echo '<div class="row">';
        echo '<div class="col-md-7">';
        echo '<div class="form-group"><label class="control-label col-md-3 text-right">'._studentName.'</label><div class="col-md-9"><label class="radio-inline"><INPUT type=radio name=values[Preferences][NAME] value=Common' . ((Preferences('NAME') == 'Common') ? ' CHECKED' : '') . '> '._commonName.'</label><label class="radio-inline"><INPUT type=radio name=values[Preferences][NAME] value=Given' . ((Preferences('NAME') == 'Given') ? ' CHECKED' : '') . '> '._givenName.'</label></div></div>';
        echo '<div class="form-group"><label class="control-label col-md-3 text-right">'._studentSorting.'</label><div class="col-md-9"><label class="radio-inline"><INPUT type=radio name=values[Preferences][SORT] value=Name' . ((Preferences('SORT') == 'Name') ? ' CHECKED' : '') . '> '._name.'</label><label class="radio-inline"><INPUT type=radio name=values[Preferences][SORT] value=Grade' . ((Preferences('SORT') == 'Grade') ? ' CHECKED' : '') . '> '._gradeName.'</label></div></div>';
        echo '<div class="form-group"><label class="control-label col-md-3 text-right">'._fileExportType.'</label><div class="col-md-9"><label class="radio-inline"><INPUT type=radio name=values[Preferences][DELIMITER] value=Tab' . ((Preferences('DELIMITER') == 'Tab') ? ' CHECKED' : '') . '> '._tabDelimitedExcel.'</label><label class="radio-inline"><INPUT type=radio name=values[Preferences][DELIMITER] value=CSV' . ((Preferences('DELIMITER') == 'CSV') ? ' CHECKED' : '') . '>'._csvOpenOffice.'</label></div></div>';
        echo '</div>'; //.col-md-6
        echo '<div class="col-md-5">';
        echo '<div class="checkbox checkbox-switch switch-success switch-xs p-b-10"><label><INPUT type=checkbox onClick="toggle_div_visibility(\'show_other_options\',this,\'json_encoder\');" name=values[Preferences][SEARCH] value=Y' . ((Preferences('SEARCH') == 'Y') ? ' CHECKED' : '') . '><span></span> '._displayStudentSearchScreen.'</label></div>';
        if (User('PROFILE') == 'admin') {
            echo '<div id="show_other_options" ' . ((Preferences('SEARCH') == 'Y') ? 'style="display:inline-block"' : 'style="display:none"') . '>';
            echo '<div class="checkbox checkbox-switch switch-success switch-xs p-b-10"><label><INPUT type=checkbox id="family" name=values[Preferences][DEFAULT_FAMILIES] value=Y' . ((Preferences('DEFAULT_FAMILIES') == 'Y') ? ' CHECKED' : '') . '><span></span> '._groupByFamilyByDefault.'</label></div>';
            echo '<div class="checkbox checkbox-switch switch-success switch-xs"><label><INPUT type=checkbox id="all_school" name=values[Preferences][DEFAULT_ALL_SCHOOLS] value=Y' . ((Preferences('DEFAULT_ALL_SCHOOLS') == 'Y') ? ' CHECKED' : '') . '><span></span> '._searchAllSchoolsByDefault.'</label></div>';
            echo '</div>';
        }
        echo '</div>'; //.col-md-6
        echo '</div>'; //.row
    }

    if (clean_param($_REQUEST['tab'], PARAM_ALPHAMOD) == 'display_options') {
        echo '<div class="form-group">';

//        echo '<div class="col-md-4">';
//        echo '<label>Theme</label>';
//        if ($handle = opendir($openSISPath . 'themes/')) {
//            while (false !== ($file = readdir($handle))) {
//                if ($file != "." && $file != ".." && !in_array($file, $IgnoreFiles)) {
//                    echo '<label class="radio radio-inline"><INPUT type=radio name=values[Preferences][THEME] value=' . $file . ((Preferences('THEME') == $file) ? ' CHECKED' : '') . '> ' . $file . '</label>';
//                    $count++;
//                }
//            }
//            closedir($handle);
//        }
//        echo '</div>'; //.col-md-4
//        $colors = array('#85e1ff', '#96f3c8', '#e9f396', '#f3bb96', '#f396a7');
//        echo '<div class="col-md-4">';
//        echo '<label class="control-label">Highlight Color</label>';
//        echo '<div class="radio">';
//        foreach ($colors as $color)
//            echo '<label class="radio-inline" style="background: ' . $color . '"><INPUT type=radio name=values[Preferences][HIGHLIGHT] value=' . $color . ((Preferences('HIGHLIGHT') == $color) ? ' CHECKED' : '') . '></label>';
//
//        echo '</div>'; //.radio
//        echo '</div>'; //.col-md-4


        echo '<div class="col-md-4">';
        echo '<label>'._dateFormat.'</label><div class="form-inline"><SELECT class="form-control" name=values[Preferences][MONTH]>';

        $values = array('F', 'M', 'n');
        foreach ($values as $value)
            echo '<OPTION value=' . $value . ((Preferences('MONTH') == $value) ? ' SELECTED' : '') . '>' . date($value) . '</OPTION>';
        echo '</SELECT> ';
        echo '<SELECT class="form-control" name=values[Preferences][DAY]>';

        $values = array('j', 'jS');
        foreach ($values as $value)
            echo '<OPTION value=' . $value . ((Preferences('DAY') == $value) ? ' SELECTED' : '') . '>' . date($value) . '</OPTION>';
        echo '</SELECT> ';
        echo '<SELECT class="form-control" name=values[Preferences][YEAR]>';
        $values = array('Y', 'y');
        foreach ($values as $value)
            echo '<OPTION value="' . $value . '"' . ((Preferences('YEAR') == $value) ? ' SELECTED' : '') . '>' . date($value) . '</OPTION>';
        echo '</SELECT>';
        echo '</div>'; //.form-horizontal
        echo '</div>'; //.col-md-4

        echo '<div class="col-md-4">';
        echo '<label class="control-label">'._disableLoginAlerts.'</label>';
        echo '<div class="m-t-10"><label class="checkbox checkbox-inline checkbox-switch switch-success switch-xs"><INPUT type=checkbox name=values[Preferences][HIDE_ALERTS] value=Y' . ((Preferences('HIDE_ALERTS') == 'Y') ? ' CHECKED' : '') . '><span></span> <p class="switch-fake-title"></p></label></div>';
        echo '</div>'; //.col-md-4

        echo '<div class="col-md-4">';
        echo '<label class="control-label">'._displayDataUsingHiddenFields.'</label>';
        echo '<div class="m-t-10"><label class="checkbox checkbox-inline checkbox-switch switch-success switch-xs"><INPUT type=checkbox name=values[Preferences][HIDDEN] value=Y' . ((Preferences('HIDDEN') == 'Y') ? ' CHECKED' : '') . '><span></span> <p class="switch-fake-title"></p></label></div>';
        echo '</div>'; //.col-md-4

        echo '</div>'; //.form-group        
    }

    if (clean_param($_REQUEST['tab'], PARAM_ALPHAMOD) == 'password') {
        echo '<div id=divErr style=display:none></div>';
        if ($error)
            echo ErrorMessage(array($error));
        if ($note)
            echo ErrorMessage(array($note), 'note');
        echo '<div class="row">';
        echo '<div class="col-md-6"><div class="form-group clearfix"><label class="control-label col-md-4">'._currentPassword.'</label><div class="col-md-8"><div class="stu_age_holder"><INPUT placeholder="'._currentPassword.'" type=password class=form-control id=values[current] name=values[current] AUTOCOMPLETE=off><div class="p-t-10 text-right stu_age"><i id="password_view" class="fa fa-eye-slash cursor-pointer" onClick="togglePasswordView(this, \'values[current]\')"></i></div></div></div></div></div>';
        echo '</div>'; //.row
        echo '<div class="row">';
        echo '<div class="col-md-6"><div class="form-group clearfix"><label class="control-label col-md-4">'._newPassword.'</label><div class="col-md-8"><div class="stu_age_holder"><INPUT type=password placeholder="'._newPassword.'" id=new_pass class=form-control name=values[verify] AUTOCOMPLETE=off onkeyup=passwordStrength(this.value);passwordMatch();><div class="p-t-10 text-right stu_age"><i id="password_view" class="fa fa-eye-slash cursor-pointer" onClick="togglePasswordView(this, \'new_pass\')"></i></div></div></div></div></div><div class="col-md-3"><div class="help-block text-white p-10" id=passwordStrength></div></div>';
        echo '</div>'; //.row
        echo '<div class="row">';
        echo '<div class="col-md-6"><div class="form-group clearfix"><label class="control-label col-md-4">'._verifyNewPassword.'</label><div class="col-md-8"><div class="stu_age_holder"><INPUT placeholder="'._verifyNewPassword.'" type=password id=ver_pass class=form-control name=values[new] AUTOCOMPLETE=off onkeyup=passwordMatch()><div class="p-t-10 text-right stu_age"><i id="password_view" class="fa fa-eye-slash cursor-pointer" onClick="togglePasswordView(this, \'ver_pass\')"></i></div></div></div></div></div><div class="col-md-3"><div class="help-block text-white p-10" id=passwordMatch></div></div>';
        echo '</div>';
    }

    if (clean_param($_REQUEST['tab'], PARAM_ALPHAMOD) == 'student_fields') {
        if (User('PROFILE_ID') != '')
            $custom_fields_RET = DBGet(DBQuery('SELECT CONCAT(\'' . '<b>' . '\',sfc.TITLE,\'' . '</b>' . '\') AS CATEGORY,cf.ID,cf.TITLE,\'' . '' . '\' AS SEARCH,\'' . '' . '\' AS DISPLAY ,\'' . '' . '\' AS SEARCHABLE FROM custom_fields cf,student_field_categories sfc WHERE sfc.ID=cf.CATEGORY_ID AND (SELECT DISTINCT CAN_USE FROM profile_exceptions WHERE PROFILE_ID=\'' . User('PROFILE_ID') . '\' AND MODNAME=CONCAT(\'' . 'students/Student.php&category_id=' . '\',cf.CATEGORY_ID))=\'' . 'Y' . '\' ORDER BY sfc.SORT_ORDER,sfc.TITLE,cf.SORT_ORDER,cf.TITLE'), array('SEARCH' => '_make', 'DISPLAY' => '_make', 'SEARCHABLE' => '_make'), array('CATEGORY'));
        else {
            $profile_id_mod = DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE USER_ID='" . User('STAFF_ID')));
            $profile_id_mod = $profile_id_mod[1]['PROFILE_ID'];
            if ($profile_id_mod != '')
                $custom_fields_RET = DBGet(DBQuery('SELECT CONCAT(\'' . '<b>' . '\',sfc.TITLE,\'' . '</b>' . '\') AS CATEGORY,cf.ID,cf.TITLE,\'' . '' . '\' AS SEARCH,\'' . '' . '\' AS DISPLAY,\'' . '' . '\' AS SEARCHABLE FROM custom_fields cf,student_field_categories sfc WHERE sfc.ID=cf.CATEGORY_ID AND (SELECT DISTINCT CAN_USE FROM profile_exceptions WHERE PROFILE_ID=\'' . $profile_id_mod . '\' AND MODNAME=CONCAT(\'' . 'students/Student.php&category_id=' . '\',cf.CATEGORY_ID))=\'' . 'Y' . '\' ORDER BY sfc.SORT_ORDER,sfc.TITLE,cf.SORT_ORDER,cf.TITLE'), array('SEARCH' => '_make', 'DISPLAY' => '_make', 'SEARCHABLE' => '_make'), array('CATEGORY'));
        }

        $THIS_RET['ID'] = 'CONTACT_INFO';
        $custom_fields_RET[-1][1] = array('CATEGORY' => '<B>'._contactInformation.'</B>', 'ID' => 'CONTACT_INFO', 'TITLE' => '<IMG SRC=assets/down_phone_button.gif width=15> '._contactInformation.'', 'DISPLAY' => _make('', 'DISPLAY'));
        $THIS_RET['ID'] = 'HOME_PHONE';
        $custom_fields_RET[-1][] = array('CATEGORY' => '<B>'._contactInformation.'</B>', 'ID' => 'HOME_PHONE', 'TITLE' =>_homePhoneNumber, 'DISPLAY' => _make('', 'DISPLAY'));
        $THIS_RET['ID'] = 'GUARDIANS';
        $custom_fields_RET[-1][] = array('CATEGORY' => '<B>'._contactInformation.'</B>', 'ID' => 'GUARDIANS', 'TITLE' =>_guardians, 'DISPLAY' => _make('', 'DISPLAY'));
        $THIS_RET['ID'] = 'ALL_CONTACTS';
        $custom_fields_RET[-1][] = array('CATEGORY' => '<B>'._contactInformation.'</B>', 'ID' => 'ALL_CONTACTS', 'TITLE' =>_allContacts, 'DISPLAY' => _make('', 'DISPLAY'));

        $custom_fields_RET[0][1] = array('CATEGORY' => '<B>'._addresses.'</B>', 'ID' => 'ADDRESS', 'TITLE' =>_none, 'DISPLAY' => _makeAddress(''));
        $custom_fields_RET[0][] = array('CATEGORY' => '<B>'._addresses.'</B>', 'ID' => 'ADDRESS', 'TITLE' => '<IMG SRC=assets/house_button.gif> '._residence.'', 'DISPLAY' => _makeAddress('RESIDENCE'));
        $custom_fields_RET[0][] = array('CATEGORY' => '<B>'._addresses.'</B>', 'ID' => 'ADDRESS', 'TITLE' => '<IMG SRC=assets/mailbox_button.gif> '._mailing.'', 'DISPLAY' => _makeAddress('MAILING'));
        $custom_fields_RET[0][] = array('CATEGORY' => '<B>'._addresses.'</B>', 'ID' => 'ADDRESS', 'TITLE' => '<IMG SRC=assets/bus_button.gif> '._busPickup.'', 'DISPLAY' => _makeAddress('BUS_PICKUP'));
        $custom_fields_RET[0][] = array('CATEGORY' => '<B>'._addresses.'</B>', 'ID' => 'ADDRESS', 'TITLE' => '<IMG SRC=assets/bus_button.gif> '._busDropoff.'', 'DISPLAY' => _makeAddress('BUS_DROPOFF'));

        if (User('PROFILE') == 'admin' || User('PROFILE') == 'teacher')
            $columns = array('CATEGORY' => '', 'TITLE' => ''._field.'', 'SEARCHABLE' => '<div class="text-center">'._searchable.'</div>', 'DISPLAY' => '<div class="text-center">'._expandedView.'</div>');
//            $columns = array('CATEGORY' => '', 'TITLE' => ''._field.'', 'SEARCH' =>_search, 'DISPLAY' => '<div class="text-center">'._field.'</div>');
        else
            $columns = array('CATEGORY' => '', 'TITLE' => ''._field.'', 'DISPLAY' =>_expandedView);

        ListOutputMod($custom_fields_RET, $columns, '', '', array(), array(array('CATEGORY')));
    }


    if ($_REQUEST['tab'] == 'display_options')
        echo "<div class=\"panel-footer p-b-0 text-right\"><INPUT type=submit class=\"btn btn-primary\" value="._save." onclick=\"self_disable(this);\" ></div></div>";
    else
        echo "<div class=\"panel-footer p-b-0 text-right\"><INPUT id=\"listingStuBtn\" type=submit class=\"btn btn-primary\" value="._save." onclick='return pass_check(this);'></div>";
    PopTable('footer');
    echo '</FORM>';
}

function _make($value, $name) {
    global $THIS_RET, $categories_RET, $current_RET;
    //echo "<pre>";
//print_r($current_RET);
    switch ($name) {
        case 'SEARCH':

            if ($current_RET['StudentFieldsSearch'][$THIS_RET['ID']])
                $checked = ' checked';
            return '<label class="checkbox-inline checkbox-switch switch-success"><INPUT type=checkbox name=values[StudentFieldsSearch][' . $THIS_RET['ID'] . '] value=Y' . $checked . '><span></span></label>';
            break;

        case 'DISPLAY':

            if ($current_RET['StudentFieldsView'][$THIS_RET['ID']])
                $checked = ' checked';
            return '<div class="text-center"><INPUT type=checkbox class="styled" name=values[StudentFieldsView][' . $THIS_RET['ID'] . '] value=Y' . $checked . '></div>';
            break;
        case 'SEARCHABLE':

            if ($current_RET['StudentFieldsSearchable'][$THIS_RET['ID']])
                $checked = ' checked';
            return '<div class="text-center"><INPUT type=checkbox class="styled" name=values[StudentFieldsSearchable][' . $THIS_RET['ID'] . '] value=Y' . $checked . '></div>';
            break;
    }
}

function _makeAddress($value) {
    global $current_RET;

    if ($current_RET['StudentFieldsView']['ADDRESS'][1]['VALUE'] == $value || (!$current_RET['StudentFieldsView']['ADDRESS'][1]['VALUE'] && $value == ''))
        $checked = ' CHECKED';
    return '<div class="text-center"><INPUT type=radio class="styled" name=values[StudentFieldsView][ADDRESS] value="' . $value . '"' . $checked . '></div>';
}

?>
