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
include 'RedirectRootInc.php';
include 'ConfigInc.php';
include 'Warehouse.php';

$id = sqlSecurityFilter($_REQUEST['id']);

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
            AND s.ID=' . $id . '  GROUP BY cp.COURSE_PERIOD_ID';

$QI = DBQuery($sql);
$schedule_RET = DBGet($QI, array('TITLE' => '_makeTitle', 'PERIOD_PULLDOWN' => '_makePeriodSelect', 'COURSE_MARKING_PERIOD_ID' => '_makeMPA', 'DAYS' => '_makeDays', 'SCHEDULER_LOCK' => '_makeViewLock', 'START_DATE' => '_makeViewDate', 'END_DATE' => '_makeViewDate', 'MODIFIED_DATE' => '_makeViewDate'));
$columns = array(
    'TITLE' =>_course,
    'PERIOD_PULLDOWN' =>_periodTeacher,
    'ROOM' =>_room,
    'DAYS' =>_daysOfWeek,
    'COURSE_MARKING_PERIOD_ID' =>_term,
    'SCHEDULER_LOCK' =>  '<IMG SRC=assets/locked.gif border=0>',
    'START_DATE' =>_enrolled,
    'END_DATE' =>_endDateDropDate,
    'MODIFIED_NAME' =>_modifiedBy,
    'MODIFIED_DATE' =>_modifiedDate,
);
$options = array('search' =>false, 'count' =>false, 'save' =>false, 'sort' =>false);
ListOutput($schedule_RET, $columns,  _course, _courses, $link, '', $options);
        
function _makeViewDate($value, $column) {
    if ($value)
        return ProperDate($value);
    else
        return '<center>' . _NA . '</center>';
}
            
//        echo '<br /><div align="center"><input type="button" class="btn btn-primary" value="Close" onclick="window.close();"></div>';

function _makeViewLock($value, $column) {
    global $THIS_RET;

    if ($value == 'Y')
        $img = 'locked';
    else
        $img = 'unlocked';

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

?>