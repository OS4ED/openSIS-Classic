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
include('../../../RedirectIncludes.php');

include_once('modules/students/includes/FunctionsInc.php');
#########################################################ENROLLMENT##############################################

if ($_REQUEST['action'] == 'forceDrop') {
    $dropDate = sqlSecurityFilter($_REQUEST['dropDate']);

    if ($dropDate != '') {
        DBQuery('DELETE FROM attendance_period WHERE STUDENT_ID = ' . UserStudentID() . ' AND COURSE_PERIOD_ID IN (SELECT COURSE_PERIOD_ID FROM schedule WHERE STUDENT_ID = ' . UserStudentID() . ' AND SYEAR = ' . UserSyear() . ' AND SCHOOL_ID = ' . UserSchool() . ') AND SCHOOL_DATE >= \'' . $dropDate . '\'');


        DBQuery('DELETE FROM attendance_day WHERE STUDENT_ID = ' . UserStudentID() . ' AND SCHOOL_DATE >= \'' . $dropDate . '\'');

        unset($_REQUEST['modfunc']);

        echo '<div class="alert alert-info alert-styled-left">' . str_replace('0000-00-00', '<span class="text-bold">' . ProperDate($dropDate) . '</span>', _attendanceRecordsFromDateHaveBeenDeletedYouCanNowDropTheStudent) . '</div>';
    }
}

if($_SESSION['ERR_TRANS'])
{
    echo $_SESSION['ERR_TRANS'];
}


