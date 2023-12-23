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
if (User('PROFILE') == 'teacher'){
    unset($_SESSION['student_id']);
}
if (isset($_SESSION['student_id'])) {
    $_REQUEST['stuid'] = $_SESSION['student_id'];
}
include('../../RedirectModulesInc.php');
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'save') {
    if (count($_SESSION['st_arr'])) {
        $st_list = '\'' . implode('\',\'', $_SESSION['st_arr']) . '\'';
        $extra['WHERE'] = ' AND s.STUDENT_ID IN (' . $st_list . ')';
        if ($_REQUEST['ADDRESS_ID']) {
            $extra['singular'] = 'Family';
            $extra['plural'] = 'Families';
            $extra['group'] = $extra['LO_group'] = array('ADDRESS_ID');
        }

        if ($_REQUEST['excelReport'] != 'Y') {
            echo "<table width=100%  style=\" font-family:Arial; font-size:12px;\" >";
            echo "<tr><td width=105>" . DrawLogo() . "</td><td style=\"font-size:15px; font-weight:bold; padding-top:20px;\">" . GetSchool(UserSchool()) . "<div style=\"font-size:12px;\">" . _studentAdvancedReport . "</div></td><td align=right style=\"padding-top:20px;\">" . ProperDate(DBDate()) . "<br />" . _studentAdvancedReport . "</td></tr><tr><td colspan=3 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";
            echo "<table >";
        }
        include('modules/miscellaneous/Export.php');
    }
}
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'call' || isset($_SESSION['student_id'])) {
    if (isset($_SESSION['student_id'])) {
        $_SESSION['st_arr'] = array("0" => $_SESSION['student_id']);
    } else {
        $_SESSION['st_arr'] = $_REQUEST['st_arr'];
    }

    echo "<FORM action=ForExport.php?modname=$_REQUEST[modname]&head_html=Student+Advanced+Report&modfunc=save&search_modfunc=list&_openSIS_PDF=true&include_inactive=$_REQUEST[include_inactive]&_search_all_schools=$_REQUEST[_search_all_schools] onsubmit=document.forms[0].relation.value=document.getElementById(\"relation\").value; method=POST target=_blank>";
    echo '<DIV id=fields_div></DIV>';
    echo '<INPUT type=hidden name=relation>';

    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-lg-6">';
    Widgets('course');
    Widgets('activity');
    Widgets('gpa');
    Widgets('letter_grade');
    $extra['search'] .= '</div><div class="col-lg-6">';
    Widgets('request');
    Widgets('absences');
    Widgets('class_rank');
    Widgets('eligibility');
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '</div>'; //.row


    $extra['search'] .= '<div class="form-group"><label>' . _includeCoursesActiveAsOf . '</label>' . DateInputAY('', 'include_active_date', 1) . '</div>';
    $extra['new'] = true;
    include('modules/miscellaneous/Export.php');
    echo '<div class="text-center m-t-20"><div><INPUT type=button value=\'' . _createReportForSelectedStudents . '\' class="btn btn-primary" onclick="triggerAdvancedReportExcel(this,\'\')"></div><div class="m-t-10"><INPUT type=button value=\'' . _createExcelReportForSelectedStudents . '\' class="btn btn-success" onclick="triggerAdvancedReportExcel(this,\'Y\')"></div></div>';
    echo "</FORM>";
}
$modal_flag = 1;
if ($_REQUEST['modname'] == 'students/AdvancedReport.php' && $_REQUEST['modfunc'] == 'save')
    $modal_flag = 0;
