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
//print_r($_REQUEST);
$stu_sd_err_count = 0;
if (isset($_SESSION['MANUAL_ERROR'])) {
    echo "<div class=\"alert bg-danger alert-styled-left\">" . $_SESSION['MANUAL_ERROR'] . "</div>";
    unset($_SESSION['MANUAL_ERROR']);
}
if (isset($_SESSION['manual_override_note'])) {
    echo '<div class="alert bg-success alert-styled-left">' . $_SESSION['manual_override_note'] . '</div>';
    unset($_SESSION['manual_override_note']);
}
if ($_REQUEST['modfunc'] == 'save') {
    $mon_arr = array("JAN" => "01", "FEB" => "02", "MAR" => "03", "APR" => "04", "MAY" => "05", "JUN" => "06", "JUL" => "07", "AUG" => "08", "SEP" => "09", "OCT" => "10", "NOV" => "11", "DEC" => "12");
    $_REQUEST['year'] = $_REQUEST['year_start'];
    $_REQUEST['month'] = $_REQUEST['month_start'];
    $_REQUEST['day'] = $_REQUEST['day_start'];
    $st_dt = $_REQUEST['year'] . '-' . $_REQUEST['month'] . '-' . $_REQUEST['day'];
    if ($_REQUEST['marking_period_id'] != '') {
        $chk_st_dt = DBGet(DBQuery('SELECT START_DATE,END_DATE FROM marking_periods WHERE MARKING_PERIOD_ID=' . $_REQUEST['marking_period_id'] . ' AND SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool()));
        $chk_st_dt = $chk_st_dt[1];
        if (strtotime($st_dt) < strtotime($chk_st_dt['START_DATE']) || strtotime($st_dt) > strtotime($chk_st_dt['END_DATE'])) {
            $modname = $_REQUEST['modname'];
            unset($_REQUEST);
            $_REQUEST['modname'] = $modname;
            unset($modname);
            // echo "<script type='text/javascript'>";
            // echo "$('body').find('.jGrowl').attr('class', '').attr('id', '').hide();
            //     $.jGrowl(" . _scheduleStartDateCannotBeBeforeMarkingPeriodsEndDate . ", {
            //         position: 'top-center',
            //         theme: 'alert-styled-left bg-danger',
            //         life: 5000,
            //     });";
            // echo "</script>";

            echo "<div class='alert bg-danger alert-styled-left'>" . _scheduleStartDateCannotBeBeforeMarkingPeriodsEndDate . "</div>";
        }
    }
}
if (!$_REQUEST['modfunc'] && $_REQUEST['search_modfunc'] != 'list')
    unset($_SESSION['MassSchedule.php']);

if (isset($_REQUEST['per']))
    $per_status = $_REQUEST['per'];

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHA) == 'seatso') {
    $cp_id = $_REQUEST['course_period_id'];
    if (isset($_REQUEST['tables']['course_periods'][$cp_id]['TOTAL_SEATS'])) {
        $stu_alrd_id = array();
        // $n_schedule_stu = $_SESSION['NOT_SCHEDULE'];
        $room_name = $room_av_id = array();
        $rooms_id = DBGet(DBQuery('SELECT ROOM_ID FROM course_period_var WHERE COURSE_PERIOD_ID=\'' . $_REQUEST['course_period_id'] . '\''));

        for ($i = 1; $i <= count($rooms_id); $i++) {
            array_push($room_av_id, $rooms_id[$i]['ROOM_ID']);
        }

        $seat = $_REQUEST['tables']['course_periods'][$cp_id]['TOTAL_SEATS'];

        for ($i = 0; $i < count($room_av_id); $i++) {
            $roomsv_id = DBGet(DBQuery('SELECT CAPACITY,TITLE FROM rooms WHERE ROOM_ID=\'' . $room_av_id[$i] . '\''));
            if ($roomsv_id[1]['CAPACITY'] < $seat) {

                array_push($room_name, $roomsv_id[1]['TITLE']);
            }
        }
        if (count($room_name) > 0) {
            $_SESSION['MANUAL_ERROR'] = "Unable to update because   " . implode(',', $room_name) . '' . _capacityIsLowerThanYourRequestedSeats . '';
            echo '<SCRIPT type="text/javascript">document.location.href = "Modules.php?modname=scheduling/MassSchedule.php";</SCRIPT>';
        } else {

            DBQuery('UPDATE course_periods SET total_seats=' . $seat . ' WHERE COURSE_PERIOD_ID=\'' . $cp_id . '\'');

            $cp_data = DBGet(DBQuery('SELECT * FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $cp_id . '\''))[1];

            $in_data_stu_id = DBGet(DBQuery('SELECT * FROM schedule WHERE COURSE_PERIOD_ID=\'' . $cp_id . '\''));

            for ($i = 1; $i <= count($in_data_stu_id); $i++) {
                array_push($stu_alrd_id, $in_data_stu_id[$i]['STUDENT_ID']);
            }
            $n_schedule_stu = $_SESSION['NOT_SCHEDULE'];
            foreach ($n_schedule_stu as $k => $v) {
                if (!in_array($v, $stu_alrd_id)) {
                    $sql = 'INSERT INTO schedule (SYEAR,SCHOOL_ID,STUDENT_ID,COURSE_ID,COURSE_PERIOD_ID,MP,MARKING_PERIOD_ID,START_DATE,END_DATE,MODIFIED_DATE,MODIFIED_BY)
	                values(\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'' . $v . '\',\'' . $cp_data['COURSE_ID'] . '\',\'' . $cp_data['COURSE_PERIOD_ID'] . '\',\'' . $cp_data['MP'] . '\',\'' . clean_param($cp_data['MARKING_PERIOD_ID'], PARAM_INT) . '\',\'' . $_SESSION['SCH']['START_DATE'] . '\',\'' . $cp_data['END_DATE'] . '\',\'' . date('Y-m-d') . '\',\'' . User('STAFF_ID') . '\')';
                    DBQuery($sql);
                }
            }
            // $qr = DBGet(DBQuery('SELECT COUNT(*) AS TOTAL FROM schedule WHERE (END_DATE>\'' . date('Y-m-d') . '\' or END_DATE IS NULL OR END_DATE=\'0000-00-00\' ) AND COURSE_PERIOD_ID=\'' . $course_id . '\''));
            // DBQuery('UPDATE course_periods SET filled_seats=' . $qr[1]['TOTAL'] . ' WHERE COURSE_PERIOD_ID=\'' . $course_id . '\'');

            $_SESSION['manual_override_note'] = "The course - <b>" . $cp_data['TITLE'] . "</b> - has been added to the remaining students' schedules.";
            echo '<SCRIPT type="text/javascript">document.location.href = "Modules.php?modname=scheduling/MassSchedule.php";</SCRIPT>';
            unset($_SESSION['NOT_SCHEDULE']);
        }
    } else {
        echo '<SCRIPT language=javascript>opener.document.location = "Modules.php?modname=scheduling/MassSchedule.php"; window.close();</script>';
    }
}
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHA) == 'save') {
    if ($_SESSION['MassSchedule.php']) {
        $month_names = array('JAN' => '01', 'FEB' => '02', 'MAR' => '03', 'APR' => '04', 'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AUG' => '08', 'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DEC' => '12');
        $start_date = $_REQUEST['day'] . '-' . array_search($_REQUEST['month'], $month_names) . '-' . $_REQUEST['year'];
        //$start_date = $_REQUEST['year'] . '-' . $_REQUEST['month'] . '-' . $_REQUEST['day'];
        $_SESSION['SCH']['START_DATE'] = $_REQUEST['year'] . '-' . $_REQUEST['month'] . '-' . $_REQUEST['day'];
        if (!VerifyDate($start_date))
            BackPrompt('The date you entered is not valid');
        $course_mp = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $_SESSION['MassSchedule.php']['course_period_id'] . '\''));
        $course_mp = $course_mp[1]['MARKING_PERIOD_ID'];
        $course_mp_table = GetMPTable(GetMP($course_mp, 'TABLE'));

        $course_bg_date = DBGet(DBQuery('SELECT BEGIN_DATE,END_DATE FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $_SESSION['MassSchedule.php']['course_period_id'] . '\''));



        if ($course_mp_table != 'FY' && $course_mp != $_REQUEST['marking_period_id'] && strpos(GetChildrenMP($course_mp_table, $course_mp), "'" . $_REQUEST['marking_period_id'] . "'") === false) {
            ShowErr("" . _youCannotScheduleAStudentIntoChatCourseDuringTheMarkingPeriodThatYouChoseThisCourseMeetsOn . "" . GetMP($course_mp) . '.');

            for_error_sch();
        }
        $mp_table = GetMPTable(GetMP($_REQUEST['marking_period_id'], 'TABLE'));

        $current_RET = DBGet(DBQuery('SELECT STUDENT_ID FROM schedule WHERE COURSE_PERIOD_ID=\'' . $_SESSION['MassSchedule.php']['course_period_id'] . '\' AND SYEAR=\'' . UserSyear() . '\' AND ((\'' . $start_date . '\' BETWEEN START_DATE AND END_DATE OR END_DATE IS NULL) AND \'' . $start_date . '\'>=START_DATE)'), array(), array('STUDENT_ID'));
        $request_RET = DBGet(DBQuery('SELECT STUDENT_ID FROM schedule_requests WHERE WITH_PERIOD_ID IN(SELECT cpv.PERIOD_ID FROM course_period_var cpv WHERE cpv.COURSE_PERIOD_ID=\'' . $_SESSION['MassSchedule.php']['course_period_id'] . '\')  AND SYEAR=\'' . UserSyear() . '\' AND COURSE_ID=\'' . $_SESSION['MassSchedule.php']['course_id'] . '\''), array(), array('STUDENT_ID'));

        // ----------------------------------------- Time Clash Logic Start ---------------------------------------------------------- //

        function get_min($time)
        {

            $str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $time);

            sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);

            $str_time = $hours * 3600 + $minutes * 60 + $seconds;
            //                    return $time.'--'.$str_time;
            //                    return $str_time;
            return strtotime($time);
            //                    return $time;
        }

        //        function con_date($date) {
        //            $mother_date = $date;
        //            $year = substr($mother_date, 7, 4);
        //            $temp_month = substr($mother_date, 3, 3);
        //
        //            if ($temp_month == 'JAN')
        //                $month = '-01-';
        //            elseif ($temp_month == 'FEB')
        //                $month = '-02-';
        //            elseif ($temp_month == 'MAR')
        //                $month = '-03-';
        //            elseif ($temp_month == 'APR')
        //                $month = '-04-';
        //            elseif ($temp_month == 'MAY')
        //                $month = '-05-';
        //            elseif ($temp_month == 'JUN')
        //                $month = '-06-';
        //            elseif ($temp_month == 'JUL')
        //                $month = '-07-';
        //            elseif ($temp_month == 'AUG')
        //                $month = '-08-';
        //            elseif ($temp_month == 'SEP')
        //                $month = '-09-';
        //            elseif ($temp_month == 'OCT')
        //                $month = '-10-';
        //            elseif ($temp_month == 'NOV')
        //                $month = '-11-';
        //            elseif ($temp_month == 'DEC')
        //                $month = '-12-';
        //
        //            $day = substr($mother_date, 0, 2);
        //
        //            $select_date = $year . $month . $day;
        //            return $select_date;
        //        }


        function con_date($date)
        {

            $mother_date = $date;
            $year = substr($mother_date, 7, 4);
            $temp_month = substr($mother_date, 3, 3);

            if ($temp_month == 'JAN')
                $month = '-01-';
            elseif ($temp_month == 'FEB')
                $month = '-02-';
            elseif ($temp_month == 'MAR')
                $month = '-03-';
            elseif ($temp_month == 'APR')
                $month = '-04-';
            elseif ($temp_month == 'MAY')
                $month = '-05-';
            elseif ($temp_month == 'JUN')
                $month = '-06-';
            elseif ($temp_month == 'JUL')
                $month = '-07-';
            elseif ($temp_month == 'AUG')
                $month = '-08-';
            elseif ($temp_month == 'SEP')
                $month = '-09-';
            elseif ($temp_month == 'OCT')
                $month = '-10-';
            elseif ($temp_month == 'NOV')
                $month = '-11-';
            elseif ($temp_month == 'DEC')
                $month = '-12-';

            $day = substr($mother_date, 0, 2);

            $select_date = $year . $month . $day;
            return $select_date;
        }

        $convdate = con_date($start_date);
        $course_per_id = $_SESSION['MassSchedule.php']['course_period_id'];
        ////Start Date Check///////////////////
        $start_date_q = DBGet(DBQuery('SELECT START_DATE FROM school_years WHERE school_id=' . UserSchool() . ' AND syear=' . UserSyear() . ''));
        if (strtotime($start_date_q[1]['START_DATE']) > strtotime($convdate)) {
            $start_date_q_clash = _cannotScheduleStudentsBeforeSchoolStartDate;
        }
        ///////   for not to schedule before course start date////////
        else if (strtotime($convdate) < strtotime($course_bg_date[1]['BEGIN_DATE']) || strtotime($convdate) > strtotime($course_bg_date[1]['END_DATE'])) {
            $sche_date_err = _studentsScheduleDateShouldBeBetweenCourseStartDateAndEndDate;
        } else {
            unset($start_date_q_clash);
            unset($sche_date_err);
            unset($_SESSION['NOT_SCHEDULE']);
            foreach ($_REQUEST['student'] as $index => $value) {
                $_SESSION['NOT_SCHEDULE'][$index] = $index;
                $_SESSION['NOT_SCHEDULE1'][$index] = $index;
                $stu_start_date_q = DBGet(DBQuery('SELECT START_DATE FROM student_enrollment WHERE STUDENT_ID=' . $index . ' AND school_id=' . UserSchool() . ' AND syear=' . UserSyear() . ''));
                if (strtotime($stu_start_date_q[1]['START_DATE']) > strtotime($convdate)) {
                    unset($_REQUEST['student'][$index]);
                    $stu_s_date_err_q = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME FROM students WHERE STUDENT_ID=' . $index . ''));
                    $stu_s_date_err_n = $stu_s_date_err_q[1]['FIRST_NAME'] . "&nbsp;" . $stu_s_date_err_q[1]['LAST_NAME'];
                    $stu_sd_err .= $stu_s_date_err_n . '<br>';
                    $stu_sd_err_count++;
                }
                if ($_REQUEST['student'][$index]) {
                    $cp_mp = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,BEGIN_DATE,END_DATE FROM course_periods WHERE SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear() . ' AND COURSE_PERIOD_ID = \'' . $course_per_id . '\' '));
                    $cp_mp[1]['MARKING_PERIOD_ID'] = ($cp_mp[1]['MARKING_PERIOD_ID'] != '' ? $cp_mp[1]['MARKING_PERIOD_ID'] : GetMPId('FY'));

                    $cp_mp_st_dt = DBGet(DBQuery('SELECT * FROM marking_periods WHERE MARKING_PERIOD_ID=' . $cp_mp[1]['MARKING_PERIOD_ID']));
                    if (strtotime($cp_mp_st_dt[1]['START_DATE']) > strtotime($convdate)) {
                        unset($_REQUEST['student'][$index]);
                        $stu_s_mp_date_err_n = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME FROM students WHERE STUDENT_ID=' . $index . ''));
                        $stu_s_mp_date_err_n = $stu_s_mp_date_err_n[1]['FIRST_NAME'] . "&nbsp;" . $stu_s_mp_date_err_n[1]['LAST_NAME'];
                        $stu_mp_sd_err .= $stu_s_mp_date_err_n . '<br>';
                    }
                }
            }
            $course_per_id = $_SESSION['MassSchedule.php']['course_period_id'];
            $per_id = DBGet(DBQuery('SELECT PERIOD_ID, DAYS FROM course_period_var WHERE COURSE_PERIOD_ID = \'' . $course_per_id . '\''));
            $period_id = $per_id[1]['PERIOD_ID'];
            $days = $per_id[1]['DAYS'];
            $day_st_count = strlen($days);

            //$st_time = DBGet(DBQuery('SELECT START_TIME, END_TIME FROM school_periods WHERE PERIOD_ID = \'' . $period_id . '\' AND (IGNORE_SCHEDULING IS NULL OR IGNORE_SCHEDULING!=\'' . Y . '\')'));  

            $st_time = DBGet(DBQuery('SELECT START_TIME, END_TIME FROM school_periods WHERE PERIOD_ID = \'' . $period_id . '\' AND (IGNORE_SCHEDULING IS NULL OR IGNORE_SCHEDULING!=\'Y\')'));
            /*             * ******* for homeroom scheduling */
            if ($st_time) {
                $start_time = $st_time[1]['START_TIME'];
                $min_start_time = get_min($start_time);
                $end_time = $st_time[1]['END_TIME'];
                $min_end_time = get_min($end_time);
            }
            // ----------------------------------------- Time Clash Logic End ---------------------------------------------------------- //		
            $period_res_cnt = 0;
            foreach ($_REQUEST['student'] as $student_id => $yes) {

                # ------------------------------------ PARENT RESTRICTION STARTS----------------------------------------- #
                $pa_RET = DBGet(DBQuery('SELECT PARENT_ID FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $_SESSION['MassSchedule.php']['course_period_id'] . '\''));
                if ($pa_RET[1]['PARENT_ID'] != $_SESSION['MassSchedule.php']['course_period_id']) {
                    $stu_pa = DBGet(DBQuery('SELECT START_DATE,END_DATE FROM schedule WHERE STUDENT_ID=\'' . $student_id . '\' AND COURSE_PERIOD_ID=\'' . $pa_RET[1]['PARENT_ID'] . '\' AND DROPPED=\'N\' AND START_DATE<=\'' . date('Y-m-d', strtotime($start_date)) . '\''));
                    $par_sch = count($stu_pa);
                    if ($par_sch < 1 || (strtotime(DBDate()) < strtotime($stu_pa[$par_sch]['START_DATE']) && $stu_pa[$par_sch]['START_DATE'] != "") || (strtotime(DBDate()) > strtotime($stu_pa[$par_sch]['END_DATE']) && $stu_pa[$par_sch]['END_DATE'] != "")) {
                        $select_stu_RET = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME FROM students WHERE STUDENT_ID=\'' . $student_id . '\''));
                        $select_stu = $select_stu_RET[1]['FIRST_NAME'] . "&nbsp;" . $select_stu_RET[1]['LAST_NAME'];
                        $parent_res .= $select_stu . "<br>";
                        continue;
                    }
                }


                # ------------------------------------ PARENT RESTRICTION ENDS----------------------------------------- #
                if ($_SESSION['MassSchedule.php']['gender'] != 'N') {
                    $select_stu_RET = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME,LEFT(GENDER,1) AS GENDER FROM students WHERE STUDENT_ID=\'' . $student_id . '\''));
                    if ($_SESSION['MassSchedule.php']['gender'] != $select_stu_RET[1]['GENDER']) {
                        $select_stu = $select_stu_RET[1]['FIRST_NAME'] . "&nbsp;" . $select_stu_RET[1]['LAST_NAME'];
                        $gender_conflict .= $select_stu . "<br>";
                        continue;
                    }
                    #$clash = true;
                }
                # ------------------------------------ Same Days Conflict Start ------------------------------------------ #
                //echo 'SELECT START_DATE,END_DATE FROM student_enrollment WHERE STUDENT_ID=\'' . $student_id . '\' AND END_DATE IS NOT NULL AND SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool() . ' ORDER BY ID DESC LIMIT 0,1';
                $select_stu_info = DBGet(DBQuery('SELECT START_DATE,END_DATE FROM student_enrollment WHERE STUDENT_ID=\'' . $student_id . '\'  AND SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool() . ' ORDER BY ID DESC LIMIT 0,1'));
                if (count($select_stu_info) > 0 && $select_stu_info[1]['END_DATE'] != '') {
                    if (strtotime($select_stu_info[1]['END_DATE']) < strtotime($course_bg_date[1]['END_DATE'])) {
                        if ($select_stu_RET[1]['FIRST_NAME'] == '') {
                            $select_stu_RET = DBGEt(DBQuery('SELECT FIRST_NAME,LAST_NAME FROM students WHERE STUDENT_ID=\'' . $student_id . '\''));
                        }

                        $select_stu = $select_stu_RET[1]['FIRST_NAME'] . "&nbsp;" . $select_stu_RET[1]['LAST_NAME'];
                        $drop_stu .= $select_stu . "<br>";
                        continue;
                    }
                }
                $mp_RET = DBGet(DBQuery('SELECT cp.MP,cp.MARKING_PERIOD_ID,cpv.DAYS,cpv.PERIOD_ID,cp.MARKING_PERIOD_ID,cp.TOTAL_SEATS,cp.END_DATE,COALESCE(cp.FILLED_SEATS,0) AS FILLED_SEATS FROM course_periods cp,course_period_var cpv WHERE cp.COURSE_PERIOD_ID=\'' . $_SESSION['MassSchedule.php']['course_period_id'] . '\' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID'));

                $cp_period_id = '';
                foreach ($mp_RET as $key => $val) {
                    $cp_period_id .= $val['PERIOD_ID'] . ',';
                }
                $cp_period_id = substr($cp_period_id, 0, -1);
                $mps = GetAllMP(GetMPTable(GetMP($mp_RET[1]['MARKING_PERIOD_ID'], 'TABLE')), $mp_RET[1]['MARKING_PERIOD_ID']);

                $period_RET = DBGet(DBQuery('SELECT cpv.DAYS,cpv.PERIOD_ID FROM schedule s,course_periods cp,course_period_var cpv WHERE cp.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID AND s.STUDENT_ID=\'' . $student_id . '\' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cpv.PERIOD_ID IN (\'' . $cp_period_id . '\') AND s.MARKING_PERIOD_ID IN (' . $mps . ') AND s.START_DATE<=\'' . $mp_RET[1]['END_DATE'] . '\' AND (s.END_DATE IS NULL OR \'' . $start_date . '\'<=s.END_DATE)'));

                $ig_scheld = DBGet(DBQuery('SELECT IGNORE_SCHEDULING FROM school_periods WHERE PERIOD_ID IN (\'' . $cp_period_id . '\') AND SCHOOL_ID=\'' . UserSchool() . '\''));
                $sql_dupl = 'SELECT COURSE_PERIOD_ID FROM schedule WHERE STUDENT_ID = \'' . $student_id . '\' AND COURSE_PERIOD_ID = \'' . $_SESSION['MassSchedule.php']['course_period_id'] . '\' AND (END_DATE IS NULL OR (\'' . $convdate . '\' BETWEEN START_DATE AND END_DATE)) AND SCHOOL_ID=\'' . UserSchool() . '\'';
                $rit_dupl = DBQuery($sql_dupl);
                $count_entry = DBGet($rit_dupl);
                $days_conflict = false;
                if (count($count_entry) < 1 && $ig_scheld[1]['IGNORE_SCHEDULING'] != 'Y')
                    foreach ($period_RET as $existing) {
                        foreach ($mp_RET as $key => $val) {
                            if (strlen($val['DAYS']) + strlen($existing['DAYS']) > 7) {

                                $days_conflict = true;
                                break 2;
                            } else
                                foreach (_str_split($val['DAYS']) as $i)
                                    if (strpos($existing['DAYS'], $i) !== false) {
                                        if ($val['PERIOD_ID'] == $existing['PERIOD_ID']) {

                                            $days_conflict = true;
                                            break 3;
                                        }
                                    }
                        }
                    }
                if (count($count_entry) >= 1)
                    $days_conflict = true;
                if ($days_conflict) {
                    $select_stu_RET = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME FROM students WHERE STUDENT_ID=\'' . $student_id . '\''));
                    $select_stu = $select_stu_RET[1]['FIRST_NAME'] . "&nbsp;" . $select_stu_RET[1]['LAST_NAME'];
                    $period_res[] = $select_stu;
                    $period_res_cnt++;
                    continue;
                }
                # ------------------------------------ Same Days Conflict End ------------------------------------------ #
                $sql = 'SELECT COURSE_PERIOD_ID,START_DATE, MARKING_PERIOD_ID FROM schedule WHERE STUDENT_ID = \'' . $student_id . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND MARKING_PERIOD_ID IN (' . $mps . ') AND START_DATE<=\'' . $mp_RET[1]['END_DATE'] . '\' AND (END_DATE IS NULL OR END_DATE >=\'' . $convdate . '\')';

                $coue_p_id = DBGet(DBQuery($sql));

                if (count($coue_p_id) >= 1) {
                    foreach ($coue_p_id as $ci => $cv) {
                        $min_sel_start_time = "";
                        $min_sel_end_time = "";
                        $cp_id = $cv['COURSE_PERIOD_ID'];
                        $st_dt = $cv['START_DATE'];
                        $mp_id_stu = $cv['MARKING_PERIOD_ID'];

                        # --------------------------------- For Duplicate Entry Start -------------------------------------- #
                        # --------------------------------- For Duplicate Entry Start -------------------------------------- #
                        # -------------------------------------- #
                        $sel_per_id = DBGet(DBQuery('SELECT PERIOD_ID, DAYS FROM course_period_var WHERE COURSE_PERIOD_ID = \'' . $cp_id . '\''));
                        $sel_period_id = $sel_per_id[1]['PERIOD_ID'];
                        $sel_days = $sel_per_id[1]['DAYS'];

                        $ignore_existing_period_ret = DBGet(DBQuery('SELECT IGNORE_SCHEDULING FROM school_periods WHERE PERIOD_ID=\'' . $sel_period_id . '\''));
                        $ignore_existing_period = $ignore_existing_period_ret[1]['IGNORE_SCHEDULING'];
                        if ($ignore_existing_period == 'Y')
                            $sel_period_id = '';
                        if ($sel_period_id) {
                            $sel_st_time = DBGet(DBQuery('SELECT START_TIME, END_TIME FROM school_periods WHERE PERIOD_ID = \'' . $sel_period_id . '\''));
                            $sel_start_time = $sel_st_time[1]['START_TIME'];
                            $min_sel_start_time = get_min($sel_start_time);
                            $sel_end_time = $sel_st_time[1]['END_TIME'];
                            $min_sel_end_time = get_min($sel_end_time);
                        }
                        # ---------------------------- Days conflict ------------------------------------ #
                        //					
                        $j = 0;
                        for ($i = 0; $i < $day_st_count; $i++) {
                            $clip = substr($days, $i, 1);
                            $pos = strpos($sel_days, $clip);
                            if ($pos !== false)
                                $j++;
                        }
                        //					
                        # ---------------------------- Days conflict ------------------------------------ #
                        if ($j != 0) {

                            //                                               if(((9:45 AM <=7:00 AM) && (10:29 AM >=7:00 AM) && 9:45 AM!='') || ((9:45 AM <= 7:44 AM) && (10:29 AM >= 7:44 AM) &&10:29 AM!='') || ((9:45 AM >= 7:00 AM) && (10:29 AM <= 7:44 AM) && 7:00 AM!=''))
                            //                                                   echo "hello";
                            //                                             echo" if(((".$min_sel_start_time." <=". $min_start_time.") && (".$min_sel_end_time." >=". $min_start_time.") &&". $min_sel_start_time."!='') || ((".$min_sel_start_time ."<=". $min_end_time.") && (".$min_sel_end_time." >=". $min_end_time.") &&". $min_sel_end_time."!='') || ((".$min_sel_start_time." >=". $min_start_time.") && (".$min_sel_end_time."<=". $min_end_time.") &&". $min_start_time."!=''))";
                            //                                                echo "<br>";
                            //                                                 exit;
                            $time_clash_found = 0;
                            if ((($min_sel_start_time <= $min_start_time) && ($min_sel_end_time >= $min_start_time) && $min_sel_start_time != '') || (($min_sel_start_time <= $min_end_time) && ($min_sel_end_time >= $min_end_time) && $min_sel_end_time != '') || (($min_sel_start_time >= $min_start_time) && ($min_sel_end_time <= $min_end_time) && $min_start_time != '')) {


                                $select_stu = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME FROM students WHERE STUDENT_ID=\'' . $student_id . '\''));
                                $select_stu = $select_stu[1]['FIRST_NAME'] . "&nbsp;" . $select_stu[1]['LAST_NAME'];

                                $time_clash .= $select_stu . "<br>";
                                $time_clash_found = 1;
                                break;
                            }


                            //			# -------------------- Manual OverRide -------------------------- #
                            //
                            //							
                        }
                    }
                    if ($j == 0 || $time_clash_found == 0) {

                        // ------------------------------------------------------- //
                        # -------------------- Manual OverRide -------------------------- #

                        $check_seats = DBGet(DBQuery('SELECT  (TOTAL_SEATS - FILLED_SEATS) AS AVAILABLE_SEATS  , marking_period_id  AS MPI FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $_SESSION['MassSchedule.php']['course_period_id'] . '\''));

                        $check_seats_mp = $check_seats[1]['MPI'];
                        $check_seats = $check_seats[1]['AVAILABLE_SEATS'];
                        # -------------------- Manual OverRide -------------------------- #

                        if ($check_seats > 0) {
                            if ($_REQUEST['marking_period_id'] != '' && $check_seats_mp != '') {
                                $mark_end_qry = DBGet(DBQuery('SELECT END_DATE FROM marking_periods WHERE MARKING_PERIOD_ID =' . $_REQUEST['marking_period_id'] . ''));
                                $mark_end_date = date('d-M-Y', strtotime($mark_end_qry[1]['END_DATE']));
                            } else if ($_REQUEST['marking_period_id'] == 1 && $check_seats_mp == '') {

                                $mark_end_qry = DBGet(DBQuery('SELECT END_DATE FROM course_details WHERE COURSE_PERIOD_ID =' . $_SESSION['MassSchedule.php']['course_period_id'] . ''));
                                $mark_end_date = date('d-M-Y', strtotime($mark_end_qry[1]['END_DATE']));
                            } else {
                                $mark_end_qry = DBGet(DBQuery('SELECT END_DATE FROM course_details WHERE COURSE_PERIOD_ID =' . $_SESSION['MassSchedule.php']['course_period_id'] . ''));
                                $mark_end_date = date('d-M-Y', strtotime($mark_end_qry[1]['END_DATE']));
                            }
                            if (clean_param($_REQUEST['marking_period_id'], PARAM_INT) == 0)
                                $sql = 'INSERT INTO schedule (SYEAR,SCHOOL_ID,STUDENT_ID,COURSE_ID,COURSE_PERIOD_ID,MP,START_DATE,END_DATE,MODIFIED_DATE,MODIFIED_BY)
													values(\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'' . clean_param($student_id, PARAM_INT) . '\',\'' . $_SESSION['MassSchedule.php']['course_id'] . '\',\'' . $_SESSION['MassSchedule.php']['course_period_id'] . '\',\'' . $mp_table . '\',\'' . $start_date . '\',\'' . $mark_end_date . '\',\'' . date('Y-m-d') . '\',\'' . User('STAFF_ID') . '\')';
                            else {
                                $mp_id = clean_param($_REQUEST['marking_period_id'], PARAM_INT);
                                $sql = 'INSERT INTO schedule (SYEAR,SCHOOL_ID,STUDENT_ID,COURSE_ID,COURSE_PERIOD_ID,MP,MARKING_PERIOD_ID,START_DATE,END_DATE,MODIFIED_DATE,MODIFIED_BY)
													values(\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'' . clean_param($student_id, PARAM_INT) . '\',\'' . $_SESSION['MassSchedule.php']['course_id'] . '\',\'' . $_SESSION['MassSchedule.php']['course_period_id'] . '\',\'' . $mp_table . '\',\'' . clean_param($_REQUEST['marking_period_id'], PARAM_INT) . '\',\'' . $start_date . '\',\'' . $mark_end_date . '\',\'' . date('Y-m-d') . '\',\'' . User('STAFF_ID') . '\')';
                            }
                            $stat_cp = 0;
                            $exis_cp = DBGet(DBQuery('select count(*) as no from course_period_var where course_period_id=' . $_SESSION['MassSchedule.php']['course_period_id']));


                            // $stu_dup_cp=DBGet(DBQuery('select count(*) as no from schedule WHERE course_period_id='.$_SESSION['MassSchedule.php']['course_period_id'].' AND STUDENT_ID='.clean_param($student_id,PARAM_INT).' AND SCHOOL_ID='.UserSchool()));
                            $stu_dup_cp = DBGet(DBQuery('select count(*) as no from schedule WHERE course_period_id=' . $_SESSION['MassSchedule.php']['course_period_id'] . ' AND (END_DATE IS NULL OR (\'' . $convdate . '\' BETWEEN START_DATE AND END_DATE)) AND STUDENT_ID=' . clean_param($student_id, PARAM_INT) . ' AND SCHOOL_ID=' . UserSchool()));

                            if ($stu_dup_cp[1]['NO'] > 0) {

                                $select_dup_stu = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME FROM students WHERE STUDENT_ID=\'' . $student_id . '\''));

                                $select_stu = $select_dup_stu[1]['FIRST_NAME'] . "&nbsp;" . $select_dup_stu[1]['LAST_NAME'];
                                $dup_schedule[] = $select_stu;
                            }
                            if (($exis_cp[1]['NO'] > 0) && ($stu_dup_cp[1]['NO'] == 0)) {
                                DBQuery($sql);
                                $request_exists = false;
                                $note = _thatCourseHasBeenAddedToTheSelectedStudentsSchedules;
                            } else {
                                if ($exis_cp[1]['NO'] == 0)
                                    $stat_cp = 1;
                            }
                        } else {
                            $student_info = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME FROM students WHERE STUDENT_ID=\'' . $student_id . '\''));
                            $select_stu = $student_info[1]['FIRST_NAME'] . "&nbsp;" . $student_info[1]['LAST_NAME'];
                            $unscheduled_students[] = $select_stu;
                            // $no_seat = '' . _thereIsNoAvailableSeatsInThisPeriod . '<br>';
                            $no_seat = 'No seats available in this period for these student(s)';

                            // $no_seat .= '</DIV>' . "<A HREF=javascript:void(0) onclick='window.open(\"ForWindow.php?modname=$_REQUEST[modname]&modfunc=seats&course_period_id=" . $_SESSION['MassSchedule.php']['course_period_id'] . "\",\"\",\"scrollbars=no,status=no,screenX=500,screenY=500,resizable=no,width=500,height=200\");'style=\"text-decoration:none;\"><strong><input type=button class=btn_large value='" . _manualOverride . "'></strong></A></TD></TR>";

                            if (UserProfileID() == '0') {
                                $_REQUEST['cp_id_override'] = $_SESSION['MassSchedule.php']['course_period_id'];
                                $no_seat .= '<br><br><b>Override the available seats here</b>&nbsp;&nbsp;<i class="fa fa-arrow-circle-right" style="font-size:15px"></i>&nbsp;&nbsp;<button type="button" class="btn-undo" data-toggle="modal" data-target="#manual_override">' . _manualOverride . '</button>';
                            }
                        }

                        // ------------------------------------------------------- //
                    }
                } else {
                    # -------------------- Manual OverRide -------------------------- #

                    $check_seats = DBGet(DBQuery('SELECT  (TOTAL_SEATS - FILLED_SEATS) AS AVAILABLE_SEATS FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $_SESSION['MassSchedule.php']['course_period_id'] . '\''));
                    $check_seats = $check_seats[1]['AVAILABLE_SEATS'];

                    # -------------------- Manual OverRide -------------------------- #
                    if ($check_seats > 0) {
                        if ($_REQUEST['marking_period_id'] != '') {
                            $mark_end_qry = DBGet(DBQuery('SELECT END_DATE FROM marking_periods WHERE MARKING_PERIOD_ID =' . $_REQUEST['marking_period_id'] . ''));
                            $mark_end_date = date('d-M-Y', strtotime($mark_end_qry[1]['END_DATE']));
                        } else {
                            $mark_end_qry = DBGet(DBQuery('SELECT END_DATE FROM course_details WHERE COURSE_PERIOD_ID =' . $_SESSION['MassSchedule.php']['course_period_id'] . ''));
                            $mark_end_date = date('d-M-Y', strtotime($mark_end_qry[1]['END_DATE']));
                        }

                        $sql = 'INSERT INTO schedule (SYEAR,SCHOOL_ID,STUDENT_ID,COURSE_ID,COURSE_PERIOD_ID,MP,MARKING_PERIOD_ID,START_DATE,END_DATE,MODIFIED_DATE,MODIFIED_BY)
											values(\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'' . clean_param($student_id, PARAM_INT) . '\',\'' . $_SESSION['MassSchedule.php']['course_id'] . '\',\'' . $_SESSION['MassSchedule.php']['course_period_id'] . '\',\'' . $mp_table . '\',\'' . clean_param($_REQUEST['marking_period_id'], PARAM_INT) . '\',\'' . $start_date . '\',\'' . $mark_end_date . '\',\'' . date('Y-m-d') . '\',\'' . User('STAFF_ID') . '\')';



                        /////////////   new for manual over ride   //////////////////

                        $stat_cp = 0;
                        $exis_cp = DBGet(DBQuery('select count(*) as no from course_period_var where course_period_id=' . $_SESSION['MassSchedule.php']['course_period_id']));


                        $stu_dup_cp = DBGet(DBQuery('select count(*) as no from schedule WHERE course_period_id=' . $_SESSION['MassSchedule.php']['course_period_id'] . ' AND STUDENT_ID=' . clean_param($student_id, PARAM_INT) . ' AND SCHOOL_ID=' . UserSchool()));

                        if ($stu_dup_cp[1]['NO'] > 0) {

                            $select_dup_stu = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME FROM students WHERE STUDENT_ID=\'' . $student_id . '\''));
                            $select_stu = $select_dup_stu[1]['FIRST_NAME'] . "&nbsp;" . $select_dup_stu[1]['LAST_NAME'];
                            $dup_schedule[] = $select_stu;
                        }
                        if (($stu_dup_cp[1]['NO'] == 0)) {
                            DBQuery($sql);

                            $request_exists = false;
                            $note = _thatCourseHasBeenAddedToTheSelectedStudentsSchedules;
                        } else {
                            if ($exis_cp[1]['NO'] == 0)
                                $stat_cp = 1;
                        }




                        /////////////   new for manual over ride   //////////////////
                    } else {
                        $student_info = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME FROM students WHERE STUDENT_ID=\'' . $student_id . '\''));
                        $select_stu = $student_info[1]['FIRST_NAME'] . "&nbsp;" . $student_info[1]['LAST_NAME'];
                        $unscheduled_students[] = $select_stu;
                        $no_seat = 'No available seats in this period for these student(s)';


                        // $no_seat = '' . _thereIsNoAvailableSeatsInThisPeriod . '<br>';

                        // $no_seat .= '</DIV>' . "<A HREF=javascript:void(0) onclick='window.open(\"ForWindow.php?modname=$_REQUEST[modname]&modfunc=seats&course_period_id=" . $_REQUEST['course_period_id'] . "\",\"\",\"scrollbars=no,status=no,screenX=500,screenY=500,resizable=no,width=500,height=200\");'style=\"text-decoration:none;\"><strong><input type=button class=btn_large value='" . _manualOverride . "'></strong></A></TD></TR>";
                        if (UserProfileID() == '0') {
                            $_REQUEST['cp_id_override'] = $_SESSION['MassSchedule.php']['course_period_id'];
                            $no_seat .= '<br><br><b>Override the available seats here</b>&nbsp;&nbsp;<i class="fa fa-arrow-circle-right" style="font-size:15px"></i>&nbsp;&nbsp;<button type="button" class="btn-undo" data-toggle="modal" data-target="#manual_override">Manual Override</button>';
                        }
                    }
                }
            }
            if ($stat_cp == 1)
                echo '<div class="alert bg-danger alert-styled-left">' . _incompleteCoursePeriodCourses . '</div>';
        }

        DBQuery('DELETE FROM missing_attendance WHERE COURSE_PERIOD_ID =' . $_SESSION['MassSchedule.php']['course_period_id'] . '');



        $cps = $_SESSION['MassSchedule.php']['course_period_id'];
        //$schedule_type_check1 = DBGet(DBQuery("SELECT * FROM course_period_var WHERE COURSE_PERIOD_ID='" . $cps . "'"));

        DBQuery('DELETE FROM attendance_completed where course_period_id="' . $cps . '" and school_date>="' . $st_dt . '"');
        DBQuery("INSERT INTO missing_attendance(SCHOOL_ID,SYEAR,SCHOOL_DATE,COURSE_PERIOD_ID,PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID) 
          SELECT s.ID AS SCHOOL_ID,acc.SYEAR,acc.SCHOOL_DATE,cp.COURSE_PERIOD_ID,cpv.PERIOD_ID, IF(tra.course_period_id=cp.course_period_id AND acc.school_date<tra.assign_date =true,tra.pre_teacher_id,cp.teacher_id) AS TEACHER_ID,
         cp.SECONDARY_TEACHER_ID FROM attendance_calendar acc INNER JOIN course_periods cp ON cp.CALENDAR_ID=acc.CALENDAR_ID INNER JOIN course_period_var cpv ON cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID 
         AND (cpv.COURSE_PERIOD_DATE IS NULL AND position(substring('UMTWHFS' FROM DAYOFWEEK(acc.SCHOOL_DATE) FOR 1) IN cpv.DAYS)>0 OR cpv.COURSE_PERIOD_DATE IS NOT NULL AND cpv.COURSE_PERIOD_DATE=acc.SCHOOL_DATE) 
         INNER JOIN schools s ON s.ID=acc.SCHOOL_ID LEFT JOIN teacher_reassignment tra ON (cp.course_period_id=tra.course_period_id) INNER JOIN schedule sch ON sch.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID 
         AND sch.student_id IN(SELECT student_id FROM student_enrollment se WHERE sch.school_id=se.school_id AND sch.syear=se.syear AND start_date<=acc.school_date AND (end_date IS NULL OR end_date>=acc.school_date))
         AND (cp.MARKING_PERIOD_ID IS NOT NULL AND cp.MARKING_PERIOD_ID IN (SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE UNION SELECT MARKING_PERIOD_ID FROM school_semesters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE UNION SELECT MARKING_PERIOD_ID FROM school_quarters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE) OR (cp.MARKING_PERIOD_ID IS NULL AND acc.school_date BETWEEN cp.begin_date AND cp.end_date))
         AND sch.START_DATE<=acc.SCHOOL_DATE AND (sch.END_DATE IS NULL OR sch.END_DATE>=acc.SCHOOL_DATE ) AND cpv.DOES_ATTENDANCE='Y' AND acc.SCHOOL_DATE<CURDATE() AND cp.course_period_id=$cps 
         AND NOT EXISTS (SELECT '' FROM  attendance_completed ac WHERE ac.SCHOOL_DATE=acc.SCHOOL_DATE AND ac.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND ac.PERIOD_ID=cpv.PERIOD_ID 
         AND IF(tra.course_period_id=cp.course_period_id AND acc.school_date<=tra.assign_date =true,ac.staff_id=tra.pre_teacher_id,ac.staff_id=cp.teacher_id))  AND isDateInMarkingPeriodWorkingDates(cp.marking_period_id, acc.SCHOOL_DATE) 
         GROUP BY acc.SCHOOL_DATE,cp.COURSE_PERIOD_ID,cp.TEACHER_ID,cpv.PERIOD_ID;");

        unset($_REQUEST['modfunc']);
        unset($_SESSION['MassSchedule.php']);
    } else {
        ShowErr(_youMustChooseACourse);

        for_error_sch();
    }
}
if ($_REQUEST['modfunc'] != 'choose_course') {
    DrawBC("" . _scheduling . " > " . ProgramTitle());
    if ($_REQUEST['search_modfunc'] == 'list') {
        echo "<FORM id=sav class=\"form-horizontal\" action=Modules.php?modname=" . strip_tags(trim($_REQUEST['modname'])) . "&modfunc=save method=POST onclick=\'return validate_group_schedule();\'>";

        PopTable_wo_header('header');
        echo '<div class="row">';
        echo '<div class="col-md-4"><div class="form-group"><label class="control-label col-lg-4 text-right">' . _courseToAdd . '</label><div class="col-lg-8"><A HREF=javascript:void(0) data-toggle="modal" data-target="#modal_default" onClick="cleanModal(\"course_modal\");cleanModal(\"cp_modal\");"><i class="icon-menu6 pull-right m-t-10"></i><DIV id=course_div class="form-control m-b-5" readonly="readonly">';
        if ($_SESSION['MassSchedule.php']) {
            $course_title = DBGet(DBQuery('SELECT TITLE FROM courses WHERE COURSE_ID=\'' . $_SESSION['MassSchedule.php']['course_id'] . '\''));
            $course_title = $course_title[1]['TITLE'];
            $period_title = DBGet(DBQuery('SELECT TITLE FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $_SESSION['MassSchedule.php']['course_period_id'] . '\''));
            $period_title = $period_title[1]['TITLE'];

            echo "$course_title - " . strip_tags(trim($_REQUEST['course_weight'])) . "<BR>$period_title";
        }

        echo '<span class="text-grey">' . _clickToSelect . '</span></div></A></div></div></div>';

        echo '<div class="col-md-4"><div class="form-group"><label class="control-label col-lg-4 text-right">' . _startDate . '</label><div class="col-lg-8">' . DateInputAY(date('Y-m-d'), 'start', 1) . '</div></div></div>';
        echo '<div class="col-md-4"><div class="form-group"><label class="control-label col-lg-4 text-right">' . _markingPeriod . '</label><div class="col-lg-8">';
        //        $years_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,NULL AS SEMESTER_ID FROM school_years WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
        //        $semesters_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,NULL AS SEMESTER_ID FROM school_semesters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER'));
        //        $quarters_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,SEMESTER_ID FROM school_quarters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER'));

        //        echo '<SELECT class="form-control" disabled  id=marking_period><OPTION value=' . $years_RET[1]['MARKING_PERIOD_ID'] . '>' . $years_RET[1]['TITLE'] . '</OPTION>';
        //        echo "<OPTION value='' selected>N/A</OPTION>";
        //        foreach ($semesters_RET as $mp)
        //            echo "<OPTION value=$mp[MARKING_PERIOD_ID]>" . $mp['TITLE'] . '</OPTION>';
        //        foreach ($quarters_RET as $mp)
        //            echo "<OPTION value=$mp[MARKING_PERIOD_ID]>" . $mp['TITLE'] . '</OPTION>';
        //        echo '</SELECT>';
        echo '<div class="form-control" id="showTitle" ></div>';
        echo '<input type=hidden name=marking_period_id id=val_marking_period_id />';
        echo '</div></div></div>';
        echo '</div>'; //.row
        PopTable_wo_header('footer');
    }

    if ($note) {
        echo '<div class="alert bg-success alert-styled-left">' . $note . '</div>';
    }
    if ($gender_conflict) {
        echo '<div class="alert alert-warning alert-styled-left">';
        echo '<button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">Close</span></button>';
        echo $gender_conflict . ' ' . _haveGenderClash . '';
        echo '</div>';
    }
    if ($drop_stu) {
        echo '<div class="alert alert-warning alert-styled-left">';
        echo '<button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">' . _close . '</span></button>';
        echo $drop_stu . '' . _haveDroppedFromSchool . '';
        echo '</div>';
    }
    if ($parent_res) {
        echo '<div class="alert alert-warning alert-styled-left">';
        echo '<button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">Close</span></button>';
        echo $parent_res . '' . _haveParentCourseRestriction . '';
        echo '</div>';
    }
    if ($start_date_q_clash) {
        echo '<div class="alert alert-warning alert-styled-left">';
        echo '<button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">' . _close . '</span></button>';
        echo $start_date_q_clash;
        echo '</div>';
    }

    if ($sche_date_err) {
        echo '<div class="alert alert-warning alert-styled-left">';
        echo '<button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">' . _close . '</span></button>';
        echo $sche_date_err;
        echo '</div>';
    }
    if ($stu_sd_err) {
        if ($stu_sd_err_count > 1) {
            $put = 'their';
        } else {
            $put = 'his/her';
        }
        echo '<div class="alert alert-warning alert-styled-left">';
        echo '<button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">' . _close . '</span></button>';
        echo $stu_sd_err . ' ' . _cannotBeScheduledBefore . ' ' . $put . ' ' . _enrolledDate . '';
        echo '</div>';
        unset($stu_sd_err);
        unset($stu_s_date_err_n);
    }
    if ($period_res) {
        if ($period_res_cnt == 1)
            $singu = 'This student is';
        if ($period_res_cnt > 1)
            $singu = 'These students are';
        echo '<div class="alert alert-warning alert-styled-left">';
        echo '<button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">' . _close . '</span></button>';
        echo implode(', ', $period_res) . ' - ' . $singu . ' ' . _alreadyScheduledInThatPeriod . '';
        echo '</div>';
    }
    if ($time_clash) {
        echo '<div class="alert alert-warning alert-styled-left">';
        echo '<button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">' . _close . '</span></button>';
        echo $time_clash . ' ' . _haveAPeriodTimeClash . '';
        echo '</div>';
    }
    if ($check_seats <= 0 && $no_seat) {
        echo '<div class="alert alert-warning alert-styled-left">';
        echo '<button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">' . _close . '</span></button>';
        echo implode(', ', $unscheduled_students) . ' - ' . $no_seat;
        echo '</div>';
    } elseif ($request_exists) {
        echo '<div class="alert alert-warning alert-styled-left">';
        echo '<button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">' . _close . '</span></button>';
        echo $request_clash . ' ' . _alreadyHaveUnscheduledRequests . '';
        echo '</div>';
    }
    if ($dup_schedule) {
        if (count($dup_schedule) > 1)
            $singu = ' are';
        if (count($dup_schedule) == 1)
            $singu = ' is';
        echo '<div class="alert alert-warning alert-styled-left">';
        echo '<button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">' . _close . '</span></button>';
        echo implode(', ', $dup_schedule) . $singu . ' ' . _alreadyScheduledInThatCourse . '';
        echo '</div>';
    }
}
if (!$_REQUEST['modfunc']) {
    if ($_REQUEST['search_modfunc'] != 'list')
        unset($_SESSION['MassSchedule.php']);
    $extra['link'] = array('FULL_NAME' => false);
    $extra['SELECT'] = ",Concat(NULL) AS CHECKBOX";
    $extra['functions'] = array('CHECKBOX' => '_makeChooseCheckbox');
    // $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name="controller" onclick="checkAll(this.form,this.form.controller.checked,\'unused\');"><A>');
    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name="controller" onclick="checkAllDtMod(this,\'student\');"><A>');
    $extra['new'] = true;

    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-lg-6">';
    Widgets('course');
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '<div class="col-lg-6">';
    Widgets('request');
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '</div>'; //.row

    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-lg-6">';
    Widgets('activity');
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '</div>'; //.row


    Search_GroupSchedule('student_id', $extra);
    // Search('student_id', $extra);

    if ($_REQUEST['search_modfunc'] == 'list') {
        if ($_SESSION['count_stu'] != 0)
            echo '<div class="text-right">' . SubmitButton('' . _addCourseToSelectedStudents . '', '', 'class="btn btn-primary" onclick="self_disable(this);" ') . '</div>';

        echo "</FORM>";
    }
}

