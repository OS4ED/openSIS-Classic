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
DrawBC(""._attendance." > " . ProgramTitle());
//////////////////////////////For new date picker///////////////////////////////////////////////////////
if ($_REQUEST['day_start'] && $_REQUEST['month_start'] && $_REQUEST['year_start']) {
    $start_date = $_REQUEST['year_start'] . '-' . $_REQUEST['month_start'] . '-' . $_REQUEST['day_start'];
//    $start_date = ProperDateMAvr($start_date);
} else {
    $start_date = date('Y-m') . '-01';
}
if ($_REQUEST['day_end'] && $_REQUEST['month_end'] && $_REQUEST['year_end']) {
    $end_date = $_REQUEST['year_end'] . '-' . $_REQUEST['month_end'] . '-' . $_REQUEST['day_end'];
//    $end_date = ProperDateMAvr($end_date);
} else {
    $end_date = ProperDateMAvr();
}
DrawBC(""._attendance." > " . ProgramTitle());
####################
$selectedStudentId = isset($_REQUEST['student_id']) ? $_REQUEST['student_id'] : UserStudentID();
if (isset($_REQUEST['student_id']) || UserStudentID()) {
    $RET = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME,NAME_SUFFIX,SCHOOL_ID FROM students,student_enrollment WHERE students.STUDENT_ID=\'' . $selectedStudentId . '\' AND student_enrollment.STUDENT_ID = students.STUDENT_ID '));
    $count_student_RET = DBGet(DBQuery('SELECT COUNT(*) AS NUM FROM students'));
    if ($count_student_RET[1]['NUM'] > 1) {
        DrawHeaderHome('<div class="panel"><div class="panel-heading"><h6 class="panel-title">'._selectedStudent.' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'] . '</h6> <div class="heading-elements"><span class="heading-text"><A HREF=Modules.php?modname=' . $_REQUEST['modname'] . '&search_modfunc=list&next_modname=' . $_REQUEST['modname'] . '&ajax=true&bottom_back=true&return_session=true target=body><i class="icon-square-left"></i> '._backToStudentList.'</A></span><div class="btn-group heading-btn"><A HREF=Side.php?student_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">'._deselect.'</A></div></div></div></div>');
    } else if ($count_student_RET[1]['NUM'] == 1) {
        DrawHeaderHome('<div class="panel"><div class="panel-heading"><h6 class="panel-title">'._selectedStudent.' : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'] . '</h6> <div class="heading-elements"><A HREF=Side.php?student_id=new&modcat=' . $_REQUEST['modcat'] . ' class="btn btn-danger btn-xs">'._deselect.'</A></div></div></div>');
    }
}
####################
if ($_REQUEST['attendance'] && ($_POST['attendance'] || $_REQUEST['ajax']) && AllowEdit()) {
    foreach ($_REQUEST['attendance'] as $student_id => $values) {
        foreach ($values as $school_date => $columns) {
            $sql = 'UPDATE attendance_period SET ADMIN=\'Y\',';
            foreach ($columns as $column => $value)
                $sql .= $column . "='" . str_replace("\'", "''", $value) . "',";

            $sql = substr($sql, 0, -1) . ' WHERE SCHOOL_DATE=\'' . date('Y-m-d', strtotime($school_date)) . '\' AND PERIOD_ID=\'' . optional_param('period_id', '', PARAM_SPCL) . '\' AND STUDENT_ID=\'' . $student_id . '\'';
            DBQuery($sql);
            UpdateAttendanceDaily($student_id, $school_date);
        }
    }

    $current_RET = DBGet(DBQuery('SELECT ATTENDANCE_TEACHER_CODE,ATTENDANCE_CODE,ATTENDANCE_REASON,STUDENT_ID,ADMIN,COURSE_PERIOD_ID FROM attendance_period WHERE SCHOOL_DATE=\'' . date('Y-m-d', strtotime($date)) . '\''), array(), array('STUDENT_ID', 'COURSE_PERIOD_ID'));
    unset($_REQUEST['attendance']);
}

$PHP_tmp_SELF = PreparePHP_SELF();
//echo "<FORM class=\"form-horizontal\" action=$PHP_tmp_SELF method=POST>";
if(isset($_REQUEST['search_modfunc']) && $_REQUEST['search_modfunc']=='list')
echo "<FORM class=\"form-horizontal\" action=$PHP_tmp_SELF method=POST>";
else
echo "<FORM class=\"form-horizontal\" action=Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[modfunc] method=POST>";

//Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=
echo "<div class=\"panel panel-default\">";
if ($_REQUEST['search_modfunc'] || $_REQUEST['student_id'] || UserStudentID() || User('PROFILE') == 'parent' || User('PROFILE') == 'student') {
    $extraM .= "";
    $period_select = "<SELECT name=period_id onchange='this.form.submit();' class='form-control'><OPTION value=\"\">Daily</OPTION>";
    if (!UserStudentID() && !$_REQUEST['student_id']) {
        $periods_RET = DBGet(DBQuery('SELECT PERIOD_ID,TITLE FROM school_periods WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER'));
        if (count($periods_RET) > 1) {
            foreach ($periods_RET as $period) {
                $period_select .= "<OPTION value=" . $period['PERIOD_ID'] . (($_REQUEST['period_id'] == $period['PERIOD_ID']) ? ' SELECTED' : '') . ">" . $period['TITLE'] . '</OPTION>';
            }
        } elseif (count($periods_RET) == 1) {
            foreach ($periods_RET as $period) {

                $period_select .= "<OPTION value=" . $period['PERIOD_ID'] . ((optional_param('period_id', '', PARAM_SPCL) == $period['PERIOD_ID'] || !isset($_REQUEST['period_id'])) ? ' SELECTED' : '') . ">" . $period['TITLE'] . '</OPTION>';
            }

            if (!isset($_REQUEST['period_id'])) {
                $_REQUEST['period_id'] = $period['PERIOD_ID'];
            }
        }
    } else {
        $period_select .= '<OPTION value="PERIOD"' . ($_REQUEST['period_id'] ? ' SELECTED' : '') . '>By Period</OPTION>';
    }

    $period_select .= '</SELECT>';
    if (User('PROFILE') == 'teacher') {
        $myclasses = '<SELECT class="form-control" name="myclasses" onchange="this.form.submit();">';
        $myclasses .='<OPTION value=""' . ($_REQUEST['myclasses'] == '' ? ' SELECTED' : '') . '>All course periods</OPTION>';
        $myclasses .='<OPTION value="my_classes"' . ($_REQUEST['myclasses'] == 'my_classes' ? ' SELECTED' : '') . '>All my course periods</OPTION>';
        $myclasses .='<OPTION value="selected_class"' . ($_REQUEST['myclasses'] == 'selected_class' ? ' SELECTED' : '') . '>Selected course period</OPTION>';
        $myclasses .='</SELECT>';
    }

    echo '<div class="panel-heading">';
    echo '<div class="form-inline clearfix"><div class="col-md-12"><div class="inline-block">' . DateInputAY($start_date, 'start', 1) . '</div><div class="inline-block" style="margin: 0 10px;">&nbsp; - &nbsp;</div><div class="inline-block">' . DateInputAY($end_date, 'end', 2) . '</div><div style="display: inline-block; margin: 0 10px;">' . $period_select . '</div><div style="display: inline-block; margin: 0 10px 0 0;"><INPUT type=submit class="btn btn-primary" value='._go.'></div><div style="display: inline-block; margin: 0 10px 0 0;">' . (($_REQUEST['period_id']) ? $myclasses : '') . '</div></div></div>';
    echo '</div>'; //.panel-body
}

$cal_RET = DBGet(DBQuery('SELECT DISTINCT SCHOOL_DATE,CONCAT(\'_\',DATE_FORMAT(SCHOOL_DATE,\'%Y%m%d\')) AS SHORT_DATE FROM attendance_calendar WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SCHOOL_DATE BETWEEN \'' . date('Y-m-d', strtotime($start_date)) . '\' AND \'' . date('Y-m-d', strtotime($end_date)) . '\' ORDER BY SCHOOL_DATE'));


