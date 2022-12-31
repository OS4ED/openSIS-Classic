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

echo '<input id="customFieldModule" type="hidden" value="school">';

if(isset($_SESSION['assoc_err']) && $_SESSION['assoc_err']=='Y'){
    echo'<div class="alert alert-danger alert-bordered"><i class="icon-alert"></i>'._thisCustomFieldCannotBeDeletedBecauseThereIsDataAssociatedWithThisFieldInTheDatabase.'.</div>';
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

            if ($_REQUEST['DEFAULT_DATATYPE_'.$id] == 'multiple' && $columns['DEFAULT_SELECTION'] != '') {
                $columns['DEFAULT_SELECTION'] = '||'.$columns['DEFAULT_SELECTION'].'||';
            }

            if ($_REQUEST['DEFAULT_DATATYPE_'.$id] == 'numeric' && $columns['REQUIRED'] == 'Y' && ($columns['DEFAULT_SELECTION'] == NULL || $columns['DEFAULT_SELECTION'] == '')) {
                $columns['DEFAULT_SELECTION'] = '0.00';
            }
            
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

                if ($go && $flag == 0)
                    DBQuery($sql);

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
                        $colLength = typeLength($custom_update['DEFAULT_SELECTION'], 15);

                        $Sql_modify_column = "ALTER TABLE schools MODIFY CUSTOM_$id VARCHAR(".$colLength.")";
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
                        // $not_default = true;
                        break;
                }
                if (!$custom_update['REQUIRED']) {
                    $Sql_modify_column.=" NOT NULL";
                } else {
                    $Sql_modify_column.=" NULL";
                }
                
                if ($custom_update['DEFAULT_SELECTION'] && $not_default == false) {
                    if ($custom_update['TYPE'] == 'multiple')
                        $Sql_modify_column.=" DEFAULT  '||" . $custom_update['DEFAULT_SELECTION'] . "||' ";
                    else
                        $Sql_modify_column.=" DEFAULT  '" . $custom_update['DEFAULT_SELECTION'] . "' ";
                    
                    $existing_column_updt = 'UPDATE `schools` SET CUSTOM_'.$id.' = \''.$custom_update['DEFAULT_SELECTION'].'\' WHERE (CUSTOM_'.$id.' IS NULL OR CUSTOM_'.$id.' = "")';
                }

                if ($flag == 0) {
                    DBQuery($Sql_modify_column);
                    
                    if($existing_column_updt)
                        DBQuery($existing_column_updt);
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

            $go = false;
            $fields = "CATEGORY_ID,SYSTEM_FIELD,";
            $values = "'" . $_REQUEST['category_id'] . "','N',";
            foreach ($columns as $column => $value) {
                if (trim($value)) {
                    $value = paramlib_validation($column, $value);
                    $fields .= $column . ',';
                    $values .= "'" . str_replace("\'", "''", $value) . "',";
                    $go = true;
                }
            }
            $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';
            if ($go && $flag == 0){
                DBQuery($sql);
                $id = mysqli_insert_id($connection);
            }
            if ($table == 'school_custom_fields') {
                if ($columns['CATEGORY_ID']) {
                    $_REQUEST['category_id'] = $columns['CATEGORY_ID'];
                    unset($columns['CATEGORY_ID']);
                }

                if (isset($columns['TYPE']) && isset($columns['REQUIRED'])) {
                    if ($columns['TYPE'] == 'numeric' && $columns['REQUIRED'] == 'Y' && ($columns['DEFAULT_SELECTION'] == NULL || $columns['DEFAULT_SELECTION'] == '')) {
                        $columns['DEFAULT_SELECTION'] = '0.00';
                    }
                }

                if ($columns['TYPE'] == 'multiple') {
                    $columns['DEFAULT_SELECTION'] = '||'.$columns['DEFAULT_SELECTION'].'||';
                }

                // $id = DBGet(DBQuery("SHOW TABLE STATUS LIKE 'school_custom_fields'"));
                // $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
                // $id = $id[1]['ID'];
                // $fields = "CATEGORY_ID,SYSTEM_FIELD,";
                // $values = "'" . $_REQUEST['category_id'] . "','N',";
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

                // $id = DBGet(DBQuery("SHOW TABLE STATUS LIKE 'student_field_categories'"));
                // $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
                // $id = $id[1]['ID'];
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

            // $go = false;

            // foreach ($columns as $column => $value) {
            //     if (trim($value)) {
            //         $value = paramlib_validation($column, $value);
            //         $fields .= $column . ',';
            //         $values .= "'" . str_replace("\'", "''", $value) . "',";
            //         $go = true;
            //     }
            // }
            // $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';

            // if ($go && $flag == 0)
            //     DBQuery($sql);
        }
        // echo $sql;
        // if ($go && $flag == 0)
        //     DBQuery($sql);
    }
    
    // echo '<script>window.location.href="Modules.php?modname=schoolsetup/SchoolCustomFields.php"</script>';

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
            UnableDeletePrompt( _thisCustomFieldCannotBeDeletedBecauseThereIsDataAssociatedWithThisFieldInTheDatabase.'.');
        }
    }
}

