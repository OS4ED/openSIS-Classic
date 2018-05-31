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
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'save') {
    if (count($_SESSION['st_arr'])) {
        $st_list = '\'' . implode('\',\'', $_SESSION['st_arr']) . '\'';
        $extra['WHERE'] = ' AND s.STUDENT_ID IN (' . $st_list . ')';
        if ($_REQUEST['ADDRESS_ID']) {
            $extra['singular'] = 'Family';
            $extra['plural'] = 'Families';
            $extra['group'] = $extra['LO_group'] = array('ADDRESS_ID');
        }

        echo "<table width=100%  style=\" font-family:Arial; font-size:12px;\" >";
        echo "<tr><td width=105>" . DrawLogo() . "</td><td style=\"font-size:15px; font-weight:bold; padding-top:20px;\">" . GetSchool(UserSchool()) . "<div style=\"font-size:12px;\">Student Advanced Report</div></td><td align=right style=\"padding-top:20px;\">" . ProperDate(DBDate()) . "<br />Powered by openSIS</td></tr><tr><td colspan=3 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";
        echo "<table >";
        include('modules/miscellaneous/Export.php');
    }
}
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'call') {
    $_SESSION['st_arr'] = $_REQUEST['st_arr'];
    echo "<FORM action=ForExport.php?modname=$_REQUEST[modname]&head_html=Student+Advanced+Report&modfunc=save&search_modfunc=list&_openSIS_PDF=true&include_inactive=$_REQUEST[include_inactive]&_search_all_schools=$_REQUEST[_search_all_schools] onsubmit=document.forms[0].relation.value=document.getElementById(\"relation\").value; method=POST target=_blank>";
    echo '<DIV id=fields_div></DIV>';
    echo '<INPUT type=hidden name=relation>';

    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-lg-6">';
    Widgets('course');
    $extra['search'] .= '</div><div class="col-lg-6">';
    Widgets('request');
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '</div>'; //.row

    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-lg-6">';
    Widgets('activity');
    $extra['search'] .= '</div><div class="col-lg-6">';
    Widgets('absences');
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '</div>'; //.row

    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-lg-6">';
    Widgets('gpa');
    $extra['search'] .= '</div><div class="col-lg-6">';
    Widgets('class_rank');
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '</div>'; //.row

    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-lg-6">';
    Widgets('letter_grade');
    $extra['search'] .= '</div><div class="col-lg-6">';
    Widgets('eligibility');
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '</div>'; //.row

    $extra['search'] .= '<div class="form-group"><label>Include courses active as of</label>' . DateInputAY('', 'include_active_date', 1) . '</div>';
    $extra['new'] = true;
    include('modules/miscellaneous/Export.php');
    echo '<BR><CENTER><INPUT type=submit value=\'Create Report for Selected Students\' class="btn btn-primary"></CENTER>';
    echo "</FORM>";
}