if (UserStudentID() || $_REQUEST['student_id'] || User('PROFILE') == 'parent') {
    // JUST TO SET USERSTUDENTID()
    Search('student_id');

    $MP_TYPE_RET = DBGet(DBQuery('SELECT MP_TYPE FROM marking_periods WHERE MARKING_PERIOD_ID=\'' . UserMP() . '\' LIMIT 1'));
    $MP_TYPE = $MP_TYPE_RET[1]['MP_TYPE'];
    if ($MP_TYPE == 'year') {
        $MP_TYPE = 'FY';
    } else if ($MP_TYPE == 'semester') {
        $MP_TYPE = 'SEM';
    } else if ($MP_TYPE == 'quarter') {
        $MP_TYPE = 'QTR';
    } else {
        $MP_TYPE = '';
    }

    if ($_REQUEST['period_id']) {
        $sql = 'SELECT cp.TITLE as COURSE_PERIOD,sp.TITLE as PERIOD,cpv.PERIOD_ID, cp.COURSE_PERIOD_ID
                 FROM schedule s,courses c,course_periods cp,course_period_var cpv,school_periods sp
                 WHERE
                s.COURSE_ID = c.COURSE_ID AND s.COURSE_ID = cp.COURSE_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID
                AND s.COURSE_PERIOD_ID = cp.COURSE_PERIOD_ID AND cpv.PERIOD_ID = sp.PERIOD_ID AND cpv.DOES_ATTENDANCE=\'Y\'
                AND s.SYEAR = c.SYEAR AND (cp.MARKING_PERIOD_ID IN (' . GetAllMP($MP_TYPE, UserMP()) . ') OR cp.MARKING_PERIOD_ID IS NULL)
                AND s.STUDENT_ID=\'' . UserStudentID() . '\' AND s.SYEAR=\'' . UserSyear() . '\'
                ' . (($_REQUEST['myclasses'] != '') ? 'AND ' . (($_REQUEST['myclasses'] == 'my_classes') ? "(cp.TEACHER_ID='" . User('STAFF_ID') . "' OR cp.SECONDARY_TEACHER_ID='" . User('STAFF_ID') . "')" : "cp.COURSE_PERIOD_ID='" . UserCoursePeriod() . "'") : '') . '
             group by cp.course_period_id,sp.period_id ORDER BY sp.SORT_ORDER
            ';

        $schedule_RET = DBGet(DBQuery($sql));
        if ($_REQUEST['myclasses'] != '') {
            $sql = 'SELECT ap.SCHOOL_DATE,ap.COURSE_PERIOD_ID,ac.SHORT_NAME,ac.STATE_CODE,ac.DEFAULT_CODE FROM attendance_period ap,attendance_codes ac, course_periods cp WHERE ap.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND ' . (($_REQUEST['myclasses'] == 'my_classes') ? "(cp.TEACHER_ID='" . User('STAFF_ID') . "' OR cp.SECONDARY_TEACHER_ID='" . User('STAFF_ID') . "')" : "cp.COURSE_PERIOD_ID='" . UserCoursePeriod() . "'") . " AND ap.SCHOOL_DATE BETWEEN '" . date('Y-m-d', strtotime($start_date)) . "' AND '" . date('Y-m-d', strtotime($end_date)) . "' AND ap.ATTENDANCE_CODE=ac.ID AND ap.STUDENT_ID='" . UserStudentID() . "'";
        } else {
            $sql = 'SELECT ap.SCHOOL_DATE,ap.COURSE_PERIOD_ID,ac.SHORT_NAME,ac.STATE_CODE,ac.DEFAULT_CODE FROM attendance_period ap,attendance_codes ac WHERE ap.SCHOOL_DATE BETWEEN \'' . date('Y-m-d', strtotime($start_date)) . '\' AND \'' . date('Y-m-d', strtotime($end_date)) . '\' AND ap.ATTENDANCE_CODE=ac.ID AND ap.STUDENT_ID=\'' . UserStudentID() . '\'';
        }
        $attendance_RET = DBGet(DBQuery($sql), array(), array('SCHOOL_DATE', 'COURSE_PERIOD_ID'));
    } else {
        $_REQUEST['myclasses'] = '';
        $schedule_RET[1] = array('COURSE_PERIOD' => _dailyAttendance, 'COURSE_PERIOD_ID' => '0');
        if ($_REQUEST['myclasses'] != '') {
            $attendance_RET = DBGet(DBQuery('SELECT ad.SCHOOL_DATE,\'0\' AS COURSE_PERIOD_ID,ad.STATE_VALUE AS STATE_CODE,' . db_case(array('ad.STATE_VALUE', "'0.0'", "'A'", "'1.0'", "'P'", "'H'")) . ' AS SHORT_NAME FROM attendance_day ad, attendance_period ap, course_periods cp WHERE ad.STUDENT_ID=ap.STUDENT_ID AND ad.SCHOOL_DATE=ap.SCHOOL_DATE AND ap.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND ' . (($_REQUEST['myclasses'] == 'my_classes') ? '(cp.TEACHER_ID=\'' . User('STAFF_ID') . '\' OR cp.SECONDARY_TEACHER_ID=\'' . User('STAFF_ID') . '\')' : 'cp.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\'') . ' AND ad.SCHOOL_DATE BETWEEN \'' . date('Y-m-d', strtotime($start_date)) . '\' AND \'' . date('Y-m-d', strtotime($end_date)) . '\' AND ad.STUDENT_ID=\'' . UserStudentID() . '\''), array(), array('SCHOOL_DATE', 'COURSE_PERIOD_ID'));
        } else {
            $attendance_RET = DBGet(DBQuery('SELECT ad.SCHOOL_DATE,\'0\' AS COURSE_PERIOD_ID,ad.STATE_VALUE AS STATE_CODE,' . db_case(array('ad.STATE_VALUE', "'0.0'", "'A'", "'1.0'", "'P'", "'H'")) . ' AS SHORT_NAME FROM attendance_day ad WHERE ad.SCHOOL_DATE BETWEEN \'' . date('Y-m-d', strtotime($start_date)) . '\' AND \'' . date('Y-m-d', strtotime($end_date)) . '\' AND ad.STUDENT_ID=\'' . UserStudentID() . '\''), array(), array('SCHOOL_DATE', 'COURSE_PERIOD_ID'));
        }
    }

    $i = 0;

    if (count($schedule_RET)) {
        foreach ($schedule_RET as $course) {
            $i++;
            $student_RET[$i]['TITLE'] = $course['COURSE_PERIOD'];
            foreach ($cal_RET as $value)
                $student_RET[$i][$value['SHORT_DATE']] = _makePeriodColor($attendance_RET[$value['SCHOOL_DATE']][$course['COURSE_PERIOD_ID']][1]['SHORT_NAME'], $attendance_RET[$value['SCHOOL_DATE']][$course['COURSE_PERIOD_ID']][1]['STATE_CODE'], $attendance_RET[$value['SCHOOL_DATE']][$course['COURSE_PERIOD_ID']][1]['DEFAULT_CODE']);
        }
    }

    $columns = array('TITLE' => _coursePeriod);

    if (count($cal_RET)) {
        foreach ($cal_RET as $value)
            $columns[$value['SHORT_DATE']] = ShortDate($value['SCHOOL_DATE']);
    }

    echo '<div class="panel-body p-0">';
    //echo '<div class="table-responsive">';
    ListOutputWithStudentInfo($student_RET, $columns, _course, _courses);
    //echo '</div>';
    echo '</div>';
    echo '</div>'; //.panel
} else {
    // echo '</div>'; //.panel


    if (!$_REQUEST['period_id']) {
        $_REQUEST['myclasses'] = '';

//working
        if ($_REQUEST['myclasses'] != '') {
            if ($_REQUEST['include_inactive'] == 'Y') {
                $sql = 'SELECT ad.STATE_VALUE,ad.STUDENT_ID,ad.SCHOOL_DATE,CONCAT(\'_\',DATE_FORMAT(ad.SCHOOL_DATE,\'%Y%m%d\')) AS SHORT_DATE FROM attendance_day ad,student_enrollment ssm, attendance_period ap, course_periods cp WHERE ad.STUDENT_ID=ap.STUDENT_ID AND ad.SCHOOL_DATE=ap.SCHOOL_DATE AND ap.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND ' . (($_REQUEST['myclasses'] == 'my_classes') ? '(cp.TEACHER_ID=\'' . User('STAFF_ID') . '\' OR cp.SECONDARY_TEACHER_ID=\'' . User('STAFF_ID') . '\')' : 'cp.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\'') . ' AND ad.STUDENT_ID=ssm.STUDENT_ID AND \'' . date('Y-m-d', strtotime(DBDate())) . '\'>=ssm.START_DATE AND ssm.SCHOOL_ID=\'' . UserSchool() . '\' AND ad.SCHOOL_DATE BETWEEN \'' . date('Y-m-d', strtotime($start_date)) . '\' AND \'' . date('Y-m-d', strtotime($end_date)) . '\'';
            } else {
                $sql = 'SELECT ad.STATE_VALUE,ad.STUDENT_ID,ad.SCHOOL_DATE,CONCAT(\'_\',DATE_FORMAT(ad.SCHOOL_DATE,\'%Y%m%d\')) AS SHORT_DATE FROM attendance_day ad,student_enrollment ssm, attendance_period ap, course_periods cp WHERE ad.STUDENT_ID=ap.STUDENT_ID AND ad.SCHOOL_DATE=ap.SCHOOL_DATE AND ap.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND ' . (($_REQUEST['myclasses'] == 'my_classes') ? '(cp.TEACHER_ID=\'' . User('STAFF_ID') . '\' OR cp.SECONDARY_TEACHER_ID=\'' . User('STAFF_ID') . '\')' : 'cp.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\'') . ' AND ad.STUDENT_ID=ssm.STUDENT_ID AND (\'' . date('Y-m-d', strtotime(DBDate())) . '\' BETWEEN ssm.START_DATE AND ssm.END_DATE OR ssm.END_DATE IS NULL) AND \'' . date('Y-m-d', strtotime(DBDate())) . '\'>=ssm.START_DATE AND ssm.SCHOOL_ID=\'' . UserSchool() . '\' AND ad.SCHOOL_DATE BETWEEN \'' . date('Y-m-d', strtotime($start_date)) . '\' AND \'' . date('Y-m-d', strtotime($end_date)) . '\'';
            }
        } else {
            if ($_REQUEST['include_inactive'] == 'Y') {
                $sql = 'SELECT ad.STATE_VALUE,ad.STUDENT_ID,SCHOOL_DATE,CONCAT(\'_\',DATE_FORMAT(ad.SCHOOL_DATE,\'%Y%m%d\')) AS SHORT_DATE FROM attendance_day ad,student_enrollment ssm WHERE ad.STUDENT_ID=ssm.STUDENT_ID AND \'' . date('Y-m-d', strtotime(DBDate())) . '\'>=ssm.START_DATE AND ssm.SCHOOL_ID=\'' . UserSchool() . '\' AND SCHOOL_DATE BETWEEN \'' . date('Y-m-d', strtotime($start_date)) . '\' AND \'' . date('Y-m-d', strtotime($end_date)) . '\'';
            } else {
                $sql = 'SELECT ad.STATE_VALUE,ad.STUDENT_ID,SCHOOL_DATE,CONCAT(\'_\',DATE_FORMAT(ad.SCHOOL_DATE,\'%Y%m%d\')) AS SHORT_DATE FROM attendance_day ad,student_enrollment ssm WHERE ad.STUDENT_ID=ssm.STUDENT_ID AND (\'' . date('Y-m-d', strtotime(DBDate())) . '\' BETWEEN ssm.START_DATE AND ssm.END_DATE OR ssm.END_DATE IS NULL) AND \'' . date('Y-m-d', strtotime(DBDate())) . '\'>=ssm.START_DATE AND ssm.SCHOOL_ID=\'' . UserSchool() . '\' AND SCHOOL_DATE BETWEEN \'' . date('Y-m-d', strtotime($start_date)) . '\' AND \'' . date('Y-m-d', strtotime($end_date)) . '\'';
            }
        }


        $RET = DBGet(DBQuery($sql), array(), array('STUDENT_ID', 'SHORT_DATE'));
    } else {
        if ($_REQUEST['myclasses'] != '') {
            $sql = 'SELECT ap.ATTENDANCE_CODE,ap.STUDENT_ID,ap.SCHOOL_DATE,CONCAT(\'_\',DATE_FORMAT(ap.SCHOOL_DATE,\'%Y%m%d\')) AS SHORT_DATE FROM attendance_period ap,student_enrollment ssm,students s,course_periods cp WHERE ap.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND s.STUDENT_ID=ssm.STUDENT_ID AND ap.STUDENT_ID=ssm.STUDENT_ID AND ' . (($_REQUEST['myclasses'] == 'my_classes') ? '(cp.TEACHER_ID=\'' . User('STAFF_ID') . '\' OR cp.SECONDARY_TEACHER_ID=\'' . User('STAFF_ID') . '\')' : 'cp.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\'') . ' AND ap.PERIOD_ID=\'' . $_REQUEST['period_id'] . '\' AND ap.SCHOOL_DATE BETWEEN \'' . date('Y-m-d', strtotime($start_date)) . '\' AND \'' . date('Y-m-d', strtotime($end_date)) . '\'';
        } else {
            $sql = 'SELECT ap.ATTENDANCE_CODE,ap.STUDENT_ID,ap.SCHOOL_DATE,CONCAT(\'_\',DATE_FORMAT(ap.SCHOOL_DATE,\'%Y%m%d\')) AS SHORT_DATE FROM attendance_period ap,student_enrollment ssm,students s WHERE s.STUDENT_ID=ssm.STUDENT_ID AND ap.STUDENT_ID=ssm.STUDENT_ID AND ap.SCHOOL_DATE BETWEEN \'' . date('Y-m-d', strtotime($start_date)) . '\' AND \'' . date('Y-m-d', strtotime($end_date)) . '\' AND ap.PERIOD_ID=\'' . $_REQUEST['period_id'] . '\'';
        }
        if ($_REQUEST['include_inactive'] != 'Y') {
            $sql .= ' AND ((\'' . date('Y-m-d', strtotime(DBDate())) . '\' BETWEEN ssm.START_DATE AND ssm.END_DATE OR ssm.END_DATE IS NULL) AND \'' . DBDate() . '\'>=ssm.START_DATE) ';
        }

        if ($_REQUEST['_search_all_schools'] != 'Y') {
            $sql .= ' AND ssm.SCHOOL_ID=\'' . UserSchool() . '\' ';
        }
//        if()
//            print_r($_REQUEST);
            if($_REQUEST['stuid']!=''){
                $sql .= ' AND ssm.STUDENT_ID=\'' . $_REQUEST['stuid'] . '\' ';
            }
//            echo $sql;
// TODO Do not Delete       $sql = appendSQL($sql,$tmp_extra=array('NoSearchTerms'=>true)); // extra must be lvalue
        $RET = DBGet(DBQuery($sql), array(), array('STUDENT_ID', 'SHORT_DATE'));
    }

    if (count($cal_RET)) {
        foreach ($cal_RET as $value) {
            $extra['SELECT'] .= ",'' as _" . str_replace('-', '', $value['SCHOOL_DATE']);
            $extra['columns_after']['_' . str_replace('-', '', $value['SCHOOL_DATE'])] = ShortDate($value['SCHOOL_DATE']);
            $extra['functions']['_' . str_replace('-', '', $value['SCHOOL_DATE'])] = '_makeColor';

            $extra['link']['FULL_NAME']['link'] = "Modules.php?modname=" . optional_param('modname', '', PARAM_NOTAGS) . "&day_start=$_REQUEST[day_start]&day_end=$_REQUEST[day_end]&month_start=$_REQUEST[month_start]&month_end=$_REQUEST[month_end]&year_start=$_REQUEST[year_start]&year_end=$_REQUEST[year_end]&period_id=" . optional_param('period_id', '', PARAM_SPCL);
            $extra['link']['FULL_NAME']['variables'] = array('student_id' => 'STUDENT_ID');
        }
    }

    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-lg-6">';
    Widgets('absences');
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '<div class="col-lg-6">';
    Widgets('course');
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '</div>'; //.row

    $extra['new'] = true;

    Search('student_id', $extra);


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

    echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' '.subjectWas : ' '.subjectsWere) . ' found.</h6>';
    if (count($subjects_RET) > 0) {
        echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>Subject</th></tr></thead>';
        foreach ($subjects_RET as $val) {
            echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearch(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
        }
        echo '</table>';
    }
    echo '</div>'; //.col-md-4
    echo '<div class="col-md-4" id="course_modal"></div>';
    echo '<div class="col-md-4" id="cp_modal"></div>';
    echo '</div>'; //.row
    echo '</div>'; //.modal-body
    echo '</div>'; //.modal-content
    echo '</div>'; //.modal-dialog
    echo '</div>'; //.modal
}

