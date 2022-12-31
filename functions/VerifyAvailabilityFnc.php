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
//================course varification=============================
function VerifyFixedSchedule($columns, $columns_var, $update = false)
{

    $qr_teachers = DBGet(DBQuery('select TEACHER_ID,SECONDARY_TEACHER_ID from course_periods where course_period_id=\'' . $_REQUEST['course_period_id'] . '\''));
    $teacher = ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TEACHER_ID'] != '' ? $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TEACHER_ID'] : $qr_teachers[1]['TEACHER_ID']);
    $secteacher = ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['SECONDARY_TEACHER_ID'] != '' ? $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['SECONDARY_TEACHER_ID'] : $qr_teachers[1]['SECONDARY_TEACHER_ID']);
    //    $secteacher=$qr_teachers[1]['SECONDARY_TEACHER_ID'];
    if ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TEACHER_ID'] != '') $all_teacher = $teacher . ($secteacher != '' ? ',' . $secteacher : '');
    elseif ($secteacher != '') $all_teacher = ($secteacher != '' ? $secteacher : '');
    else $all_teacher = $teacher;
    //    $all_teacher=$teacher.($secteacher!=''?','.$secteacher:'');
    $mp_id = $columns['MARKING_PERIOD_ID'];
    $start_date = $columns['BEGIN_DATE'];
    $end_date = $columns['END_DATE'];
    $days = $columns_var['DAYS'];
    $period = $columns_var['PERIOD_ID'];
    $room = $columns_var['ROOM_ID'];
    $min_start_dt_chk_qr = DBGet(DBQuery("SELECT min(start_date) as start_date from  staff_school_relationship where staff_id in($all_teacher)"));
    $min_start_dt_chk_pr = DBGet(DBQuery("SELECT min(start_date) as start_date from  staff_school_relationship where staff_id in($teacher)"));

    if ($update) {
        $check_cp = DBGet(DBQuery("SELECT * FROM course_periods cp,course_period_var cpv WHERE cp.course_period_id=cpv.course_period_id AND cp.course_period_id='" . $columns['COURSE_PERIOD_ID'] . "'"));

        $check_cp = $check_cp[1];

        if ($check_cp['TEACHER_ID'] == $teacher && $check_cp['SECONDARY_TEACHER_ID'] == $secteacher && $check_cp['BEGIN_DATE'] == $start_date && $check_cp['END_DATE'] == $end_date && $check_cp['DAYS'] == $days && $check_cp['PERIOD_ID'] == $period && $check_cp['ROOM_ID'] == $room) {
            return true;
        }
    }
    if ($columns_var['DAYS'] == '' || $columns_var['PERIOD_ID'] == '' || $columns_var['ROOM_ID'] == '') {
        return 'Input valid details';
    }
    $period_time = DBGet(DBQuery("SELECT START_TIME, END_TIME ,IGNORE_SCHEDULING FROM school_periods WHERE period_id={$period}"));
    $period_time = $period_time[1];
    $start_time = $period_time['START_TIME'];
    $end_time = $period_time['END_TIME'];
    $pre_ing = $period_time['IGNORE_SCHEDULING'];
    if ($mp_id != '') {
        $mp_date = DBGet(DBQuery("SELECT START_DATE,END_DATE FROM marking_periods where marking_period_id ={$mp_id}"));
        $mp_date = $mp_date[1];
        $end_date = $mp_date['END_DATE'];
        $start_date = $mp_date['START_DATE'];
    }
    // $mp_append_sql = " AND begin_date<='$end_date' AND '$start_date'<=end_date AND IF (schedule_type='BLOCKED',course_period_date BETWEEN '$start_date' AND '$end_date','1')";
    $mp_append_sql = " AND begin_date >= '$start_date' AND end_date <= '$end_date'  AND IF (schedule_type='BLOCKED',course_period_date BETWEEN '$start_date' AND '$end_date','1')";
    // $period_append_sql = " AND period_id IN(SELECT period_id from school_periods WHERE start_time<='$end_time' AND '$start_time'<=end_time)";
    $period_append_sql = " AND period_id IN(SELECT period_id from school_periods WHERE start_time >= '$start_time' AND end_time <= '$end_time')";

    $days_append_sql = ' AND (';
    $days_room_append_sql = ' AND (';
    $days_arr = str_split($days);
    foreach ($days_arr as $day) {
        $days_append_sql .= "DAYS LIKE '%$day%' OR ";
        $days_room_append_sql .= "(DAYS LIKE '%$day%' AND room_id=$room) OR ";
    }
    $days_append_sql = substr($days_append_sql, 0, -4) . ')';
    $days_room_append_sql = substr($days_room_append_sql, 0, -4) . ')';
    if ($update) $cp_id = " AND cp.COURSE_PERIOD_ID!={$columns['COURSE_PERIOD_ID']}";
    else $cp_id = '';
    if ($pre_ing != 'Y') {
        $cp_RET = DBGet(DBQuery("SELECT cp.COURSE_PERIOD_ID FROM course_periods cp,course_period_var cpv WHERE cp.course_period_id=cpv.course_period_id AND (secondary_teacher_id IN ($all_teacher) OR teacher_id IN ($all_teacher)){$mp_append_sql}{$period_append_sql}{$days_append_sql}{$cp_id}"));
        if ($cp_RET) return 'Teacher Not Available';
        else return true;
    } elseif (strtotime($min_start_dt_chk_pr[1]['START_DATE']) > strtotime($start_date)) return 'Teacher\'s start date cannot be after course period\'s start date.';
    else {
        if ($pre_ing != 'Y') {
            $room_RET = DBGet(DBQuery("SELECT cp.COURSE_PERIOD_ID FROM course_periods cp,course_period_var cpv WHERE cp.course_period_id=cpv.course_period_id{$mp_append_sql}{$period_append_sql}{$days_room_append_sql}{$cp_id}"));
            if ($room_RET && $_REQUEST['tables']['course_period_var'][$_REQUEST['course_period_id']]['ROOM_ID'] != '') {
                return 'Room Not Available';
            }
        }
        return true;
    }
}

