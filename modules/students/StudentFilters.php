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
include('RedirectRootInc.php');
include('Warehouse.php');
include('modules/students/configInc.php');
ini_set('memory_limit', '1200000000M');
ini_set('max_execution_time', '500000');

session_start();

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'filter_edit_save' && $_REQUEST['filter_id'] != '')
{
    $filter_names = array("last", "first", "stuid", "altid", "addr", "grade", "section", "address_group", "_search_all_schools", "include_inactive", "mp_comment","GENDER","ETHNICITY_ID","LANGUAGE_ID", "goal_title", "goal_description", "progress_name", "progress_description", "doctors_note_comments", "type", "imm_comments", "med_alrt_title", "reason", "result", "med_vist_comments");

    $cust_filters = array();

    if(isset($_REQUEST['each_custom_fields_ids']) && $_REQUEST['each_custom_fields_ids'] != '')
    {
        $res_cfs = explode(", ", $_REQUEST['each_custom_fields_ids']);

        foreach($res_cfs as $one_cf)
        {
            array_push($cust_filters, $one_cf);
        }
    }

    // echo "<pre>";print_r($_REQUEST);echo "</pre>";
    // die;

    // $edit_counter=0;

    foreach ($filter_names as $filter_key) {
        if ($_REQUEST[$filter_key] != '') {
            $check_filter_exist = DBGet(DBQuery('SELECT COUNT(*) AS COUNT_FILTER FROM filter_fields WHERE FILTER_ID = "' . $_REQUEST['filter_id'] . '" AND FILTER_COLUMN = "' . $filter_key . '"'));
            
            if ($check_filter_exist[1]['COUNT_FILTER'] == 0) {
                DBQuery('INSERT INTO filter_fields (FILTER_ID,FILTER_COLUMN,FILTER_VALUE) VALUES ("' . $_REQUEST['filter_id'] . '",\'' . $filter_key . '\',\'' . $_REQUEST[$filter_key] . '\')');
                // $edit_counter++;
            } else {
                DBQuery('UPDATE filter_fields SET FILTER_VALUE = "' . $_REQUEST[$filter_key] . '" WHERE FILTER_ID = "' . $_REQUEST['filter_id'] . '" AND FILTER_COLUMN = "' . $filter_key . '"');
                // $edit_counter++;
            }
        }
    }

    if(!empty($cust_filters))
    {
        foreach($cust_filters as $cust_filters_columns)
        {
            if(isset($_REQUEST['cust']) && $_REQUEST['cust'][$cust_filters_columns] != '')
            {
                $check_filter_exist = DBGet(DBQuery('SELECT COUNT(*) AS COUNT_FILTER FROM filter_fields WHERE FILTER_ID = "' . $_REQUEST['filter_id'] . '" AND FILTER_COLUMN = "' . $cust_filters_columns . '"'));

                if ($check_filter_exist[1]['COUNT_FILTER'] == 0) 
                {
                    DBQuery('INSERT INTO filter_fields (FILTER_ID,FILTER_COLUMN,FILTER_VALUE) VALUES ('.$filter_id.',\''.$cust_filters_columns.'\',\''.$_REQUEST['cust'][$cust_filters_columns].'\')');
                }
                else
                {
                    DBQuery('UPDATE filter_fields SET FILTER_VALUE = "' . $_REQUEST['cust'][$cust_filters_columns] . '" WHERE FILTER_ID = "' . $_REQUEST['filter_id'] . '" AND FILTER_COLUMN = "' . $cust_filters_columns . '"');
                }
            }
        }
    }


    $get_filter_name = DBGet(DBQuery('SELECT * FROM filters WHERE SCHOOL_ID IN (' . UserSchool() . ',0) AND SHOW_TO IN (' . UserID() . ',0) AND FILTER_ID =' . $_REQUEST['filter_id']));
    
    $this_SHOW_TO= $get_filter_name[1]['SHOW_TO'];
    
    $this_ALL_SCHOOL=$get_filter_name[1]['SCHOOL_ID'];
    
    if(isset($_REQUEST['filter_public']))
    {
        if($_REQUEST['filter_public']=='Y')
        {
            $this_SHOW_TO='0';
        }
        else
        {
            $this_SHOW_TO=UserID();
        }
    }
    else if($get_filter_name[1]['SHOW_TO'] == 0 && !isset($_REQUEST['filter_public']))
    {
        $this_SHOW_TO=UserID();
    }


    if(isset($_REQUEST['filter_all_school']))
    {
        if($_REQUEST['filter_all_school']=='Y')
        {
            $this_ALL_SCHOOL='0';
        }
        else
        {
            $this_ALL_SCHOOL=UserSchool();
        }
    }
    else if($get_filter_name[1]['SCHOOL_ID'] == 0 && !isset($_REQUEST['filter_all_school']))
    {
        $this_ALL_SCHOOL=UserSchool();
    }
    DBQuery('UPDATE filters SET filter_name = "'.$_REQUEST['filter_name'].'", show_to = "' . $this_SHOW_TO . '", school_id = "' . $this_ALL_SCHOOL . '" WHERE FILTER_ID = "' . $_REQUEST['filter_id'] . '"'); 
}

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'remove' && $_REQUEST['filter_id'] != '') {
    if (DeletePrompt_Filter('filter')) {
        $filter_id = paramlib_validation($colmn = 'FILTER_ID', $_REQUEST['filter_id']);
        DBQuery('DELETE FROM filters WHERE FILTER_ID=\'' . $filter_id . '\'');
        DBQuery('DELETE FROM filter_fields WHERE FILTER_ID=\'' . $filter_id . '\'');
        unset($_REQUEST['modfunc']);
    }
}
if (isset($_REQUEST['delete_ok']) && $_REQUEST['delete_ok'] == 1) {
    echo "<script>window.location.href='Modules.php?modname=students/Student.php';</script>";
}

