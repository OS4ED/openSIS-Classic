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

echo '<div class="panel panel-default">';
echo "<FORM class=\"no-margin\" name=scheaddr id=scheaddr action=" . PreparePHP_SELF() . " method=POST>";
DrawBC(""._scheduling." > " . ProgramTitle());
if ($_REQUEST['day__start'] && $_REQUEST['month__start'] && $_REQUEST['year__start']) {
    $_REQUEST['placed_From'] = $_REQUEST['day__start'] . '-' . $_REQUEST['month__start'] . '-' . $_REQUEST['year__start'];
    $start_date = (date('Y-m-d', strtotime($_REQUEST['placed_From'])));
} else
    $start_date = date("Y-m") . '-01';
if ($_REQUEST['day__end'] && $_REQUEST['month__end'] && $_REQUEST['year__end']) {
    $_REQUEST['placed_End'] = $_REQUEST['day__end'] . '-' . $_REQUEST['month__end'] . '-' . $_REQUEST['year__end'];
    $end_date = (date('Y-m-d', strtotime($_REQUEST['placed_End'])));
} else
    $end_date = date("Y-m-d");
if ($_REQUEST['flag'] != 'list')
    DrawHeader('<div class="form-inline">'.PrepareDateSchedule($start_date, '_start') . '<div class="form-group"><label class="control-label">-</label></div>' . PrepareDateSchedule($end_date, '_end').'</div>', '<INPUT type=submit class="btn btn-primary" value='._go.' >');
