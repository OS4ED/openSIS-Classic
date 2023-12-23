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
error_reporting(0);
include('../../RedirectModulesInc.php');
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
DrawBC(""._extracurricular." > " . ProgramTitle());
if ($_REQUEST['search_modfunc'] || User('PROFILE') == 'parent' || User('PROFILE') == 'student') {
    $tmp_PHP_SELF = PreparePHP_SELF();
    echo "<FORM name=stud_list id=stud_list action=$tmp_PHP_SELF method=POST>";

    $begin_year = DBGet(DBQuery('SELECT min(unix_timestamp(SCHOOL_DATE)) as SCHOOL_DATE FROM attendance_calendar WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''));
    $begin_year = $begin_year[1]['SCHOOL_DATE'];

    $date_select = "<OPTION value=$start>" . date('M d, Y', $start) . ' - ' . date('M d, Y', $end) . '</OPTION>';

    if ($begin_year != "" || !begin_year) {
        for ($i = $start - (60 * 60 * 24 * 7); $i >= $begin_year; $i-=(60 * 60 * 24 * 7))
            $date_select .= "<OPTION value=$i" . (($i + 86400 >= $start_time && $i - 86400 <= $start_time) ? ' SELECTED' : '') . ">" . date('M d, Y', $i) . ' - ' . date('M d, Y', ($i + 1 + (($END_DAY - $START_DAY)) * 60 * 60 * 24)) . '</OPTION>';
    }

    echo '<div class="panel panel-default">';
    echo '<div class="panel-heading">';
    echo '<div class="row">';
    echo '<div class="col-md-4">';
    echo '<div class="input-group"><SELECT name=start_date class=form-control>' . $date_select . '</SELECT>' . $period_select, '<span class="input-group-btn"><INPUT type=submit class="btn btn-primary" value='._go.' onclick=\'formload_ajax("stud_list");\'></span></div>';
    echo '</div>'; //.col-md-4
    echo '</div>'; //.row
    echo '</div>'; //.panel-heading
    echo '</div>'; //.panel.panel-default
    echo '</FORM>';
}
$extra['SELECT'] = ',e.ELIGIBILITY_CODE,c.TITLE as COURSE_TITLE';
$extra['FROM'] = ',eligibility e,courses c,course_periods cp';
$extra['WHERE'] = ' AND e.STUDENT_ID=ssm.STUDENT_ID AND e.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND cp.COURSE_ID=c.COURSE_ID AND e.SCHOOL_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\'';

$extra['functions'] = array('ELIGIBILITY_CODE' => '_makeLower');
$extra['group'] = array('STUDENT_ID');


$extra['search'] .= '<div class="row">';
$extra['search'] .= '<div class="col-md-6">';
Widgets('course');
$extra['search'] .= '</div>'; //.col-md-6
$extra['search'] .= '<div class="col-md-6">';
Widgets('eligibility');
$extra['search'] .= '</div>'; //.col-md-6
$extra['search'] .= '</div>'; //.row

$extra['search'] .= '<div class="row">';
$extra['search'] .= '<div class="col-md-6">';
Widgets('activity');
$extra['search'] .= '</div>'; //.col-md-6
$extra['search'] .= '</div>'; //.row

if (!$_REQUEST['search_modfunc'] && User('PROFILE') != 'parent' && User('PROFILE') != 'student') {
    $extra['new'] = true;
    Search('student_id', $extra);
} else {
    $RET = GetStuList($extra);
    $columns = array('FULL_NAME' =>_student,
     'COURSE_TITLE' =>_course,
     'ELIGIBILITY_CODE' =>_grade,
    );
    echo '<div class="panel">';
    ListOutput($RET, $columns,  _student, _students, array(), array('STUDENT_ID' => array('FULL_NAME', 'STUDENT_ID')));
    echo '</div>';
}


/*
 * Course Modal
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

echo count($subjects_RET) . ((count($subjects_RET) == 1) ? ' '._subjectWas : ' '._subjectsWere) . ' found.<br>';
if (count($subjects_RET) > 0) {
        echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>'._subject.'</th></tr></thead><tbody>';
    foreach ($subjects_RET as $val) {
        echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearch(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
    }
    echo '</tbody></table>';
}
echo '</div>';
echo '<div class="col-md-4" id="course_modal"></div>';
echo '<div class="col-md-4" id="cp_modal"></div>';
echo '</div>'; //.row
echo '</div>'; //.modal-body
echo '</div>'; //.modal-content
echo '</div>'; //.modal-dialog
echo '</div>'; //.modal



/*
 * Functions
 */

function _makeLower($word) {
    return ucwords(strtolower($word));
}

?>