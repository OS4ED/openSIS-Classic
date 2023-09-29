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

include "lang/language.php";

while (!UserSyear()) {
    session_write_close();
    session_start();
}

if (isset($_REQUEST['modname']))
    $_REQUEST['modname'] = sqlSecurityFilter($_REQUEST['modname']);

$this_portal_toggle = "";

$current_hour = date('H');
$welcome .= ''._user.' : ' . User('NAME');

// if ($_SESSION['LAST_LOGIN'])
//    $welcome .= ' | Last login : ' . ProperDate(substr($_SESSION['LAST_LOGIN'], 0, 10)) . ' at ' . substr($_SESSION['LAST_LOGIN'], 10);
// if ($_SESSION['FAILED_LOGIN'])
//    $welcome .= ' | <span class=red >' . $_SESSION['FAILED_LOGIN'] . '</b> failed login attempts</span>';

// $det=DBGet(DBQuery('SELECT count(1) as REC_EX,STUDENT_ID,course_period_id FROM `schedule` GROUP By STUDENT_ID,course_period_id having count(1)>1'));
// foreach($det as $dt){
//    $limit=$dt['REC_EX']-1;
//    $ids=DBGet(DBQuery('SELECT * FROM schedule WHERE COURSE_PERIOD_ID='.$dt['COURSE_PERIOD_ID'].' AND STUDENT_ID='.$dt['STUDENT_ID'].' LIMIT 0,'.$limit));
//    foreach($ids as $id_d){
//        echo 'DELETE FROM schedule WHERE ID='.$id_d['ID'].';<br>';
//    }
// }

# --------------------------- #
#  Update Missing Attendance  #
# --------------------------- #

echo '<div id="calculating" style="display: none;" class="alert alert-info alert-bordered"><i class="fa fa-cog fa-spin fa-lg fa-fw"></i><span class="text-semibold">'._pleaseWait.'.</span> '._compilingMissingAttendanceData.'. '._doNotClickAnywhere.'.</div>';
echo '<div id="resp"></div>';

// $stu_missing_atten = DBGet(DBQuery('SELECT * FROM missing_attendance WHERE syear=\'' . UserSyear() . '\''));

// foreach ($stu_missing_atten as $k => $f) {

//     $pr_id = $f['PERIOD_ID'];
//     $sch_date = $f['SCHOOL_DATE'];
//     $staff_id = $f['TEACHER_ID'];
//     $c_id = $f['COURSE_PERIOD_ID'];
//     $sch_qr = DBGet(DBQuery('SELECT distinct(student_id) FROM schedule  WHERE  (END_DATE IS NULL OR END_DATE>=\'' . $sch_date . '\') AND START_DATE<=\'' . $sch_date . '\' AND course_period_id=' . $c_id));
//     $att_qr = DBGet(DBQuery('SELECT distinct(student_id) FROM attendance_period  where SCHOOL_DATE=\'' . $sch_date . '\' AND PERIOD_ID=' . $pr_id . ' AND course_period_id=' . $c_id));

//     if (count($sch_qr) == count($att_qr)) {
//         DBQuery('DELETE FROM missing_attendance WHERE  TEACHER_ID=' . $staff_id . ' AND SCHOOL_DATE=\'' . $sch_date . '\' AND PERIOD_ID=' . $pr_id);
//     }
// }

# -------------------------------- #
#  Update missing attendance ends  #
# -------------------------------- #

$userName = User('USERNAME');
$link = array();
$id = array();
$arr = array();
$qr = "select to_user,mail_id,to_cc,to_bcc from msg_inbox where isdraft is NULL";
$fetch = DBGet(DBQuery($qr));
$id_arr = array();
foreach ($fetch as $key => $value) {
    $to = $value['TO_USER'];
    "<br>";
    $cc = $value['TO_CC'];
    $bcc = $value['TO_BCC'];
    $mul = $value['TO_MULTIPLE_USERS'];
    $mul_cc = $value['TO_CC_MULTIPLE'];
    $mul_bcc = $value['TO_BCC_MULTIPLE'];

    $to_arr = explode(',', $to);
    $arr_cc = explode(',', $cc);
    $arr_bcc = explode(',', $bcc);
    $arr_mul = explode(',', $mul);

    if (in_array($userName, $to_arr) || in_array($userName, $arr_mul) || in_array($userName, $arr_bcc) || in_array($userName, $arr_cc) || in_array($userName, $arr_cc) || in_array($userName, $arr_bcc)) {
        array_push($id_arr, $value['MAIL_ID']);
    }
}


$total_count = count($id_arr);
if ($total_count > 0)
    $to_user_id = implode(',', $id_arr);
else
    $to_user_id = 'null';
$inbox = "select count(*) as total from msg_inbox where mail_id in($to_user_id) and FIND_IN_SET('$userName', mail_read_unread )";

$in = DBGet(DBQuery($inbox));
$in = $in[1]['TOTAL'];

$inbox_info = $total_count - $in;
if ($inbox_info > 1) {
    echo '<div class="alert alert-danger alert-bordered">';
    echo '<i class="fa fa-info-circle"></i> '._youHave.' ' . $inbox_info . ' '._unreadMessages.'';
    echo '</div>';
} else {
    if ($inbox_info == 1) {
        echo '<div class="alert alert-danger alert-bordered">';
        echo '<i class="fa fa-info-circle"></i> '._youHaveOneUnreadMessage.'';
        echo '</div>';
    }
}

if ($_SESSION['PROFILE_ID'] == 0)
    $title1 = _superAdministrator;
if ($_SESSION['PROFILE_ID'] == 1)
    $title1 = _administrator;

