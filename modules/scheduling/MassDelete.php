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
//echo "<pre>"; print_r($_REQUEST); echo "</pre>";
if (!$_REQUEST['modfunc'] && $_REQUEST['search_modfunc'] != 'list')
    unset($_SESSION['MassDrops.php']);
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHA) == 'save') {
    // $END_DATE = $_REQUEST['day'] . '-' . $_REQUEST['month'] . '-' . $_REQUEST['year'];
    // $end_date_mod = date('Y-m-d', strtotime($END_DATE));
    // if (!VerifyDate($END_DATE)) {
    //     echo '<div class="alert alert-warning alert-bordered">'._theDateYouEnteredIsNotValid.'</div>';
    //     for_error_sch();
    // } else {
        $mp_table = GetMPTable(GetMP($_REQUEST['marking_period_id'], 'TABLE'));
        
        if (count($_REQUEST['student']) > 0) {

            $course_per_id = $_SESSION['MassDrops.php']['course_period_id'];
            $c_id = $_SESSION['MassDrops.php']['course_id'];
            $course = DBGet(DBQuery('SELECT c.TITLE AS COURSE_TITLE,cp.TITLE,cp.COURSE_ID FROM course_periods cp,courses c WHERE c.COURSE_ID=cp.COURSE_ID AND cp.COURSE_PERIOD_ID=\'' . $_SESSION['MassDrops.php']['course_period_id'] . '\''));
            $cp_title=$course[1]['TITLE'];
            if(DeletePromptMod('schedule')){
                $stu_g_d_success_count = 0;
            foreach ($_REQUEST['student'] as $student_id => $yes) {
               
                
                    $stu_info = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME FROM students WHERE STUDENT_ID=' . $student_id . ''));

                $association_query_reportcard = DBQuery('Select * from  student_report_card_grades where student_id=\'' . $student_id . '\' and course_period_id=\'' . $course_per_id . '\'');
                $association_query_grade = DBQuery('Select * from gradebook_grades where student_id=\'' . $student_id . '\' and course_period_id=\'' . $course_per_id . '\' ');
                $association_query_attendance = DBQuery('Select * from attendance_period where student_id=\'' . $student_id . '\' and course_period_id=\'' . $course_per_id . '\' ');
                $schedule_data = DBGet(DBQuery('Select * from schedule where student_id=\'' . $student_id . '\' and course_period_id=\'' . $course_per_id . '\' and syear =' . UserSyear() . ' '));
                // echo mysql_num_rows($association_query_reportcard); //exit;
                $a_attn = count(DBGet($association_query_attendance));
                $a_grd = count(DBGet($association_query_grade));
                $a_rpt = count(DBGet($association_query_reportcard));

                if ($a_grd > 0) {
                    $stu_g_del_grade_err_n = $stu_info[1]['FIRST_NAME'] . "&nbsp;" . $stu_info[1]['LAST_NAME'];
                    $stu_g_del_grade_err.=$stu_g_del_grade_err_n . ', ';
                } elseif ($a_rpt > 0) {
                    $stu_g_del_f_grade_err_n = $stu_info[1]['FIRST_NAME'] . "&nbsp;" . $stu_info[1]['LAST_NAME'];
                    $stu_g_del_f_grade_err.=$stu_g_del_f_grade_err_n . ', ';
                    //UnableDeletePrompt('' . _cannotDeleteBecauseFinalGradeIsAlreadyGiven . '');

                    
                } elseif ($a_attn > 0 || $a_grd > 0 || $a_rpt > 0) {
                    $stu_g_del_s_a_n = $stu_info[1]['FIRST_NAME'] . "&nbsp;" . $stu_info[1]['LAST_NAME'];
                    $stu_g_del_s_a_err.=$stu_g_del_s_a_n . ', ';
                    //UnableDeletePrompt('' . _cannotDeleteBecauseStudentsAttendanceAreAlreadyTaken . '');
                    
                } else {

                    $current_RET = DBGet(DBQuery('SELECT ID FROM schedule WHERE STUDENT_ID = \'' . $student_id . '\' AND COURSE_PERIOD_ID=\'' . $course_per_id . '\''));
                    $sch_id =  $current_RET[1]['ID'];   
                    $schedule_fetch = DBGet(DBQuery('SELECT DROPPED FROM schedule WHERE ID=\'' . $sch_id . '\''));
                    //print_r($schedule_fetch);die;
    
                    $schedule_status = $schedule_fetch[1]['DROPPED'];
                        $seat_query = DBQuery('SELECT FILLED_SEATS FROM course_periods WHERE COURSE_ID=\'' . $c_id . '\' AND COURSE_PERIOD_ID=\'' . $course_per_id . '\' ');
                        $seat_fetch = DBGet($seat_query);
                        if ($schedule_status == 'Y') {
                            $seat_fill = $seat_fetch[1]['FILLED_SEATS'];
                        }
                        if ($schedule_status == 'N') {
                            $seat_fill = $seat_fetch[1]['FILLED_SEATS'] - 1;
                        }
                        DBQuery('Delete from schedule where student_id=\'' . $student_id . '\' and course_period_id=\'' . $course_per_id . '\' and course_id=\'' . $c_id . '\' and id=\'' . $sch_id . '\'');
                        DBQuery('Update course_periods set filled_seats=\'' . $seat_fill . '\' where course_id=\'' . $c_id . '\' and course_period_id=\'' . $course_per_id . '\' ');
                        $stu_g_del_success_n = $stu_info[1]['FIRST_NAME'] . "&nbsp;" . $stu_info[1]['LAST_NAME'];
                        $stu_g_del_success.=$stu_g_del_success_n . ', ';
                        $stu_g_d_success_count++;
                        if($stu_g_d_success_count==1)
                        $note = _theAboveStudentHasBeenDeletedFromTheCoursePeriod;
                        elseif($stu_g_d_success_count>1)
                        $note = _theAboveStudentsHaveBeenDeletedFromTheCoursePeriod;
                        
                }


            }
            unset($_REQUEST['modfunc']);
            unset($_SESSION['MassDrops.php']);
            if ($note)
                echo '<div class="alert alert-success alert-styled-left"> '. rtrim($stu_g_del_success, ", ").'<br>'. $note . ' : <b>'.$cp_title.'</b></div>';
                if ($stu_g_del_grade_err) {
                    echo '<div class="alert alert-warning alert-styled-left">';
                    echo '<button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">Close</span></button>';
                    echo rtrim($stu_g_del_grade_err,", ") . '<br>'._cannotdeleteBecauseAssignmentsGradingAreAlreadyGiven.'';
                    echo '</div>';
                }
                if ($stu_g_del_f_grade_err){
                    echo '<div class="alert alert-warning alert-styled-left">';
                    echo '<button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">'._close.'</span></button>';
                    echo rtrim($stu_g_del_f_grade_err, ", ") . '<br>'._cannotDeleteBecauseFinalGradeIsAlreadyGiven.'';
                    echo '</div>';
                }
                if ($stu_g_del_s_a_err){
                    echo '<div class="alert alert-warning alert-styled-left">';
                    echo '<button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">Close</span></button>';
                    echo rtrim($stu_g_del_s_a_err,", ") . '<br>'._cannotDeleteBecauseStudentsAttendanceAreAlreadyTaken.'';
                    echo '</div>';
                }
            }
            
               
        }
        else {
            unset($_REQUEST['modfunc']);
            unset($_SESSION['MassDrops.php']);
            echo '<div class="alert alert-warning alert-bordered"><i class="fa fa-exclamation-triangle"></i> '._noStudentSelected.'</div>';
        }
    //}
}
if (!$_REQUEST['modfunc']) {
    if ($_REQUEST['search_modfunc'] == 'list') {
        if ($_SESSION['MassDrops.php']) {
        echo "<FORM name=ww id=ww action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=save method=POST>";
        echo '<div class="panel panel-default">';
        }else{
            ShowErr(_youMustChooseACourse);

        for_error_sch();
        }
    }
    if ($_REQUEST['search_modfunc'] != 'list')
        unset($_SESSION['MassDrops.php']);
    $extra['SELECT'] = ",CAST(NULL AS CHAR(1)) AS CHECKBOX";
    $extra['functions'] = array('CHECKBOX' => '_makeChooseCheckbox');
    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAllDtMod(this,\'student\',\'Y\');"><A>');
    // $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAll(this.form,this.form.controller.checked,\'student\');"><A>');
    $extra['new'] = true;

    if ($_SESSION['MassDrops.php']['course_period_id']) {
        $extra['FROM'] .= ',schedule w_ss';
        $extra['WHERE'] .= ' AND w_ss.STUDENT_ID=s.STUDENT_ID AND w_ss.SYEAR=ssm.SYEAR AND w_ss.SCHOOL_ID=ssm.SCHOOL_ID AND w_ss.COURSE_PERIOD_ID=\'' . $_SESSION['MassDrops.php']['course_period_id'] . '\' AND (' . (($_REQUEST['include_inactive']) ? '' : 'w_ss.START_DATE <=\'' . DBDate() . '\' AND') . ' (w_ss.END_DATE>=\'' . DBDate() . '\' OR w_ss.END_DATE IS NULL))';
        $course = DBGet(DBQuery('SELECT c.TITLE AS COURSE_TITLE,cp.TITLE,cp.COURSE_ID FROM course_periods cp,courses c WHERE c.COURSE_ID=cp.COURSE_ID AND cp.COURSE_PERIOD_ID=\'' . $_SESSION['MassDrops.php']['course_period_id'] . '\''));
        $_openSIS['SearchTerms'] .= '<b>'._coursePeriod.' : </b>' . $course[1]['COURSE_TITLE'] . ' : ' . $course[1]['TITLE'];
    }
//    $extra['search'] .= "<label class=\"control-label\">Course Period</label><DIV id=course_div></DIV><A HREF=# onclick='window.open(\"ForWindow.php?modname=$_REQUEST[modname]&modfunc=choose_course\",\"\",\"scrollbars=yes,resizable=yes,width=800,height=400\");'>Choose Course Period</A>";
    $extra['search'] .= "<label class=\"control-label\">"._coursePeriod."</label><div><A HREF=javascript:void(0) data-toggle='modal' data-target='#modal_default'  onClick='cleanModal(\"course_modal\");cleanModal(\"cp_modal\");' class=\"text-primary\"><i class=\"icon-menu6 m-t-10 pull-right\"></i><DIV id=course_div class=form-control readonly=readonly><span class=text-grey>"._clickToSelect."</span></DIV></A></div>";

    if ($_REQUEST['search_modfunc'] == 'search_fnc' || !$_REQUEST['search_modfunc']) {

        //echo '<script language=JavaScript>parent.help.location.reload();</script>';

        echo '<div class="row">';
        echo '<div class="col-md-6 col-md-offset-3">';
        echo "<FORM class=no-margin-bottom name=search id=search action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=" . strip_tags(trim($_REQUEST[modfunc])) . "&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST>";
        PopTable('header', ''._findStudentsToDelete.'');

        echo '<div class="form-group">';
        echo $extra['search'];
        echo '</div>';
        //echo '<div class="checkbox checkbox-switch switch-success"><label><INPUT type=checkbox name=include_inactive value=Y /><span></span>'._includeAdvanceSchedule.'</label></div>';
        echo '<DIV id=cp_detail></DIV>';
        //echo '<div class="panel-footer">';
        $btn = "<div class=\"m-l-20\"><INPUT type=SUBMIT class='btn btn-primary' id=submit value='"._submit."' onclick='return formcheck_mass_drop(this);formload_ajax(\"search\");'> &nbsp;<INPUT type=RESET class='btn btn-default' value='"._reset."' onclick='document.getElementById(\"course_div\").innerHTML =\"\";document.getElementById(\"cp_detail\").innerHTML =\"\";' ></div>";
        //echo '</div>';     
        PopTable('footer', $btn);
        echo '</FORM>';
        echo '</div>';
        echo '</div>'; //.row
    } else {
        DrawBC(""._scheduling." > " . ProgramTitle());
        echo '<input type="hidden" name="marking_period_id" value=' . strip_tags(trim($_REQUEST['marking_period_id'])) . ' >';

        if(isset($_REQUEST['LO_sort']) && $_REQUEST['LO_sort'] != '' && $_REQUEST['LO_sort'] != NULL && isset($_REQUEST['LO_direction'])) {
            $extra['ORDER_BY'] = $_REQUEST['LO_sort'];

            if($_REQUEST['LO_direction'] == '1') {
                $extra['ORDER_BY'] = $_REQUEST['LO_sort'].' ASC';
            }
            if($_REQUEST['LO_direction'] == '-1') {
                $extra['ORDER_BY'] = $_REQUEST['LO_sort'].' DESC';
            }
        }

        # Set pagination params
        keepRequestParams($_REQUEST);
        keepExtraParams($extra);

        $students_RET = GetStuList($extra);

        $LO_columns = array('FULL_NAME' =>_student, 'STUDENT_ID' =>_studentId, 'ALT_ID' =>_alternateId, 'GRADE_ID' =>_grade, 'PHONE' =>_phone);


        if (is_array($extra['columns_before'])) {
            $columns = $extra['columns_before'] + $LO_columns;
            $LO_columns = $columns;
        }

        if (is_array($extra['columns_after']))
            $columns = $LO_columns + $extra['columns_after'];
        if (!$extra['columns_before'] && !$extra['columns_after'])
            $columns = $LO_columns;
        if (count($students_RET) > 0) {
            //echo '<div class="panel-body form-horizontal"><label class="control-label col-md-1 text-right">'._dropDate.'</label><div class="col-md-3">' . PrepareDate(DBDate(), '') . '</div></div>';
            //echo '<hr class="no-margin"/>';
        }
        if (count($students_RET) > 1 || $link['add'] || !$link['FULL_NAME'] || $extra['columns_before'] || $extra['columns_after'] || ($extra['BackPrompt'] == false && count($students_RET) == 0) || ($extra['Redirect'] === false && count($students_RET) == 1)) {
            $tmp_REQUEST = $_REQUEST;
            unset($tmp_REQUEST['expanded_view']);
            if ($_REQUEST['expanded_view'] != 'true' && !UserStudentID() && count($students_RET) != 0) {
                DrawHeader("<A HREF=" . PreparePHP_SELF($tmp_REQUEST) . "&expanded_view=true class=big_font ><i class=\"icon-square-down-right\"></i> "._expandedView."</A>", '<span class="heading-text">' . str_replace('<BR>', '<BR> &nbsp;', substr($_openSIS['SearchTerms'], 0, -4)) . '</span>', $extra['header_right']);
            } elseif (!UserStudentID() && count($students_RET) != 0) {
                DrawHeader("<A HREF=" . PreparePHP_SELF($tmp_REQUEST) . "&expanded_view=false class=big_font><i class=\"icon-square-up-left\"></i> "._originalView."</A>", '<span class="heading-text">' . str_replace('<BR>', '<BR> &nbsp;', substr($_openSIS['Search'], 0, -4)) . '</span>', $extra['header_right']);
            }
            DrawHeader($extra['extra_header_left'], $extra['extra_header_right']);
            if ($_REQUEST['LO_save'] != '1' && !$extra['suppress_save']) {
                $_SESSION['List_PHP_SELF'] = PreparePHP_SELF($_SESSION['_REQUEST_vars']);
                //echo '<script language=JavaScript>parent.help.location.reload();</script>';
            }
            if (!$extra['singular'] || !$extra['plural'])
                $extra['singular'] = ''._student.'';
            $extra['plural'] = ''._students.'';

            echo '<div id="hidden_checkboxes"></div>';
            $check_all_arr = array();
            foreach ($students_RET as $xy) {
                $check_all_arr[] = $xy['STUDENT_ID'];
            }
            $check_all_stu_list = implode(',', $check_all_arr);
            echo '<input type=hidden name=res_length id=res_length value="' . count($check_all_arr) . '">';
            echo '<input type=hidden name=res_len id=res_len value=\'' . $check_all_stu_list . '\'>';
            
            # Set pagination params
            setPaginationRequisites($_REQUEST['modname'], $_REQUEST['search_modfunc'], $_REQUEST['next_modname'], $columns, $extra['singular'], $extra['plural'], $link, $extra['LO_group'], $extra['options'], 'ListOutputCustomDT', ProgramTitle());

            echo "<div id='tabs_resp'><div id='students' class=\"table-responsive\">";
            ListOutputCustomDT($students_RET, $columns, $extra['singular'], $extra['plural'], $link, '', $extra['LO_group'], $extra['options']);
            echo "</div></div>";
        }

        if (count($students_RET) > 0) {
            //echo '<div class="panel-footer"><div class="heading-elements"><span class="heading-text no-margin-top">' . SubmitButton('Drop Course for Selected Students', '', 'class="btn btn-primary" onclick=\'formload_ajax("ww");\'') . '</span></div></div>';
            echo '<div class="panel-footer text-right p-r-20">' . SubmitButton(''._deleteCourseForSelectedStudents.'', '', 'class="btn btn-primary" onclick="self_disable(this);" ') . '</div>';
            echo '</div>';
            echo "</FORM>";
        }
    }
}

