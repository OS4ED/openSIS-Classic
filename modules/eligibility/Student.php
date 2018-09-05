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
DrawBC("Extracurricular > " . ProgramTitle());

$extra['search'] .= '<div class="row">';
$extra['search'] .= '<div class="col-md-6">';
Widgets('activity');
$extra['search'] .= '</div>'; //.col-md-6
$extra['search'] .= '<div class="col-md-6">';
Widgets('eligibility');
$extra['search'] .= '</div>'; //.col-md-6
$extra['search'] .= '</div>'; //.row

$extra['search'] .= '<div class="row">';
$extra['search'] .= '<div class="col-md-6">';
Widgets('course');
$extra['search'] .= '</div>'; //.col-md-6
$extra['search'] .= '</div>'; //.row
Search('student_id', $extra);
if($_REQUEST['search_modfunc']!='list')
{
echo '<div id="modal_default" class="modal fade">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h5 class="modal-title">Choose course</h5>
</div>

<div class="modal-body">';
echo '<center><div id="conf_div"></div></center>';
echo'<table id="resp_table"><tr><td valign="top">';
echo '<div>';
   $sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='".UserSchool()."' AND SYEAR='".UserSyear()."' ORDER BY TITLE";
$QI = DBQuery($sql);
$subjects_RET = DBGet($QI);

echo count($subjects_RET). ((count($subjects_RET)==1)?' Subject was':' Subjects were').' found.<br>';
if(count($subjects_RET)>0)
{
echo '<table class="table table-bordered"><tr class="bg-grey-200"><th>Subject</th></tr>'; 
foreach($subjects_RET as $val)
{
echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearch('.$val['SUBJECT_ID'].',\'courses\')">'.$val['TITLE'].'</a></td></tr>';
}
echo '</table>';
}
echo '</div></td>';
echo '<td valign="top"><div id="course_modal"></div></td>';
echo '<td valign="top"><div id="cp_modal"></div></td>';
echo '</tr></table>';
//         echo '<div id="coursem"><div id="cpem"></div></div>';
echo' </div>
</div>
</div>
</div>';
}
if ($_REQUEST['modfunc'] == 'add' || $_REQUEST['student_id']) {
    if ($_REQUEST['student_id'])
        $RET = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME,NAME_SUFFIX FROM students WHERE STUDENT_ID=\'' . $_REQUEST['student_id'] . '\''));
    else
        $RET = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME,NAME_SUFFIX FROM students WHERE STUDENT_ID=\'' . UserStudentID() . '\''));
    $count_student_RET = DBGet(DBQuery('SELECT COUNT(*) AS NUM FROM students'));
    if ($count_student_RET[1]['NUM'] > 1) {
        DrawHeaderHome('<div class="panel"><div class="panel-heading"><h6 class="panel-title">Selected Student : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'] . '</h6> <div class="heading-elements"><span class="heading-text"><A HREF=Modules.php?modname=' . clean_param($_REQUEST['modname'], PARAM_NOTAGS) . '&search_modfunc=list&next_modname=Students/Student.php&ajax=true&bottom_back=true&return_session=true target=body><i class="icon-square-left"></i> Back to Student List</A></span><div class="btn-group heading-btn"><A HREF=Side.php?student_id=new&modcat=' . clean_param($_REQUEST['modcat'], PARAM_NOTAGS) . ' class="btn btn-danger btn-xs">Deselect</A></div></div></div></div>');
    } else if ($count_student_RET[1]['NUM'] == 1) {
        DrawHeaderHome('<div class="panel"><div class="panel-heading"><h6 class="panel-title">Selected Student : ' . $RET[1]['FIRST_NAME'] . '&nbsp;' . ($RET[1]['MIDDLE_NAME'] ? $RET[1]['MIDDLE_NAME'] . ' ' : '') . $RET[1]['LAST_NAME'] . '&nbsp;' . $RET[1]['NAME_SUFFIX'] . '</h6> <div class="heading-elements"><span class="heading-text"></span><A HREF=Side.php?student_id=new&modcat=' . clean_param($_REQUEST['modcat'], PARAM_NOTAGS) . ' class="btn btn-danger btn-xs">Deselect</A></div></div></div>');
    }
}
if ($_REQUEST['modfunc'] == 'add' && AllowEdit()) {
    $stu_act_record = DBGet(DBQuery('SELECT ACTIVITY_ID FROM student_eligibility_activities WHERE STUDENT_ID=' . UserStudentID() . ' AND SYEAR=' . UserSyear()));
    foreach ($stu_act_record as $rec_k => $rec_v)
        foreach ($rec_v as $recr_k => $recr_v)
            $ret[] = $recr_v;

    if (in_array($_REQUEST['new_activity'], $ret)) {
        echo'<p style=color:red>Duplicate entry of activity is not permissible</p>';
        unset($_REQUEST['modfunc']);
    } else {
        DBQuery('INSERT INTO student_eligibility_activities (STUDENT_ID,ACTIVITY_ID,SYEAR) values(\'' . UserStudentID() . '\',\'' . $_REQUEST['new_activity'] . '\',\'' . UserSyear() . '\')');
        unset($_REQUEST['modfunc']);
    }
}
if ($_REQUEST['modfunc'] == 'remove' && AllowEdit()) {
    if (DeletePromptMod('activity')) {
        DBQuery('DELETE FROM student_eligibility_activities WHERE STUDENT_ID=\'' . UserStudentID() . '\' AND ACTIVITY_ID=\'' . $_REQUEST['activity_id'] . '\' AND SYEAR=\'' . UserSyear() . '\'');
        unset($_REQUEST['modfunc']);
    }
}

