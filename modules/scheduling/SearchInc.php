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
unset($_SESSION['student_id']);
if ($_openSIS['modules_search'] && $extra['force_search'])
    $_REQUEST['search_modfunc'] = '';

if (Preferences('SEARCH') != 'Y' && !$extra['force_search'])
    $_REQUEST['search_modfunc'] = 'list';
if ($_REQUEST['search_modfunc'] == 'search_fnc' || !$_REQUEST['search_modfunc']) {
    if ($_SESSION['student_id'] && User('PROFILE') == 'admin' && $_REQUEST['student_id'] == 'new') {
        unset($_SESSION['student_id']);
    }
    switch (User('PROFILE')) {
        case 'admin':
        case 'teacher':
            
            $_SESSION['Search_PHP_SELF'] = PreparePHP_SELF($_SESSION['_REQUEST_vars']);

            if (isset($_SESSION['stu_search']['sql'])) {
                unset($_SESSION['stu_search']);
            }
            PopTable('header', 'Find a Student');
            if ($extra['pdf'] != true)
                echo "<FORM name=search class=\"form-horizontal m-b-0\" id=search action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=" . strip_tags(trim($_REQUEST[modfunc])) . "&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST>";
            else
                echo "<FORM name=search class=\"form-horizontal m-b-0\" id=search action=ForExport.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=" . strip_tags(trim($_REQUEST[modfunc])) . "&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST target=_blank>";

            Search('general_info');
            if ($extra['search'])
                echo $extra['search'];
            Search('student_fields');
            echo '<input type=hidden name=sql_save_session value=true />';
            echo '<div class="row">';
            echo '<div class="col-lg-12">';
            if (User('PROFILE') == 'admin') {
                echo '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=address_group value=Y' . (Preferences('DEFAULT_FAMILIES') == 'Y' ? ' CHECKED' : '') . '>Group by Family</label>';
                echo '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=_search_all_schools value=Y' . (Preferences('DEFAULT_ALL_SCHOOLS') == 'Y' ? ' CHECKED' : '') . '>Search All Schools</label>';
            }
            echo '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=include_inactive value=Y>Include Inactive Students</label>';
            echo '</div>'; //.col-lg-12
            echo '</div>'; //.row

            echo '<hr>';
            echo '<div class="text-right">';
            if ($extra['pdf'] != true)
                echo "<INPUT type=SUBMIT class=\"btn btn-primary\" value='Submit' > &nbsp; <INPUT type=RESET class=\"btn btn-default\" value='Reset'>";
            else
                echo "<INPUT type=SUBMIT class=\"btn btn-primary\" value='Submit'> &nbsp; <INPUT type=RESET class=\"btn btn-default\" value='Reset'>";
            echo '</div>';
            echo '</FORM>';

            echo '<script type="text/javascript"><!--
				document.search.last.focus();
				--></script>';
            PopTable('footer');
            break;
        case 'parent':
        case 'student':
            PopTable('header', 'Search');
            if ($extra['pdf'] != true)
                echo "<FORM action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=" . strip_tags(trim($_REQUEST[modfunc])) . "&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST>";
            else
                echo "<FORM action=ForExport.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=" . strip_tags(trim($_REQUEST[modfunc])) . "&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST target=_blank>";
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
        $extra['SELECT'] .= ',sam.ID AS ADDRESS_ID';
        if (!($_REQUEST['expanded_view'] == 'true' || $_REQUEST['addr'] || $extra['addr']))
            $extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (sam.STUDENT_ID=ssm.STUDENT_ID AND sam.TYPE=\'Home Address\')' . $extra['FROM'];
        $extra['group'] = array('ADDRESS_ID');
    }
    $extra['SELECT'] .= ' ,ssm.SECTION_ID';
    if (count($extra['functions']) > 0)
        $extra['functions'] += array('SECTION_ID' => '_make_sections');
    else
        $extra['functions'] = array('SECTION_ID' => '_make_sections');

    if ($_REQUEST['section'] != '')
        $extra['WHERE'] .= ' AND ssm.SECTION_ID=' . $_REQUEST['section'];
    $students_RET = GetStuList($extra);
    if ($_REQUEST['address_group']) {
        // if address_group specified but only one address returned then convert to ungrouped
        if (count($students_RET) == 1) {
            $students_RET = $students_RET[key($students_RET)];
            unset($_REQUEST['address_group']);
        } else
            $extra['LO_group'] = array('ADDRESS_ID');
    }

    if ($extra['array_function'] && function_exists($extra['array_function']))
        if ($_REQUEST['address_group'])
            foreach ($students_RET as $id => $student_RET)
                $students_RET[$id] = $extra['array_function']($student_RET);
        else
            $students_RET = $extra['array_function']($students_RET);

    $LO_columns = array('FULL_NAME' => 'Student', 'STUDENT_ID' => 'Student ID', 'GRADE_ID' => 'Grade', 'SECTION_ID' => 'Section');
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
        $tmp_REQUEST = $_REQUEST;
        unset($tmp_REQUEST['expanded_view']);
        
        echo '<div class="panel panel-default">';
        
        if ($_REQUEST['expanded_view'] != 'true' && !UserStudentID() && count($students_RET) != 0)
            DrawHeader("<A HREF=" . PreparePHP_SELF($tmp_REQUEST) . "&expanded_view=true><i class=\"icon-square-down-right\"></i> Expanded View</A>", $extra['header_right']);
        elseif (!UserStudentID() && count($students_RET) != 0)
            DrawHeader("<A HREF=" . PreparePHP_SELF($tmp_REQUEST) . "&expanded_view=false><i class=\"icon-square-up-left\"></i> Original View</A>", $extra['header_right']);
        DrawHeader($extra['extra_header_left'], $extra['extra_header_right']);
        DrawHeader(str_replace('<BR>', '<BR> &nbsp;', substr($_openSIS['SearchTerms'], 0, -4)));
        if ($_REQUEST['LO_save'] != '1' && !$extra['suppress_save']) {
            $_SESSION['List_PHP_SELF'] = PreparePHP_SELF($_SESSION['_REQUEST_vars']);
            //echo '<script language=JavaScript>parent.help.location.reload();</script>';
        }
        if (!$extra['singular'] || !$extra['plural'])
            if ($_REQUEST['address_group']) {
                $extra['singular'] = 'Family';
                $extra['plural'] = 'Families';
            } else {
                $extra['singular'] = 'Student';
                $extra['plural'] = 'Students';
            }
        
        echo "<div id='students' >";
         echo '<div id="hidden_checkboxes" />';
        $check_all_arr=array();
        foreach($students_RET as $xy)
        {
            $check_all_arr[]=$xy['STUDENT_ID'];
        }
        $check_all_stu_list=implode(',',$check_all_arr);
        echo'<input type=hidden name=res_length id=res_length value=\''.count($check_all_arr).'\'>';
        echo '<br>';
        echo'<input type=hidden name=res_len id=res_len value=\''.$check_all_stu_list.'\'>'; 

      
        ListOutput($students_RET, $columns, $extra['singular'], $extra['plural'], $link, $extra['LO_group'], $extra['options']);
        echo "</div>";
        echo "</div>";
        echo "</div>";
    } elseif (count($students_RET) == 1) {
        if (count($link['FULL_NAME']['variables'])) {
            foreach ($link['FULL_NAME']['variables'] as $var => $val)
                $_REQUEST[$var] = $students_RET['1'][$val];
        }
        if (!is_array($students_RET[1]['STUDENT_ID'])) {
            $_SESSION['student_id'] = $students_RET[1]['STUDENT_ID'];
            $_SESSION['UserSchool'] = $students_RET[1]['LIST_SCHOOL_ID'];
            //echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
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
    } else
        return '';
}
?>