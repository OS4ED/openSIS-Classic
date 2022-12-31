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

// echo "<pre>"; print_r($_REQUEST); echo "</pre>";

// if ($_REQUEST['search_modfunc'] == 'search_fnc' || !$_REQUEST['search_modfunc']) {
if (($_REQUEST['search_modfunc'] == 'search_fnc' && !$_SESSION['student_id']) || (!$_REQUEST['search_modfunc'] && !$_SESSION['student_id']) || $_REQUEST['modname'] == 'students/Letters.php') 
{
    if ($_SESSION['student_id'] && User('PROFILE') == 'admin' && $_REQUEST['student_id'] == 'new') {
        unset($_SESSION['student_id']);
        // echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
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
            //echo '<script language=JavaScript>parent.help.location.reload();</script>';
            if (isset($_SESSION['stu_search']['sql']) && $search_from_grade != 'true') {
                unset($_SESSION['stu_search']);
            } else if ($search_from_grade == 'true') {
                $_SESSION['stu_search']['search_from_grade'] = 'true';
            }


            if ($extra['pdf'] != true) {
                echo "<FORM name=search class=\"no-margin-bottom form-horizontal\" id=search action=Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST>";
            } else {
                echo "<FORM name=search class=\"no-margin-bottom form-horizontal\" id=search action=ForExport.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST target=_blank>";
            }

            PopTable('header',  _findAStudent);

            Search('general_info');
            if ($extra['search']) {
                echo $extra['search'];
            }
            Search('student_fields');

            # ---   Advanced Search Start ---------------------------------------------------------- #

            echo '<input type=hidden name=sql_save_session value=true />';


            echo '<div id="searchdiv" class="pt-20 mt-20 well" style="display:none;">';
            echo '<div><a href="javascript:void(0);" onclick="hide_search_div();" class="text-pink"><i class="icon-cancel-square"></i> '._closeAdvancedSearch.'</a></div>';

            echo '<div class="row">';
            echo '<div class="col-lg-12">';
            echo '<div class="form-group pt-15"><label class="control-label col-lg-2 text-right">'._comments.'</label><div class="col-lg-10"><input type=text name="mp_comment" size=30 placeholder="'._comments.'" class="form-control"></div></div>';
            echo '</div>'; //.col-lg-12
            echo '</div>'; //.row

            echo '<h5 class="text-primary">'._birthday.'</h5>';
            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._from.'</label><div class="col-lg-8">' . SearchDateInput('day_from_birthdate', 'month_from_birthdate', '', 'Y', 'Y', '') . '</div></div>';
            echo '</div><div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._to.'</label><div class="col-lg-8">' . SearchDateInput('day_to_birthdate', 'month_to_birthdate', '', 'Y', 'Y', '') . '</div></div>';
            echo '</div>'; //.col-lg-6
            echo '</div>'; //.row
            
            echo '<h5 class="text-primary">'._goalAndProgress.'</h5>';
            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._goalTitle.'</label><div class="col-lg-8"><input type=text name="goal_title" placeholder="'._goalTitle.'" size=30 class="form-control"></div></div>';
            echo '</div><div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._goalDescription.'</label><div class="col-lg-8"><input type=text name="goal_description" placeholder="'._goalDescription.'" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-lg-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._progressPeriod.'</label><div class="col-lg-8"><input type=text name="progress_name" placeholder="'._progressPeriod.'" size=30 class="form-control"></div></div>';
            echo '</div><div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._progressAssessment.'</label><div class="col-lg-8"><input type=text name="progress_description" placeholder="'._progressAssessment.'" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-lg-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">'._medical.'</h5>';
            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._date.'</label><div class="col-lg-8">' . SearchDateInput('med_day', 'med_month', 'med_year', 'Y', 'Y', 'Y') . '</div></div>';
            echo '</div><div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._doctorSNote.'</label><div class="col-lg-8"><input type=text name="doctors_note_comments" placeholder="'._doctorSNote.'" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-lg-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">'._immunization.'</h5>';
            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._type.'</label><div class="col-lg-8"><input type=text name="type" size=30 placeholder="'._type.'" class="form-control"></div></div>';
            echo '</div><div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._date.'</label><div class="col-lg-8">' . SearchDateInput('imm_day', 'imm_month', 'imm_year', 'Y', 'Y', 'Y') . '</div></div>';
            echo '</div>'; //.col-lg-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._comments.'</label><div class="col-lg-8"><input type=text name="imm_comments" placeholder="'._comments.'" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-lg-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">'._medicalAlert.'</h5>';
            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._date.'</label><div class="col-lg-8">' . SearchDateInput('ma_day', 'ma_month', 'ma_year', 'Y', 'Y', 'Y') . '</div></div>';
            echo '</div><div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._alert.'</label><div class="col-lg-8"><input type=text name="med_alrt_title" placeholder="'._alert.'" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-lg-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">'._nurseVisit.'</h5>';
            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._date.'</label><div class="col-lg-8">' . SearchDateInput('nv_day', 'nv_month', 'nv_year', 'Y', 'Y', 'Y') . '</div></div>';
            echo '</div><div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._reason.'</label><div class="col-lg-8"><input type=text name="reason" size=30 placeholder="'._reason.'" class="form-control"></div></div>';
            echo '</div>'; //.col-lg-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._result.'</label><div class="col-lg-8"><input type=text name="result" size=30 placeholder="'._result.'" class="form-control"></div></div>';
            echo '</div><div class="col-lg-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._comments.'</label><div class="col-lg-8"><input type=text name="med_vist_comments" placeholder="'._comments.'" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-lg-6
            echo '</div>'; //.row

            echo '</div>';

            # ---   Advanced Search End ----------------------------------------------------------- #

            echo '<div class="row">';
            echo '<div class="col-md-12">';
            if (User('PROFILE') == 'admin') {
                echo '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=address_group value=Y' . (Preferences('DEFAULT_FAMILIES') == 'Y' ? ' CHECKED' : '') . '> '._groupByFamily.'</label>';
                echo '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=_search_all_schools value=Y' . (Preferences('DEFAULT_ALL_SCHOOLS') == 'Y' ? ' CHECKED' : '') . '> '._searchAllSchools.'</label>';
            }
            if ($_REQUEST['modname'] != 'students/StudentReenroll.php')
                echo '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=include_inactive value=Y> '._includeInactiveStudents.'</label>';
            echo '</div>'; //.col-md-12
            echo '</div>'; //.row
            // echo '<hr/>';

            $extra_footer = '<div class="text-right">';
            $extra_footer .= '<a id="advancedSearchForStudentDiv" href="javascript:void(0);" onclick="show_search_div();" class="text-pink m-r-10"><i class="icon-cog"></i> '._advancedSearch.'</a>';
            if ($extra['pdf'] != true)
                $extra_footer .= "<INPUT id=\"searchStuBtn\" type=SUBMIT class=\"btn btn-primary\" value='"._submit."' onclick='return formcheck_student_advnc_srch(this);formload_ajax(\"search\");'> &nbsp; <INPUT type=RESET class=\"btn btn-default\" value='"._reset."'>";
            else
                $extra_footer .= "<INPUT id=\"searchStuBtn\" type=SUBMIT class=\"btn btn-primary\" value='"._submit."' onclick='return formcheck_student_advnc_srch(this);'> &nbsp; <INPUT type=RESET class=\"btn btn-default\" value='"._reset."'>";
            $extra_footer .= '</div>';

            PopTable('footer', $extra['footer'] . $extra_footer);
            echo '</FORM>';
            // set focus to last name text box
            echo '<script type="text/javascript"><!--
				document.search.last.focus();
				--></script>';

            break;

        case 'parent':
        case 'student':
            if ($extra['pdf'] != true)
                echo "<FORM class='form-horizontal m-b-0' action=Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST>";
            else
                echo "<FORM class='form-horizontal m-b-0' action=ForExport.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST target=_blank>";

            PopTable('header',  _search);

            if ($extra['search'])
                echo $extra['search'];

            $btn = Buttons(_submit, _reset);

            PopTable('footer', $btn);
            echo '</FORM>';
            break;
    }
}
else if($_REQUEST['search_modfunc'] == 'search_mod')
{
    // $_REQUEST['expanded_view'] = 'false';

    $allow_buffer_list = array("students/AdvancedReport.php");

    if (!$_REQUEST['next_modname'])
        $_REQUEST['next_modname'] = 'students/Student.php';


    if(in_array($_REQUEST['modname'], $allow_buffer_list) && isset($_SESSION['student_id']) && $_SESSION['student_id'] != '')
    {
        if ($extra['pdf'] != true) {
            echo "<FORM name=search class=\"no-margin-bottom form-horizontal\" id=search action=Modules.php?modname=$_REQUEST[modname]&modfunc=call&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST>";
        } else {
            echo "<FORM name=search class=\"no-margin-bottom form-horizontal\" id=search action=ForExport.php?modname=$_REQUEST[modname]&modfunc=call&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST target=_blank>";
        }

        $extra['SELECT'] = ' ,s.STUDENT_ID AS CHECKBOX ,ssm.SECTION_ID';
        $extra['WHERE'] .= ' AND s.STUDENT_ID=' . $_SESSION['student_id'];
        $extra['functions'] = array('CHECKBOX' => '_makeChooseCheckbox', 'SECTION_ID' => '_make_sections');
    }
    else if($_REQUEST['modname'] == 'scheduling/UnfilledRequests.php' && isset($_SESSION['student_id']) && $_SESSION['student_id'] != '')
    {
        $extra['SELECT'] .= ' ,ssm.SECTION_ID';
        $extra['WHERE'] .= ' AND s.STUDENT_ID=' . $_SESSION['student_id'];
    }

    if(isset($_REQUEST['LO_sort']) && $_REQUEST['LO_sort'] != '' && $_REQUEST['LO_sort'] != NULL && isset($_REQUEST['LO_direction'])) {
        $extra['ORDER_BY'] = $_REQUEST['LO_sort'];

        if($_REQUEST['LO_direction'] == '1') {
            $extra['ORDER_BY'] = $_REQUEST['LO_sort'].' ASC';
        }
        if($_REQUEST['LO_direction'] == '-1') {
            $extra['ORDER_BY'] = $_REQUEST['LO_sort'].' DESC';
        }
    }

    # Set pagination params
    keepRequestParams($_REQUEST);
    keepExtraParams($extra);

    $students_RET = GetStuList($extra);

    if ($extra['array_function'] && function_exists($extra['array_function']))
        $students_RET = $extra['array_function']($students_RET);

    $LO_columns = array('FULL_NAME' =>_student,
     'STUDENT_ID' =>_studentId,
     'ALT_ID' =>_alternateId,
     'GRADE_ID' =>_grade,
     'SECTION_ID' =>_section,
     'PHONE' =>_phone,
    );

    if(in_array($_REQUEST['modname'], $allow_buffer_list) && isset($_SESSION['student_id']) && $_SESSION['student_id'] != '')
    {
        $extra['link'] = array('FULL_NAME' => '');
        $name_link = array();
        $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAllDtMod2(this,\'st_arr\');"><A>');
    }
    else if($_REQUEST['modname'] == 'scheduling/UnfilledRequests.php' && isset($_SESSION['student_id']) && $_SESSION['student_id'] != '')
    {
        $name_link['FULL_NAME']['link'] = "Modules.php?modname=$_REQUEST[next_modname]";
        $name_link['FULL_NAME']['variables'] = array('student_id' => 'STUDENT_ID');
    }

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
        if ($_REQUEST['modname'] != 'attendance/Administration.php')
            echo '<div class="panel panel-default">';

        $tmp_REQUEST = $_REQUEST;
        unset($tmp_REQUEST['expanded_view']);

        // echo "a-".$_REQUEST['expanded_view']."<br>";
        // echo "b-".UserStudentID()."<br>";
        // echo "c-".count($students_RET)."<br>";

        if ($_REQUEST['expanded_view'] != 'true' && UserStudentID() && count($students_RET) != 0) {
            DrawHeader("<A HREF=" . PreparePHP_SELF($tmp_REQUEST) . "&expanded_view=true><i class=\"icon-square-down-right\"></i> "._expandedView."</A>", $extra['header_right']);
            DrawHeader(str_replace('<BR>', '', substr($_openSIS['SearchTerms'], 0, -4)));
        } elseif (UserStudentID() && count($students_RET) != 0) {
            DrawHeader("<A HREF=" . PreparePHP_SELF($tmp_REQUEST) . "&expanded_view=false><i class=\"icon-square-up-left\"></i> "._originalView."</A>", $extra['header_right']);
            DrawHeader(str_replace('<BR>', '', substr($_openSIS['Search'], 0, -4)));
        }
        DrawHeader($extra['extra_header_left'], $extra['extra_header_right']);
        if ($_REQUEST['LO_save'] != '1' && !$extra['suppress_save']) {
            $_SESSION['List_PHP_SELF'] = PreparePHP_SELF($_SESSION['_REQUEST_vars']);
            //echo '<script language=JavaScript>parent.help.location.reload();</script>';
        }

        if (!$extra['singular'] || !$extra['plural']) {
            $extra['singular'] = 'Student';
            $extra['plural'] = 'Students';
        }

        echo "<div id='students'>";
        if ($_REQUEST['_search_all_schools'] == 'Y' && $_REQUEST['modname'] == 'scheduling/PrintSchedules.php')
            echo '<INPUT type=hidden name="_search_all_schools" value="Y">';


        if ($_REQUEST['modname'] == 'scheduling/Schedule.php' && $extra['singular'] == 'Request') {
            echo '<div class="panel-body">';
            if (count($students_RET) > 0) {
                echo '<div class="table-responsive">';
            }
            echo '<div id="hidden_checkboxes"></div>';
            $check_all_arr = array();
            foreach ($students_RET as $xy) {
                $check_all_arr[] = $xy['STUDENT_ID'];
            }
            $check_all_stu_list = implode(',', $check_all_arr);
            echo '<input type=hidden name=res_length id=res_length value="' . count($check_all_arr) . '">';
            echo '<input type=hidden name=res_len id=res_len value=\'' . $check_all_stu_list . '\'>';

            # Set pagination params
            setPaginationRequisites($_REQUEST['modname'], $_REQUEST['search_modfunc'], $_REQUEST['next_modname'], $columns, $extra['singular'], $extra['plural'], $link, $extra['LO_group'], $extra['options'], 'ListOutputUnscheduleRequests', ProgramTitle());

            echo "<div id='tabs_resp'>";
            ListOutputUnscheduleRequests($students_RET, $columns, $extra['singular'], $extra['plural'], $link, $extra['LO_group'], $extra['options']);
            echo '</div>';

            if (count($students_RET) > 0) {
                echo '</div>'; //.table-responsive
            }
            echo '</div>'; //.panel-body
        } else {
            if (User('PROFILE') == 'student' || User('PROFILE') == 'parent') {
                echo '<input type=hidden name=st_arr[] value=' . UserStudentID() . '>';
            }
            echo '<div class="panel-body">';
            $stu_ids_for_hidden = array();
            if (count($students_RET) > 0) {
                echo '<div class="table-responsive">';
            }
            echo '<div id="hidden_checkboxes"></div>';
            $check_all_arr = array();
            foreach ($students_RET as $xy) {
                $check_all_arr[] = $xy['STUDENT_ID'];
            }
            $check_all_stu_list = implode(',', $check_all_arr);
            echo'<input type=hidden name=res_length id=res_length value=\'' . count($check_all_arr) . '\'>';
            echo'<input type=hidden name=res_len id=res_len value=\'' . $check_all_stu_list . '\'>';

            if($_REQUEST['modname'] == 'users/TeacherPrograms.php?include=grades/ProgressReports.php' || $_REQUEST['modname'] == 'grades/ProgressReports.php' || $_REQUEST['modname'] == 'attendance/DailySummary.php' || $_REQUEST['modname'] == 'attendance/Administration.php') {
                ListOutputExcel($students_RET, $columns, $extra['singular'], $extra['plural'], $link, $extra['LO_group'], $extra['options']);
            } else {
                # Set pagination params
                setPaginationRequisites($_REQUEST['modname'], $_REQUEST['search_modfunc'], $_REQUEST['next_modname'], $columns, $extra['singular'], $extra['plural'], $link, $extra['LO_group'], $extra['options'], 'ListOutputExcelCustomDT', ProgramTitle());

                echo "<div id='tabs_resp'>";

                ListOutputExcelCustomDT($students_RET, $columns, $extra['singular'], $extra['plural'], $link, '', $extra['LO_group'], $extra['options']);

                echo '</div>';
            }


            if (count($students_RET) > 0) {
                echo '</div>'; //.table-responsive
            }
            echo '</div>'; //.panel-body

            if(in_array($_REQUEST['modname'], $allow_buffer_list) && isset($_SESSION['student_id']) && $_SESSION['student_id'] != '')
            {
                unset($_REQUEST['search_modfunc']);
                echo '<div class="text-right p-b-20 p-r-20"><INPUT type=submit value=\''._createReportForSelectedStudents.'\' class="btn btn-primary"></div>';
            }
        }

        echo '</div>'; //#students
        echo $extra['footer'];
        if ($_REQUEST['modname'] != 'attendance/Administration.php')
            echo "</div>"; //.panel
    } elseif (count($students_RET) == 1) {

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


           // echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
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
        BackPrompt(_noStudentsWereFound.'.');
}
else {
    if (!$_REQUEST['next_modname'])
        $_REQUEST['next_modname'] = 'students/Student.php';

    if ($_REQUEST['address_group']) {
        $extra['SELECT'] = $extra['SELECT'] . ',ssm.student_id AS CHILD';
        if (!empty($extra['functions']))
            $extra['functions'] += array('CHILD' => '_make_Parents');
        else
            $extra['functions'] = array('CHILD' => '_make_Parents');

        if (!($_REQUEST['expanded_view'] == 'true' || $_REQUEST['addr'] || $extra['addr'])) {

            //  if($_REQUEST['modname']=='students/AdvancedReport.php')
            if ($_REQUEST['w_course_period_id'] != '')
                $extra['FROM'] .= ' INNER JOIN students_join_people sam ON (sam.STUDENT_ID=ssm.STUDENT_ID) INNER JOIN schedule w_ss ON (w_ss.STUDENT_ID=ssm.STUDENT_ID) ';
            else
                $extra['FROM'] .= ' INNER JOIN students_join_people sam ON (sam.STUDENT_ID=ssm.STUDENT_ID) ';




            $extra['ORDER_BY'] = 'FULL_NAME';
            $extra['DISTINCT'] = 'DISTINCT';
        }
    }
    if ($_REQUEST['request_course_id']) {
        $course = DBGet(DBQuery('SELECT c.TITLE FROM courses c WHERE c.COURSE_ID=\'' . $_REQUEST['request_course_id'] . '\''));
        if (!$_REQUEST['not_request_course']) {
            $extra['FROM'] .= ',schedule_requests sch_r';
            $extra['WHERE'] = ' AND sch_r.STUDENT_ID=s.STUDENT_ID AND sch_r.SYEAR=ssm.SYEAR AND sch_r.SCHOOL_ID=ssm.SCHOOL_ID AND sch_r.COURSE_ID=\'' . $_REQUEST['request_course_id'] . '\'';

            $_openSIS['SearchTerms'] .= '<font color=gray><b>'._request.': </b></font>' . $course[1]['TITLE'] . '<BR>';
        } else {
            $extra['WHERE'] .= ' AND NOT EXISTS (SELECT \'\' FROM schedule_requests sch_r WHERE sch_r.STUDENT_ID=ssm.STUDENT_ID AND sch_r.SYEAR=ssm.SYEAR AND sch_r.COURSE_ID=\'' . $_REQUEST['request_course_id'] . '\') ';
            $_openSIS['SearchTerms'] .= '<font color=gray><b>'._missingRequest.': </b></font>' . $course[1]['TITLE'] . '<BR>';
        }
    }

    if ($_SESSION['MassDrops.php']['course_period_id'] != '') {
       if($_REQUEST['modname'] !='scheduling/PrintSchedules.php')
       {
            $extra['FROM'] .=',schedule sr '; 
                 $extra['WHERE'] .=' AND sr.STUDENT_ID=ssm.STUDENT_ID AND s.student_id=ssm.student_id'; 
       }
       $extra['WHERE'] .= ' AND sr.SYEAR=ssm.SYEAR AND sr.SCHOOL_ID=ssm.SCHOOL_ID AND sr.COURSE_PERIOD_ID=\'' . $_SESSION['MassDrops.php']['course_period_id'] . '\'';
       if($_REQUEST['modname'] !='attendance/DailySummary.php' && $_REQUEST['modname'] !='scheduling/Schedule.php' && $_REQUEST['modname'] !='scheduling/ViewSchedule.php') 
       unset($_SESSION['MassDrops.php']['course_period_id']);
    }

    $extra['SELECT'] .= ' ,ssm.SECTION_ID';
    if (is_countable($extra['functions']) && count($extra['functions']) > 0)
        $extra['functions'] += array('SECTION_ID' => '_make_sections');
    else
        $extra['functions'] = array('SECTION_ID' => '_make_sections');

    if ($_REQUEST['section'] != '')
        $extra['WHERE'] .= ' AND ssm.SECTION_ID=' . $_REQUEST['section'];

    if(isset($_REQUEST['LO_sort']) && $_REQUEST['LO_sort'] != '' && $_REQUEST['LO_sort'] != NULL && isset($_REQUEST['LO_direction'])) {
        $extra['ORDER_BY'] = $_REQUEST['LO_sort'];

        if($_REQUEST['LO_direction'] == '1') {
            $extra['ORDER_BY'] = $_REQUEST['LO_sort'].' ASC';
        }
        if($_REQUEST['LO_direction'] == '-1') {
            $extra['ORDER_BY'] = $_REQUEST['LO_sort'].' DESC';
        }
    }

    # Set pagination params
    keepRequestParams($_REQUEST);
    keepExtraParams($extra);

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

    $LO_columns = array('FULL_NAME' =>_student,
     'STUDENT_ID' =>_studentId,
     'ALT_ID' =>_alternateId,
     'GRADE_ID' =>_grade,
     'SECTION_ID' =>_section,
     'PHONE' =>_phone,
    );
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

    if ((is_countable($students_RET) && count($students_RET) > 1) || $link['add'] || !$link['FULL_NAME'] || $extra['columns_before'] || $extra['columns_after'] || ($extra['BackPrompt'] == false && (is_countable($students_RET) && count($students_RET) == 0)) || ($extra['Redirect'] === false && (is_countable($students_RET) && count($students_RET) == 1))) {
        if ($_REQUEST['modname'] != 'attendance/Administration.php')
            echo '<div class="panel panel-default">';

        $tmp_REQUEST = $_REQUEST;
        unset($tmp_REQUEST['expanded_view']);
        if ($_REQUEST['expanded_view'] != 'true' && !UserStudentID() && (is_countable($students_RET) && count($students_RET) != 0)) {
            DrawHeader("<A HREF=" . PreparePHP_SELF($tmp_REQUEST) . "&expanded_view=true><i class=\"icon-square-down-right\"></i> "._expandedView."</A>", $extra['header_right']);
            DrawHeader(str_replace('<BR>', '', substr($_openSIS['SearchTerms'], 0, -4)));
        } elseif (!UserStudentID() && (is_countable($students_RET) && count($students_RET) != 0)) {
            DrawHeader("<A HREF=" . PreparePHP_SELF($tmp_REQUEST) . "&expanded_view=false><i class=\"icon-square-up-left\"></i> "._originalView."</A>", $extra['header_right']);
            DrawHeader(str_replace('<BR>', '', substr($_openSIS['Search'], 0, -4)));
        }
        DrawHeader($extra['extra_header_left'], $extra['extra_header_right']);
        if ($_REQUEST['LO_save'] != '1' && !$extra['suppress_save']) {
            $_SESSION['List_PHP_SELF'] = PreparePHP_SELF($_SESSION['_REQUEST_vars']);
            //echo '<script language=JavaScript>parent.help.location.reload();</script>';
        }

        if (!$extra['singular'] || !$extra['plural']) {
            $extra['singular'] = 'Student';
            $extra['plural'] = 'Students';
        }

        if ($_REQUEST['modname'] == 'grades/GPARankList.php') {
            $class_rank = array();
            foreach ($students_RET as $sr => $sd) {
                $class_rank[$sd['STUDENT_ID']] = $sd['GPA'];
            }

            $new_class_rank = array_unique($class_rank);
            rsort($new_class_rank);

            $final_class_rank = array();
            unset($cr);
            unset($cd);
            foreach ($class_rank as $ci => $cr) {
                $array_key = array_keys($new_class_rank, $cr);
                $final_class_rank[$ci] = $array_key[0] + 1;
            }
            unset($sr);
            unset($sd);
            foreach ($students_RET as $sr => $sd) {
                $students_RET[$sr]['CLASS_RANK'] = $final_class_rank[$sd['STUDENT_ID']];
            }
            unset($class_rank);
            unset($new_class_rank);
            unset($final_class_rank);
            unset($array_key);
            unset($sr);
            unset($sd);
        }

        echo "<div id='students'>";
        if ($_REQUEST['_search_all_schools'] == 'Y' && $_REQUEST['modname'] == 'scheduling/PrintSchedules.php')
            echo '<INPUT type=hidden name="_search_all_schools" value="Y">';


        if ($_REQUEST['modname'] == 'scheduling/Schedule.php' && $extra['singular'] == 'Request') {
            echo '<div class="panel-body">';
            if (count($students_RET) > 0) {
                echo '<div class="table-responsive">';
            }
            echo '<div id="hidden_checkboxes"></div>';
            $check_all_arr = array();
            foreach ($students_RET as $xy) {
                $check_all_arr[] = $xy['STUDENT_ID'];
            }
            $check_all_stu_list = implode(',', $check_all_arr);
            echo '<input type=hidden name=res_length id=res_length value="' . count($check_all_arr) . '">';
            echo '<input type=hidden name=res_len id=res_len value=\'' . $check_all_stu_list . '\'>';

            ListOutputUnscheduleRequests($students_RET, $columns, $extra['singular'], $extra['plural'], $link, $extra['LO_group'], $extra['options']);

            if (count($students_RET) > 0) {
                echo '</div>'; //.table-responsive
            }
            echo '</div>'; //.panel-body
        } else {
            if (User('PROFILE') == 'student' || User('PROFILE') == 'parent') {
                echo '<input type=hidden name=st_arr[] value=' . UserStudentID() . '>';
            }
            echo '<div class="panel-body">';
            $stu_ids_for_hidden = array();
            if (is_countable($students_RET) && count($students_RET) > 0) {
                echo '<div class="table-responsive">';
            }
            echo '<div id="hidden_checkboxes"></div>';
            $check_all_arr = array();
            foreach ($students_RET as $xy) {
                $check_all_arr[] = $xy['STUDENT_ID'];
            }
            $check_all_stu_list = implode(',', $check_all_arr);
            echo'<input type=hidden name=res_length id=res_length value=\'' . count($check_all_arr) . '\'>';
            echo'<input type=hidden name=res_len id=res_len value=\'' . $check_all_stu_list . '\'>';

            if($_REQUEST['modname'] == 'users/TeacherPrograms.php?include=grades/ProgressReports.php' || $_REQUEST['modname'] == 'grades/ProgressReports.php' || $_REQUEST['modname'] == 'attendance/DailySummary.php' || $_REQUEST['modname'] == 'attendance/Administration.php') {
                ListOutputExcel($students_RET, $columns, $extra['singular'], $extra['plural'], $link, $extra['LO_group'], $extra['options']);
            } else {
                # Set pagination params
                setPaginationRequisites($_REQUEST['modname'], $_REQUEST['search_modfunc'], $_REQUEST['next_modname'], $columns, $extra['singular'], $extra['plural'], $link, $extra['LO_group'], $extra['options'], 'ListOutputExcelCustomDT', ProgramTitle());

                echo "<div id='tabs_resp'>";
                ListOutputExcelCustomDT($students_RET, $columns, $extra['singular'], $extra['plural'], $link, '', $extra['LO_group'], $extra['options']);
                echo '</div>';
            }

            if (is_countable($students_RET) && count($students_RET) > 0) {
                echo '</div>'; //.table-responsive
            }
            echo '</div>'; //.panel-body
        }

        echo '</div>'; //#students
        echo $extra['footer'];
        if ($_REQUEST['modname'] != 'attendance/Administration.php')
            echo "</div>"; //.panel
    } elseif (count($students_RET) == 1) {
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


//            echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
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
        BackPrompt(_noStudentsWereFound.'.');
}
echo '<div id="modal_default_request" class="modal fade">';
echo '<div class="modal-dialog">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<button type="button" class="close" data-dismiss="modal">×</button>';
echo '<h5 class="modal-title">'._chooseCourse.'</h5>';
echo '</div>';

echo '<div class="modal-body">';
echo '<center><div id="conf_div"></div></center>';

echo'<div class="row" id="resp_table">';
echo '<div class="col-md-6">';
$sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
$QI = DBQuery($sql);
$subjects_RET = DBGet($QI);

echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' '._subjectWas : ' '._subjectsWere) . ' '._found.'.</h6>';
if (count($subjects_RET) > 0) {
    echo '<table class="table table-bordered"><tr class="alpha-grey"><th>'._subject.'</th></tr>';
    foreach ($subjects_RET as $val) {
        echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearchRequest(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
    }
    echo '</table>';
}
echo '</div>';
echo '<div class="col-md-6"><div id="course_modal_request"></div></div>';
echo '</div>'; //.row
echo '</div>'; //.modal-body

echo '</div>'; //.modal-content
echo '</div>'; //.modal-dialog
echo '</div>'; //.modal



echo '<div id="modal_default" class="modal fade">';
echo '<div class="modal-dialog modal-lg">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<button type="button" class="close" data-dismiss="modal">×</button>';
echo '<h5 class="modal-title">'._chooseCourse.'</h5>';
echo '</div>';

echo '<div class="modal-body">';
echo '<div id="conf_div" class="text-center"></div>';
echo '<div class="row" id="resp_table">';
echo '<div class="col-md-4">';
$sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
$QI = DBQuery($sql);
$subjects_RET = DBGet($QI);

echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' '._subjectWas : ' '._subjectsWere) . ' '._found.'.</h6>';
if (count($subjects_RET) > 0) {
    echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>'._subject.'</th></tr></thead><tbody>';
    foreach ($subjects_RET as $val) {
        echo '<tr><td><a href=javascript:void(0); onclick="MassDropModal(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
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


/* New Modal Design Starts */
echo '<div id="modal_default_cp_calc" class="modal fade">';
echo '<div class="modal-dialog modal-xl">';
echo '<div class="modal-content">';

echo '<div class="modal-header">';
echo '<button type="button" class="close" data-dismiss="modal">×</button>';
echo '<h5 class="modal-title">'._chooseCourse.'</h5>';
echo '</div>'; //.modal-header
if ($_REQUEST['modname'] == 'scheduling/Schedule.php') {
    if (isset($_SESSION['schedule_selected_date']) && $_SESSION['schedule_selected_date'] != '') {
        $orderdate = explode('-', $_SESSION['schedule_selected_date']);
        $year = $orderdate[0];
        $month = $orderdate[1];
        $day  = $orderdate[2];
        unset($_SESSION['schedule_selected_date']);

        echo '<FORM class="m-b-0" name="courses" method="post" action="Modules.php?modname=scheduling/Schedule.php?modfunc=cp_insert&month_date=' . $month . '&day_date=' . $day . '&year_date=' . $year . '">';
    } else {
        echo '<FORM class="m-b-0" name="courses" method="post" action="Modules.php?modname=scheduling/Schedule.php?modfunc=cp_insert">';
    }
}
echo '<div class="modal-body">';

echo '<div id=conf_div1 class=text-center></div>';

echo '<div id="calculating" class="text-center" style="display:none;"><i class="fa fa-refresh fa-spin fa-fw"></i> '._checkingSchedulePleaseWait.'...</div>';
if ($clash) {
    echo '<div class="text-center"><b>'._thereIsAConflictYouCannotAddThisCoursePeriod.' </b>' . ErrorMessage($clash, 'note') . '</div>';
}
echo '<div class="row" id="resp_table">';
echo '<div class="col-md-12" class="col-md-4"id="selected_course1"></div>';
echo '<div class="col-md-4">';
$sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
$QI = DBQuery($sql);
$subjects_RET = DBGet($QI);

echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' '._subjectWas : ' '._subjectsWere) . ' '._found.'.</h6>';
echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>'._subject.'</th></tr></thead>';
echo '<tbody>';
foreach ($subjects_RET as $val) {
    echo '<tr><td><a href=javascript:void(0); onclick="grab_coursePeriod(' . $val['SUBJECT_ID'] . ',\'courses\',\'subject_id\')">' . $val['TITLE'] . '</a></td></tr>    ';
}
echo '</tbody>';
echo '</table>';
echo '</div>';
echo '<div class="col-md-4"><div id="course_modal_cp"></div></div>';
echo '<div class="col-md-4"><div id="cp_modal_cp"></div></div>';
echo '</div>'; //.row
echo '</div>'; //.modal-body
//if (count($coursePeriods_RET)) {
    echo '<div id="sub_btn" class="modal-footer text-right p-r-20" style="display:none">' . SubmitButtonModal(_done, 'done', 'class="btn btn-primary" ') . '&nbsp;&nbsp;' . SubmitButtonModal(_close, 'exit', 'class="btn btn-white"') . '</div>';
//}
if ($_REQUEST['modname'] == 'scheduling/Schedule.php')
echo '</FORM>';

echo '</div>'; //.modal-content
echo '</div>'; //.modal-dialog
echo '</div>'; //.modal

// function _make_sections($value) {
//     if ($value != '') {
//         $get = DBGet(DBQuery('SELECT NAME FROM school_gradelevel_sections WHERE ID=' . $value));
//         return $get[1]['NAME'];
//     } else
//         return '';
// }