/*
 * Modal Start
 */
echo '<div id="modal_default" class="modal fade">';
echo '<div class="modal-dialog modal-lg">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<button type="button" class="close" data-dismiss="modal">×</button>';
echo '<h5 class="modal-title">'._chooseCourse.'</h5>';
echo '</div>';

echo '<div class="modal-body">';
echo '<div id="conf_div" class="text-center"></div>';
echo '<div class="row" id="resp_table">';
echo '<div class="col-md-4">';
$sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
$QI = DBQuery($sql);
$subjects_RET = DBGet($QI);

echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' '._subjectWas.'' : ' '._subjectsWere.'') . ' '._found.'.</h6>';
if (count($subjects_RET) > 0) {
    echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>'._subject.'</th></tr></thead><tbody>';
    foreach ($subjects_RET as $val) {
        echo '<tr><td><a href=javascript:void(0); onclick="MassDropModal(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
    }
    echo '</tbody></table>';
}
echo '</div>';
echo '<div class="col-md-4"><div id="course_modal"></div></div>';
echo '<div class="col-md-4"><div id="cp_modal"></div></div>';
echo '</div>'; //.row
echo '</div>'; //.modal-body

echo '</div>'; //.modal-content
echo '</div>'; //.modal-dialog
echo '</div>'; //.modal