//if ($_REQUEST['modfunc'] == 'choose_course') {
//
//    if (!$_REQUEST['course_period_id'])
//        include 'modules/scheduling/CoursesforWindow.php';
//    else {
//        $_SESSION['MassSchedule.php']['subject_id'] = $_REQUEST['subject_id'];
//        $_SESSION['MassSchedule.php']['course_id'] = $_REQUEST['course_id'];
//        $_SESSION['MassSchedule.php']['course_period_id'] = $_REQUEST['course_period_id'];
//
//        $course_title = DBGet(DBQuery('SELECT TITLE FROM courses WHERE COURSE_ID=\'' . $_SESSION['MassSchedule.php']['course_id'] . '\''));
//        $course_title = $course_title[1]['TITLE'];
//        $period_title_RET = DBGet(DBQuery('SELECT TITLE,MARKING_PERIOD_ID,GENDER_RESTRICTION,BEGIN_DATE,END_DATE FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $_SESSION['MassSchedule.php']['course_period_id'] . '\''));
//        $period_title = $period_title_RET[1]['TITLE'];
//        $mperiod = ($period_title_RET[1]['MARKING_PERIOD_ID'] != '' ? $period_title_RET[1]['MARKING_PERIOD_ID'] : GetMPId('FY'));
////               
//        if ($period_title_RET[1]['MARKING_PERIOD_ID'] == NULL)
//            $true = 'true';
//        else {
//            $true = '';
//        }
//
//        $gender_res = $period_title_RET[1]['GENDER_RESTRICTION'];
//        $_SESSION['MassSchedule.php']['gender'] = $gender_res;
//        $period_title = str_replace('"', '\"', $period_title);
//        if ($gender_res == 'N')
//            echo "<script language=javascript>opener.document.getElementById(\"course_div\").innerHTML = \"<span title=$course_title - $period_title>$course_title - $period_title</span>\";opener.document.getElementById(\"marking_period\").value=\"$mperiod\";opener.document.getElementById(\"marking_period\").disabled=\"$true\"; window.close();</script>";
//        else
//            echo "<script language=javascript>opener.document.getElementById(\"course_div\").innerHTML = \"<span title=$course_title - $period_title - Gender : ".($gender_res == 'M' ? 'Male' : 'Female').">$course_title - $period_title - Gender : " . ($gender_res == 'M' ? 'Male' : 'Female') . "</span> \";opener.document.getElementById(\"marking_period\").value=\"$mperiod\"; window.close();</script>";
//    }
//}

