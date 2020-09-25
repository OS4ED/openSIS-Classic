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
include('RedirectRootInc.php');
include('Warehouse.php');
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
        $columns = array('TITLE' =>_course,
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

        ListOutput($schedule_RET, $columns,  _course, _courses, $link, '', $options,'',false,false);
        
        
 function _makeTitle($value, $column = '') {
    global $_openSIS, $THIS_RET;
    return $value;
}

///For deleting schedules
function _makeAction($value) {
    global $THIS_RET;
    $i = UserStudentId();
    $rem = "<center><a href=Modules.php?modname=scheduling/Schedule.php&student_id=$i&del=true&c_id=$value&cp_id=$THIS_RET[COURSE_PERIOD_ID]&schedule_id=$THIS_RET[SCHEDULE_ID] class=\"btn btn-danger btn-xs btn-icon\"><i class=\"fa fa-times\"></i></a></center>";
    return $rem;
}

function _makeViewLock($value, $column) {
    global $THIS_RET;

    if ($value == 'Y')
        $img = 'locked';
    else
        $img = 'unlocked';

    return '<IMG SRC=assets/' . $img . '.gif >';
}

function _makePeriodSelect($course_period_id, $column = '') {
    global $_openSIS, $THIS_RET, $fy_id;

    $sql = 'SELECT cp.COURSE_PERIOD_ID,cp.PARENT_ID,cp.TITLE,cp.MARKING_PERIOD_ID,COALESCE(cp.TOTAL_SEATS-cp.FILLED_SEATS,0) AS AVAILABLE_SEATS FROM course_periods cp,course_period_var cpv,school_periods sp WHERE sp.PERIOD_ID=cpv.PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.COURSE_ID=\'' . $THIS_RET[COURSE_ID] . '\' ORDER BY sp.SORT_ORDER';
    $QI = DBQuery($sql);
    $orders_RET = DBGet($QI);

    foreach ($orders_RET as $value) {
        if ($value['COURSE_PERIOD_ID'] != $value['PARENT_ID']) {
            $parent = DBGet(DBQuery('SELECT SHORT_NAME FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $value['PARENT_ID'] . '\''));
            $parent = $parent[1]['SHORT_NAME'];
        }
        $periods[$value['COURSE_PERIOD_ID']] = $value['TITLE'] . (($value['MARKING_PERIOD_ID'] != $fy_id && $value['COURSE_PERIOD_ID'] != $course_period_id) ? ' (' . GetMP($value['MARKING_PERIOD_ID']) . ')' : '') . ($value['COURSE_PERIOD_ID'] != $course_period_id ? ' (' . $value['AVAILABLE_SEATS'] . ' seats)' : '') . (($value['COURSE_PERIOD_ID'] != $course_period_id && $parent) ? ' -> ' . $parent : '');
    }


    return SelectInput_Disonclick($course_period_id, "schedule[$THIS_RET[COURSE_PERIOD_ID]][$THIS_RET[START_DATE]][COURSE_PERIOD_ID]", '', $periods, false);
}

function _makeMPSelect($mp_id, $name = '') {
    global $_openSIS, $THIS_RET, $fy_id;
    if ($mp_id != '') {
        if (!$_openSIS['_makeMPSelect']) {
            $semesters_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,NULL AS SEMESTER_ID FROM school_semesters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER'));
            $quarters_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,SEMESTER_ID FROM school_quarters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER'));

            $_openSIS['_makeMPSelect'][$fy_id][1] = array('MARKING_PERIOD_ID' => "$fy_id", 'TITLE' => 'Full Year', 'SEMESTER_ID' => '');
            foreach ($semesters_RET as $sem)
                $_openSIS['_makeMPSelect'][$fy_id][] = $sem;
            foreach ($quarters_RET as $qtr)
                $_openSIS['_makeMPSelect'][$fy_id][] = $qtr;

            $quarters_QI = DBQuery('SELECT MARKING_PERIOD_ID,TITLE,SEMESTER_ID FROM school_quarters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER');
            $quarters_indexed_RET = DBGet($quarters_QI, array(), array('SEMESTER_ID'));

            foreach ($semesters_RET as $sem) {
                $_openSIS['_makeMPSelect'][$sem['MARKING_PERIOD_ID']][1] = $sem;
                foreach ($quarters_indexed_RET[$sem['MARKING_PERIOD_ID']] as $qtr)
                    $_openSIS['_makeMPSelect'][$sem['MARKING_PERIOD_ID']][] = $qtr;
            }

            foreach ($quarters_RET as $qtr)
                $_openSIS['_makeMPSelect'][$qtr['MARKING_PERIOD_ID']][] = $qtr;
        }

        foreach ($_openSIS['_makeMPSelect'][$mp_id] as $value)
            $mps[$value['MARKING_PERIOD_ID']] = $value['TITLE'];

        if ($THIS_RET['MARKING_PERIOD_ID'] != $mp_id)
            $mps[$THIS_RET['MARKING_PERIOD_ID']] = '* ' . $mps[$THIS_RET['MARKING_PERIOD_ID']];

        return SelectInput($THIS_RET['MARKING_PERIOD_ID'], "schedule[$THIS_RET[COURSE_PERIOD_ID]][$THIS_RET[START_DATE]][MARKING_PERIOD_ID]", '', $mps, false);
    }
    else {
        $check_custom = DBGet(DBQuery('SELECT BEGIN_DATE,END_DATE FROM course_periods WHERE COURSE_PERIOD_ID=' . $THIS_RET['COURSE_PERIOD_ID'] . ' AND BEGIN_DATE IS NOT NULL AND END_DATE IS NOT NULL AND BEGIN_DATE!=\'0000-00-00\' AND END_DATE!=\'0000-00-00\' '));
        if (count($check_custom) > 0) {
            return '<div style="white-space: nowrap;">' . ProperDateAY($check_custom[1]['BEGIN_DATE']) . ' to ' . ProperDateAY($check_custom[1]['END_DATE']) . '</div>';
        }
    }
}

function _makeMPSelect_red($mp_id, $name = '') {
    global $_openSIS, $THIS_RET, $fy_id;
    if ($mp_id != '') {
        if (!$_openSIS['_makeMPSelect']) {
            $semesters_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,NULL AS SEMESTER_ID FROM school_semesters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER'));
            $quarters_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,SEMESTER_ID FROM school_quarters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER'));

            $_openSIS['_makeMPSelect'][$fy_id][1] = array('MARKING_PERIOD_ID' => "$fy_id", 'TITLE' => 'Full Year', 'SEMESTER_ID' => '');
            foreach ($semesters_RET as $sem)
                $_openSIS['_makeMPSelect'][$fy_id][] = $sem;
            foreach ($quarters_RET as $qtr)
                $_openSIS['_makeMPSelect'][$fy_id][] = $qtr;

            $quarters_QI = DBQuery('SELECT MARKING_PERIOD_ID,TITLE,SEMESTER_ID FROM school_quarters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER');
            $quarters_indexed_RET = DBGet($quarters_QI, array(), array('SEMESTER_ID'));

            foreach ($semesters_RET as $sem) {
                $_openSIS['_makeMPSelect'][$sem['MARKING_PERIOD_ID']][1] = $sem;
                foreach ($quarters_indexed_RET[$sem['MARKING_PERIOD_ID']] as $qtr)
                    $_openSIS['_makeMPSelect'][$sem['MARKING_PERIOD_ID']][] = $qtr;
            }

            foreach ($quarters_RET as $qtr)
                $_openSIS['_makeMPSelect'][$qtr['MARKING_PERIOD_ID']][] = $qtr;
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

function _makeDate($value, $column) {
    global $THIS_RET;
    static $counter = 1;
    if ($column == 'START_DATE')
        $allow_na = false;
    else
        $allow_na = true;

    if ($column == 'END_DATE' && $THIS_RET[END_DATE] != '') {
        $counter++;
        return '<div style="white-space: nowrap;">' . DateInputAY($value != "" ? $value : "", "schedule[$THIS_RET[COURSE_PERIOD_ID]][$THIS_RET[START_DATE]][$column]", $counter . $THIS_RET[COURSE_PERIOD_ID], '', true, $allow_na) . '</div>';
    } else {

        $counter++;
        return '<div style="white-space: nowrap;">' . DateInputAY($value != "" ? $value : "", "schedule[$THIS_RET[COURSE_PERIOD_ID]][$THIS_RET[START_DATE]][$column]", $counter . $THIS_RET[COURSE_PERIOD_ID], '', true, $allow_na) . '</div>';
    }
}

function _makeDate_red($value, $column) {
    global $THIS_RET;
    static $counter = 0;
    if ($column == 'START_DATE')
        $allow_na = false;
    else
        $allow_na = true;

    if ($column == 'END_DATE' && $THIS_RET[END_DATE] != '') {
        return date('M/d/Y', strtotime($value));
    } else {

        $counter++;
        return '<div style="white-space: nowrap;">' . DateInputAY_red($value, "schedule[$THIS_RET[COURSE_PERIOD_ID]][$THIS_RET[START_DATE]][$column]", $counter . $THIS_RET[COURSE_PERIOD_ID], $THIS_RET[COURSE_PERIOD_ID]) . '</div>';
    }
}

function _makeInfo($value, $column) {
    global $THIS_RET;
    return "<center><a href=javascript:void(0) onclick=Sch_Mrinfo('".$value."');><i class=\"icon-info22\"></i></a></center>";
}

function _makeMP($value, $column) {
    global $THIS_RET;

    if ($value != '')
        return GetMP($value);
    else {
        $check_custom = DBGet(DBQuery('SELECT BEGIN_DATE,END_DATE FROM course_periods WHERE COURSE_PERIOD_ID=' . $THIS_RET['COURSE_PERIOD_ID'] . ' AND BEGIN_DATE IS NOT NULL AND END_DATE IS NOT NULL AND BEGIN_DATE!=\'0000-00-00\' AND END_DATE!=\'0000-00-00\' '));
        if (count($check_custom) > 0) {
            return '<div style="white-space: nowrap;">' . ProperDateAY($check_custom[1]['BEGIN_DATE']) . ' to ' . ProperDateAY($check_custom[1]['END_DATE']) . '</div>';
        }
    }
}

function _makeMPA($value, $column) {
    global $THIS_RET;

    if ($value != '' && $THIS_RET['MARKING_PERIOD_ID'] == $THIS_RET['MPA_ID'])
        return GetMP($value);
    elseif ($value != '' && $THIS_RET['MARKING_PERIOD_ID'] != $THIS_RET['MPA_ID'])
        return '*' . GetMP($value);
    else {
        $check_custom = DBGet(DBQuery('SELECT BEGIN_DATE,END_DATE FROM course_periods WHERE COURSE_PERIOD_ID=' . $THIS_RET['COURSE_PERIOD_ID'] . ' AND BEGIN_DATE IS NOT NULL AND END_DATE IS NOT NULL AND BEGIN_DATE!=\'0000-00-00\' AND END_DATE!=\'0000-00-00\' '));
        if (count($check_custom) > 0) {
            return '<div style="white-space: nowrap;">' . ProperDateAY($check_custom[1]['BEGIN_DATE']) . ' to ' . ProperDateAY($check_custom[1]['END_DATE']) . '</div>';
        }
    }
}

function _makeViewDate($value, $column) {
    if ($value)
        return ProperDate($value);
    else
        return '<center>n/a</center>';
}

function _makeLock($value, $column) {
    global $THIS_RET;
    $hidd = "<input type='hidden' name='schedule[$THIS_RET[COURSE_PERIOD_ID]][$THIS_RET[START_DATE]][SCHEDULE_ID]' value='" . $THIS_RET[SCHEDULE_ID] . "'>";

    if ($value == 'Y')
        $img = 'locked';
    else
        $img = 'unlocked';

    return '<IMG SRC=assets/' . $img . '.gif ' . (AllowEdit() ? 'onclick="if(this.src.indexOf(\'assets/locked.gif\')!=-1) {this.src=\'assets/unlocked.gif\'; document.getElementById(\'lock' . $THIS_RET['COURSE_PERIOD_ID'] . '-' . $THIS_RET['START_DATE'] . '\').value=\'\';} else {this.src=\'assets/locked.gif\'; document.getElementById(\'lock' . $THIS_RET['COURSE_PERIOD_ID'] . '-' . $THIS_RET['START_DATE'] . '\').value=\'Y\';}"' : '') . '><INPUT type=hidden name=schedule[' . $THIS_RET['COURSE_PERIOD_ID'] . '][' . $THIS_RET['START_DATE'] . '][SCHEDULER_LOCK] id=lock' . $THIS_RET['COURSE_PERIOD_ID'] . '-' . $THIS_RET['START_DATE'] . ' value=' . $value . '>' . $hidd;
}

function _makeDays($value) {
    $value = str_replace(',', '', $value);
    for ($i = 0; $i < strlen($value); $i++) {
        $arr[] = substr($value, $i, 1);
    }
    $arr = array_unique($arr);
    $arr = implode('', $arr);
    return $arr;
}

function VerifySchedule(&$schedule) {
    $conflicts = array();

    $ij = count($schedule);
    for ($i = 1; $i < $ij; $i++)
        for ($j = $i + 1; $j <= $ij; $j++)
            if (!$conflicts[$i] || !$conflicts[$j])
                if (strpos(GetAllMP(GetMPTable(GetMP($schedule[$i]['MARKING_PERIOD_ID'], 'TABLE')), $schedule[$i]['MARKING_PERIOD_ID']), "'" . $schedule[$j]['MARKING_PERIOD_ID'] . "'") !== false && (!$schedule[$i]['END_EPOCH'] || $schedule[$j]['START_EPOCH'] <= $schedule[$i]['END_EPOCH']) && (!$schedule[$j]['END_EPOCH'] || $schedule[$i]['START_EPOCH'] <= $schedule[$j]['END_EPOCH']))
                    if ($schedule[$i]['COURSE_ID'] == $schedule[$j]['COURSE_ID']) //&& $schedule[$i]['COURSE_WEIGHT']==$schedule[$j]['COURSE_WEIGHT'])
                        $conflicts[$i] = $conflicts[$j] = true;
                    else
                    if ($schedule[$i]['PERIOD_ID'] == $schedule[$j]['PERIOD_ID'])
                        if (strlen($schedule[$i]['DAYS']) + strlen($schedule[$j]['DAYS']) > 7)
                            $conflicts[$i] = $conflicts[$j] = true;
                        else
                            foreach (veriry_str_split($schedule[$i]['DAYS']) as $k)
                                if (strpos($schedule[$j]['DAYS'], $k) !== false) {
                                    $conflicts[$i] = $conflicts[$j] = true;
                                    break;
                                }
    $student_id = UserStudentID();


    foreach ($conflicts as $i => $true)
        $schedule[$i]['TITLE'] = '<span class="text-bold">' . $schedule[$i]['TITLE'] . '</span>';
}

function veriry_str_split($str) {
    $ret = array();
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++)
        $ret [] = substr($str, $i, 1);
    return $ret;
}

function CreateSelect($val, $name, $link = '', $mpid) {


    if ($link != '')
        $html .= "<select class=\"form-control\" name=" . $name . " id=" . $name . " onChange=\"window.location='" . $link . "' + this.options[this.selectedIndex].value;\">";
    else
        $html .= "<select class=\"form-control\" name=" . $name . " id=" . $name . " >";

    foreach ($val as $key => $value) {


        if (!isset($mpid) && (UserMP() == $value[strtoupper($name)]))
            $html .= "<option selected value=" . UserMP() . ">" . $value['TITLE'] . "</option>";
        else {
            if ($value[strtoupper($name)] == $_REQUEST[$name])
                $html .= "<option selected value=" . $value[strtoupper($name)] . ">" . $value['TITLE'] . "</option>";
            else
                $html .= "<option value=" . $value[strtoupper($name)] . ">" . $value['TITLE'] . "</option>";
        }
    }



    $html .= "</select>";
    return $html;
}       

?>
