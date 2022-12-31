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
if (GetTeacher(UserStaffID(), '', 'PROFILE', false) == 'teacher') {
    $mp_select_RET = DBGet(DBQuery('SELECT DISTINCT cp.MARKING_PERIOD_ID, (SELECT TITLE FROM marking_periods WHERE MARKING_PERIOD_ID=cp.MARKING_PERIOD_ID) AS TITLE FROM course_periods cp,courses c, school_periods sp,course_period_var cpv WHERE cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.COURSE_ID=c.COURSE_ID AND (cp.TEACHER_ID=\'' . UserStaffID() . '\' OR cp.SECONDARY_TEACHER_ID=\'' . UserStaffID() . '\') AND cpv.PERIOD_ID=sp.PERIOD_ID AND cp.MARKING_PERIOD_ID IS NOT NULL AND cp.SYEAR=\'' . UserSyear() . '\' AND cp.SCHOOL_ID=\'' . UserSchool() . '\''));
    $print_mp = CreateSelect($mp_select_RET, 'marking_period_id', 'Show All', 'Modules.php?modname=' . $_REQUEST['modname'] . '&include=' . $_REQUEST['include'] . '&category_id=' . $_REQUEST['category_id'] . '&marking_period_id=');



    echo '<div class="form-group"><label class="control-label col-md-2">' . _markingPeriods . ' :</label><div class="col-md-3">' . $print_mp . '</div></div>';
    if (!$_REQUEST['marking_period_id']) {
        $schedule_RET = DBGet(DBQuery('SELECT 
    sp.START_TIME as START_TIME,
    sp.END_TIME as END_TIME, cp.SCHEDULE_TYPE,cp.course_period_id,c.TITLE AS COURSE,cpv.DAYS,cpv.COURSE_PERIOD_DATE,CONCAT(sp.START_TIME,\'' . ' to ' . '\', sp.END_TIME) AS DURATION,r.TITLE as ROOM,sp.TITLE AS PERIOD,cp.COURSE_WEIGHT,IF(cp.MARKING_PERIOD_ID IS NULL ,\'Custom\',cp.MARKING_PERIOD_ID) AS MARKING_PERIOD_ID from
course_periods cp , courses c,course_period_var cpv,school_periods sp,rooms r  WHERE cp.course_id=c.COURSE_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID  AND sp.PERIOD_ID=cpv.PERIOD_ID AND cpv.ROOM_ID=r.ROOM_ID AND (cp.TEACHER_ID=\'' . UserStaffID() . '\' OR cp.SECONDARY_TEACHER_ID=\'' . UserStaffID() . '\')  AND cp.SYEAR=\'' . UserSyear() . '\' AND cp.SCHOOL_ID=' . UserSchool()), array('PERIOD_ID' => 'GetPeriod', 'MARKING_PERIOD_ID' => 'GetMP_teacherschedule'));
    } else if ($_REQUEST['marking_period_id']) {
        $sel_mp_info = DBGet(DBQuery('SELECT * FROM marking_periods WHERE MARKING_PERIOD_ID=' . $_REQUEST['marking_period_id']));
        $sel_mp_info = $sel_mp_info[1];

        $schedule_RET = DBGet(DBQuery('SELECT 
    sp.START_TIME as START_TIME,
    sp.END_TIME as END_TIME, cp.SCHEDULE_TYPE,cp.course_period_id,cpv.DAYS,cpv.COURSE_PERIOD_DATE,CONCAT(sp.START_TIME,\'' . ' to ' . '\', sp.END_TIME) AS DURATION,r.TITLE as ROOM,sp.TITLE AS PERIOD,c.TITLE AS COURSE,cp.COURSE_WEIGHT,IF(cp.MARKING_PERIOD_ID IS NULL ,\'Custom\',cp.MARKING_PERIOD_ID) AS MARKING_PERIOD_ID from
course_periods cp , courses c,course_period_var cpv,school_periods sp,rooms r WHERE cp.course_id=c.COURSE_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID  AND sp.PERIOD_ID=cpv.PERIOD_ID AND cpv.ROOM_ID=r.ROOM_ID AND (cp.MARKING_PERIOD_ID IN (' . GetMPChildren($sel_mp_info['MP_TYPE'], $_REQUEST['marking_period_id']) . ') OR (cp.MARKING_PERIOD_ID IS NULL AND (cp.BEGIN_DATE BETWEEN \'' . $sel_mp_info['START_DATE'] . '\' AND \'' . $sel_mp_info['END_DATE'] . '\'))) AND (cp.TEACHER_ID=\'' . UserStaffID() . '\' OR cp.SECONDARY_TEACHER_ID=\'' . UserStaffID() . '\') AND cp.SCHOOL_ID=\'' . UserSchool() . '\' AND cp.SYEAR=' . UserSyear()), array('PERIOD_ID' => 'GetPeriod', 'MARKING_PERIOD_ID' => 'GetMP_teacherschedule'));
    }

    foreach ($schedule_RET as $rdi => $rdd) {
        $schedule_RET[$rdi]['DURATION'] = date("g:i A", strtotime($rdd['START_TIME'])) . ' to ' . date("g:i A", strtotime($rdd['END_TIME']));
        $cp_info = DBGet(DBQuery('SELECT * FROM course_periods WHERE COURSE_PERIOD_ID=' . $rdd['COURSE_PERIOD_ID']));
        if ($schedule_RET[$rdi]['MARKING_PERIOD_ID'] == 'Custom') {
            $schedule_RET[$rdi]['MARKING_PERIOD_ID'] = date('M/d/Y', strtotime($cp_info[1]['BEGIN_DATE'])) . ' to ' . date('M/d/Y', strtotime($cp_info[1]['END_DATE']));
        }
        // $get_det = DBGet(DBQuery('SELECT cpv.DAYS,cpv.COURSE_PERIOD_DATE,sp.START_TIME as START_TIME,sp.END_TIME as END_TIME,CONCAT(sp.START_TIME,\'' . ' to ' . '\', sp.END_TIME) AS DURATION,r.TITLE as ROOM,sp.TITLE AS PERIOD FROM course_period_var cpv,school_periods sp,rooms r WHERE sp.PERIOD_ID=cpv.PERIOD_ID AND cpv.ROOM_ID=r.ROOM_ID AND cpv.COURSE_PERIOD_ID=' . $rdd['COURSE_PERIOD_ID']));
        if ($rdd['SCHEDULE_TYPE'] == 'FIXED') {
            $schedule_RET[$rdi]['DAYS'] = _makeDays($rdd['DAYS']);
        } else {
            if ($rdd['COURSE_PERIOD_DATE'] != '') {
                $schedule_RET[$rdi]['DAYS'] = date('Y-m-d', strtotime($rdd['COURSE_PERIOD_DATE']));
            }
        }
    }

    if (count($schedule_RET) > 0) {
        echo '<div class="panel">';
        ListOutput($schedule_RET, array(
            'COURSE' => _course,
            'PERIOD' => _period,
            'DAYS' => _days,
            'DURATION' => _time,
            'ROOM' => _room,
            'MARKING_PERIOD_ID' => _markingPeriod,
        ), _course, _courses);
        echo '</div>';
    } else
        echo '<br><div class="alert alert-danger no-border">' . _thisStaffIsNotScheduledToAnyCoursePeriodAndThereforeNoScheduleDataIsAvailable . '.</div>';
} else {
    echo '<div class="alert alert-danger no-border"><i class="icon-alert"></i> ' . _thisStaffIsNotScheduledToAnyCoursePeriodAndThereforeNoScheduleDataIsAvailable . '.</div>';
}
$_REQUEST['category_id'] = 2;
include('modules/users/includes/OtherInfoInc.inc.php');

function CreateSelect($val, $name, $opt, $link = '')
{
    $html = '';
    if ($link != '')
        $html .= "<select class=\"form-control\" name=" . $name . " id=" . $name . " onChange=\"window.location='" . $link . "' + this.options[this.selectedIndex].value;\">";
    else
        $html .= "<select class=\"form-control\" name=" . $name . " id=" . $name . " >";
    $html .= "<option value=''>" . $opt . "</option>";

    foreach ($val as $key => $value) {
        if ($value[strtoupper($name)] == $_REQUEST[$name])
            $html .= "<option selected value=" . $value[strtoupper($name)] . ">" . $value['TITLE'] . "</option>";
        else
            $html .= "<option value=" . $value[strtoupper($name)] . ">" . $value['TITLE'] . "</option>";
    }



    $html .= "</select>";
    return $html;
}

function _makeDays($value, $column = '')
{
    $return = '';
    foreach (array('U', 'M', 'T', 'W', 'H', 'F', 'S') as $day)
        if (strpos($value, $day) !== false)
            $return .= $day;
        else
            $return .= '-';
    return '<div style="white-space: nowrap">' . $return . '</div>';
}
