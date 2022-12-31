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

include 'Warehouse.php';
include('lang/language.php');
session_start();

if($_REQUEST['loadpage'])
{
    foreach($_SESSION['PEGI_MODS'] as $pagemods_key => $pagemods_val)
    {
        $_REQUEST[$pagemods_key] = $pagemods_val;
    }

    $loading_page = $_REQUEST['loadpage'];

    if($loading_page == 1)
    {
        $row = 0;
        $ini_point = 1;
        $end_point = 50;
    }
    else
    {
        $row = (($loading_page * 50) - 50);
        $ini_point = $row;
        $end_point = ($loading_page * 50);
    }

    if($end_point > $_SESSION['AL_RES_COUNT'])
    {
        $end_point = $_SESSION['AL_RES_COUNT'];
    }
    
    $rowperpage = 50;

    $entries_data = array(
            "aaIni" =>  $ini_point,
            "aaEnd" =>  $end_point,
            "aaTot" =>  $_SESSION['AL_RES_COUNT'],
            "aaCur" =>  $loading_page,
            "fxRow" =>  $rowperpage
        );

    $ListOutputFunc = $_SESSION['LISTOUTPUT_FUNC'];

    $extra = $_SESSION['PEGI_EXTRA'];

    $extra['LIMIT'] = $row.','.$rowperpage;

    if(isset($_SESSION['PEGI_REQUESTS'])) {
        foreach($_SESSION['PEGI_REQUESTS'] as $R_key => $R_val) {
            $_REQUEST[$R_key] = $R_val;
        }
    }

    if($_SESSION['PEGI_MODS']['modname'] == 'attendance/StudentSummary.php')
    {
        $students_RET = GetStuList_Absence_Summary($extra);
    }
    else
    {
        $students_RET = GetStuList($extra);
    }

    $res_len_set = '';
    foreach($students_RET as $one_stu_data)
    {
        if(isset($one_stu_data['STUDENT_ID']))
        {
            $res_len_set .= $one_stu_data['STUDENT_ID'].',';
        }
    }
    $res_len_set = rtrim($res_len_set, ',');

    echo "<script>\n";
    echo 'var res_length = document.getElementById(\'res_len\');
        if(res_length) { res_length.value=\'\'; res_length.value=\''.$res_len_set.'\'; }
        /* var div_selected_cbx = document.getElementById(\'hidden_checkboxes\');
        if(div_selected_cbx) { div_selected_cbx.innerHTML = \'\'; } */
    ';
    echo "</script>\n";

    if($ListOutputFunc != '') {
        $ListOutputFunc($students_RET, $_SESSION['PEGI_COLS'], $_SESSION['PEGI_SINGULAR'], $_SESSION['PEGI_PLURAL'], $_SESSION['PEGI_LINK'], $entries_data, $_SESSION['PEGI_LOGRP'], $_SESSION['PEGI_OPTION']);
    } else {
        echo '<div class="m-20 p-10"><div class="alert alert-styled-left alert-danger"><b>'. _error .':</b> '. _cannotGeneratePagination .'</div></div>';
    }
}


// FUNCTIONS LISTED HERE

function _makeChooseCheckbox($value, $title) {
    global $THIS_RET;
    
    switch ($_SESSION['PEGI_MODS']['modname']) {
        case 'scheduling/PrintSchedules.php':
        case 'grades/FinalGrades.php':
        case 'users/TeacherPrograms.php?include=grades/ProgressReports.php':
        case 'grades/ProgressReports.php':
        return '<INPUT type=checkbox name=st_arr[] value=' . $value . '>';
    
        case 'scheduling/MassDrops.php':
            case 'scheduling/MassRequests.php':
            case 'attendance/AddAbsences.php':
            case 'eligibility/AddActivity.php':
            case 'students/AssignOtherInfo.php':
                return "<input name=unused[$THIS_RET[STUDENT_ID]] value=$THIS_RET[STUDENT_ID]  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"student[$THIS_RET[STUDENT_ID]]\",this,$THIS_RET[STUDENT_ID], \"Y\");' />";
    
            case 'scheduling/MassSchedule.php':
                return "<input name=unused[$THIS_RET[STUDENT_ID]] value=$THIS_RET[STUDENT_ID]  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"student[$THIS_RET[STUDENT_ID]]\",this,$THIS_RET[STUDENT_ID]);' />";
            
            default:
        return "<input  class='student_label_cbx' name=unused[$THIS_RET[STUDENT_ID]] value=" . $THIS_RET['STUDENT_ID'] . "  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"st_arr[]\",this,$THIS_RET[STUDENT_ID]);' />";
    }
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

// function _make_sections($value) {
//     if ($value != '') {
//         $get = DBGet(DBQuery('SELECT NAME FROM school_gradelevel_sections WHERE ID=' . $value));
//         return $get[1]['NAME'];
//     } else
//         return '';
// }

?>