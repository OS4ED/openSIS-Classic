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
if(isset($_SESSION['assoc_err']) && $_SESSION['assoc_err']=='Y'){
    echo'<div class="alert alert-danger alert-bordered"><i class="icon-alert"></i>This custom field cannot be deleted because there is data associated with this field in the database.</div>';
    unset($_SESSION['assoc_err']);
}

echo '<div class="row">';
if (clean_param($_REQUEST['tables'], PARAM_NOTAGS) && ($_POST['tables'] || $_REQUEST['ajax'])) {

    // print_r($_REQUEST);
    if($_REQUEST['SYSTEM_WIDE']=='Y')
        $_REQUEST['tables'][$_REQUEST['custom']]['SCHOOL_ID']=0;
    else
        $_REQUEST['tables'][$_REQUEST['custom']]['SCHOOL_ID']= UserSchool();
    unset($_REQUEST['SYSTEM_WIDE']);
    // echo '<br><br>';
    // print_r($_REQUEST);
    
    $table = $_REQUEST['table'];
    foreach ($_REQUEST['tables'] as $id => $columns) {
        $flag = 0;
        if ($id != 'new') {
            if ($columns['CATEGORY_ID'] && $columns['CATEGORY_ID'] != $_REQUEST['category_id'])
                $_REQUEST['category_id'] = $columns['CATEGORY_ID'];

            $sql = "UPDATE $table SET ";
            $sort_oder_to_change=0;
            foreach ($columns as $column => $value) {
                $value = paramlib_validation($column, $value);
                $sql .= $column . "='" . str_replace("\'", "''", trim($value)) . "',";
                if($column=='SORT_ORDER' && $value!='') 
                $sort_oder_to_change=$value;
            }
            $sql = substr($sql, 0, -1) . " WHERE ID='$id'";
            $go = true;
            if ($table == 'school_custom_fields')
                $custom_field_id = $id;

            // print_r($_REQUEST);exit;
            if ($custom_field_id) {

                $chk_cus_data = DBGet(DBQuery('SELECT * from schools WHERE SYEAR=' . UserSyear() . ' AND id=' . UserSchool()));
                if ($chk_cus_data[1]['CUSTOM_' . $custom_field_id] != '') {
                    $flag = 1;
                }
                $custom_update = DBGet(DBQuery("SELECT TYPE,REQUIRED,DEFAULT_SELECTION,HIDE FROM school_custom_fields WHERE ID=$custom_field_id"));
                $custom_update = $custom_update[1];
                switch ($custom_update['TYPE']) {
                    case 'radio':
                        $Sql_modify_column = "ALTER TABLE schools MODIFY CUSTOM_$id VARCHAR(1) ";
                        break;

                    case 'text':
                        $Sql_modify_column = "ALTER TABLE schools MODIFY CUSTOM_$id VARCHAR(255)";
                        break;

                    case 'select':
                    case 'autos':
                    case 'edits':
                        $Sql_modify_column = "ALTER TABLE schools MODIFY CUSTOM_$id VARCHAR(100)";
                        break;

                    case 'codeds':
                        $Sql_modify_column = "ALTER TABLE schools MODIFY CUSTOM_$id VARCHAR(15)";
                        break;

                    case 'multiple':
                        $Sql_modify_column = "ALTER TABLE schools MODIFY CUSTOM_$id VARCHAR(255)";
                        break;

                    case 'numeric':
                        $Sql_modify_column = "ALTER TABLE schools MODIFY CUSTOM_$id NUMERIC(20,2)";
                        if (!is_numeric($columns['DEFAULT_SELECTION'])) {
                            $not_default = true;
                        }
                        break;

                    case 'date':
                        $Sql_modify_column = "ALTER TABLE schools MODIFY CUSTOM_$id  DATE";
                        if (preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $columns['DEFAULT_SELECTION']) === 0) {
                            $not_default = true;
                        }
                        break;

                    case 'textarea':
                        $Sql_modify_column = "ALTER TABLE schools MODIFY CUSTOM_$id LONGTEXT";
                        $not_default = true;
                        break;
                }
                if (!$custom_update['REQUIRED']) {
                    $Sql_modify_column.=" NOT NULL";
                } else {
                    $Sql_modify_column.=" NULL";
                }
                if ($custom_update['DEFAULT_SELECTION'] && $not_default == false) {
                    $Sql_modify_column.=" DEFAULT  '" . $custom_update['DEFAULT_SELECTION'] . "' ";
                }
                if ($flag == 0) {
                    DBQuery($Sql_modify_column);
                } else{
                    if($sort_oder_to_change!=0){
                        DBQuery('UPDATE school_custom_fields SET SORT_ORDER=\''.$sort_oder_to_change.'\' WHERE ID='.$custom_field_id);
                    }
                    else{
                        $_SESSION['assoc_err']='Y';
                    }
                }
                    // echo'<p style=color:red>This custom field cannot be deleted because there is data associated with this field in the database.</p>';
            }
        }
        else {
            $sql = "INSERT INTO $table ";

            if ($table == 'school_custom_fields') {
                if ($columns['CATEGORY_ID']) {
                    $_REQUEST['category_id'] = $columns['CATEGORY_ID'];
                    unset($columns['CATEGORY_ID']);
                }

                $id = DBGet(DBQuery("SHOW TABLE STATUS LIKE 'school_custom_fields'"));
                $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
                $id = $id[1]['ID'];
                $fields = "CATEGORY_ID,SYSTEM_FIELD,";
                $values = "'" . $_REQUEST['category_id'] . "','N',";
                $_REQUEST['id'] = $id;

                switch ($columns['TYPE']) {
                    case 'radio':
                        $Sql_add_column = "ALTER TABLE schools ADD CUSTOM_$id VARCHAR(1) ";
                        break;

                    case 'text':
                        $Sql_add_column = "ALTER TABLE schools ADD CUSTOM_$id VARCHAR(255)";
                        break;

                    case 'select':
                    case 'autos':
                    case 'edits':
                        $Sql_add_column = "ALTER TABLE schools ADD CUSTOM_$id VARCHAR(100)";
                        break;

                    case 'codeds':
                        $Sql_add_column = "ALTER TABLE schools ADD CUSTOM_$id VARCHAR(15)";
                        break;

                    case 'multiple':
                        $Sql_add_column = "ALTER TABLE schools ADD CUSTOM_$id VARCHAR(255)";
                        break;

                    case 'numeric':
                        $Sql_add_column = "ALTER TABLE schools ADD CUSTOM_$id NUMERIC(20,2)";
                        if (!is_numeric($columns['DEFAULT_SELECTION'])) {
                            $not_default = true;
                            $columns['DEFAULT_SELECTION'] = '';
                        }
                        break;

                    case 'date':
                        $Sql_add_column = "ALTER TABLE schools ADD CUSTOM_$id  DATE";
                        if (preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $columns['DEFAULT_SELECTION']) === 0) {
                            $not_default = true;
                            $columns['DEFAULT_SELECTION'] = '';
                        }
                        break;

                    case 'textarea':
                        $Sql_add_column = "ALTER TABLE schools ADD CUSTOM_$id LONGTEXT";
                        $not_default = true;
                        break;
                }
                if ($columns['REQUIRED']) {
                    $Sql_add_column.=" NOT NULL ";
                } else {
                    $Sql_add_column.=" NULL ";
                }
                if ($columns['DEFAULT_SELECTION'] && $not_default == false) {
                    $Sql_add_column.=" DEFAULT  '" . $columns['DEFAULT_SELECTION'] . "' ";
                }
                DBQuery($Sql_add_column);

                unset($table);
            } elseif ($table == 'student_field_categories') {

                $id = DBGet(DBQuery("SHOW TABLE STATUS LIKE 'student_field_categories'"));
                $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
                $id = $id[1]['ID'];
                $fields = "";
                $values = "";
                $_REQUEST['category_id'] = $id;
                // add to profile or permissions of user creating it
                if (User('PROFILE_ID') != '')
                    DBQuery("INSERT INTO profile_exceptions (PROFILE_ID,MODNAME,CAN_USE,CAN_EDIT) values('" . User('PROFILE_ID') . "','students/Student.php&category_id=$id','Y','Y')");
                else {
                    $profile_id_mod = DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE USER_ID='" . User('STAFF_ID')));
                    $profile_id_mod = $profile_id_mod[1]['PROFILE_ID'];
                    if ($profile_id_mod != '')
                        DBQuery("INSERT INTO profile_exceptions (PROFILE_ID,MODNAME,CAN_USE,CAN_EDIT) values('" . $profile_id_mod . "','students/Student.php&category_id=$id','Y','Y')");
                }
            }

            $go = false;

            foreach ($columns as $column => $value) {
                if (trim($value)) {
                    $value = paramlib_validation($column, $value);
                    $fields .= $column . ',';
                    $values .= "'" . str_replace("\'", "''", $value) . "',";
                    $go = true;
                }
            }
            $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';
        }