function VerifyVariableSchedule($columns)
{
    //    $teacher=$columns['TEACHER_ID'];
    //    $secteacher=$columns['SECONDARY_TEACHER_ID'];
    //     if($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TEACHER_ID']!='')
    //     $all_teacher=$teacher.($secteacher!=''?','.$secteacher:'');
    //    else
    //        $all_teacher=($secteacher!=''?$secteacher:'');
    $flag = 0;
    $teacher = ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TEACHER_ID'] != '' ? $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TEACHER_ID'] : $columns['TEACHER_ID']);
    $secteacher = ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['SECONDARY_TEACHER_ID'] != '' ? $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['SECONDARY_TEACHER_ID'] : $columns['SECONDARY_TEACHER_ID']);
    //    $secteacher=$qr_teachers[1]['SECONDARY_TEACHER_ID'];
    if ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TEACHER_ID'] != '') $all_teacher = $teacher . ($secteacher != '' ? ',' . $secteacher : '');
    elseif ($secteacher != '') $all_teacher = ($secteacher != '' ? $secteacher : '');
    else $all_teacher = $teacher;

    //    $all_teacher=$teacher.($secteacher!=''?','.$secteacher:'');
    $mp_id = $columns['MARKING_PERIOD_ID'];
    $start_date = $columns['BEGIN_DATE'];
    $end_date = $columns['END_DATE'];
    $min_start_dt_chk_qr = DBGet(DBQuery("SELECT min(start_date) as start_date from  staff_school_relationship where staff_id in($all_teacher)"));
    $min_start_dt_chk_pr = DBGet(DBQuery("SELECT min(start_date) as start_date from  staff_school_relationship where staff_id in($teacher)"));

    if (!$_REQUEST['course_period_variable']) return 'Please input valid data';
    if ($mp_id != '') {
        $mp_date = DBGet(DBQuery("SELECT START_DATE,END_DATE FROM marking_periods where marking_period_id ={$mp_id}"));
        $mp_date = $mp_date[1];
        $end_date = $mp_date['END_DATE'];
        $start_date = $mp_date['START_DATE'];
    }
    // $mp_append_sql = " AND begin_date<='$end_date' AND '$start_date'<=end_date";
    $mp_append_sql = " AND begin_date >= '$start_date' AND end_date <= '$end_date'";
    $days_append_sql = ' AND (';
    $days_room_append_sql = ' AND (';
    $err_msg = '';
    foreach ($_REQUEST['course_period_variable'] as $cp_id => $days) {

        if ($days['DAYS'] == '' || ($days['PERIOD_ID'] == '' || $days['ROOM_ID'] == '')) return 'Please input valid data';
        $period_time = DBGet(DBQuery("SELECT TITLE,IGNORE_SCHEDULING FROM school_periods WHERE period_id={$days['PERIOD_ID']}"));
        $period_time = $period_time[1];

        $pre_ing = $period_time['IGNORE_SCHEDULING'];
        //            $same_periods=DBGet(DBQuery("SELECT cp.COURSE_PERIOD_ID FROM course_periods cp,course_period_var cpv WHERE cp.course_period_id=cpv.course_period_id AND (secondary_teacher_id IN ($all_teacher) OR teacher_id IN ($all_teacher)){$mp_append_sql} AND cpv.ROOM_ID={$days['ROOM_ID']} AND cpv.PERIOD_ID={$days['PERIOD_ID']} AND days LIKE '%$days[DAYS]%'"));
        //            if($same_periods)
        //            {
        //                $err_msg.='Teacher Already Scheduled to '.$period_time['TITLE'].',';
        //                continue;
        //            }
        if ($pre_ing != 'Y') {
            $flag = 1;
            if ($cp_id == 'new') {
                // $days_append_sql .= "(days like '%$days[DAYS]%' AND start_time<='" . $days['n']['END_TIME'] . "' AND '" . $days['n']['START_TIME'] . "'<=end_time) OR ";
                $days_append_sql .= "(days like '%$days[DAYS]%' AND start_time >= '" . $days['n']['START_TIME'] . "' AND end_time <= '" . $days['n']['END_TIME'] . "') OR ";

                // $days_room_append_sql .= "(days like '%$days[DAYS]%' AND room_id=$days[ROOM_ID] AND start_time<='" . $days['n']['END_TIME'] . "' AND '" . $days['n']['START_TIME'] . "'<=end_time) OR ";
                $days_room_append_sql .= "(days like '%$days[DAYS]%' AND room_id=$days[ROOM_ID] AND start_time >= '" . $days['n']['START_TIME'] . "' AND end_time <= '" . $days['n']['END_TIME'] . "') OR ";
            } else {
                // $days_append_sql .= "(days like '%$days[DAYS]%' AND start_time<='$days[END_TIME]' AND '$days[START_TIME]'<=end_time) OR ";
                $days_append_sql .= "(days like '%$days[DAYS]%' AND start_time >= '$days[START_TIME]' AND end_time <= '$days[END_TIME]') OR ";

                // $days_room_append_sql .= "(days like '%$days[DAYS]%' AND room_id=$days[ROOM_ID] AND start_time<='$days[END_TIME]' AND '$days[START_TIME]'<=end_time) OR ";
                $days_room_append_sql .= "(days like '%$days[DAYS]%' AND room_id=$days[ROOM_ID] AND start_time >= '$days[START_TIME]' AND end_time <= '$days[END_TIME]') OR ";
            }
        }
    }
    $days_append_sql = substr($days_append_sql, 0, -4) . ')';
    $days_room_append_sql = substr($days_room_append_sql, 0, -4) . ')';
    if ($err_msg != '') {
        return substr($err_msg, 0, -1);
    }
    if ($flag == 1) {
        $sql_ch = "SELECT cp.COURSE_PERIOD_ID FROM course_periods  cp LEFT JOIN course_period_var cpv ON (cp.course_period_id=cpv.course_period_id) WHERE (secondary_teacher_id IN ($all_teacher) OR teacher_id IN ($all_teacher)){$mp_append_sql}{$days_append_sql}";

        $cp_RET = DBGet(DBQuery($sql_ch));
        if ($cp_RET) return 'Teacher Not Available';
        else return true;
    } elseif (strtotime($min_start_dt_chk_pr[1]['START_DATE']) > strtotime($start_date)) return 'Teacher\'s start date cannot be after course period\'s start date.';
    elseif ($day_RET) return 'Day Not Available';
    else {
        if ($flag == 1) {
            $room_RET = DBGet(DBQuery("SELECT cp.COURSE_PERIOD_ID FROM course_periods  cp LEFT JOIN course_period_var cpv ON (cp.course_period_id=cpv.course_period_id) WHERE 1{$mp_append_sql}{$days_room_append_sql}"));
            if ($room_RET) {
                return 'Room Not Available';
            }
        }
        return true;
    }
}

