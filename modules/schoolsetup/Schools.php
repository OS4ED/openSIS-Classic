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
include('lang/language.php');

unset($_SESSION['_REQUEST_vars']['values']);
unset($_SESSION['_REQUEST_vars']['modfunc']);
DrawBC(""._schoolSetup." > " . ProgramTitle());
// --------------------------------------------------------------- Test SQL ------------------------------------------------------------------ //
// --------------------------------------------------------------- Tset SQL ------------------------------------------------------------------ //

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'update' && (clean_param($_REQUEST['button'], PARAM_ALPHAMOD) == _save || clean_param($_REQUEST['button'], PARAM_ALPHAMOD) == _update || clean_param($_REQUEST['button'], PARAM_ALPHAMOD) == '')) {
    if (clean_param($_REQUEST['values'], PARAM_NOTAGS) && $_POST['values'] && User('PROFILE') == 'admin') {
        if ($_REQUEST['new_school'] != 'true') {

            $sql = 'UPDATE schools SET ';


            foreach ($_REQUEST as $col => $val) {
                $dt_ex = explode("_", $col);
                if ($dt_ex[0] == 'month') {
                    if ($_REQUEST['day_' . $dt_ex[1]]['CUSTOM_' . $dt_ex[1]] != '' && $_REQUEST['month_' . $dt_ex[1]]['CUSTOM_' . $dt_ex[1]] != '' && $_REQUEST['year_' . $dt_ex[1]]['CUSTOM_' . $dt_ex[1]] != '') {
                        // $_REQUEST['values']['CUSTOM_' . $dt_ex[1]] = $_REQUEST['year_' . $dt_ex[1]]['CUSTOM_' . $dt_ex[1]] . "-" . MonthFormatter($_REQUEST['month_' . $dt_ex[1]]['CUSTOM_' . $dt_ex[1]]) . '-' . $_REQUEST['day_' . $dt_ex[1]]['CUSTOM_' . $dt_ex[1]];
                        $_REQUEST['values']['CUSTOM_' . $dt_ex[1]] = $_REQUEST['year_' . $dt_ex[1]]['CUSTOM_' . $dt_ex[1]] . "-" . $_REQUEST['month_' . $dt_ex[1]]['CUSTOM_' . $dt_ex[1]] . '-' . $_REQUEST['day_' . $dt_ex[1]]['CUSTOM_' . $dt_ex[1]];
                    }
                }
            }

            foreach ($_REQUEST['values'] as $column => $value) {
                if (substr($column, 0, 6) == 'CUSTOM') {
                    $custom_id = str_replace("CUSTOM_", "", $column);
                    $custom_RET = DBGet(DBQuery("SELECT TITLE,TYPE,REQUIRED FROM school_custom_fields WHERE ID=" . $custom_id));

                    $custom = DBGet(DBQuery("SHOW COLUMNS FROM schools WHERE FIELD='" . $column . "'"));
                    $custom = $custom[1];

                    if ($custom_RET[1]['TYPE'] == 'multiple') {
                        $valueSize = count($value);
                        if($valueSize == 0) {
                            $valueSize = '';
                        }
                    } else {
                        $valueSize = trim($value);
                    }

                    if ($custom['NULL'] == 'NO' && trim($valueSize) == '' && $custom['DEFAULT']) {
                        $value = $custom['DEFAULT'];
                    } else if ($custom['NULL'] == 'NO' && $valueSize == '' && $custom_RET[1]['REQUIRED'] == 'Y') {
                        $custom_TITLE = $custom_RET[1]['TITLE'];
                        echo "<div class='alert alert-danger'>". ucfirst(_unableToSaveDataBecause) ." " . $custom_TITLE . ' '._isRequired.'</div>';
                        $error = true;
                        break;
                    } else if ($custom_RET[1]['TYPE'] == 'numeric' && (!is_numeric($value) && $value != '')) {
                        $custom_TITLE = $custom_RET[1]['TITLE'];
                        echo "<div class='alert alert-danger'>". ucfirst(_unableToSaveDataBecause) ." " . $custom_TITLE . ' '. isNumericType.'</div>';
                        $error = true;
                    } else {
                        $m_custom_RET = DBGet(DBQuery("SELECT ID,TITLE,TYPE FROM school_custom_fields WHERE ID='" . $custom_id . "' AND TYPE='multiple'"));

                        if ($m_custom_RET) {
                            $str = "";
                            foreach ($value as $m_custom_val) {
                                if ($m_custom_val)
                                    $str.="||" . $m_custom_val;
                            }
                            if ($str)
                                $value = $str . "||";
                            else {
                                $value = '';
                            }
                        }
                    }
                }  ###Custom Ends#####
                if ($column != 'WWW_ADDRESS')
                $value = paramlib_validation($column, trim($value));
                // ',\''.singleQuoteReplace('','',trim($value)).'\''
                if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                    $sql .= $column . '=\'' . singleQuoteReplace('', '', trim($value)) . '\',';
                } else {
                    $sql .= $column . '=\'' . singleQuoteReplace('', '', trim($value)) . '\',';
                }
            }
            $sql = substr($sql, 0, -1) . ' WHERE ID=\'' . UserSchool() . '\'';
           
            if ($error != 1)
                DBQuery($sql);
            // echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
            $note[] = _thisSchoolHasBeenModified; //This school has been modified.
            $_REQUEST['modfunc'] = '';
        }
        else {
            $fields = $values = '';

            foreach ($_REQUEST['values'] as $column => $value)
                if ($column != 'ID' && $value) {
                    if ($column != 'WWW_ADDRESS')
                    $value = paramlib_validation($column, trim($value));
                    $fields .= ',' . $column;
                    $values .= ',\'' . singleQuoteReplace('', '', trim($value)) . '\'';
                }

            if ($fields && $values) {


                // $id = DBGet(DBQuery('SHOW TABLE STATUS LIKE \'schools\''));
                // $id = $id[1]['AUTO_INCREMENT'];

                
                $start_date=$_REQUEST['year__min'].'-'.$_REQUEST['month__min'].'-'.$_REQUEST['day__min'];
                $end_date=$_REQUEST['year__max'].'-'.$_REQUEST['month__max'].'-'.$_REQUEST['day__max'];
                $syear=$_REQUEST['year__min'];
                $sql = 'INSERT INTO schools (SYEAR' . $fields . ') values(' . $syear . '' . $values . ')';
                DBQuery($sql);
                $id = mysqli_insert_id($connection);
                
                DBQuery('INSERT INTO  staff_school_relationship(staff_id,school_id,syear,start_date) VALUES (' . UserID() . ',' . $id . ',' . $syear. ',"'.date('Y-m-d').'")');
                $other_admin_details=DBGet(DBQuery('SELECT * FROM login_authentication WHERE PROFILE_ID=0 AND USER_ID!=' . UserID() . ''));
                if(!empty($other_admin_details))
                {
                foreach($other_admin_details as $school_data)
                {
                DBQuery('INSERT INTO  staff_school_relationship(staff_id,school_id,syear,start_date) VALUES (' . $school_data['USER_ID'] . ',' . $id . ',' . $syear. ',"'.date('Y-m-d').'")');    
                }
                }
                if (User('PROFILE_ID') != 0) {
                    $super_id = DBGet(DBQuery('SELECT STAFF_ID FROM staff WHERE PROFILE_ID=0 AND PROFILE=\'admin\''));
                    $staff_exists=DBGet(DBQuery('SELECT * FROM staff_school_relationship WHERE STAFF_ID='.$super_id[1]['STAFF_ID'] . ' AND SCHOOL_ID='. $id . ' AND SYEAR='.$syear));
                    if(count($staff_exists)==0)
                        DBQuery('INSERT INTO  staff_school_relationship(staff_id,school_id,syear,start_date) VALUES (' . $super_id[1]['STAFF_ID'] . ',' . $id . ',' . $syear . ',"'.date('Y-m-d').'")');
                }
                // DBQuery('INSERT INTO school_years (MARKING_PERIOD_ID,SYEAR,SCHOOL_ID,TITLE,SHORT_NAME,SORT_ORDER,START_DATE,END_DATE,POST_START_DATE,POST_END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS,ROLLOVER_ID) SELECT fn_marking_period_seq(),SYEAR,\'' . $id . '\' AS SCHOOL_ID,TITLE,SHORT_NAME,SORT_ORDER,START_DATE,END_DATE,POST_START_DATE,POST_END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS,MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY MARKING_PERIOD_ID');
                DBQuery('INSERT INTO school_years (MARKING_PERIOD_ID,SYEAR,SCHOOL_ID,TITLE,SHORT_NAME,SORT_ORDER,START_DATE,END_DATE,ROLLOVER_ID) SELECT fn_marking_period_seq(),\''.$syear.'\' as SYEAR,\'' . $id . '\' AS SCHOOL_ID,TITLE,SHORT_NAME,SORT_ORDER,\''.$start_date.'\' as START_DATE,\''.$end_date.'\' as  END_DATE,MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY MARKING_PERIOD_ID');
                DBQuery('INSERT INTO system_preference(school_id, full_day_minute, half_day_minute) VALUES (' . $id . ', NULL, NULL)');

                DBQuery('INSERT INTO program_config (SCHOOL_ID,SYEAR,PROGRAM,TITLE,VALUE) VALUES(\'' . $id . '\',\'' . $syear. '\',\'MissingAttendance\',\'LAST_UPDATE\',\'' . date('Y-m-d') . '\')');
                DBQuery('INSERT INTO program_config(SCHOOL_ID,SYEAR,PROGRAM,TITLE,VALUE) VALUES(\'' . $id . '\',\'' . $syear . '\',\'UPDATENOTIFY\',\'display_school\',"Y")');
                $_SESSION['UserSchool'] = $id;

                $chk_stu_enrollment_codes_exist = DBGet(DBQuery('SELECT COUNT(*) AS STU_ENR_COUNT FROM `student_enrollment_codes` WHERE `syear` = \''.$syear.'\''));
                if($chk_stu_enrollment_codes_exist[1]['STU_ENR_COUNT'] == 0)
                {
                    DBQuery('INSERT INTO `student_enrollment_codes` (`syear`, `title`, `short_name`, `type`) VALUES (\''.$syear.'\', \'Transferred out\', \'TRAN\', \'TrnD\')');
                    DBQuery('INSERT INTO `student_enrollment_codes` (`syear`, `title`, `short_name`, `type`) VALUES (\''.$syear.'\', \'Transferred in\', \'TRAN\', \'TrnE\')');
                    DBQuery('INSERT INTO `student_enrollment_codes` (`syear`, `title`, `short_name`, `type`) VALUES (\''.$syear.'\', \'Rolled over\', \'ROLL\', \'Roll\')');
                    DBQuery('INSERT INTO `student_enrollment_codes` (`syear`, `title`, `short_name`, `type`) VALUES (\''.$syear.'\', \'Dropped Out\', \'DROP\', \'Drop\')');
                    DBQuery('INSERT INTO `student_enrollment_codes` (`syear`, `title`, `short_name`, `type`) VALUES (\''.$syear.'\', \'New\', \'NEW\', \'Add\')');
                }

                unset($_REQUEST['new_school']);
            }
            echo '<FORM action=Modules.php?modname='.strip_tags(trim($_REQUEST['modname'])).' method=POST>';
	        // echo '<script language=JavaScript>parent.side.location="'.$_SESSION['Side_PHP_SELF'].'?modcat="+parent.side.document.forms[0].modcat.value;</script>';
	
            echo '<div class="panel panel-default">';
            echo '<div class="panel-body text-center">';
            echo '<div class="new-school-created  p-30">';
            echo '<div class="icon-school">';
            echo '<span></span>';
            echo '</div>';
            echo '<h5 class="p-20">A new school called <b class="text-success">'.GetSchool(UserSchool()).'</b> has been created. To finish the operation, click the button below.</h5>';
            echo '<div class="text-right p-r-20"><INPUT type="submit" value="Finish Setup" class="btn btn-primary btn-lg"></div>';
            echo '</div>'; //.new-school-created
            echo '</div>'; //.panel-body
            echo '</div>'; //.panel
        
	        // DrawHeaderHome('<IMG SRC=assets/check.gif> &nbsp; A new school called <strong>'.  GetSchool(UserSchool()).'</strong> has been created. To finish the operation, click OK button.','<INPUT  type=submit value="._ok." class="btn_medium">');
            echo '<input type="hidden" name="copy" value="done"/>';
	        echo '</FORM>';
        }
    } else {
        $_REQUEST['modfunc'] = '';
    }


    unset($_SESSION['_REQUEST_vars']['values']);
    unset($_SESSION['_REQUEST_vars']['modfunc']);
}

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'update' && clean_param($_REQUEST['button'], PARAM_ALPHAMOD) == 'Delete' && User('PROFILE') == 'admin') {
    if (DeletePrompt('school')) {
        if (BlockDelete('school')) {
            DBQuery('DELETE FROM schools WHERE ID=\'' . UserSchool() . '\'');
            DBQuery('DELETE FROM school_gradelevels WHERE SCHOOL_ID=\'' . UserSchool() . '\'');
            DBQuery('DELETE FROM attendance_calendar WHERE SCHOOL_ID=\'' . UserSchool() . '\'');
            DBQuery('DELETE FROM school_periods WHERE SCHOOL_ID=\'' . UserSchool() . '\'');
            DBQuery('DELETE FROM school_years WHERE SCHOOL_ID=\'' . UserSchool() . '\'');
            DBQuery('DELETE FROM school_semesters WHERE SCHOOL_ID=\'' . UserSchool() . '\'');
            DBQuery('DELETE FROM school_quarters WHERE SCHOOL_ID=\'' . UserSchool() . '\'');
            DBQuery('DELETE FROM school_progress_periods WHERE SCHOOL_ID=\'' . UserSchool() . '\'');
            DBQuery('UPDATE staff SET CURRENT_SCHOOL_ID=NULL WHERE CURRENT_SCHOOL_ID=\'' . UserSchool() . '\'');
            DBQuery('UPDATE staff SET SCHOOLS=replace(SCHOOLS,\',' . UserSchool() . ',\',\',\')');

            unset($_SESSION['UserSchool']);
            //echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
            unset($_REQUEST);
            $_REQUEST['modname'] = "schoolsetup/Schools.php?new_school=true";
            $_REQUEST['new_school'] = true;
            unset($_REQUEST['modfunc']);
            echo '
				<SCRIPT language="JavaScript">
				window.location="Side.php?school_id=new&modcat=' . strip_tags(trim($_REQUEST['modcat'])) . '";
				</SCRIPT>
				';
        }
    }
}
if (clean_param($_REQUEST['copy'], PARAM_ALPHAMOD) == 'done') {
    echo '<div class="alert alert-success alert-styled-left">' . _schoolHasBeenCreatedSuccessfully . '</div>';
    echo'<script type="text/javascript">
    window.setTimeout(function() {
        window.location.href="Modules.php?modname=miscellaneous/portal.php";
    }, 2000);
    </script>';
} else {
    if (!$_REQUEST['modfunc']) {
        if (!$_REQUEST['new_school']) {
            $schooldata = DBGet(DBQuery('SELECT * FROM schools WHERE ID=\'' . UserSchool() . '\''));
            $schooldata = $schooldata[1];
            $school_name = GetSchool(UserSchool());
        } 
        else
            $school_name = 'Add a School';
        if (!$_REQUEST['new_school'])
            $_REQUEST['new_school'] = false;
        //echo "<FORM name=school  id=school class=\"form-horizontal\"  enctype='multipart/form-data'  METHOD='POST' ACTION='Modules.php?modname=" . strip_tags(trim($_REQUEST['modname'])) . "&modfunc=update&btn=" . $_REQUEST['button'] . "&new_school=$_REQUEST[new_school]'>";
        echo "<FORM name=school  id=school class=\"form-horizontal\"  enctype='multipart/form-data'  METHOD='POST' ACTION='Modules.php?modname=" . strip_tags(trim($_REQUEST['modname'])) . "&modfunc=update'>";

        PopTable('header',  _schoolInformation);

        echo '<div class="row">';
        echo '<div class="col-lg-6">';
        echo "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">"._schoolName."<span class=\"text-danger\">*</span></label><div class=\"col-md-8\">" . TextInput($schooldata['TITLE'], 'values[TITLE]', '', ' size=24 onKeyUp=checkDuplicateName(1,this,' . $schooldata['ID'] . '); onBlur=checkDuplicateName(1,this,' . $schooldata['ID'] . ');') . "</div></div>";
        echo "<input type=hidden id=checkDuplicateNameTable1 value='schools'/>";
        echo "<input type=hidden id=checkDuplicateNameField1 value='title'/>";
        echo "<input type=hidden id=checkDuplicateNameMsg1 value='school name'/>";
        echo '</div>'; //.col-lg-6

        echo '<div class="col-lg-6">';
        echo "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">"._address."</label><div class=\"col-md-8\">" . TextInput($schooldata['ADDRESS'], 'values[ADDRESS]', '', 'class=cell_floating maxlength=100 size=24') . "</div></div>";
        echo '</div>'; //.col-lg-6
        echo '</div>'; //.row


        echo '<div class="row">';
        echo '<div class="col-lg-6">';
        echo "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">"._city."</label><div class=\"col-md-8\">" . TextInput($schooldata['CITY'], 'values[CITY]', '', 'maxlength=100, class=cell_floating size=24') . "</div></div>";
        echo '</div>'; //.col-lg-6

        echo '<div class="col-lg-6">';
        echo "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">"._state."</label><div class=\"col-md-8\">" . TextInput($schooldata['STATE'], 'values[STATE]', '', 'maxlength=100, class=cell_floating size=24') . "</div></div>";
        echo '</div>'; //.col-lg-6
        echo '</div>'; //.row

        //Zip/Postal Code
        echo '<div class="row">';
        echo '<div class="col-lg-6">';
        echo "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">"._zipPostalCode."</label><div class=\"col-md-8\">" . TextInput($schooldata['ZIPCODE'], 'values[ZIPCODE]', '', 'maxlength=10 class=cell_floating size=24') . "</div></div>";
        echo '</div>'; //.col-lg-6

        
        echo '<div class="col-lg-6">';
        echo "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">"._areaCode."</label><div class=\"col-md-8\">" . TextInput($schooldata['AREA_CODE'], 'values[AREA_CODE]', '', 'class=cell_floating size=24') . "</div></div>";
        echo '</div>'; //.col-lg-6
        echo '</div>'; //.row 
        
        
        echo '<div class="col-lg-6">';
        echo "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">"._telephone."</label><div class=\"col-md-8\">" . TextInput($schooldata['PHONE'], 'values[PHONE]', '', 'class=cell_floating size=24') . "</div></div>";
        echo '</div>'; //.col-lg-6
        echo '</div>'; //.row 


        echo '<div class="row">';
        echo '<div class="col-lg-6">';
        echo "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">"._principal."</label><div class=\"col-md-8\">" . TextInput($schooldata['PRINCIPAL'], 'values[PRINCIPAL]', '', 'class=cell_floating size=24') . "</div></div>";
        echo '</div>'; //.col-lg-6
        //Base Grading Scale
        echo '<div class="col-lg-6">';
        echo "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">"._baseGradingScale."<span class=\"text-danger\">*</span></label><div class=\"col-md-8\">" . TextInput($schooldata['REPORTING_GP_SCALE'], 'values[REPORTING_GP_SCALE]', '', 'class=cell_floating maxlength=10 size=24') . "</div></div>";
        echo '</div>'; //.col-lg-6
        echo '</div>'; //.row

         //E-Mail
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">"._email."</label><div class=\"col-md-8\">" . TextInput($schooldata['E_MAIL'], 'values[E_MAIL]', '', 'class=cell_floating maxlength=100 size=24') . "</div></div>";
        echo '</div>'; //.col-md-6

        echo '<div class="col-md-6">';
        
        if (AllowEdit() || !$schooldata['WWW_ADDRESS']) {
            //Website
            echo "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">"._website."</label><div class=\"col-md-8\">" . TextInput($schooldata['WWW_ADDRESS'], 'values[WWW_ADDRESS]', '', 'class=cell_floating size=24') . "</div></div>";
        } else {
            echo "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">"._website."</label><div class=\"col-md-8\"><A HREF=http://$schooldata[WWW_ADDRESS] target=_blank>$schooldata[WWW_ADDRESS]</A></div></div>";
        }
        echo '</div>';
        echo '</div>';
        
        echo '<div class="row">';
        if ($school_name != 'Add a School')
            include('modules/schoolsetup/includes/SchoolcustomfieldsInc.php');
        echo '</div>';

        echo '<div class="row">';
        echo '<div class="col-md-6">';

        // $uploaded_sql = DBGet(DBQuery("SELECT VALUE FROM program_config WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR IS NULL AND TITLE='PATH'"));
        // $_SESSION['logo_path'] = $uploaded_sql[1]['VALUE'];
        // if (!$_REQUEST['new_school'] && file_exists($uploaded_sql[1]['VALUE']))
        
        $sch_img_info= DBGet(DBQuery('SELECT * FROM user_file_upload WHERE SCHOOL_ID='. UserSchool().' AND FILE_INFO=\'schlogo\''));
    
        
        if(!$_REQUEST['new_school'] && count($sch_img_info)>0)
            echo "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">School Logo</label><div class=\"col-md-8\">" . (AllowEdit() != false ? "<a href ='Modules.php?modname=schoolsetup/UploadLogo.php&modfunc=edit'>" : '') . "<div class=\"image-holder\"><img src='data:image/jpeg;base64,".base64_encode($sch_img_info[1]['CONTENT'])."' class=img-responsive /></div>" . (AllowEdit() != false ? "</a>" : '') . (AllowEdit() != false ? "<a href='Modules.php?modname=schoolsetup/UploadLogo.php&modfunc=edit' class=\"show text-center m-t-10 text-primary\"><i class=\"icon-upload position-left\"></i> Click here to change logo</a>" : '') . "</div></div>";
        else if (!$_REQUEST['new_school'])
            echo "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">School Logo</label><div class=\"col-md-8\">" . (AllowEdit() != false ? "<a href ='Modules.php?modname=schoolsetup/UploadLogo.php' class=\"form-control text-primary\" readonly=\"readonly\"><i class=\"icon-upload position-left\"></i> Click here to upload logo</a>" : '-') . "</div></div>";

        echo '</div>'; //.col-md-4
        echo '</div>'; //.row  

        if($_REQUEST['new_school']=='true')
        {
            $get_this_school_date=DBGet(DBQuery('SELECT * FROM school_years where SYEAR='.UserSyear().' AND SCHOOL_ID='.UserSchool()));  
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">"._startDate."</label><div class=\"col-md-8\">" . DateInputAY($get_this_school_date[1]['START_DATE'], '_min', 1). "</div></div>";
            echo '</div>'; //.col-md-6
            
            echo '<div class="col-md-6">';
            echo "<div class=\"form-group\"><label class=\"col-md-4 control-label text-right\">"._endDate."</label><div class=\"col-md-8\">" . DateInputAY($get_this_school_date[1]['END_DATE'], '_max', 2). "</div></div>";
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row  
        }

        if($_REQUEST['new_school'] == 'true')
        {
            echo '<input id="h1" type="hidden" value="">';
        }
        else
        {
            echo '<input id="h1" type="hidden" value="'. UserSchool() .'">';
        }

        $btns = '';
        if (User('PROFILE') == 'admin' && AllowEdit()) {
            //echo '<hr class="no-margin"/>';
            if ($_REQUEST['new_school']) {
                $btns = "<div class=\"text-right\"><INPUT TYPE=submit name=button id=button class=\"btn btn-primary\" VALUE="._save." onclick='return formcheck_school_setup_school(this);'></div>";
            } else {

                $btns = "<div class=\"text-right\"><INPUT TYPE=submit name=button id=button class=\"btn btn-primary\" VALUE="._update." onclick='return formcheck_school_setup_school(this);'></div>";
            }
        }


        PopTable('footer',$btns);

        echo "</FORM>";
    }
}

?>