// if ($_REQUEST['modfunc'] == 'seats') {

//     if ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TOTAL_SEATS'] != '' && $_REQUEST['update']) {
//     }
//     if ($_REQUEST['course_period_id']) {
//         $sql = DBGet(DBQuery('SELECT TOTAL_SEATS FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $_REQUEST['course_period_id'] . '\''));
//         $RET = $sql[1];
//     }
//     echo '<div align=center>';
//     echo '<form name=update_seats id=update_seats method=POST action=ForWindow.php?modname=' . strip_tags(trim($_REQUEST['modname'])) . '&modfunc=seatso&course_period_id=' . $_REQUEST['course_period_id'] . '&update=true>';
//     echo '<TABLE><TR>';
//     echo '<td colspan=2 align=center><b>' . _clickOnTheNumberOfSeatsToEditAndClick . ' ' . _update . '</b></td></tr><tr><td colspan=2 align=center><table><tr><td>' . _clickOnTheNumberOfSeatsToEditAndClick . '</td><td>:</td><td>' . TextInput($RET['TOTAL_SEATS'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][TOTAL_SEATS]', '', 'size=10 class=form-control maxlength=5') . '</td></tr></table>';
//     echo '</TR><tr><td colspan=2 align=center><input type=submit value=' . _update . ' class="btn btn-primary" onclick="return check_update_seat(' . $_REQUEST['course_period_id'] . '); "></td></tr></TABLE>';
//     echo '</form>';
//     echo '</div>';
// }