function VerifyVariableSchedule_Update($columns)
{

    //    $teacher=$columns['TEACHER_ID'];
    //    $secteacher=$columns['SECONDARY_TEACHER_ID'];
    //     if($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TEACHER_ID']!='')
    //     {
    //     $all_teacher=$teacher.($secteacher!=''?','.$secteacher:'');
    //     }
    //    else
    //        //$all_teacher=($secteacher!=''?$secteacher:'');
    //    $all_teacher=$teacher.($secteacher!=''?','.$secteacher:'');


    $teacher = ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TEACHER_ID'] != '' ? $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TEACHER_ID'] : $columns['TEACHER_ID']);
    $secteacher = ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['SECONDARY_TEACHER_ID'] != '' ? $_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['SECONDARY_TEACHER_ID'] : $columns['SECONDARY_TEACHER_ID']);
    //    $secteacher=$qr_teachers[1]['SECONDARY_TEACHER_ID'];
    if ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TEACHER_ID'] != '') $all_teacher = $teacher . ($secteacher != '' ? ',' . $secteacher : '');
    elseif ($secteacher != '') $all_teacher = ($secteacher != '' ? $secteacher : '');
    else $all_teacher = $teacher;

    $mp_id = $columns['MARKING_PERIOD_ID'];
    $start_date = $columns['BEGIN_DATE'];
    $end_date = $columns['END_DATE'];
    $id = $columns['ID'];
    $per_id = $columns['PERIOD_ID'];
    $period_time = DBGet(DBQuery("SELECT IGNORE_SCHEDULING FROM school_periods WHERE period_id={$per_id}"));
    $period_time = $period_time[1];
    $pre_ing = $period_time['IGNORE_SCHEDULING'];
    $min_start_dt_chk_qr = DBGet(DBQuery("SELECT min(start_date) as start_date from  staff_school_relationship where staff_id in($all_teacher)"));
    $min_start_dt_chk_pr = DBGet(DBQuery("SELECT min(start_date) as start_date from  staff_school_relationship where staff_id in($teacher)"));

    if (!$_REQUEST['course_period_variable'] || $columns['PERIOD_ID'] = '' || $columns['ROOM_ID'] == '') return 'Please input valid data';
    if ($columns['CP_SECTION'] == 'cpv') {
        if ($mp_id != '') {
            $mp_date = DBGet(DBQuery("SELECT START_DATE,END_DATE FROM marking_periods where marking_period_id ={$mp_id}"));
            $mp_date = $mp_date[1];
            $end_date = $mp_date['END_DATE'];
            $start_date = $mp_date['START_DATE'];
        }
        // $mp_append_sql = " AND begin_date<='$end_date' AND '$start_date'<=end_date";
        $mp_append_sql = " AND begin_date >= '$start_date' AND end_date <= '$end_date'";

        $days_append_sql = ' AND (';
        $days_room_append_sql = ' AND (';

        if ($columns['SELECT_DAYS'] == '' && ($columns['PERIOD_ID'] == '' || $columns['ROOM_ID'] == '')) return 'Please input valid data';

        // $days_append_sql .= "(days like '%$columns[DAYS]%' AND start_time<='$columns[END_TIME]' AND '$columns[START_TIME]'<=end_time) OR ";
        $days_append_sql .= "(days like '%$columns[DAYS]%' AND start_time >= '$columns[START_TIME]' AND end_time <= '$columns[END_TIME]') OR ";

        // $days_room_append_sql .= "(days like '%$columns[DAYS]%' AND room_id=$columns[ROOM_ID] AND start_time<='$columns[END_TIME]' AND '$columns[START_TIME]'<=end_time) OR ";
        $days_room_append_sql .= "(days like '%$columns[DAYS]%' AND room_id=$columns[ROOM_ID] AND start_time >= '$columns[START_TIME]' AND end_time <= '$columns[END_TIME]') OR ";

        $days_append_sql = substr($days_append_sql, 0, -4) . ')';
        $days_room_append_sql = substr($days_room_append_sql, 0, -4) . ')';

        // $sql_same_period = "SELECT cp.COURSE_PERIOD_ID FROM course_periods cp,course_period_var cpv  WHERE  days = '" . $columns[DAYS] . "' AND period_id='" . $per_id . "' AND ROOM_ID=$columns[ROOM_ID] AND TEACHER_ID=$teacher {$mp_append_sql} AND cpv.course_period_id = cp.course_period_id AND cp.COURSE_PERIOD_ID!={$_REQUEST['cp_id']}";
        // $same_cp_RET = DBGet(DBQuery($sql_same_period));
        // if (count($same_cp_RET) > 0) {
        //     return 'Same meeting days already exists.';
        // } else {
        //     $sql_op="SELECT cp.COURSE_PERIOD_ID  FROM course_periods  cp LEFT JOIN course_period_var cpv ON (cp.course_period_id=cpv.course_period_id) WHERE cpv.PERIOD_ID='".$per_id."'  AND (secondary_teacher_id IN ($all_teacher) OR teacher_id IN ($all_teacher)) AND cp.COURSE_PERIOD_ID!={$_REQUEST['cp_id']}{$mp_append_sql}{$days_append_sql}";
        if ($pre_ing != 'Y') {
            $sql_op = "SELECT cp.COURSE_PERIOD_ID  FROM course_periods  cp LEFT JOIN course_period_var cpv ON (cp.course_period_id=cpv.course_period_id) WHERE (secondary_teacher_id IN ($all_teacher) OR teacher_id IN ($all_teacher)){$mp_append_sql}{$days_append_sql}";

            $cp_RET = DBGet(DBQuery($sql_op));
            if (count($cp_RET) > 0) return 'Teacher Not Available';
            else return true;
        } elseif (strtotime($min_start_dt_chk_pr[1]['START_DATE']) > strtotime($start_date)) return 'Teacher\'s start date cannot be after course period\'s start date.';
        else {
            if ($pre_ing != 'Y') {
                $room_RET = DBGet(DBQuery("SELECT cp.COURSE_PERIOD_ID FROM course_periods  cp LEFT JOIN course_period_var cpv ON (cp.course_period_id=cpv.course_period_id) WHERE 1{$mp_append_sql}{$days_room_append_sql} AND cp.course_period_id!={$columns['COURSE_PERIOD_ID']}"));
                if ($room_RET) {
                    return 'Room Not Available';
                }
            }
            return true;
        }
        //}

    } else {
        if ($mp_id != '') {
            $mp_date = DBGet(DBQuery("SELECT START_DATE,END_DATE FROM marking_periods where marking_period_id ={$mp_id}"));
            $mp_date = $mp_date[1];
            $end_date = $mp_date['END_DATE'];
            $start_date = $mp_date['START_DATE'];
        }
        // $mp_append_sql = " AND begin_date<='$end_date' AND '$start_date'<=end_date";
        $mp_append_sql = " AND begin_date >= '$start_date' AND end_date <= '$end_date'";

        if ($columns[DAYS] != 'n' && $columns[END_TIME] != '' && $columns[START_TIME] != '') {
            $days_append_sql = ' AND (';

            // $days_append_sql .= "(days like '%$columns[DAYS]%' AND start_time<='$columns[END_TIME]' AND '$columns[START_TIME]'<=end_time) OR ";
            $days_append_sql .= "(days like '%$columns[DAYS]%' AND start_time >= '$columns[START_TIME]' AND end_time <= '$columns[END_TIME]') OR ";

            $days_append_sql = substr($days_append_sql, 0, -4) . ')';
        }
        if ($pre_ing != 'Y') {
            $cp_RET = DBGet(DBQuery("SELECT cp.COURSE_PERIOD_ID FROM course_periods  cp LEFT JOIN course_period_var cpv ON (cp.course_period_id=cpv.course_period_id) WHERE (secondary_teacher_id IN ($all_teacher) OR teacher_id IN ($all_teacher)){$mp_append_sql}{$days_append_sql} AND cpv.PERIOD_ID='" . $per_id . "' AND cpv.DAYS='" . $columns['SELECT_DAYS'] . "' AND cpv.ROOM_ID='" . $columns['ROOM_ID'] . "' AND cp.COURSE_PERIOD_ID!={$_REQUEST['cp_id']}"));
            if ($cp_RET) return "Teacher Not Available";
            else return true;
        } elseif (strtotime($min_start_dt_chk_pr[1]['START_DATE']) > strtotime($start_date)) return 'Teacher\'s start date cannot be after course period\'s start date.';
        else return true;
    }
}