if ($_REQUEST['id'] && $_REQUEST['id'] != 'new') {
    $sql = "SELECT SCHOOL_ID,CATEGORY_ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,SORT_ORDER,REQUIRED,REQUIRED,HIDE FROM school_custom_fields WHERE ID='".$_REQUEST['id']."' ";
    $RET = DBGet(DBQuery($sql));
    $RET = $RET[1];
    $title = $RET['TITLE'];
} elseif ($_REQUEST['id'] == 'new') {
    $title = _newSchoolField;
}

if ($_REQUEST['id'] && !$_REQUEST['modfunc']) {

    echo '<div class="col-md-8 col-md-offset-2">';

    if ($_REQUEST['id'] != 'new')
        $delete_button = "<INPUT type=button value='"._delete."' class=\"btn btn-danger pull-right\" onClick='javascript:window.location=\"Modules.php?modname=$_REQUEST[modname]&modfunc=delete&id=$_REQUEST[id]\"'>" . "&nbsp;";
    
    $action = "Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "";
    
    
    if ($_REQUEST['id'] != 'new')
        $action .= "&id=" . strip_tags(trim($_REQUEST['id']));
        $action .= "&table=school_custom_fields";    
    
    echo "<FORM name=SF1 class=\"form-horizontal\" id=SF1 action=".$action." method=POST>";    
    
    $header .= '<div class="panel">';

    $header .= '<div class="tabbable">';
    $header .= '<ul class="nav nav-tabs nav-tabs-bottom no-margin-bottom">';
    $header .= '<li class="active">';
    $header .= '<a href="javascript:void(0);">' . $title . '</a>'; //'<INPUT type=submit value='._save.'>');
    $header .= '</li>';
    $header .= '<li class="pull-right">';
    $header .= '<a href="Modules.php?modname=schoolsetup/SchoolCustomFields.php"><i class="icon-square-left m-r-5"></i> ' . _backToSchoolCustomFields . '</a>';
    $header .= '</li>';
    $header .= '</ul>';
    $header .= '</div>';

    $header .= '<div class="panel-body">';
    $header .= '<input type=hidden name=tables[' . $_REQUEST['id'] . '][SCHOOL_ID] value=' . UserSchool() . '>';
    $header .= '<div class="form-group">' . TextInput($RET['TITLE'], 'tables[' . $_REQUEST['id'] . '][TITLE]', ''._fieldName.'') . '</div>';

    // You can't change a student field type after it has been created
    // mab - allow changing between select and autos and edits and text
    
    echo "<input id=custom name=custom type=hidden value=" . strip_tags(trim($_REQUEST['id'])) . " />";
    
    if ($_REQUEST['id'] != 'new') {
        $type_options = array(
            'select' => _pullDown,
            'autos' => _autoPullDown,
            'edits' => _editPullDown,
            'text' => _text,
            'radio' => _checkbox,
            'codeds' => _codedPullDown,
            'numeric' => _number,
            'multiple' => _selectMultipleFromOptions,
            'date' => _date,
            'textarea' => _longText,
        );
    } else
        $type_options = array(
            'select' => _pullDown,
            'autos' => _autoPullDown,
            'edits' => _editPullDown,
            'text' => _text,
            'radio' => _checkbox,
            'codeds' => _codedPullDown,
            'numeric' => _number,
            'multiple' => _selectMultipleFromOptions,
            'date' => _date,
            'textarea' => _longText,
        );

    $header .= '<div class="form-group"><label class="control-label col-lg-4 text-right">'._dataType.'<br>(' . ($_REQUEST['id'] != 'new' ? _thisValueCantBeChanged : _enterThisValueCarefullyAsThisCantBeChangedLater) . ')</label><div class="col-lg-8">' . SelectInput($RET['TYPE'], 'tables[' . $_REQUEST['id'] . '][TYPE]', '', $type_options, false, 'id=type onchange="formcheck_student_studentField_F1_defalut();" ' . ($_REQUEST['id'] != 'new' ? 'disabled' : '')) . '</div></div>';

    $header .= '<input id="DEFAULT_DATATYPE_'.$_REQUEST['id'].'" type="hidden" value="'.$RET['TYPE'].'">';

    if ($_REQUEST['id'] != 'new' && $RET['TYPE'] != 'select' && $RET['TYPE'] != 'codeds' && $RET['TYPE'] != 'autos' && $RET['TYPE'] != 'edits' && $RET['TYPE'] != 'multiple' && $RET['TYPE'] != 'text' && $RET['TYPE'] != 'date' && $RET['TYPE'] != 'radio' && $RET['TYPE'] != 'numeric' && $RET['TYPE'] != 'textarea') {
        $_openSIS['allow_edit'] = $allow_edit;
        $_openSIS['AllowEdit'][$modname] = $AllowEdit;
    }
    foreach ($categories_RET as $type)
        $categories_options[$type['ID']] = $type['TITLE'];

    if ($_REQUEST['id'] == 'new') {
        $header .= '<div class="form-group">' . TextInput($RET['SORT_ORDER'], 'tables[' . $_REQUEST['id'] . '][SORT_ORDER]', ''._sortOrder.'', 'onkeydown="return numberOnly(event);"') . '</div>';
    } else {
        $header .= '<div class="form-group">' . TextInput($RET['SORT_ORDER'], 'tables[' . $_REQUEST['id'] . '][SORT_ORDER]', ''._sortOrder.'', 'onkeydown=\"return numberOnly(event);\"') . '</div>';
    }

    $defaultMessage = _default;
    $exampleText = '';
    if ($RET['TYPE'] == 'multiple') {
        $RET['DEFAULT_SELECTION'] = str_replace('"', '&rdquo;', str_replace('||', ', ', substr($RET['DEFAULT_SELECTION'], 2, -2)));
    }
    if ($_REQUEST['id'] != 'new') {
        if ($RET['TYPE'] == 'autos' || $RET['TYPE'] == 'edits' || $RET['TYPE'] == 'select' || $RET['TYPE'] == 'multiple') {
            $exampleText = _example.':<br/>Good<br/>Bad<br/>etc.';
        } else if ($RET['TYPE'] == 'codeds') {
            $exampleText = _example.':<br/>0|Good<br/>1|Bad<br/>etc.';
        } 
        // else if ($RET['TYPE'] == 'multiple') {
        //     $exampleText = _example':<br/>||Good||<br/>||Bad||<br/>etc.';
        // }

        if (trim($RET['SELECT_OPTIONS']) != '') {
            if ($RET['TYPE'] == 'autos' || $RET['TYPE'] == 'edits' || $RET['TYPE'] == 'select' || $RET['TYPE'] == 'multiple') {
                $selectOptionsArr = explode(PHP_EOL, $RET['SELECT_OPTIONS']);
            } else if ($RET['TYPE'] == 'codeds') {
                $selectOptionsArrP1 = explode(PHP_EOL, $RET['SELECT_OPTIONS']);
                $selectOptionsArrP2 = array();
                
                foreach ($selectOptionsArrP1 as $sOA1) {
                    $sOA1_v = explode('|', $sOA1)[0];
                    array_push($selectOptionsArrP2, $sOA1_v);
                }

                $selectOptionsArr = $selectOptionsArrP2;
            }

            $filteredSelectOptArr = array();

            foreach ($selectOptionsArr as $sOA2) {
                array_push($filteredSelectOptArr, trim($sOA2));
            }

            if ($RET['DEFAULT_SELECTION'] != NULL && $RET['DEFAULT_SELECTION'] != '') {
                if (!in_array(trim($RET['DEFAULT_SELECTION']), $filteredSelectOptArr)) {
                    $defaultMessage = '<span class="text-warning"><b>'._warning.'!</b> '._defaultValueDoesNotMatchWithTheValuesOfPullDown.'!</span>';
                }
            }
        }
    } else {
        $exampleText = _example.':<br/>Good<br/>Bad<br/>etc.';
    }

    $colspan = 2;
    if ($RET['TYPE'] == 'autos' || $RET['TYPE'] == 'edits' || $RET['TYPE'] == 'select' || $RET['TYPE'] == 'codeds' || $RET['TYPE'] == 'multiple' || $_REQUEST['id'] == 'new') {
        $header .= '<div class="form-group" id="show_textarea" style="display:block"><label class="control-label col-lg-4 text-right">'._pullDownAutoPullDownCodedPullDownSelectMultipleChoicesOnePerLine.'</label><div class="col-lg-8">' . TextAreaInput($RET['SELECT_OPTIONS'], 'tables[' . $_REQUEST['id'] . '][SELECT_OPTIONS]', '', 'rows=7 cols=40 onkeyup=checkValidDefaultValue()') . '<p class="help-block">* '. ucfirst(_onePerLine) .'</p><p id="exmp" class="help-block">' . $exampleText . '</p></div></div>';
        $colspan = 1;

        $header .= '<div style="display:none;"><textarea id="SELECT_OPTIONS_VALUE_'.$_REQUEST['id'].'">'.$RET['SELECT_OPTIONS'].'</textarea></div>';
    }

    if ($RET['TYPE'] == 'numeric')
        $defaultValueFunc = 'onkeydown="return numberOnly(event);"';
    else
        $defaultValueFunc = 'onkeyup=checkValidDefaultValue()';
    
    if($RET['DEFAULT_SELECTION'] == NULL || $RET['DEFAULT_SELECTION'] == '') {
        $header .= '<div class="form-group"><label class="control-label col-lg-4 text-right">'._default.'<br>('._enterThisValueCarefullyAsThisCantBeChangedLater.')</label><div class="col-lg-8">' . TextInput_mod_a($RET['DEFAULT_SELECTION'], 'tables[' . $_REQUEST['id'] . '][DEFAULT_SELECTION]', '', $defaultValueFunc) . '<p id="helpBlock" class="help-block">'._default.'</p></div></div>';
    } else {
        $header .= '<div class="form-group"><label class="control-label col-lg-4 text-right">'._default.'<br>('._thisValueCantBeChanged.')</label><div class="col-lg-8">' . TextInput_mod_a($RET['DEFAULT_SELECTION'], 'tables[' . $_REQUEST['id'] . '][DEFAULT_SELECTION]', '', $defaultValueFunc.' disabled') . '<p id="helpBlock" class="help-block">'. $defaultMessage .'</p></div></div>';
    }

    $header .= '<input id="DEFAULT_VALUE_'.$_REQUEST['id'].'" type="hidden" value="'.$RET['DEFAULT_SELECTION'].'">';

    $new = ($_REQUEST['id'] == 'new');
    $header .= '<div class="form-group"><div class="col-lg-8 col-md-offset-4">' . CheckboxInputSwitch($RET['REQUIRED'], 'tables[' . $_REQUEST['id'] . '][REQUIRED]', _required,'', false, 'Yes', 'No', '', 'switch-success') . '</div></div>';
    
    if($RET['SCHOOL_ID']>0)
        $system_wide='N';
    else
         $system_wide='Y';
    // print_r($RET);
    // echo $system_wide;
    $header .= '<div class="form-group"><div class="col-lg-8 col-md-offset-4">' . CheckboxInputSwitch($system_wide, 'SYSTEM_WIDE', ''._systemWide.'','', false, 'Yes','No', '', 'switch-success') . '</div></div>';
   
    $header .= '</div>';
    $header .= '<div class="panel-footer"><div class="heading-elements">' . SubmitButton(_save, '', 'id="setupSchoolFieldsBtn" class="btn btn-primary heading-btn pull-right ml-10" onclick="formcheck_schoolfields(this);"') . $delete_button . '</div></div>';
    $header .= '</div>'; //.panel

    DrawHeaderHome($header);

    echo '</FORM>';
    echo '</div>'; //.col-md-6
} elseif (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) != 'delete') {

    echo '<div class="col-md-6 col-md-offset-3">';
    $count = 0;
    $count++;
    $LO_options = array('save' =>false, 'search' =>false, 'add' =>true);

    $columns = array('TITLE' =>_schoolFields,'SORT_ORDER' =>_sortOrder, 'TYPE' =>_fieldType);
    $link = array();
    $arr = array(_schoolName,
     _address,
     _city,
     _state,
     _zipPostalCode,
     _principal,
     _baseGradingScale,
     _email,
    _website,
     _schoolLogo,
    );
    $RET = DBGet(DBQuery("SELECT * FROM school_custom_fields WHERE SCHOOL_ID IN (" . UserSchool() .",0) ORDER BY SORT_ORDER"));
    foreach ($arr as $key => $value) {
        $fields_RET1[$count] = array('ID' => '', 'TITLE' => $value, 'TYPE' => '<span style="color:#ea8828;">'._default.'</span>');
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
    ListOutput($dd, $columns, _schoolField, _schoolFields, $link, array(), $LO_options);
    echo '</div>'; //.panel

    echo '</div>'; //.col-md-6
}
echo '</div>'; //.row

function typeLength($defaultValue, $setLength) {
    if($setLength == '') {
        $setLength = 1;
    }

    if($defaultValue != '') {
        if(strlen($defaultValue) > $setLength) {
            $returnLength = strlen($defaultValue);
        } else {
            $returnLength = $setLength;
        }
    } else {
        $returnLength = $setLength;
    }

    return $returnLength;
}

?>