/*
 * Course Period Modal
 */
echo '<div id="modal_default" class="modal fade">';
echo '<div class="modal-dialog modal-lg">';
echo '<div class="modal-content">';

echo '<div class="modal-header">';
echo '<button type="button" class="close" data-dismiss="modal">×</button>';
echo '<h5 class="modal-title">' . _chooseCourse . '</h5>';
echo '</div>'; //.modal-header

echo '<div class="modal-body">';
echo '<div id="conf_div" class="text-center"></div>';
echo '<div class="row" id="resp_table">';
echo '<div class="col-md-4">';
$sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
$QI = DBQuery($sql);
$subjects_RET = DBGet($QI);

echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' ' . _subjectWas : ' ' . _subjectsWere) . ' ' . _found . '.</h6>';
if (count($subjects_RET) > 0) {
    echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>' . _subject . '</th></tr></thead>';
    echo '<tbody>';
    foreach ($subjects_RET as $val) {
        //MassScheduleModal
        echo '<tr><td><a href=javascript:void(0); onclick="MassScheduleModal(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
    }
    echo '</tbody>';
    echo '</table>';
}
echo '</div>';
echo '<div class="col-md-4"><div id="course_modal"></div></div>';
echo '<div class="col-md-4"><div id="cp_modal"></div></div>';
echo '</div>'; //.row
echo '</div>'; //.modal-body