function VerifyBlockedSchedule($columns, $course_period_id, $sec, $edit = false)
{
    if ($course_period_id != 'new') {
        $cp_det_RET = DBGet(DBQuery("SELECT * FROM course_periods WHERE course_period_id=$course_period_id"));

        $cp_det_RET = $cp_det_RET[1];
        $teacher = $cp_det_RET['TEACHER_ID'];
        $secteacher = $cp_det_RET['SECONDARY_TEACHER_ID'];
        $all_teacher = $teacher . ($secteacher != '' ? $secteacher : '');
    } else {

        if ($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TEACHER_ID'] != '') $all_teacher = $teacher . ($secteacher != '' ? ',' . $secteacher : '');
        else $all_teacher = ($secteacher != '' ? $secteacher : '');
    }

    //        $all_teacher=$teacher.($secteacher!=''?','.$secteacher:'');
    $mp_id = $cp_det_RET['MARKING_PERIOD_ID'];
    $start_date = $cp_det_RET['BEGIN_DATE'];
    $end_date = $cp_det_RET['END_DATE'];
    $min_start_dt_chk_qr = DBGet(DBQuery("SELECT min(start_date) as start_date from  staff_school_relationship where staff_id in($all_teacher)"));
    $min_start_dt_chk_pr = DBGet(DBQuery("SELECT min(start_date) as start_date from  staff_school_relationship where staff_id in($teacher)"));

    if ($sec == 'cpv') {
        if ($edit) {
            $period = $columns['PERIOD_ID'];
            $room = $columns['ROOM_ID'];
            $cp_id = " AND cp.COURSE_PERIOD_ID!={$course_period_id}";
        } else {
            $cp_id = '';
            $period = $_REQUEST['values']['PERIOD_ID'];
            $room = $_REQUEST['values']['ROOM_ID'];
        }
        $period_time = DBGet(DBQuery("SELECT START_TIME, END_TIME ,IGNORE_SCHEDULING FROM school_periods WHERE period_id={$period}"));
        $period_time = $period_time[1];
        $start_time = $period_time['START_TIME'];
        $end_time = $period_time['END_TIME'];
        $pre_ing = $period_time['IGNORE_SCHEDULING'];
        if ($mp_id != '') {
            $mp_date = DBGet(DBQuery("SELECT START_DATE,END_DATE FROM marking_periods where marking_period_id ={$mp_id}"));
            $mp_date = $mp_date[1];
            $end_date = $mp_date['END_DATE'];
            $start_date = $mp_date['START_DATE'];
        }
        // $mp_append_sql = " AND begin_date<='$end_date' AND '$start_date'<=end_date";
        $mp_append_sql = " AND begin_date >= '$start_date' AND end_date <= '$end_date'";

        // $days_append_sql .= " AND IF (schedule_type='BLOCKED', course_period_date ='" . $_REQUEST['meet_date'] . "' AND start_time<='$end_time' AND '$start_time'<=end_time,'$_REQUEST[meet_date]' BETWEEN begin_date AND end_date AND days like '%" . conv_day(date('D', strtotime($_REQUEST['meet_date'])) , 'key') . "%' AND start_time<='$end_time' AND '$start_time'<=end_time)";
        $days_append_sql = " AND IF (schedule_type='BLOCKED', course_period_date ='" . $_REQUEST['meet_date'] . "' AND start_time >= '$start_time' AND end_time <= '$end_time','$_REQUEST[meet_date]' BETWEEN begin_date AND end_date AND days like '%" . conv_day(date('D', strtotime($_REQUEST['meet_date'])), 'key') . "%' AND start_time >= '$start_time' AND end_time <= '$end_time')";

        // $days_room_append_sql .= " AND IF (schedule_type='BLOCKED', course_period_date ='" . $_REQUEST['meet_date'] . "' AND room_id=$room AND start_time<='$end_time' AND '$start_time'<=end_time,'$_REQUEST[meet_date]' BETWEEN begin_date AND end_date AND days like '%" . conv_day(date('D', strtotime($_REQUEST['meet_date'])) , 'key') . "%' AND room_id=$room AND start_time<='$end_time' AND '$start_time'<=end_time)";
        $days_room_append_sql = " AND IF (schedule_type='BLOCKED', course_period_date ='" . $_REQUEST['meet_date'] . "' AND room_id=$room AND start_time >= '$start_time' AND end_time <= '$end_time','$_REQUEST[meet_date]' BETWEEN begin_date AND end_date AND days like '%" . conv_day(date('D', strtotime($_REQUEST['meet_date'])), 'key') . "%' AND room_id=$room AND start_time >= '$start_time' AND end_time <= '$end_time')";

        if ($pre_ing != 'Y') {
            $cp_RET = DBGet(DBQuery("SELECT cp.COURSE_PERIOD_ID FROM course_periods  cp LEFT JOIN course_period_var cpv ON (cp.course_period_id=cpv.course_period_id) WHERE (secondary_teacher_id IN ($all_teacher) OR teacher_id IN ($all_teacher)){$mp_append_sql}{$days_append_sql}{$cp_id}"));
            if ($cp_RET) return 'Teacher Not Available';
        } elseif (strtotime($min_start_dt_chk_pr[1]['START_DATE']) > strtotime($start_date)) return 'Teacher\'s start date cannot be after course period\'s start date.';
        else {
            if ($pre_ing != 'Y') {
                $room_RET = DBGet(DBQuery("SELECT cp.COURSE_PERIOD_ID FROM course_periods  cp LEFT JOIN course_period_var cpv ON (cp.course_period_id=cpv.course_period_id) WHERE 1{$mp_append_sql}{$days_room_append_sql}{$cp_id}"));
                if ($room_RET) {
                    return 'Room Not Available';
                }
            }
            return true;
        }
        return true;
    } else {
        $cp_dates = DBGet(DBQuery("SELECT PERIOD_ID,COURSE_PERIOD_DATE,START_TIME,END_TIME,ROOM_ID FROM course_period_var WHERE COURSE_PERIOD_ID='$course_period_id'"));
        $teacher = $columns['TEACHER_ID'];
        $secteacher = $columns['SECONDARY_TEACHER_ID'];
        if (($_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['TEACHER_ID'] != '') || ($_REQUEST['hidden_primary_teacher_id'] != ''))
            $all_teacher = $teacher . ($secteacher != '' ? ',' . $secteacher : '');
        else $all_teacher = ($secteacher != '' ? $secteacher : '');
        //        $all_teacher=$teacher.($secteacher!=''?','.$secteacher:'');
        $start_date = $columns['BEGIN_DATE'];
        if ($columns['MARKING_PERIOD_ID']) $mp_id = $columns['MARKING_PERIOD_ID'];
        else $mp_id = '';
        $end_date = $columns['END_DATE'];
        if ($mp_id != '') {
            $mp_date = DBGet(DBQuery("SELECT START_DATE,END_DATE FROM marking_periods where marking_period_id ={$mp_id}"));
            $mp_date = $mp_date[1];
            $end_date = $mp_date['END_DATE'];
            $start_date = $mp_date['START_DATE'];
        }
        foreach ($cp_dates as $key => $cp) {
            $period = $cp['PERIOD_ID'];
            $room = $cp['ROOM_ID'];
            $meet_date = $cp['COURSE_PERIOD_DATE'];
            $start_time = $cp['START_TIME'];
            $end_time = $cp['END_TIME'];
            $period_time = DBGet(DBQuery("SELECT IGNORE_SCHEDULING FROM school_periods WHERE period_id={$period}"));
            $period_time = $period_time[1];

            $pre_ing = $period_time['IGNORE_SCHEDULING'];
            // $mp_append_sql = " AND begin_date<='$end_date' AND '$start_date'<=end_date";
            $mp_append_sql = " AND begin_date >= '$start_date' AND end_date <= '$end_date'";
            
            // $days_append_sql = " AND IF (schedule_type='BLOCKED', course_period_date ='$meet_date' AND start_time<='$end_time' AND '$start_time'<=end_time,'$meet_date' BETWEEN begin_date AND end_date AND days like '%" . conv_day(date('D', strtotime($meet_date)) , 'key') . "%' AND start_time<='$end_time' AND '$start_time'<=end_time)";
            $days_append_sql = " AND IF (schedule_type='BLOCKED', course_period_date ='$meet_date' AND start_time >= '$start_time' AND end_time <= '$end_time','$meet_date' BETWEEN begin_date AND end_date AND days like '%" . conv_day(date('D', strtotime($meet_date)), 'key') . "%' AND start_time >= '$start_time' AND end_time <= '$end_time')";

            // $days_room_append_sql = " AND IF (schedule_type='BLOCKED', course_period_date ='$meet_date' AND room_id=$room AND start_time<='$end_time' AND '$start_time'<=end_time,'$meet_date' BETWEEN begin_date AND end_date AND days like '%" . conv_day(date('D', strtotime($meet_date)) , 'key') . "%' AND room_id=$room AND start_time<='$end_time' AND '$start_time'<=end_time)";
            $days_room_append_sql = " AND IF (schedule_type='BLOCKED', course_period_date ='$meet_date' AND room_id=$room AND start_time >= '$start_time' AND end_time <= '$end_time','$meet_date' BETWEEN begin_date AND end_date AND days like '%" . conv_day(date('D', strtotime($meet_date)), 'key') . "%' AND room_id=$room AND start_time >= '$start_time' AND end_time <= '$end_time')";
            $cp_id = " AND cp.COURSE_PERIOD_ID!={$course_period_id}";
            if ($pre_ing != 'Y') {
                $cp_RET = DBGet(DBQuery("SELECT cp.COURSE_PERIOD_ID FROM course_periods  cp LEFT JOIN course_period_var cpv ON (cp.course_period_id=cpv.course_period_id) WHERE (secondary_teacher_id IN ($all_teacher) OR teacher_id IN ($all_teacher)){$mp_append_sql}{$days_append_sql}{$cp_id}"));
                if ($cp_RET) return 'Teacher Not Available';
            } elseif (strtotime($min_start_dt_chk_pr[1]['START_DATE']) > strtotime($start_date)) return 'Teacher\'s start date cannot be after course period\'s start date.';
            else {
                if ($pre_ing != 'Y') {
                    $room_RET = DBGet(DBQuery("SELECT cp.COURSE_PERIOD_ID FROM course_periods  cp LEFT JOIN course_period_var cpv ON (cp.course_period_id=cpv.course_period_id) WHERE 1{$mp_append_sql}{$days_room_append_sql}{$cp_id}"));
                    if ($room_RET) {
                        return 'Room Not Available';
                    }
                }
            }
        }
        $total_days = DBGet(DBQuery("SELECT COUNT(course_period_date) AS CP_DATES FROM course_period_var WHERE COURSE_PERIOD_ID={$course_period_id}"));
        $mp_RET = DBGet(DBQuery("SELECT COUNT(course_period_date) AS CP_DATES FROM course_period_var WHERE COURSE_PERIOD_ID={$course_period_id} AND course_period_date BETWEEN '$start_date' AND '$end_date'"));
        if ($total_days[1]['CP_DATES'] != $mp_RET[1]['CP_DATES']) {
            if ($mp_id != '') return 'Marking Period Cannot be Changed.';
            else return 'Begin or End Date Cannot be Changed.';
        }

        return true;
    }
}

//================Course varification ends=============================
//================ Student schedule varification=============================
function VerifyStudentSchedule($course_RET, $student_id = '')
{
    if ($student_id == '') $student_id = UserStudentID();
    if (!$course_RET) {
        return 'Incomplete course period';
    }
    if (count($course_RET) > 0) {
        $schedule_exist = DBGet(DBQuery("SELECT *  FROM `schedule` WHERE `syear` =" . $course_RET[1]['SYEAR'] . " AND `school_id` =" . $course_RET[1]['SCHOOL_ID'] . " AND `student_id` =" . $student_id . " AND `course_id` =" . $course_RET[1]['COURSE_ID'] . " AND `course_period_id` = " . $course_RET[1]['COURSE_PERIOD_ID'] . ' AND (END_DATE>="' . date('Y-m-d') . '" OR END_DATE IS NULL OR END_DATE="0000-00-00")'));
        if (count($schedule_exist) > 0) {
            return 'Course period already scheduled';
        }
    }
    $check_parent_schedule = DBGet(DBQuery('SELECT PARENT_ID FROM course_periods WHERE COURSE_PERIOD_ID=' . $course_RET[1]['COURSE_PERIOD_ID']));
    if ($check_parent_schedule[1]['PARENT_ID'] != '' && $check_parent_schedule[1]['PARENT_ID'] != $course_RET[1]['COURSE_PERIOD_ID']) {
        $check_stu_schedule = DBGet(DBQuery('SELECT COUNT(*) as REC_EX FROM schedule WHERE COURSE_PERIOD_ID=' . $check_parent_schedule[1]['PARENT_ID'] . ' AND STUDENT_ID=' . $student_id . ' AND (END_DATE>="' . date('Y-m-d') . '" OR END_DATE IS NULL OR END_DATE="0000-00-00")'));
        if ($check_stu_schedule[1]['REC_EX'] == 0) return 'Course period has parent course restriction';
    }
    if ($course_RET[1]['TOTAL_SEATS'] - $course_RET[1]['FILLED_SEATS'] <= 0) {
        return 'Seat not available';
    }
    $student_RET = DBGet(DBQuery("SELECT LEFT(GENDER,1) AS GENDER FROM students WHERE STUDENT_ID='" . $student_id . "'"));
    $student = $student_RET[1];
    if ($course_RET[1]['GENDER_RESTRICTION'] != 'N' && $course_RET[1]['GENDER_RESTRICTION'] != $student['GENDER']) {
        return 'There is gender restriction';
    }
    $do_check = false;
    foreach ($course_RET as $course) {
        if ($course['IGNORE_SCHEDULING'] != 'Y') {
            $do_check = true;
            break;
        }
    }
    if ($do_check == false) return true;

    $teacher = $course_RET[1]['TEACHER_ID'];
    $mp_id = $course_RET[1]['MARKING_PERIOD_ID'];
    $end_date = $course_RET[1]['END_DATE'];
    $start_date = date('Y-m-d');
    $cp_start_date = $course_RET[1]['BEGIN_DATE'];
    $mp_append_sql = " AND s.start_date>='$cp_start_date' AND s.end_date<='$end_date' AND ('$start_date'<=s.end_date OR s.end_date IS NULL)";

    if ($course_RET[1]['SCHEDULE_TYPE'] == 'FIXED') {
        $days = $course_RET[1]['DAYS'];
        $start_time = $course_RET[1]['START_TIME'];
        $end_time = $course_RET[1]['END_TIME'];

        // $period_days_append_sql = " AND course_period_id IN(SELECT course_period_id from course_period_var cpv,school_periods sp WHERE cpv.period_id=sp.period_id AND ignore_scheduling IS NULL AND sp.start_time<='$end_time' AND '$start_time'<=sp.end_time AND (";
        $period_days_append_sql = " AND course_period_id IN(SELECT course_period_id from course_period_var cpv,school_periods sp WHERE cpv.period_id=sp.period_id AND ignore_scheduling IS NULL AND sp.start_time >= '$start_time' AND sp.end_time <= '$end_time' AND (";

        $days_arr = str_split($days);
        foreach ($days_arr as $day) {
            $period_days_append_sql .= "DAYS LIKE '%$day%' OR ";
        }
        $period_days_append_sql = substr($period_days_append_sql, 0, -4) . '))';
    } elseif ($course_RET[1]['SCHEDULE_TYPE'] == 'VARIABLE') {
        $period_days_append_sql = " AND course_period_id IN(SELECT course_period_id from course_period_var cpv,school_periods sp WHERE cpv.period_id=sp.period_id AND ignore_scheduling IS NULL AND (";
        foreach ($course_RET as $period_day) {
            // $period_days_append_sql .= "(sp.start_time<='$period_day[END_TIME]' AND '$period_day[START_TIME]'<=sp.end_time AND DAYS LIKE '%$period_day[DAYS]%') OR ";
            $period_days_append_sql .= "(sp.start_time >= '$period_day[START_TIME]' AND sp.end_time <=  '$period_day[END_TIME]' AND DAYS LIKE '%$period_day[DAYS]%') OR ";
        }
        $period_days_append_sql = substr($period_days_append_sql, 0, -4) . '))';
    } elseif ($course_RET[1]['SCHEDULE_TYPE'] == 'BLOCKED') {
        $period_days_append_sql = " AND course_period_id IN(SELECT course_period_id from course_period_var cpv,school_periods sp WHERE cpv.period_id=sp.period_id AND ignore_scheduling IS NULL AND (";
        foreach ($course_RET as $period_date) {
            // $period_days_append_sql .="(sp.start_time<='$period_date[END_TIME]' AND '$period_date[START_TIME]'<=sp.end_time AND IF(course_period_date IS NULL, course_period_date='$period_date[COURSE_PERIOD_DATE]',DAYS LIKE '%$period_date[DAYS]%')) OR ";
            $period_days_append_sql .= "(sp.start_time >= '$period_date[START_TIME]' AND sp.end_time <= '$period_date[END_TIME]' AND (cpv.course_period_date IS NULL OR cpv.course_period_date='$period_date[COURSE_PERIOD_DATE]') AND cpv.DAYS LIKE '%$period_date[DAYS]%') OR ";
        }
        $period_days_append_sql = substr($period_days_append_sql, 0, -4) . '))';
    }

    $exist_RET = DBGet(DBQuery("SELECT s.ID FROM schedule s WHERE student_id=" . $student_id . " AND s.syear='" . UserSyear() . "' {$mp_append_sql}{$period_days_append_sql} UNION SELECT s.ID FROM temp_schedule s WHERE student_id=" . $student_id . "{$mp_append_sql}{$period_days_append_sql}"));
    if ($exist_RET) return 'There is a Period Conflict (' . $course_RET[1]['CP_TITLE'] . ')';
    else {
        return true;
    }
}
