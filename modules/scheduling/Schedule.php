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

include 'lang/language.php';
if(!$_REQUEST['modfunc'] && !isset($_REQUEST['search_modfunc'])){
    unset($_SESSION['MassDrops.php']);
}

if (UserStudentID() != '') {
    $RET = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME,NAME_SUFFIX FROM students WHERE STUDENT_ID=\'' . UserStudentID() . '\''));

    echo '<div class="panel panel-default">';
    DrawHeader('' . _selectedStudent . ' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'], '<div class="btn-group heading-btn"><A HREF=Side.php?student_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">' . _deselect . '</A></div>');
    echo '</div>';
}

if ($_REQUEST['modfunc'] == 'cp_insert') {

    if ($_POST['exit']) {
        DBQuery("DROP TABLE IF EXISTS temp_schedule");
        unset($_SESSION['course_periods']);
        unset($_REQUEST['selected_course_periods']);
//            echo '<script type=text/javascript>window.close();</script>';
    } elseif ($_POST['done']) {

        $cp_list = '\'' . implode('\',\'', $_REQUEST['selected_course_periods']) . '\'';
        $parent_course = array();
        foreach ($_REQUEST['selected_course_periods'] as $val) {
            $res = DBGet(DBQuery("SELECT parent_id from  course_periods WHERE course_period_id=" . $val));

            if ($res[1]['PARENT_ID'] != $val) {
                $res_sch = DBGet(DBQuery('SELECT count(*) as res from  schedule WHERE course_period_id=' . $res[1]['PARENT_ID'] . ' and student_id=' . UserStudentID()));

                if ($res_sch[1]['RES'] > 0) {
                    DBQuery("INSERT INTO schedule(syear, school_id, student_id, start_date, end_date,modified_by, course_id, course_weight, course_period_id, mp, marking_period_id, scheduler_lock, dropped) SELECT syear, school_id, student_id, start_date, end_date, modified_by, course_id, course_weight, course_period_id, mp, marking_period_id, scheduler_lock, dropped FROM temp_schedule WHERE course_period_id =$val");
                    DBQuery("DROP TABLE IF EXISTS temp_schedule");
                    unset($_SESSION['course_periods']);
                    unset($_SESSION['marking_period_id']);
                    unset($_REQUEST['selected_course_periods']);
                } else {

                    $parent_course[] = $val;
                }
            } else {

                DBQuery("INSERT INTO schedule(syear, school_id, student_id, start_date, end_date,modified_by, course_id, course_weight, course_period_id, mp, marking_period_id, scheduler_lock, dropped) SELECT syear, school_id, student_id, start_date, end_date, modified_by, course_id, course_weight, course_period_id, mp, marking_period_id, scheduler_lock, dropped FROM temp_schedule WHERE course_period_id=$val");

                unset($_SESSION['course_periods']);
                unset($_SESSION['marking_period_id']);
                unset($_REQUEST['selected_course_periods']);
            }
        }

        $parent_course = implode(',', $parent_course);
        if ($parent_course != '') {
            $parent_course_name = DBGet(DBQuery("SELECT title from  course_periods WHERE course_period_id in (" . $parent_course . ")"));

            $parent_c_name = array();
            foreach ($parent_course_name as $title) {
                foreach ($title as $c_title) {
                    $parent_c_name[] = $c_title;
                }

            }
            if (count($parent_c_name) > 0) {
                $parent_c_name = implode(',', $parent_c_name);
            } else {
                $parent_c_name = '';
            }
        }

        $_SESSION['conflict_cp'] = $parent_c_name;
    }
    DBQuery("DROP TABLE IF EXISTS temp_schedule");
    $day = $_REQUEST['day_date'];
    $month   = $_REQUEST['month_date'];
    $year = $_REQUEST['year_date'];
    echo "<script type=text/javascript>window.location.href='Modules.php?modname=scheduling/Schedule.php&month_date=$month&day_date=$day&year_date=$year&student_id=" . UserStudentID() . "';</script>";

}

foreach ($_REQUEST as $i => $r) {

    if ($i == 'month_schedule') {
        foreach ($r as $dt => $req) {
            foreach ($req as $r_d) {
                $end_d[] = $r_d['END_DATE'];
            }

        }
    }
    if ($i == 'day_schedule') {
        foreach ($r as $dt => $req) {
            foreach ($req as $r_d) {
                $end_d[] = $r_d['END_DATE'];
            }

        }
    }
    if ($i == 'year_schedule') {
        foreach ($r as $dt => $req) {
            foreach ($req as $r_d) {
                $end_d[] = $r_d['END_DATE'];
            }

        }
    }
}
$end_darr = isset($end_d) && is_array($end_d) ? $end_d : [];
$end_d = implode('-', $end_darr);
//$end_d = date('m-d-Y', strtotime($end_d));
include '../../RedirectModulesInc.php';
ini_set('memory_limit', '12000000M');
ini_set('max_execution_time', '50000');
DrawBC("" . _scheduling . " > " . ProgramTitle());
$tot_cp = '';

$extra['search'] .= '<div class="row">';
$extra['search'] .= '<div class="col-lg-6">';
Widgets('activity');
$extra['search'] .= '</div>'; //.col-lg-6
$extra['search'] .= '<div class="col-lg-6">';
Widgets('course');
$extra['search'] .= '</div>'; //.col-lg-6
$extra['search'] .= '</div>'; //.row

$extra['search'] .= '<div class="row">';
$extra['search'] .= '<div class="col-lg-6">';
Widgets('request_mod');
$extra['search'] .= '</div>'; //.col-lg-6
$extra['search'] .= '</div>'; //.row

if (!$_SESSION['student_id']) {
    Search('student_id', $extra);
}
####################
/////For deleting schedule

echo '<div id="modal_default" class="modal fade">';
echo '<div class="modal-dialog modal-lg">';
echo '<div class="modal-content">';

echo '<div class="modal-header">';
echo '<button type="button" class="close" data-dismiss="modal">×</button>';
echo '<h5 class="modal-title">' . _chooseCourse . '</h5>';
echo '</div>'; //.modal-header

echo '<div class="modal-body">';
echo '<div class="row" id="resp_table">';
echo '<div class="col-md-4">';
echo '<div>';
$sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
$QI = DBQuery($sql);
$subjects_RET = DBGet($QI);

echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' ' . _subjectWas . '' : '' . _subjectWas . '') . ' ' . _subjectWas . '.</h6>';
if (count($subjects_RET) > 0) {
    echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>' . _subject . '</th></tr></thead>';
    echo '<tbody>';
    foreach ($subjects_RET as $val) {
        echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearch(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
    }
    echo '</tbody>';
    echo '</table>';
}
echo '</div></div>';
echo '<div class="col-md-4"><div id="course_modal"></div></div>';
echo '<div class="col-md-4"><div id="cp_modal"></div></div>';
echo '</div>'; //.row
echo '</div>'; //.modal-body

echo '</div>'; //.modal-content
echo '</div>'; //.modal-dialog
echo '</div>'; //.modal

echo '<div id="modal_default_request" class="modal fade">';
echo '<div class="modal-dialog">';
echo '<div class="modal-content">';

echo '<div class="modal-header">';
echo '<button type="button" class="close" data-dismiss="modal">×</button>';
echo '<h5 class="modal-title">' . _chooseCourse . '</h5>';
echo '</div>'; //.modal-header

echo '<div class="modal-body">';
echo '<div class="row" id="resp_table">';
echo '<div class="col-md-6">';
$sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
$QI = DBQuery($sql);
$subjects_RET = DBGet($QI);

echo count($subjects_RET) . ((count($subjects_RET) == 1) ? ' ' . _subjectWas . '' : ' ' . _subjectWas . '') . ' ' . _subjectWas . '.<br>';
if (count($subjects_RET) > 0) {
    echo '<table class="table table-bordered"><tr class="alpha-grey"><th>' . _subject . '</th></tr>';
    foreach ($subjects_RET as $val) {
        echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearchRequest(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
    }
    echo '</table>';
}
echo '</div>';
echo '<div class="col-md-6"><div id="course_modal_request"></div></div>';
echo '</div>'; //.row
echo '</div>'; //.modal-body

echo '</div>'; //.modal-content
echo '</div>'; //.modal-dialog
echo '</div>'; //.modal

//More Info
echo '<div id="modal_moreinfo" class="modal fade">';
echo '<div class="modal-dialog modal-lg">';
echo '<div class="modal-content">';

echo '<div class="modal-header">';
echo '<button type="button" class="close" data-dismiss="modal">×</button>';
echo '<h5 class="modal-title">' . _moreInfo . '</h5>';
echo '</div>'; //.modal-header

echo '<div class="modal-body">';
echo '<div id="more_info_lbl"></div>';
echo '<div id="modal-mrif"></div>';
echo '</div>'; //.modal-body

echo '</div>'; //.modal-content
echo '</div>'; //.modal-dialog
echo '</div>'; //.modal

if (isset($_SESSION['conflict_cp']) && $_SESSION['conflict_cp'] != '') {
    DrawHeaderHome('<IMG SRC=assets/warning_button.gif><br>' . $_SESSION['conflict_cp'] . ' ' . _haveParentCourseRestriction . '');
    unset($_SESSION['conflict_cp']);
}
if ($_REQUEST['del'] == 'true') {
    $association_query_reportcard = DBQuery('Select * from  student_report_card_grades where student_id=\'' . UserStudentId() . '\' and course_period_id=\'' . $_REQUEST['cp_id'] . '\'');
    $association_query_grade = DBQuery('Select * from gradebook_grades where student_id=\'' . UserStudentId() . '\' and course_period_id=\'' . $_REQUEST['cp_id'] . '\' ');
    $association_query_attendance = DBQuery('Select * from attendance_period where student_id=\'' . UserStudentId() . '\' and course_period_id=\'' . $_REQUEST['cp_id'] . '\' ');
    $schedule_data = DBGet(DBQuery('Select * from schedule where student_id=\'' . UserStudentId() . '\' and course_period_id=\'' . $_REQUEST['cp_id'] . '\' and syear =' . UserSyear() . ' '));
    // echo mysql_num_rows($association_query_reportcard); //exit;
    $a_attn = count(DBGet($association_query_attendance));
    $a_grd = count(DBGet($association_query_grade));
    $a_rpt = count(DBGet($association_query_reportcard));

    if ($a_grd > 0) {
        UnableDeletePrompt('' . _cannotdeleteBecauseAssignmentsGradingAreAlreadyGiven . '');

        unset($_REQUEST['del']);
        unset($_REQUEST['c_id']);
    } elseif ($a_rpt > 0) {
        UnableDeletePrompt('' . _cannotDeleteBecauseFinalGradeIsAlreadyGiven . '');

        unset($_REQUEST['del']);
        unset($_REQUEST['c_id']);
    } elseif ($a_attn > 0 || $a_grd > 0 || $a_rpt > 0) {
        UnableDeletePrompt('' . _cannotDeleteBecauseStudentsAttendanceAreAlreadyTaken . '');
        unset($_REQUEST['del']);
        unset($_REQUEST['c_id']);
    } else {

        if (DeletePromptMod('schedule')) {
            $schedule_fetch = DBGet(DBQuery('SELECT DROPPED FROM schedule WHERE ID=\'' . $_REQUEST['schedule_id'] . '\''));
            $schedule_status = $schedule_fetch[1]['DROPPED'];
            $seat_query = DBQuery('SELECT FILLED_SEATS FROM course_periods WHERE COURSE_ID=\'' . $_REQUEST['c_id'] . '\' AND COURSE_PERIOD_ID=\'' . $_REQUEST['cp_id'] . '\' ');
            $seat_fetch = DBGet($seat_query);
            if ($schedule_status == 'Y') {
                $seat_fill = $seat_fetch[1]['FILLED_SEATS'];
            }
            if ($schedule_status == 'N') {
                $seat_fill = $seat_fetch[1]['FILLED_SEATS'] - 1;
            }
            DBQuery('Delete from schedule where student_id=\'' . UserStudentId() . '\' and course_period_id=\'' . $_REQUEST['cp_id'] . '\' and course_id=\'' . $_REQUEST['c_id'] . '\' and id=\'' . $_REQUEST['schedule_id'] . '\'');
            DBQuery('Update course_periods set filled_seats=\'' . $seat_fill . '\' where course_id=\'' . $_REQUEST['c_id'] . '\' and course_period_id=\'' . $_REQUEST['cp_id'] . '\' ');
            unset($_REQUEST['del']);
            unset($_REQUEST['c_id']);
            unset($_REQUEST['cp_id']);
            $day = $_REQUEST['day_date'];
            $month   = $_REQUEST['month_date'];
            $year = $_REQUEST['year_date'];

            echo "<script>window.location.href='Modules.php?modname=scheduling/Schedule.php&month_date=$month&day_date=$day&year_date=$year'</script>";

        }
        unset($_REQUEST['del']);
        unset($_REQUEST['c_id']);
    }
} else {
    $selectedStudentId = isset($_REQUEST['student_id']) ? $_REQUEST['student_id'] : UserStudentID();
    if (isset($_REQUEST['student_id']) || UserStudentID()) {
        $RET = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME,NAME_SUFFIX,SCHOOL_ID FROM students,student_enrollment WHERE students.STUDENT_ID=\'' . $selectedStudentId . '\' AND student_enrollment.STUDENT_ID = students.STUDENT_ID '));

        $count_student_RET[1]['NUM'] = $_SESSION['count_stu'];
        if ($count_student_RET[1]['NUM'] > 1) {
            DrawHeaderHome('<div class="panel"><div class="panel-heading"><h6 class="panel-title">' . _selectedStudent . ':' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'] . '</h6> <div class="heading-elements"><span class="heading-text"><A HREF=Modules.php?modname=' . $_REQUEST['modname'] . '&search_modfunc=list&next_modname=students/Student.php&ajax=true&bottom_back=true&return_session=true target=body><i class="icon-square-left"></i> ' . _backToStudentList . '</A></span><div class="btn-group heading-btn"><A HREF=Side.php?student_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">' . _deselect . '</A></div></div></div></div>');
        } elseif ($count_student_RET[1]['NUM'] == 1) {
            DrawHeaderHome('<div class="panel"><div class="panel-heading"><h6 class="panel-title">' . _selectedStudent . ':' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'] . '</h6> <div class="heading-elements"><A HREF=Side.php?student_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">' . _deselect . '</A></div></div></div>');
        }
    }
####################
    if ($_REQUEST['month_date'] && $_REQUEST['day_date'] && $_REQUEST['year_date']) {
        while (!VerifyDate($date = $_REQUEST['day_date'] . '-' . $_REQUEST['month_date'] . '-' . $_REQUEST['year_date'])) {
            $_REQUEST['day_date']--;
        }
    } else {
        $min_date = DBGet(DBQuery('SELECT min(SCHOOL_DATE) AS MIN_DATE FROM attendance_calendar WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
        if ($min_date[1]['MIN_DATE'] && DBDate('postgres') < $min_date[1]['MIN_DATE']) {
            $date = $min_date[1]['MIN_DATE'];
            $_REQUEST['day_date'] = date('d', strtotime($date));
            $_REQUEST['month_date'] = strtoupper(date('m', strtotime($date)));
            $_REQUEST['year_date'] = date('Y', strtotime($date));
            $first_visit = 'yes';
        } else {
            $_REQUEST['day_date'] = date('d');
            $_REQUEST['month_date'] = date('m');
            $_REQUEST['year_date'] = date('Y');
            $date = $_REQUEST['year_date'] . '-' . $_REQUEST['month_date'] . '-' . $_REQUEST['day_date'];
            $first_visit = 'yes';
        }
    }
    if ($_REQUEST['month_schedule'] && ($_POST['month_schedule'] || $_REQUEST['ajax'])) {

        foreach ($_REQUEST['month_schedule'] as $id => $start_dates) {
            foreach ($start_dates as $start_date => $columns) {

                foreach ($columns as $column => $value) {
                    $_REQUEST['schedule'][$id][$start_date][$column] = $_REQUEST['day_schedule'][$id][$start_date][$column] . '-' . $value . '-' . $_REQUEST['year_schedule'][$id][$start_date][$column];
                    if ($_REQUEST['schedule'][$id][$start_date][$column] == '--') {
                        $_REQUEST['schedule'][$id][$start_date][$column] = '';
                    }

                }
            }
        }

        unset($_REQUEST['month_schedule']);
        unset($_REQUEST['day_schedule']);
        unset($_REQUEST['year_schedule']);
        unset($_SESSION['_REQUEST_vars']['month_schedule']);
        unset($_SESSION['_REQUEST_vars']['day_schedule']);
        unset($_SESSION['_REQUEST_vars']['year_schedule']);
        $_POST['schedule'] = $_REQUEST['schedule'];
    }

    if ($_REQUEST['schedule'] && ($_POST['schedule'] || $_REQUEST['ajax'])) {

        $error_flag = 0;
        $count_start_date = 0;
        $count_update_data = 0;
        foreach ($_REQUEST['schedule'] as $course_period_id => $start_dates) {

            foreach ($start_dates as $start_date => $columns) {

                $count_start_date++;
                $flag = 0;
                $mark_id = $columns['MARKING_PERIOD_ID'];
                $schdl_is_exist_qry = DBGet(DBQuery('SELECT COUNT(*) AS ROWSE FROM schedule WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND COURSE_PERIOD_ID=\'' . $course_period_id . '\''));

                if ($schdl_is_exist_qry[1]['ROWSE'] > 1) {
                    $schdl_drop_status = DBGet(DBQuery('SELECT DROPPED FROM schedule WHERE ID=\'' . $columns['SCHEDULE_ID'] . '\''));
                    if ($schdl_drop_status[1]['DROPPED'] == 'N') {
                        $drooped_end_date = DBGet(DBQuery('SELECT END_DATE FROM schedule WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND DROPPED=\'Y\' ORDER BY END_DATE'));
                        if ((date('Y-m-d', strtotime($columns['START_DATE'])) > $drooped_end_date[1]['END_DATE']) || $columns['START_DATE'] == '') {
                            $flag = 1;
                        }
                    } elseif ($schdl_drop_status[1]['DROPPED'] == 'Y') {
                        $schdl_start_date = DBGet(DBQuery('SELECT MAX(START_DATE) AS GREATER FROM schedule WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND COURSE_PERIOD_ID=\'' . $course_period_id . '\''));

                        if ($start_date == $schdl_start_date[1]['GREATER']) {

                            $flag = 1;
                        } else {
                            $_SESSION['schedule_error'] = 2;
                        }
                    }
                } else {
                    $flag = 1;
                }

                if ($flag == 0 && ($columns['START_DATE'] != '' || $columns['END_DATE'] != '')) {
                    $start_date_msg = "dropped_schdl_error";
                }

                $chk_emrol_qry = DBGet(DBQuery('SELECT BEGIN_DATE,END_DATE FROM course_periods WHERE  COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND SCHOOL_ID=' . UserSchool() . ' AND SYEAR=\'' . UserSyear() . '\''));

                if (($columns['START_DATE'] != '' && strtotime($columns['START_DATE']) < strtotime($chk_emrol_qry[1]['BEGIN_DATE']))) {
                    $start_date_msg = "re_enroll";
                    $flag = 0;
                }

                if ($flag == 1) {
                    $count_update_data++;
                    $error_flag = 1;
                    $sql = 'UPDATE schedule SET ';
                    foreach ($columns as $column => $value) {
                        $edt_qry = DBGet(DBQuery('SELECT START_DATE,END_DATE FROM schedule WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND START_DATE=\'' . date('Y-m-d', strtotime($start_date)) . '\''));
                        $ch1 = strtotime($edt_qry[1]['END_DATE']);
                        $ch2 = strtotime($columns['END_DATE']);
                        if ($ch1 != $ch2 && $columns['SCHEDULER_LOCK'] == 'Y') {
                            $sch_lock_msg = "end";
                        } else {
                            if ($column == 'SCHEDULE_ID') {
                                continue;
                            }

                            if ($column == 'MARKING_PERIOD_ID') {
                                if ($mark_id != '') {
                                    $mark_end_qry = DBGet(DBQuery('SELECT END_DATE FROM marking_periods WHERE MARKING_PERIOD_ID =' . $mark_id . ''));
                                    $mark_end_date = date('Y-m-d', strtotime($mark_end_qry[1]['END_DATE']));
                                } else {
                                    $mark_end_qry = DBGet(DBQuery('SELECT END_DATE FROM course_details WHERE COURSE_PERIOD_ID =' . $course_period_id . ''));
                                    $mark_end_date = date('Y-m-d', strtotime($mark_end_qry[1]['END_DATE']));
                                }

                                $qr = DBGet(DBQuery('SELECT END_DATE FROM student_enrollment WHERE STUDENT_ID =' . UserStudentID() . ' AND SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear() . ''));

                                if ($qr[1]['END_DATE'] == '') {
                                    if ($mark_id != '') {
                                        $mark_end_qry = DBGet(DBQuery('SELECT END_DATE FROM marking_periods WHERE MARKING_PERIOD_ID =' . $mark_id . ''));
                                        $mark_end_date = date('Y-m-d', strtotime($mark_end_qry[1]['END_DATE']));
                                    } else {
                                        $mark_end_qry = DBGet(DBQuery('SELECT END_DATE FROM course_details WHERE COURSE_PERIOD_ID =' . $course_period_id . ''));
                                        $mark_end_date = date('Y-m-d', strtotime($mark_end_qry[1]['END_DATE']));
                                    }
                                } else {

                                    $mark_end = date('Y-m-d', strtotime($qr[1]['END_DATE']));
                                    if ($mark_id != '') {
                                        $mark_end_qry = DBGet(DBQuery('SELECT END_DATE FROM marking_periods WHERE MARKING_PERIOD_ID =' . $mark_id . ''));
                                        $mark_end_date = date('Y-m-d', strtotime($mark_end_qry[1]['END_DATE']));
                                    } else {
                                        $mark_end_qry = DBGet(DBQuery('SELECT END_DATE FROM course_details WHERE COURSE_PERIOD_ID =' . $course_period_id . ''));
                                        $mark_end_date = date('Y-m-d', strtotime($mark_end_qry[1]['END_DATE']));
                                    }
                                    if (strtotime($qr[1]['END_DATE']) < strtotime($mark_end_qry[1]['END_DATE'])) {
                                        $mark_end_date = $mark_end;
                                    } else {
                                        $mark_end_date = date('Y-m-d', strtotime($mark_end_qry[1]['END_DATE']));
                                    }

                                }

                                //$sql .= 'END_DATE' . '=\'' . str_replace("\'", "''", $mark_end_date) . '\',';
                            }
                            $edt_fetch_start_t = strtotime($edt_qry[1]['START_DATE']);
                            $edt_fetch_end_t = strtotime($edt_qry[1]['END_DATE']);
                            $value = paramlib_validation($column, $value);
                            $end_date_time = strtotime($value);
                            $new_st_date = date('Y-m-d', strtotime($start_date));
                            $new_st_date_time = strtotime($new_st_date);
                            if ($columns['END_DATE'] != '' && $columns['START_DATE'] != '') {

                                if (strtotime($columns['END_DATE']) <= strtotime($columns['START_DATE'])) {

                                    $end_date_msg = "end";
                                } elseif ($column == 'START_DATE') {
                                    $value = paramlib_validation($column, $value);
                                    $start_date_time = strtotime($value);
                                    $enroll_date_sql = DBGet(DBQuery('SELECT START_DATE FROM student_enrollment WHERE SYEAR = \'' . UserSyear() . '\' AND STUDENT_ID = \'' . UserStudentID() . '\''));

                                    if (strtotime($enroll_date_sql[1]['START_DATE']) <= strtotime($value)) {
                                        if ($start_date_time < $edt_fetch_end_t || $edt_fetch_end_t == '') {
                                            $sql .= $column . '=\'' . str_replace("\'", "''", date('Y-m-d', strtotime($value))) . '\',';
                                            $tot_cp .= $course_period_id . ',';
                                        } else {

                                            $start_date_msg = "start";
                                        }
                                    } else {
                                        $start_date_msg = "enroll";
                                    }
                                } else {
                                    if ($start_date_msg != "enroll" && $start_date_msg != "strat") {
                                        $updateValue = str_replace("\'", "''", $column === 'SCHEDULER_LOCK' ? $value : date('Y-m-d', strtotime($value)));
                                        $sql .= $column . '=\'' . $updateValue . '\',';
                                    }
                                }
                            } else {

                                if ($column == 'END_DATE') {
                                    $prev_scheduler_lock = DBGet(DBQuery('SELECT SCHEDULER_LOCK FROM schedule WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND START_DATE=\'' . date('Y-m-d', strtotime($start_date)) . '\''));
                                    if ($mark_id != '') {
                                        $mark_end_qry = DBGet(DBQuery('SELECT END_DATE FROM marking_periods WHERE MARKING_PERIOD_ID =' . $mark_id . ''));
                                        $mark_end_date = date('Y-m-d', strtotime($mark_end_qry[1]['END_DATE']));
                                    } else {
                                        $mark_end_qry = DBGet(DBQuery('SELECT END_DATE FROM course_details WHERE COURSE_PERIOD_ID =' . $course_period_id . ''));
                                        $mark_end_date = date('Y-m-d', strtotime($mark_end_qry[1]['END_DATE']));
                                    }
                                    if ($column == END_DATE && str_replace("\'", "''", $value) == '') {
                                        $sql .= $column . '=\'' . str_replace("\'", "''", $mark_end_date) . '\',';
                                    } elseif ($column == END_DATE && str_replace("\'", "''", $value) != '' && $prev_scheduler_lock[1]['SCHEDULER_LOCK'] != 'Y') {
                                        $mother_date = $value;
                                        $year = substr($mother_date, 7, 4);
                                        $day = substr($mother_date, 0, 2);
                                        $temp_month = substr($mother_date, 3, 3);

                                        if ($temp_month == 'JAN') {
                                            $month = '01';
                                        } elseif ($temp_month == 'FEB') {
                                            $month = '02';
                                        } elseif ($temp_month == 'MAR') {
                                            $month = '03';
                                        } elseif ($temp_month == 'APR') {
                                            $month = '04';
                                        } elseif ($temp_month == 'MAY') {
                                            $month = '05';
                                        } elseif ($temp_month == 'JUN') {
                                            $month = '06';
                                        } elseif ($temp_month == 'JUL') {
                                            $month = '07';
                                        } elseif ($temp_month == 'AUG') {
                                            $month = '08';
                                        } elseif ($temp_month == 'SEP') {
                                            $month = '09';
                                        } elseif ($temp_month == 'OCT') {
                                            $month = '10';
                                        } elseif ($temp_month == 'NOV') {
                                            $month = '11';
                                        } elseif ($temp_month == 'DEC') {
                                            $month = '12';
                                        }

                                        $select_date = $year . '-' . $month . '-' . $day;

                                        $end_date_sql = DBGet(DBQuery('SELECT MAX(SCHOOL_DATE) AS SCHOOL_DATE FROM attendance_period WHERE COURSE_PERIOD_ID = \'' . $course_period_id . '\' AND STUDENT_ID = \'' . UserStudentID() . '\''));

                                        if (strtotime($select_date) >= $edt_fetch_start_t || $new_st_date_time <= strtotime($select_date)) {

                                            if (strtotime($end_date_sql[1]['SCHOOL_DATE']) <= strtotime($select_date)) {

                                                $sql .= $column . '=\'' . str_replace("\'", "''", $value) . '\',';
                                                if ($columns['END_DATE']) {
                                                    DBQuery('DELETE FROM attendance_period WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND SCHOOL_DATE > \'' . $columns['END_DATE'] . '\'');
                                                }

                                            } else {
                                                $end_date_msg = "attn";
                                                $mother_date = $end_date_sql[1]['SCHOOL_DATE'];
                                                $year = substr($mother_date, 0, 4);
                                                $day = substr($mother_date, 8, 2);
                                                $month = substr($mother_date, 5, 2);

                                                $select_date1 = $month . '-' . $day . '-' . $year;
                                                $_SESSION['last_attendance'] = $select_date1;
                                            }
                                        } else {
                                            $end_date_msg = "end";
                                        }
                                    } else {
                                        $sch_lock_msg = "end";
                                    }

                                } else {
                                    if ($column == 'START_DATE') {
                                        if ($value == '') {
                                            $value = date('Y-M-d');
                                        } else {

                                            $value = paramlib_validation($column, $value);
                                        }
                                        $start_date_time = strtotime($value);
                                        $enroll_date_sql = DBGet(DBQuery('SELECT START_DATE FROM student_enrollment WHERE SYEAR = \'' . UserSyear() . '\' AND STUDENT_ID = \'' . UserStudentID() . '\''));

                                        if (strtotime($enroll_date_sql[1]['START_DATE']) <= strtotime($value)) {
                                            if ($start_date_time < $edt_fetch_end_t || $edt_fetch_end_t == '') {
                                                $sql .= $column . '=\'' . str_replace("\'", "''", $value) . '\',';
                                                $tot_cp .= $course_period_id . ',';
                                            } else {
                                                $start_date_msg = "start";
                                            }
                                        } else {
                                            $start_date_msg = "enroll";
                                        }
                                    } else {
                                        $sql .= $column . '=\'' . str_replace("\'", "''", $value) . '\',';
                                    }
                                }
                            }
                        }
                    }

                    if ($columns['START_DATE'] || $columns['END_DATE'] || $columns['MARKING_PERIOD_ID']) {
                        $sql .= 'MODIFIED_DATE' . "='" . DBDate() . "',";
                        $sql .= 'MODIFIED_BY' . "='" . User('STAFF_ID') . "',";
                    }
                    $sql = substr($sql, 0, -1) . ' WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND START_DATE=\'' . date('Y-m-d', strtotime($start_date)) . '\'';
                    DBQuery($sql);
                    ########################### For Missing Attendance ###########################
                    ################################# Start of Filled seats update code ###############################

                    $start_end_RET = DBGet(DBQuery('SELECT START_DATE,END_DATE FROM schedule WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND END_DATE<=CURRENT_DATE AND COURSE_PERIOD_ID=\'' . $course_period_id . '\''));

                    if (count($start_end_RET)) {
                        $end_null_RET = DBGet(DBQuery('SELECT START_DATE,END_DATE FROM schedule WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND END_DATE IS NULL'));
                        if (!count($end_null_RET)) {

                            DBQuery('CALL SEAT_COUNT()');
                        }
                    }

                    ################################# End of Filled seats update code ###############################
                }
            }
            $qr_pre_att_del = 'DELETE FROM attendance_period where STUDENT_ID="' . UserStudentID() . '" and course_period_id="' . $course_period_id . '" and school_date<"' . date('Y-m-d', strtotime($columns['START_DATE'])) . '"';

            DBQuery($qr_pre_att_del);
        }

        if ($tot_cp != '') {
            $tot_cp = substr($tot_cp, 0, -1);

            $all_cps = explode(',', $tot_cp);
            foreach ($all_cps as $cps) {

                $schedule_type_check1 = DBGet(DBQuery("SELECT SCHEDULE_TYPE FROM course_periods WHERE COURSE_PERIOD_ID='" . $cps . "'"));

                if ($schedule_type_check1[1]['SCHEDULE_TYPE'] == 'FIXED') {

                    $day1 = DBGet(DBQuery("SELECT DAYS,PERIOD_ID FROM course_period_var WHERE COURSE_PERIOD_ID='" . $cps . "' AND DOES_ATTENDANCE='Y'"));
                    if (count($day1) > 0) {
                        $days_check = DBGet(DBQuery("SELECT sch.START_DATE FROM schedule sch,course_periods cp WHERE cp.COURSE_PERIOD_ID='" . $cps . "' AND cp.COURSE_PERIOD_ID=sch.COURSE_PERIOD_ID AND cp.COURSE_ID=sch.COURSE_ID AND sch.SCHOOL_ID='" . UserSchool() . "' AND sch.SYEAR='" . UserSyear() . "' AND sch.STUDENT_ID='" . UserStudentID() . "'"));
                        foreach ($days_check as $index => $dates) {
                            $day_found_count = 0;
                            $sec = 0;
                            $total_diff_days = (strtotime(date('Y-m-d')) - strtotime($dates['START_DATE'])) / 86400;
                            for ($i = 0; $i < $total_diff_days; $i++) {

                                $day_found = date('l', strtotime($dates['START_DATE']) + $sec);

                                $dates_all = date('Y-m-d', strtotime($dates['START_DATE']) + $sec);
                                $calendar_id = DBGet(DBQuery("SELECT CALENDAR_ID FROM course_periods WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "' AND COURSE_PERIOD_ID='" . $cps . "'"));
                                $calendar_id = $calendar_id[1]['CALENDAR_ID'];
                                $attendance_day_date = DBGet(DBQuery("SELECT COUNT(*) as PRESENT FROM attendance_calendar WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_DATE='" . $dates_all . "' AND SCHOOL_ID='" . UserSchool() . "' AND CALENDAR_ID='" . $calendar_id . "'"));
                                if ($attendance_day_date[1]['PRESENT'] != 0) {
                                    $day_found_count++;
                                    $teach_id = DBGet(DBquery("SELECT TEACHER_ID FROM teacher_reassignment WHERE course_period_id='" . $cps . "' AND ASSIGN_DATE<='" . $dates_all . "'"));
                                    if ($teach_id[1]['TEACHER_ID'] != '') {
                                        $teachers_id = $teach_id[1]['TEACHER_ID'];
                                    } else {
                                        $teachers_id = DBGet(DBQuery("SELECT TEACHER_ID,SECONDARY_TEACHER_ID FROM course_periods WHERE COURSE_PERIOD_ID='" . $cps . "'"));
                                        if ($teachers_id[1]['SECONDARY_TEACHER_ID'] != '') {
                                            $secondary_teachers_id = $teachers_id[1]['SECONDARY_TEACHER_ID'];
                                        } else {
                                            $secondary_teachers_id = '';
                                        }

                                        $teachers_id = $teachers_id[1]['TEACHER_ID'];
                                    }

                                    $attendance_completed_check = DBGet(DBQuery("SELECT COUNT(*) as COMPLETED FROM attendance_completed WHERE PERIOD_ID='" . $day1[1]['PERIOD_ID'] . "' AND COURSE_PERIOD_ID='" . $cps . "'
                                                                    AND SCHOOL_DATE='" . $dates_all . "'"));
                                    $stu_attn_count = DBGet(DBQuery('SELECT COUNT(STUDENT_ID) AS STUDENT_ID FROM attendance_period WHERE SCHOOL_DATE=\'' . $dates_all . '\' AND PERIOD_ID=\'' . $day1[1]['PERIOD_ID'] . '\' AND COURSE_PERIOD_ID=\'' . $cps . '\''));
                                    $stu_sch_count = DBGet(DBQuery('SELECT COUNT(ID) AS ID FROM schedule WHERE START_DATE<=\'' . $dates_all . '\' AND (END_DATE IS NULL OR END_DATE=\'0000-00-00\' OR END_DATE>=\'' . $dates_all . '\') AND COURSE_PERIOD_ID=\'' . $cps . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
                                    if ($attendance_completed_check[1]['COMPLETED'] == 0 || ($stu_sch_count[1]['ID'] > $stu_attn_count[1]['STUDENT_ID'])) {
                                        $cpsMarkingPeriod = DBGet(DBQuery("select marking_period_id from course_periods where course_period_id = $cps;"))[1]['MARKING_PERIOD_ID'];

                                        if (isDateInMarkingPeriodWorkingDates($cpsMarkingPeriod, $dates_all)) {
                                            if ($secondary_teachers_id != '') {
                                                DBQuery("INSERT INTO missing_attendance (SCHOOL_ID,SYEAR,SCHOOL_DATE,COURSE_PERIOD_ID,PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID)
                                                VALUES ('" . UserSchool() . "','" . UserSyear() . "','" . $dates_all . "','" . $cps . "','" . $day1[1]['PERIOD_ID'] . "','" . $teachers_id . "','" . $secondary_teachers_id . "')");
                                            } else {
                                                DBQuery("INSERT INTO missing_attendance (SCHOOL_ID,SYEAR,SCHOOL_DATE,COURSE_PERIOD_ID,PERIOD_ID,TEACHER_ID)
                                                VALUES ('" . UserSchool() . "','" . UserSyear() . "','" . $dates_all . "','" . $cps . "','" . $day1[1]['PERIOD_ID'] . "','" . $teachers_id . "')");
                                            }
                                        }
                                    }
                                }
                                $sec = $sec + 86400;
                            }
                        }
                    }
                }
                if ($schedule_type_check1[1]['SCHEDULE_TYPE'] == 'VARIABLE') {
                    $day1 = DBGet(DBQuery("SELECT DAYS,PERIOD_ID FROM course_period_var WHERE COURSE_PERIOD_ID='" . $cps . "' AND DOES_ATTENDANCE='Y'"));
                    foreach ($day1 as $index => $day) {
                        if ($day['DAYS'] == 'M') {
                            $day2[$day['PERIOD_ID']] = 'Monday';
                        }

                        if ($day['DAYS'] == 'T') {
                            $day2[$day['PERIOD_ID']] = 'Tuesday';
                        }

                        if ($day['DAYS'] == 'W') {
                            $day2[$day['PERIOD_ID']] = 'Wednesday';
                        }

                        if ($day['DAYS'] == 'H') {
                            $day2[$day['PERIOD_ID']] = 'Thursday';
                        }

                        if ($day['DAYS'] == 'F') {
                            $day2[$day['PERIOD_ID']] = 'Friday';
                        }

                        if ($day['DAYS'] == 'S') {
                            $day2[$day['PERIOD_ID']] = 'Saturday';
                        }

                        if ($day['DAYS'] == 'U') {
                            $day2[$day['PERIOD_ID']] = 'Sunday';
                        }

                        $days_check = DBGet(DBQuery("SELECT sch.START_DATE FROM schedule sch,course_periods cp WHERE cp.COURSE_PERIOD_ID='" . $cps . "' AND cp.COURSE_PERIOD_ID=sch.COURSE_PERIOD_ID AND cp.COURSE_ID=sch.COURSE_ID AND sch.SCHOOL_ID='" . UserSchool() . "' AND sch.SYEAR='" . UserSyear() . "' AND sch.STUDENT_ID='" . UserStudentID() . "'"));
                        foreach ($days_check as $index => $dates) {
                            $day_found_count = 0;
                            $sec = 0;
                            $total_diff_days = (strtotime(date('Y-m-d')) - strtotime($dates['START_DATE'])) / 86400;
                            for ($i = 0; $i < $total_diff_days; $i++) {

                                $day_found = date('l', strtotime($dates['START_DATE']) + $sec);

                                if ($day_found == $day2[$day['PERIOD_ID']]) {

                                    $dates_all = date('Y-m-d', strtotime($dates['START_DATE']) + $sec);
                                    $calendar_id = DBGet(DBQuery("SELECT CALENDAR_ID FROM course_periods WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "' AND COURSE_PERIOD_ID='" . $cps . "'"));
                                    $calendar_id = $calendar_id[1]['CALENDAR_ID'];
                                    $attendance_day_date = DBGet(DBQuery("SELECT COUNT(*) as PRESENT FROM attendance_calendar WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_DATE='" . $dates_all . "' AND SCHOOL_ID='" . UserSchool() . "' AND CALENDAR_ID='" . $calendar_id . "'"));
                                    if ($attendance_day_date[1]['PRESENT'] != 0) {
                                        $day_found_count++;
                                        $teach_id = DBGet(DBquery("SELECT TEACHER_ID FROM teacher_reassignment WHERE course_period_id='" . $cps . "' AND ASSIGN_DATE<='" . $dates_all . "'"));
                                        if ($teach_id[1]['TEACHER_ID'] != '') {
                                            $teachers_id = $teach_id[1]['TEACHER_ID'];
                                        } else {
                                            $teachers_id = DBGet(DBQuery("SELECT TEACHER_ID,SECONDARY_TEACHER_ID FROM course_periods WHERE COURSE_PERIOD_ID='" . $cps . "'"));
                                            if ($teachers_id[1]['SECONDARY_TEACHER_ID'] != '') {
                                                $secondary_teachers_id = $teachers_id[1]['SECONDARY_TEACHER_ID'];
                                            } else {
                                                $secondary_teachers_id = '';
                                            }

                                            $teachers_id = $teachers_id[1]['TEACHER_ID'];
                                        }

                                        $attendance_completed_check = DBGet(DBQuery("SELECT COUNT(*) as COMPLETED FROM attendance_completed WHERE PERIOD_ID='" . $day['PERIOD_ID'] . "' AND COURSE_PERIOD_ID='" . $cps . "'
                                                                    AND SCHOOL_DATE='" . $dates_all . "'"));

                                        $stu_attn_count = DBGet(DBQuery('SELECT COUNT(STUDENT_ID) AS STUDENT_ID FROM attendance_period WHERE SCHOOL_DATE=\'' . $dates_all . '\' AND PERIOD_ID=\'' . $day['PERIOD_ID'] . '\' AND COURSE_PERIOD_ID=\'' . $cps . '\''));
                                        $stu_sch_count = DBGet(DBQuery('SELECT COUNT(ID) AS ID FROM schedule WHERE START_DATE<=\'' . $dates_all . '\' AND (END_DATE IS NULL OR END_DATE=\'0000-00-00\' OR END_DATE>=\'' . $dates_all . '\') AND COURSE_PERIOD_ID=\'' . $cps . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));

                                        if ($attendance_completed_check[1]['COMPLETED'] == 0 || ($stu_sch_count[1]['ID'] > $stu_attn_count[1]['STUDENT_ID'])) {
                                            $cpsMarkingPeriod = DBGet(DBQuery("select marking_period_id from course_periods where course_period_id = $cps;"))[1]['MARKING_PERIOD_ID'];

                                            if (isDateInMarkingPeriodWorkingDates($cpsMarkingPeriod, $dates_all)) {
                                                if ($secondary_teachers_id != '') {
                                                    DBQuery("INSERT INTO missing_attendance (SCHOOL_ID,SYEAR,SCHOOL_DATE,COURSE_PERIOD_ID,PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID)
                                                    VALUES ('" . UserSchool() . "','" . UserSyear() . "','" . $dates_all . "','" . $cps . "','" . $day['PERIOD_ID'] . "','" . $teachers_id . "','" . $secondary_teachers_id . "')");
                                                } else {
                                                    DBQuery("INSERT INTO missing_attendance (SCHOOL_ID,SYEAR,SCHOOL_DATE,COURSE_PERIOD_ID,PERIOD_ID,TEACHER_ID)
                                                    VALUES ('" . UserSchool() . "','" . UserSyear() . "','" . $dates_all . "','" . $cps . "','" . $day['PERIOD_ID'] . "','" . $teachers_id . "')");
                                                }
                                            }
                                        }
                                    }
                                }
                                $sec = $sec + 86400;
                            }
                        }
                    }
                }

                if ($schedule_type_check1[1]['SCHEDULE_TYPE'] == 'BLOCKED') {

                    $block_schedule_vals = DBGet(DBQuery("SELECT COURSE_PERIOD_DATE,PERIOD_ID FROM course_period_var WHERE COURSE_PERIOD_ID='" . $cps . "' AND DOES_ATTENDANCE='Y'"));

                    foreach ($block_schedule_vals as $index => $vals) {
                        $calendar_id = DBGet(DBQuery("SELECT CALENDAR_ID FROM course_periods WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "' AND COURSE_PERIOD_ID='" . $cps . "'"));
                        $calendar_id = $calendar_id[1]['CALENDAR_ID'];
                        $attendance_day_date = DBGet(DBQuery("SELECT COUNT(*) as PRESENT FROM attendance_calendar WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_DATE='" . $vals['COURSE_PERIOD_DATE'] . "' AND SCHOOL_ID='" . UserSchool() . "' AND CALENDAR_ID='" . $calendar_id . "'"));

                        if ($attendance_day_date[1]['PRESENT'] != 0) {
                            $days_check = DBGet(DBQuery("SELECT sch.START_DATE FROM schedule sch,course_periods cp WHERE cp.COURSE_PERIOD_ID='" . $cps . "' AND cp.COURSE_PERIOD_ID=sch.COURSE_PERIOD_ID AND cp.COURSE_ID=sch.COURSE_ID AND sch.SCHOOL_ID='" . UserSchool() . "' AND sch.SYEAR='" . UserSyear() . "' AND sch.STUDENT_ID='" . UserStudentID() . "' AND sch.START_DATE<='" . $vals['COURSE_PERIOD_DATE'] . "'"));
                            if ($days_check[1]['START_DATE'] != '') {
                                $teach_id = DBGet(DBquery("SELECT TEACHER_ID FROM teacher_reassignment WHERE course_period_id='" . $cps . "' AND ASSIGN_DATE<='" . $vals['COURSE_PERIOD_DATE'] . "'"));
                                if ($teach_id[1]['TEACHER_ID'] != '') {
                                    $teachers_id = $teach_id[1]['TEACHER_ID'];
                                } else {
                                    $teachers_id = DBGet(DBQuery("SELECT TEACHER_ID,SECONDARY_TEACHER_ID FROM course_periods WHERE COURSE_PERIOD_ID='" . $cps . "'"));
                                    if ($teachers_id[1]['SECONDARY_TEACHER_ID'] != '') {
                                        $secondary_teachers_id = $teachers_id[1]['SECONDARY_TEACHER_ID'];
                                    } else {
                                        $secondary_teachers_id = '';
                                    }

                                    $teachers_id = $teachers_id[1]['TEACHER_ID'];
                                }
                                $attendance_completed_check = DBGet(DBQuery("SELECT COUNT(*) as COMPLETED FROM attendance_completed WHERE PERIOD_ID='" . $vals['PERIOD_ID'] . "' AND COURSE_PERIOD_ID='" . $cps . "'
                                                                    AND SCHOOL_DATE='" . $vals['COURSE_PERIOD_DATE'] . "'"));
                                $stu_attn_count = DBGet(DBQuery('SELECT COUNT(STUDENT_ID) AS STUDENT_ID FROM attendance_period WHERE SCHOOL_DATE=\'' . $vals['COURSE_PERIOD_DATE'] . '\' AND PERIOD_ID=\'' . $vals['PERIOD_ID'] . '\' AND COURSE_PERIOD_ID=\'' . $cps . '\''));
                                $stu_sch_count = DBGet(DBQuery('SELECT COUNT(ID) AS ID FROM schedule WHERE START_DATE<=\'' . $vals['COURSE_PERIOD_DATE'] . '\' AND (END_DATE IS NULL OR END_DATE=\'0000-00-00\' OR END_DATE>=\'' . $vals['COURSE_PERIOD_DATE'] . '\') AND COURSE_PERIOD_ID=\'' . $cps . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
                                if ($attendance_completed_check[1]['COMPLETED'] == 0 || ($stu_sch_count[1]['ID'] > $stu_attn_count[1]['STUDENT_ID'])) {
                                    $cpsMarkingPeriod = DBGet(DBQuery("select marking_period_id from course_periods where course_period_id = $cps;"))[1]['MARKING_PERIOD_ID'];

                                    if (isDateInMarkingPeriodWorkingDates($cpsMarkingPeriod, $dates_all)) {
                                        if ($secondary_teachers_id != '') {
                                            DBquery("INSERT INTO missing_attendance (SCHOOL_ID,SYEAR,SCHOOL_DATE,COURSE_PERIOD_ID,PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID)
                                            VALUES ('" . UserSchool() . "','" . UserSyear() . "','" . $vals['COURSE_PERIOD_DATE'] . "','" . $cps . "','" . $vals['PERIOD_ID'] . "','" . $teachers_id . "','" . $secondary_teachers_id . "')");
                                        } else {
                                            DBquery("INSERT INTO missing_attendance (SCHOOL_ID,SYEAR,SCHOOL_DATE,COURSE_PERIOD_ID,PERIOD_ID,TEACHER_ID)
                                            VALUES ('" . UserSchool() . "','" . UserSyear() . "','" . $vals['COURSE_PERIOD_DATE'] . "','" . $cps . "','" . $vals['PERIOD_ID'] . "','" . $teachers_id . "')");
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $stu_missing_atten = DBGet(DBQuery('SELECT * FROM missing_attendance WHERE COURSE_PERIOD_ID=\'' . $course_period_id . '\''));
        }
        DBQuery("CALL SEAT_FILL()");
        unset($_SESSION['_REQUEST_vars']['schedule']);
        unset($_REQUEST['schedule']);
    }

    if (UserStudentID() && $_REQUEST['modfunc'] != 'choose_course' && $_REQUEST['modfunc'] != 'more_info') {
        echo '<div class="panel panel-default">';
        echo '<div class="panel-body">';
        echo "<FORM name=modify class=no-margin id=modify action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=modify METHOD=POST>";

        $tmp_REQUEST = $_REQUEST;
        unset($tmp_REQUEST['include_inactive']);

        ##################################################################

        $years_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,NULL AS SEMESTER_ID FROM school_years WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . "'"));

        $semesters_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,NULL AS SEMESTER_ID FROM school_semesters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER'));

        $uarters_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,SEMESTER_ID FROM school_quarters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER'));

        $mp_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,SORT_ORDER,1 AS TBL FROM school_years WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' UNION SELECT MARKING_PERIOD_ID,TITLE,SORT_ORDER,2 AS TBL FROM school_semesters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' UNION SELECT MARKING_PERIOD_ID,TITLE,SORT_ORDER,3 AS TBL FROM school_quarters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY TBL,SORT_ORDER'));

        $mp = CreateSelect($mp_RET, 'marking_period_id', 'Modules.php?modname=' . $_REQUEST['modname'] . '&day_date=' . $_REQUEST['day_date'] . '&month_date=' . $_REQUEST['month_date'] . '&year_date=' . $_REQUEST['year_date'] .  '&marking_period_id=', $_REQUEST['marking_period_id']);

        ###################################################################3

        $time = strtotime($date);
        $newformat = date('Y-m-d', $time);
        $_SESSION['schedule_selected_date'] = $newformat;

        echo '<div class="row">';
        echo '<div class="col-md-3">';
        echo '<div class="form-group" id="filter"><label class="control-label">' . _date . '</label>' . PrepareDateSchedule($newformat, '_date', false, array('submit' => true)) . '</div>';
        echo '</div>'; //col-md-3

        ?>
            <script>
                $("#filter :input").change(function(e) {
                    const date = e.target.value.split("-")
                    const markingPeriodId = $("#marking_period_id").val();
                    const location='Modules.php?modname=<?php echo $_REQUEST['modname'] ?>&marking_period_id=' + markingPeriodId + '&month_date='+date[1]+'&day_date='+date[2]+'&year_date='+date[0];
                    // console.log(location)
                    window.location=location
                    // +'&month_date=8&day_date=12&year_date=2020'
                });
            </script>
        <?php

        echo '<div class="col-md-3">';
        echo '<div class="form-group"><label class="control-label">' . _markingPeriod . '</label>' . $mp . '</div>';
        echo '</div>'; //.col-md-3

        echo '<div class="col-md-3">';
        echo '<div class="form-group"><label class="control-label">&nbsp;</label><div class="checkbox"><label><INPUT type=checkbox name=include_inactive value=Y' . ($_REQUEST['include_inactive'] == 'Y' ? " CHECKED onclick='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST) . "&include_inactive=\";'" : " onclick='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST) . "&include_inactive=Y\";'") . '>' . _includeInactiveCourses . '</label></div></div>';
        echo '</div>'; //.col-md-3
        echo '</div>'; //.row

        echo '<div class="form-group">' . SubmitButton(_save, '', 'class="btn btn-primary" onclick=\'formload_ajax("modify");self_disable(this);\'') . '</div>';

        $fy_id = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
        $fy_id = $fy_id[1]['MARKING_PERIOD_ID'];

        $sql = 'SELECT
                              s.COURSE_ID as ACTION,
				s.COURSE_ID,s.COURSE_PERIOD_ID,s.ID AS SCHEDULE_ID,
				s.MARKING_PERIOD_ID,s.START_DATE,s.END_DATE,s.MODIFIED_DATE,s.MODIFIED_BY,
				UNIX_TIMESTAMP(s.START_DATE) AS START_EPOCH,UNIX_TIMESTAMP(s.END_DATE) AS END_EPOCH,sp.PERIOD_ID,
				cpv.PERIOD_ID,cp.MARKING_PERIOD_ID as COURSE_MARKING_PERIOD_ID,cp.MP,sp.SORT_ORDER,
				c.TITLE,cp.COURSE_PERIOD_ID AS PERIOD_PULLDOWN,
				s.STUDENT_ID,r.TITLE AS ROOM,(SELECT GROUP_CONCAT(cpv.DAYS) FROM course_period_var cpv WHERE cpv.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID) as DAYS,SCHEDULER_LOCK,CONCAT(st.LAST_NAME, \'' . ' ' . '\' ,st.FIRST_NAME) AS MODIFIED_NAME
			FROM courses c,course_periods cp,course_period_var cpv,school_periods sp,rooms r,schedule s
                        LEFT JOIN staff st ON s.MODIFIED_BY = st.STAFF_ID
			WHERE
			 s.COURSE_ID = c.COURSE_ID AND s.COURSE_ID = cp.COURSE_ID
				AND s.COURSE_PERIOD_ID = cp.COURSE_PERIOD_ID
                                AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID
                                 AND r.ROOM_ID=cpv.ROOM_ID
				AND s.SCHOOL_ID = sp.SCHOOL_ID AND s.SYEAR = c.SYEAR AND sp.PERIOD_ID = cpv.PERIOD_ID
				AND s.STUDENT_ID=\'' . UserStudentID() . '\'
				AND s.SYEAR=\'' . UserSyear() . '\'';
        $sql .= ' AND s.SCHOOL_ID = \'' . UserSchool() . '\'';

        if ($_REQUEST['include_inactive'] != 'Y') {
            $sql .= ' AND (\'' . date('Y-m-d', strtotime($date)) . '\' BETWEEN s.START_DATE AND s.END_DATE OR (s.END_DATE IS NULL AND s.START_DATE<=\'' . date('Y-m-d', strtotime($date)) . '\')) ';
        }

        if (clean_param($_REQUEST['marking_period_id'], PARAM_INT)) {
            $mp_id = $_REQUEST['marking_period_id'];
        }

        if (!isset($_REQUEST['marking_period_id'])) {
            $mp_id = UserMP();
        }
        $sql .= ' AND (s.MARKING_PERIOD_ID IN (' . GetAllMP_Mod(GetMPTable(GetMP($mp_id, 'TABLE')), $mp_id) . ')  OR s.MARKING_PERIOD_ID IS NULL)';
        $sql .= ' GROUP BY cp.COURSE_PERIOD_ID ORDER BY sp.SORT_ORDER,s.MARKING_PERIOD_ID';

        $QI = DBQuery($sql);

        if ($_REQUEST['_openSIS_PDF'] == true) {
            $schedule_RET = DBGet($QI, array('ACTION' => '_makeAction', 'TITLE' => '_makeTitle', 'PERIOD_PULLDOWN' => '_makePeriodSelect', 'DAYS' => '_makeDays', 'COURSE_MARKING_PERIOD_ID' => '_makeMPSelect', 'SCHEDULER_LOCK' => '_makeLock', 'START_DATE' => '_makeDate', 'END_DATE' => '_makeDate', 'SCHEDULE_ID' => '_makeInfo'));
            //$schedule_RET = DBGet($QI, array('ACTION' => '_makeAction', 'TITLE' => '_makeTitle', 'PERIOD_PULLDOWN' => '_makePeriodSelect', 'DAYS' => '_makeDays', 'COURSE_MARKING_PERIOD_ID' => '_makeMPSelect', 'SCHEDULER_LOCK' => '_makeLock', 'START_DATE' => '_makeDate_red', 'END_DATE' => '_makeDate', 'SCHEDULE_ID' => '_makeInfo'));
            //       print_r($schedule_RET);
        } else {
            $schedule_RET = DBGet($QI, array('ACTION' => '_makeAction', 'TITLE' => '_makeTitle', 'PERIOD_PULLDOWN' => '_makePeriodSelect', 'DAYS' => '_makeDays', 'COURSE_MARKING_PERIOD_ID' => '_makeMPSelect_red', 'SCHEDULER_LOCK' => '_makeLock', 'START_DATE' => '_makeDate', 'END_DATE' => '_makeDate', 'SCHEDULE_ID' => '_makeInfo'));
        }

        //$schedule_RET = DBGet($QI, array('ACTION' => '_makeAction', 'TITLE' => '_makeTitle', 'PERIOD_PULLDOWN' => '_makePeriodSelect', 'DAYS' => '_makeDays', 'COURSE_MARKING_PERIOD_ID' => '_makeMPSelect_red', 'SCHEDULER_LOCK' => '_makeLock', 'START_DATE' => '_makeDate_red', 'END_DATE' => '_makeDate', 'SCHEDULE_ID' => '_makeInfo'));
        $link['add']['link'] = "javascript:void(0) data-toggle='modal' data-target='#modal_default_cp_calc' onclick='cleanModal(\"course_modal_cp\");cleanModal(\"cp_modal_cp\");cleanTempData();'";
//        $link['add']['link'] = "javascript:void(0) data-toggle='modal' data-target='#modal_default' onclick='window.open(\"ForWindow.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=choose_course&ses=1\",\"\",\"scrollbars=yes,resizable=yes,width=800,height=400\");' ";
        $link['add']['title'] = "" . _addACourse . "";

        if (User('PROFILE') == 'teacher') {
            $columns = array('TITLE' => _course,
                'PERIOD_PULLDOWN' => _periodTeacher,
                'ROOM' => _room,
                'DAYS' => _daysOfWeek,
                'COURSE_MARKING_PERIOD_ID' => _term,
                'SCHEDULER_LOCK' => '<IMG SRC=assets/locked.gif border=0>',
                'START_DATE' => _enrolled,
                'END_DATE' => _endDateDropDate,
                'SCHEDULE_ID' => _moreInfo,
            );
        } else {
            $columns = array('ACTION' => _action,
                'TITLE' => _course,
                'PERIOD_PULLDOWN' => _periodTeacher,
                'ROOM' => _room,
                'DAYS' => _daysOfWeek,
                'COURSE_MARKING_PERIOD_ID' => _term,
                'SCHEDULER_LOCK' => '<IMG SRC=assets/locked.gif border=0>',
                'START_DATE' => _enrolled,
                'END_DATE' => _endDateDropDate,
                'SCHEDULE_ID' => _moreInfo,
            );
        }

        $days_RET = DBGet(DBQuery('SELECT DISTINCT DAYS FROM course_period_var'));
        if (count($days_RET) == 1) {
            unset($columns['DAYS']);
        }

        if ($_REQUEST['_openSIS_PDF']) {
            unset($columns['SCHEDULER_LOCK']);
        }

        if ($start_date_msg == "start") {
            echo "<b style='color:red'>" . _enrolledDateCannotBeAfterDroppedDate . "</b>";

            unset($start_date_msg);
        }
        if ($start_date_msg == "enroll") {
            echo "<b style='color:red'>" . _courseEnrolledDateCannotBeBeforeStudentsSchoolStartDate . "</b>";

            unset($start_date_msg);
        }
        if ($start_date_msg == "re_enroll") {
            echo "<b style='color:red'>" . _courseEnrolledDateCannotBeBeforeStudentsCourseStartDate . "</b>";

            unset($start_date_msg);
        }

        if ($start_date_msg == "prev_schdl_error") {
            echo "<b style='color:red'>" . _courseEnrolledDateCannotBeBeforeDropdateOfPreviousSchedule . "</b>";

            unset($start_date_msg);
        }
        if ($start_date_msg == "dropped_schdl_error") {

            echo "<b style='color:red'>" . _youCannotModifyTheScheduleEnrolledDateAsItsClashingWithOtherDroppedCourse . "</b>";

            unset($start_date_msg);
        }
        if ($start_date_msg == "error") {
            echo "<b style='color:red'>" . _courseEnrolledDateCannotBeBeforeDropdateOfPreviousSchedule . "</b>";

            unset($start_date_msg);
        }
        if ($end_date_msg == "end") {
            echo "<b style='color:red'>" . _pleaseEnterProperDroppedDateDroppedDateMustBeGreaterThanStartDate . "</b>";

            unset($end_date_msg);
        }

        if ($sch_lock_msg == "end") {
            echo "<b style='color:red'>" . _thisScheduleIsLockedDroppedDateCanNotBeChanged . "</b>";

            unset($sch_lock_msg);
        }
        if ($end_date_msg == "attn" && strtotime($end_d) < strtotime($_SESSION['last_attendance'])) {

            echo "<b style='color:red'>" . _courseCannotBeDroppedBecauseStudentHasGotAttendanceTill . " " . $_SESSION['last_attendance'] . ".</b>";

            unset($end_date_msg);
        }
        VerifySchedule($schedule_RET);

        echo '<hr class="no-margin"/>';

        echo '<div class="table-responsive">';
        ListOutputSchedule($schedule_RET, $columns, _course, _courses, $link);
        echo '</div>';

        if (!$schedule_RET) {
            echo '';
        } else {
            echo '<div class="panel-footer no-padding-bottom">' . ProgramLinkforExport('scheduling/PrintSchedules.php', '<b><i class="icon-printer4"></i></b>' . _printSchedule . '', '&modfunc=save&st_arr[]=' . UserStudentID() . '&mp_id=' . $mp_id . '&include_inactive=' . $_REQUEST['include_inactive'] . '&_openSIS_PDF=true', ' target=_blank class="btn btn-success btn-labeled"') . ' &nbsp; ';
            echo SubmitButton(_save, '', 'class="btn btn-primary" onclick=\'formload_ajax("modify");self_disable(this);\'') . '</div>';
        }

        echo '</FORM>';
        //echo "<div class=break></div>";
        echo '</div>'; //.panel-body
        echo '</div>'; //.panel

//        echo '<div class="panel panel-default">';
        //         echo '<div class="panel-body">';
        //
        //        $qr = 'SELECT em.STUDENT_ID,em.ACTIVITY_ID,ea.TITLE,ea.START_DATE,ea.END_DATE FROM eligibility_activities ea,student_eligibility_activities em WHERE  em.STUDENT_ID=' . UserStudentID() . ' AND em.SYEAR=\'' . UserSyear() . '\' ';
        //        $st_date = date('Y-m-d', strtotime($date));
        //        $qr.= ' AND \'' . $st_date . '\' BETWEEN ea.start_date AND ea.end_date';
        //        $qr.= '  AND em.SYEAR=ea.SYEAR AND em.ACTIVITY_ID=ea.ID ORDER BY ea.START_DATE';
        //
        //        $RET_AC = DBGet(DBQuery($qr), array('START_DATE' => 'ProperDate', 'END_DATE' => 'ProperDate'));
        //
        //
        //        $columns = array('TITLE' => 'Activity', 'START_DATE' => 'Starts', 'END_DATE' => 'Ends');
        //        echo '<div class="table-responsive">';
        //        ListOutput($RET_AC, $columns, activity, activities);
        //        echo '</div>';
        ////        echo "<div class=break></div>";
        //
        //
        //        $RET_C = DBGet(DBQuery('SELECT e.ELIGIBILITY_CODE,e.SCHOOL_DATE,c.TITLE as COURSE_TITLE FROM eligibility e,courses c,course_periods cp WHERE e.STUDENT_ID=\'' . UserStudentID() . '\' AND e.SYEAR=\'' . UserSyear() . '\' AND e.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND cp.COURSE_ID=c.COURSE_ID AND e.SCHOOL_DATE>=\'' . date('Y-m-d', strtotime($date)) . '\''), array('ELIGIBILITY_CODE' => '_makeLower','SCHOOL_DATE' => 'ProperDate'));
        //        $columns = array('COURSE_TITLE' => 'Course', 'ELIGIBILITY_CODE' => 'Grade', 'SCHOOL_DATE' => 'Date');
        //        echo '<div class="table-responsive">';
        //        ListOutput($RET_C, $columns, 'Course', 'Courses22');
        //         echo '</div>';
        //        echo '</div>'; //.panel-body
        //        echo '</div>';

        if (AllowEdit()) {
            unset($_REQUEST);
            $_REQUEST['modname'] = 'scheduling/Schedule.php';
            $_REQUEST['search_modfunc'] = 'list';
            $extra['link']['FULL_NAME']['link'] = 'Modules.php?modname=scheduling/Requests.php';
            $extra['link']['FULL_NAME']['variables'] = array('subject_id' => 'SUBJECT_ID', 'course_id' => 'COURSE_ID');
            include 'modules/scheduling/UnfilledRequests.php';
        }

    }
    if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'choose_course') {
//echo 'dgf';
        if (!isset($_REQUEST['confirm_cid']) || !$_REQUEST['sel_course_period']) {
            include "modules/scheduling/MultiCoursesforWindow.php";
        } else {
            foreach ($_REQUEST['sel_course_period'] as $ses_cpid => $select_cpid) {

                $student_start_date = DBGet(DBQuery('SELECT START_DATE FROM student_enrollment WHERE student_id=' . UserStudentID() . ' AND SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear()));
                $student_start_date = $student_start_date[1]['START_DATE'];
                $get_cp_date = DBGet(DBQuery('SELECT BEGIN_DATE FROM course_periods WHERE course_period_id=' . $select_cpid));
                if (strtotime($date) < strtotime($get_cp_date[1]['BEGIN_DATE'])) {
                    $date = (strtotime($get_cp_date[1]['BEGIN_DATE']) < strtotime($student_start_date) ? $student_start_date : $get_cp_date[1]['BEGIN_DATE']);
                }

                DBQuery("INSERT INTO schedule (SYEAR,SCHOOL_ID,STUDENT_ID,START_DATE,MODIFIED_DATE,MODIFIED_BY,COURSE_ID,COURSE_PERIOD_ID,MP,MARKING_PERIOD_ID) values('" . UserSyear() . "','" . UserSchool() . "','" . UserStudentID() . "','" . $date . "','" . $date . "','" . User('STAFF_ID') . "','" . clean_param($_SESSION['crs_id'][$ses_cpid], PARAM_INT) . "','" . clean_param($select_cpid, PARAM_INT) . "','" . clean_param($_SESSION['mp'][$ses_cpid], PARAM_ALPHA) . "','" . clean_param((GetCpDet($ses_cpid, 'MARKING_PERIOD_ID') != '' ? $_SESSION['marking_period_id'][$ses_cpid] : GetMPId('FY')), PARAM_INT) . "')");
                DBQuery('UPDATE course_periods SET FILLED_SEATS=FILLED_SEATS+1 WHERE COURSE_PERIOD_ID=\'' . clean_param($select_cpid, PARAM_INT) . '\'');
            }
            unset($_SESSION['course_period']);
            unset($_SESSION['crs_id']);
            unset($_SESSION['marking_period_id']);
            unset($_SESSION['mp']);
            echo "<script language=javascript>opener.document.location = 'Modules.php?modname=" . clean_param($_REQUEST['modname'], PARAM_NOTAGS) . "&time=" . time() . "';window.close();</script>";
        }
    }

    if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'more_info') {
//    if (clean_param($_REQUEST['search_modfunc'], PARAM_ALPHAMOD) == 'list') {
        $sql = 'SELECT
                                s.COURSE_ID,s.COURSE_PERIOD_ID,
                                s.MARKING_PERIOD_ID,s.START_DATE,s.END_DATE,s.MODIFIED_DATE,s.MODIFIED_BY,
                                UNIX_TIMESTAMP(s.START_DATE) AS START_EPOCH,UNIX_TIMESTAMP(s.END_DATE) AS END_EPOCH,sp.PERIOD_ID,
                                cpv.PERIOD_ID,s.MARKING_PERIOD_ID as COURSE_MARKING_PERIOD_ID,cp.MARKING_PERIOD_ID as mpa_id,cp.MP,sp.SORT_ORDER,
                                c.TITLE,cp.COURSE_PERIOD_ID AS PERIOD_PULLDOWN,
                                s.STUDENT_ID,r.TITLE AS ROOM,(SELECT GROUP_CONCAT(cpv.DAYS) FROM course_period_var cpv WHERE cpv.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID) as DAYS,SCHEDULER_LOCK,CONCAT(st.LAST_NAME, \'' . ' ' . '\' ,st.FIRST_NAME) AS MODIFIED_NAME
                                FROM courses c,course_periods cp,course_period_var cpv,rooms r,school_periods sp,schedule s
                                LEFT JOIN staff st ON s.MODIFIED_BY = st.STAFF_ID
                                WHERE
                                s.COURSE_ID = c.COURSE_ID AND s.COURSE_ID = cp.COURSE_ID
                                AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID
                                 AND r.ROOM_ID=cpv.ROOM_ID
                                AND s.COURSE_PERIOD_ID = cp.COURSE_PERIOD_ID
                                AND s.SCHOOL_ID = sp.SCHOOL_ID AND s.SYEAR = c.SYEAR AND sp.PERIOD_ID = cpv.PERIOD_ID
                                AND s.ID=' . $_REQUEST[id] . '  GROUP BY cp.COURSE_PERIOD_ID';

        $QI = DBQuery($sql);
        $schedule_RET = DBGet($QI, array('TITLE' => '_makeTitle', 'PERIOD_PULLDOWN' => '_makePeriodSelect', 'COURSE_MARKING_PERIOD_ID' => '_makeMPA', 'DAYS' => '_makeDays', 'SCHEDULER_LOCK' => '_makeViewLock', 'START_DATE' => '_makeViewDate', 'END_DATE' => '_makeViewDate', 'MODIFIED_DATE' => '_makeViewDate'));
        $columns = array('TITLE' => _course,
            'PERIOD_PULLDOWN' => _periodTeacher,
            'ROOM' => _room,
            'DAYS' => _daysOfWeek,
            'COURSE_MARKING_PERIOD_ID' => _term,
            'SCHEDULER_LOCK' => '<IMG SRC=assets/locked.gif border=0>',
            'START_DATE' => _enrolled,
            'END_DATE' => _endDateDropDate,
            'MODIFIED_NAME' => _modifiedBy,
            'MODIFIED_DATE' => _modifiedDate,
        );
        $options = array('search' => false, 'count' => false, 'save' => false, 'sort' => false);
        ListOutput($schedule_RET, $columns, _course, _courses, $link, '', $options);

        echo '<br /><div align="center"><input type="button" class="btn btn-primary" value="' . _close . '" onclick="window.close();"></div>';
    }
}

function _makeTitle($value, $column = '')
{
    global $_openSIS, $THIS_RET;
    return $value;
}

///For deleting schedules
function _makeAction($value)
{
    global $THIS_RET;
    $day = $_REQUEST['day_date'];
    $month   = $_REQUEST['month_date'];
    $year = $_REQUEST['year_date'];
    $i = UserStudentId();
    $rem = "<center><a href=Modules.php?modname=scheduling/Schedule.php&student_id=$i&del=true&c_id=$value&cp_id=$THIS_RET[COURSE_PERIOD_ID]&schedule_id=$THIS_RET[SCHEDULE_ID]&month_date=$month&day_date=$day&year_date=$year class=\"btn btn-danger btn-xs btn-icon\"><i class=\"fa fa-times\"></i></a></center>";

    return $rem;
}

function _makeViewLock($value, $column)
{
    global $THIS_RET;

    if ($value == 'Y') {
        $img = 'locked';
    } else {
        $img = 'unlocked';
    }

    return '<IMG SRC=assets/' . $img . '.gif >';
}

function _makePeriodSelect($course_period_id, $column = '')
{
    global $_openSIS, $THIS_RET, $fy_id;

    $sql = 'SELECT sp.TITLE AS PERIOD,cp.COURSE_PERIOD_ID,cp.PARENT_ID,cp.TITLE,cp.MARKING_PERIOD_ID,COALESCE(cp.TOTAL_SEATS-cp.FILLED_SEATS,0) AS AVAILABLE_SEATS FROM course_periods cp,course_period_var cpv,school_periods sp WHERE sp.PERIOD_ID=cpv.PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.COURSE_ID=\'' . $THIS_RET['COURSE_ID'] . '\' ORDER BY sp.SORT_ORDER';
    $orders_RET = DBGet(DBQuery($sql));

    foreach ($orders_RET as $value) {
        if ($value['COURSE_PERIOD_ID'] != $value['PARENT_ID']) {
            $parent = DBGet(DBQuery('SELECT SHORT_NAME FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $value['PARENT_ID'] . '\''));
            $parent = $parent[1]['SHORT_NAME'];
        }

        $periods[$value['COURSE_PERIOD_ID']] = $value['PERIOD'] . ' - ' . $value['TITLE'] . (($value['MARKING_PERIOD_ID'] != $fy_id && $value['COURSE_PERIOD_ID'] != $course_period_id) ? ' (' . GetMP($value['MARKING_PERIOD_ID']) . ')' : '') . ($value['COURSE_PERIOD_ID'] != $course_period_id ? ' (' . $value['AVAILABLE_SEATS'] . ' seats)' : '') . (($value['COURSE_PERIOD_ID'] != $course_period_id && $parent) ? ' -> ' . $parent : '');
    }

    return SelectInput_Disonclick($course_period_id, "schedule[$THIS_RET[COURSE_PERIOD_ID]][$THIS_RET[START_DATE]][COURSE_PERIOD_ID]", '', $periods, false);
}

function _makeMPSelect($mp_id, $name = '')
{
    global $_openSIS, $THIS_RET, $fy_id;
    if ($mp_id != '') {
        if (!$_openSIS['_makeMPSelect']) {
            $semesters_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,NULL AS SEMESTER_ID FROM school_semesters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER'));
            $quarters_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,SEMESTER_ID FROM school_quarters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER'));

            $_openSIS['_makeMPSelect'][$fy_id][1] = array('MARKING_PERIOD_ID' => "$fy_id", 'TITLE' => 'Full Year', 'SEMESTER_ID' => '');
            foreach ($semesters_RET as $sem) {
                $_openSIS['_makeMPSelect'][$fy_id][] = $sem;
            }

            foreach ($quarters_RET as $qtr) {
                $_openSIS['_makeMPSelect'][$fy_id][] = $qtr;
            }

            $quarters_QI = DBQuery('SELECT MARKING_PERIOD_ID,TITLE,SEMESTER_ID FROM school_quarters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER');
            $quarters_indexed_RET = DBGet($quarters_QI, array(), array('SEMESTER_ID'));

            foreach ($semesters_RET as $sem) {
                $_openSIS['_makeMPSelect'][$sem['MARKING_PERIOD_ID']][1] = $sem;
                foreach ($quarters_indexed_RET[$sem['MARKING_PERIOD_ID']] as $qtr) {
                    $_openSIS['_makeMPSelect'][$sem['MARKING_PERIOD_ID']][] = $qtr;
                }

            }

            foreach ($quarters_RET as $qtr) {
                $_openSIS['_makeMPSelect'][$qtr['MARKING_PERIOD_ID']][] = $qtr;
            }

        }

        foreach ($_openSIS['_makeMPSelect'][$mp_id] as $value) {
            $mps[$value['MARKING_PERIOD_ID']] = $value['TITLE'];
        }

        if ($THIS_RET['MARKING_PERIOD_ID'] != $mp_id) {
            $mps[$THIS_RET['MARKING_PERIOD_ID']] = '* ' . $mps[$THIS_RET['MARKING_PERIOD_ID']];
        }

        return SelectInput($THIS_RET['MARKING_PERIOD_ID'], "schedule[$THIS_RET[COURSE_PERIOD_ID]][$THIS_RET[START_DATE]][MARKING_PERIOD_ID]", '', $mps, false);
    } else {
        $check_custom = DBGet(DBQuery('SELECT BEGIN_DATE,END_DATE FROM course_periods WHERE COURSE_PERIOD_ID=' . $THIS_RET['COURSE_PERIOD_ID'] . ' AND BEGIN_DATE IS NOT NULL AND END_DATE IS NOT NULL AND BEGIN_DATE!=\'0000-00-00\' AND END_DATE!=\'0000-00-00\' '));
        if (count($check_custom) > 0) {
            return '<div style="white-space: nowrap;">' . ProperDateAY($check_custom[1]['BEGIN_DATE']) . ' to ' . ProperDateAY($check_custom[1]['END_DATE']) . '</div>';
        }
    }
}

function _makeMPSelect_red($mp_id, $name = '')
{
    global $_openSIS, $THIS_RET, $fy_id;
    if ($mp_id != '') {
        if (!$_openSIS['_makeMPSelect']) {
            $semesters_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,NULL AS SEMESTER_ID FROM school_semesters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER'));
            $quarters_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,SEMESTER_ID FROM school_quarters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER'));

            $_openSIS['_makeMPSelect'][$fy_id][1] = array('MARKING_PERIOD_ID' => "$fy_id", 'TITLE' => 'Full Year', 'SEMESTER_ID' => '');
            foreach ($semesters_RET as $sem) {
                $_openSIS['_makeMPSelect'][$fy_id][] = $sem;
            }

            foreach ($quarters_RET as $qtr) {
                $_openSIS['_makeMPSelect'][$fy_id][] = $qtr;
            }

            $quarters_QI = DBQuery('SELECT MARKING_PERIOD_ID,TITLE,SEMESTER_ID FROM school_quarters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER');
            $quarters_indexed_RET = DBGet($quarters_QI, array(), array('SEMESTER_ID'));

            foreach ($semesters_RET as $sem) {
                $_openSIS['_makeMPSelect'][$sem['MARKING_PERIOD_ID']][1] = $sem;
                foreach ($quarters_indexed_RET[$sem['MARKING_PERIOD_ID']] as $qtr) {
                    $_openSIS['_makeMPSelect'][$sem['MARKING_PERIOD_ID']][] = $qtr;
                }

            }

            foreach ($quarters_RET as $qtr) {
                $_openSIS['_makeMPSelect'][$qtr['MARKING_PERIOD_ID']][] = $qtr;
            }

        }

        foreach ($_openSIS['_makeMPSelect'][$mp_id] as $value) {

            $student_id = UserStudentID();
            $qr = DBGet(DBQuery('select end_date from student_enrollment where student_id=' . $student_id . ' order by id desc limit 0,1'));

            $stu_end_date = $qr[1]['END_DATE'];
            $qr1 = DBGet(DBQuery('select end_date from course_periods where COURSE_PERIOD_ID=' . $THIS_RET['COURSE_PERIOD_ID'] . ''));

            $cr_end_date = $qr1[1]['END_DATE'];
            if (strtotime($cr_end_date) > strtotime($stu_end_date) && $stu_end_date != '') {
                $val = '<span class="text-primary text-bold">' . $value['TITLE'] . '</span>';
            } else {
                $val = $value['TITLE'];
            }
            $mps[$value['MARKING_PERIOD_ID']] = $val;
        }

        if ($THIS_RET['MARKING_PERIOD_ID'] != $mp_id) {

            $mps[$THIS_RET['MARKING_PERIOD_ID']] = '* ' . $mps[$THIS_RET['MARKING_PERIOD_ID']];
        }

        return SelectInput($THIS_RET['MARKING_PERIOD_ID'], "schedule[$THIS_RET[COURSE_PERIOD_ID]][$THIS_RET[START_DATE]][MARKING_PERIOD_ID]", '', $mps, false);
    } else {
        $student_id = UserStudentID();
        $qr = DBGet(DBQuery('select end_date from student_enrollment where student_id=' . $student_id . ' order by id desc limit 0,1'));

        $stu_end_date = $qr[1]['END_DATE'];
        $qr1 = DBGet(DBQuery('select end_date from course_periods where COURSE_PERIOD_ID=' . $THIS_RET['COURSE_PERIOD_ID'] . ''));

        $cr_end_date = $qr1[1]['END_DATE'];

        $check_custom = DBGet(DBQuery('SELECT BEGIN_DATE,END_DATE FROM course_periods WHERE COURSE_PERIOD_ID=' . $THIS_RET['COURSE_PERIOD_ID'] . ' AND BEGIN_DATE IS NOT NULL AND END_DATE IS NOT NULL AND BEGIN_DATE!=\'0000-00-00\' AND END_DATE!=\'0000-00-00\' '));
        if (count($check_custom) > 0) {
            if (strtotime($cr_end_date) > strtotime($stu_end_date) && $stu_end_date != '') {
                return '<div style="white-space: nowrap;"><FONT color=red>' . ProperDateAY($check_custom[1]['BEGIN_DATE']) . ' to ' . ProperDateAY($check_custom[1]['END_DATE']) . '</FONT></div>';
            } else {
                return '<div style="white-space: nowrap;">' . ProperDateAY($check_custom[1]['BEGIN_DATE']) . ' to ' . ProperDateAY($check_custom[1]['END_DATE']) . '</div>';
            }
        }
    }
}

function _makeDate($value, $column)
{
    global $THIS_RET;
    static $counter = 1;
    if ($column == 'START_DATE') {
        $allow_na = false;
    } else {
        $allow_na = true;
    }

    if ($column == 'END_DATE' && $THIS_RET['END_DATE'] != '') {
        $counter++;
        return '<div style="white-space: nowrap;">' . DateInputAY($value != "" ? $value : "", "schedule[$THIS_RET[COURSE_PERIOD_ID]][$THIS_RET[START_DATE]][$column]", $counter . $THIS_RET['COURSE_PERIOD_ID'], '', true, $allow_na) . '</div>';
    } else {

        $counter++;
        return '<div style="white-space: nowrap;">' . DateInputAY($value != "" ? $value : "", "schedule[$THIS_RET[COURSE_PERIOD_ID]][$THIS_RET[START_DATE]][$column]", $counter . $THIS_RET['COURSE_PERIOD_ID'], '', true, $allow_na) . '</div>';
    }
}

function _makeDate_red($value, $column)
{
    global $THIS_RET;
    static $counter = 0;
    if ($column == 'START_DATE') {
        $allow_na = false;
    } else {
        $allow_na = true;
    }

    if ($column == 'END_DATE' && $THIS_RET[END_DATE] != '') {
        return date('M/d/Y', strtotime($value));
    } else {

        $counter++;
        return '<div style="white-space: nowrap;">' . DateInputAY_red($value, "schedule[$THIS_RET[COURSE_PERIOD_ID]][$THIS_RET[START_DATE]][$column]", $counter . $THIS_RET[COURSE_PERIOD_ID], $THIS_RET[COURSE_PERIOD_ID]) . '</div>';
    }
}

function _makeInfo($value, $column)
{
    global $THIS_RET;

    return "<center><a href=javascript:void(0) data-toggle='modal' data-target='#modal_moreinfo' onclick=Course_Mrinfo('" . $value . "');><i class=\"icon-info22\"></i></a></center>";
}

function makeModal($value)
{
    echo _hello;
}

function _makeMP($value, $column)
{
    global $THIS_RET;

    if ($value != '') {
        return GetMP($value);
    } else {
        $check_custom = DBGet(DBQuery('SELECT BEGIN_DATE,END_DATE FROM course_periods WHERE COURSE_PERIOD_ID=' . $THIS_RET['COURSE_PERIOD_ID'] . ' AND BEGIN_DATE IS NOT NULL AND END_DATE IS NOT NULL AND BEGIN_DATE!=\'0000-00-00\' AND END_DATE!=\'0000-00-00\' '));
        if (count($check_custom) > 0) {
            return '<div style="white-space: nowrap;">' . ProperDateAY($check_custom[1]['BEGIN_DATE']) . ' to ' . ProperDateAY($check_custom[1]['END_DATE']) . '</div>';
        }
    }
}

function _makeMPA($value, $column)
{
    global $THIS_RET;

    if ($value != '' && $THIS_RET['MARKING_PERIOD_ID'] == $THIS_RET['MPA_ID']) {
        return GetMP($value);
    } elseif ($value != '' && $THIS_RET['MARKING_PERIOD_ID'] != $THIS_RET['MPA_ID']) {
        return '*' . GetMP($value);
    } else {
        $check_custom = DBGet(DBQuery('SELECT BEGIN_DATE,END_DATE FROM course_periods WHERE COURSE_PERIOD_ID=' . $THIS_RET['COURSE_PERIOD_ID'] . ' AND BEGIN_DATE IS NOT NULL AND END_DATE IS NOT NULL AND BEGIN_DATE!=\'0000-00-00\' AND END_DATE!=\'0000-00-00\' '));
        if (count($check_custom) > 0) {
            return '<div style="white-space: nowrap;">' . ProperDateAY($check_custom[1]['BEGIN_DATE']) . ' to ' . ProperDateAY($check_custom[1]['END_DATE']) . '</div>';
        }
    }
}

function _makeViewDate($value, $column)
{
    if ($value) {
        return ProperDate($value);
    } else {
        return '<center>' . _nA . '</center>';
    }

}

function _makeLock($value, $column)
{
    global $THIS_RET;
    $hidd = "<input type='hidden' name='schedule[$THIS_RET[COURSE_PERIOD_ID]][$THIS_RET[START_DATE]][SCHEDULE_ID]' value='" . $THIS_RET['SCHEDULE_ID'] . "'>";

    if ($value == 'Y') {
        $img = 'locked';
    } else {
        $img = 'unlocked';
    }

    return '<IMG SRC=assets/' . $img . '.gif ' . (AllowEdit() ? 'onclick="if(this.src.indexOf(\'assets/locked.gif\')!=-1) {this.src=\'assets/unlocked.gif\'; document.getElementById(\'lock' . $THIS_RET['COURSE_PERIOD_ID'] . '-' . $THIS_RET['START_DATE'] . '\').value=\'\';} else {this.src=\'assets/locked.gif\'; document.getElementById(\'lock' . $THIS_RET['COURSE_PERIOD_ID'] . '-' . $THIS_RET['START_DATE'] . '\').value=\'Y\';}"' : '') . '><INPUT type=hidden name=schedule[' . $THIS_RET['COURSE_PERIOD_ID'] . '][' . $THIS_RET['START_DATE'] . '][SCHEDULER_LOCK] id=lock' . $THIS_RET['COURSE_PERIOD_ID'] . '-' . $THIS_RET['START_DATE'] . ' value=' . $value . '>' . $hidd;
}

function _makeDays($value)
{
    $value = str_replace(',', '', $value);
    for ($i = 0; $i < strlen($value); $i++) {
        $arr[] = substr($value, $i, 1);
    }
    $arr = array_unique($arr);
    $arr = implode('', $arr);
    return $arr;
}

function VerifySchedule(&$schedule)
{
    $conflicts = array();

    $ij = count($schedule);
    for ($i = 1; $i < $ij; $i++) {
        for ($j = $i + 1; $j <= $ij; $j++) {
            if (!$conflicts[$i] || !$conflicts[$j]) {
                if (strpos(GetAllMP(GetMPTable(GetMP($schedule[$i]['MARKING_PERIOD_ID'], 'TABLE')), $schedule[$i]['MARKING_PERIOD_ID']), "'" . $schedule[$j]['MARKING_PERIOD_ID'] . "'") !== false && (!$schedule[$i]['END_EPOCH'] || $schedule[$j]['START_EPOCH'] <= $schedule[$i]['END_EPOCH']) && (!$schedule[$j]['END_EPOCH'] || $schedule[$i]['START_EPOCH'] <= $schedule[$j]['END_EPOCH'])) {
                    if ($schedule[$i]['COURSE_ID'] == $schedule[$j]['COURSE_ID']) //&& $schedule[$i]['COURSE_WEIGHT']==$schedule[$j]['COURSE_WEIGHT'])
                    {
                        $conflicts[$i] = $conflicts[$j] = true;
                    } elseif ($schedule[$i]['PERIOD_ID'] == $schedule[$j]['PERIOD_ID']) {
                        if (strlen($schedule[$i]['DAYS']) + strlen($schedule[$j]['DAYS']) > 7) {
                            $conflicts[$i] = $conflicts[$j] = true;
                        } else {
                            foreach (veriry_str_split($schedule[$i]['DAYS']) as $k) {
                                if (strpos($schedule[$j]['DAYS'], $k) !== false) {

                                    if ($schedule[$i]['COURSE_PERIOD_ID'] != '' && $schedule[$i]['PERIOD_ID'] != '' && $k != '') {
                                        $check_conf = DBGet(DBQuery('SELECT COUNT(*) as REC_RET FROM course_period_var WHERE COURSE_PERIOD_ID=' . $schedule[$i]['COURSE_PERIOD_ID'] . ' AND PERIOD_ID=' . $schedule[$i]['PERIOD_ID'] . ' AND DAYS LIKE \'%' . $k . '%\' '));
                                        if ($check_conf[1]['REC_RET'] > 0) {
                                            $conflicts[$i] = $conflicts[$j] = true;
                                        }

                                    } else {
                                        $conflicts[$i] = $conflicts[$j] = true;
                                    }

                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    $student_id = UserStudentID();

    foreach ($conflicts as $i => $true) {
        $schedule[$i]['TITLE'] = '<span class="text-bold">' . $schedule[$i]['TITLE'] . '</span>';
    }

}

function veriry_str_split($str)
{
    $ret = array();
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++) {
        $ret[] = substr($str, $i, 1);
    }

    return $ret;
}

function CreateSelect($val, $name, $link = '', $mpid)
{

    if ($link != '') {
        $html .= "<select class=\"form-control\" name=" . $name . " id=" . $name . " onChange=\"window.location='" . $link . "' + this.options[this.selectedIndex].value;\">";
    } else {
        $html .= "<select class=\"form-control\" name=" . $name . " id=" . $name . " >";
    }

    foreach ($val as $key => $value) {

        if (!isset($mpid) && (UserMP() == $value[strtoupper($name)])) {
            $html .= "<option selected value=" . UserMP() . ">" . $value['TITLE'] . "</option>";
        } else {
            if ($value[strtoupper($name)] == $_REQUEST[$name]) {
                $html .= "<option selected value=" . $value[strtoupper($name)] . ">" . $value['TITLE'] . "</option>";
            } else {
                $html .= "<option value=" . $value[strtoupper($name)] . ">" . $value['TITLE'] . "</option>";
            }

        }
    }

    $html .= "</select>";
    return $html;
}
function _makeLower($word)
{
    return ucwords(strtolower($word));
}
?>
