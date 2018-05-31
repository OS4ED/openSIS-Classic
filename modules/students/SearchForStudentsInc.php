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
if ($_openSIS['modules_search'] && $extra['force_search'])
    $_REQUEST['search_modfunc'] = '';

if (Preferences('SEARCH') != 'Y' && !$extra['force_search'])
    $_REQUEST['search_modfunc'] = 'list';
if ($extra['skip_search'] == 'Y')
    $_REQUEST['search_modfunc'] = 'list';

if ($_REQUEST['search_modfunc'] == 'search_fnc' || !$_REQUEST['search_modfunc']) {
    if ($_SESSION['student_id'] && User('PROFILE') == 'admin' && $_REQUEST['student_id'] == 'new') {
        unset($_SESSION['student_id']);
        echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
    }

    switch (User('PROFILE')) {
        case 'admin':
        case 'teacher':
            if (isset($_SESSION['stu_search']['sql']) && $search_from_grade != 'true') {
                unset($_SESSION['smc']);
                unset($_SESSION['g']);
                unset($_SESSION['p']);
                unset($_SESSION['smn']);
                unset($_SESSION['sm']);
                unset($_SESSION['sma']);
                unset($_SESSION['smv']);
                unset($_SESSION['s']);
                unset($_SESSION['_search_all']);
            }
            
            $_SESSION['Search_PHP_SELF'] = PreparePHP_SELF($_SESSION['_REQUEST_vars']);
            echo '<script language=JavaScript>parent.help.location.reload();</script>';
            if (isset($_SESSION['stu_search']['sql']) && $search_from_grade != 'true') {
                unset($_SESSION['stu_search']);
            } else if ($search_from_grade == 'true') {
                $_SESSION['stu_search']['search_from_grade'] = 'true';
            }
            
            echo '<div class="row">';
            echo '<div class="col-md-12">';
            PopTable('header', 'Find a Student');
            unset($_SESSION['students_order']);
           // echo 'test';
         // echo  encode_url("Modules.php?="); 
            if ($extra['pdf'] != true)
                echo "<FORM name=search class=form-horizontal id=search action=Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST>";
            else
                echo "<FORM name=search class=form-horizontal id=search action=ForExport.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST target=_blank>";

            Search('general_info');
            if ($extra['search'])
                echo $extra['search'];
            Search('student_fields');




            # ---   Advanced Search Start ---------------------------------------------------------- #
            echo '<div style="height:10px;"></div>';
            echo '<input type=hidden name=sql_save_session value=true />';
            

            echo '<div id="searchdiv" style="display:none;" class="well">';
            echo '<div><a href="javascript:void(0);" class="text-pink" onclick="hide_search_div();"><i class="icon-cancel-square"></i> Close Advance Search</a></div>';
            echo '<br/>';

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4">Comments </label><div class="col-lg-8"><input type=text name="mp_comment" size=30 placeholder="Comments" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Birthday</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4">From: </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('day_from_birthdate', 'month_from_birthdate', '', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4">To: </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('day_to_birthdate', 'month_to_birthdate', '', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Goal and Progress</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4">Goal Title </label><div class="col-lg-8"><input type=text name="goal_title" placeholder="Goal Title" size=30 class="form-control"></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4">Goal Description </label><div class="col-lg-8"><input type=text name="goal_description" placeholder="Goal Description" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4">Progress Period </label><div class="col-lg-8"><input type=text name="progress_name" placeholder="Progress Period" size=30 class="form-control"></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4">Progress Assessment </label><div class="col-lg-8"><input type=text name="progress_description" size=30 placeholder="Progress Assessment" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Medical</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4">Date</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('med_day', 'med_month', 'med_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4">Doctor\'s Note</label><div class="col-lg-8"><input type=text name="doctors_note_comments" placeholder="Doctor\'s Note" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Immunization</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4">Type</label><div class="col-lg-8"><input type=text name="type" placeholder="Immunization Type" size=30 class="form-control"></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4">Date</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('imm_day', 'imm_month', 'imm_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4">Comments</label><div class="col-lg-8"><input type=text name="imm_comments" placeholder="Immunization Comments" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Medical Alert</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4">Date</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('ma_day', 'ma_month', 'ma_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4">Alert</label><div class="col-lg-8"><input type=text name="med_alrt_title" placeholder="Medical Alert" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Nurse Visit</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4">Date</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('nv_day', 'nv_month', 'nv_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4">Reason</label><div class="col-lg-8"><input type=text name="reason" size=30 placeholder="Nurse Visit Reason" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4">Result</label><div class="col-lg-8"><input type=text name="result" size=30 placeholder="Nurse Visit Result" class="form-control"></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4">Comments</label><div class="col-lg-8"><input type=text name="med_vist_comments" placeholder="Nurse Visit Comments" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '</div>';



            # ---   Advanced Search End ----------------------------------------------------------- #



            echo '<div class="row">';
            echo '<div class="col-md-12">';
            if (User('PROFILE') == 'admin') {
                echo '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=address_group value=Y' . (Preferences('DEFAULT_FAMILIES') == 'Y' ? ' CHECKED' : '') . '> Group by Family</label>';
                echo '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=_search_all_schools value=Y' . (Preferences('DEFAULT_ALL_SCHOOLS') == 'Y' ? ' CHECKED' : '') . '> Search All Schools</label>';
            }
            if ($_REQUEST['modname'] != 'students/StudentReenroll.php')
                echo '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=include_inactive value=Y> Include Inactive Students</label>';
            echo '</div>'; //.col-md-12
            echo '</div>'; //.row

            echo '<hr/>';
            echo '<div>';
            if ($extra['pdf'] != true)
                echo "<INPUT type=SUBMIT class=\"btn btn-primary\" value='Submit' onclick='return formcheck_student_advnc_srch();formload_ajax(\"search\");'>&nbsp; <INPUT type=RESET class=\"btn btn-default\" value='Reset'>&nbsp; &nbsp; ";
            else
                echo "<INPUT type=SUBMIT class=\"btn btn-primary\" value='Submit' onclick='return formcheck_student_advnc_srch();'>&nbsp; <INPUT type=RESET class=\"btn btn-default\" value='Reset'>&nbsp; &nbsp; ";
            
            echo '<a id="addiv" href="javascript:void(0);" class="text-pink" onclick="show_search_div();"><i class="icon-cog"></i> Advanced Search</a>';
            echo '</div>';

            echo '</FORM>';
            // set focus to last name text box
            echo '<script type="text/javascript"><!--
				document.search.last.focus();
				--></script>';
            PopTable('footer');
            echo '</div>'; //.col-md-12
            echo '</div>'; //.row
            break;

        case 'parent':
        case 'student':
            echo '<BR>';
            PopTable('header', 'Search');
            if ($extra['pdf'] != true)
                echo "<FORM action=Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST>";
            else
                echo "<FORM action=ForExport.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST target=_blank>";
            echo '<TABLE border=0>';
            if ($extra['search'])
                echo $extra['search'];
            echo '<TR><TD colspan=2 align=center>';
            echo '<BR>';
            echo Buttons('Submit', 'Reset');
            echo '</TD></TR>';
            echo '</TABLE>';
            echo '</FORM>';
            PopTable('footer');
            break;
    }
}
else {
    if (!$_REQUEST['next_modname'])
        $_REQUEST['next_modname'] = 'students/Student.php';

    if ($_REQUEST['address_group']) {
        $extra['SELECT'] = $extra['SELECT'] . ',ssm.student_id AS CHILD';
        if (count($extra['functions']) > 0)
            $extra['functions']+=array('CHILD' => '_make_Parents');
        else
            $extra['functions'] = array('CHILD' => '_make_Parents');

        if (!($_REQUEST['expanded_view'] == 'true' || $_REQUEST['addr'] || $extra['addr'])) {

            $extra['FROM'] = ' INNER JOIN students_join_people sam ON (sam.STUDENT_ID=ssm.STUDENT_ID) ';

            $extra['ORDER_BY'] = 'FULL_NAME';
            $extra['DISTINCT'] = 'DISTINCT';
        }
    }
    $extra['SELECT'].=' ,ssm.SECTION_ID';
    if (count($extra['functions']) > 0)
        $extra['functions']+=array('SECTION_ID' => '_make_sections');
    else
        $extra['functions'] = array('SECTION_ID' => '_make_sections');


    if ($_REQUEST['section'] != '')
        $extra['WHERE'].=' AND ssm.SECTION_ID=' . $_REQUEST['section'];


    $students_RET = GetStuList($extra);
    if ($_REQUEST['modname'] == 'grades/HonorRoll.php') {
        $i = 1;
        foreach ($students_RET as $key => $stuRET) {
            if ($stuRET['HONOR_ROLL'] != '') {
                $stu[$i] = $stuRET;
                $i++;
            }
        }
        $students_RET = $stu;
    }
    if ($_REQUEST['address_group']) {
        
    }

    if ($extra['array_function'] && function_exists($extra['array_function']))
        $students_RET = $extra['array_function']($students_RET);

    $LO_columns = array('FULL_NAME' => 'Student', 'STUDENT_ID' => 'Student ID', 'ALT_ID' => 'Alternate ID', 'GRADE_ID' => 'Grade', 'SECTION_ID' => 'Section', 'PHONE' => 'Phone');
    $name_link['FULL_NAME']['link'] = "Modules.php?modname=$_REQUEST[next_modname]";
    $name_link['FULL_NAME']['variables'] = array('student_id' => 'STUDENT_ID');
    if ($_REQUEST['_search_all_schools'])
        $name_link['FULL_NAME']['variables'] += array('school_id' => 'SCHOOL_ID');

    if (is_array($extra['link']))
        $link = $extra['link'] + $name_link;
    else
        $link = $name_link;
    if (is_array($extra['columns_before'])) {
        $columns = $extra['columns_before'] + $LO_columns;
        $LO_columns = $columns;
    }

    if (is_array($extra['columns_after']))
        $columns = $LO_columns + $extra['columns_after'];
    if (!$extra['columns_before'] && !$extra['columns_after'])
        $columns = $LO_columns;

    if (count($students_RET) > 1 || $link['add'] || !$link['FULL_NAME'] || $extra['columns_before'] || $extra['columns_after'] || ($extra['BackPrompt'] == false && count($students_RET) == 0) || ($extra['Redirect'] === false && count($students_RET) == 1)) {

        echo '<div class="panel panel-default">';
        $tmp_REQUEST = $_REQUEST;
        unset($tmp_REQUEST['expanded_view']);
        if ($_REQUEST['expanded_view'] != 'true' && !UserStudentID() && count($students_RET) != 0) {
            DrawHeader("<A HREF=" . PreparePHP_SELF($tmp_REQUEST) . "&expanded_view=true><i class=\"icon-square-down-right\"></i> Expanded View</A>", $extra['header_right']);
            DrawHeader(str_replace('', '', substr($_openSIS['SearchTerms'], 0, -4)));
        } elseif (!UserStudentID() && count($students_RET) != 0) {
            DrawHeader("<A HREF=" . PreparePHP_SELF($tmp_REQUEST) . "&expanded_view=false><i class=\"icon-square-up-left\"></i> Original View</A>", $extra['header_right']);
            DrawHeader(str_replace('', '', substr($_openSIS['Search'], 0, -4)));
        }
        DrawHeader($extra['extra_header_left'], $extra['extra_header_right']);
        if ($_REQUEST['LO_save'] != '1' && !$extra['suppress_save']) {
            $_SESSION['List_PHP_SELF'] = PreparePHP_SELF($_SESSION['_REQUEST_vars']);
            echo '<script language=JavaScript>parent.help.location.reload();</script>';
        }
        if (!$extra['singular'] || !$extra['plural'])
            $extra['singular'] = 'Student';
        $extra['plural'] = 'Students';

        foreach ($students_RET as $si => $sd)
            $_SESSION['students_order'][$si] = $sd['STUDENT_ID'];


        echo "<div id='students' class=\"table-responsive\">";
               
        ListOutput($students_RET, $columns, $extra['singular'], $extra['plural'], $link, $extra['LO_group'], $extra['options']);
        echo "</div>"; //.table-responsive
        echo "</div>"; //.panel.panel-default
    }
    elseif (count($students_RET) == 1) {
        if (count($link['FULL_NAME']['variables'])) {
            foreach ($link['FULL_NAME']['variables'] as $var => $val)
                $_REQUEST[$var] = $students_RET['1'][$val];
        }
        if (!is_array($students_RET[1]['STUDENT_ID'])) {
            $_SESSION['student_id'] = $students_RET[1]['STUDENT_ID'];



            if (User('PROFILE') == 'admin')
                $_SESSION['UserSchool'] = $students_RET[1]['LIST_SCHOOL_ID'];
            if (User('PROFILE') == 'teacher')
                $_SESSION['UserSchool'] = $students_RET[1]['SCHOOL_ID'];


            echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
            unset($_REQUEST['search_modfunc']);
        }
        if ($_REQUEST['modname'] != $_REQUEST['next_modname']) {
            $modname = $_REQUEST['next_modname'];
            if (strpos($modname, '?'))
                $modname = substr($_REQUEST['next_modname'], 0, strpos($_REQUEST['next_modname'], '?'));
            if (strpos($modname, '&'))
                $modname = substr($_REQUEST['next_modname'], 0, strpos($_REQUEST['next_modname'], '&'));
            if ($_REQUEST['modname'])
                $_REQUEST['modname'] = $modname;
            include('modules/' . $modname);
        }
    } else
        BackPrompt('No Students were found.');
}

function _make_sections($value) {
    if ($value != '') {
        $get = DBGet(DBQuery('SELECT NAME FROM school_gradelevel_sections WHERE ID=' . $value));
        return $get[1]['NAME'];
    }
    else
        return '';
}

?>