echo '</FORM>';
echo '<hr class="no-margin" />';
if ($_REQUEST['modfunc'] == 'save') {
    $a = count($_REQUEST['st_arr']);
    if ($a == 0) {
        echo ""._sorryNoStudentsWereSelected."";
    } else {
        if (count($_REQUEST['st_arr'])) {
            $st_list = '\'' . implode('\',\'', $_REQUEST['st_arr']) . '\'';
            $extra['WHERE'] = '  se.ID IN (' . $st_list . ')';
        }

        $start_date = $_REQUEST['sday'];
        $end_date = $_REQUEST['eday'];
        $enrollment_RET = DBGet(DBQuery('SELECT c.TITLE AS COURSE_TITLE,cp.TITLE,se.START_DATE AS START_DATE,se.END_DATE AS END_DATE,se.END_DATE AS DATE,se.STUDENT_ID,CONCAT(s.LAST_NAME,\'' . ',' . '\',s.FIRST_NAME) AS FULL_NAME FROM schedule se,students s,courses c,course_periods cp WHERE c.COURSE_ID=se.COURSE_ID AND cp.COURSE_PERIOD_ID=se.COURSE_PERIOD_ID AND cp.COURSE_ID=c.COURSE_ID AND s.STUDENT_ID=se.STUDENT_ID AND se.SCHOOL_ID=\'' . UserSchool() . '\' AND (se.START_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\' OR se.END_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\') AND ' . $extra['WHERE'] . '
								ORDER BY DATE DESC'), array('START_DATE' => 'ProperDate', 'END_DATE' => 'ProperDate'));

        $columns = array('FULL_NAME' =>_student, 'STUDENT_ID' =>_studentId, 'COURSE_TITLE' =>_course, 'TITLE' =>_coursePeriod, 'START_DATE' =>_enrolled, 'END_DATE' =>_dropped);
        if (count($enrollment_RET) > 0)
            echo "<table width=100%><tr><td width=105>" . DrawLogo() . "</td><td  style=\"font-size:15px; font-weight:bold; padding-top:20px;\">" . GetSchool(UserSchool()) . "<div style=\"font-size:12px;\">"._addDropReport."</div></td><td align=right style=\"padding-top:20px;\">" . ProperDate(DBDate()) . "<br />"._poweredByOpenSis." </td></tr><tr><td colspan=3 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";

        ListOutputPrint($enrollment_RET, $columns, _scheduleRecord , _scheduleRecords);
    }
}
else {
    echo "<FORM class=\"no-margin\" name=addr id=addr action='ForExport.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&head_html=Add+/+Drop+Report&modfunc=save&sday=$start_date&eday=$end_date&include_inactive=" . strip_tags(trim($_REQUEST['include_inactive'])) . "&_openSIS_PDF=true&flag=list' method=POST target=_blank>";
    $enrollment_RET1 = DBGet(DBQuery('SELECT se.ID CHECKBOX,c.TITLE AS COURSE_TITLE,cp.TITLE,se.START_DATE AS START_DATE,se.END_DATE AS END_DATE,se.END_DATE AS DATE,se.STUDENT_ID,CONCAT(s.LAST_NAME,\'' . ',' . '\',s.FIRST_NAME) AS FULL_NAME FROM schedule se,students s,courses c,course_periods cp WHERE c.COURSE_ID=se.COURSE_ID AND cp.COURSE_PERIOD_ID=se.COURSE_PERIOD_ID AND cp.COURSE_ID=c.COURSE_ID AND s.STUDENT_ID=se.STUDENT_ID AND se.SCHOOL_ID=\'' . UserSchool() . '\' AND (se.START_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\' OR se.END_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\')
								ORDER BY DATE DESC'), array('START_DATE' => 'ProperDate', 'END_DATE' => 'ProperDate', 'CHECKBOX' => '_makeChooseCheckbox'));

    $columns_b = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAllDtModAddDrop(this,\'st_arr\');"><A>');
    $columns = $columns_b + array('FULL_NAME' =>_student, 'STUDENT_ID' =>_studentId, 'COURSE_TITLE' =>_course, 'TITLE' =>_coursePeriod, 'START_DATE' =>_enrolled, 'END_DATE' =>_dropped);

    $enrollment_RET_ID_sql = 'SELECT se.ID,c.TITLE AS COURSE_TITLE,cp.TITLE,se.START_DATE AS START_DATE,se.END_DATE AS END_DATE,se.END_DATE AS DATE,se.STUDENT_ID,CONCAT(s.LAST_NAME,\'' . ',' . '\',s.FIRST_NAME) AS FULL_NAME FROM schedule se,students s,courses c,course_periods cp WHERE c.COURSE_ID=se.COURSE_ID AND cp.COURSE_PERIOD_ID=se.COURSE_PERIOD_ID AND cp.COURSE_ID=c.COURSE_ID AND s.STUDENT_ID=se.STUDENT_ID AND se.SCHOOL_ID=\'' . UserSchool() . '\' AND (se.START_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\' OR se.END_DATE BETWEEN \'' . $start_date . '\' AND \'' . $end_date . '\') ORDER BY DATE DESC';
    foreach(DBGet(DBQuery($enrollment_RET_ID_sql)) as $xy){
        $all_stu_res[] = $xy['ID'];
    }
    $all_stu_res = implode(',', $all_stu_res);
    echo '<div id="hidden_checkboxes"></div>';
    echo'<input type=hidden name=all_stu_res id=all_stu_res value=\''.$all_stu_res.'\'>';
    echo'<input type=hidden name=checked_all id=checked_all value=false>';
    ListOutput($enrollment_RET1, $columns, _scheduleRecord, _scheduleRecords);


    if ($_REQUEST['flag'] != 'list' && count($enrollment_RET1) != '0')
        echo '<div class="panel-body"><INPUT type=submit class="btn btn-primary" value="'._createAddDropReportForSelectedStudents.'"></div>';

    echo '</FORM>';
}

echo '</div>';

function _makeChooseCheckbox($value, $title) {
    // global $THIS_RET;

    // return "<input name=unused[$THIS_RET[STUDENT_ID]] value=" . $THIS_RET[STUDENT_ID] . "  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"st_arr[]\",this,$THIS_RET[STUDENT_ID]);' />";
    return "<INPUT type=checkbox name=unused[] value=" . $value . " id=" . $value . " onClick='setHiddenCheckboxStudents(\"st_arr[]\",this,$value);'>";
}

?>