echo '</FORM>';

function _makeColor($value, $column) {
    global $THIS_RET, $RET, $attendance_codes;

    if ($_REQUEST['period_id']) {
        if (!$attendance_codes)
            $attendance_codes = DBGet(DBQuery('SELECT ID,TITLE,DEFAULT_CODE,STATE_CODE,SHORT_NAME FROM attendance_codes WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND TABLE_NAME=\'0\''), array(), array('ID'));

        if ($attendance_codes[$RET[$THIS_RET['STUDENT_ID']][$column][1]['ATTENDANCE_CODE']][1]['DEFAULT_CODE'] == 'Y') {
            return "<TABLE bgcolor=#00FF00 cellpadding=0 cellspacing=0 width=10 class=LO_field><TR><TD>" . makeCodePulldown($RET[$THIS_RET['STUDENT_ID']][$column][1]['ATTENDANCE_CODE'], $THIS_RET['STUDENT_ID'], $column) . "</TD></TR></TABLE>";
        } elseif ($attendance_codes[$RET[$THIS_RET['STUDENT_ID']][$column][1]['ATTENDANCE_CODE']][1]['STATE_CODE'] == 'P') {
            return "<TABLE bgcolor=#FFCC00 cellpadding=0 cellspacing=0 width=10 class=LO_field><TR><TD>" . makeCodePulldown($RET[$THIS_RET['STUDENT_ID']][$column][1]['ATTENDANCE_CODE'], $THIS_RET['STUDENT_ID'], $column) . "</TD></TR></TABLE>";
        } elseif ($attendance_codes[$RET[$THIS_RET['STUDENT_ID']][$column][1]['ATTENDANCE_CODE']][1]['STATE_CODE'] == 'H') {
            return "<TABLE bgcolor=#FFCC00 cellpadding=0 cellspacing=0 width=10 class=LO_field><TR><TD>" . makeCodePulldown($RET[$THIS_RET['STUDENT_ID']][$column][1]['ATTENDANCE_CODE'], $THIS_RET['STUDENT_ID'], $column) . "</TD></TR></TABLE>";
        } elseif ($attendance_codes[$RET[$THIS_RET['STUDENT_ID']][$column][1]['ATTENDANCE_CODE']][1]['STATE_CODE'] == 'A') {
            return "<TABLE bgcolor=#FF0000 cellpadding=0 cellspacing=0 width=10 class=LO_field><TR><TD>" . makeCodePulldown($RET[$THIS_RET['STUDENT_ID']][$column][1]['ATTENDANCE_CODE'], $THIS_RET['STUDENT_ID'], $column) . "</TD></TR></TABLE>";
        }
    } else {
        if ($RET[$THIS_RET['STUDENT_ID']][$column][1]['STATE_VALUE'] == '0.0') {
            return "<TABLE bgcolor=#FF0000 cellpadding=0 cellspacing=0 width=10 class=LO_field><TR><TD>A</TD></TR></TABLE>";
        } elseif ($RET[$THIS_RET['STUDENT_ID']][$column][1]['STATE_VALUE'] > 0 && $RET[$THIS_RET['STUDENT_ID']][$column][1]['STATE_VALUE'] < 1) {
            return "<TABLE bgcolor=#FFCC00 cellpadding=0 cellspacing=0 width=10 class=LO_field><TR><TD>H</TD></TR></TABLE>";
        } elseif ($RET[$THIS_RET['STUDENT_ID']][$column][1]['STATE_VALUE'] == 1) {
            return "<TABLE bgcolor=#00FF00 cellpadding=0 cellspacing=0 width=10 class=LO_field><TR><TD>P</TD></TR></TABLE>";
        }
    }
}