if (!$_REQUEST['modfunc']) {
    DrawBC("Students > " . ProgramTitle());

    if ($_REQUEST['search_modfunc'] == 'list' || $_REQUEST['search_modfunc'] == 'select') {
        $_REQUEST['search_modfunc'] = 'select';

        $extra['link'] = array('FULL_NAME' => false);
        $extra['SELECT'] = ",CONCAT('<INPUT type=checkbox name=st_arr[] value=',s.STUDENT_ID,' checked>') AS CHECKBOX";

        $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller checked onclick="checkAll(this.form,this.form.controller.checked,\'st_arr\');"><A>');
        $extra['options']['search'] = false;


        echo "<FORM action=Modules.php?modname=$_REQUEST[modname]&modfunc=call method=POST>";
        echo '<DIV id=fields_div></DIV>';
        if ($_REQUEST['include_inactive'])
            echo '<INPUT type=hidden name=include_inactive value=' . $_REQUEST['include_inactive'] . '>';
        echo '<INPUT type=hidden name=relation>';

        $extra['search'] .= '<div class="row">';
        $extra['search'] .= '<div class="col-lg-6">';
        Widgets('course');
        $extra['search'] .= '</div><div class="col-lg-6">';
        Widgets('request');
        $extra['search'] .= '</div>'; //.col-lg-6
        $extra['search'] .= '</div>'; //.row

        $extra['search'] .= '<div class="row">';
        $extra['search'] .= '<div class="col-lg-6">';
        Widgets('activity');
        $extra['search'] .= '</div><div class="col-lg-6">';
        Widgets('absences');
        $extra['search'] .= '</div>'; //.col-lg-6
        $extra['search'] .= '</div>'; //.row

        $extra['search'] .= '<div class="row">';
        $extra['search'] .= '<div class="col-lg-6">';
        Widgets('gpa');
        $extra['search'] .= '</div><div class="col-lg-6">';
        Widgets('class_rank');
        $extra['search'] .= '</div>'; //.col-lg-6
        $extra['search'] .= '</div>'; //.row

        $extra['search'] .= '<div class="row">';
        $extra['search'] .= '<div class="col-lg-6">';
        Widgets('letter_grade');
        $extra['search'] .= '</div><div class="col-lg-6">';
        Widgets('eligibility');
        $extra['search'] .= '</div>'; //.col-lg-6
        $extra['search'] .= '</div>'; //.row

        $extra['search'] .= '<div class="row">';
        $extra['search'] .= '<div class="col-lg-6">';
        $extra['search'] .= '<div class="form-group"><label class="control-label col-lg-4">Include courses active as of </label><div class="col-lg-8">' . DateInputAY('', 'include_active_date', 2) . '</div></div>';
        $extra['search'] .= '</div>'; //.col-lg-6
        $extra['search'] .= '</div>'; //.row
        $extra['new'] = true;

        Search('student_id', $extra);

        if ($_SESSION['count_stu'] != '0') {
            unset($_SESSION['count_stu']);
            echo '<INPUT type=submit value=\'Create Report for Selected Students\' class="btn btn-primary">';
        }
        echo "</FORM>";
    } else {
        $extra['search'] .= '<div class="row">';
        $extra['search'] .= '<div class="col-lg-6">';
        Widgets('course');
        $extra['search'] .= '</div><div class="col-lg-6">';
        Widgets('request');
        $extra['search'] .= '</div>'; //.col-lg-6
        $extra['search'] .= '</div>'; //.row

        $extra['search'] .= '<div class="row">';
        $extra['search'] .= '<div class="col-lg-6">';
        Widgets('activity');
        $extra['search'] .= '</div>';
        $extra['search'] .= '</div>'; //.row

        $extra['search'] .= '<div class="row">';
        $extra['search'] .= '<div class="col-lg-6">';
        $extra['search'] .= '<div class="well mb-20">';
        Widgets('absences');
        $extra['search'] .= '</div>'; //.well
        $extra['search'] .= '</div><div class="col-lg-6">';
        $extra['search'] .= '<div class="well mb-20">';
        Widgets('class_rank');
        $extra['search'] .= '</div>'; //.well
        $extra['search'] .= '</div>';
        $extra['search'] .= '</div>'; //.row
        
        $extra['search'] .= '<div class="row">';
        $extra['search'] .= '<div class="col-lg-6">';
        $extra['search'] .= '<div class="well mb-20">';
        Widgets('gpa');
        $extra['search'] .= '</div>'; //.well
        $extra['search'] .= '</div><div class="col-lg-6">';
        $extra['search'] .= '<div class="well mb-20">';
        Widgets('letter_grade');
        $extra['search'] .= '</div>'; //.well
        $extra['search'] .= '</div>';
        $extra['search'] .= '</div>'; //.row
        
        $extra['search'] .= '<div class="row">';
        $extra['search'] .= '<div class="col-lg-6">';
        Widgets('eligibility');
        $extra['search'] .= '<div class="form-group"><label class="control-label col-lg-4">Include courses active as of </label><div class="col-lg-8">' . DateInputAY('', 'include_active_date', 3) . '</div></div>';
        $extra['search'] .= '</div>'; //.col-lg-6
        $extra['search'] .= '</div>'; //.row

        $extra['new'] = true;

        Search('student_id', $extra);
    }
}
?>