if (($_REQUEST['month_values'] && ($_POST['month_values'] || $_REQUEST['ajax'])) || ($_REQUEST['values']['student_enrollment'] && ($_POST['values']['student_enrollment'] || $_REQUEST['ajax']))) {
    if (!$_REQUEST['values']['student_enrollment']['new']['ENROLLMENT_CODE'] && !$_REQUEST['month_values']['student_enrollment']['new']['START_DATE']) {
        unset($_REQUEST['values']['student_enrollment']['new']);
        unset($_REQUEST['day_values']['student_enrollment']['new']);
        unset($_REQUEST['month_values']['student_enrollment']['new']);
        unset($_REQUEST['year_values']['student_enrollment']['new']);
    } else {
        $date = $_REQUEST['day_values']['student_enrollment']['new']['START_DATE'] . '-' . $_REQUEST['month_values']['student_enrollment']['new']['START_DATE'] . '-' . $_REQUEST['year_values']['student_enrollment']['new']['START_DATE'];
        $found_RET = DBGet(DBQuery('SELECT ID FROM student_enrollment WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND SYEAR=\'' . UserSyear() . '\' AND \'' . date("Y-m-d", strtotime($date)) . '\' BETWEEN START_DATE AND END_DATE'));
        if (count($found_RET)) {
            unset($_REQUEST['values']['student_enrollment']['new']);
            unset($_REQUEST['day_values']['student_enrollment']['new']);
            unset($_REQUEST['month_values']['student_enrollment']['new']);
            unset($_REQUEST['year_values']['student_enrollment']['new']);
            echo ErrorMessage(array(_theStudentIsAlreadyEnrolledOnThatDateAndCouldNotBeEnrolledASecondTimeOnTheDateYouSpecifiedPleaseFixAndTryEnrollingTheStudentAgain));
        }
    }

    $iu_extra['student_enrollment'] = "STUDENT_ID='" . UserStudentID() . "' AND ID='__ID__'";
    $iu_extra['fields']['student_enrollment'] = 'SYEAR,STUDENT_ID,';
    $iu_extra['values']['student_enrollment'] = "'" . UserSyear() . "','" . UserStudentID() . "',";
    if (!$new_student) {
        if ($_REQUEST['month_values']) {
            foreach ($_REQUEST['month_values'] as $table => $values) {
                foreach ($values as $id => $columns) {
                    foreach ($columns as $column => $value) {


                        if ($value == 'JAN')
                            $value = '01';
                        if ($value == 'FEB')
                            $value = '02';
                        if ($value == 'MAR')
                            $value = '03';
                        if ($value == 'APR')
                            $value = '04';
                        if ($value == 'MAY')
                            $value = '05';
                        if ($value == 'JUN')
                            $value = '06';
                        if ($value == 'JUL')
                            $value = '07';
                        if ($value == 'AUG')
                            $value = '08';
                        if ($value == 'SEP')
                            $value = '09';
                        if ($value == 'OCT')
                            $value = '10';
                        if ($value == 'NOV')
                            $value = '11';
                        if ($value == 'DEC')
                            $value = '12';



                        $_REQUEST['values'][$table][$id][$column] = $_REQUEST['year_values'][$table][$id][$column] . '-' . $value . '-' . $_REQUEST['day_values'][$table][$id][$column];

                        if ($_REQUEST['values'][$table][$id][$column] == '--')
                            $_REQUEST['values'][$table][$id][$column] = '';
                    }
                }
            }
        }


        if ($_REQUEST['values']['student_enrollment']) {
            $sql = 'SELECT START_DATE FROM student_enrollment WHERE STUDENT_ID=\'' . UserStudentID() . '\'';
            $start_date = DBGet(DBQuery($sql));
            $start_date = $start_date[1]['START_DATE'];


            if ($_REQUEST['values'][$table][$id][$column] != '') {
                if ($_REQUEST['values'][$table][$id][$column] != '' && strtotime($_REQUEST['values'][$table][$id][$column]) >= strtotime($start_date)) {
                    if ($column == 'END_DATE') {
                        $e_date = '1-' . $_REQUEST['month_values'][$table][$id][$column] . '-' . $_REQUEST['year_values'][$table][$id][$column];
                        $num_days = date('t', strtotime($e_date));

                        if ($num_days < $_REQUEST['day_values'][$table][$id][$column]) {
                            $error = date('F', strtotime($e_date)) . ' has ' . $num_days . ' days';
                        } else {
                            unset($error);
                        }
                    }
                    if (isset($error) && $error != '') {
                        echo '<div class="alert bg-danger alert-styled-left">' . $error . '</div>';
                    } else {
                        $sql = 'SELECT ID,COURSE_ID,COURSE_PERIOD_ID,MARKING_PERIOD_ID FROM schedule WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\'';
                        $schedules = DBGet(DBQuery($sql));
                        $c = count($schedules);
                        if ($c > 0) {
                            for ($i = 1; $i <= count($schedules); $i++) {
                                $cp_id[$i] = $schedules[$i]['COURSE_PERIOD_ID'];
                            }
                            $cp_id = implode(',', $cp_id);
                            $sql = 'SELECT MAX(SCHOOL_DATE) AS SCHOOL_DATE FROM attendance_period WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND COURSE_PERIOD_ID IN (' . $cp_id . ')';
                            $attendence = DBGet(DBQuery($sql));
                            $max_at_dt = $attendence[1]['SCHOOL_DATE'];
                            if (strtotime($_REQUEST['values'][$table][$id][$column]) >= strtotime($max_at_dt)) {

                                //SaveData($iu_extra, '', $field_names);
                            } else {
                                echo '<div class="alert alert-danger alert-styled-left">'._studentCannotBeDroppedBecauseStudentHasGotAttendanceTill.'' . ProperDate(date('Y-m-d', strtotime($max_at_dt))) . '</div>';
                                echo '<div class="alert alert-warning alert-styled-left">' . str_replace('0000-00-00', '<span class="text-bold">' . ProperDate($_REQUEST['values'][$table][$id]['END_DATE']) . '</span>', _deleteTheAttendanceRecordsAfterDateAndProceedWithDroppingTheStudent) . '<a href="javascript:void(0)" onclick="load_link(\'' . PreparePHP_SELF($_REQUEST) . '&action=forceDrop&ajax=true&dropDate=' . $_REQUEST['values'][$table][$id]['END_DATE'] . '\')" class="btn-undo alert-link m-l-20" title="Proceed with caution. This action cannot be undone.">' . _yes . '</a></div>';
                            }
                        } else {

                            $get_details = DBGet(DBQuery('SELECT max(START_DATE) AS START_DATE FROM student_enrollment WHERE STUDENT_ID=' . UserStudentID()));

                            if (strtotime($get_details[1]['START_DATE']) > strtotime($_REQUEST['values'][$table][$id][$column])) {
                                echo '<div class="alert bg-danger alert-styled-left">'._studentDropDateCannotBeBeforeStudentEnrollmentDate.' </div>';
                            } else {
                               // SaveData($iu_extra, '', $field_names);
                            }
                        }
                        $enroll_count = DBGet(DBQuery('SELECT * FROM student_enrollment WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND SYEAR=' . UserSyear() . '  AND SCHOOL_ID=' . UserSchool() . ' ORDER BY START_DATE DESC LIMIT 1'));
                        if ($enroll_count[1]['CALENDAR_ID'] == '' && $enroll_count[1]['GRADE_ID'] == '' && $enroll_count[1]['NEXT_SCHOOL'] == '') {
                            $stu_grd_cal = DBGet(DBQuery('SELECT CALENDAR_ID,GRADE_ID,NEXT_SCHOOL FROM student_enrollment WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool() . ' ORDER BY START_DATE DESC LIMIT 1,1'));
                            $stu_grd_cal_max = DBGet(DBQuery('SELECT ID FROM student_enrollment WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool() . ' ORDER BY START_DATE DESC LIMIT 1'));

                            DBQuery('UPDATE student_enrollment SET CALENDAR_ID=' . $stu_grd_cal[1]['CALENDAR_ID'] . ',GRADE_ID=' . $stu_grd_cal[1]['GRADE_ID'] . ', NEXT_SCHOOL=\'' . $stu_grd_cal[1]['NEXT_SCHOOL'] . '\' WHERE ID=' . $stu_grd_cal_max[1]['ID']);
                        }
                    }
                } else {
                    echo '<div class="alert bg-danger alert-styled-left">'._pleaseEnterProperDropDateDropDateMustBeGreaterThanStudentEnrollmentDate.'</div>';
                }
            }
        }
    }
}


$functions = array('ENROLLMENT_CODE' => '_makeStartInputCodeenrl', 'DROP_CODE' => '_makeEndInputCodeenrl', 'SCHOOL_ID' => '_makeSchoolInput');
unset($THIS_RET);
$student_RET_qry = 'SELECT e.SYEAR, s.FIRST_NAME,s.LAST_NAME,s.GENDER, e.ID,e.GRADE_ID,e.ENROLLMENT_CODE,e.START_DATE,e.DROP_CODE,e.END_DATE,e.END_DATE AS END,e.SCHOOL_ID,e.NEXT_SCHOOL,e.CALENDAR_ID FROM student_enrollment e,students s WHERE e.STUDENT_ID=\'' . UserStudentID() . '\' AND e.SYEAR=\'' . UserSyear() . '\' AND e.STUDENT_ID=s.STUDENT_ID ORDER BY e.START_DATE';
$RET = DBGet(DBQuery($student_RET_qry));
$not_add = false;
if (count($RET)) {
    foreach ($RET as $in => $value) {
        if ($value['DROP_CODE'] == '' || !$value['DROP_CODE'])
            $not_add = true;
    }
}
$date_counter = 1;

//if($not_add==false)
//	$link['add']['html'] = array('START_DATE'=>_makeEnrollmentDates('START_DATE',$date_counter,''),'ENROLLMENT_CODE'=>_makeStartInputCode('','ENROLLMENT_CODE'),'SCHOOL_ID'=>_makeSchoolInput('','SCHOOL_ID'));


unset($THIS_RET);
$RET = DBGet(DBQuery('SELECT e.DROP_CODE as DC,e.SYEAR, s.FIRST_NAME,s.LAST_NAME,s.GENDER, e.ID,e.GRADE_ID,e.ENROLLMENT_CODE,e.START_DATE,e.DROP_CODE,e.END_DATE,e.END_DATE AS END,e.SCHOOL_ID,e.NEXT_SCHOOL,e.CALENDAR_ID FROM student_enrollment e,students s WHERE e.STUDENT_ID=\'' . UserStudentID() . '\'  AND e.STUDENT_ID=s.STUDENT_ID ORDER BY e.START_DATE'), $functions);

if (count($RET)) {
    $date_counter = $date_counter + 1;
    foreach ($RET as $in => $value) {
        if ($value['DROP_CODE'] == '' || !$value['DROP_CODE'])
            $not_add = true;
        if ($RET[$in]['DC'] != '') {
            $get_SEC = DBGet(DBQuery('SELECT TYPE FROM student_enrollment_codes WHERE ID=' . $RET[$in]['DC']));
            $get_SEC = $get_SEC[1]['TYPE'];
        } else
            $get_SEC = '';
        $RET[$in]['START_DATE'] = ($get_SEC == 'TrnD' ? date('M/d/Y', strtotime($RET[$in]['START_DATE'])) : _makeEnrollmentDates('START_DATE', $date_counter, $value));
        $date_counter = $date_counter + 1;
//                        if($RET[$in]['END_DATE']!='')

        $RET[$in]['END_DATE'] = ($get_SEC == 'TrnD' ? date('M/d/Y', strtotime($RET[$in]['END_DATE'])) : _makeEnrollmentDates('END_DATE', $date_counter, $value));
//                  else {
//                  $RET[$in]['END_DATE']=='0000-00-00';    
//                  }
//                      $date_counter=$date_counter+1;
    }
}


$columns = array('START_DATE' =>_startDate,
 'ENROLLMENT_CODE' =>_enrollmentCode,
 'END_DATE' =>_dropDate,
 'DROP_CODE' =>_dropCode,
 'SCHOOL_ID' =>_school,
);

$schools_RET = DBGet(DBQuery('SELECT ID,TITLE FROM schools WHERE ID!=\'' . UserSchool() . '\''));
$next_school_options = array(UserSchool() =>_nextGradeAtCurrentSchool,
 '0' =>_retain,
 '-1' =>_doNotEnrollAfterThisSchoolYear,
);
if (count($schools_RET)) {
    foreach ($schools_RET as $school)
        $next_school_options[$school['ID']] = $school['TITLE'];
}

if (!UserSchool()) {
    $user_school_RET = DBGet(DBQuery('SELECT SCHOOL_ID FROM student_enrollment WHERE STUDENT_ID=\'' . UserStudentID() . '\' LIMIT 1'));
    $_SESSION['UserSchool'] = $user_school_RET[1]['SCHOOL_ID'];
}
$calendars_RET = DBGet(DBQuery('SELECT CALENDAR_ID,DEFAULT_CALENDAR,TITLE FROM school_calendars WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY DEFAULT_CALENDAR DESC'));

if (count($calendars_RET)) {
    foreach ($calendars_RET as $calendar)
        $calendar_options[$calendar['CALENDAR_ID']] = $calendar['TITLE'];
}

$get_latest_enrollment = DBGet(DBQuery('SELECT * FROM `student_enrollment` WHERE `id` = (SELECT MAX(`id`) FROM `student_enrollment` WHERE `student_id` = \''.UserStudentID().'\')'));

if($get_latest_enrollment[1]['SCHOOL_ID'] != UserSchool() && $get_latest_enrollment[1]['CALENDAR_ID'] != '')
{
    $get_calendar = DBGet(DBQuery('SELECT * FROM `school_calendars` WHERE `calendar_id` = \''.$get_latest_enrollment[1]['CALENDAR_ID'].'\''));

    $calendar_options[$get_calendar[1]['CALENDAR_ID']] = $get_calendar[1]['TITLE'];
}

if ($_REQUEST['student_id'] != 'new') {
    if (count($RET))
        $id = $RET[count($RET)]['ID'];
    else
        $id = 'new';

    if ($id != 'new')
        $next_school = $RET[count($RET)]['NEXT_SCHOOL'];
    if ($id != 'new')
    {
        // $calendar = $RET[count($RET)]['CALENDAR_ID'];
        foreach($RET as $one_set)
        {
            if($one_set['SYEAR'] == UserSyear())
            {
                $calendar = $one_set['CALENDAR_ID'];
            }
        }
    }
    $div = true;
}
else {
    $id = 'new';
    $next_school = UserSchool();
    $calendar = $calendars_RET[1]['CALENDAR_ID'];
    $div = false;
}

################################################################################

echo '</div>';

echo '<h5 class="text-primary">'._enrollmentInformation.'</h5>';

echo '<input type=hidden id=cal_stu_id value=' . $id . ' />';

echo '<div class="row">';
echo '<div class="col-md-6"><div class="form-group"><label class="control-label col-lg-4 text-right" for="values[student_enrollment][' . $id . '][CALENDAR_ID]">'._calendar.' <span class="text-danger">*</span></label><div class="col-lg-8">' . SelectInput($calendar, "values[student_enrollment][$id][CALENDAR_ID]", (!$calendar || !$div ? '' : '') . '' . (!$calendar || !$div ? '' : ''), $calendar_options, false, '', $div) . '</div></div></div>';
echo '<div class="col-md-6"><div class="form-group"><label class="control-label col-lg-4 text-right" for="values[student_enrollment][' . $id . '][NEXT_SCHOOL]">'._rollingRetentionOptions.'</label><div class="col-lg-8">' . SelectInput($next_school, "values[student_enrollment][$id][NEXT_SCHOOL]", (!$next_school || !$div ? '' : '') . '' . (!$next_school || !$div ? '' : ''), $next_school_options, false, '', $div) . '</div></div></div>';
echo '</div>'; //.row

echo '<hr class="no-margin-bottom"/>';

$enrol_id=$_REQUEST['enrollment_id'];
if ($_REQUEST['student_id'] && $_REQUEST['student_id'] != 'new' && $_REQUEST['values']['student_enrollment'][$enrol_id]['END_DATE']!='') {

    
    $sql_enroll_id = DBGet(DBQuery('SELECT MAX(ID) AS M_ID FROM student_enrollment WHERE STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));

    $enroll_id = $sql_enroll_id[1]['M_ID'];

    $end_date = DBGet(DBQuery('SELECT END_DATE FROM student_enrollment WHERE STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND ID=\'' . $enroll_id . '\''));

    if ($end_date[1]['END_DATE']) {
        $end_date = $end_date[1]['END_DATE'];
        DBQuery('UPDATE schedule SET END_DATE=\'' . $end_date . '\' WHERE STUDENT_ID=\'' . $_REQUEST['student_id'] . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND (END_DATE IS NULL OR \'' . $end_date . '\' < END_DATE )');
        DBQuery('CALL SEAT_COUNT()');
    }
}

if ($_REQUEST['student_id'] != 'new') {
    if (count($RET))
        $id = $RET[count($RET)]['ID'];
    else
        $id = 'new';
    echo '<div id="students" class="table-responsive">';
    ListOutput($RET, $columns,  _enrollmentRecord,_enrollmentRecords, $link);
    //echo "</div>";
    if ($id != 'new')
        $next_school = $RET[count($RET)]['NEXT_SCHOOL'];
    if ($id != 'new')
        $calendar = $RET[count($RET)]['CALENDAR_ID'];
    $div = true;
}
else {
    $id = 'new';
    echo '<div id="students">';
    ListOutputMod($RET, $columns, _enrollmentRecord,_enrollmentRecords, $link, array(), array('count' =>false));
    echo "</div>";
    $next_school = UserSchool();
    $calendar = $calendars_RET[1]['CALENDAR_ID'];
    $div = false;
}
//echo '<div class="panel-body">'; // .panel-body start to end in footer
//echo '<div class="tab-content">'; // .panel-content start to end in footer