switch (User('PROFILE')) {

    case 'admin':
        DrawBC($welcome . ' | '._role.' : ' . $title1);



//        $user_agent = explode('/', $_SERVER['HTTP_USER_AGENT']);
//        if ($user_agent[0] == 'Mozilla') {
//            $update_notify = DBGet(DBQuery('SELECT VALUE FROM program_config WHERE school_id=\'' . UserSchool() . '\' AND program=\'UPDATENOTIFY\' AND title=\'display\' LIMIT 0, 1'));
//            if ($update_notify[1]['VALUE'] == 'Y') {
//                
//                if (function_exists('curl_init')) {
//                   
//                    $ch = curl_init();
//                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
//                    curl_setopt($ch, CURLOPT_URL, 'https://opensis.com/CheckVersion');
//                    curl_setopt($ch, CURLOPT_HEADER, 0);
//
//


        $update_notify_s = DBGet(DBQuery('SELECT VALUE FROM program_config WHERE school_id=\'' . UserSchool() . '\'  AND program=\'UPDATENOTIFY\' AND title=\'display_school\' LIMIT 0, 1'));
        if ($update_notify_s[1]['VALUE'] == 'Y') {
            $cal_setup = DBGet(DBQuery('SELECT COUNT(*) as REC FROM school_calendars WHERE SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear()));
            $mp_setup = DBGet(DBQuery('SELECT COUNT(*) as REC FROM marking_periods WHERE SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear()));
            $att_code_setup = DBGet(DBQuery('SELECT COUNT(*) as REC FROM attendance_codes WHERE SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear()));
            $grade_scale_setup = DBGet(DBQuery('SELECT COUNT(*) as REC FROM report_card_grade_scales WHERE SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear()));
            $enroll_code_setup = DBGet(DBQuery('SELECT COUNT(*) as REC FROM student_enrollment_codes WHERE SYEAR=' . UserSyear()));
            $grade_level_setup = DBGet(DBQuery('SELECT COUNT(*) as REC FROM school_gradelevels WHERE SCHOOL_ID=' . UserSchool()));
            $periods_setup = DBGet(DBQuery('SELECT COUNT(*) as REC FROM school_periods WHERE SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear()));
            $rooms_setup = DBGet(DBQuery('SELECT COUNT(*) as REC FROM rooms WHERE SCHOOL_ID=' . UserSchool()));


//                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//
//
//                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
//
//
//                    $response = curl_exec($ch);
//                   
//                    
//                    curl_close($ch);
//                    $response = json_decode($response);
//                     
//                    
//                    $qr_lcl_qr = DBGet(DBQuery('select value from app where name=\'build\''));
//                    if ($qr_lcl_qr[1]['VALUE'] != $response[0]->build_id && $response[0]->build_id != '') {
//                        echo "<div class=\"alert bg-info alert-styled-left\">Latest version " . $response[0]->build_name . ' ' . $response[0]->version . " is available <a href='http://www.opensis.com/download_package/opensis.zip' target='_blank' class=\"text-underlined\"><b>click here</b></a> to download.</div>";
//                    }
//                }
//            }
//        }

        

            if ($cal_setup[1]['REC'] == 0 || $mp_setup[1]['REC'] < 1 || $att_code_setup[1]['REC'] == 0 || $grade_scale_setup[1]['REC'] == 0 || $enroll_code_setup[1]['REC'] == 0 || $grade_level_setup[1]['REC'] == 0 || $periods_setup[1]['REC'] == 0 || $rooms_setup[1]['REC'] == 0) {
                $width = 0;
                $percent = 0;

                if ($cal_setup[1]['REC'] > 0)
                    $width = $width + 52.5;
                if ($mp_setup[1]['REC'] > 1)
                    $width = $width + 52.5;
                if ($att_code_setup[1]['REC'] > 0)
                    $width = $width + 52.5;
                if ($grade_scale_setup[1]['REC'] > 0)
                    $width = $width + 52.5;
                if ($enroll_code_setup[1]['REC'] > 0)
                    $width = $width + 52.5;
                if ($grade_level_setup[1]['REC'] > 0)
                    $width = $width + 52.5;
                if ($periods_setup[1]['REC'] > 0)
                    $width = $width + 52.5;
                if ($rooms_setup[1]['REC'] > 0)
                    $width = $width + 52.5;

                $percent = ($width / 420) * 100;

                echo '<div class="panel panel-flat">
                        <div class="panel-heading">
                            <h6 class="panel-title">'._pleaseCompleteTheSetupBeforeUsingTheSystemTheFollowingComponentsNeedToBeSet.'</h6>
                            <div class="heading-elements">
                                <div class="progress">
                                    <div class="progress-bar progress-bar-success" style="width: ' . $percent . '%;">
                                        <span>' . $percent . '% Complete</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 mb-15">
                                    <div class="well">
                                        <div class="media-left media-middle">' . (AllowUse('schoolsetup/Calendar.php') == true ? '<a href="javascript:void(0);" class="btn border-indigo-400 text-indigo-400 btn-flat btn-rounded btn-xs btn-icon" onClick="check_content(\'Ajax.php?modname=schoolsetup/Calendar.php\');">' : '') . '<i class="icon-calendar3"></i>' . (AllowUse('schoolsetup/Calendar.php') == true ? '</a>' : '') . '</div>

                                        <div class="media-left">
                                            <h6 class="text-semibold no-margin">' . (AllowUse('schoolsetup/Calendar.php') == true ? '<a href="javascript:void(0);" onClick="check_content(\'Ajax.php?modname=schoolsetup/Calendar.php\');">' : '') . ''._calendarSetup.' ' . ($cal_setup[1]['REC'] > 0 ? '<small class="display-block no-margin text-success"><i class="icon-checkmark2"></i> '._complete.'</small>' : '<small class="display-block no-margin text-danger"><i class="icon-cross3"></i> '._incomplete.'</small>') . (AllowUse('schoolsetup/Calendar.php') == true ? '</a>' : '') . '</h6>
                                        </div>
                                    </div>
                                </div>


                                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 mb-15">
                                    <div class="well">
                                        <div class="media-left media-middle">' . (AllowUse('schoolsetup/MarkingPeriods.php') == true ? '<a href="javascript:void(0);" class="btn border-indigo-400 text-indigo-400 btn-flat btn-rounded btn-xs btn-icon" onClick="check_content(\'Ajax.php?modname=schoolsetup/MarkingPeriods.php\');">' : '') . '<i class="icon-tree7"></i>' . (AllowUse('schoolsetup/MarkingPeriods.php') == true ? '</a>' : '') . '</div>

                                        <div class="media-left">
                                            <h6 class="text-semibold no-margin">' . (AllowUse('schoolsetup/MarkingPeriods.php') == true ? '<a href="javascript:void(0);" onClick="check_content(\'Ajax.php?modname=schoolsetup/MarkingPeriods.php\');">' : '') . ''._markingPeriodSetup.'</a> ' . ($mp_setup[1]['REC'] > 1 ? '<small class="display-block no-margin text-success"><i class="icon-checkmark2"></i> '._complete.'</small>' : '<small class="display-block no-margin text-danger"><i class="icon-cross3"></i> '._incomplete.'</small>') . (AllowUse('schoolsetup/MarkingPeriods.php') == true ? '</a>' : '') . '</h6>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 mb-15">
                                    <div class="well">
                                        <div class="media-left media-middle">' . (AllowUse('attendance/AttendanceCodes.php') == true ? '<a href="javascript:void(0);" class="btn border-indigo-400 text-indigo-400 btn-flat btn-rounded btn-xs btn-icon" onClick="check_content(\'Ajax.php?modname=attendance/AttendanceCodes.php\');">' : '') . '<i class="icon-clipboard5"></i>' . (AllowUse('attendance/AttendanceCodes.php') == true ? '</a>' : '') . '</div>

                                        <div class="media-left">
                                            <h6 class="text-semibold no-margin">' . (AllowUse('attendance/AttendanceCodes.php') == true ? '<a href="javascript:void(0);" onClick="check_content(\'Ajax.php?modname=attendance/AttendanceCodes.php\');">' : '') . ''._attendanceCodeSetup.' ' . ($att_code_setup[1]['REC'] > 0 ? '<small class="display-block no-margin text-success"><i class="icon-checkmark2"></i> '._complete.'</small>' : '<small class="display-block no-margin text-danger"><i class="icon-cross3"></i> '._incomplete.'</small>') . (AllowUse('attendance/AttendanceCodes.php') == true ? '</a>' : '') . '</h6>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 mb-15">
                                    <div class="well">
                                        <div class="media-left media-middle">' . (AllowUse('grades/ReportCardGrades.php') == true ? '<a href="javascript:void(0);" class="btn border-indigo-400 text-indigo-400 btn-flat btn-rounded btn-xs btn-icon" onClick="check_content(\'Ajax.php?modname=grades/ReportCardGrades.php\');">' : '') . '<i class="icon-stack3"></i>' . (AllowUse('grades/ReportCardGrades.php') == true ? '</a>' : '') . '</div>

                                        <div class="media-left">
                                            <h6 class="text-semibold no-margin">' . (AllowUse('grades/ReportCardGrades.php') == true ? '<a href="javascript:void(0);" onClick="check_content(\'Ajax.php?modname=grades/ReportCardGrades.php\');">' : '') . ''._gradeScaleSetup.' ' . ($grade_scale_setup[1]['REC'] > 0 ? '<small class="display-block no-margin text-success"><i class="icon-checkmark2"></i> '._complete.'</small>' : '<small class="display-block no-margin text-danger"><i class="icon-cross3"></i> '._incomplete.'</small>') . (AllowUse('grades/ReportCardGrades.php') == true ? '</a>' : '') . '</h6>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 mb-15">
                                    <div class="well">
                                        <div class="media-left media-middle">' . (AllowUse('students/EnrollmentCodes.php') == true ? '<a href="javascript:void(0);" class="btn border-indigo-400 text-indigo-400 btn-flat btn-rounded btn-xs btn-icon" onClick="check_content(\'Ajax.php?modname=students/EnrollmentCodes.php\');">' : '') . '<i class="icon-clipboard6"></i>' . (AllowUse('students/EnrollmentCodes.php') == true ? '</a>' : '') . '</div>

                                        <div class="media-left">
                                            <h6 class="text-semibold no-margin">' . (AllowUse('students/EnrollmentCodes.php') == true ? '<a href="javascript:void(0);" onClick="check_content(\'Ajax.php?modname=students/EnrollmentCodes.php\');">' : '') . ''._enrollmentCodeSetup.' ' . ($enroll_code_setup[1]['REC'] > 0 ? '<small class="display-block no-margin text-success"><i class="icon-checkmark2"></i> '._complete.'</small>' : '<small class="display-block no-margin text-danger"><i class="icon-cross3"></i> '._incomplete.'</small>') . (AllowUse('students/EnrollmentCodes.php') == true ? '</a>' : '') . '</h6>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 mb-15">
                                    <div class="well">
                                        <div class="media-left media-middle">' . (AllowUse('schoolsetup/GradeLevels.php') == true ? '<a href="javascript:void(0);" class="btn border-indigo-400 text-indigo-400 btn-flat btn-rounded btn-xs btn-icon" onClick="check_content(\'Ajax.php?modname=schoolsetup/GradeLevels.php\');">' : '') . '<i class="icon-graph"></i>' . (AllowUse('schoolsetup/GradeLevels.php') == true ? '</a>' : '') . '</div>

                                        <div class="media-left">
                                            <h6 class="text-semibold no-margin">' . (AllowUse('schoolsetup/GradeLevels.php') == true ? '<a href="javascript:void(0);" onClick="check_content(\'Ajax.php?modname=schoolsetup/GradeLevels.php\');">' : '') . ''._gradeLevelSetup.' ' . ($grade_level_setup[1]['REC'] > 0 ? '<small class="display-block no-margin text-success"><i class="icon-checkmark2"></i> '._complete.'</small>' : '<small class="display-block no-margin text-danger"><i class="icon-cross3"></i> '._incomplete.'</small>') . (AllowUse('schoolsetup/GradeLevels.php') == true ? '</a>' : '') . '</h6>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 mb-15">
                                    <div class="well">
                                        <div class="media-left media-middle">' . (AllowUse('schoolsetup/Periods.php') == true ? '<a href="javascript:void(0);" class="btn border-indigo-400 text-indigo-400 btn-flat btn-rounded btn-xs btn-icon" onClick="check_content(\'Ajax.php?modname=schoolsetup/Periods.php\');">' : '') . '<i class="icon-watch2"></i>' . (AllowUse('schoolsetup/Periods.php') == true ? '</a>' : '') . '</div>

                                        <div class="media-left">
                                            <h6 class="text-semibold no-margin">' . (AllowUse('schoolsetup/Periods.php') == true ? '<a href="javascript:void(0);" onClick="check_content(\'Ajax.php?modname=schoolsetup/Periods.php\');">' : '') . ''._schoolPeriodsSetup.' ' . ($periods_setup[1]['REC'] > 0 ? '<small class="display-block no-margin text-success"><i class="icon-checkmark2"></i> '._complete.'</small>' : '<small class="display-block no-margin text-danger"><i class="icon-cross3"></i> '._incomplete.'</small>') . (AllowUse('schoolsetup/Periods.php') == true ? '</a>' : '') . '</h6>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 mb-15">
                                    <div class="well">
                                        <div class="media-left media-middle">' . (AllowUse('schoolsetup/Rooms.php') == true ? '<a href="javascript:void(0);" class="btn border-indigo-400 text-indigo-400 btn-flat btn-rounded btn-xs btn-icon" onClick="check_content(\'Ajax.php?modname=schoolsetup/Rooms.php\');">' : '') . '<i class="icon-grid6"></i>' . (AllowUse('schoolsetup/Rooms.php') == true ? '</a>' : '') . '</div>

                                        <div class="media-left">
                                            <h6 class="text-semibold no-margin">' . (AllowUse('schoolsetup/Rooms.php') == true ? '<a href="javascript:void(0);" onClick="check_content(\'Ajax.php?modname=schoolsetup/Rooms.php\');">' : '') . ''._roomsSetup.' ' . ($rooms_setup[1]['REC'] > 0 ? '<small class="display-block no-margin text-success"><i class="icon-checkmark2"></i> '._complete.'</small>' : '<small class="display-block no-margin text-danger"><i class="icon-cross3"></i> '._incomplete.'</small>') . (AllowUse('schoolsetup/Rooms.php') == true ? '</a>' : '') . '</h6>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- /.row -->
                        </div><!-- //.panel-body -->
                                               

                    </div>';
            }
        }




        //////////////// new  for incomplete marking period //////////
//                    $flag=0;
//                    $fy_edate=DBGet(DBQuery('SELECT END_DATE, START_DATE,MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID='.UserSchool().' AND SYEAR='.UserSyear()));
//                    $fuly_sdate=$fy_edate[1]['START_DATE'];
//                    $fuly_edate=$fy_edate[1]['END_DATE'];
//                    $fuly_mp_id=$fy_edate[1]['MARKING_PERIOD_ID'];
//                    $all_sem=DBGet(DBQuery('SELECT  MAX(END_DATE) as END_DATE ,MIN(start_date) as START_DATE  FROM school_semesters WHERE  YEAR_ID='.$fuly_mp_id.' AND SCHOOL_ID='.UserSchool().' AND SYEAR='.UserSyear()));
//                    
//                    if(($all_sem[1]['END_DATE']!='' && ($all_sem[1]['END_DATE']!=$fuly_edate)) || ($all_sem[1]['START_DATE']!='' && ($all_sem[1]['START_DATE']!=$fuly_sdate)))
//                    {
//                        $flag++;
//                    }
//    
//                    $all_sem_chk=DBGet(DBQuery('SELECT  *  FROM school_semesters WHERE  YEAR_ID='.$fuly_mp_id.' AND SCHOOL_ID='.UserSchool().' AND SYEAR='.UserSyear()));
//                    
//
//                    foreach($all_sem_chk as $all_sem_k=>$all_sem_v)
//                    {
//
//                       
//                      $qtr_edate_chk=DBGet(DBQuery('SELECT MAX(END_DATE) AS END_DATE, MIN(START_DATE) AS START_DATE FROM school_quarters WHERE SEMESTER_ID='.$all_sem_v['MARKING_PERIOD_ID'].' AND SCHOOL_ID='.UserSchool().' AND SYEAR='.UserSyear()));  
//
//                      if((($qtr_edate_chk[1]['END_DATE']!='') && $qtr_edate_chk[1]['END_DATE']!=$all_sem_v['END_DATE']) || (($qtr_edate_chk[1]['START_DATE']!='') && $qtr_edate_chk[1]['START_DATE']!=$all_sem_v['START_DATE']))
//                         
//                      {
//                          $flag++;
//
//                      }
//                      unset($qtr_edate_chk);
//                      
//                    }
//                    
//                    
//                
//                    if($flag>0)
//                    {
//                        $mp_not='<font style="color:red"><b>Marking period setup is incomplete.</b></font></br>';
//                    }
//                    
//                    echo ($mp_not!=''?$mp_not:'');
        ////////////////  end new //////////

        ######################################### Teacher reassignment #################################################
        // $reassign_cp = DBGet(DBQuery('SELECT COURSE_PERIOD_ID ,TEACHER_ID,PRE_TEACHER_ID,ASSIGN_DATE,COURSE_PERIOD_ID FROM teacher_reassignment WHERE ASSIGN_DATE <= \'' . date('Y-m-d') . '\' AND UPDATED=\'N\' '));
        // foreach ($reassign_cp as $re_key => $reassign_cp_value) {
        //     if (strtotime($reassign_cp_value['ASSIGN_DATE']) <= strtotime(date('Y-m-d'))) {

        //         $get_pname = DBGet(DBQuery("SELECT CONCAT(sp.title,IF(cp.marking_period_id!='',IF(cp.mp!='FY',CONCAT(' - ',mp.short_name),' '),' - Custom'),IF(CHAR_LENGTH(cpv.days)<5,CONCAT(' - ',cpv.days),' '),' - ',cp.short_name,' - ',CONCAT_WS(' ',st.first_name,st.middle_name,st.last_name)) AS CP_NAME FROM course_periods cp,course_period_var cpv,school_periods sp,marking_periods mp,staff st WHERE cpv.period_id=sp.period_id and (cp.marking_period_id=mp.marking_period_id or cp.marking_period_id is NULL) and st.staff_id=" . $reassign_cp_value['TEACHER_ID'] . "  AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=" . $reassign_cp_value['COURSE_PERIOD_ID']));
        //         $get_pname = $get_pname[1]['CP_NAME'];
        //         DBQuery('UPDATE course_periods SET TITLE=\'' . $get_pname . '\', teacher_id=' . $reassign_cp_value['TEACHER_ID'] . ' WHERE COURSE_PERIOD_ID=' . $reassign_cp_value['COURSE_PERIOD_ID']);
        //         DBQuery('UPDATE teacher_reassignment SET updated=\'Y\' WHERE assign_date <=CURDATE() AND updated=\'N\' AND COURSE_PERIOD_ID=' . $reassign_cp_value['COURSE_PERIOD_ID']);
        //         DBQuery('UPDATE missing_attendance SET TEACHER_ID=' . $reassign_cp_value['TEACHER_ID'] . ' WHERE TEACHER_ID=' . $reassign_cp_value['PRE_TEACHER_ID'] . ' AND COURSE_PERIOD_ID=' . $reassign_cp_value['COURSE_PERIOD_ID']);
        //     }
        // }
        ############################################# Teacher Reassignment End ##################################################

        // $schedule_exit = DBGet(DBQuery('SELECT ID FROM schedule WHERE syear=\'' . UserSyear() . '\' AND school_id=\'' . UserSchool() . '\'  LIMIT 0,1'));

        // if ($schedule_exit[1]['ID'] != '') {
        //     $last_update = DBGet(DBQuery('SELECT VALUE FROM program_config WHERE PROGRAM=\'MissingAttendance\' AND TITLE=\'LAST_UPDATE\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
        //     if ($last_update[1]['VALUE'] != '') {
        //         if ($last_update[1]['VALUE'] < date('Y-m-d')) {
        //             echo '<script type=text/javascript>calculate_missing_atten();</script>';
        //         }
        //     }
        // }


        $notes_RET = DBGet(DBQuery('SELECT IF(pn.published_profiles like\'%all%\',\'All School\',(SELECT TITLE FROM schools WHERE id=pn.school_id)) AS SCHOOL,pn.LAST_UPDATED,CONCAT(\'<b>\',pn.TITLE,\'</b>\') AS TITLE,pn.CONTENT 
                                    FROM portal_notes pn
                                    WHERE pn.SYEAR=\'' . UserSyear() . '\' AND pn.START_DATE<=CURRENT_DATE AND 
                                        (pn.END_DATE>=CURRENT_DATE OR pn.END_DATE IS NULL)
                                        AND (pn.published_profiles like\'%all%\' OR pn.school_id IN(' . UserSchool() . '))
                                        AND (' . (User('PROFILE_ID') == '' ? ' FIND_IN_SET(\'admin\', pn.PUBLISHED_PROFILES)>0' : ' FIND_IN_SET(' . User('PROFILE_ID') . ',pn.PUBLISHED_PROFILES)>0)') .
                        'ORDER BY pn.SORT_ORDER,pn.LAST_UPDATED DESC'), array('LAST_UPDATED' => 'ProperDate', 'CONTENT' => '_nl2br'));
      
      
                        if (count($notes_RET)) {
            echo '<div class="panel panel-default">';
            ListOutput($notes_RET, array('LAST_UPDATED' =>_datePosted,
             'TITLE' =>_title,
             'CONTENT' =>_note,
             'SCHOOL' =>_school,
            ), _note, _notes, array(), array(), array('save' =>false, 'search' =>false));
            echo '</div>';
        }
            
//            echo '<div class="panel panel-default">';
//            echo '<div class="panel-heading">';
//            echo '<h6 class="panel-title text-orange"><i class="icon-file-text2"></i> <b>1 Note was found</b></h6>';
//            echo '</div>'; //.panel-heading
//            echo '<div class="panel-body">';
//            echo '<h5 class="m-t-0 m-b-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit</h5>';
//            echo '<p class="text-grey-300">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean nec cursus massa. Donec dapibus lacus metus, at imperdiet nisl hendrerit eget. Nulla id sapien hendrerit, fringilla augue eu, facilisis massa. Sed in orci sed erat placerat aliquam sodales non ipsum. In eu purus tempor, rhoncus elit sed, 
//lacinia mi. Aliquam non sollicitudin sem. Ut ut nulla nul...[<a href="">read more</a>]<p/>';
//            echo '<p class="text-grey-700"><i class="icon-calendar3 text-primary"></i> Posted on Mar/6/2018 &nbsp; &nbsp; <i class="icon-users text-primary"></i> Visible to All School</p>';
//            echo '</div>'; //.panel-body
//            echo '<hr class="no-margin"/>';
//            echo '<div class="panel-body">';
//            echo '<h5 class="m-t-0 m-b-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit</h5>';
//            echo '<p class="text-grey-300">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean nec cursus massa. Donec dapibus lacus metus, at imperdiet nisl hendrerit eget. Nulla id sapien hendrerit, fringilla augue eu, facilisis massa. Sed in orci sed erat placerat aliquam sodales non ipsum. In eu purus tempor, rhoncus elit sed, 
//lacinia mi. Aliquam non sollicitudin sem. Ut ut nulla nul...[<a href="">read more</a>]<p/>';
//            echo '<p class="text-grey-700"><i class="icon-calendar3 text-primary"></i> Posted on Mar/6/2018 &nbsp; &nbsp; <i class="icon-users text-primary"></i> Visible to All School</p>';
//            echo '</div>'; //.panel-body
//            echo '</div>';
//        $events_RET = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE AS INDEX_DATE,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
//                FROM calendar_events ce,calendar_events_visibility cev,schools s
//                WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
//                    AND ce.SYEAR=\'' . UserSyear() . '\'
//                    AND ce.SCHOOL_ID IN(' . GetUserSchools(UserID(), true) . ')
//                    AND s.ID=ce.SCHOOL_ID AND (ce.CALENDAR_ID=cev.CALENDAR_ID)
//                    AND ' . (User('PROFILE_ID') == '' ? 'cev.PROFILE=\'admin\'' : 'cev.PROFILE_ID=\'' . User('PROFILE_ID')) . '\' 
//                    ORDER BY ce.SCHOOL_DATE,s.TITLE'), array('SCHOOL_DATE' => 'ProperDate', 'DESCRIPTION' => 'makeDescription'));
        
        $events_RET = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE AS INDEX_DATE,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                FROM calendar_events ce,calendar_events_visibility cev,schools s
                WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                    AND ce.SYEAR=\'' . UserSyear() . '\'
                    AND ce.SCHOOL_ID IN(' . UserSchool(). ')
                    AND s.ID=ce.SCHOOL_ID AND (ce.CALENDAR_ID=cev.CALENDAR_ID)
                    AND ' . (User('PROFILE_ID') == '' ? 'cev.PROFILE=\'admin\'' : 'cev.PROFILE_ID=\'' . User('PROFILE_ID')) . '\' 
                    ORDER BY ce.SCHOOL_DATE,s.TITLE'), array('SCHOOL_DATE' => 'ProperDate', 'DESCRIPTION' => 'makeDescription'));

        $events_RET1 = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION, ce.SCHOOL_DATE as index_date,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                FROM calendar_events ce,schools s
                WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                    AND ce.SYEAR=\'' . UserSyear() . '\'
                    AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=0 ORDER BY ce.SCHOOL_DATE,s.TITLE'), array('SCHOOL_DATE' => 'ProperDate', 'DESCRIPTION' => 'makeDescription'));
        $event_count = count($events_RET) + 1;
        foreach ($events_RET1 as $events_RET_key => $events_RET_value) {
            $events_RET[$event_count] = $events_RET_value;
            $event_count++;
        }


        $new_arr = array();
        foreach ($events_RET as $key => $val) {
            $new_arr[strtotime($val['INDEX_DATE'])][$key] = $val;
        }
        ksort($new_arr);
        $keyt = 1;
        foreach ($new_arr as $key1 => $val1) {
            foreach ($val1 as $val2) {
                $events_RET[$keyt] = $val2;
                $keyt++;
            }
        }
        if (count($events_RET)) {
            echo '<div class="panel panel-default">';
            ListOutput($events_RET, array('SCHOOL_DATE' =>_date,
             'TITLE' =>_event,
             'DESCRIPTION' =>_description,
             'SCHOOL' =>_school,
            ), _upcomingEvent, _upcomingEvents, array(), array(), array('save' =>false, 'search' =>false));
            echo '</div>'; //.panel
        }

        # ------------------------------------ Original Raw Query Start ------------------------------------------------ #


            //echo 'SELECT SCHOOL_ID,SCHOOL_DATE,COURSE_PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID FROM missing_attendance WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.  UserSyear().'\' AND SCHOOL_DATE<\''.date('Y-m-d').'\' LIMIT 0,1 ';
            //echo 'SELECT SCHOOL_ID,SCHOOL_DATE,COURSE_PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID FROM missing_attendance WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.  UserSyear().'\' AND SCHOOL_DATE<\''.date('Y-m-d').'\' ORDER BY SCHOOL_DATE LIMIT 0,1 ';
//                   $RET=DBGet(DBQuery('SELECT SCHOOL_ID,SCHOOL_DATE,COURSE_PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID FROM missing_attendance WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.  UserSyear().'\' AND SCHOOL_DATE<\''.date('Y-m-d').'\' ORDER BY SCHOOL_DATE LIMIT 0,1 '));
            // echo 'SELECT mi.SCHOOL_ID,mi.SCHOOL_DATE,mi.COURSE_PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID FROM missing_attendance mi,course_periods cp,schools s,course_period_var cpv WHERE mi.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cpv.PERIOD_ID=mi.PERIOD_ID AND s.ID=mi.SCHOOL_ID WHERE mi.SCHOOL_ID=\''.UserSchool().'\' AND mi.SYEAR=\''.  UserSyear().'\' AND mi.SCHOOL_DATE<\''.date('Y-m-d').'\' ORDER BY mi.SCHOOL_DATE AND (mi.SCHOOL_DATE=cpv.COURSE_PERIOD_DATE OR POSITION(IF(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\') LIKE \'Thu\',\'H\',(IF(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\') LIKE \'Sun\',\'U\',SUBSTR(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\'),1,1)))) IN cpv.DAYS)>0)';
            //echo 'SELECT mi.SCHOOL_ID,mi.SCHOOL_DATE,mi.COURSE_PERIOD_ID,mi.TEACHER_ID,mi.SECONDARY_TEACHER_ID FROM missing_attendance mi,course_periods cp,schools s,course_period_var cpv WHERE mi.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cpv.PERIOD_ID=mi.PERIOD_ID AND s.ID=mi.SCHOOL_ID and mi.SCHOOL_ID=\''.UserSchool().'\' AND mi.SYEAR=\''.  UserSyear().'\' AND mi.SCHOOL_DATE<\''.date('Y-m-d').'\' ORDER BY mi.SCHOOL_DATE AND (mi.SCHOOL_DATE=cpv.COURSE_PERIOD_DATE OR POSITION(IF(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\') LIKE \'Thu\',\'H\',(IF(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\') LIKE \'Sun\',\'U\',SUBSTR(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\'),1,1)))) IN cpv.DAYS)>0)';
            if (Preferences('HIDE_ALERTS') != 'Y') {
            $RET = DBGet(DBQuery('SELECT mi.SCHOOL_ID,mi.SCHOOL_DATE,mi.COURSE_PERIOD_ID,mi.TEACHER_ID,mi.SECONDARY_TEACHER_ID FROM missing_attendance mi,course_periods cp,schools s,course_period_var cpv WHERE mi.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cpv.PERIOD_ID=mi.PERIOD_ID AND s.ID=mi.SCHOOL_ID and mi.SCHOOL_ID=\'' . UserSchool() . '\' AND mi.SYEAR=\'' . UserSyear() . '\' AND mi.SCHOOL_DATE<\'' . date('Y-m-d') . '\'  AND (mi.SCHOOL_DATE=cpv.COURSE_PERIOD_DATE OR POSITION(IF(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\') LIKE \'Thu\',\'H\',(IF(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\') LIKE \'Sun\',\'U\',SUBSTR(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\'),1,1)))) IN cpv.DAYS)>0)'));

            if (count($RET)) {
                echo '<div class="alert alert-danger alert-styled-left alert-bordered">';
                //echo '<button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">Close</span></button>';
                echo '<span class="text-bold">'._warning.'!!</span> - '._teachersHaveMissingAttendance.'. '._go_To.': <span class="text-bold">'._users.' <i class="icon-arrow-right13"></i>'._teacherPrograms.' <i class="icon-arrow-right13"></i> '._missingAttendance.'.</span>';
                echo '</div>';
            }
        }
        echo '<div id="attn_alert" style="display: none" class="alert alert-danger alert-styled-left alert-bordered"><span class="text-bold">'._warning.'!!</span> - '._teachersHaveMissingAttendance.'. '._go_To.' : <b>Users <i class="icon-arrow-right13"></i> '._teacherPrograms.'<i class="icon-arrow-right13"></i>'._missingAttendance.'</b></div>';
        //-------------------------------------------------------------------------------ROLLOVER NOTIFICATION STARTS----------------------------------------------------------------------------------------------------------------------------------------------------------------------------

        $notice_date = DBGet(DBQuery('SELECT END_DATE FROM school_years WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
        $notice_roll_date = DBGet(DBQuery('SELECT SYEAR FROM school_years WHERE SYEAR>\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
        $rolled = count($notice_roll_date);
        $last_date = strtotime($notice_date[1]['END_DATE']) - strtotime(DBDate());
        $last_date = $last_date / (60 * 60 * 24);
        if ($last_date <= 15 && $rolled == 0) {
            echo '<div class="alert alert-warning alert-styled-left">'._schoolYearIsEndingOrHasEndedRolloverRequired.'.</div>';
        }
        //-------------------------------------------------------------------------------ROLLOVER NOTIFICATION ENDS----------------------------------------------------------------------------------------------------------------------------------------------------------------------------


        break;

    case 'teacher':
        DrawBC($welcome . ' | '._role.' : '._teacher.'');
        $att_qry = DBGet(DBQuery('SELECT Count(1) as count FROM  profile_exceptions WHERE MODNAME 
                  IN (\'attendance/TakeAttendance.php\',\'attendance/DailySummary.php\',\'attendance/StudentSummary\') AND 
                  PROFILE_ID=' . User('PROFILE_ID') . ' AND CAN_USE=\'Y\' '));

        ############################################### Teacher Reassignment ########################################## 
        // $reassign_cp = DBGet(DBQuery('SELECT COURSE_PERIOD_ID ,TEACHER_ID,PRE_TEACHER_ID,ASSIGN_DATE FROM teacher_reassignment WHERE ASSIGN_DATE <= \'' . date('Y-m-d') . '\' AND UPDATED=\'N\' '));
        // foreach ($reassign_cp as $re_key => $reassign_cp_value) {
        //     if (strtotime($reassign_cp_value['ASSIGN_DATE']) <= strtotime(date('Y-m-d'))) {
        //         $get_pname = DBGet(DBQuery("SELECT CONCAT(sp.title,IF(cp.marking_period_id!='',IF(cp.mp!='FY',CONCAT(' - ',mp.short_name),' '),' - Custom'),IF(CHAR_LENGTH(cpv.days)<5,CONCAT(' - ',cpv.days),' '),' - ',cp.short_name,' - ',CONCAT_WS(' ',st.first_name,st.middle_name,st.last_name)) AS CP_NAME FROM course_periods cp,course_period_var cpv,school_periods sp,marking_periods mp,staff st WHERE cpv.period_id=sp.period_id and (cp.marking_period_id=mp.marking_period_id or cp.marking_period_id is NULL) and st.staff_id=" . $reassign_cp_value['TEACHER_ID'] . "  AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=" . $reassign_cp_value['COURSE_PERIOD_ID']));
        //         $get_pname = $get_pname[1]['CP_NAME'];
        //         DBQuery('UPDATE course_periods SET title=\'' . $get_pname . '\', teacher_id=' . $reassign_cp_value['TEACHER_ID'] . ' WHERE COURSE_PERIOD_ID=' . $reassign_cp_value['COURSE_PERIOD_ID']);
        //         DBQuery('UPDATE teacher_reassignment SET updated=\'Y\' WHERE assign_date <=CURDATE() AND updated=\'N\' AND COURSE_PERIOD_ID=' . $reassign_cp_value['COURSE_PERIOD_ID']);
        //         DBQuery('UPDATE missing_attendance SET TEACHER_ID=' . $reassign_cp_value['TEACHER_ID'] . ' WHERE TEACHER_ID=' . $reassign_cp_value['PRE_TEACHER_ID'] . ' AND COURSE_PERIOD_ID=' . $reassign_cp_value['COURSE_PERIOD_ID']);
        //     }
        // }
        ############################################# Teacher Reassignment End ##################################################
        // $schedule_exit = DBGet(DBQuery('SELECT ID FROM schedule WHERE syear=\'' . UserSyear() . '\' AND school_id=\'' . UserSchool() . '\' LIMIT 0,1'));
        // if ($schedule_exit[1]['ID'] != '') {
        //     $last_update = DBGet(DBQuery('SELECT VALUE FROM program_config WHERE PROGRAM=\'MissingAttendance\' AND TITLE=\'LAST_UPDATE\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
        //     if ($last_update[1]['VALUE'] != '') {
        //         if ($last_update[1]['VALUE'] < date('Y-m-d')) {

        //             echo '<script type=text/javascript>calculate_missing_atten();</script>';
        //         }
        //     }
        // }
        $notes_RET = DBGet(DBQuery('SELECT IF(pn.school_id IS NULL,\'All School\',(SELECT TITLE FROM schools WHERE id=pn.school_id)) AS SCHOOL,pn.LAST_UPDATED,CONCAT(\'<b>\',pn.TITLE,\'</b>\') AS TITLE,pn.CONTENT 
                            FROM portal_notes pn
                            WHERE pn.SYEAR=\'' . UserSyear() . '\' AND pn.START_DATE<=CURRENT_DATE AND 
                                (pn.END_DATE>=CURRENT_DATE OR pn.END_DATE IS NULL)
                                AND (pn.school_id IS NULL OR pn.school_id IN(' . GetUserSchools(UserID(), true) . '))
                                AND (' . (User('PROFILE_ID') == '' ? ' FIND_IN_SET(\'teacher\', pn.PUBLISHED_PROFILES)>0' : ' FIND_IN_SET(' . User('PROFILE_ID') . ',pn.PUBLISHED_PROFILES)>0)') . '
                                ORDER BY pn.SORT_ORDER,pn.LAST_UPDATED DESC'), array('LAST_UPDATED' => 'ProperDate', 'CONTENT' => '_nl2br'));

        if (count($notes_RET)) {
            echo '<div class="panel panel-default">';
            ListOutput($notes_RET, array('LAST_UPDATED' =>_datePosted,
             'TITLE' =>_title,
             'CONTENT' =>_note,
             'SCHOOL' =>_school,
            ), _note, _notes, array(), array(), array('save' =>false, 'search' =>false));
            echo '</div>';
        }
        
        $events_RET = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                FROM calendar_events ce,calendar_events_visibility cev,schools s
                WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                    AND ce.SYEAR=\'' . UserSyear() . '\'
                    AND ce.school_id IN(' . UserSchool() . ')
                    AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=cev.CALENDAR_ID 
                    AND ' . (User('PROFILE_ID') == '' ? 'cev.PROFILE=\'teacher\'' : 'cev.PROFILE_ID=' . User('PROFILE_ID')) . ' 
                    ORDER BY ce.SCHOOL_DATE,s.TITLE'), array('SCHOOL_DATE' => 'ProperDate', 'DESCRIPTION' => 'makeDescription'));

//        $events_RET = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
//                FROM calendar_events ce,calendar_events_visibility cev,schools s
//                WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
//                    AND ce.SYEAR=\'' . UserSyear() . '\'
//                    AND ce.school_id IN(' . GetUserSchools(UserID(), true) . ')
//                    AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=cev.CALENDAR_ID 
//                    AND ' . (User('PROFILE_ID') == '' ? 'cev.PROFILE=\'teacher\'' : 'cev.PROFILE_ID=' . User('PROFILE_ID')) . ' 
//                    ORDER BY ce.SCHOOL_DATE,s.TITLE'), array('SCHOOL_DATE' => 'ProperDate', 'DESCRIPTION' => 'makeDescription'));
        

        $events_RET1 = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                FROM calendar_events ce,schools s
                WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                    AND ce.SYEAR=\'' . UserSyear() . '\'
                    AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=0 ORDER BY ce.SCHOOL_DATE,s.TITLE'), array('SCHOOL_DATE' => 'ProperDate', 'DESCRIPTION' => 'makeDescription'));
        $event_count = count($events_RET) + 1;
        foreach ($events_RET1 as $events_RET_key => $events_RET_value) {
            $events_RET[$event_count] = $events_RET_value;
            $event_count++;
        }
        if (count($events_RET)) {
            echo '<div class="panel panel-default">';
            ListOutput($events_RET, array('SCHOOL_DATE' =>_date,
             'TITLE' =>_event,
             'DESCRIPTION' =>_description,
             'SCHOOL' =>_school,
            ), _upcomingEvent, _upcomingEvents, array(), array(), array('save' =>false, 'search' =>false));
            echo '</div>';
        }
        if ($att_qry[1]['count'] != 0)
            echo '<div id="attn_alert" style="display: none" class="alert alert-danger alert-styled-left alert-bordered"><span class="text-bold">'._warning.'!!</span> - '._teachersHaveMissingAttendance.'. '._go_To.' : <b>'._users.' -> '._teacherPrograms.' -> '._missingAttendance.'</b></div>';
        if (Preferences('HIDE_ALERTS') != 'Y') {
            // warn if missing attendance

            if ($_REQUEST['modfunc'] == 'attn') {
                header("Location:Modules.php?modname=users/TeacherPrograms.php?include=attendance/TakeAttendance.php");
            }



            $RET = DBGet(DBQuery('SELECT DISTINCT s.TITLE AS SCHOOL,mi.SCHOOL_DATE,cp.TITLE AS TITLE,mi.COURSE_PERIOD_ID,mi.PERIOD_ID,cpv.ID AS CPV_ID FROM missing_attendance mi,schools s,course_periods cp,course_period_var cpv WHERE s.ID=mi.SCHOOL_ID AND  cp.COURSE_PERIOD_ID=mi.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND mi.period_id=cpv.period_id AND (mi.TEACHER_ID=\'' . User('STAFF_ID') . '\' OR mi.SECONDARY_TEACHER_ID=\'' . User('STAFF_ID') . '\') AND mi.SCHOOL_ID=\'' . UserSchool() . '\' AND mi.SYEAR=\'' . UserSyear() . '\' AND mi.SCHOOL_DATE < \'' . DBDate() . '\' AND (mi.SCHOOL_DATE=cpv.COURSE_PERIOD_DATE OR POSITION(IF(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\') LIKE \'Thu\',\'H\',(IF(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\') LIKE \'Sun\',\'U\',SUBSTR(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\'),1,1)))) IN cpv.DAYS)>0) AND IF(cp.SCHEDULE_TYPE=\'BLOCKED\', mi.SCHOOL_DATE=cpv.COURSE_PERIOD_DATE, 1) ORDER BY mi.SCHOOL_DATE,cp.TITLE'), array('SCHOOL_DATE' => 'ProperDate'));

            $codes_RET_count = DBGet(DBQuery('SELECT COUNT(*) AS CODES FROM attendance_codes WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'  AND TYPE=\'teacher\' AND TABLE_NAME=\'0\' ORDER BY SORT_ORDER'));

            if (count($RET) && $codes_RET_count[1]['CODES']) {
                echo '<div class="alert alert-danger alert-styled-left alert-bordered"><span class="text-bold">'._warning.'!</span> '._teachersHaveMissingAttendanceData.'.</div>';

                $modname = 'users/TeacherPrograms.php?include=attendance/TakeAttendance.php';
                $link['remove']['link'] = "Modules.php?modname=$modname&modfunc=attn&attn=miss&from_dasboard=1";
                $link['remove']['variables'] = array('date' => 'SCHOOL_DATE', 'cp_id_miss_attn' => 'COURSE_PERIOD_ID', 'cpv_id_miss_attn' => 'CPV_ID');
                $_SESSION['take_mssn_attn'] = true;

                echo '<div class="panel panel-default">';
                ListOutput_missing_attn_teach_port($RET, array('SCHOOL_DATE' => _date,
                 'TITLE' => _periodTeacher,
                 'SCHOOL' => _school,
                ), _period, _periods, $link, array(), array('save' =>false, 'search' =>false));
                echo '</div>';
            }
        }



        break;

    case 'parent':
        DrawBC($welcome . ' | '._role.' : '._parent.'');
        $notes_RET = DBGet(DBQuery('SELECT IF(pn.school_id IS NULL,\'All School\',(SELECT TITLE FROM schools WHERE id=pn.school_id)) AS SCHOOL,pn.LAST_UPDATED,pn.TITLE,pn.CONTENT 
            FROM portal_notes pn
            WHERE pn.SYEAR=\'' . UserSyear() . '\' 
                AND pn.START_DATE<=CURRENT_DATE AND (pn.END_DATE>=CURRENT_DATE OR pn.END_DATE IS NULL) 
                AND (pn.school_id IS NULL OR pn.school_id IN(' . GetUserSchools(UserID(), true) . '))
                AND (' . (User('PROFILE_ID') == '' ? ' FIND_IN_SET(\'parent\', pn.PUBLISHED_PROFILES)>0' : ' FIND_IN_SET(' . User('PROFILE_ID') . ',pn.PUBLISHED_PROFILES)>0)') . '
                ORDER BY pn.SORT_ORDER,pn.LAST_UPDATED DESC'), array('LAST_UPDATED' => 'ProperDate', 'CONTENT' => '_nl2br'));

        if (count($notes_RET)) {
            echo '<div class="panel">';
            ListOutput($notes_RET, array('LAST_UPDATED' =>_datePosted,
             'TITLE' =>_title,
             'CONTENT' =>_note,
             'SCHOOL' =>_school,
            ), _note, _notes, array(), array(), array('save' =>false, 'search' =>false));
            echo '</div>';
        }

//        $events_RET = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
//                FROM calendar_events ce,calendar_events_visibility cev,schools s
//                WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
//                    AND ce.SYEAR=\'' . UserSyear() . '\'
//                    AND ce.school_id IN(' . GetUserSchools(UserID(), true) . ')
//                    AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=cev.CALENDAR_ID 
//                    AND ' . (User('PROFILE_ID') == '' ? 'cev.PROFILE=\'parent\'' : 'cev.PROFILE_ID=' . User('PROFILE_ID')) . ' 
//                    ORDER BY ce.SCHOOL_DATE,s.TITLE'), array('SCHOOL_DATE' => 'ProperDate', 'DESCRIPTION' => 'makeDescription'));
        
        $events_RET = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                FROM calendar_events ce,calendar_events_visibility cev,schools s
                WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                    AND ce.SYEAR=\'' . UserSyear() . '\'
                    AND ce.school_id IN(' . UserSchool() . ')
                    AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=cev.CALENDAR_ID 
                    AND ' . (User('PROFILE_ID') == '' ? 'cev.PROFILE=\'parent\'' : 'cev.PROFILE_ID=' . User('PROFILE_ID')) . ' 
                    ORDER BY ce.SCHOOL_DATE,s.TITLE'), array('SCHOOL_DATE' => 'ProperDate', 'DESCRIPTION' => 'makeDescription'));
        $events_RET1 = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                FROM calendar_events ce,schools s
                WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                    AND ce.SYEAR=\'' . UserSyear() . '\'
                    AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=0 ORDER BY ce.SCHOOL_DATE,s.TITLE'), array('SCHOOL_DATE' => 'ProperDate', 'DESCRIPTION' => 'makeDescription'));
        $event_count = count($events_RET) + 1;
        foreach ($events_RET1 as $events_RET_key => $events_RET_value) {
            $events_RET[$event_count] = $events_RET_value;
            $event_count++;
        }
        if (count($events_RET)) {
            echo '<div class="panel">';
            ListOutput($events_RET, array('SCHOOL_DATE' =>_date,
             'TITLE' =>_event,
             'DESCRIPTION' =>_description,
             'SCHOOL' =>_school,
            ), _upcomingEvent, _upcomingEvents, array(), array(), array('save' =>false, 'search' =>false));
            echo '</div>';
        }




        $courses_RET = DBGet(DBQuery('SELECT DISTINCT c.TITLE ,cp.COURSE_PERIOD_ID,cp.COURSE_ID,cp.TEACHER_ID AS STAFF_ID FROM schedule s,course_periods cp,course_period_var cpv,courses c,attendance_calendar acc WHERE s.SYEAR=\'' . UserSyear() . '\' AND cp.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID  AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID  AND (s.MARKING_PERIOD_ID IN (SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE  UNION SELECT MARKING_PERIOD_ID FROM school_semesters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE  UNION SELECT MARKING_PERIOD_ID FROM school_quarters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE )or s.MARKING_PERIOD_ID  is NULL) AND (\'' . DBDate() . '\' BETWEEN s.START_DATE AND s.END_DATE OR \'' . DBDate() . '\'>=s.START_DATE AND s.END_DATE IS NULL) AND s.STUDENT_ID=\'' . UserStudentID() . '\' AND cp.GRADE_SCALE_ID IS NOT NULL' . (User('PROFILE') == 'teacher' ? ' AND cp.TEACHER_ID=\'' . User('STAFF_ID') . '\'' : '') . ' AND c.COURSE_ID=cp.COURSE_ID ORDER BY (SELECT SORT_ORDER FROM school_periods WHERE PERIOD_ID=cpv.PERIOD_ID)'));

        foreach ($courses_RET as $course) {
            $staff_id = $course['STAFF_ID'];
            $assignments_Graded = DBGet(DBQuery('SELECT gg.STUDENT_ID,ga.ASSIGNMENT_ID,gg.POINTS,gg.COMMENT,ga.TITLE,ga.DESCRIPTION,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS AS POINTS_POSSIBLE,at.TITLE AS CATEGORY
                                                   FROM gradebook_assignments ga LEFT OUTER JOIN gradebook_grades gg
                                                  ON (gg.COURSE_PERIOD_ID=\'' . $course['COURSE_PERIOD_ID'] . '\' AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.STUDENT_ID=\'' . UserStudentID() . '\'),gradebook_assignment_types at
                                                  WHERE (ga.COURSE_PERIOD_ID=\'' . $course['COURSE_PERIOD_ID'] . '\' OR ga.COURSE_ID=\'' . $course['COURSE_ID'] . '\' AND ga.STAFF_ID=\'' . $staff_id . '\') AND ga.MARKING_PERIOD_ID=\'' . UserMP() . '\'
                                                   AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND (gg.POINTS IS NOT NULL) AND (ga.POINTS!=\'0\' OR gg.POINTS IS NOT NULL AND gg.POINTS!=\'-1\') ORDER BY ga.ASSIGNMENT_ID DESC'));

            $GRADED_ASSIGNMENT_ID = array();
            foreach ($assignments_Graded AS $assignments_Graded)
                $GRADED_ASSIGNMENT_ID[] = $assignments_Graded['ASSIGNMENT_ID'];
            $ASSIGNMENT_ID_GRADED = implode(",", $GRADED_ASSIGNMENT_ID);

            $GRADED_ASSIGNMENT = '( ' . $ASSIGNMENT_ID_GRADED . ' )';

            $full_year_mp = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear()));
            $full_year_mp = $full_year_mp[1]['MARKING_PERIOD_ID'];


            if (count($assignments_Graded)) {
                $assignments_RET = DBGet(DBQuery('SELECT ga.ASSIGNMENT_ID,ga.TITLE,ga.DESCRIPTION as COMMENT,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS AS POINTS_POSSIBLE,at.TITLE AS CATEGORY
                                                   FROM gradebook_assignments ga
                                                 ,gradebook_assignment_types at
                                                  WHERE ga.ASSIGNMENT_ID NOT IN ' . $GRADED_ASSIGNMENT . ' AND (ga.COURSE_PERIOD_ID=\'' . $course[COURSE_PERIOD_ID] . '\' OR ga.COURSE_ID=' . $course[COURSE_ID] . ' AND ga.STAFF_ID=' . $staff_id . ') AND (ga.MARKING_PERIOD_ID=\'' . UserMP() . '\'or ga.MARKING_PERIOD_ID=' . $full_year_mp . ')
                                                   AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND(  CURRENT_DATE>=ga.ASSIGNED_DATE OR CURRENT_DATE<=ga.ASSIGNED_DATE )AND ga.DUE_DATE IS NOT NULL AND CURRENT_DATE<=ga.DUE_DATE
                                                   AND (ga.POINTS!=\'0\') ORDER BY ga.ASSIGNMENT_ID DESC'), array('ASSIGNED_DATE' => 'ProperDate', 'DUE_DATE' => 'ProperDate'));
            } else {

                $assignments_RET = DBGet(DBQuery('SELECT ga.ASSIGNMENT_ID,ga.TITLE,ga.DESCRIPTION as COMMENT,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS AS POINTS_POSSIBLE,at.TITLE AS CATEGORY
                                                   FROM gradebook_assignments ga
                                                 ,gradebook_assignment_types at
                                                  WHERE (ga.COURSE_PERIOD_ID=\'' . $course['COURSE_PERIOD_ID'] . '\' OR ga.COURSE_ID=\'' . $course['COURSE_ID'] . '\' AND ga.STAFF_ID=\'' . $staff_id . '\') AND (ga.MARKING_PERIOD_ID=\'' . UserMP() . '\' or ga.MARKING_PERIOD_ID=' . $full_year_mp . ')
                                                   AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND( CURRENT_DATE>=ga.ASSIGNED_DATE OR CURRENT_DATE<=ga.ASSIGNED_DATE)AND ga.DUE_DATE IS NOT NULL AND CURRENT_DATE<=ga.DUE_DATE
                                                   AND (ga.POINTS!=\'0\') ORDER BY ga.ASSIGNMENT_ID DESC'), array('ASSIGNED_DATE' => 'ProperDate', 'DUE_DATE' => 'ProperDate'));
            }


            if (count($assignments_RET)) {


                $LO_columns = array('TITLE' =>_title,
                 'CATEGORY' =>_category,
                 'ASSIGNED_DATE' =>_assignedDate,
                 'DUE_DATE' =>_dueDate,
                 'COMMENT' =>_description,
                );

                $LO_ret = array(0 => array());
                foreach ($assignments_RET as $assignment) {
                    $LO_ret[] = array('TITLE' => $assignment['TITLE'], 'CATEGORY' => $assignment['CATEGORY'], 'ASSIGNED_DATE' => $assignment['ASSIGNED_DATE'], 'DUE_DATE' => $assignment['DUE_DATE'], 'COMMENT' => html_entity_decode(html_entity_decode($assignment['COMMENT'])));
                }

                echo '<div class="panel">';
                DrawHeader('<span class="text-pink">Subject - ' . substr($course['TITLE'] . '</span>', strrpos(str_replace(' - ', ' ^ ', $course['TITLE']), '^')));
                echo '<hr class="no-margin" />';
                unset($LO_ret[0]);
                ListOutput($LO_ret, $LO_columns,  _assignment, _assignments, array(), array(), array('center' =>false, 'save' => $_REQUEST['id'] != 'all', 'search' =>false));
                echo '</div>';
            }
        }

        break;

    case 'student':
        DrawBC($welcome . ' | '._role.' : '._student.'');

        $notes_RET = DBGet(DBQuery('SELECT IF(pn.school_id IS NULL,\'All School\',(SELECT TITLE FROM schools WHERE id=pn.school_id)) AS SCHOOL,pn.LAST_UPDATED,pn.TITLE,pn.CONTENT 
            FROM portal_notes pn
            WHERE pn.SYEAR=\'' . UserSyear() . '\' 
                AND pn.START_DATE<=CURRENT_DATE AND (pn.END_DATE>=CURRENT_DATE OR pn.END_DATE IS NULL) 
                AND (pn.school_id IS NULL OR pn.SCHOOL_ID=\'' . UserSchool() . '\') 
                AND  position(\',3,\' IN pn.PUBLISHED_PROFILES)>0
                ORDER BY pn.SORT_ORDER,pn.LAST_UPDATED DESC'), array('LAST_UPDATED' => 'ProperDate', 'CONTENT' => '_nl2br'));

        if (count($notes_RET)) {
            echo '<div class="panel panel-default">';

            ListOutput($notes_RET, array('LAST_UPDATED' => _datePosted,
             'TITLE' => _title,
             'CONTENT' => _note,
            ), _note, _notes, array(), array(), array('save' =>false, 'search' =>false));
            echo '</div>';
        }


        $events_RET = DBGet(DBQuery("SELECT TITLE,SCHOOL_DATE,DESCRIPTION FROM calendar_events ce,calendar_events_visibility cev WHERE ce.calendar_id=cev.calendar_id AND cev.profile_id=3 AND SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE+30 AND SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "'"), array('SCHOOL_DATE' => 'ProperDate', 'DESCRIPTION' => 'makeDescription'));
        $events_RET1 = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                FROM calendar_events ce,schools s
                WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                    AND ce.SYEAR=\'' . UserSyear() . '\'
                    AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=0 ORDER BY ce.SCHOOL_DATE,s.TITLE'), array('SCHOOL_DATE' => 'ProperDate', 'DESCRIPTION' => 'makeDescription'));
        $event_count = count($events_RET) + 1;
        foreach ($events_RET1 as $events_RET_key => $events_RET_value) {
            $events_RET[$event_count] = $events_RET_value;
            $event_count++;
        }
        if (count($events_RET)) {
            echo '<div class="panel panel-default">';
            ListOutput($events_RET, array('TITLE' => _event,
             'SCHOOL_DATE' => _date,
             'DESCRIPTION' => _description,
            ), _upcomingEvent, _upcomingEvents, array(), array(), array('save' =>false, 'search' =>false));
            echo '</div>';
        }


        $sql = 'SELECT s.STAFF_ID,CONCAT(s.LAST_NAME,\', \',s.FIRST_NAME) AS FULL_NAME,sp.TITLE,cp.PERIOD_ID
		FROM staff s,course_periods cp,school_periods sp, attendance_calendar acc
		WHERE
			sp.PERIOD_ID = cp.PERIOD_ID AND cp.GRADE_SCALE_ID IS NOT NULL
			AND cp.TEACHER_ID=s.STAFF_ID AND cp.MARKING_PERIOD_ID IN (SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE  UNION SELECT MARKING_PERIOD_ID FROM school_semesters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE  UNION SELECT MARKING_PERIOD_ID FROM school_quarters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE )
			AND cp.SYEAR=\'' . UserSyear() . '\' AND cp.SCHOOL_ID=\'' . UserSchool() . '\' AND s.PROFILE=\'teacher\'
			' . (($_REQUEST['period']) ? ' AND cp.PERIOD_ID=\'' . $_REQUEST[period] . '\'' : '') . '
			AND NOT EXISTS (SELECT \'\' FROM grades_completed ac WHERE ac.STAFF_ID=cp.TEACHER_ID AND ac.MARKING_PERIOD_ID=\'' . $_REQUEST['mp'] . '\' AND ac.PERIOD_ID=sp.PERIOD_ID)
		';


        $courses_RET = DBGet(DBQuery('SELECT DISTINCT c.TITLE ,cp.COURSE_PERIOD_ID,cp.COURSE_ID,cp.TEACHER_ID AS STAFF_ID,cp.MARKING_PERIOD_ID AS MPI FROM schedule s,course_periods cp,courses c,attendance_calendar acc WHERE s.SYEAR=\'' . UserSyear() . '\' AND cp.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID AND (s.MARKING_PERIOD_ID IN (SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE  UNION SELECT MARKING_PERIOD_ID FROM school_semesters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE  UNION SELECT MARKING_PERIOD_ID FROM school_quarters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE )or s.MARKING_PERIOD_ID  is NULL)  AND (\'' . DBDate() . '\' BETWEEN s.START_DATE AND s.END_DATE OR \'' . DBDate() . '\'>=s.START_DATE AND s.END_DATE IS NULL) AND s.STUDENT_ID=' . UserStudentID() . (User('PROFILE') == 'teacher' ? ' AND cp.TEACHER_ID=\'' . User('STAFF_ID') . '\'' : '') . ' AND c.COURSE_ID=cp.COURSE_ID ORDER BY (SELECT SORT_ORDER FROM school_periods WHERE PERIOD_ID=cp.course_period_id)'));


        foreach ($courses_RET as $course) {
            $staff_id = $course['STAFF_ID'];

            $assignments_Graded = DBGet(DBQuery('SELECT gg.STUDENT_ID,ga.ASSIGNMENT_ID,gg.POINTS,gg.COMMENT,ga.TITLE,ga.DESCRIPTION,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS AS POINTS_POSSIBLE,at.TITLE AS CATEGORY
                                                   FROM gradebook_assignments ga LEFT OUTER JOIN gradebook_grades gg
                                                  ON (gg.COURSE_PERIOD_ID=\'' . $course['COURSE_PERIOD_ID'] . '\' AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.STUDENT_ID=\'' . UserStudentID() . '\'),gradebook_assignment_types at
                                                  WHERE (ga.COURSE_PERIOD_ID=\'' . $course['COURSE_PERIOD_ID'] . '\' OR ga.COURSE_ID=\'' . $course['COURSE_ID'] . '\' AND ga.STAFF_ID=\'' . $staff_id . '\') AND ga.MARKING_PERIOD_ID=\'' . UserMP() . '\'
                                                   AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND (gg.POINTS IS NOT NULL) AND (ga.POINTS!=\'0\' OR gg.POINTS IS NOT NULL AND gg.POINTS!=\'-1\') ORDER BY ga.ASSIGNMENT_ID DESC'));

            foreach ($assignments_Graded AS $assignments_Graded)
                $GRADED_ASSIGNMENT_ID[] = $assignments_Graded['ASSIGNMENT_ID'];
            $ASSIGNMENT_ID_GRADED = is_array($GRADED_ASSIGNMENT_ID) ? implode(",", $GRADED_ASSIGNMENT_ID) : '';

            $GRADED_ASSIGNMENT = '( ' . $ASSIGNMENT_ID_GRADED . ' )';


            $full_year_mp = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear()));
            $full_year_mp = $full_year_mp[1]['MARKING_PERIOD_ID'];

            if (count($assignments_Graded)) {
                $assignments_RET = DBGet(DBQuery('SELECT ga.ASSIGNMENT_ID,ga.TITLE,ga.DESCRIPTION as COMMENT,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS AS POINTS_POSSIBLE,at.TITLE AS CATEGORY FROM gradebook_assignments ga, gradebook_assignment_types at    WHERE ga.ASSIGNMENT_ID NOT IN ' . $GRADED_ASSIGNMENT . ' AND (ga.COURSE_PERIOD_ID=\'' . $course['COURSE_PERIOD_ID'] . '\' OR ga.COURSE_ID=\'' . $course['COURSE_ID'] . '\' AND ga.STAFF_ID=\'' . $staff_id . '\') AND (ga.MARKING_PERIOD_ID=\'' . UserMP() . '\'or ga.MARKING_PERIOD_ID=' . $full_year_mp . ')
                                                   AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND( CURRENT_DATE>=ga.ASSIGNED_DATE OR CURRENT_DATE<=ga.ASSIGNED_DATE)AND ga.DUE_DATE IS NOT NULL AND CURRENT_DATE<=ga.DUE_DATE
                                                   AND (ga.POINTS!=\'0\') ORDER BY ga.ASSIGNMENT_ID DESC'));
            } else {


                $assignments_RET = DBGet(DBQuery('SELECT ga.ASSIGNMENT_ID,ga.TITLE,ga.DESCRIPTION as COMMENT,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS AS POINTS_POSSIBLE,at.TITLE AS CATEGORY
                                                   FROM gradebook_assignments ga
                                                 ,gradebook_assignment_types at
                                                  WHERE (ga.COURSE_PERIOD_ID=\'' . $course['COURSE_PERIOD_ID'] . '\' OR ga.COURSE_ID=\'' . $course['COURSE_ID'] . '\' AND ga.STAFF_ID=\'' . $staff_id . '\') AND (ga.MARKING_PERIOD_ID=\'' . UserMP() . '\' or ga.MARKING_PERIOD_ID=' . $full_year_mp . ')
                                                   AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND( CURRENT_DATE>=ga.ASSIGNED_DATE OR CURRENT_DATE<=ga.ASSIGNED_DATE)AND ga.DUE_DATE IS NOT NULL AND CURRENT_DATE<=ga.DUE_DATE
                                                   AND (ga.POINTS!=\'0\') ORDER BY ga.ASSIGNMENT_ID DESC'));
            }

            if (count($assignments_RET)) {

                echo '<div class="panel panel-default">';
                $LO_columns = array('TITLE' =>_title,
                 'CATEGORY' =>_category,
                 'ASSIGNED_DATE' =>_assignedDate,
                 'DUE_DATE' =>_dueDate,
                 'COMMENT' =>_description,
                );

                $LO_ret = array(0 => array());

                foreach ($assignments_RET as $assignment) {
                    $LO_ret[] = array('TITLE' => $assignment['TITLE'], 'CATEGORY' => $assignment['CATEGORY'], 'ASSIGNED_DATE' => $assignment['ASSIGNED_DATE'], 'DUE_DATE' => $assignment['DUE_DATE'], 'COMMENT' => html_entity_decode(html_entity_decode($assignment['COMMENT'])));
                }
                DrawHeader(''._subject.' - ' . substr($course['TITLE'], strrpos(str_replace(' - ', ' ^ ', $course['TITLE']), '^')));

                unset($LO_ret[0]);

                ListOutput($LO_ret, $LO_columns,  _assignment, _assignments, array(), array(), array('center' =>false, 'save' => $_REQUEST['id'] != 'all', 'search' =>false));
                echo '</div>';
            }
        }


        break;
}

function _nl2br($value, $column) {
    return nl2br($value);
}

function makeDescription($value, $column) {
    return '<div style="width:450px;word-wrap:break-word;">' . $value . '</div>';
}

?>