if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAEXT) == 'choose_course') {
    if (!clean_param($_REQUEST['course_period_id'], PARAM_INT))
        include 'modules/scheduling/CoursesforWindow.php';
    else {
        $_SESSION['MassDrops.php']['subject_id'] = clean_param($_REQUEST['subject_id'], PARAM_INT);
        $_SESSION['MassDrops.php']['course_id'] = clean_param($_REQUEST['course_id'], PARAM_INT);

        $_SESSION['MassDrops.php']['course_period_id'] = clean_param($_REQUEST['course_period_id'], PARAM_INT);

        $course_title = DBGet(DBQuery('SELECT TITLE FROM courses WHERE COURSE_ID=\'' . $_SESSION['MassDrops.php']['course_id'] . '\''));
        $course_title = $course_title[1]['TITLE'];


        $cp_RET = DBGet(DBQuery('SELECT cp.TITLE,(SELECT TITLE FROM school_periods sp WHERE sp.PERIOD_ID=cpv.PERIOD_ID) AS PERIOD_TITLE,cp.MARKING_PERIOD_ID,(SELECT CONCAT(FIRST_NAME,\'' . ' ' . '\',LAST_NAME) FROM staff st WHERE st.STAFF_ID=cp.TEACHER_ID) AS TEACHER,r.TITLE AS ROOM,cp.TOTAL_SEATS-cp.FILLED_SEATS AS AVAILABLE_SEATS FROM course_periods cp,course_period_var cpv,rooms r WHERE cp.COURSE_PERIOD_ID=\'' . $_SESSION['MassDrops.php']['course_period_id'] . '\' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cpv.ROOM_ID=r.ROOM_ID'));

        $get_type = DBGEt(DBQuery('SELECT * FROM course_periods WHERE course_period_id=' . $_SESSION['MassDrops.php']['course_period_id']));
        if ($get_type[1]['SCHEDULE_TYPE'] == 'BLOCKED') {
            if (count($cp_RET) == 0) {
                echo "<script language=javascript>opener.document.getElementById(\"divErr\").innerHTML = \"<font style='color:red;'><b>"._cannotSelectCoursePeriodAsNoPeriodHasBeenAssigned."</b></font>\";window.close();</script>";
            }
        }

        $cp_title = $cp_RET[1]['TITLE'];
        $cp_teacher = $cp_RET[1]['TEACHER'];
        $period_title = $cp_RET[1]['PERIOD_TITLE'];
        $mp_title = GetMP($cp_RET[1]['MARKING_PERIOD_ID']);
        $room = $cp_RET[1]['ROOM'];
        $seats = $cp_RET[1]['AVAILABLE_SEATS'];

        echo "<script language=javascript>opener.document.getElementById(\"course_div\").innerHTML = \"<div class=form-control readonly=readonly>$cp_title</div>\";opener.document.getElementById(\"submit\").focus(); window.close();</script>";
    }
}

function _makeChooseCheckbox($value, $title) {
    global $THIS_RET;
    // return '<INPUT type=checkbox name=st_arr[] value=' . $value . ' checked>';
    
    return "<input name=unused[$THIS_RET[STUDENT_ID]] value=$THIS_RET[STUDENT_ID]  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"student[$THIS_RET[STUDENT_ID]]\",this,$THIS_RET[STUDENT_ID], \"Y\");' />";
}

// function _makeChooseCheckbox($value, $title) {
//     global $THIS_RET;
//     return "<INPUT type=checkbox name=student[" . $THIS_RET['STUDENT_ID'] . "] value=Y>";
// }

?>
