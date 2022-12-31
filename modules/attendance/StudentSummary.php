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

DrawBC(""._attendance." > " . ProgramTitle());
$month_names = array('JAN' => '01', 'FEB' => '02', 'MAR' => '03', 'APR' => '04', 'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AUG' => '08', 'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DEC' => '12');
if ($_REQUEST['day_start'] && $_REQUEST['month_start'] && $_REQUEST['year_start'])
    $start_date = $_REQUEST['year_start'] . '-' . $_REQUEST['month_start'] . '-' . $_REQUEST['day_start'];
else
    $start_date = date('Y-m') . '-01';

if ($_REQUEST['day_end'] && $_REQUEST['month_end'] && $_REQUEST['year_end'])
    $end_date = $_REQUEST['year_end'] . '-' . $_REQUEST['month_end'] . '-' . $_REQUEST['day_end'];
else
    $end_date = DBDate('mysql');
####################
$selectedStudentId = isset($_REQUEST['student_id']) ? $_REQUEST['student_id'] : UserStudentID();
if (isset($_REQUEST['student_id']) || UserStudentID()) {
    $RET = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME,NAME_SUFFIX,SCHOOL_ID FROM students,student_enrollment WHERE students.STUDENT_ID=\'' . $selectedStudentId . '\' AND student_enrollment.STUDENT_ID = students.STUDENT_ID '));
    $count_student_RET = DBGet(DBQuery("SELECT COUNT(*) AS NUM FROM students"));
    if ($count_student_RET[1]['NUM'] > 1) {
        DrawHeaderHome('<div class="panel"><div class="panel-heading"><h6 class="panel-title">'._selectedStudent.' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'] . '</h6> <div class="heading-elements"><span class="heading-text"><A HREF=Modules.php?modname=' . $_REQUEST['modname'] . '&search_modfunc=list&next_modname=' . $_REQUEST['modname'] . '&ajax=true&bottom_back=true&return_session=true&&day_start=' . $_REQUEST['day_start'] . '&&month_start=' . $_REQUEST['month_start'] . '&&year_start=' . $_REQUEST['year_start'] . '&&period_id=' . $_REQUEST['period_id'] . '&&myclasses=' . $_REQUEST['myclasses'] . '&&chk=1 target=body><i class="icon-square-left"></i> '._backToStudentList.'</A></span><div class="btn-group heading-btn"><A HREF=Side.php?student_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">'._deselect.'</A></div></div></div></div>');
    } else if ($count_student_RET[1]['NUM'] == 1) {
        DrawHeaderHome('<div class="panel"><div class="panel-heading"><h6 class="panel-title">'._selectedStudent.' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'] . '</h6> <div class="heading-elements"><A HREF=Side.php?student_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">'._deselect.'</A></div></div></div>');
    }
}
####################
if ($_REQUEST['search_modfunc'] || $_REQUEST['student_id'] || UserStudentID() || User('PROFILE') == 'parent' || User('PROFILE') == 'student') {
    if (!UserStudentID() && !$_REQUEST['student_id']) {
        if ($_REQUEST['myclasses'] == '')
            $periods_RET = DBGet(DBQuery('SELECT sp.PERIOD_ID,sp.TITLE FROM school_periods sp,course_periods cp,course_period_var cpv WHERE cpv.PERIOD_ID=sp.PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cpv.DOES_ATTENDANCE=\'Y\' AND sp.SYEAR=\'' . UserSyear() . '\' AND sp.SCHOOL_ID=\'' . UserSchool() . '\'  GROUP BY cpv.PERIOD_ID ORDER BY  sp.SORT_ORDER'));
        elseif ($_REQUEST['myclasses'] == 'my_classes')
            $periods_RET = DBGet(DBQuery('SELECT sp.PERIOD_ID,sp.TITLE FROM school_periods sp,course_periods cp,course_period_var cpv WHERE cpv.PERIOD_ID=sp.PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND (cp.TEACHER_ID=' . User('STAFF_ID') . ' OR cp.SECONDARY_TEACHER_ID=' . User('STAFF_ID') . ') AND cpv.DOES_ATTENDANCE=\'Y\' AND sp.SYEAR=\'' . UserSyear() . '\' AND sp.SCHOOL_ID=\'' . UserSchool() . '\' GROUP BY cpv.PERIOD_ID ORDER BY sp.SORT_ORDER'));
        else
            $periods_RET = DBGet(DBQuery('SELECT sp.PERIOD_ID,sp.TITLE FROM school_periods sp,course_periods cp,course_period_var cpv WHERE cpv.PERIOD_ID=sp.PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cpv.DOES_ATTENDANCE=\'Y\' AND cp.COURSE_PERIOD_ID=' . UserCoursePeriod() . ' AND sp.SYEAR=\'' . UserSyear() . '\' AND sp.SCHOOL_ID=\'' . UserSchool() . '\'  GROUP BY cpv.PERIOD_ID ORDER BY sp.SORT_ORDER'));
        $period_select = "<SELECT class=\"form-control\" name=period_id onchange='this.form.submit();'><OPTION value=\"\">Daily</OPTION>";
        if (count($periods_RET)) {
            foreach ($periods_RET as $period)
                $period_select .= "<OPTION value=$period[PERIOD_ID]" . (($_REQUEST['period_id'] == $period['PERIOD_ID']) ? ' SELECTED' : '') . ">$period[TITLE]</OPTION>";
        }
        $period_select .= '</SELECT>';
    }
    if (User('PROFILE') == 'teacher') {
        $myclasses = '<SELECT class="form-control" name="myclasses" onchange="this.form.submit();">';
        $myclasses .='<OPTION value=""' . ($_REQUEST['myclasses'] == '' ? ' SELECTED' : '') . '>All course periods</OPTION>';
        $myclasses .='<OPTION value="my_classes"' . ($_REQUEST['myclasses'] == 'my_classes' ? ' SELECTED' : '') . '>All my course periods</OPTION>';
        $myclasses .='<OPTION value="selected_class"' . ($_REQUEST['myclasses'] == 'selected_class' ? ' SELECTED' : '') . '>Selected course period</OPTION>';
        $myclasses .='</SELECT>';
    }
    $PHP_tmp_SELF = PreparePHP_SELF();
    echo "<FORM class=\"form-horizontal\" action=$PHP_tmp_SELF method=POST>";
    echo "<div class=\"panel panel-default\">";
    echo "<div class=\"panel-body\">";
    DrawHeaderHome('<div class="form-inline clearfix"><div class="col-md-12"><div class="inline-block">' . PrepareDateSchedule($start_date, 'start') . '</div><div style="display: inline-block; margin: 0 10px;">&nbsp; - &nbsp;</div><div class="inline-block">' . PrepareDateSchedule($end_date, 'end') . '</div><div style="display: inline-block; margin: 0 10px;"><INPUT type=submit name=absence_go class="btn btn-primary" value='._go.'></div><div style="display: inline-block; margin: 0 10px 0 0;">', $period_select . '</div><div style="display: inline-block;">' . $myclasses . '</div></div></div>');
    echo '</div>';
    echo '</div>';
    echo '</FORM>';
}