echo '</div>'; //.modal-content
echo '</div>'; //.modal-dialog
echo '</div>'; //.modal




/*
 * Course Modal
 */
echo '<div id="modal_default_request" class="modal fade">';
echo '<div class="modal-dialog">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<button type="button" class="close" data-dismiss="modal">×</button>';
echo '<h5 class="modal-title">' . _chooseCourse . '</h5>';
echo '</div>';

echo '<div class="modal-body">';
echo '<div id="conf_div" class="text-center"></div>';
echo '<div class="row" id="resp_table">';
echo '<div class="col-md-6">';
$sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
$QI = DBQuery($sql);
$subjects_RET = DBGet($QI);

echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' ' . _subjectWas : ' ' . _subjectsWere) . '' . _found . '</h6>';
if (count($subjects_RET) > 0) {
    echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>' . _subject . '</th></tr></thead>';
    echo '<tbody>';
    foreach ($subjects_RET as $val) {
        echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearchRequest(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
    }
    echo '</tbody>';
    echo '</table>';
}
echo '</div>';
echo '<div class="col-md-6"><div id="course_modal_request"></div></div>';
echo '</div>'; //.row
echo '</div>'; //.modal-body

echo '</div>'; //.modal-content
echo '</div>'; //.modal-dialog
echo '</div>'; //.modal


/*
 * Manual Overide modal
 */
echo '<div id="manual_override" class="modal fade">';
echo '<div class="modal-dialog">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<button type="button" class="close" data-dismiss="modal">×</button>';
echo '<h5 class="modal-title">Manual Override</h5>';
echo '</div>';

echo '<form name=update_seats id=update_seats method=POST action=Modules.php?modname=' . strip_tags(trim($_REQUEST['modname'])) . '&modfunc=seatso&course_period_id=' . $_REQUEST['cp_id_override'] . '&update=true>';
echo '<div class="modal-body">';
// echo '<div id="conf_div" class="text-center"></div>';
if ($_REQUEST['cp_id_override']) {
    $sql = DBGet(DBQuery('SELECT * FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $_REQUEST['cp_id_override'] . '\''));
    $RET = $sql[1];
}
echo '<div id=err_message ></div><br/>';

echo '<div class="row">';
echo '<div class="form-group">';
echo '<div class="col-md-4"><label class="control-label">Course Period</label></div>';
echo '<div class="col-md-8" class="form-control">' . $RET['TITLE'] . '</div>';
echo '</div>'; //.form-group
echo '</div>'; //.row

echo '<div class="row">';
echo '<div class="form-group">';
echo '<div class="col-md-4"><label class="control-label">Total Available Seats</label></div>';
echo '<div class="col-md-8" class="form-control">' . $RET['TOTAL_SEATS'] . '</div>';
echo '</div>'; //.form-group
echo '</div>'; //.row

echo '<div class="row">';
echo '<div class="form-group">';
echo '<div class="col-md-4"><label class="control-label">No. of students left to schedule</label></div>';
echo '<div class="col-md-8" class="form-control">' . count($unscheduled_students) . '</div>';
echo '</div>'; //.form-group
echo '</div>'; //.row

echo '<div class="row">';
echo '<div class="form-group">';
echo '<div class="col-md-4"><label class="control-label">New Available Seats</label></div>';
echo '<div class="col-md-8">' . TextInput('', 'tables[course_periods][' . $_REQUEST['cp_id_override'] . '][TOTAL_SEATS]', '', 'size=10 class="form-control" maxlength=5') . '</div>';
echo '</div>'; //.form-group
echo '</div>'; //.row

echo '</div>'; //.modal-body

echo '<div class="modal-footer">
        <button type="submit" class="btn btn-primary" onclick="return check_update_seat(' . $RET['TOTAL_SEATS'] . ',' . $_REQUEST['cp_id_override'] . ',' . count($unscheduled_students) . ');">Save changes</button>
    </div>';
echo '</form>';
echo '</div>'; //.modal-content
echo '</div>'; //.modal-dialog
echo '</div>'; //.modal

function _makeChooseCheckbox($value, $title)
{
    global $THIS_RET;


    //    return "<input name=unused[$THIS_RET[STUDENT_ID]]  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckbox(\"values[STUDENTS][$THIS_RET[STUDENT_ID]]\",this,$THIS_RET[STUDENT_ID]);' />";

    return "<input name=unused[$THIS_RET[STUDENT_ID]] value=" . $THIS_RET['STUDENT_ID'] . "  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"student[$THIS_RET[STUDENT_ID]]\",this,$THIS_RET[STUDENT_ID]);' />";
}

function _str_split($str)
{
    $ret = array();
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++)
        $ret[] = substr($str, $i, 1);
    return $ret;
}
