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

unset($_SESSION['student_id']);
// echo "<pre>";print_r($_REQUEST);echo "</pre>";
// echo "<pre>";print_r($_SESSION['MassRequests.php']);echo "</pre>";
// exit;

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHA) == 'save') {

    if($_REQUEST['request_course_id'])
    {
        $get_subject = DBGet(DBQuery("SELECT `subject_id` FROM `courses` WHERE `course_id` = '".$_REQUEST['request_course_id']."'"));

        $this_subject_id = $get_subject[1]['SUBJECT_ID'];

        $_SESSION['MassRequests.php']['course_id'] = $_REQUEST['request_course_id'];
        $_SESSION['MassRequests.php']['subject_id'] = $this_subject_id;
    }

    $added_counter = 0;
    $duplicate_counter = 0;

    if ($_SESSION['MassRequests.php']) {
        $current_RET = DBGet(DBQuery("SELECT STUDENT_ID FROM schedule_requests WHERE COURSE_ID='" . clean_param($_SESSION['MassRequests.php']['course_id'], PARAM_INT) . "' AND SYEAR='" . UserSyear() . "'"), array(), array('STUDENT_ID'));

        $mp_id = DBGet(DBQuery("SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "'"));
        $mp_id = $mp_id[1]['MARKING_PERIOD_ID'];

        # ------------------  Without Period Selection Request Entry Start  ------------------------------------ #
        foreach ($_REQUEST['student'] as $student_id => $yes) {

            $check_dup_sql = "SELECT COUNT(STUDENT_ID) AS DUPLICATE FROM schedule_requests WHERE COURSE_ID='" . clean_param($_SESSION['MassRequests.php']['course_id'], PARAM_INT) . "' AND SYEAR='" . UserSyear() . "' AND STUDENT_ID='" . clean_param($student_id, PARAM_INT) . "'";


            if(clean_param($_REQUEST['with_teacher_id'], PARAM_INT) == '0')
            {
                $check_dup_sql .= ' AND WITH_TEACHER_ID IS NULL';
            }
            else
            {
                $check_dup_sql .= " AND WITH_TEACHER_ID = '".clean_param($_REQUEST['with_teacher_id'], PARAM_INT)."'";
            }


            if(clean_param($_REQUEST['with_period_id'], PARAM_INT) == '0')
            {
                $check_dup_sql .= ' AND WITH_PERIOD_ID IS NULL';
            }
            else
            {
                $check_dup_sql .= " AND WITH_PERIOD_ID = '".clean_param($_REQUEST['with_period_id'], PARAM_INT)."'";
            }

            $check_dup = DBGet(DBQuery($check_dup_sql));

            $check_dup = $check_dup[1]['DUPLICATE'];

            if ($check_dup < 1) {
                if ($current_RET[$student_id] != $student_id) {
                    $sql = "INSERT INTO schedule_requests (SYEAR,SCHOOL_ID,STUDENT_ID,SUBJECT_ID,COURSE_ID,MARKING_PERIOD_ID,WITH_TEACHER_ID,NOT_TEACHER_ID,WITH_PERIOD_ID,NOT_PERIOD_ID)
									values('" . UserSyear() . "','" . UserSchool() . "','" . $student_id . "','" . $_SESSION['MassRequests.php']['subject_id'] . "','" . $_SESSION['MassRequests.php']['course_id'] . "','" . UserMP() . "','" . $_REQUEST['with_teacher_id'] . "','" . $_REQUEST['without_teacher_id'] . "','" . $_REQUEST['with_period_id'] . "','" . $_REQUEST['without_period_id'] . "')";
                    DBQuery($sql);

                    $added_counter++;
                }
            } else {
                $duplicate_counter++;
                $duplicate = "<span class=red>".$duplicate_counter.""._duplicateDataRoundRequestAlreadyExists."</span>";
                unset($_REQUEST['modfunc']);
            }
        }
        // if (!$duplicate) {
        if($added_counter != 0) {
            unset($_REQUEST['modfunc']);
            unset($_SESSION['MassRequests.php']);

            if($added_counter > 1)
            {
                $note = _thatCourseHasBeenAddedAsARequestFor." ".$added_counter." "._selectedStudents.".";
            }
            else
            {
                $note = _thatCourseHasBeenAddedAsARequestForStudent;
            }
        }
        # -------------------  Without Period Selection Request Entry End  ------------------------------------- #
    } else {
        ShowErr(_youMustChooseACourse);
        unset($_SESSION['MassRequests.php']);
        for_error();
    }
}
if ($_REQUEST['modfunc'] != 'choose_course') {
    DrawBC(""._scheduling." > " . ProgramTitle());
    if ($_REQUEST['search_modfunc'] == 'list') {
        echo "<FORM name=qq id=qq action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=save  method=POST>";

        //PopTable_wo_header('header');
        echo '<div class="panel panel-default">';
        echo '<div class="panel-body">';
        echo '<div class="row">';
        echo '<div class="col-md-4">';
        echo '<div class="form-group">';
        echo '<label class="control-label col-lg-4 text-right">'._requestToAdd.'</label>';
        echo '<div class="col-lg-8">';
        echo '<A HREF=javascript:void(0) data-toggle="modal" data-target="#modal_default_request" onClick="cleanModal(\"modal_default_request\");"><i class="icon-menu6 pull-right m-t-10"></i><div id=course_div class="form-control m-b-5" readonly="readonly">'._chooseACourse.'</div></a>';
        echo '</div>'; //.col-md-8
        echo '</div>'; //.form-group
        echo '</div>'; //.col-md-4

        if ($_SESSION['MassRequests.php']) {
            $course_title = DBGet(DBQuery("SELECT TITLE,COURSE_ID FROM courses WHERE COURSE_ID='" . $_SESSION['MassRequests.php']['course_id'] . "'"));
            $course_title = $course_title[1]['TITLE'];
        }

        //echo '</DIV>' . "<A HREF=# onclick='window.open(\"ForWindow.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=choose_course\",\"\",\"scrollbars=yes,resizable=yes,width=800,height=400\");'>Choose a Course</A></div>";


        echo '<div class="col-md-4"><label class="control-label">'._withTeacher.' &amp; '._period.'</label>';
        echo '<DIV id=WITH_TEACHER_PERIOD ><SELECT name=with_teacher_id class="form-control"><OPTION value="">'._teacherNA.'</OPTION>';

        $teachers_RET = DBGet(DBQuery("SELECT s.STAFF_ID,s.LAST_NAME,s.FIRST_NAME,MIDDLE_NAME FROM staff s,staff_school_relationship ssr WHERE s.STAFF_ID=ssr.STAFF_ID AND s.CURRENT_SCHOOL_ID=ssr.SCHOOL_ID AND s.CURRENT_SCHOOL_ID LIKE '%" . UserSchool() . "%' AND ssr.SYEAR='" . UserSyear() . "' AND s.PROFILE='teacher' ORDER BY s.LAST_NAME,s.FIRST_NAME"));
        foreach ($teachers_RET as $teacher)
            echo '<OPTION value=' . $teacher['STAFF_ID'] . '>' . $teacher['LAST_NAME'] . ', ' . $teacher['FIRST_NAME'] . ' ' . $teacher['MIDDLE_NAME'] . '</OPTION>';
        echo '</SELECT><SELECT class="form-control" name=with_period_id><OPTION value="">'._periodNA.'</OPTION>';
        $periods_RET = DBGet(DBQuery("SELECT PERIOD_ID,TITLE FROM school_periods WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
        foreach ($periods_RET as $period)
            echo '<OPTION value=' . $period['PERIOD_ID'] . '>' . $period['TITLE'] . '</OPTION>';
        echo '</SELECT></DIV></div>';


        echo '<div class="col-md-4"><label class="control-label">'._withoutTeacher.' &amp; '._period.'</label>';
        echo '<DIV ID=WITHOUT_TEACHER_PERIOD><SELECT class="form-control" name=without_teacher_id><OPTION value="">'._teacherNA.'</OPTION>';
        foreach ($teachers_RET as $teacher)
            echo '<OPTION value=' . $teacher['STAFF_ID'] . '>' . $teacher['LAST_NAME'] . ', ' . $teacher['FIRST_NAME'] . ' ' . $teacher['MIDDLE_NAME'] . '</OPTION>';
        echo '</SELECT><SELECT class="form-control" name=without_period_id><OPTION value="">'._periodNA.'</OPTION>';
        foreach ($periods_RET as $period)
            echo '<OPTION value=' . $period['PERIOD_ID'] . '>' . $period['TITLE'] . '</OPTION>';
        echo '</SELECT></DIV></div>';
        echo '</div>'; //.row
        echo '</div>'; //.panel-body
        echo '</div>'; //.panel
        //PopTable('footer');
    }
    if ($note)
        DrawHeaderHome('<div class="alert alert-success alert-styled-left alert-bordered"> ' . $note . '</div>');
    if ($teacher_error)
        DrawHeaderHome('<p class="text-danger"><i class="fa fa-exclamation-triangle"></i> ' . $teacher_error . '</p>');
    if ($period_error)
        DrawHeaderHome('<p class="text-danger"><i class="fa fa-exclamation-triangle"></i> ' . $period_error . '</p>');
    if ($duplicate)
        DrawHeaderHome('<div class="alert alert-danger alert-styled-left alert-bordered"> ' . $duplicate . '</div>');
}

if (!$_REQUEST['modfunc']) {
    if ($_REQUEST['search_modfunc'] != 'list')
        unset($_SESSION['MassRequests.php']);
    $extra['link'] = array('FULL_NAME' =>false);
    $extra['SELECT'] = ",CAST(NULL AS CHAR(1)) AS CHECKBOX";
    $extra['functions'] = array('CHECKBOX' => '_makeChooseCheckbox');
    // $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAll(this.form,this.form.controller.checked,\'student\');"><A>');
    
    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAllDtMod(this,\'student\');"><A>');
    $extra['new'] = true;

    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-lg-6">';
    Widgets('request');
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '<div class="col-lg-6">';
    Widgets('activity');
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '</div>'; //.row

    Search('student_id', $extra);
}

if ($_REQUEST['modfunc'] != 'choose_course') {

    if ($_REQUEST['search_modfunc'] == 'list') {
        if ($_SESSION['count_stu'] != 0)
            echo '<div class="text-right p-b-20 p-r-20">'.SubmitButton(_addRequestToSelectedStudents, '', 'id="groupReqBtn" class="btn btn-primary" onclick=\' return validate_group_request(this);\'').'</div>';
        echo '</FORM>';
    }
}

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAEXT) == 'choose_course') {


    if (!$_REQUEST['course_id'])
        include 'modules/scheduling/CoursesforWindow.php';
    else {
        $_SESSION['MassRequests.php']['subject_id'] = clean_param($_REQUEST['subject_id'], PARAM_INT);
        $_SESSION['MassRequests.php']['course_id'] = clean_param($_REQUEST['course_id'], PARAM_INT);


        $course_title = DBGet(DBQuery("SELECT TITLE,COURSE_ID FROM courses WHERE COURSE_ID='" . $_SESSION['MassRequests.php']['course_id'] . "'"));
        $course_title = $course_title[1]['TITLE'];
        $c = $_SESSION['MassRequests.php']['course_id'];

        //***WITH TEACHER_PERIOD*************************************************************
        $tp_html = '';
        $tp_html .= '<SELECT name=with_teacher_id class=form-control><OPTION>'._teacherNA.'</OPTION>';
        $corr_teachers = DBGet(DBQuery("SELECT Distinct s.FIRST_NAME,s.LAST_NAME,s.STAFF_ID AS TEACHER_ID FROM staff s,course_periods cp WHERE s.STAFF_ID=cp.TEACHER_ID AND cp.COURSE_ID='" . $c . "'"));
        foreach ($corr_teachers as $teacher) {
            $tp_html .= '<OPTION value=' . $teacher['TEACHER_ID'] . '>' . $teacher['LAST_NAME'] . ', ' . $teacher['FIRST_NAME'] . '</OPTION>';
        }
        $tp_html .= '</SELECT><SELECT name=with_period_id class=form-control><OPTION>'._periodNA.'</OPTION>';
        $corr_periods = DBGet(DBQuery("SELECT Distinct p.TITLE,p.PERIOD_ID FROM school_periods p,course_periods cp,course_period_var cpv WHERE p.PERIOD_ID=cpv.PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.COURSE_ID='" . $c . "'"));
        foreach ($corr_periods as $period) {
            $tp_html .= '<OPTION value=' . $period['PERIOD_ID'] . '>' . $period['TITLE'] . '</OPTION>';
        }
        $tp_html .= '</SELECT>';


        //***WITH TEACHER_PERIOD**********************************************************
        //***WITHOUT TEACHER_PERIOD*******************************************************
        $tp_html_w = '';
        $tp_html_w .= '<SELECT name=without_teacher_id class=form-control><OPTION>'._teacherNA.'</OPTION>';
        $corr_teachers = DBGet(DBQuery("SELECT Distinct s.FIRST_NAME,s.LAST_NAME,s.STAFF_ID AS TEACHER_ID FROM staff s,course_periods cp WHERE s.STAFF_ID=cp.TEACHER_ID AND cp.COURSE_ID='" . $c . "'"));
        foreach ($corr_teachers as $teacher) {
            $tp_html_w .= '<OPTION value=' . $teacher['TEACHER_ID'] . '>' . $teacher['LAST_NAME'] . ', ' . $teacher['FIRST_NAME'] . '</OPTION>';
        }
        $tp_html_w .= '</SELECT><SELECT name=without_period_id class=form-control><OPTION>'._periodNA.'</OPTION>';
        $corr_periods = DBGet(DBQuery("SELECT Distinct p.TITLE,p.PERIOD_ID FROM school_periods p,course_periods cp,course_period_var cpv WHERE p.PERIOD_ID=cpv.PERIOD_ID AND cp.COURSE_ID='" . $c . "' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID"));
        foreach ($corr_periods as $period) {
            $tp_html_w .= '<OPTION value=' . $period['PERIOD_ID'] . '>' . $period['TITLE'] . '</OPTION>';
        }
        $tp_html_w .= '</SELECT>';

        //***WITHOUT TEACHER_PERIOD*******************************************************
        echo "<script language=javascript>opener.document.getElementById(\"course_div\").innerHTML = \"$course_title\";
                opener.document.getElementById(\"WITH_TEACHER_PERIOD\").innerHTML = \"$tp_html\"; 
                opener.document.getElementById(\"WITHOUT_TEACHER_PERIOD\").innerHTML = \"$tp_html_w\";
                window.close();
                </script>";
    }
}

function _makeChooseCheckbox($value, $title) {
    global $THIS_RET;

    // return "<INPUT type=checkbox name=student[" . $THIS_RET['STUDENT_ID'] . "] value=Y>";
    return "<INPUT type=checkbox name=unused[" . $THIS_RET['STUDENT_ID'] . "] value=Y id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"student[$THIS_RET[STUDENT_ID]]\",this,$THIS_RET[STUDENT_ID]);'>";
    // return "<input name=unused[$THIS_RET[STUDENT_ID]] value=" . $THIS_RET[STUDENT_ID] . "  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"st_arr[]\",this,$THIS_RET[STUDENT_ID]);' />";
}

echo '<div id="modal_default_request" class="modal fade">';
echo '<div class="modal-dialog">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<button type="button" class="close" data-dismiss="modal">×</button>';
echo '<h5 class="modal-title">'._chooseCourse.'</h5>';
echo '</div>';

echo '<div class="modal-body">';
echo '<center><div id="conf_div"></div></center>';
echo '<table id="resp_table"><tr><td valign="top">';
echo '<div>';
$sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
$QI = DBQuery($sql);
$subjects_RET = DBGet($QI);

echo count($subjects_RET) . ((count($subjects_RET) == 1) ?  _subjectWas : _subjectsWere) . ''._found.'.<br>';
if (count($subjects_RET) > 0) {
    echo '<table class="table table-bordered"><tr class="bg-grey-200"><th>'._subject.'</th></tr>';
    foreach ($subjects_RET as $val) {
        echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearchRequest(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
    }
    echo '</table>';
}
echo '</div></td>';
echo '<td valign="top"><div id="course_modal_request"></div></td>';
echo '</tr></table>';
// echo '<div id="coursem"><div id="cpem"></div></div>';
echo' </div>
</div>
</div>
</div>';
?>