if ($modal_flag == 1) {
    echo '<div id="modal_default" class="modal fade">';
    echo '<div class="modal-dialog modal-lg">';
    echo '<div class="modal-content">';
    echo '<div class="modal-header">';
    echo '<button type="button" class="close" data-dismiss="modal">×</button>';
    echo '<h5 class="modal-title">' . _chooseCourse . '</h5>';
    echo '</div>';

    echo '<div class="modal-body">';
    echo '<center><div id="conf_div"></div></center>';

    echo '<div class="row" id="resp_table">';
    echo '<div class="col-md-4">';
    $sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
    $QI = DBQuery($sql);
    $subjects_RET = DBGet($QI);

    echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' ' . _subjectWas : ' ' . _subjectsWere) . ' ' . _found . '.</h6>';
    if (count($subjects_RET) > 0) {
        echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>' . _subject . '</th></tr></thead><tbody>';
        foreach ($subjects_RET as $val) {
            echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearch(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
        }
        echo '</tbody></table>';
    }
    echo '</div>';
    echo '<div class="col-md-4"><div id="course_modal"></div></div>';
    echo '<div class="col-md-4"><div id="cp_modal"></div></div>';
    echo '</div>'; //.row
    echo '</div>'; //.modal-body

    echo '</div>'; //.modal-content
    echo '</div>'; //.modal-dialog
    echo '</div>'; //.modal




    echo '<div id="modal_default_request" class="modal fade">';
    echo '<div class="modal-dialog">';
    echo '<div class="modal-content">';
    echo '<div class="modal-header">';
    echo '<button type="button" class="close" data-dismiss="modal">×</button>';
    echo '<h5 class="modal-title">' . _chooseCourse . '</h5>';
    echo '</div>';

    echo '<div class="modal-body">';
    echo '<center><div id="conf_div"></div></center>';

    echo '<div class="row" id="resp_table">';
    echo '<div class="col-md-6">';
    $sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
    $QI = DBQuery($sql);
    $subjects_RET = DBGet($QI);

    echo count($subjects_RET) . ((count($subjects_RET) == 1) ? ' ' . _subjectWas : ' ' . _subjectsWere) . ' found.<br>';
    if (count($subjects_RET) > 0) {
        echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>' . _subject . '</th></tr></thead><tbody>';
        foreach ($subjects_RET as $val) {
            echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearchRequest(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
        }
        echo '</tbody></table>';
    }
    echo '</div>';
    echo '<div class="col-md-6"><div id="course_modal_request"></div></div>';
    echo '</div>'; //.row
    echo '</div>'; //.modal-body

    echo '</div>'; //.modal-content
    echo '</div>'; //.modal-dialog
    echo '</div>'; //.modal
}

if (!$_REQUEST['modfunc']) {
    DrawBC("" . _students . " > " . ProgramTitle());

    if ($_REQUEST['search_modfunc'] == 'list' || $_REQUEST['search_modfunc'] == 'select') {
        $_REQUEST['search_modfunc'] = 'select';

        $extra['link'] = array('FULL_NAME' => false);
        $extra['SELECT'] = ",s.STUDENT_ID AS CHECKBOX";
        $extra['functions'] = array('CHECKBOX' => '_makeChooseCheckbox');
        //        $extra['SELECT'] = ",CONCAT('<INPUT type=checkbox name=st_arr[] value=',s.STUDENT_ID,' checked>') AS CHECKBOX";

        // $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAllDtMod(this,\'st_arr\');"><A>');
        $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAllDtMod2(this,\'st_arr\');"><A>');
        $extra['options']['search'] = false;


        echo "<FORM action=Modules.php?modname=$_REQUEST[modname]&modfunc=call method=POST>";
        echo '<DIV id=fields_div></DIV>';
        if ($_REQUEST['include_inactive'])
            echo '<INPUT type=hidden name=include_inactive value=' . $_REQUEST['include_inactive'] . '>';
        echo '<INPUT type=hidden name=relation>';

        $extra['search'] .= '<div class="row">';
        $extra['search'] .= '<div class="col-lg-6">';
        Widgets('course');
        Widgets('activity');
        Widgets('gpa');
        Widgets('letter_grade');
        $extra['search'] .= '</div><div class="col-lg-6">';
        Widgets('request');
        Widgets('absences');
        Widgets('class_rank');
        Widgets('eligibility');
        $extra['search'] .= '<div class="form-group"><label class="control-label col-lg-4">' . _includeCoursesActiveAsOf . ' </label><div class="col-lg-8">' . DateInputAY('', 'include_active_date', 2) . '</div></div>';
        $extra['search'] .= '</div>'; //.col-lg-6
        $extra['search'] .= '</div>'; //.row

        $extra['new'] = true;

        Search('student_id', $extra);

        if ($_SESSION['count_stu'] != '0') {
            unset($_SESSION['count_stu']);
            echo '<div class="text-right p-b-20 p-r-20"><INPUT type=submit value=\'' . _createReportForSelectedStudents . '\' class="btn btn-primary"></div>';
        }
        echo "</FORM>";
    } else {
        $extra['search'] .= '<div class="row">';
        $extra['search'] .= '<div class="col-lg-6">';

        Widgets('course');
        Widgets('activity');
        $extra['search'] .= '<div class="well mb-20">';
        Widgets('absences');
        $extra['search'] .= '</div>'; //.well
        $extra['search'] .= '<div class="well mb-20">';
        Widgets('gpa');
        $extra['search'] .= '</div>'; //.well
        $extra['search'] .= '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _includeCoursesActiveAsOf . ' </label><div class="col-lg-8">' . DateInputAY('', 'include_active_date', 3) . '</div></div>';

        $extra['search'] .= '</div><div class="col-lg-6">';

        Widgets('request');
        Widgets('eligibility');
        $extra['search'] .= '<div class="well mb-20">';
        Widgets('class_rank');
        $extra['search'] .= '</div>'; //.well
        $extra['search'] .= '<div class="well mb-20">';
        Widgets('letter_grade');
        $extra['search'] .= '</div>'; //.well

        $extra['search'] .= '</div>'; //.col-lg-6
        $extra['search'] .= '</div>'; //.row
        $extra['new'] = true;
        Search('student_id', $extra);
    }
}

function _makeChooseCheckbox($value, $title)
{
    global $THIS_RET;
    return "<input  class='student_label_cbx' name=unused[$THIS_RET[STUDENT_ID]] value=" . $THIS_RET['STUDENT_ID'] . "  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"st_arr[]\",this,$THIS_RET[STUDENT_ID]);' />";
}