if ($_REQUEST['period_id']) {
    $extra['SELECT'] .= ',(SELECT count(*) FROM attendance_period ap,attendance_codes ac
                        WHERE ac.ID=ap.ATTENDANCE_CODE AND (ac.STATE_CODE=\'A\' OR ac.STATE_CODE=\'H\') AND ap.STUDENT_ID=ssm.STUDENT_ID
                        AND ap.PERIOD_ID=\'' . $_REQUEST['period_id'] . '\'
                        AND ap.SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\') AS STATE_ABS';
    $codes_RET = DBGet(DBQuery('SELECT ID,TITLE FROM attendance_codes WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND TABLE_NAME=\'0\' AND (DEFAULT_CODE!=\'Y\' OR DEFAULT_CODE IS NULL)'));
    if (count($codes_RET) > 1) {
        foreach ($codes_RET as $code) {
            if ($_REQUEST['myclasses'] != '') {
                $extra['SELECT'] .= ',(SELECT count(*) FROM attendance_period ap,attendance_codes ac,course_periods cp
                        WHERE ac.ID=ap.ATTENDANCE_CODE AND ac.ID=\'' . $code[ID] . '\' AND ap.PERIOD_ID=\'' . $_REQUEST['period_id'] . '\' AND ap.STUDENT_ID=ssm.STUDENT_ID
                        AND ap.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND ' . (($_REQUEST['myclasses'] == 'my_classes') ? '(cp.TEACHER_ID=\'' . User('STAFF_ID') . '\' OR cp.SECONDARY_TEACHER_ID=\'' . User('STAFF_ID') . '\')' : 'cp.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\'') . '
                        AND ap.SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\') AS ABS_' . $code[ID];
            } else {
                $extra['SELECT'] .= ',(SELECT count(*) FROM attendance_period ap,attendance_codes ac
                        WHERE ac.ID=ap.ATTENDANCE_CODE AND ac.ID=\'' . $code[ID] . '\' AND ap.PERIOD_ID=\'' . $_REQUEST['period_id'] . '\' AND ap.STUDENT_ID=ssm.STUDENT_ID
                        AND ap.SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\') AS ABS_' . $code[ID];
            }

            $extra['columns_after']["ABS_" . $code[ID] . ""] = $code['TITLE'];
        }
    }
} else {
    $extra['SELECT'] .= ',(SELECT count(*) FROM attendance_period ap,attendance_codes ac
                        WHERE ac.ID=ap.ATTENDANCE_CODE AND (ac.STATE_CODE=\'A\' OR ac.STATE_CODE=\'H\') AND ap.STUDENT_ID=ssm.STUDENT_ID
                        AND ap.PERIOD_ID=\'' . $_REQUEST['period_id'] . '\'
                        AND ap.SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\') AS STATE_ABS';
    $codes_RET = DBGet(DBQuery('SELECT ID,TITLE FROM attendance_codes WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND TABLE_NAME=\'0\' AND (DEFAULT_CODE!=\'Y\' OR DEFAULT_CODE IS NULL)'));
    if (count($codes_RET) > 1) {
        foreach ($codes_RET as $code) {
            if ($_REQUEST['myclasses'] != '') {
                $extra['SELECT'] .= ',(SELECT count(*) FROM attendance_period ap,attendance_codes ac,course_periods cp
                        WHERE ac.ID=ap.ATTENDANCE_CODE AND ac.ID=\'' . $code[ID] . '\' AND ap.STUDENT_ID=ssm.STUDENT_ID
                        AND ap.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND ' . (($_REQUEST['myclasses'] == 'my_classes') ? '(cp.TEACHER_ID=\'' . User('STAFF_ID') . '\' OR cp.SECONDARY_TEACHER_ID=\'' . User('STAFF_ID') . '\')' : 'cp.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\'') . '
                        AND ap.SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\') AS ABS_' . $code[ID] . '';
            } else {
                $extra['SELECT'] .= ',(SELECT count(*) FROM attendance_period ap,attendance_codes ac
                        WHERE ac.ID=ap.ATTENDANCE_CODE AND ac.ID=\'' . $code[ID] . '\' AND ap.STUDENT_ID=ssm.STUDENT_ID
                        AND ap.SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\') AS ABS_' . $code[ID] . '';
            }

            $extra['columns_after']["ABS_" . $code[ID] . ""] = $code['TITLE'];
        }
    }
}
$extra['link']['FULL_NAME']['link'] = "Modules.php?modname=$_REQUEST[modname]&day_start=$_REQUEST[day_start]&day_end=$_REQUEST[day_end]&month_start=$_REQUEST[month_start]&month_end=$_REQUEST[month_end]&year_start=$_REQUEST[year_start]&year_end=$_REQUEST[year_end]&period_id=$_REQUEST[period_id]&myclasses=$_REQUEST[myclasses]";
$extra['link']['FULL_NAME']['variables'] = array('student_id' => 'STUDENT_ID');
if ((!$_REQUEST['search_modfunc'] || $_openSIS['modules_search']) && !$_REQUEST['student_id'])
    $extra['new'] = true;


$extra['search'] .= '<div class="row">';
$extra['search'] .= '<div class="col-lg-6">';
Widgets('activity');
$extra['search'] .= '</div>'; //.col-lg-6
$extra['search'] .= '<div class="col-lg-6">';
Widgets('course');
$extra['search'] .= '</div>';
$extra['search'] .= '</div>'; //.row

$extra['search'] .= '<div class="row">';
$extra['search'] .= '<div class="col-lg-6">';
Widgets('absences');
$extra['search'] .= '</div>';
$extra['search'] .= '</div>'; //.row


if (!$_SESSION['student_id'])
    Search_absence_summary('student_id', $extra);


/*
 * Course Selection Modal Start
 */
echo '<div id="modal_default" class="modal fade">';
echo '<div class="modal-dialog modal-lg">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<button type="button" class="close" data-dismiss="modal">×</button>';
echo '<h4 class="modal-title">'._chooseCourse.'</h4>';
echo '</div>';

echo '<div class="modal-body">';
echo '<div id="conf_div" class="text-center"></div>';
echo '<div class="row" id="resp_table">';
echo '<div class="col-md-4">';
$sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
$QI = DBQuery($sql);
$subjects_RET = DBGet($QI);

echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? _subjectWas : _subjectsWere) . ' '._found.'.</h6>';
if (count($subjects_RET) > 0) {
    echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>'._subject.'</th></tr></thead>';
    foreach ($subjects_RET as $val) {
        echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearch(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
    }
    echo '</table>';
}
echo '</div>';
echo '<div class="col-md-4" id="course_modal"></div>';
echo '<div class="col-md-4" id="cp_modal"></div>';
echo '</div>'; //.row
echo '</div>'; //.modal-body
echo '</div>'; //.modal-content
echo '</div>'; //.modal-dialog
echo '</div>'; //.modal

if (UserStudentID()) {
    $name_RET = DBGet(DBQuery('SELECT concat(FIRST_NAME, \' \', COALESCE(concat(MIDDLE_NAME,\' \'),\' \'), LAST_NAME) AS FULL_NAME FROM students WHERE STUDENT_ID=\'' . UserStudentID() . '\''));

    $PHP_tmp_SELF = PreparePHP_SELF();
    $i = 0;
    if ($_REQUEST['myclasses'] != '') {
        $absences_RET = DBGet(DBQuery('SELECT ap.STUDENT_ID,ap.PERIOD_ID,ap.SCHOOL_DATE,ac.SHORT_NAME,ad.STATE_VALUE,ad.COMMENT AS OFFICE_COMMENT,ap.COMMENT AS TEACHER_COMMENT,ac.STATE_CODE FROM attendance_period ap,attendance_day ad,attendance_codes ac,course_periods cp WHERE ap.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND ' . (($_REQUEST['myclasses'] == 'my_classes') ? '(cp.TEACHER_ID=\'' . User('STAFF_ID') . '\' OR cp.SECONDARY_TEACHER_ID=\'' . User('STAFF_ID') . '\')' : 'cp.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\'') . ' AND ap.STUDENT_ID=ad.STUDENT_ID AND ap.SCHOOL_DATE=ad.SCHOOL_DATE AND ap.ATTENDANCE_CODE=ac.ID  AND ap.STUDENT_ID=\'' . UserStudentID() . '\' AND ap.SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\''), array(), array('SCHOOL_DATE', 'PERIOD_ID'));
    } else {
        $absences_RET = DBGet(DBQuery('SELECT ap.STUDENT_ID,ap.PERIOD_ID,ap.SCHOOL_DATE,ac.SHORT_NAME,ad.STATE_VALUE,ad.COMMENT AS OFFICE_COMMENT,ap.COMMENT AS TEACHER_COMMENT,ac.STATE_CODE FROM attendance_period ap,attendance_day ad,attendance_codes ac WHERE ap.STUDENT_ID=ad.STUDENT_ID AND ap.SCHOOL_DATE=ad.SCHOOL_DATE AND ap.ATTENDANCE_CODE=ac.ID  AND ap.STUDENT_ID=\'' . UserStudentID() . '\' AND ap.SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\''), array(), array('SCHOOL_DATE', 'PERIOD_ID'));
    }
    foreach ($absences_RET as $school_date => $absences) {

        $i++;
        $days_RET[$i]['SCHOOL_DATE'] = ProperDate($school_date);
        $days_RET[$i]['DAILY'] = _makeStateValue($absences[key($absences)][1]['STATE_VALUE']);
        $days_RET[$i]['OFFICE_COMMENT'] = $absences[key($absences)][1]['OFFICE_COMMENT'];
        $days_RET[$i]['TEACHER_COMMENT'] = $absences[key($absences)][1]['TEACHER_COMMENT'];
        foreach ($absences as $period_id => $absence) {
            $days_RET[$i][$period_id] = ($absence[1]['STATE_CODE'] != 'P' ? $absence[1]['SHORT_NAME'] : '');
        }
    }
    if ($_REQUEST['myclasses'] != '') {
        $periods_RET = DBGet(DBQuery('SELECT sp.PERIOD_ID,sp.SHORT_NAME FROM school_periods sp,schedule s,course_periods cp,course_period_var cpv WHERE sp.SCHOOL_ID=\'' . UserSchool() . '\' AND sp.SYEAR=\'' . UserSyear() . '\' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND s.STUDENT_ID=\'' . UserStudentID() . '\' AND ' . (($_REQUEST['myclasses'] == 'my_classes') ? '(cp.TEACHER_ID=\'' . User('STAFF_ID') . '\' OR cp.SECONDARY_TEACHER_ID=\'' . User('STAFF_ID') . '\')' : 'cp.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\'') . ' AND cp.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID AND cpv.PERIOD_ID=sp.PERIOD_ID AND cpv.DOES_ATTENDANCE=\'Y\' ORDER BY sp.SORT_ORDER'));
    } else {
        $periods_RET = DBGet(DBQuery('SELECT sp.PERIOD_ID,sp.SHORT_NAME FROM school_periods sp,schedule s,course_periods cp,course_period_var cpv WHERE sp.SCHOOL_ID=\'' . UserSchool() . '\' AND sp.SYEAR=\'' . UserSyear() . '\' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND s.STUDENT_ID=\'' . UserStudentID() . '\' AND cp.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID AND cpv.PERIOD_ID=sp.PERIOD_ID AND cpv.DOES_ATTENDANCE=\'Y\' ORDER BY sp.SORT_ORDER'));
    }
    $columns['SCHOOL_DATE'] = _date;
    $columns['DAILY'] = _present;
    $columns['OFFICE_COMMENT'] = _adminOfficeComment;
    $columns['TEACHER_COMMENT'] = _teacherComment;
    foreach ($periods_RET as $period)
        $columns[$period['PERIOD_ID']] = $period['SHORT_NAME'];


    //echo '<div class="panel panel-default">';
    PopTable('header', $name_RET[1]['FULL_NAME']);
    ListOutputWithStudentInfo($days_RET, $columns, _day, _days);
    PopTable('footer');
    //echo '</div>'; //.panel.panel-default
}

function _makeStateValue($value) {
    global $THIS_RET, $date;

    if ($value == '0.0')
        return 'None';
    elseif ($value == '.5')
        return 'Half-Day';
    else
        return 'Full-Day';
}

?>