function _makePeriodColor($name, $state_code, $default_code) {
    if ($state_code == 'A' || $state_code == '0.0') {
        $color = '#FF0000';
    } elseif ($default_code == 'Y' || $state_code == '1.0') {
        $color = '#00FF00';
    } elseif ($state_code == 'P' || is_numeric($state_code)) {
        $color = '#FFCC00';
    } elseif ($state_code == 'H' || is_numeric($state_code)) {
        $color = '#FFCC00';
    }

    if ($color) {
        return "<TABLE bgcolor=$color cellpadding=0 cellspacing=0 width=10><TR><TD>$name</TD></TR></TABLE>";
    } else {
        return false;
    }
}

function makeCodePulldown($value, $student_id, $date) {
    global $THIS_RET, $attendance_codes, $_openSIS;

    $date = substr($date, 1, 4) . '-' . substr($date, 5, 2) . '-' . substr($date, 7);

    if (!$_openSIS['code_options']) {
        foreach ($attendance_codes as $id => $code)
            $_openSIS['code_options'][$id] = ($code[1]['SHORT_NAME'] == '' ? $code[1]['TITLE'] : $code[1]['SHORT_NAME']);
    }

    return SelectInput($value, 'attendance[' . $student_id . '][' . $date . '][ATTENDANCE_CODE]', '', $_openSIS['code_options']);
}

?>