//echo $sql;
        if ($go && $flag == 0)
            DBQuery($sql);
    }

    
    echo '<script>window.location.href="Modules.php?modname=schoolsetup/SchoolCustomFields.php"</script>';

    unset($_REQUEST['tables']);
}
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'delete') {
    if (clean_param($_REQUEST['id'], PARAM_INT)) {

        $sc_cus_fld_column = 'CUSTOM_' . $_REQUEST['id'];

        $chk_sch_cus = DBGet(DBQuery('SELECT COUNT(*) AS REC_EX FROM schools WHERE (' . $sc_cus_fld_column . '<>\'\' AND  ' . $sc_cus_fld_column . ' is NOT NULL)'));

        if ($chk_sch_cus[1]['REC_EX'] == 0) {
            if (DeletePromptCommon('school field')) {
                $id = clean_param($_REQUEST['id'], PARAM_INT);
                DBQuery('DELETE FROM school_custom_fields WHERE ID=\'' . $id . '\'');
                DBQuery('ALTER TABLE schools DROP COLUMN CUSTOM_' . $id . '');
                $_REQUEST['modfunc'] = '';
                unset($_REQUEST['id']);
            }
        } else {
            UnableDeletePrompt('This custom field cannot be deleted because there is data associated with this field in the database.');
        }
    }
}