if ((clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'filter_edit' || clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'filter_edit_save') && $_REQUEST['filter_id'] != '') {

    if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'filter_edit_save') {
        echo "<div class='alert alert-success alert-styled-left alert-dismissible'><a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;&nbsp;&nbsp;</a>"._theFilterHasBeenSuccessfullySaved."</div>";
    }

    $get_filters = DBGet(DBQuery('SELECT * FROM filter_fields WHERE FILTER_ID=' . $_REQUEST['filter_id']));

    foreach ($get_filters as $get_results) {
        $_REQUEST[$get_results['FILTER_COLUMN']] = $get_results['FILTER_VALUE'];

        if(strpos($get_results['FILTER_COLUMN'], 'CUSTOM') !== false)
        {
            $_REQUEST['cust'][$get_results['FILTER_COLUMN']] = $get_results['FILTER_VALUE'];
        }
    }

    $get_filters2 = DBGet(DBQuery('SELECT * FROM filters WHERE FILTER_ID=' . $_REQUEST['filter_id']));
    
    foreach ($get_filters2 as $get_results2) {
        if ($get_results2['SHOW_TO'] == '0' && $get_results2['SCHOOL_ID'] == '0')
        {
            $_REQUEST['filter_public'] = 'Y';
            $_REQUEST['filter_all_school'] = 'Y';
        }
        if ($get_results2['SHOW_TO'] != '0' && $get_results2['SCHOOL_ID'] == '0')
        {
            $_REQUEST['filter_public'] = 'N';
            $_REQUEST['filter_all_school'] = 'Y';
        }
        if ($get_results2['SHOW_TO'] == '0' && $get_results2['SCHOOL_ID'] != '0')
        {
            $_REQUEST['filter_public'] = 'Y';
            $_REQUEST['filter_all_school'] = 'N';
        }
        if ($get_results2['SHOW_TO'] != '0' && $get_results2['SCHOOL_ID'] != '0')
        {
            $_REQUEST['filter_public'] = 'N';
            $_REQUEST['filter_all_school'] = 'N';
        }
            
    }
    

    echo "<FORM name=search onSubmit='return save_student_filters();' class=\"form-horizontal m-b-0\" id=search action=Modules.php?modname=$_REQUEST[modname]&modfunc=filter_edit_save&filter_id=$_REQUEST[filter_id] method=POST>";
    echo '<div id="error_modal_filter"></div>';
    echo '<div class="panel">';
    echo '<ul class="nav nav-tabs nav-tabs-bottom no-margin-bottom"><li class="active" id="tab[]"><a href="javascript:void(0);">'._editFilter.'</a></li></ul>';
    echo '<div class="panel-body">';

    $get_filter_name = DBGet(DBQuery('SELECT * FROM filters WHERE SCHOOL_ID IN (' . UserSchool() . ',0) AND SHOW_TO IN (' . UserID() . ',0) AND FILTER_ID =' . $_REQUEST['filter_id']));
    $_REQUEST['filter_name']=$get_filter_name[1]['FILTER_NAME'];
    if ( $get_filter_name[1]['SHOW_TO'] == 0)
            $get_filter_show_to = 'checked';
    else 
            $get_filter_show_to = '';

    if ( $get_filter_name[1]['SCHOOL_ID'] == 0)
            $get_filter_school_id = 'checked';
    else 
            $get_filter_school_id = '';


    $filter_modal = DBGet(DBQuery('SELECT FILTER_NAME FROM filters WHERE SCHOOL_ID IN ('.UserSchool().',0) AND SHOW_TO IN ('. UserID().',0)'));

    $filter_name = array_column($filter_modal, 'FILTER_NAME');

    if (($key = array_search($_REQUEST['filter_name'], $filter_name)) !== false) {
        unset($filter_name[$key]);
    }

    $other_filter_list = '';

    foreach($filter_name as $one_filter)
    {
        if($other_filter_list == '')
        {
            $other_filter_list .= $one_filter;
        }
        else
        {
            $other_filter_list .= ','.$one_filter;
        }
    }


    echo '<div class="row">';
    echo '<div class="col-md-12">';
    echo '<div class="table-responsive">';
    echo '<table class="table table-striped table-bordered table-xxs">';
    echo '<tbody>';
    echo '<tr>';
    echo '<th><span class="text-primary">'._filterName.'</span></th>';
    echo '<th><span class="text-primary">'._makePublic.'</span></th>';
    echo '<th><span class="text-primary">'._allSchool.'</span></th>';
    echo '</tr>';
    echo '<tr>';
    echo '<td><input class="form-control" type="text" id="filter_name" name="filter_name" value="' . $_REQUEST['filter_name'] . '"/></td>';
    echo '<td><div class="checkbox checkbox-switch switch-success"><label><input type="checkbox" name="filter_public" '.$get_filter_show_to.' value="Y" ><span></span></label></div></td>';
    echo '<td><div class="checkbox checkbox-switch switch-success"><label><input type="checkbox" name="filter_all_school" '.$get_filter_school_id.' value="Y" ><span></span></label></div></td>';
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    echo '</div>'; // .col-md-6
    echo '</div>'; // .row

    //    echo '<div class="row">';
    //    echo '<div class="col-md-3"><Label>Filter Name</Label><input class="form-control" type="text" id="filter_name" name="filter_name" value="' . $_REQUEST['filter_name'] . '"/></div>';
    //
    //    echo '<div class="col-md-3"> Make Public <div class="checkbox checkbox-switch switch-success"><label><input type="checkbox" name="filter_public" '.$get_filter_show_to.' value="Y" ><span></span></label></div></div>';
    //
    //    echo '<div class="col-md-3"> All School <div class="checkbox checkbox-switch switch-success"><label><input type="checkbox" name="filter_all_school" '.$get_filter_school_id.' value="Y" ><span></span></label></div></div>';
    //    echo '</div>'; // .row
    

    echo '</div>'; // .panel-body

    echo '<div class="panel-body">';
    echo '<div class="table-responsive">';
    echo '<table class="table table-striped table-bordered table-xxs">';
    echo '<tbody>';
    echo '<tr>';
    echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleLastName\');">'._lastName.'</a></th>';
    echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleFirstName\');">'._firstName.'</a></th>';
    echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleStudentId\');">'._studentId.'</a></th>';
    echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleAltId\');">'._altId.'</th>';
    echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleAddress\');">'._address.'</th>';
    echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleGrade\');">'._grade.'</th>';
    echo '</tr>';
    echo '<tr>';


    if ($_REQUEST['last'] != '')
        echo '<td><div  id="toggleLastName_element"><input type="text" id="last" name="last" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._lastName.'" value="' . $_REQUEST['last'] . '"/></div></td>';
    else
        echo '<td><div onclick="divToggle(\'#toggleLastName\');" id="toggleLastName">Any</div><div style="display:none;" id="toggleLastName_element" class="hide-element"><input type="text" name="last" id="last" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._lastName.'" /></div></td>';

    if ($_REQUEST['first'] != '')
        echo '<td><div id="toggleFirstName_element"><input type="text" id="first" name="first" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._firstName.'" value="' . $_REQUEST['first'] . '"/></div></td>';
    else
        echo '<td><div onclick="divToggle(\'#toggleFirstName\');" id="toggleFirstName">Any</div><div style="display:none;" id="toggleFirstName_element" class="hide-element"><input type="text" id="first" name="first" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._firstName.'" /></div></td>';


    if ($_REQUEST['stuid'] != '')
        echo '<td><div id="toggleStudentId_element"><input type="text" id="stuid" name="stuid" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._studentId.'" value="' . $_REQUEST['stuid'] . '"/></div></td>';
    else
        echo '<td><div onclick="divToggle(\'#toggleStudentId\');" id="toggleStudentId">Any</div><div style="display:none;" id="toggleStudentId_element" class="hide-element"><input type="text" id="stuid" name="stuid" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._studentId.'" /></div></td>';

    if ($_REQUEST['altid'] != '')
        echo '<td><div id="toggleAltId_element"><input type="text" id="altid" name="altid" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._altId.'" value="' . $_REQUEST['altid'] . '"/></div></td>';
    else
        echo '<td><div onclick="divToggle(\'#toggleAltId\');" id="toggleAltId">Any</div><div style="display:none;" id="toggleAltId_element" class="hide-element"><input type="text" id="altid" name="altid" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._altId.'" /></div></td>';


    if ($_REQUEST['addr'] != '')
        echo '<td><div id="toggleAddress_element" class="hide-element"><input type="text" id="addr" name="addr" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._address.'" value="' . $_REQUEST['addr'] . '"/></div></td>';
    else
        echo '<td><div onclick="divToggle(\'#toggleAddress\');" id="toggleAddress">Any</div><div style="display:none;" id="toggleAddress_element" class="hide-element"><input type="text" id="addr" name="addr" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._address.'" /></div></td>';


    $list = DBGet(DBQuery("SELECT DISTINCT TITLE,ID,SORT_ORDER FROM school_gradelevels WHERE SCHOOL_ID='" . UserSchool() . "' ORDER BY SORT_ORDER"));

    if ($_REQUEST['grade'] != '') {
        echo '<td><div id="toggleGrade_element"><select id="grade" name=grade class="form-control p-t-0 p-b-0 input-xs"><option value="">-- Select --</option>';
        foreach ($list as $value)
            echo '<option value="' . $value['TITLE'] . '" ' . ($value['TITLE'] == $_REQUEST['grade'] ? 'selected' : '') . '>' . $value['TITLE'] . '</option>';
        echo '</select></div></td>';
        echo '</tr>';
    } else {
        echo '<td><div onclick="divToggle(\'#toggleGrade\');" id="toggleGrade">Any</div><div style="display:none;" id="toggleGrade_element" class="hide-element"><select id="grade" name=grade class="form-control p-t-0 p-b-0 input-xs"><option value="">-- Select --</option>';
        foreach ($list as $value)
            echo '<option value="' . $value['TITLE'] . '">' . $value['TITLE'] . '</option>';
        echo '</select></div></td>';
        echo '</tr>';
    }
    echo '<tr>';
    echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleSection\');">'._section.'</a></th>';
    echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleGrpByFamily\');">'._groupByFamily.'</a></th>';
    echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleSearchAllSchool\');">'._searchAllSchools.'</a></th>';
    echo '<th colspan="3"><a href="javascript:void(0);" onclick="divToggle(\'#toggleIncludeInactive\');">'._includeInactiveStudents.'</a></th>';
    echo '</tr>';


    $list = DBGet(DBQuery("SELECT DISTINCT NAME,ID,SORT_ORDER FROM school_gradelevel_sections WHERE SCHOOL_ID='" . UserSchool() . "' ORDER BY SORT_ORDER"));
    echo '<tr>';

    if ($_REQUEST['section'] != '') {
        echo '<td><div id="toggleSection_element">';
        echo '<select id="section" name=section class="form-control p-t-0 p-b-0 input-xs"><option value="">-- Select --</option>';
        foreach ($list as $value)
            echo '<option value="' . $value['ID'] . '" ' . ($value['ID'] == $_REQUEST['section'] ? 'selected' : '') . '>' . $value['NAME'] . '</option>';
        echo '</select></div></td>';
    } else {
        echo '<td><div onclick="divToggle(\'#toggleSection\');" id="toggleSection">Any</div><div style="display:none;" id="toggleSection_element" class="hide-element">';
        echo '<select id="section" name=section class="form-control p-t-0 p-b-0 input-xs"><option value="">-- Select --</option>';
        foreach ($list as $value)
            echo '<option value=' . $value['ID'] . '>' . $value['NAME'] . '</option>';
        echo '</select></div></td>';
    }

    if ($_REQUEST['address_group'] != '')
        echo '<td><div id="toggleGrpByFamily_element"><div class="checkbox m-b-0"><label><input id="address_group" type="checkbox" name="address_group" value="Y" checked/></label></div></div></td>';
    else
        echo '<td><div onclick="divToggle(\'#toggleGrpByFamily\');" id="toggleGrpByFamily">No</div><div style="display:none;" id="toggleGrpByFamily_element" class="hide-element"><div class="checkbox m-b-0"><label><input type="checkbox" id="address_group" name="address_group" value="Y"/></label></div></div></td>';

    if ($_REQUEST['_search_all_schools'] != '')
        echo '<td><div id="toggleSearchAllSchool_element"><div class="checkbox m-b-0"><label><input type="checkbox" id="_search_all_schools" name="_search_all_schools" value="Y" checked/></label></div></div></td>';
    else
        echo '<td><div onclick="divToggle(\'#toggleSearchAllSchool\');" id="toggleSearchAllSchool">No</div><div style="display:none;" id="toggleSearchAllSchool_element" class="hide-element"><div class="checkbox m-b-0"><label><input type="checkbox" id="_search_all_schools" name="_search_all_schools" value="Y"/></label></div></div></td>';

    if ($_REQUEST['include_inactive'] != '')
        echo '<td colspan="3"><div id="toggleIncludeInactive_element"><div class="checkbox m-b-0"><label><input type="checkbox" id="include_inactive" name="include_inactive" value="Y" checked/></label></div></div></td>';
    else
        echo '<td colspan="3"><div onclick="divToggle(\'#toggleIncludeInactive\');" id="toggleIncludeInactive">No</div><div style="display:none;" id="toggleIncludeInactive_element" class="hide-element"><div class="checkbox m-b-0"><label><input type="checkbox" id="include_inactive" name="include_inactive" value="Y"/></label></div></div></td>';
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
    echo '</div>'; //.table-responsive
    echo '</div>'; //.panel-body

    # ---   Advanced Filter Start ---------------------------------------------------------- #
    $ethnicity = DBGet(DBQuery('SELECT * FROM ethnicity'));
    $ethnic_option = array($ethnicity[1]['ETHNICITY_ID'] => $ethnicity[1]['ETHNICITY_NAME'], $ethnicity[2]['ETHNICITY_ID'] => $ethnicity[2]['ETHNICITY_NAME'], $ethnicity[3]['ETHNICITY_ID'] => $ethnicity[3]['ETHNICITY_NAME'], $ethnicity[4]['ETHNICITY_ID'] => $ethnicity[4]['ETHNICITY_NAME'], $ethnicity[5]['ETHNICITY_ID'] => $ethnicity[5]['ETHNICITY_NAME'], $ethnicity[6]['ETHNICITY_ID'] => $ethnicity[6]['ETHNICITY_NAME'], $ethnicity[7]['ETHNICITY_ID'] => $ethnicity[7]['ETHNICITY_NAME'], $ethnicity[8]['ETHNICITY_ID'] => $ethnicity[8]['ETHNICITY_NAME'], $ethnicity[9]['ETHNICITY_ID'] => $ethnicity[9]['ETHNICITY_NAME'], $ethnicity[10]['ETHNICITY_ID'] => $ethnicity[10]['ETHNICITY_NAME'], $ethnicity[11]['ETHNICITY_ID'] => $ethnicity[11]['ETHNICITY_NAME']);
    $language = DBGet(DBQuery('SELECT * FROM language'));
    $language_option = array($language[1]['LANGUAGE_ID'] => $language[1]['LANGUAGE_NAME'], $language[2]['LANGUAGE_ID'] => $language[2]['LANGUAGE_NAME'], $language[3]['LANGUAGE_ID'] => $language[3]['LANGUAGE_NAME'], $language[4]['LANGUAGE_ID'] => $language[4]['LANGUAGE_NAME'], $language[5]['LANGUAGE_ID'] => $language[5]['LANGUAGE_NAME'], $language[6]['LANGUAGE_ID'] => $language[6]['LANGUAGE_NAME'], $language[7]['LANGUAGE_ID'] => $language[7]['LANGUAGE_NAME'], $language[8]['LANGUAGE_ID'] => $language[8]['LANGUAGE_NAME'], $language[9]['LANGUAGE_ID'] => $language[9]['LANGUAGE_NAME'], $language[10]['LANGUAGE_ID'] => $language[10]['LANGUAGE_NAME'], $language[11]['LANGUAGE_ID'] => $language[11]['LANGUAGE_NAME'], $language[12]['LANGUAGE_ID'] => $language[12]['LANGUAGE_NAME'], $language[13]['LANGUAGE_ID'] => $language[13]['LANGUAGE_NAME'], $language[14]['LANGUAGE_ID'] => $language[14]['LANGUAGE_NAME'], $language[15]['LANGUAGE_ID'] => $language[15]['LANGUAGE_NAME'], $language[16]['LANGUAGE_ID'] => $language[16]['LANGUAGE_NAME'], $language[17]['LANGUAGE_ID'] => $language[17]['LANGUAGE_NAME'], $language[18]['LANGUAGE_ID'] => $language[18]['LANGUAGE_NAME'], $language[19]['LANGUAGE_ID'] => $language[19]['LANGUAGE_NAME'], $language[20]['LANGUAGE_ID'] => $language[20]['LANGUAGE_NAME']);

    echo '<div style="height:10px;"></div>';
    echo '<input type=hidden name=sql_save_session value=true />';


    echo '<div id="searchdiv1" style="display:none;" class="well">';
    echo '<div><a href="javascript:void(0);" class="text-pink" onclick="hide_search_div1();"><i class="icon-cancel-square"></i> '._closeAdvancedFilter.'</a></div>';
    echo '<br/>';

    echo '<div class="row">';
    echo '<div class="col-md-6">';
    if ($_REQUEST['mp_comment'] != '')
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._comments.' </label><div class="col-lg-8"><input type="text" id="mp_comment" name="mp_comment" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._comments.'" value="' . $_REQUEST['mp_comment'] . '"/></div></div>';
    else
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._comments.' </label><div class="col-lg-8"><input type=text id="mp_comment" name="mp_comment" size=30 placeholder="'._comments.'" class="form-control"></div></div>';
    echo '</div>'; //.col-md-6
    echo '</div>'; //.row

    Search('student_fields');

    // echo "<pre>";print_r($_REQUEST);echo "</pre>";

    if(isset($_REQUEST['cust']) && !empty($_REQUEST['cust']))
    {
        foreach($_REQUEST['cust'] as $single_cfk => $single_cfv)
        {
            $keep_elem = '';

            $keep_elem = 'cust['.$single_cfk.']';

            echo '<script>document.getElementsByName("'.$keep_elem.'")[0].value = "'.$single_cfv.'";</script>';
        }
    }


    echo '<h5 class="text-primary">'._generalInformation.'</h5>';

    echo '<div class="row">';
    echo '<div class="col-md-6">';
    $G = array('Male' => 'Male', 'Female' => 'Female');
    if ($_REQUEST['GENDER'] != '') {
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._gender.' </label><div class="col-lg-8"><select id="GENDER" name="GENDER" class="form-control"><option value="">-- Select --</option>';
        foreach ($G as $value)
            echo '<option value="' . $value . '" ' . ($value == $_REQUEST['GENDER'] ? 'selected' : '') . '>' . $value . '</option>';
        echo '</select></div></div>';
    } else {
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._gender.' </label><div class="col-lg-8"><select id="GENDER" name="GENDER" class="form-control"><option value="">-- Select --</option>';
        foreach ($G as $value)
            echo '<option value="' . $value . '">' . $value . '</option>';
        echo '</select></div></div>';
    }
    echo '</div>'; //.col-md-6

    echo '<div class="col-md-6">';
    if ($_REQUEST['ETHNICITY_ID'] != '') {
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._ethnicity.' </label><div class="col-lg-8"><select id="ETHNICITY_ID" name="ETHNICITY_ID" class="form-control"><option value="">-- Select --</option>';
        foreach ($ethnic_option as $value)
            echo '<option value="' . $value . '" ' . ($value == $_REQUEST['ETHNICITY_ID'] ? 'selected' : '') . '>' . $value . '</option>';
        echo '</select></div></div>';
    } else {
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._ethnicity.' </label><div class="col-lg-8"><select id="ETHNICITY_ID" name="ETHNICITY_ID" class="form-control"><option value="">-- Select --</option>';
        foreach ($ethnic_option as $value)
            echo '<option value="' . $value . '">' . $value . '</option>';
        echo '</select></div></div>';
    }
    echo '</div>'; //.col-md-6

    echo '<div class="col-md-6">';
    if ($_REQUEST['LANGUAGE_ID'] != '') {
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._language.' </label><div class="col-lg-8"><select id="LANGUAGE_ID" name="LANGUAGE_ID" class="form-control"><option value="">-- Select --</option>';

        foreach ($language_option as $value)
            echo '<option value="' . $value . '" ' . ($value == $_REQUEST['LANGUAGE_ID'] ? 'selected' : '') . '>' . $value . '</option>';
        echo '</select></div></div>';
    } else {
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._language.' </label><div class="col-lg-8"><select id="LANGUAGE_ID" name="LANGUAGE_ID" class="form-control"><option value="">-- Select --</option>';

        foreach ($language_option as $value)
            echo '<option value="' . $value . '" >' . $value . '</option>';
        echo '</select></div></div>';
    }
    echo '</div>'; //.col-md-6
    echo '</div>';


    echo '<h5 class="text-primary">'._birthdaySearch.'</h5>';

    echo '<div class="row">';
    echo '<div class="col-md-6">';
    echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._from.': </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInputDob('day_from_birthdate', 'month_from_birthdate', '', 'Y', 'Y', '') . '</div></div></div></div>';
    echo '</div><div class="col-md-6">';
    echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._to.': </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInputDob('day_to_birthdate', 'month_to_birthdate', '', 'Y', 'Y', '') . '</div></div></div></div>';
    echo '</div>'; //.col-md-6
    echo '</div>'; //.row


    echo '<h5 class="text-primary">'._dob.'</h5>';

    echo '<div class="row">';
    echo '<div class="col-md-6">';
    echo '<div class="form-group"><label class="control-label col-lg-4 text-right"> </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('day_dob_birthdate', 'month_dob_birthdate', 'year_dob_birthdate', 'Y', 'Y', 'Y') . '</div></div></div></div>';
    echo '</div><div class="col-md-6">';

    echo '</div>'; //.col-md-6
    echo '</div>'; //.row


    echo '<h5 class="text-primary">'._goalAndProgress.'</h5>';

    echo '<div class="row">';
    echo '<div class="col-md-6">';
    if ($_REQUEST['goal_title'] != '')
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._goalTitle.' </label><div class="col-lg-8"><input type="text" id="goal_title" name="goal_title" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._goalTitle.'" value="' . $_REQUEST['goal_title'] . '"/></div></div>';
    else
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._goalTitle.' </label><div class="col-lg-8"><input type=text id="goal_title" name="goal_title" placeholder="'._goalTitle.'" size=30 class="form-control"></div></div>';
    echo '</div><div class="col-md-6">';
    if ($_REQUEST['goal_description'] != '')
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._goalDescription.' </label><div class="col-lg-8"><input type="text" id="goal_description" name="goal_description" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._goalDescription.'" value="' . $_REQUEST['goal_description'] . '"/></div></div>';
    else
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._goalDescription.' </label><div class="col-lg-8"><input type=text id="goal_description" name="goal_description" placeholder="'._goalDescription.'" size=30 class="form-control"></div></div>';
    echo '</div>'; //.col-md-6
    echo '</div>'; //.row

    echo '<div class="row">';
    echo '<div class="col-md-6">';
    if ($_REQUEST['progress_name'] != '')
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._progressPeriod.' </label><div class="col-lg-8"><input type="text" id="progress_name" name="progress_name" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._progressPeriod.'" value="' . $_REQUEST['progress_name'] . '"/></div></div>';
    else
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._progressPeriod.' </label><div class="col-lg-8"><input type=text id="progress_name" name="progress_name" placeholder="'._progressPeriod.'" size=30 class="form-control"></div></div>';
    echo '</div><div class="col-md-6">';
    if ($_REQUEST['progress_description'] != '')
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._progressAssessment.' </label><div class="col-lg-8"><input type="text" id="progress_description" name="progress_description" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._progressAssessment.'" value="' . $_REQUEST['progress_description'] . '"/></div></div>';
    else
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._progressAssessment.' </label><div class="col-lg-8"><input type=text id="progress_description" name="progress_description" size=30 placeholder="'._progressAssessment.'" class="form-control"></div></div>';
    echo '</div>'; //.col-md-6
    echo '</div>'; //.row


    echo '<h5 class="text-primary">'._medical.'</h5>';

    echo '<div class="row">';
    echo '<div class="col-md-6">';
    echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._date.'</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('med_day', 'med_month', 'med_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
    echo '</div><div class="col-md-6">';
    if ($_REQUEST['doctors_note_comments'] != '')
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._doctorSNote.' </label><div class="col-lg-8"><input type="text" id="doctors_note_comments" name="doctors_note_comments" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._doctorSNote.'" value="' . $_REQUEST['doctors_note_comments'] . '"/></div></div>';
    else
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._doctorSNote.'</label><div class="col-lg-8"><input type=text id="doctors_note_comments" name="doctors_note_comments" placeholder="'._doctorSNote.'" size=30 class="form-control"></div></div>';
    echo '</div>'; //.col-md-6
    echo '</div>'; //.row


    echo '<h5 class="text-primary">'._immunization.'</h5>';

    echo '<div class="row">';
    echo '<div class="col-md-6">';
    if ($_REQUEST['type'] != '')
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._type.' </label><div class="col-lg-8"><input type="text" id="type" name="type" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._immunizationType.'" value="' . $_REQUEST['type'] . '"/></div></div>';
    else
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._type.'</label><div class="col-lg-8"><input type=text id="type" name="type" placeholder="'._immunizationType.'" size=30 class="form-control"></div></div>';
    echo '</div><div class="col-md-6">';
    echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._date.'</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('imm_day', 'imm_month', 'imm_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
    echo '</div>'; //.col-md-6
    echo '</div>'; //.row

    echo '<div class="row">';
    echo '<div class="col-md-6">';
    if ($_REQUEST['imm_comments'] != '')
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._comments.' </label><div class="col-lg-8"><input type="text" id="imm_comments" name="imm_comments" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._immunizationComments.'" value="' . $_REQUEST['imm_comments'] . '"/></div></div>';
    else
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._comments.'</label><div class="col-lg-8"><input type=text id="imm_comments" name="imm_comments" placeholder="'._immunizationComments.'" size=30 class="form-control"></div></div>';
    echo '</div>'; //.col-md-6
    echo '</div>'; //.row


    echo '<h5 class="text-primary">'._medicalAlert.'</h5>';

    echo '<div class="row">';
    echo '<div class="col-md-6">';
    echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._date.'</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('ma_day', 'ma_month', 'ma_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
    echo '</div><div class="col-md-6">';
    if ($_REQUEST['med_alrt_title'] != '')
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._alert.' </label><div class="col-lg-8"><input type="text" id="med_alrt_title" name="med_alrt_title" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._medicalAlert.'" value="' . $_REQUEST['med_alrt_title'] . '"/></div></div>';
    else
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._alert.'</label><div class="col-lg-8"><input type=text id="med_alrt_title" name="med_alrt_title" placeholder="'._medicalAlert.'" size=30 class="form-control"></div></div>';
    echo '</div>'; //.col-md-6
    echo '</div>'; //.row


    echo '<h5 class="text-primary">'._nurseVisit.'</h5>';

    echo '<div class="row">';
    echo '<div class="col-md-6">';
    echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._date.'</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('nv_day', 'nv_month', 'nv_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
    echo '</div><div class="col-md-6">';
    if ($_REQUEST['reason'] != '')
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._reason.' </label><div class="col-lg-8"><input type="text" id="reason" name="reason" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._nurseVisitReason.'" value="' . $_REQUEST['reason'] . '"/></div></div>';
    else
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._reason.'</label><div class="col-lg-8"><input type=text id="reason" name="reason" size=30 placeholder="'._nurseVisitReason.'" class="form-control"></div></div>';
    echo '</div>'; //.col-md-6
    echo '</div>'; //.row

    echo '<div class="row">';
    echo '<div class="col-md-6">';
    if ($_REQUEST['result'] != '')
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._result.' </label><div class="col-lg-8"><input type="text" id="result" name="result" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._nurseVisitResult.'" value="' . $_REQUEST['result'] . '"/></div></div>';
    else
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._result.'</label><div class="col-lg-8"><input type=text id="result" name="result" size=30 placeholder="'._nurseVisitResult.'" class="form-control"></div></div>';
    echo '</div><div class="col-md-6">';
    if ($_REQUEST['med_vist_comments'] != '')
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._comments.' </label><div class="col-lg-8"><input type="text" id="med_vist_comments" name="med_vist_comments" class="form-control p-t-0 p-b-0 input-xs" placeholder="'._nurseVisitComments.'" value="' . $_REQUEST['med_vist_comments'] . '"/></div></div>';
    else
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._comments.'</label><div class="col-lg-8"><input type=text id="med_vist_comments" name="med_vist_comments" placeholder="'._nurseVisitComments.'" size=30 class="form-control"></div></div>';
    echo '</div>'; //.col-md-6
    echo '</div>'; //.row

    echo '</div>';



    # ---   Advanced Filter End ----------------------------------------------------------- #
    echo  '<input type="hidden" id="last_hidden" name="last"/>';
    echo  '<input type="hidden" id="first_hidden" name="first"/>';
    echo  '<input type="hidden" id="stuid_hidden" name="stuid"/>';
    echo  '<input type="hidden" id="altid_hidden" name="altid"/>';
    echo  '<input type="hidden" id="addr_hidden" name="addr"/>';
    echo  '<input type="hidden" id="grade_hidden" name="grade"/>';
    echo  '<input type="hidden" id="section_hidden" name="section"/>';

    echo  '<input type="hidden" id="GENDER_hidden" name="GENDER"/>';
    echo  '<input type="hidden" id="ETHNICITY_ID_hidden" name="ETHNICITY_ID"/>';
    echo  '<input type="hidden" id="LANGUAGE_ID_hidden" name="LANGUAGE_ID"/>';

    echo '<div id="address_group_hidden"></div>';
    echo '<div id="_search_all_schools_hidden"></div>';
    echo '<div id="include_inactive_hidden"></div>';

    echo  '<input type="hidden" id="mp_comment_hidden" name="mp_comment"/>';
    echo  '<input type="hidden" id="goal_title_hidden" name="goal_title"/>';
    echo  '<input type="hidden" id="goal_description_hidden" name="goal_description"/>';
    echo  '<input type="hidden" id="progress_name_hidden" name="progress_name"/>';
    echo  '<input type="hidden" id="progress_description_hidden" name="progress_description"/>';
    echo  '<input type="hidden" id="doctors_note_comments_hidden" name="doctors_note_comments"/>';
    echo  '<input type="hidden" id="type_hidden" name="type"/>';
    echo  '<input type="hidden" id="imm_comments_hidden" name="imm_comments"/>';
    echo  '<input type="hidden" id="med_alrt_title_hidden" name="med_alrt_title"/>';
    echo  '<input type="hidden" id="reason_hidden" name="reason"/>';
    echo  '<input type="hidden" id="result_hidden" name="result"/>';
    echo  '<input type="hidden" id="med_vist_comments_hidden" name="med_vist_comments"/>';
    echo  '<input type="hidden" id="other_filter_names" value="'.$other_filter_list.'"/>';


    $searchable_fields = DBGet(DBQuery("SELECT CONCAT('CUSTOM_',cf.ID) AS COLUMN_NAME,cf.TYPE,cf.TITLE,cf.SELECT_OPTIONS FROM program_user_config puc,custom_fields cf WHERE puc.TITLE=cf.ID AND puc.PROGRAM='StudentFieldsSearchable' AND puc.USER_ID='" . User('STAFF_ID') . "' AND puc.VALUE='Y' ORDER BY cf.SORT_ORDER,cf.TITLE"));

    $each_custom_fields = '';

    foreach($searchable_fields as $one_searchable)
    {
        if($one_searchable['TYPE'] != 'textarea')
        {
            echo '<input type="hidden" id="custom_'.$one_searchable['COLUMN_NAME'].'_hidden" name="cust['.$one_searchable['COLUMN_NAME'].']"/>';

            if($each_custom_fields == '')
            {
                $each_custom_fields .= $one_searchable['COLUMN_NAME'];
            }
            else
            {
                $each_custom_fields .= ', '.$one_searchable['COLUMN_NAME'];
            }
        }
    }

    echo '<input id="each_custom_fields_ids" name="each_custom_fields_ids" type="hidden" value="'.$each_custom_fields.'">';


    echo '<div class="panel-footer p-l-15 p-r-15">';

    echo '<div class="row">';
    echo '<div class="col-sm-6 col-md-6 col-lg-6">';

    echo '<a id="advancedSearchDivForStudentsFilters" href="javascript:void(0);" class="text-pink btn-block m-t-10" onclick="show_search_div1();">  &nbsp;<i class="icon-cog"></i> '._advancedFilter.'</a>';
    echo '</div>'; //.col-sm-6

    echo '<div class="col-sm-6 col-md-6 col-lg-6 text-lg-right text-md-right text-sm-right">';
    
    
    echo '<button id="saveFilterBtn" class="btn btn-primary display-inline-block" onClick="setFilterValues();">'._saveFilter.'</button>';

    echo '</div>'; //.col-sm-6
    echo '</div>'; //.row

    echo '</div>'; //.panel-footer
    echo '</div>'; //.panel
    echo '</form>';

    // form for filter student ends
}