if (UserStudentID() && !$_REQUEST['modfunc']) {
    $start_end_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_config WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND PROGRAM=\'eligibility\' AND TITLE IN (\'' . 'START_DAY' . '\',\'' . 'END_DAY' . '\')'));
    if (count($start_end_RET)) {
        foreach ($start_end_RET as $value)
            $$value['TITLE'] = $value['VALUE'];
    }
    switch (date('D')) {
        case 'Mon':
            $today = 1;
            break;
        case 'Tue':
            $today = 2;
            break;
        case 'Wed':
            $today = 3;
            break;
        case 'Thu':
            $today = 4;
            break;
        case 'Fri':
            $today = 5;
            break;
        case 'Sat':
            $today = 6;
            break;
        case 'Sun':
            $today = 7;
            break;
    }

    $start = time() - ($today - $START_DAY) * 60 * 60 * 24;
    $end = time();

    if (!$_REQUEST['start_date']) {
        $start_time = $start;
        $start_date = strtoupper(date('d-M-y', $start_time));
        $end_date = strtoupper(date('d-M-y', $end));
    } else {
        $start_time = $_REQUEST['start_date'];
        $start_date = strtoupper(date('d-M-y', $start_time));
        $end_date = strtoupper(date('d-M-y', $start_time + 60 * 60 * 24 * 7));
    }

    $sql = 'SELECT min(unix_timestamp(SCHOOL_DATE)) as SCHOOL_DATE FROM attendance_calendar WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'';
    $begin_year = DBGet(DBQuery($sql));
    $begin_year = $begin_year[1]['SCHOOL_DATE'];

    $date_select = "<OPTION value=$start>" . date('M d, Y', $start) . ' - ' . date('M d, Y', $end) . '</OPTION>';

    if ($begin_year != "" || !begin_year) {
        for ($i = $start - (60 * 60 * 24 * 7); $i >= $begin_year; $i-=(60 * 60 * 24 * 7)) {
            $date_select .= "<OPTION value='" . $i . '-' . ($i + 1 + (($END_DAY - $START_DAY)) * 60 * 60 * 24) . "'" . (($i + 86400 >= $start_time && $i - 86400 <= $start_time) ? ' SELECTED' : '') . ">" . date('M d, Y', $i) . ' - ' . date('M d, Y', ($i + 1 + (($END_DAY - $START_DAY)) * 60 * 60 * 24)) . '</OPTION>';
        }
    }

    echo '<div class="panel panel-default">';
    echo '<div class="panel-heading">';
    echo "<FORM class=no-margin name=elig_stud id=elig_stud action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . " method=POST>";
    echo '<div class="row">';
    echo '<div class="col-md-4">';
    echo '<div class="input-group"><SELECT name=start_date class=form-control>' . $date_select . '</SELECT><span class="input-group-btn"><INPUT type=submit value=Go class="btn btn-primary" onclick=\'formload_ajax("elig_stud");\' ></span></div>';
    echo '</div>'; //.col-md-4
    echo '</div>'; //.row
    echo '</FORM>';
    echo '</div>'; //.panel-heading
    
    echo '<div class="row">';
    echo '<div class="col-md-6">';
    $qr = 'SELECT em.STUDENT_ID,em.ACTIVITY_ID,ea.TITLE,ea.START_DATE,ea.END_DATE FROM eligibility_activities ea,student_eligibility_activities em WHERE  em.STUDENT_ID=' . UserStudentID() . ' AND em.SYEAR=\'' . UserSyear() . '\'';
    if ($_REQUEST['start_date']) {
        $dates = explode('-', $_REQUEST['start_date']);
        $st_date = date('Y-m-d', $dates[0]);


        if ($dates[1] != '') {
            $qr.= ' AND ((\'' . $st_date . '\' BETWEEN ea.start_date AND ea.end_date) OR (\'' . date('Y-m-d', $dates[1]) . '\' BETWEEN ea.start_date AND ea.end_date))';
        } else
            $qr.= ' AND \'' . $st_date . '\' BETWEEN ea.start_date AND ea.end_date';
    }
    else {

        $st_date = date('Y-m-d');
        $qr.= ' AND \'' . $st_date . '\' BETWEEN ea.start_date AND ea.end_date';
    }
    $qr.= '  AND em.SYEAR=ea.SYEAR AND em.ACTIVITY_ID=ea.ID ORDER BY ea.START_DATE';

    $RET = DBGet(DBQuery($qr), array('START_DATE' => 'ProperDate', 'END_DATE' => 'ProperDate'));

    $activities_RET = DBGet(DBQuery('SELECT ID,TITLE FROM eligibility_activities WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
    if (count($activities_RET)) {
        foreach ($activities_RET as $value)
            $activities[$value['ID']] = $value['TITLE'];
    }


    $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=remove&start_date=$_REQUEST[start_date]";

    $link['remove']['variables'] = array('activity_id' => 'ACTIVITY_ID');
    $link['add']['html']['TITLE'] = '<TABLE border=0 cellpadding=0 cellspacing=0><TR><TD>' . SelectInput('', 'new_activity', '', $activities) . ' </TD><TD>&nbsp;<INPUT type=submit value=Add class="btn btn-primary" onclick=\'formload_ajax("elig_stud");\'></TD></TR></TABLE>';
    $link['add']['html']['remove'] = button('add');

    echo "<FORM action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=add&start_date=" . strip_tags(trim($_REQUEST[start_date])) . " method=POST>";
    $columns = array('TITLE' => 'Activity', 'START_DATE' => 'Starts', 'END_DATE' => 'Ends');
    ListOutput($RET, $columns, 'Activity', 'Activities', $link);

    echo '</FORM>';

    echo '</div><div class="col-md-6">';
   $RET = DBGet(DBQuery('SELECT e.ELIGIBILITY_CODE,c.TITLE as COURSE_TITLE FROM eligibility e,courses c,course_periods cp WHERE e.STUDENT_ID=\'' . UserStudentID() . '\' AND e.SYEAR=\'' . UserSyear() . '\' AND e.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND cp.COURSE_ID=c.COURSE_ID AND e.SCHOOL_DATE BETWEEN \'' . date('Y-m-d', strtotime($start_date)) . '\' AND \'' . date('Y-m-d', strtotime($end_date)) . '\''), array('ELIGIBILITY_CODE' => '_makeLower'));
    $columns = array('COURSE_TITLE' => 'Course', 'ELIGIBILITY_CODE' => 'Grade');
    ListOutputNew($RET, $columns, 'Course', 'Courses');


    echo '</div>'; //.col-md-6
    echo '</div>'; //.row
    
    echo '</div>'; //.panel
}

function _makeLower($word) {
    return ucwords(strtolower($word));
}

?>