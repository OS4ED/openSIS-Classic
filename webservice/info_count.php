<?php
include '../Data.php';
include 'function/DbGetFnc.php';
include 'function/ParamLib.php';
include 'function/app_functions.php';
include 'function/function.php';

header('Content-Type: application/json');
$profile = $_REQUEST['profile'];
$staff_id = $_REQUEST['staff_id'];

$auth_data = check_auth();
if (count($auth_data) > 0) {
    if ($auth_data['user_id'] == $staff_id && $auth_data['user_profile'] == $profile) {
        $teacher_info['menu_permission'] = check_permission($_REQUEST['profile_id']);
        if ($profile == 'teacher') {
            $profile_id = 2;
        } elseif ($profile == 'student') {
            $profile_id = 3;
        } elseif ($profile == 'parent') {
            $profile_id = 4;
        } else {
            $profile_id = 0;
        }
        $teacher_info = array();

        if ($profile == 'teacher') {
//            $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
            //                $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
            //                $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
            //                $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
            //                $scr_path = explode('/',$_SERVER['SCRIPT_NAME']);
            //                $file_path = $scr_path[1];
            //
            //                $htpath=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port ."/".$file_path."/assets/userphotos/";
            //                $path ='../assets/userphotos/';
            $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
            $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
            $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
            $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":" . $_SERVER["SERVER_PORT"]);
            $scr_path = explode('/webservice/', $_SERVER['SCRIPT_NAME']);
            $file_path = $scr_path[0];

            $htpath = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port;
            if ($file_path != '') {
                $htpath = $htpath . "/" . $file_path;
            }

            $htpath = $htpath . "/assets/userphotos/";

            $path = '../assets/userphotos/';
            $PicPath = $path . $staff_id . ".JPG";
            if (file_exists($PicPath)) {
                $teacher_info['PHOTO'] = $htpath . $staff_id . ".JPG";
            } else {
                $teacher_info['PHOTO'] = "";
            }

# --------------------------- Seat Count Update Start ------------------------------------------ #

            $course_name = DBGet(DBQuery("SELECT DISTINCT(COURSE_PERIOD_ID)FROM schedule WHERE  END_DATE <'" . date("Y-m-d") . "' AND  DROPPED =  'N' "));

            foreach ($course_name as $column => $value) {
                $course_count = DBGet(DBQuery("SELECT *  FROM schedule WHERE  COURSE_PERIOD_ID='" . $value[COURSE_PERIOD_ID] . "' AND  END_DATE <'" . date("Y-m-d") . "'AND  DROPPED =  'N' "));
                for ($i = 1; $i <= count($course_count); $i++) {
                    DBQuery('CALL SEAT_FILL()');
                    DBQuery('UPDATE course_periods SET filled_seats=filled_seats-1 WHERE COURSE_PERIOD_ID IN (SELECT COURSE_PERIOD_ID FROM schedule WHERE end_date IS NOT NULL AND END_DATE  <\'' . date("Y-m-d") . '\' AND  DROPPED=\'N\' AND COURSE_PERIOD_ID=\'' . $value[COURSE_PERIOD_ID] . '\')');
                    DBQuery(' UPDATE schedule SET  DROPPED=\'Y\' WHERE END_DATE  IS NOT NULL AND COURSE_PERIOD_ID=\'' . $value[COURSE_PERIOD_ID] . '\' AND END_DATE  <\'' . date("Y-m-d") . '\'AND   DROPPED =  \'N\' AND  STUDENT_ID=\'' . $course_count[$i][STUDENT_ID] . '\'');
                }
            }

            # ---------------------------- Seat Count Update End ------------------------------------------- #
            $get_ac_st = 0;
            $get_tot_st = 0;
            $login_Check = DBGet(DBQuery('SELECT STAFF_ID,PROFILE_ID FROM staff WHERE STAFF_ID=' . $staff_id . ' AND PROFILE=\'' . $profile . '\''));

            if (count($login_Check) > 0) {
                $opensis_staff_access = DBGet(DBQuery('SELECT * FROM staff_school_info WHERE STAFF_ID=' . $login_Check[1]['STAFF_ID']));

                $get_details = DBGet(DBQuery("SELECT SYEAR,SCHOOL_ID FROM `school_years` WHERE SYEAR IN (SELECT MAX(SYEAR) FROM school_years GROUP BY SCHOOL_ID)"));
                foreach ($get_details as $gd_i => $gd_d) {
                    $get_stf_d = DBGet(DBQuery('SELECT COUNT(1) as INACTIVE FROM staff_school_relationship WHERE staff_id=\'' . $login_Check[1]['STAFF_ID'] . '\' AND SCHOOL_ID=\'' . $gd_d['SCHOOL_ID'] . '\' AND SYEAR=\'' . $gd_d['SYEAR'] . '\' AND END_DATE<\'' . date('Y-m-d') . '\' AND END_DATE!=\'0000-00-00\' '));
                    if ($get_stf_d[1]['INACTIVE'] > 0) {
                        $get_ac_st++;
                    }

                    $tot_stf_rec = DBGet(DBQuery('SELECT COUNT(1) as TOTAL FROM staff_school_relationship WHERE staff_id=\'' . $login_Check[1]['STAFF_ID'] . '\' AND SCHOOL_ID=\'' . $gd_d['SCHOOL_ID'] . '\' AND SYEAR=\'' . $gd_d['SYEAR'] . '\''));
                    if ($tot_stf_rec[1]['TOTAL'] > 0) {
                        $get_tot_st++;
                    }

                }

                if ($login_Check[1]['STAFF_ID'] != '' && $get_ac_st < $get_tot_st) {
                    $login_RET = DBGet(DBQuery("SELECT PROFILE,STAFF_ID,CURRENT_SCHOOL_ID,FIRST_NAME,LAST_NAME,EMAIL,s.PROFILE_ID,IS_DISABLE,MAX(ssr.SYEAR) AS SYEAR,s.GENDER
                                FROM staff s INNER JOIN staff_school_relationship ssr USING(staff_id),school_years sy
                                WHERE sy.school_id=s.current_school_id AND sy.syear=ssr.syear AND s.STAFF_ID=" . $staff_id));

                    if (count($login_RET) > 0) {
                        $teacher_info['SCHOOL_ID'] = $login_RET[1]['CURRENT_SCHOOL_ID'];
                        $teacher_info['SYEAR'] = $login_RET[1]['SYEAR'];
                        $RET = DBGet(DBQuery('SELECT s.ID,s.TITLE FROM schools s,staff st INNER JOIN staff_school_relationship ssr USING(staff_id) WHERE s.id=ssr.school_id AND ssr.syear=\'' . $login_RET[1][SYEAR] . '\' AND st.staff_id=\'' . $login_RET[1][STAFF_ID] . '\' AND (ssr.END_DATE>=curdate() OR ssr.END_DATE=\'0000-00-00\')'));
                        $i = 0;
                        $data = array();
                        foreach ($RET as $school) {
                            $data[$i]['id'] = $school['ID'];
                            $data[$i]['title'] = $school['TITLE'];
                            $i++;
                        }
                        $teacher_info['School_list'] = $data;

                        $i = 0;
                        $data = array();
                        $school_years_RET = DBGet(DBQuery("SELECT YEAR(sy.START_DATE)AS START_DATE,YEAR(sy.END_DATE)AS END_DATE FROM school_years sy,staff st INNER JOIN staff_school_relationship ssr USING(staff_id) WHERE ssr.SYEAR=sy.SYEAR AND sy.school_id=ssr.school_id AND sy.school_id=" . $login_RET[1][CURRENT_SCHOOL_ID] . " AND st.staff_id=" . $login_RET[1][STAFF_ID]));

                        foreach ($school_years_RET as $school_years) {
                            $data[$i]['start_date'] = $school_years[START_DATE];
                            $data[$i]['end_date'] = $school_years[END_DATE];
                            $data[$i]['title'] = $school_years[START_DATE] . ($school_years[END_DATE] != $school_years[START_DATE] ? "-" . $school_years['END_DATE'] : '');

                            $i++;
                        }
                        $teacher_info['Schoolyear_list'] = $data;

                        $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_quarters WHERE SCHOOL_ID='" . $login_RET[1][CURRENT_SCHOOL_ID] . "' AND SYEAR='" . $login_RET[1][SYEAR] . "' ORDER BY SORT_ORDER"));
                        if (!isset($teacher_info['UserMP'])) {
                            $teacher_info['UserMP'] = GetCurrentMPWs('QTR', date('Y-m-d'), $login_RET[1][SYEAR], $login_RET[1][CURRENT_SCHOOL_ID]);
                            $allMP = 'QTR';
                        }
                        if (!$RET) {
                            $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_semesters WHERE SCHOOL_ID='" . $login_RET[1][CURRENT_SCHOOL_ID] . "' AND SYEAR='" . $login_RET[1][SYEAR] . "' ORDER BY SORT_ORDER"));
                            if (!isset($teacher_info['UserMP'])) {
                                $teacher_info['UserMP'] = GetCurrentMPWs('SEM', date('Y-m-d'), $login_RET[1][SYEAR], $login_RET[1][CURRENT_SCHOOL_ID]);
                                $allMP = 'SEM';
                            }
                        }

                        if (!$RET) {
                            $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_years WHERE SCHOOL_ID='" . $login_RET[1][CURRENT_SCHOOL_ID] . "' AND SYEAR='" . $login_RET[1][SYEAR] . "' ORDER BY SORT_ORDER"));
                            if (!isset($teacher_info['UserMP'])) {
                                $teacher_info['UserMP'] = GetCurrentMPWs('FY', date('Y-m-d'), $login_RET[1][SYEAR], $login_RET[1][CURRENT_SCHOOL_ID]);
                                $allMP = 'FY';
                            }
                        }
                        if (count($RET)) {
                            if (!$teacher_info['UserMP']) {
                                $teacher_info['UserMP'] = $RET[1]['MARKING_PERIOD_ID'];
                            }

                            $i = 0;
                            $data = array();
                            foreach ($RET as $quarter) {
                                $data[$i]['id'] = $quarter['MARKING_PERIOD_ID'];
                                $data[$i]['title'] = $quarter['TITLE'];
                                $i++;
                            }
                        }
                        $teacher_info['marking_period_list'] = $data;
                        $teacher_info['marking_period_type'] = $allMP;

                        $sub = DBQuery("SELECT DISTINCT cs.TITLE, cs.SUBJECT_ID,cs.SCHOOL_ID FROM course_subjects as cs,course_details as cd WHERE cs.SUBJECT_ID=cd.SUBJECT_ID AND cd.SYEAR='" . $login_RET[1][SYEAR] . "' AND (cd.TEACHER_ID='" . $login_RET[1][STAFF_ID] . "' OR cd.SECONDARY_TEACHER_ID='" . $login_RET[1][STAFF_ID] . "') AND cs.SCHOOL_ID='" . $login_RET[1][CURRENT_SCHOOL_ID] . "' AND (cd.MARKING_PERIOD_ID IN (" . GetAllMPWs($allMP, $teacher_info['UserMP'], $login_RET[1][SYEAR], $login_RET[1][CURRENT_SCHOOL_ID]) . ") OR (cd.MARKING_PERIOD_ID IS NULL AND cd.BEGIN_DATE<='" . date('Y-m-d') . "' AND cd.END_DATE>='" . date('Y-m-d') . "'))");
                        $RET = DBGet($sub);

                        if (!$teacher_info['UserSubject']) {
                            $teacher_info['UserSubject'] = $RET[1]['SUBJECT_ID'];
                        }
                        $i = 0;
                        $data = array();
                        if (count($RET) > 0) {
                            foreach ($RET as $subject) {
                                $data[$i]['id'] = $subject['SUBJECT_ID'];
                                $data[$i]['title'] = $subject['TITLE'];
                                $i++;
                            }
                        } else {
                            $data[0]['id'] = '';
                            $data[0]['title'] = 'N/A';
                        }
                        $teacher_info['subject_list'] = $data;

                        $course = DBQuery("SELECT DISTINCT cd.COURSE_TITLE, cd.COURSE_ID,cd.SUBJECT_ID,cd.SCHOOL_ID FROM course_details cd WHERE (cd.TEACHER_ID='" . $login_RET[1][STAFF_ID] . "' OR cd.SECONDARY_TEACHER_ID='" . $login_RET[1][STAFF_ID] . "') AND cd.SYEAR='" . $login_RET[1][SYEAR] . "' AND cd.SCHOOL_ID='" . $login_RET[1][CURRENT_SCHOOL_ID] . "' AND cd.SUBJECT_ID='" . $teacher_info['UserSubject'] . "' AND (cd.MARKING_PERIOD_ID IN (" . GetAllMPWs($allMP, $teacher_info['UserMP'], $login_RET[1][SYEAR], $login_RET[1][CURRENT_SCHOOL_ID]) . ") OR (cd.MARKING_PERIOD_ID IS NULL AND cd.BEGIN_DATE<='" . date('Y-m-d') . "' AND cd.END_DATE>='" . date('Y-m-d') . "'))");
                        $RET = DBGet($course);
                        if (!$teacher_info['UserCourse']) {
                            $teacher_info['UserCourse'] = $RET[1]['COURSE_ID'];
                        }
                        $i = 0;
                        $data = array();
                        if (count($RET) > 0) {
                            foreach ($RET as $course) {
                                $data[$i]['id'] = $course['COURSE_ID'];
                                $data[$i]['title'] = $course['COURSE_TITLE'];
                                $i++;
                            }
                            $cpv_id = DBGet(DBQuery("SELECT cpv.ID FROM course_periods cp,course_period_var cpv WHERE cp.SYEAR='" . $login_RET[1][SYEAR] . "' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.SCHOOL_ID='" . $login_RET[1][CURRENT_SCHOOL_ID] . "' AND cp.COURSE_ID='" . $teacher_info['UserCourse'] . "' AND (TEACHER_ID='" . $login_RET[1][STAFF_ID] . "' OR SECONDARY_TEACHER_ID='" . $login_RET[1][STAFF_ID] . "') AND (MARKING_PERIOD_ID IN (" . GetAllMPWs($allMP, $teacher_info['UserMP'], $login_RET[1][SYEAR], $login_RET[1][CURRENT_SCHOOL_ID]) . ") OR (MARKING_PERIOD_ID IS NULL AND BEGIN_DATE<='" . date('Y-m-d') . "' AND END_DATE>='" . date('Y-m-d') . "')) LIMIT 0,1"));
                        } else {
                            $data[0]['id'] = '';
                            $data[0]['title'] = 'N/A';
                        }
                        $teacher_info['course_list'] = $data;

                        $QI = DBQuery("SELECT cpv.ID,cp.COURSE_PERIOD_ID,cp.COURSE_ID,cp.TITLE,cp.SCHOOL_ID,cpv.PERIOD_ID FROM course_periods cp,course_period_var cpv WHERE cp.SYEAR='" . $login_RET[1][SYEAR] . "' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.SCHOOL_ID='" . $login_RET[1][CURRENT_SCHOOL_ID] . "' AND cp.COURSE_ID='" . $teacher_info['UserCourse'] . "' AND (TEACHER_ID='" . $login_RET[1][STAFF_ID] . "' OR SECONDARY_TEACHER_ID='" . $login_RET[1][STAFF_ID] . "') AND (MARKING_PERIOD_ID IN (" . GetAllMPWs($allMP, $teacher_info['UserMP'], $login_RET[1][SYEAR], $login_RET[1][CURRENT_SCHOOL_ID]) . ") OR (MARKING_PERIOD_ID IS NULL AND BEGIN_DATE<='" . date('Y-m-d') . "' AND END_DATE>='" . date('Y-m-d') . "')) group by (cp.COURSE_PERIOD_ID)");
                        $RET = DBGet($QI);

                        $fy_id = DBGet(DBQuery("SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR='" . $login_RET[1][SYEAR] . "' AND SCHOOL_ID='" . $login_RET[1][CURRENT_SCHOOL_ID] . "'"));
                        $fy_id = $fy_id[1]['MARKING_PERIOD_ID'];

                        if (!$teacher_info['UserCoursePeriod']) {
                            $teacher_info['UserCoursePeriod'] = $RET[1]['COURSE_PERIOD_ID'];
                            $teacher_info['UserCoursePeriodVar'] = $RET[1]['ID'];
                        }

                        $i = 0;
                        $data = array();

                        if (count($RET) > 0) {
                            foreach ($RET as $period) {

                                $period_det = DBGet(DBQuery('SELECT sp.TITLE as PERIOD_NAME,cpv.DAYS,cpv.COURSE_PERIOD_DATE FROM course_period_var cpv,school_periods sp WHERE cpv.ID=' . $period['ID'] . ' AND cpv.PERIOD_ID=sp.PERIOD_ID'));
                                $period_det = $period_det[1];
                                $days_arr = array("Monday" => 'M', "Tuesday" => 'T', "Wednesday" => 'W', "Thursday" => 'H', "Friday" => 'F', "Saturday" => 'S', "Sunday" => 'U');
                                if ($period_det['DAYS'] == '') {
                                    $period_det['DAYS'] = date('l', strtotime($period_det['COURSE_PERIOD_DATE']));
                                    $period_det['DAYS'] = $days_arr[$period_det['DAYS']];
                                }
                                $data[$i]['cp_id'] = $period['COURSE_PERIOD_ID'];
                                $data[$i]['cpv_id'] = $period['ID'];
                                $data[$i]['period_id'] = $period['ID'];
                                $data[$i]['title'] = $period['TITLE'] . " - " . $period_det['PERIOD_NAME'];
                                $i++;
                            }
                        } else {
                            $data[0]['cp_id'] = '';
                            $data[0]['cpv_id'] = '';
                            $data[0]['period_id'] = '';
                            $data[0]['title'] = 'N/A';
                        }
                        $teacher_info['course_period_list'] = $data;
                        $ma_count = 0;
                        $att_qry = DBGet(DBQuery('SELECT Count(1) as count FROM  profile_exceptions WHERE MODNAME
                                        IN (\'attendance/TakeAttendance.php\',\'attendance/DailySummary.php\',\'attendance/StudentSummary\') AND
                                        PROFILE_ID=' . $login_RET[1][PROFILE_ID] . ' AND CAN_USE=\'Y\' '));

                        $reassign_cp = DBGet(DBQuery('SELECT COURSE_PERIOD_ID ,TEACHER_ID,PRE_TEACHER_ID,ASSIGN_DATE FROM teacher_reassignment WHERE ASSIGN_DATE <= \'' . date('Y-m-d') . '\' AND UPDATED=\'N\' '));
                        foreach ($reassign_cp as $re_key => $reassign_cp_value) {
                            if (strtotime($reassign_cp_value['ASSIGN_DATE']) <= strtotime(date('Y-m-d'))) {
                                $get_pname = DBGet(DBQuery("SELECT CONCAT(sp.title,IF(cp.mp!='FY',CONCAT(' - ',mp.short_name),' '),IF(CHAR_LENGTH(cpv.days)<5,CONCAT(' - ',cpv.days),' '),' - ',cp.short_name,' - ',CONCAT_WS(' ',st.first_name,st.middle_name,st.last_name)) AS CP_NAME FROM course_periods cp,course_period_var cpv,school_periods sp,marking_periods mp,staff st WHERE cpv.period_id=sp.period_id and cp.marking_period_id=mp.marking_period_id and st.staff_id=" . $reassign_cp_value['TEACHER_ID'] . "  AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=" . $reassign_cp_value['COURSE_PERIOD_ID']));
                                $get_pname = $get_pname[1]['CP_NAME'];
                                DBQuery('UPDATE course_periods SET title=\'' . $get_pname . '\', teacher_id=' . $reassign_cp_value['TEACHER_ID'] . ' WHERE COURSE_PERIOD_ID=' . $reassign_cp_value['COURSE_PERIOD_ID']);
                                DBQuery('UPDATE teacher_reassignment SET updated=\'Y\' WHERE assign_date <=CURDATE() AND updated=\'N\' AND COURSE_PERIOD_ID=' . $reassign_cp_value['COURSE_PERIOD_ID']);
                                DBQuery('UPDATE missing_attendance SET TEACHER_ID=' . $reassign_cp_value['TEACHER_ID'] . ' WHERE TEACHER_ID=' . $reassign_cp_value['PRE_TEACHER_ID'] . ' AND COURSE_PERIOD_ID=' . $reassign_cp_value['COURSE_PERIOD_ID']);
                            }

                        }
                        $schedule_exit = DBGet(DBQuery('SELECT ID FROM schedule WHERE syear=\'' . $login_RET[1][SYEAR] . '\' AND school_id=\'' . $login_RET[1][CURRENT_SCHOOL_ID] . '\' LIMIT 0,1'));
                        if ($schedule_exit[1]['ID'] != '') {
                            $last_update = DBGet(DBQuery('SELECT VALUE FROM program_config WHERE PROGRAM=\'MissingAttendance\' AND TITLE=\'LAST_UPDATE\' AND SYEAR=\'' . $login_RET[1][SYEAR] . '\' AND SCHOOL_ID=\'' . $login_RET[1][CURRENT_SCHOOL_ID] . '\''));
                            if ($last_update[1]['VALUE'] != '') {
                                if ($last_update[1]['VALUE'] < date('Y-m-d')) {
//                                                  echo '<script type=text/javascript>calculate_missing_atten();</script>';
                                    $syear = $login_RET[1][SYEAR];
                                    $flag = false;
                                    $RET = DBGet(DBQuery('SELECT SCHOOL_ID,SCHOOL_DATE,COURSE_PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID FROM missing_attendance WHERE SYEAR=\'' . $login_RET[1][SYEAR] . '\' AND SCHOOL_ID=\'' . $login_RET[1][CURRENT_SCHOOL_ID] . '\' LIMIT 0,1'));
                                    if (count($RET)) {
                                        $flag = true;
                                    }
                                    $last_update = DBGet(DBQuery('SELECT VALUE FROM program_config WHERE PROGRAM=\'MissingAttendance\' AND TITLE=\'LAST_UPDATE\' AND SYEAR=\'' . $login_RET[1][SYEAR] . '\' AND SCHOOL_ID=\'' . $login_RET[1][CURRENT_SCHOOL_ID] . '\''));
                                    $last_update = trim($last_update[1]['VALUE']);
                                    DBQuery("INSERT INTO missing_attendance(SCHOOL_ID,SYEAR,SCHOOL_DATE,COURSE_PERIOD_ID,PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID)
                                                            SELECT s.ID AS SCHOOL_ID,acc.SYEAR,acc.SCHOOL_DATE,cp.COURSE_PERIOD_ID,cpv.PERIOD_ID, IF(tra.course_period_id=cp.course_period_id AND acc.school_date<tra.assign_date =true,tra.pre_teacher_id,cp.teacher_id) AS TEACHER_ID,
                                                            cp.SECONDARY_TEACHER_ID FROM attendance_calendar acc INNER JOIN course_periods cp ON cp.CALENDAR_ID=acc.CALENDAR_ID INNER JOIN course_period_var cpv ON cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID
                                                            AND (cpv.COURSE_PERIOD_DATE IS NULL AND position(substring('UMTWHFS' FROM DAYOFWEEK(acc.SCHOOL_DATE) FOR 1) IN cpv.DAYS)>0 OR cpv.COURSE_PERIOD_DATE IS NOT NULL AND cpv.COURSE_PERIOD_DATE=acc.SCHOOL_DATE)
                                                            INNER JOIN schools s ON s.ID=acc.SCHOOL_ID LEFT JOIN teacher_reassignment tra ON (cp.course_period_id=tra.course_period_id) INNER JOIN schedule sch ON sch.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID
                                                            AND sch.student_id IN(SELECT student_id FROM student_enrollment se WHERE sch.school_id=se.school_id AND sch.syear=se.syear AND start_date<=acc.school_date AND (end_date IS NULL OR end_date>=acc.school_date))
                                                            AND (cp.MARKING_PERIOD_ID IN (SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE UNION SELECT MARKING_PERIOD_ID FROM school_semesters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE UNION SELECT MARKING_PERIOD_ID FROM school_quarters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE) OR acc.school_date BETWEEN cp.begin_date AND cp.end_date)
                                                            AND sch.START_DATE<=acc.SCHOOL_DATE AND (sch.END_DATE IS NULL OR sch.END_DATE>=acc.SCHOOL_DATE ) AND cpv.DOES_ATTENDANCE='Y' AND acc.SCHOOL_DATE<=CURDATE() AND acc.SCHOOL_DATE > '" . $last_update . "' AND acc.syear=$syear AND acc.SCHOOL_ID='" . $login_RET[1][CURRENT_SCHOOL_ID] . "'
                                                            AND NOT EXISTS (SELECT '' FROM  attendance_completed ac WHERE ac.SCHOOL_DATE=acc.SCHOOL_DATE AND ac.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND ac.PERIOD_ID=cpv.PERIOD_ID)  AND isDateInMarkingPeriodWorkingDates(cp.marking_period_id, acc.SCHOOL_DATE)
                                                            GROUP BY acc.SCHOOL_DATE,cp.COURSE_PERIOD_ID,cpv.PERIOD_ID");

                                    DBQuery("UPDATE program_config SET VALUE=CURDATE() WHERE PROGRAM='MissingAttendance' AND TITLE='LAST_UPDATE'");

                                    $RET = DBGet(DBQuery("SELECT SCHOOL_ID,SCHOOL_DATE,COURSE_PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID FROM missing_attendance WHERE SYEAR='" . $login_RET[1][SYEAR] . "' LIMIT 0,1"));
                                }
                            }
                        }
                        $notes_RET = DBGet(DBQuery('SELECT IF(pn.school_id IS NULL,\'All School\',(SELECT TITLE FROM schools WHERE id=pn.school_id)) AS SCHOOL,pn.LAST_UPDATED,CONCAT(\'<b>\',pn.TITLE,\'</b>\') AS TITLE,pn.CONTENT
                                                  FROM portal_notes pn
                                                  WHERE pn.SYEAR=\'' . $login_RET[1][SYEAR] . '\' AND pn.START_DATE<=CURRENT_DATE AND
                                                      (pn.END_DATE>=CURRENT_DATE OR pn.END_DATE IS NULL)
                                                      AND (pn.school_id IS NULL OR pn.school_id IN(' . GetUserSchoolsWs($login_RET[1][STAFF_ID], $login_RET[1][SYEAR], $login_RET[1][PROFILE_ID], true) . '))
                                                      AND (' . ($login_RET[1][PROFILE_ID] == '' ? ' FIND_IN_SET(\'teacher\', pn.PUBLISHED_PROFILES)>0' : ' FIND_IN_SET(' . $login_RET[1][PROFILE_ID] . ',pn.PUBLISHED_PROFILES)>0)') . '
                                                      ORDER BY pn.SORT_ORDER,pn.LAST_UPDATED DESC'), array('LAST_UPDATED' => 'ProperDate', 'CONTENT' => '_nl2br'));

                        $events_RET = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL
                                      FROM calendar_events ce,calendar_events_visibility cev,schools s
                                      WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY
                                          AND ce.SYEAR=\'' . $login_RET[1][SYEAR] . '\'
                                          AND ce.school_id IN(' . GetUserSchoolsWs($login_RET[1][STAFF_ID], $login_RET[1][SYEAR], $login_RET[1][PROFILE_ID], true) . ')
                                          AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=cev.CALENDAR_ID
                                          AND ' . ($login_RET[1][PROFILE_ID] == '' ? 'cev.PROFILE=\'teacher\'' : 'cev.PROFILE_ID=' . $login_RET[1][PROFILE_ID]) . '
                                          ORDER BY ce.SCHOOL_DATE,s.TITLE'), array('SCHOOL_DATE' => 'ProperDate', 'DESCRIPTION' => 'makeDescription'));
                        $events_RET1 = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL
                                      FROM calendar_events ce,schools s
                                      WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY
                                          AND ce.SYEAR=\'' . $login_RET[1][SYEAR] . '\'
                                          AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=0 ORDER BY ce.SCHOOL_DATE,s.TITLE'), array('SCHOOL_DATE' => 'ProperDate', 'DESCRIPTION' => 'makeDescription'));
                        $event_count = count($events_RET) + 1;
                        foreach ($events_RET1 as $events_RET_key => $events_RET_value) {
                            $events_RET[$event_count] = $events_RET_value;
                            $event_count++;
                        }
                        if (count($events_RET) > 0) {
                            foreach ($events_RET as $event) {
                                $events_data[] = $event;
                            }
                        } else {
                            $events_data = array();
                        }
                        $RET = DBGet(DBQuery('SELECT DISTINCT s.TITLE AS SCHOOL,mi.SCHOOL_DATE,cp.TITLE AS TITLE,mi.COURSE_PERIOD_ID,mi.PERIOD_ID,cpv.ID AS CPV_ID
                          FROM missing_attendance mi,schools s,course_periods cp,course_period_var cpv WHERE s.ID=mi.SCHOOL_ID AND  cp.COURSE_PERIOD_ID=mi.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND (mi.TEACHER_ID=\'' . $login_RET[1][STAFF_ID] . '\' OR mi.SECONDARY_TEACHER_ID=\'' . $login_RET[1][STAFF_ID] . '\' ) AND mi.SCHOOL_ID=\'' . $login_RET[1][CURRENT_SCHOOL_ID] . '\' AND mi.SYEAR=\'' . $login_RET[1][SYEAR] . '\' AND mi.SCHOOL_DATE < \'' . date('Y-m-d') . '\' AND (mi.SCHOOL_DATE=cpv.COURSE_PERIOD_DATE OR POSITION(IF(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\') LIKE \'Thu\',\'H\',(IF(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\') LIKE \'Sun\',\'U\',SUBSTR(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\'),1,1)))) IN cpv.DAYS)>0) ORDER BY cp.TITLE,mi.SCHOOL_DATE '), array('SCHOOL_DATE' => 'ProperDate'));
                        $codes_RET_count = DBGet(DBQuery('SELECT COUNT(*) AS CODES FROM attendance_codes WHERE SCHOOL_ID=\'' . $login_RET[1][CURRENT_SCHOOL_ID] . '\' AND SYEAR=\'' . $login_RET[1][SYEAR] . '\'  AND TYPE=\'teacher\' AND TABLE_NAME=\'0\' ORDER BY SORT_ORDER'));
                        $ma_count = count($RET);
                        $link = array();
                        $id = array();
                        $arr = array();
                        $qr = "select to_user,mail_id,to_cc,to_bcc from msg_inbox where (isdraft=0 OR isdraft IS NULL)";
                        $fetch = DBGet(DBQuery($qr));
                        foreach ($fetch as $key => $value) {
                            $s = $value['TO_USER'];
                            $cc = $value['TO_CC'];
                            $bcc = $value['TO_BCC'];

                            $arr = explode(',', $s);
                            $arr_cc = explode(',', $cc);
                            $arr_bcc = explode(',', $bcc);

                            if (in_array($login_RET[1][USERNAME], $arr) || in_array($login_RET[1][USERNAME], $arr_cc) || in_array($login_RET[1][USERNAME], $arr_bcc)) {
                                array_push($id, $value['MAIL_ID']);
                            } else {

                            }
                        }
                        $count = count($id);
                        if ($count > 0) {
                            $to_user_id = implode(',', $id);
                        } else {
                            $to_user_id = 'null';
                        }

                        $inbox = "select * from msg_inbox where mail_id in($to_user_id) order by(mail_id)desc";
                        $inbox_info = DBGet(DBQuery($inbox));

                        foreach ($inbox_info as $key => $value) {
                            if ($value['MAIL_READ_UNREAD'] == '') {
                                $inbox_info[$key]['MAIL_SUBJECT'] = $inbox_info[$key]['MAIL_SUBJECT'];
                            }
                            if ($value['MAIL_READ_UNREAD'] != '') {
                                $read_user = explode(',', $value['MAIL_READ_UNREAD']);
                                if (!in_array($login_RET[1][USERNAME], $read_user)) {
                                    array_push($key, $value['MAIL_ID']);
                                    $inbox_info[$key]['MAIL_SUBJECT'] = $inbox_info[$key]['MAIL_SUBJECT'];
                                }
                            }
                            if ($value['MAIL_ATTACHMENT'] != '') {
                                $inbox_info[$key]['MAIL_SUBJECT'] = $inbox_info[$key]['MAIL_SUBJECT'];
                            }
                        }

                        $msg_count = 0;
                        foreach ($inbox_info as $mail) {
                            $read_unread = explode(',', $mail['MAIL_READ_UNREAD']);
                            if (!in_array($login_RET[1][USERNAME], $read_unread)) {
                                $msg_count++;
                            }
                        }
                        // -------------------------------------- Dynamic Menu -------------------------------------------//

//                        $openSISModules = array(
                        //                            'schoolsetup'=>true,
                        //                            'students'=>true,
                        //                            'users'=>true,
                        //                            'scheduling'=>true,
                        //                            'grades'=>true,
                        //                            'attendance'=>true,
                        //                            'eligibility'=>true,
                        //                            'Discipline'=>true,
                        //                            'Billing' =>true,
                        //                            'EasyCom' =>true,
                        //                            'Library' =>true,
                        //                            'messaging'=>true,
                        //                            'tools'=>true,
                        //                        );
                        //                        foreach($openSISModules as $module=>$include)
                        //                        {
                        //                            if($include)
                        //                                include "../modules/$module/Menu.php";
                        //                        }
                        //                        $profile = $login_RET[1]['PROFILE'];
                        //
                        //                        if($profile!='student')
                        //                        {
                        //                            if($login_RET[1]['PROFILE_ID']!='')
                        //                                    $can_use_RET = DBGet(DBQuery("SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID='".$login_RET[1][PROFILE_ID]."' AND CAN_USE='Y'"),array(),array('MODNAME'));
                        //                            else
                        //                            {
                        //                                $profile_id_mod=DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE USER_ID='".$login_RET[1]['STAFF_ID']));
                        //                                $profile_id_mod=$profile_id_mod[1]['PROFILE_ID'];
                        //                                if($profile_id_mod!='')
                        //                                $can_use_RET = DBGet(DBQuery("SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID='".$profile_id_mod."' AND CAN_USE='Y'"),array(),array('MODNAME'));
                        //                            }
                        //
                        //                        }
                        //                        else
                        //                        {
                        //                                $can_use_RET = DBGet(DBQuery("SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID='3' AND CAN_USE='Y'"),array(),array('MODNAME'));
                        //                                $profile = 'parent';
                        //                        }
                        //
                        //                        foreach($menu as $modcat=>$profiles)
                        //                        {
                        //                            $menuprof = $menu;
                        //                            $programs = $profiles[$profile];
                        //                            foreach($programs as $program=>$title)
                        //                            {
                        //                                if(!is_numeric($program))
                        //                                {
                        //                                    if($can_use_RET[$program] && ($profile!='admin' || !$exceptions[$modcat][$program]))  // || AllowEdit($program)
                        //                                        $_openSIS['Menu'][$modcat][] = $title; //[$program]
                        //                                }
                        //                                else
                        //                                {
                        //                                    $_openSIS['Menu'][$modcat][$program] = $title;
                        //                                }
                        //                            }
                        //                        }
                        //
                        //                        if($profile=='student')
                        //                                unset($_openSIS['Menu']['users']);
                        // -------------------------------------- Dynamic Menu -------------------------------------------//
                        $teacher_info['tech_info'][0] = $login_RET[1];
                        $teacher_info['notification_count'] = (count($events_data) + count($notes_RET));
                        $teacher_info['message_count'] = $msg_count;
                        $teacher_info['missing_attendance_count'] = $ma_count;
                        $teacher_info['dynamic_menu'] = $_openSIS['Menu'];
                        $teacher_info['success'] = 1;
                        $teacher_info['err_msg'] = 'nil';
                        $teacher_info['type'] = $login_Check[1]['PROFILE_ID'];
                    }
                } else {
                    if ($get_ac_st == $get_tot_st && $get_tot_st != 0) {
                        $error = "Your opensis account is inactive.";
                    } else {
                        $error = "Incorrect username or password. Please try again.";
                    }

                    $teacher_info['PHOTO'] = "";
                    $teacher_info['success'] = 0;
                    $teacher_info['notification_count'] = 0;
                    $teacher_info['message_count'] = 0;
                    $teacher_info['missing_attendance_count'] = 0;
                    $teacher_info['dynamic_menu'] = '';
                    $teacher_info['SCHOOL_ID'] = '';
                    $teacher_info['SYEAR'] = '';
                    $teacher_info['School_list'] = '';
                    $teacher_info['Schoolyear_list'] = '';
                    $teacher_info['UserMP'] = '';
                    $teacher_info['marking_period_list'] = '';
                    $teacher_info['UserSubject'] = '';
                    $teacher_info['subject_list'] = '';
                    $teacher_info['UserCourse'] = '';
                    $teacher_info['course_list'] = '';
                    $teacher_info['UserCoursePeriod'] = '';
                    $teacher_info['UserCoursePeriodVar'] = '';
                    $teacher_info['course_period_list'] = '';
                    $teacher_info['err_msg'] = $error;
                    $teacher_info['type'] = $login_uniform['PROFILE_ID'];
                }

            } else {
                $error = "User doesnot exist or have some other profile.";
                $teacher_info['success'] = 0;
                $teacher_info['PHOTO'] = "";
                $teacher_info['notification_count'] = 0;
                $teacher_info['message_count'] = 0;
                $teacher_info['missing_attendance_count'] = 0;
                $teacher_info['dynamic_menu'] = '';
                $teacher_info['SCHOOL_ID'] = '';
                $teacher_info['SYEAR'] = '';
                $teacher_info['School_list'] = '';
                $teacher_info['Schoolyear_list'] = '';
                $teacher_info['UserMP'] = '';
                $teacher_info['marking_period_list'] = '';
                $teacher_info['UserSubject'] = '';
                $teacher_info['subject_list'] = '';
                $teacher_info['UserCourse'] = '';
                $teacher_info['course_list'] = '';
                $teacher_info['UserCoursePeriod'] = '';
                $teacher_info['UserCoursePeriodVar'] = '';
                $teacher_info['course_period_list'] = '';
                //$teacher_info['err_msg']='This user is not a '.$profile.'.';
                $teacher_info['type'] = $login_uniform['PROFILE_ID'];
                $teacher_info['err_msg'] = $error;
            }
        } elseif ($profile == 'student') {
            $login_uniform = DBGet(DBQuery('SELECT * FROM login_authentication  WHERE USER_ID=\'' . $staff_id . '\' AND PROFILE_ID=\'' . $_REQUEST['profile_id'] . '\''));
            $login_uniform = $login_uniform[1];
//            $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
            //                $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
            //                $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
            //                $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
            //                $scr_path = explode('/',$_SERVER['SCRIPT_NAME']);
            //                $file_path = $scr_path[1];
            //
            //                $htpath=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port ."/".$file_path."/assets/studentphotos/";
            //                $path ='../assets/studentphotos/';
            $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
            $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
            $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
            $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":" . $_SERVER["SERVER_PORT"]);
            $scr_path = explode('/webservice/', $_SERVER['SCRIPT_NAME']);
            $file_path = $scr_path[0];

            $htpath = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port;
            if ($file_path != '') {
                $htpath = $htpath . "/" . $file_path;
            }

            $htpath = $htpath . "/assets/studentphotos/";

            $path = '../assets/studentphotos/';
            if ($login_uniform['PROFILE_ID'] == $_REQUEST['profile_id']) {
                $school_years_data = array();
                $student_Check = DBGet(DBQuery("SELECT s.FIRST_NAME,s.LAST_NAME,s.GENDER,s.EMAIL,e.SYEAR,e.SCHOOL_ID FROM students s, student_enrollment e WHERE s.STUDENT_ID = e.STUDENT_ID AND s.STUDENT_ID=" . $login_uniform['USER_ID']));
                $student_Check = $student_Check[1];
                $_SESSION['UserSchool'] = $student_Check['SCHOOL_ID'];
                $school_years_RET1 = DBGet(DBQuery("SELECT START_DATE,END_DATE FROM school_years WHERE SCHOOL_ID=" . UserSchool()));
                $school_years_RET1 = $school_years_RET1[1];
                $school_years_RET1['START_DATE'] = explode("-", $school_years_RET1['START_DATE']);
                $school_years_RET1['START_DATE'] = $school_years_RET1['START_DATE'][0];
                $school_years_RET1['END_DATE'] = explode("-", $school_years_RET1['END_DATE']);
                $school_years_RET1['END_DATE'] = $school_years_RET1['END_DATE'][0];
                $i = $s = $m = 0;
                if ($school_years_RET1['END_DATE'] > $school_years_RET1['START_DATE']) {
                    $school_years_RET = DBGet(DBQuery("SELECT DISTINCT sy.START_DATE,sy.END_DATE FROM school_years sy,student_enrollment se WHERE se.SYEAR=sy.SYEAR AND se.STUDENT_ID='" . $login_uniform['USER_ID'] . "' AND sy.SCHOOL_ID=" . UserSchool() . " "));
                    $i = 0;
                    foreach ($school_years_RET as $school_years) {
                        $school_years['START_DATE'] = explode("-", $school_years['START_DATE']);
                        $school_years['START_DATE'] = $school_years['START_DATE'][0];
                        $school_years['END_DATE'] = explode("-", $school_years['END_DATE']);
                        $school_years['END_DATE'] = $school_years['END_DATE'][0];
                        $school_years_data[$i]['ID'] = $school_years[START_DATE];
                        $school_years_data[$i]['VALUE'] = $school_years[START_DATE] . "-" . $school_years['END_DATE'];
                        $i++;
                    }
                } elseif ($school_years_RET1['END_DATE'] == $school_years_RET1['START_DATE']) {
                    $school_years_RET = DBGet(DBQuery("SELECT DISTINCT sy.START_DATE,sy.END_DATE FROM school_years sy,student_enrollment se WHERE se.SYEAR=sy.SYEAR AND se.STUDENT_ID='" . $login_uniform['USER_ID'] . "' AND sy.SCHOOL_ID=" . UserSchool() . " "));
                    $i = 0;
                    foreach ($school_years_RET as $school_years) {
                        $school_years['START_DATE'] = explode("-", $school_years['START_DATE']);
                        $school_years_RET['START_DATE'] = $school_years['START_DATE'][0];

                        $school_years_data[$i]['VALUE'] = $school_years_RET['START_DATE'];
                        $school_years_data[$i]['TITLE'] = $school_years_RET['START_DATE'];
                        $i++;
                    }

                }

                $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_quarters WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . $student_Check['SYEAR'] . "' ORDER BY SORT_ORDER"));
                if (!isset($teacher_info['UserMP'])) {
                    $teacher_info['UserMP'] = GetCurrentMPWs('QTR', date('Y-m-d'), $student_Check['SYEAR'], UserSchool());
                    $allMP = 'QTR';
                }
                if (!$RET) {
                    $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_semesters WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . $student_Check['SYEAR'] . "' ORDER BY SORT_ORDER"));
                    if (!isset($teacher_info['UserMP'])) {
                        $teacher_info['UserMP'] = GetCurrentMPWs('SEM', date('Y-m-d'), $student_Check['SYEAR'], UserSchool());
                        $allMP = 'SEM';
                    }
                }

                if (!$RET) {
                    $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_years WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . $student_Check['SYEAR'] . "' ORDER BY SORT_ORDER"));
                    if (!isset($teacher_info['UserMP'])) {
                        $teacher_info['UserMP'] = GetCurrentMPWs('FY', date('Y-m-d'), $student_Check['SYEAR'], UserSchool());
                        $allMP = 'FY';
                    }
                }
                if (count($RET)) {
                    if (!$teacher_info['UserMP']) {
                        $teacher_info['UserMP'] = $RET[1]['MARKING_PERIOD_ID'];
                    }

                    $i = 0;
                    $data = array();
                    foreach ($RET as $quarter) {
                        $data[$i]['id'] = $quarter['MARKING_PERIOD_ID'];
                        $data[$i]['title'] = $quarter['TITLE'];
                        $i++;
                    }
                }
                $notes_RET = DBGet(DBQuery('SELECT IF(pn.school_id IS NULL,\'All School\',(SELECT TITLE FROM schools WHERE id=pn.school_id)) AS SCHOOL,pn.LAST_UPDATED,CONCAT(\'<b>\',pn.TITLE,\'</b>\') AS TITLE,pn.CONTENT
                                FROM portal_notes pn
                                WHERE pn.SYEAR=\'' . $student_Check['SYEAR'] . '\' AND pn.START_DATE<=CURRENT_DATE AND
                                    (pn.END_DATE>=CURRENT_DATE OR pn.END_DATE IS NULL)
                                    AND (pn.school_id IS NULL OR pn.school_id IN(' . $student_Check['SCHOOL_ID'] . '))
                                    AND (' . ($login_uniform['PROFILE_ID'] == '' ? ' FIND_IN_SET(\'teacher\', pn.PUBLISHED_PROFILES)>0' : ' FIND_IN_SET(' . $login_uniform['PROFILE_ID'] . ',pn.PUBLISHED_PROFILES)>0)') . '
                                    ORDER BY pn.SORT_ORDER,pn.LAST_UPDATED DESC'), array('LAST_UPDATED' => 'ProperDate', 'CONTENT' => '_nl2br'));

                $events_RET = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL
                    FROM calendar_events ce,calendar_events_visibility cev,schools s
                    WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY
                        AND ce.SYEAR=\'' . $student_Check['SYEAR'] . '\'
                        AND ce.school_id IN(' . $student_Check['SCHOOL_ID'] . ')
                        AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=cev.CALENDAR_ID
                        AND ' . ($login_uniform['PROFILE_ID'] == '' ? 'cev.PROFILE=\'teacher\'' : 'cev.PROFILE_ID=' . $login_uniform['PROFILE_ID']) . '
                        ORDER BY ce.SCHOOL_DATE,s.TITLE'), array('SCHOOL_DATE' => 'ProperDate', 'DESCRIPTION' => 'makeDescription'));
                $events_RET1 = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL
                    FROM calendar_events ce,schools s
                    WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY
                        AND ce.SYEAR=\'' . $student_Check['SYEAR'] . '\'
                        AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=0 ORDER BY ce.SCHOOL_DATE,s.TITLE'), array('SCHOOL_DATE' => 'ProperDate', 'DESCRIPTION' => 'makeDescription'));
                $event_count = count($events_RET) + 1;
                foreach ($events_RET1 as $events_RET_key => $events_RET_value) {
                    $events_RET[$event_count] = $events_RET_value;
                    $event_count++;
                }
                if (count($events_RET) > 0) {
                    foreach ($events_RET as $event) {
                        $events_data[] = $event;
                    }
                } else {
                    $events_data = array();
                }

                $link = array();
                $id = array();
                $arr = array();
                $qr = "select to_user,mail_id,to_cc,to_bcc from msg_inbox where (isdraft=0 OR isdraft IS NULL)";
                $fetch = DBGet(DBQuery($qr));
                foreach ($fetch as $key => $value) {
                    $s = $value['TO_USER'];
                    $cc = $value['TO_CC'];
                    $bcc = $value['TO_BCC'];

                    $arr = explode(',', $s);
                    $arr_cc = explode(',', $cc);
                    $arr_bcc = explode(',', $bcc);

                    if (in_array($login_uniform['USERNAME'], $arr) || in_array($login_uniform['USERNAME'], $arr_cc) || in_array($login_uniform['USERNAME'], $arr_bcc)) {
                        array_push($id, $value['MAIL_ID']);
                    } else {

                    }
                }
                $count = count($id);
                if ($count > 0) {
                    $to_user_id = implode(',', $id);
                } else {
                    $to_user_id = 'null';
                }

                $inbox = "select * from msg_inbox where mail_id in($to_user_id) order by(mail_id)desc";
                $inbox_info = DBGet(DBQuery($inbox));

                foreach ($inbox_info as $key => $value) {
                    if ($value['MAIL_READ_UNREAD'] == '') {
                        $inbox_info[$key]['MAIL_SUBJECT'] = $inbox_info[$key]['MAIL_SUBJECT'];
                    }
                    if ($value['MAIL_READ_UNREAD'] != '') {
                        $read_user = explode(',', $value['MAIL_READ_UNREAD']);
                        if (!in_array($login_uniform['USERNAME'], $read_user)) {
                            array_push($key, $value['MAIL_ID']);
                            $inbox_info[$key]['MAIL_SUBJECT'] = $inbox_info[$key]['MAIL_SUBJECT'];
                        }
                    }
                    if ($value['MAIL_ATTACHMENT'] != '') {
                        $inbox_info[$key]['MAIL_SUBJECT'] = $inbox_info[$key]['MAIL_SUBJECT'];
                    }
                }

                $msg_count = 0;
                foreach ($inbox_info as $mail) {
                    $read_unread = explode(',', $mail['MAIL_READ_UNREAD']);
                    if (!in_array($login_uniform['USERNAME'], $read_unread)) {
                        $msg_count++;
                    }
                }

                $teacher_info['success'] = 1;
                $teacher_info['notification_count'] = (count($event_data) + count($notes_RET));
                $teacher_info['message_count'] = $msg_count;
                $teacher_info['FIRST_NAME'] = $student_Check['FIRST_NAME'];
                $teacher_info['LAST_NAME'] = $student_Check['LAST_NAME'];
                $teacher_info['EMAIL'] = $student_Check['EMAIL'];
                $teacher_info['GENDER'] = $student_Check['GENDER'];
                $teacher_info['LAST_LOGIN'] = $login_uniform['LAST_LOGIN'];
                $teacher_info['STUDENT_ID'] = $login_uniform['USER_ID'];
                $stuPicPath = $path . $login_uniform['USER_ID'] . ".JPG";
                if (file_exists($stuPicPath)) {
                    $teacher_info['PHOTO'] = $htpath . $login_uniform['USER_ID'] . ".JPG";
                } else {
                    $teacher_info['PHOTO'] = "";
                }

                $teacher_info['SCHOOL_ID'] = UserSchool();
                $teacher_info['SYEAR'] = $student_Check['SYEAR'];
                $teacher_info['Schoolyear_list'] = $school_years_data;
                $teacher_info['marking_period_list'] = $data;
                $teacher_info['marking_period_type'] = $allMP;
                $teacher_info['err_msg'] = 'Nil';
                $teacher_info['type'] = $login_uniform['PROFILE_ID'];
            } else {
                $teacher_info['success'] = 0;
                $teacher_info['notification_count'] = 0;
                $teacher_info['message_count'] = 0;
                $teacher_info['FIRST_NAME'] = '';
                $teacher_info['LAST_NAME'] = '';
                $teacher_info['EMAIL'] = '';
                $teacher_info['GENDER'] = '';
                $teacher_info['LAST_LOGIN'] = '';
                $teacher_info['STUDENT_ID'] = '';
                $teacher_info['PHOTO'] = '';
                $teacher_info['SCHOOL_ID'] = '';
                $teacher_info['SYEAR'] = '';
                $teacher_info['Schoolyear_list'] = '';
                $teacher_info['UserMP'] = '';
                $teacher_info['marking_period_list'] = '';
                $teacher_info['err_msg'] = 'This user is not a ' . $profile . '.';
                $teacher_info['type'] = $login_uniform['PROFILE_ID'];
            }
        } elseif ($profile == 'parent') {
            $login_uniform = DBGet(DBQuery('SELECT * FROM login_authentication  WHERE USER_ID=\'' . $staff_id . '\' AND PROFILE_ID=\'' . $_REQUEST['profile_id'] . '\''));
            $login_uniform = $login_uniform[1];

//            $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
            //            $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
            //            $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
            //            $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
            //            $scr_path = explode('/',$_SERVER['SCRIPT_NAME']);
            //            $file_path = $scr_path[1];
            //
            //            $htpath=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port ."/".$file_path."/assets/studentphotos/";
            //            $path ='../assets/studentphotos/';
            $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
            $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
            $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
            $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":" . $_SERVER["SERVER_PORT"]);
            $scr_path = explode('/webservice/', $_SERVER['SCRIPT_NAME']);
            $file_path = $scr_path[0];

            $htpath = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port;
            if ($file_path != '') {
                $htpath = $htpath . "/" . $file_path;
            }

            $htpath = $htpath . "/assets/studentphotos/";

            $path = '../assets/studentphotos/';
            if ($login_uniform['PROFILE_ID'] == $_REQUEST['profile_id']) {
                $login_RET = DBGet(DBQuery("SELECT PROFILE,STAFF_ID AS STAFF_ID,CURRENT_SCHOOL_ID AS CURRENT_SCHOOL_ID,FIRST_NAME,LAST_NAME,PROFILE_ID,IS_DISABLE,EMAIL FROM people WHERE STAFF_ID=" . $login_uniform['USER_ID'])); //pinki

                if (count($login_RET) > 0) {
                    $login_RET[1]['USERNAME'] = $login_uniform['USERNAME'];
                    $login_RET[1]['LAST_LOGIN'] = $login_uniform['LAST_LOGIN'];
                    $login_RET[1]['FAILED_LOGIN'] = $login_uniform['FAILED_LOGIN'];
                }

                if ($login_RET && $login_RET[1]['IS_DISABLE'] != 'Y') {
                    $_SESSION['STAFF_ID'] = $login_RET[1]['STAFF_ID'];
                    $_SESSION['LAST_LOGIN'] = $login_RET[1]['LAST_LOGIN'];

                    $syear_RET = DBGet(DBQuery("SELECT MAX(SYEAR) AS SYEAR FROM school_years WHERE SCHOOL_ID=" . $login_RET[1]['CURRENT_SCHOOL_ID']));
                    $_SESSION['UserSyear'] = $syear_RET[1]['SYEAR'];
                    $_SESSION['UserSchool'] = $login_RET[1]['CURRENT_SCHOOL_ID'];
                    $_SESSION['PROFILE_ID'] = $login_RET[1]['PROFILE_ID'];
                    $_SESSION['FIRST_NAME'] = $login_RET[1]['FIRST_NAME'];
                    $_SESSION['LAST_NAME'] = $login_RET[1]['LAST_NAME'];
                    $_SESSION['PROFILE'] = $login_RET[1]['PROFILE'];
                    $_SESSION['USERNAME'] = $login_uniform['USERNAME'];
                    $_SESSION['FAILED_LOGIN'] = $login_RET[1]['FAILED_LOGIN'];
                    $_SESSION['CURRENT_SCHOOL_ID'] = $login_RET[1]['CURRENT_SCHOOL_ID'];

//        $_SESSION['USERNAME'] = optional_param('USERNAME','',PARAM_RAW);
                    $RET = DBGet(DBQuery("SELECT sju.STUDENT_ID, se.SCHOOL_ID FROM students s,students_join_people sju, student_enrollment se WHERE s.STUDENT_ID=sju.STUDENT_ID AND sju.PERSON_ID='" . $staff_id . "' AND se.SYEAR=" . UserSyear() . " AND se.STUDENT_ID=sju.STUDENT_ID AND (('" . DBDate('mysql') . "' BETWEEN se.START_DATE AND se.END_DATE OR se.END_DATE IS NULL) AND '" . DBDate('mysql') . "'>=se.START_DATE)"));
                    foreach ($RET as $student) {
                        $_SESSION['UserSchool'] = $student['SCHOOL_ID'];
                    }
                    $school_years_RET1 = DBGet(DBQuery("SELECT START_DATE,END_DATE FROM school_years WHERE SCHOOL_ID=" . UserSchool() . " AND SYEAR=" . UserSyear()));
                    $school_years_RET1 = $school_years_RET1[1];
                    $school_years_RET1['START_DATE'] = explode("-", $school_years_RET1['START_DATE']);
                    $school_years_RET1['START_DATE'] = $school_years_RET1['START_DATE'][0];
                    $school_years_RET1['END_DATE'] = explode("-", $school_years_RET1['END_DATE']);
                    $school_years_RET1['END_DATE'] = $school_years_RET1['END_DATE'][0];
                    $i = $s = $m = 0;
                    if ($school_years_RET1['END_DATE'] > $school_years_RET1['START_DATE']) {
                        if (UserStudentIDWs() == '') {
                            $stu_ID = DBGet(DBQuery("SELECT sju.STUDENT_ID,CONCAT(s.LAST_NAME,', ',s.FIRST_NAME) AS FULL_NAME,se.SCHOOL_ID FROM students s,students_join_people sju, student_enrollment se WHERE s.STUDENT_ID=sju.STUDENT_ID AND sju.PERSON_ID='" . $login_RET[1]['STAFF_ID'] . "' AND se.SYEAR=" . UserSyear() . " AND se.STUDENT_ID=sju.STUDENT_ID AND (('" . DBDate('mysql') . "' BETWEEN se.START_DATE AND se.END_DATE OR se.END_DATE IS NULL) AND '" . DBDate('mysql') . "'>=se.START_DATE)"));
                            $stu_ID = $stu_ID[1]['STUDENT_ID'];
                        } else {
                            $stu_ID = UserStudentIDWs();
                        }

                        $school_years_RET = DBGet(DBQuery("SELECT DISTINCT sy.START_DATE,sy.END_DATE FROM school_years sy,student_enrollment se WHERE se.SYEAR=sy.SYEAR AND se.STUDENT_ID=" . $stu_ID . " AND sy.SCHOOL_ID=" . UserSchool() . " "));

                        foreach ($school_years_RET as $school_years) {
                            $st_date = explode("-", $school_years['START_DATE']);
                            $school_years['START_DATE'] = $st_date[0];
                            $end_date = explode("-", $school_years['END_DATE']);
                            $school_years['END_DATE'] = $end_date[0];
                            $school_years_data[$i]['ID'] = $school_years['START_DATE'];
                            $school_years_data[$i]['VALUE'] = $school_years['START_DATE'] . "-" . $school_years['END_DATE'];
                            $i++;
//                        echo "<OPTION value=$school_years[START_DATE]".((UserSyear()==$school_years['START_DATE'])?' SELECTED':'')."> $school_years[START_DATE]-".($school_years['END_DATE'])."</OPTION>";
                        }
                    } elseif ($school_years_RET1['END_DATE'] == $school_years_RET1['START_DATE']) {
                        if (UserStudentIDWs() == '') {
                            $stu_ID = DBGet(DBQuery("SELECT sju.STUDENT_ID,CONCAT(s.LAST_NAME,', ',s.FIRST_NAME) AS FULL_NAME,se.SCHOOL_ID FROM students s,students_join_people sju, student_enrollment se WHERE s.STUDENT_ID=sju.STUDENT_ID AND sju.PERSON_ID='" . $login_RET[1]['STAFF_ID'] . "' AND se.SYEAR=" . UserSyear() . " AND se.STUDENT_ID=sju.STUDENT_ID AND (('" . DBDate('mysql') . "' BETWEEN se.START_DATE AND se.END_DATE OR se.END_DATE IS NULL) AND '" . DBDate('mysql') . "'>=se.START_DATE)"));
                            $stu_ID = $stu_ID[1]['STUDENT_ID'];
                        } else {
                            $stu_ID = UserStudentIDWs();
                        }

                        $school_years_RET = DBGet(DBQuery("SELECT DISTINCT sy.START_DATE,sy.END_DATE FROM school_years sy,student_enrollment se WHERE se.SYEAR=sy.SYEAR AND se.STUDENT_ID=" . $stu_ID . " AND sy.SCHOOL_ID=" . UserSchool() . " "));

                        foreach ($school_years_RET as $school_years) {
                            $st_date = explode("-", $school_years['START_DATE']);
                            $school_years['START_DATE'] = $st_date[0];
                            $end_date = explode("-", $school_years['END_DATE']);
                            $school_years['END_DATE'] = $end_date[0];
                            $school_years_data[$i]['ID'] = $school_years['START_DATE'];
                            $school_years_data[$i]['VALUE'] = $school_years['START_DATE'];
                            $i++;
//                        echo "<OPTION value=$school_years[START_DATE]".((UserSyear()==$school_years['START_DATE'])?' SELECTED':'')."> $school_years[START_DATE]-".($school_years['END_DATE'])."</OPTION>";
                        }
                    }
                    $RET = DBGet(DBQuery("SELECT sju.STUDENT_ID,CONCAT(s.LAST_NAME,', ',s.FIRST_NAME) AS FULL_NAME,se.SCHOOL_ID FROM students s,students_join_people sju, student_enrollment se WHERE s.STUDENT_ID=sju.STUDENT_ID AND sju.PERSON_ID='" . UserWs('STAFF_ID') . "' AND se.SYEAR=" . UserSyear() . " AND se.STUDENT_ID=sju.STUDENT_ID AND (('" . DBDate('mysql') . "' BETWEEN se.START_DATE AND se.END_DATE OR se.END_DATE IS NULL) AND '" . DBDate('mysql') . "'>=se.START_DATE)"));
                    if (!UserStudentIDWs()) {
                        $_SESSION['student_id'] = $RET[1]['STUDENT_ID'];
                    }

//                    echo "<SELECT name=student_id onChange='this.form.submit();'>";
                    if (count($RET) > 0) {
                        foreach ($RET as $student) {

                            $student_data[$s]['ID'] = $student['STUDENT_ID'];
                            $student_data[$s]['VALUE'] = $student['FULL_NAME'];
                            $s++;
//                            echo "<OPTION value=$student[STUDENT_ID]".((UserStudentID()==$student['STUDENT_ID'])?' SELECTED':'').">".$student['FULL_NAME']."</OPTION>";
                            if (UserStudentIDWs() == $student['STUDENT_ID']) {
                                $_SESSION['UserSchool'] = $student['SCHOOL_ID'];
                            }

                        }
                    }

                    if (!UserMP()) {
                        $_SESSION['UserMP'] = GetCurrentMPWs('QTR', DBDate(), UserSyear(), UserSchool());
                    }

                    $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_quarters WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
                    if (!isset($_SESSION['UserMP'])) {
                        $_SESSION['UserMP'] = GetCurrentMPWs('QTR', DBDate(), UserSyear(), UserSchool());
                    }

                    if (!$RET) {
                        $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_semesters WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
                        if (!isset($_SESSION['UserMP'])) {
                            $_SESSION['UserMP'] = GetCurrentMPWs('SEM', DBDate(), UserSyear(), UserSchool());
                        }

                    }

                    if (!$RET) {
                        $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM school_years WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
                        if (!isset($_SESSION['UserMP'])) {
                            $_SESSION['UserMP'] = GetCurrentMPWs('FY', DBDate(), UserSyear(), UserSchool());
                        }

                    }

//                    echo "<SELECT name=mp onChange='this.form.submit();'>";
                    if (count($RET) > 0) {
                        if (!UserMP()) {
                            $_SESSION['UserMP'] = $RET[1]['MARKING_PERIOD_ID'];
                        }

                        foreach ($RET as $quarter) {
                            $mp_data[$m]['ID'] = $quarter['MARKING_PERIOD_ID'];
                            $mp_data[$m]['TITLE'] = $quarter['TITLE'];
                            $m++;
                        }
                        $allMP = 'QTR';
//                            echo "<OPTION value=$quarter[MARKING_PERIOD_ID]".(UserMP()==$quarter['MARKING_PERIOD_ID']?' SELECTED':'').">".$quarter['TITLE']."</OPTION>";
                    }
//            }

                    $notes_RET = DBGet(DBQuery('SELECT IF(pn.school_id IS NULL,\'All School\',(SELECT TITLE FROM schools WHERE id=pn.school_id)) AS SCHOOL,pn.LAST_UPDATED,CONCAT(\'<b>\',pn.TITLE,\'</b>\') AS TITLE,pn.CONTENT
                                FROM portal_notes pn
                                WHERE pn.SYEAR=\'' . UserSyear() . '\' AND pn.START_DATE<=CURRENT_DATE AND
                                    (pn.END_DATE>=CURRENT_DATE OR pn.END_DATE IS NULL)
                                    AND (pn.school_id IS NULL OR pn.school_id IN(' . GetUserSchoolsWs($login_uniform['USER_ID'], UserSyear(), $login_uniform['PROFILE_ID'], true) . '))
                                    AND (' . ($login_uniform['PROFILE_ID'] == '' ? ' FIND_IN_SET(\'teacher\', pn.PUBLISHED_PROFILES)>0' : ' FIND_IN_SET(' . $login_uniform['PROFILE_ID'] . ',pn.PUBLISHED_PROFILES)>0)') . '
                                    ORDER BY pn.SORT_ORDER,pn.LAST_UPDATED DESC'), array('LAST_UPDATED' => 'ProperDate', 'CONTENT' => '_nl2br'));

                    $events_RET = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL
                    FROM calendar_events ce,calendar_events_visibility cev,schools s
                    WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY
                        AND ce.SYEAR=\'' . UserSyear() . '\'
                        AND ce.school_id IN(' . GetUserSchoolsWs($login_uniform['USER_ID'], UserSyear(), $login_uniform['PROFILE_ID'], true) . ')
                        AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=cev.CALENDAR_ID
                        AND ' . ($login_uniform['PROFILE_ID'] == '' ? 'cev.PROFILE=\'teacher\'' : 'cev.PROFILE_ID=' . $login_uniform['PROFILE_ID']) . '
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
                    if (count($events_RET) > 0) {
                        foreach ($events_RET as $event) {
                            $events_data[] = $event;
                        }
                    } else {
                        $events_data = array();
                    }

                    $link = array();
                    $id = array();
                    $arr = array();
                    $qr = "select to_user,mail_id,to_cc,to_bcc from msg_inbox where (isdraft=0 OR isdraft IS NULL)";
                    $fetch = DBGet(DBQuery($qr));
                    foreach ($fetch as $key => $value) {
                        $s = $value['TO_USER'];
                        $cc = $value['TO_CC'];
                        $bcc = $value['TO_BCC'];

                        $arr = explode(',', $s);
                        $arr_cc = explode(',', $cc);
                        $arr_bcc = explode(',', $bcc);

                        if (in_array($login_uniform['USERNAME'], $arr) || in_array($login_uniform['USERNAME'], $arr_cc) || in_array($login_uniform['USERNAME'], $arr_bcc)) {
                            array_push($id, $value['MAIL_ID']);
                        } else {

                        }
                    }
                    $count = count($id);
                    if ($count > 0) {
                        $to_user_id = implode(',', $id);
                    } else {
                        $to_user_id = 'null';
                    }

                    $inbox = "select * from msg_inbox where mail_id in($to_user_id) order by(mail_id)desc";
                    $inbox_info = DBGet(DBQuery($inbox));

                    foreach ($inbox_info as $key => $value) {
                        if ($value['MAIL_READ_UNREAD'] == '') {
                            $inbox_info[$key]['MAIL_SUBJECT'] = $inbox_info[$key]['MAIL_SUBJECT'];
                        }
                        if ($value['MAIL_READ_UNREAD'] != '') {
                            $read_user = explode(',', $value['MAIL_READ_UNREAD']);
                            if (!in_array($login_uniform['USERNAME'], $read_user)) {
                                array_push($key, $value['MAIL_ID']);
                                $inbox_info[$key]['MAIL_SUBJECT'] = $inbox_info[$key]['MAIL_SUBJECT'];
                            }
                        }
                        if ($value['MAIL_ATTACHMENT'] != '') {
                            $inbox_info[$key]['MAIL_SUBJECT'] = $inbox_info[$key]['MAIL_SUBJECT'];
                        }
                    }

                    $msg_count = 0;
                    foreach ($inbox_info as $mail) {
                        $read_unread = explode(',', $mail['MAIL_READ_UNREAD']);
                        if (!in_array($login_uniform['USERNAME'], $read_unread)) {
                            $msg_count++;
                        }
                    }

                    $teacher_info['success'] = 1;
                    $teacher_info['notification_count'] = (count($event_data) + count($notes_RET));
                    $teacher_info['message_count'] = $msg_count;
                    $teacher_info['USERNAME'] = $login_uniform['USERNAME'];
                    $teacher_info['FIRST_NAME'] = $login_RET[1]['FIRST_NAME'];
                    $teacher_info['LAST_NAME'] = $login_RET[1]['LAST_NAME'];
                    $teacher_info['EMAIL'] = $login_RET[1]['EMAIL'];
                    $teacher_info['STAFF_ID'] = $login_RET[1]['STAFF_ID'];
                    //             $stuPicPath=$path.$login_uniform['USER_ID'].".JPG";
                    //             if(file_exists($stuPicPath))
                    //                 $teacher_info['PHOTO']=$htpath.$login_uniform['USER_ID'].".JPG";
                    //             else
                    $teacher_info['PHOTO'] = "";
                    //
                    $teacher_info['SCHOOL_ID'] = UserSchool();
                    $teacher_info['SYEAR'] = UserSyear();
                    $teacher_info['Schoolyear_list'] = $school_years_data;
                    $teacher_info['UserMP'] = UserMP();
                    $teacher_info['marking_period_list'] = $mp_data;
                    $teacher_info['marking_period_type'] = $allMP;
                    $teacher_info['students_list'] = $student_data;
                    $teacher_info['selected_student'] = UserStudentIDWs();
                    $teacher_info['err_msg'] = 'Nil';
                    $teacher_info['type'] = $login_uniform['PROFILE_ID'];
                } else {
                    $teacher_info['success'] = 0;
                    $teacher_info['notification_count'] = 0;
                    $teacher_info['message_count'] = 0;
                    $teacher_info['USERNAME'] = '';
                    $teacher_info['FIRST_NAME'] = '';
                    $teacher_info['LAST_NAME'] = '';
                    $teacher_info['EMAIL'] = '';
                    $teacher_info['STAFF_ID'] = '';
                    $teacher_info['PHOTO'] = '';
                    $teacher_info['SCHOOL_ID'] = '';
                    $teacher_info['SYEAR'] = '';
                    $teacher_info['Schoolyear_list'] = '';
                    $teacher_info['UserMP'] = '';
                    $teacher_info['marking_period_list'] = '';
                    $teacher_info['err_msg'] = 'Either your account is inactive or your access permission has been revoked. Please contact the school administration.';
                    $teacher_info['type'] = $login_uniform['PROFILE_ID'];
                }
            } else {
                $teacher_info['success'] = 0;
                $teacher_info['notification_count'] = 0;
                $teacher_info['message_count'] = 0;
                $teacher_info['USERNAME'] = '';
                $teacher_info['FIRST_NAME'] = '';
                $teacher_info['LAST_NAME'] = '';
                $teacher_info['EMAIL'] = '';
                $teacher_info['STAFF_ID'] = '';
                $teacher_info['PHOTO'] = '';
                $teacher_info['SCHOOL_ID'] = '';
                $teacher_info['SYEAR'] = '';
                $teacher_info['Schoolyear_list'] = '';
                $teacher_info['UserMP'] = '';
                $teacher_info['marking_period_list'] = '';
                $teacher_info['err_msg'] = 'This user is not a ' . $profile . '.';
                $teacher_info['type'] = $login_uniform['PROFILE_ID'];
            }

        }
    } else {
        $data = array('success' => 0, 'msg' => 'Not authenticated user');
    }
} else {
    $data = array('success' => 0, 'msg' => 'Not authenticated user');
}

echo json_encode($teacher_info);