if ($_REQUEST['id'] && $_REQUEST['id'] != 'new') {
    $sql = "SELECT SCHOOL_ID,CATEGORY_ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,SORT_ORDER,REQUIRED,REQUIRED,HIDE FROM school_custom_fields WHERE ID='$_REQUEST[id]'";
    $RET = DBGet(DBQuery($sql));
    $RET = $RET[1];
    $title = $RET['TITLE'];
} elseif ($_REQUEST['id'] == 'new') {
    $title = 'New School Field';
}

if ($_REQUEST['id'] && !$_REQUEST['modfunc']) {

    echo '<div class="col-md-6 col-md-offset-3">';

    if ($_REQUEST['id'] != 'new')
        $delete_button = "<INPUT type=button value=Delete class=\"btn btn-danger pull-right\" onClick='javascript:window.location=\"Modules.php?modname=$_REQUEST[modname]&modfunc=delete&id=$_REQUEST[id]\"'>" . "&nbsp;";
    
    $action = "Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "";
    
    
    if ($_REQUEST['id'] != 'new')
        $action .= "&id=" . strip_tags(trim($_REQUEST[id]));
        $action .= "&table=school_custom_fields";    
    
    echo "<FORM name=SF1 class=\"form-horizontal\" id=SF1 action=".$action." method=POST>";    
    
    $header .= '<div class="panel">';

    $header .= '<div class="tabbable">';
    $header .= '<ul class="nav nav-tabs nav-tabs-bottom no-margin-bottom">';
    $header .= '<li class="active">';
    $header .= '<a href="javascript:void(0);">' . $title . '</a>'; //'<INPUT type=submit value=Save>');
    $header .= '</li>';
    $header .= '</ul>';
    $header .= '</div>';

    $header .= '<div class="panel-body">';
    $header .= '<input type=hidden name=tables[' . $_REQUEST['id'] . '][SCHOOL_ID] value=' . UserSchool() . '>';
    $header .= '<div class="form-group">' . TextInput($RET['TITLE'], 'tables[' . $_REQUEST['id'] . '][TITLE]', 'Field Name') . '</div>';

    // You can't change a student field type after it has been created
    // mab - allow changing between select and autos and edits and text
    echo "<input id=custom name=custom type=hidden value=" . strip_tags(trim($_REQUEST[id])) . " />";
    if ($_REQUEST['id'] != 'new') {
       

        $type_options = array('select' => 'Pull-Down', 'autos' => 'Auto Pull-down', 'edits' => 'Edit Pull-Down', 'text' => 'Text', 'radio' => 'Checkbox', 'codeds' => 'Coded Pull-Down', 'numeric' => 'Number', 'multiple' => 'Select Multiple from Options', 'date' => 'Date', 'textarea' => 'Long Text');
    } else
        $type_options = array('select' => 'Pull-Down', 'autos' => 'Auto Pull-down', 'edits' => 'Edit Pull-Down', 'text' => 'Text', 'radio' => 'Checkbox', 'codeds' => 'Coded Pull-Down', 'numeric' => 'Number', 'multiple' => 'Select Multiple from Options', 'date' => 'Date', 'textarea' => 'Long Text');

    $header .= '<div class="form-group"><label class="control-label col-lg-4 text-right">Data Type</label><div class="col-lg-8">' . SelectInput($RET['TYPE'], 'tables[' . $_REQUEST['id'] . '][TYPE]', '', $type_options, false, 'id=type onchange="formcheck_student_studentField_F1_defalut();"') . '</div></div>';
    if ($_REQUEST['id'] != 'new' && $RET['TYPE'] != 'select' && $RET['TYPE'] != 'codeds' && $RET['TYPE'] != 'autos' && $RET['TYPE'] != 'edits' && $RET['TYPE'] != 'text') {
        $_openSIS['allow_edit'] = $allow_edit;
        $_openSIS['AllowEdit'][$modname] = $AllowEdit;
    }
    foreach ($categories_RET as $type)
        $categories_options[$type['ID']] = $type['TITLE'];

    if ($_REQUEST['id'] == 'new') {
        $header .= '<div class="form-group">' . TextInput($RET['SORT_ORDER'], 'tables[' . $_REQUEST['id'] . '][SORT_ORDER]', 'Sort Order', 'onkeydown="return numberOnly(event);"') . '</div>';
    } else {
        $header .= '<div class="form-group">' . TextInput($RET['SORT_ORDER'], 'tables[' . $_REQUEST['id'] . '][SORT_ORDER]', 'Sort Order', 'onkeydown=\"return numberOnly(event);\"') . '</div>';
    }

    $colspan = 2;
    if ($RET['TYPE'] == 'autos' || $RET['TYPE'] == 'edits' || $RET['TYPE'] == 'select' || $RET['TYPE'] == 'codeds' || $RET['TYPE'] == 'multiple' || $_REQUEST['id'] == 'new') {
        $header .= '<div class="form-group" id="show_textarea" style="display:block">' . TextAreaInput($RET['SELECT_OPTIONS'], 'tables[' . $_REQUEST['id'] . '][SELECT_OPTIONS]', 'Pull-Down/Auto Pull-Down/Coded Pull-Down/Select Multiple Choices one per line', 'rows=7 cols=40') . '</div>';
        $colspan = 1;
    }
    $header .= '<div class="form-group"><label class="control-label col-lg-4 text-right">Default</label><div class="col-lg-8">' . TextInput_mod_a($RET['DEFAULT_SELECTION'], 'tables[' . $_REQUEST['id'] . '][DEFAULT_SELECTION]', '') . '<p class="help-block">** for dates: YYYY-MM-DD, for checkboxes: Y, for long text it will be ignored</p></div></div>';

    $new = ($_REQUEST['id'] == 'new');
    $header .= '<div class="form-group"><div class="col-lg-8 col-md-offset-4">' . CheckboxInputSwitch($RET['REQUIRED'], 'tables[' . $_REQUEST['id'] . '][REQUIRED]', 'Required','', false, 'Yes', 'No', '', 'switch-success') . '</div></div>';
    
    if($RET['SCHOOL_ID']>0)
        $system_wide='N';
    else
         $system_wide='Y';
//    print_r($RET);
//    echo $system_wide;
    $header .= '<div class="form-group"><div class="col-lg-8 col-md-offset-4">' . CheckboxInputSwitch($system_wide, 'SYSTEM_WIDE', 'System Wide','', false, 'Yes','No', '', 'switch-success') . '</div></div>';
   
    $header .= '</div>';
    $header .= '<div class="panel-footer"><div class="heading-elements">' . SubmitButton('Save', '', 'class="btn btn-primary heading-btn pull-right ml-10" onclick="formcheck_schoolfields();"') . $delete_button . '</div></div>';
    $header .= '</div>'; //.panel

    DrawHeaderHome($header);

    echo '</FORM>';
    echo '</div>'; //.col-md-6
} elseif (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) != 'delete') {

    echo '<div class="col-md-6 col-md-offset-3">';
    $count = 0;
    $count++;
    $LO_options = array('save' => false, 'search' => false, 'add' => true);

    $columns = array('TITLE' => 'School Fields','SORT_ORDER' => 'Sort Order', 'TYPE' => 'Field Type');
    $link = array();
    $arr = array('School Name', 'Address', 'City', 'State', 'Zip/Postal Code', 'Principal', 'Base Grading Scale', 'E-Mail', 'Website', 'School Logo');
    $RET = DBGet(DBQuery("SELECT * FROM school_custom_fields WHERE SCHOOL_ID IN (" . UserSchool() .",0) ORDER BY SORT_ORDER"));
    foreach ($arr as $key => $value) {
        $fields_RET1[$count] = array('ID' => '', 'TITLE' => $value, 'TYPE' => '<span style="color:#ea8828;">Default</span>');
        $count++;
    }
    $count2 = 1;
    foreach ($fields_RET1 as $key_index=>$key2) {
        $dd[$count2] = $key2;
        $dd[$count2]['SORT_ORDER'] = $key_index;
        $count2++;
    }
    foreach ($RET as $row) {
        if ($row['TYPE'] = 'Custom')
            $dd[$count2] = $row;
        $count2++;
    }
    $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]";
    $link['add']['link'] = "#" . " onclick='check_content(\"Ajax.php?modname=$_REQUEST[modname]&id=new\");'";
    $link['TITLE']['variables'] = array('id' => 'ID');

    echo '<div class="panel panel-white">';
    ListOutput($dd, $columns, 'School Field', 'School Fields', $link, array(), $LO_options);
    echo '</div>'; //.panel

    echo '</div>'; //.col-md-6
}
echo '</div>'; //.row
?>