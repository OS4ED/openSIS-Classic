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
DrawBC("" . _users . " > " . ProgramTitle());
$_openSIS['allow_edit'] = true;
$not_default = false;

echo '<input id="customFieldModule" type="hidden" value="people">';

if (clean_param($_REQUEST['tables'], PARAM_NOTAGS) && ($_POST['tables'] || $_REQUEST['ajax'])) {
    $table = $_REQUEST['table'];
    foreach ($_REQUEST['tables'] as $id => $columns) {
        if ($id != 'new') {
            if (isset($columns['TITLE']) && trim($columns['TITLE']) == '') {
                echo "<div class='alert alert-danger'>" . _unableToSaveDataBecauseTitleCanNotBeBlank . "</div>";
            } else {
                if ($columns['CATEGORY_ID'] && $columns['CATEGORY_ID'] != $_REQUEST['category_id'])
                    $_REQUEST['category_id'] = $columns['CATEGORY_ID'];

                $sql = 'UPDATE ' . $table . ' SET ';

                if ($_REQUEST['DEFAULT_DATATYPE_' . $id] == 'multiple' && $columns['DEFAULT_SELECTION'] != '') {
                    $columns['DEFAULT_SELECTION'] = '||' . $columns['DEFAULT_SELECTION'] . '||';
                }

                if ($_REQUEST['DEFAULT_DATATYPE_' . $id] == 'numeric' && $columns['REQUIRED'] == 'Y' && ($columns['DEFAULT_SELECTION'] == NULL || $columns['DEFAULT_SELECTION'] == '')) {
                    $columns['DEFAULT_SELECTION'] = '0.00';
                }

                foreach ($columns as $column => $value) {

                    if ($column == 'TITLE' && $value != '') {
                        $value = str_replace("'", "''", clean_param(trim($value), PARAM_SPCL));
                    }
                    $sql .= $column . '=\'' . $value . '\',';
                }
                $sql = substr($sql, 0, -1) . ' WHERE ID=\'' . $id . '\'';
                $go = true;
                if ($table == 'people_fields')
                    $custom_field_id = $id;
            }
        } else {
            $sql = 'INSERT INTO ' . $table . '';

            if ($table == 'people_fields') {
                if ($columns['CATEGORY_ID']) {
                    $_REQUEST['category_id'] = $columns['CATEGORY_ID'];
                    unset($columns['CATEGORY_ID']);
                }

                if (isset($columns['TYPE']) && isset($columns['REQUIRED'])) {
                    if ($columns['TYPE'] == 'numeric' && $columns['REQUIRED'] == 'Y' && ($columns['DEFAULT_SELECTION'] == NULL || $columns['DEFAULT_SELECTION'] == '')) {
                        $columns['DEFAULT_SELECTION'] = '0.00';
                    }
                }

                if ($columns['TYPE'] == 'multiple' && $columns['DEFAULT_SELECTION'] != '') {
                    $columns['DEFAULT_SELECTION'] = '||' . $columns['DEFAULT_SELECTION'] . '||';
                }

                // $id = DBGet(DBQuery('SHOW TABLE STATUS LIKE \'' . 'people_fields' . '\''));
                // $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
                // $id = $id[1]['ID'];
                $fields = 'CATEGORY_ID,';
                $values = '\'' . $_REQUEST['category_id'] . '\',';
                // $_REQUEST['id'] = $id;

                // switch ($columns['TYPE']) {
                //     case 'radio':
                //         $Sql_add_column = 'ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(1) ';
                //         break;

                //     case 'text':
                //         $Sql_add_column = 'ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(255) ';
                //         break;
                //     case 'select':
                //     case 'autos':
                //     case 'edits':
                //         $Sql_add_column = 'ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(100) ';
                //         break;

                //     case 'codeds':
                //         $Sql_add_column = 'ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(15)';
                //         break;

                //     case 'multiple':
                //         $Sql_add_column = 'ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(255)';
                //         break;

                //     case 'numeric':
                //         $Sql_add_column = 'ALTER TABLE people ADD CUSTOM_' . $id . ' NUMERIC(20,2) ';
                //         if (!is_numeric($columns['DEFAULT_SELECTION'])) {
                //             $not_default = true;
                //             $columns['DEFAULT_SELECTION'] = '';
                //         }
                //         break;

                //     case 'date':
                //         $Sql_add_column = 'ALTER TABLE people ADD CUSTOM_' . $id . ' DATE ';
                //         if (preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $columns['DEFAULT_SELECTION']) === 0) {
                //             $not_default = true;
                //             $columns['DEFAULT_SELECTION'] = '';
                //         }
                //         break;

                //     case 'textarea':
                //         $Sql_add_column = 'ALTER TABLE people ADD CUSTOM_' . $id . ' LONGTEXT ';
                //         $not_default = true;
                //         break;
                // }
                // if ($columns['REQUIRED']) {
                //     $Sql_add_column .= ' NOT NULL ';
                // } else {
                //     $Sql_add_column .= ' NULL ';
                // }
                // if ($columns['DEFAULT_SELECTION'] && $not_default == false) {
                //     $Sql_add_column .= ' DEFAULT  \'' . $columns['DEFAULT_SELECTION'] . '\' ';
                // }
                // DBQuery($Sql_add_column);
                // if ($columns['TYPE'] != 'textarea')
                //     DBQuery('CREATE INDEX CUSTOM_IND' . $id . ' ON people (CUSTOM_' . $id . ')');
                // unset($table);
            } elseif ($table == 'people_field_categories') {

                // $id = DBGet(DBQuery('SHOW TABLE STATUS LIKE \'' . 'people_field_categories' . '\''));
                // $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
                // $id = $id[1]['ID'];
                $fields = '';
                $values = '';
                // $_REQUEST['category_id'] = $id;
                // add to profile or permissions of user creating it
                // if (User('PROFILE_ID') != '')
                //     DBQuery('INSERT INTO profile_exceptions (PROFILE_ID,MODNAME,CAN_USE,CAN_EDIT) values(\'' . User('PROFILE_ID') . '\',\'users/User.php&category_id=' . $id . '\',\'Y\',\'Y\')');
                // else
                //     DBQuery('INSERT INTO staff_exceptions (USER_ID,MODNAME,CAN_USE,CAN_EDIT) values(\'' . User('STAFF_ID') . '\',\'users/User.php&category_id=' . $id . '\',\'Y\',\'Y\')');
            }

            $go = false;

            foreach ($columns as $column => $value) {
                if ($value) {
                    if ($column == 'TITLE' && $value != '') {
                        $value = str_replace("'", "''", clean_param(trim($value), PARAM_SPCL));
                    }
                    $fields .= $column . ',';
                    $values .= '\'' . $value . '\',';
                    $go = true;
                }
            }
            $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';
        }

        if ($go) {
            DBQuery($sql);
            if ($table == 'people_field_categories') {
                $_REQUEST['category_id'] = $id = mysqli_insert_id($connection);
                if (User('PROFILE_ID') != '')
                    DBQuery('INSERT INTO profile_exceptions (PROFILE_ID,MODNAME,CAN_USE,CAN_EDIT) values(\'' . User('PROFILE_ID') . '\',\'users/User.php&category_id=' . $id . '\',\'Y\',\'Y\')');
                else
                    DBQuery('INSERT INTO staff_exceptions (USER_ID,MODNAME,CAN_USE,CAN_EDIT) values(\'' . User('STAFF_ID') . '\',\'users/User.php&category_id=' . $id . '\',\'Y\',\'Y\')');
            } else if ($table == 'people_fields') {
                $_REQUEST['id'] = $id = mysqli_insert_id($connection);
                switch ($columns['TYPE']) {
                    case 'radio':
                        $Sql_add_column = 'ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(1) ';
                        break;

                    case 'text':
                        $Sql_add_column = 'ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(255) ';
                        break;
                    case 'select':
                    case 'autos':
                    case 'edits':
                        $Sql_add_column = 'ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(100) ';
                        break;

                    case 'codeds':
                        $Sql_add_column = 'ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(15)';
                        break;

                    case 'multiple':
                        $Sql_add_column = 'ALTER TABLE people ADD CUSTOM_' . $id . ' VARCHAR(255)';
                        break;

                    case 'numeric':
                        $Sql_add_column = 'ALTER TABLE people ADD CUSTOM_' . $id . ' NUMERIC(20,2) ';
                        if (!is_numeric($columns['DEFAULT_SELECTION'])) {
                            $not_default = true;
                            $columns['DEFAULT_SELECTION'] = '';
                        }
                        break;

                    case 'date':
                        $Sql_add_column = 'ALTER TABLE people ADD CUSTOM_' . $id . ' DATE ';
                        if (preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $columns['DEFAULT_SELECTION']) === 0) {
                            $not_default = true;
                            $columns['DEFAULT_SELECTION'] = '';
                        }
                        break;

                    case 'textarea':
                        $Sql_add_column = 'ALTER TABLE people ADD CUSTOM_' . $id . ' LONGTEXT ';
                        $not_default = true;
                        break;
                }
                if ($columns['REQUIRED']) {
                    $Sql_add_column .= ' NOT NULL ';
                } else {
                    $Sql_add_column .= ' NULL ';
                }
                if ($columns['DEFAULT_SELECTION'] && $not_default == false) {
                    $Sql_add_column .= ' DEFAULT  \'' . $columns['DEFAULT_SELECTION'] . '\' ';
                }
                DBQuery($Sql_add_column);
                if ($columns['TYPE'] != 'textarea')
                    DBQuery('CREATE INDEX CUSTOM_IND' . $id . ' ON people (CUSTOM_' . $id . ')');
                unset($table);
            }
        }

        if ($custom_field_id) {
            $custom_update = DBGet(DBQuery('SELECT TYPE,REQUIRED,DEFAULT_SELECTION FROM people_fields WHERE ID=\'' . $custom_field_id . '\''));
            $custom_update = $custom_update[1];
            switch ($custom_update['TYPE']) {
                case 'radio':
                    $Sql_modify_column = 'ALTER TABLE people MODIFY CUSTOM_' . $id . ' VARCHAR(1) ';
                    break;

                case 'text':
                    $Sql_modify_column = 'ALTER TABLE people MODIFY CUSTOM_' . $id . ' VARCHAR(255)';
                    break;

                case 'select':
                case 'autos':
                case 'edits':
                    $Sql_modify_column = 'ALTER TABLE people MODIFY CUSTOM_' . $id . ' VARCHAR(100)';
                    break;

                case 'codeds':
                    $Sql_modify_column = 'ALTER TABLE people MODIFY CUSTOM_' . $id . ' VARCHAR(15)';
                    break;

                case 'multiple':
                    $Sql_modify_column = 'ALTER TABLE people MODIFY CUSTOM_' . $id . ' VARCHAR(255)';
                    break;

                case 'numeric':
                    $Sql_modify_column = 'ALTER TABLE people MODIFY CUSTOM_' . $id . ' NUMERIC(20,2)';
                    if (!is_numeric($columns['DEFAULT_SELECTION'])) {
                        $not_default = true;
                    }
                    break;

                case 'date':
                    $Sql_modify_column = 'ALTER TABLE people MODIFY CUSTOM_' . $id . '  DATE';
                    if (preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $columns['DEFAULT_SELECTION']) === 0) {
                        $not_default = true;
                    }
                    break;

                case 'textarea':
                    $Sql_modify_column = 'ALTER TABLE people MODIFY CUSTOM_' . $id . ' LONGTEXT';
                    // $not_default = true;
                    break;
            }

            if ($custom_update['REQUIRED']) {
                $Sql_modify_column .= ' NOT NULL ';
            } else {
                $Sql_modify_column .= ' NULL ';
            }
            if ($custom_update['DEFAULT_SELECTION'] && $not_default == false) {
                $Sql_modify_column .= ' DEFAULT  \'' . $custom_update['DEFAULT_SELECTION'] . '\' ';

                $existing_column_updt = 'UPDATE `people` SET CUSTOM_' . $id . ' = \'' . $custom_update['DEFAULT_SELECTION'] . '\' WHERE (CUSTOM_' . $id . ' IS NULL OR CUSTOM_' . $id . ' = "")';
            }

            DBQuery($Sql_modify_column);

            if ($existing_column_updt)
                DBQuery($existing_column_updt);
        }
    }
    unset($_REQUEST['tables']);
}

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'delete') {
    if (clean_param($_REQUEST['id'], PARAM_INT)) {
        $fid = $_REQUEST['id'];
        $has_assigned_RET = DBGet(DBQuery("SELECT COUNT(CUSTOM_$fid) AS TOTAL_ASSIGNED FROM people WHERE CUSTOM_$fid<>'' OR CUSTOM_$fid IS NULL"));
        $has_assigned = $has_assigned_RET[1]['TOTAL_ASSIGNED'];
        if ($has_assigned > 0) {
            UnableDeletePromptMod('Cannot delete becauses this people field is associated.');
        } else {
            if (DeletePromptMod('parent field')) {
                $id = clean_param($_REQUEST['id'], PARAM_INT);
                DBQuery('DELETE FROM people_fields WHERE ID=\'' . $id . '\'');
                DBQuery('ALTER TABLE people DROP COLUMN CUSTOM_' . $id . '');
                $_REQUEST['modfunc'] = '';
                unset($_REQUEST['id']);
            }
        }
    } elseif (clean_param($_REQUEST['category_id'], PARAM_INT)) {
        $has_assigned_RET = DBGet(DBQuery('SELECT COUNT(*) AS TOTAL_ASSIGNED FROM people_fields WHERE CATEGORY_ID=\'' . $_REQUEST['category_id'] . '\''));
        $has_assigned = $has_assigned_RET[1]['TOTAL_ASSIGNED'];
        if ($has_assigned > 0) {
            UnableDeletePromptMod('Cannot delete becauses this people field category is associated.');
        } else {
            if (DeletePromptMod('parent field category')) {
                $fields = DBGet(DBQuery('SELECT ID FROM people_fields WHERE CATEGORY_ID=\'' . $_REQUEST['category_id'] . '\''));
                foreach ($fields as $field) {
                    DBQuery('DELETE FROM people_fields WHERE ID=\'' . $field[ID] . '\'');
                    DBQuery('ALTER TABLE people DROP COLUMN CUSTOM_' . $field[ID] . '');
                }
                DBQuery('DELETE FROM people_field_categories WHERE ID=\'' . $_REQUEST['category_id'] . '\'');
                // remove from profiles and permissions
                DBQuery('DELETE FROM profile_exceptions WHERE MODNAME=\'users/User/Student.php&category_id=' . $_REQUEST['category_id'] . '\'');
                $_REQUEST['modfunc'] = '';
                unset($_REQUEST['category_id']);
            }
        }
    }
}

if (!$_REQUEST['modfunc']) {
    // CATEGORIES
    $sql = 'SELECT ID,TITLE,SORT_ORDER FROM people_field_categories ORDER BY SORT_ORDER,TITLE';
    $QI = DBQuery($sql);
    $categories_RET = DBGet($QI);

    if (AllowEdit() && $_REQUEST['id'] != 'new' && $_REQUEST['category_id'] != 'new' && ($_REQUEST['id'] || $_REQUEST['category_id'] > 2))
        $delete_button = "<INPUT type=button class=\"btn btn-danger m-r-10\" value='" . _delete . "' onClick='javascript:window.location=\"Modules.php?modname=$_REQUEST[modname]&modfunc=delete&category_id=$_REQUEST[category_id]&id=$_REQUEST[id]\"'> ";

    // ADDING & EDITING FORM
    if ($_REQUEST['id'] && $_REQUEST['id'] != 'new') {
        $sql = 'SELECT CATEGORY_ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,SORT_ORDER,REQUIRED,REQUIRED,(SELECT TITLE FROM people_field_categories WHERE ID=CATEGORY_ID) AS CATEGORY_TITLE FROM people_fields WHERE ID=\'' . $_REQUEST['id'] . '\'';
        $RET = DBGet(DBQuery($sql));
        $RET = $RET[1];
        $title = $RET['CATEGORY_TITLE'] . ' - ' . $RET['TITLE'];
    } elseif ($_REQUEST['category_id'] && $_REQUEST['category_id'] != 'new' && $_REQUEST['id'] != 'new') {
        $sql = 'SELECT ID, TITLE,ADMIN,TEACHER,PARENT,NONE,SORT_ORDER,INCLUDE
				FROM people_field_categories
				WHERE ID=\'' . $_REQUEST['category_id'] . '\'';
        $RET = DBGet(DBQuery($sql));
        $RET = $RET[1];
        $title = $RET['TITLE'];
    } elseif ($_REQUEST['id'] == 'new')
        $title = ucwords(strtolower(_newUserField));
    elseif ($_REQUEST['category_id'] == 'new')
        $title = _newUserFieldCategory;


    if ($_REQUEST['id']) {

        echo "<FORM class=\"form-horizontal\" name=F1 id=F1 action=Modules.php?modname=$_REQUEST[modname]&category_id=$_REQUEST[category_id]";
        if ($_REQUEST['id'] != 'new')
            echo "&id=$_REQUEST[id]";
        echo "&table=people_fields method=POST>";
        echo '<div class="panel panel-default">';

        DrawHeader($title, $delete_button . '' . SubmitButton(_save, '', 'id="userFieldsBtn" class="btn btn-primary" onclick="formcheck_user_userfields_F1(this);"')); //'<INPUT type=submit value='._save.'>');

        echo '<div class="panel-body">';
        $header .= '<input type=hidden id=f_id value="' . $_REQUEST['id'] . '"/>';
        $header .= '<div class="row">';
        $header .= '<div class="col-md-6">';
        $header .= TextInput($RET['TITLE'], 'tables[' . $_REQUEST['id'] . '][TITLE]', _fieldName);
        $header .= '</div>'; //.col-md-6
        // You can't change a student field type after it has been created
        // mab - allow changing between select and autos and edits and text
        if ($_REQUEST['id'] != 'new') {
            $type_options = array('select' => 'Pull-Down', 'autos' => 'Auto Pull-Down', 'edits' => 'Edit Pull-Down', 'text' => 'Text', 'radio' => 'Checkbox', 'codeds' => 'Coded Pull-Down', 'numeric' => 'Number', 'multiple' => 'Select Multiple from Options', 'date' => 'Date', 'textarea' => 'Long Text');
        } else
            $type_options = array('select' => 'Pull-Down', 'autos' => 'Auto Pull-down', 'edits' => 'Edit Pull-Down', 'text' => 'Text', 'radio' => 'Checkbox', 'codeds' => 'Coded Pull-Down', 'numeric' => 'Number', 'multiple' => 'Select Multiple from Options', 'date' => 'Date', 'textarea' => 'Long Text');

        $header .= '<div class="col-md-6">';
        $header .= '<div class="form-group">' . SelectInput($RET['TYPE'], 'tables[' . $_REQUEST['id'] . '][TYPE]', _dataType . '<br>(' . ($_REQUEST['id'] != 'new' ? _thisValueCantBeChanged : _enterThisValueCarefullyAsThisCantBeChangedLater) . ')', $type_options, false, 'id=type onchange="formcheck_student_studentField_F1_defalut();" ' . ($_REQUEST['id'] != 'new' ? 'disabled' : '')) . '</div>';
        $header .= '</div>'; //.col-md-6

        $header .= '<input id="DEFAULT_DATATYPE_' . $_REQUEST['id'] . '" name="DEFAULT_DATATYPE_' . $_REQUEST['id'] . '" type="hidden" value="' . $RET['TYPE'] . '">';

        if ($_REQUEST['id'] != 'new' && $RET['TYPE'] != 'multiple' && $RET['TYPE'] != 'codeds' && $RET['TYPE'] != 'select' && $RET['TYPE'] != 'autos' && $RET['TYPE'] != 'edits' && $RET['TYPE'] != 'text' && $RET['TYPE'] != 'date' && $RET['TYPE'] != 'radio' && $RET['TYPE'] != 'numeric' && $RET['TYPE'] != 'textarea') {
            $_openSIS['allow_edit'] = $allow_edit;
            $_openSIS['AllowEdit'][$modname] = $AllowEdit;
        }
        foreach ($categories_RET as $type)
            $categories_options[$type['ID']] = $type['TITLE'];

        $header .= '<div class="col-md-6">';
        $header .= '<div class="form-group"><label class="control-label text-right col-md-4">' . _userFieldCategory . '</label><div class="col-md-8">' . SelectInput($RET['CATEGORY_ID'] ? $RET['CATEGORY_ID'] : $_REQUEST['category_id'], 'tables[' . $_REQUEST['id'] . '][CATEGORY_ID]', '', $categories_options, false, 'onchange="formcheck_student_studentField_F1_defalut();"') . '</div></div>';
        $header .= '</div>'; //.col-md-6

        $header .= '<div class="col-md-6">';
        if ($_REQUEST['id'] == 'new' || $RET['SORT_ORDER'] == '')
            $header .= '<div class="form-group">' . TextInput($RET['SORT_ORDER'], 'tables[' . $_REQUEST['id'] . '][SORT_ORDER]', _sortOrder, 'maxlength=5 onkeydown="return numberOnly(event);"') . '</div>';
        else
            $header .= '<div class="form-group">' . TextInput($RET['SORT_ORDER'], 'tables[' . $_REQUEST['id'] . '][SORT_ORDER]', _sortOrder, 'maxlength=5 onkeydown=\"return numberOnly(event);\"') . '</div>';

        $header .= '</div>'; //.col-md-6
        $header .= '</div>'; //.row

        $defaultMessage = '';
        $exampleText = '';
        if ($RET['TYPE'] == 'multiple') {
            $RET['DEFAULT_SELECTION'] = str_replace('"', '&rdquo;', str_replace('||', ', ', substr($RET['DEFAULT_SELECTION'], 2, -2)));
        }
        if ($_REQUEST['id'] != 'new') {
            if ($RET['TYPE'] == 'autos' || $RET['TYPE'] == 'edits' || $RET['TYPE'] == 'select' || $RET['TYPE'] == 'multiple') {
                $exampleText = _example . ':<br/>Good<br/>Bad<br/>etc.';
            } else if ($RET['TYPE'] == 'codeds') {
                $exampleText = _example . ':<br/>0|Good<br/>1|Bad<br/>etc.';
            }
            // else if ($RET['TYPE'] == 'multiple') {
            //     $exampleText = _example.':<br/>||Good||<br/>||Bad||<br/>etc.';
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
                        $defaultMessage = '<span class="text-warning"><b>' . _warning . '!</b> ' . _defaultValueDoesNotMatchWithTheValuesOfPullDown . '!</span>';
                    }
                }
            }
        } else {
            $exampleText = _example . ':<br/>Good<br/>Bad<br/>etc.';
        }

        $header .= '<div class="row">';
        $colspan = 2;
        if ($RET['TYPE'] == 'autos' || $RET['TYPE'] == 'edits' || $RET['TYPE'] == 'select' || $RET['TYPE'] == 'codeds' || $RET['TYPE'] == 'multiple' || $_REQUEST['id'] == 'new') {
            $header .= '<div class="col-md-6" id="show_textarea" style="display:block"><label class="control-label col-lg-4 text-right">' . _pullDown . '/' . _autoPullDown . '/' . _codedPullDown . '/' . _selectMultipleChoices . '</label><div class="col-lg-8">' . TextAreaInput($RET['SELECT_OPTIONS'], 'tables[' . $_REQUEST['id'] . '][SELECT_OPTIONS]', '', 'rows=7 cols=40 onkeyup=checkValidDefaultValue()') . '<p class="help-block">* ' . ucfirst(_onePerLine) . '</p><p id="exmp" class="help-block">' . $exampleText . '</p></div></div>';
            $colspan = 1;

            $header .= '<div style="display:none;"><textarea id="SELECT_OPTIONS_VALUE_' . $_REQUEST['id'] . '">' . $RET['SELECT_OPTIONS'] . '</textarea></div>';
        }

        if ($RET['TYPE'] == 'numeric')
            $defaultValueFunc = 'onkeydown=\"return numberOnly(event);\"';
        else
            $defaultValueFunc = 'onkeyup=checkValidDefaultValue()';

        $header .= '<div class="col-md-6">';

        if (($RET['DEFAULT_SELECTION'] == NULL || $RET['DEFAULT_SELECTION'] == '') && $RET['TYPE'] != 'date') {
            $header .= '<div class="form-group"><label class="col-lg-4 text-right control-label">' . _default . '<br>(' . _enterThisValueCarefullyAsThisCantBeChangedLater . ')</label><div class="col-lg-8">' . TextInput($RET['DEFAULT_SELECTION'], 'tables[' . $_REQUEST['id'] . '][DEFAULT_SELECTION]', '', $defaultValueFunc) . '<p class="help-block">* ' . _forDatesYyyyMmDdForCheckboxesYAmpForLongTextItWillBeIgnored . '</p><p id="helpBlock" class="help-block"></p></div></div>';
        } else {
            $header .= '<div class="form-group"><label class="col-lg-4 text-right control-label">' . _default . '<br>(' . _thisValueCantBeChanged . ')</label><div class="col-lg-8">' . TextInput($RET['DEFAULT_SELECTION'], 'tables[' . $_REQUEST['id'] . '][DEFAULT_SELECTION]', '', $defaultValueFunc . ' disabled') . '<p id="helpBlock" class="help-block">' . $defaultMessage . '</p></div></div>';
        }

        $header .= '<input id="DEFAULT_VALUE_' . $_REQUEST['id'] . '" type="hidden" value="' . $RET['DEFAULT_SELECTION'] . '">';

        $new = ($_REQUEST['id'] == 'new');
        // $header .= '<div class="col-md-3"><div class="form-group"><label class="col-lg-4 text-right control-label">'._required.'</label><div class="col-lg-8">' . CheckboxInput($RET['REQUIRED'], 'tables[' . $_REQUEST['id'] . '][REQUIRED]', '', '', $new) . '</div></div></div>';
        $header .= '<div class="form-group"><label class="control-label col-lg-4 text-right">&nbsp;</label><div class="col-lg-8">';
        $header .= CheckboxInputSwitch($RET['REQUIRED'], 'tables[' . $_REQUEST['id'] . '][REQUIRED]', _required, '', $new, 'Yes', 'No', '', 'switch-success');
        $header .= '</div></div>';

        $header .= '</div>'; //.col-md-6
        $header .= '</div>'; //.row
    } elseif ($_REQUEST['category_id']) {

        echo "<FORM class=\"form-horizontal\" name=F2 id=F2 action=Modules.php?modname=$_REQUEST[modname]&table=people_field_categories";
        if ($_REQUEST['category_id'] != 'new')
            echo "&category_id=$_REQUEST[category_id]";
        echo " method=POST>";
        echo '<div class="panel panel-default">';
        DrawHeader($title, $delete_button . SubmitButton(_save, '', 'id="userFieldsCatBtn" class="btn btn-primary" onclick="formcheck_user_userfields_F2(this);"')); //'<INPUT type=submit value='._save.'>');

        echo '<div class="panel-body">';
        $header .= '<input type=hidden id=t_id value="' . $_REQUEST['category_id'] . '"/>';

        $header .= '<div class="row">';
        $header .= '<div class="col-md-6">';
        $header .= '<div class="form-group">' . (($RET['ID'] > 2 || $RET['ID'] == '') ? TextInput($RET['TITLE'], 'tables[' . $_REQUEST['category_id'] . '][TITLE]', _title) : NoInput($RET['TITLE'], _title)) . '</div>';
        $header .= '</div>'; //.col-md-6

        $header .= '<div class="col-md-4">';
        $header .= '<div class="form-group">' . (($RET['SORT_ORDER'] > 2 || $RET['SORT_ORDER'] == '') ? TextInput($RET['SORT_ORDER'], 'tables[' . $_REQUEST['category_id'] . '][SORT_ORDER]', _sortOrder) : NoInput($RET['SORT_ORDER'], _sortOrder)) . '</div>';
        $header .= '</div>'; //.col-md-4
        $new = ($_REQUEST['category_id'] == 'new');
        // if ($_REQUEST['category_id'] > 2 || $new) {
        //     $header .= '<div class="col-md-6">';
        //     $header .= '<div class="form-group">' . TextInput($RET['INCLUDE'], 'tables[' . $_REQUEST['category_id'] . '][INCLUDE]', 'Include (should be left blank for most categories)') . '</div>';
        //     $header .= '</div>'; //.col-md-6
        // }
        $header .= '</div>'; //.row

        $header .= '<div class="form-group">';
        $header .= '<label class="control-label col-md-2 text-right">' . _profiles . '</label>';
        $header .= '<div class="col-md-10">';
        $header .= ($RET['ID'] > 2 || $RET['ID'] == '') ? '<div class="checkbox">' : '<p>';
        // $header .= (($RET['ID'] > 2 || $RET['ID'] == '') ? CheckboxInput($RET['ADMIN'], 'tables[' . $_REQUEST['category_id'] . '][ADMIN]', ($_REQUEST['category_id'] == '1' && !$RET['ADMIN'] ? '<FONT color=red>' : '') . 'Administrator' . ($_REQUEST['category_id'] == '1' && !$RET['ADMIN'] ? '</FONT>' : ''), '', $new, '<i class="icon-checkbox-checked"></i>', '<i class="icon-checkbox-unchecked"></i>') : '<span>' . ($RET['ADMIN'] == 'Y' ? '<i class="icon-checkbox-checked"></i>' : '<i class="icon-checkbox-unchecked"></i>') . ' &nbsp; Administrator</span> &nbsp; &nbsp; ');
        // $header .= (($RET['ID'] > 2 || $RET['ID'] == '') ? CheckboxInput($RET['TEACHER'], 'tables[' . $_REQUEST['category_id'] . '][TEACHER]', ($_REQUEST['category_id'] == '1' && !$RET['TEACHER'] ? '<FONT color=red>' : '') . 'Teacher' . ($_REQUEST['category_id'] == '1' && !$RET['TEACHER'] ? '</FONT>' : ''), '', $new, '<i class="icon-checkbox-checked"></i>', '<i class="icon-checkbox-unchecked"></i>') : '<span>' . ($RET['TEACHER'] == 'Y' ? '<i class="icon-checkbox-checked"></i>' : '<i class="icon-checkbox-unchecked"></i>') . ' &nbsp; Teacher</span> &nbsp; &nbsp; ');
        $header .= (($RET['ID'] > 2 || $RET['ID'] == '') ? CheckboxInput($RET['PARENT'], 'tables[' . $_REQUEST['category_id'] . '][PARENT]', ($_REQUEST['category_id'] == '1' && !$RET['PARENT'] ? '<FONT color=red>' : '') . 'Parent' . ($_REQUEST['category_id'] == '1' && !$RET['TEACHER'] ? '</FONT>' : ''), '', $new, '<i class="icon-checkbox-checked"></i>', '<i class="icon-checkbox-unchecked"></i>') : '<span>' . ($RET['PARENT'] == 'Y' ? '<i class="icon-checkbox-checked"></i>' : '<i class="icon-checkbox-unchecked"></i>') . ' &nbsp; ' . _parent . '</span> &nbsp; &nbsp; ');
        $header .= '<br>';
        $header .= (($RET['ID'] > 2 || $RET['ID'] == '') ? CheckboxInput($RET['NONE'], 'tables[' . $_REQUEST['category_id'] . '][NONE]', ($_REQUEST['category_id'] == '1' && !$RET['NONE'] ? '<FONT color=red>' : '') . 'No Access' . ($_REQUEST['category_id'] == '1' && !$RET['TEACHER'] ? '</FONT>' : ''), '', $new, '<i class="icon-checkbox-checked"></i>', '<i class="icon-checkbox-unchecked"></i>') : '<span>' . ($RET['NONE'] == 'Y' ? '<i class="icon-checkbox-checked"></i>' : '<i class="icon-checkbox-unchecked"></i>') . ' &nbsp; ' . _noAccess . '</span> &nbsp; &nbsp;');
        $header .= ($RET['ID'] > 2 || $RET['ID'] == '') ? '</div>' : '</p>'; //.checkbox
        $header .= '</div>'; //.col-md-10
        $header .= '</div>'; //.form-group
    } else
        $header = false;

    if ($header) {
        DrawHeaderHome($header);
        echo '</div>'; //.panel-body
        echo '</div>'; //.panel
        echo '</FORM>';
    }


    // DISPLAY THE MENU
    $LO_options = array('save' => false, 'search' => false, 'add' => true);

    echo '<div class="row">';

    if (is_countable($categories_RET) && count($categories_RET)) {
        if ($_REQUEST['category_id']) {
            foreach ($categories_RET as $key => $value) {
                if ($value['ID'] == $_REQUEST['category_id'])
                    $categories_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
            }
        }
    }

    echo '<div class="col-md-6">';
    echo '<div class="panel panel-default">';
    $columns = array(
        'TITLE' => _category,
        'SORT_ORDER' => _order,
    );
    $link = array();
    $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]";
    $link['TITLE']['variables'] = array('category_id' => 'ID');
    $link['add']['link'] = "#" . " onclick='check_content(\"Ajax.php?modname=$_REQUEST[modname]&category_id=new\");'";

    echo '<div class="table-responsive">';
    foreach ($categories_RET as $key => $value) {
        switch ($value['TITLE']) {
            case 'General Info':
                $categories_RET[$key]['TITLE'] = ucwords(strtolower(_generalInfo));
                break;
            case 'Address Info':
                $categories_RET[$key]['TITLE'] = _addressInfo;
                break;
            case 'Addresses &amp; Contacts':
                $categories_RET[$key]['TITLE'] = _addressesContacts;
                break;
            case 'Medical':
                $categories_RET[$key]['TITLE'] = _medical;
                break;
            case 'Comments':
                $categories_RET[$key]['TITLE'] = _comments;
                break;
            case 'Goals':
                $categories_RET[$key]['TITLE'] = _goals;
                break;
            case 'Enrollment Info':
                $categories_RET[$key]['TITLE'] = _enrollmentInfo;
                break;
            case 'Files':
                $categories_RET[$key]['TITLE'] = _files;
                break;
            default:
                $categories_RET[$key]['TITLE'] = $value['TITLE'];
                break;
                // case 'Demographic Info':
                //     $categories_RET[$key]['TITLE'] = _demographicInfo;
                //     break;
                // case 'Addresses &amp; Contacts':
                //     $categories_RET[$key]['TITLE'] = _addressesContacts;
                //     break;
                // case 'School Information':
                //     $categories_RET[$key]['TITLE'] = _schoolInformation;
                //     break;
                // case 'Certification Information':
                //     $categories_RET[$key]['TITLE'] = _certificationInformation;
                //     break;
                // case 'Schedule':
                //     $categories_RET[$key]['TITLE'] = _schedule;
                //     break;
        }
    }
    ListOutput($categories_RET, $columns, _userFieldCategory, _userFieldCategories, $link, array(), $LO_options);
    echo '</div>'; //.table-responsive

    echo '</div>'; //.panel.panel-default
    echo '</div>'; //.col-md-6
    // FIELDS
    if ($_REQUEST['category_id'] && $_REQUEST['category_id'] != 'new' && count($categories_RET)) {
        $sql = 'SELECT ID,TITLE,TYPE,SORT_ORDER FROM people_fields WHERE CATEGORY_ID=\'' . $_REQUEST['category_id'] . '\' ORDER BY SORT_ORDER,TITLE';
        $fields_RET = DBGet(DBQuery($sql), array('TYPE' => '_makeType'));

        if (is_countable($fields_RET) && count($fields_RET)) {
            if ($_REQUEST['id'] && $_REQUEST['id'] != 'new') {
                foreach ($fields_RET as $key => $value) {
                    if ($value['ID'] == $_REQUEST['id'])
                        $fields_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
                }
            }
        }

        echo '<div class="col-md-6">';
        echo '<div class="panel panel-default">';
        $columns = array(
            'TITLE' => _userField,
            'SORT_ORDER' => _order,
            'TYPE' => _dataType,
        );
        $link = array();
        $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&category_id=$_REQUEST[category_id]";


        $link['TITLE']['variables'] = array('id' => 'ID');

        $link['add']['link'] = "#" . " onclick='check_content(\"Ajax.php?modname=$_REQUEST[modname]&category_id=$_REQUEST[category_id]&id=new\");'";

        $count = 0;
        $count++;
        switch ($_REQUEST['category_id']) {
            case 1:
                $arr = array(
                    _name,
                    _emailAddress,
                    _disableUser,
                    _userId,
                    _homePhone,
                    _workPhone,
                    _cellPhone,
                    _userProfile,
                    _username,
                    _password,
                );
                $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&category_id=$_REQUEST[category_id]";
                $link['add']['link'] = "#" . " onclick='check_content(\"Ajax.php?modname=$_REQUEST[modname]&category_id=$_REQUEST[category_id]&id=new\");'";
                $link['TITLE']['variables'] = array('id' => 'ID');

                break;
            case 2:
                $arr = array(
                    _address,
                    _street,
                    _city,
                    _state,
                    _zipCode,
                );
                $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&category_id=$_REQUEST[category_id]";
                $link['add']['link'] = "#" . " onclick='check_content(\"Ajax.php?modname=$_REQUEST[modname]&category_id=$_REQUEST[category_id]&id=new\");'";
                $link['TITLE']['variables'] = array('id' => 'ID');

                break;

            default:
                $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&category_id=$_REQUEST[category_id]";


                $link['TITLE']['variables'] = array('id' => 'ID');

                $link['add']['link'] = "#" . " onclick='check_content(\"Ajax.php?modname=$_REQUEST[modname]&category_id=$_REQUEST[category_id]&id=new\");'";


                break;
        }
        foreach ($arr as $key => $value) {
            $fields_RET1[$count] = array('ID' => '', 'SORT_ORDER' => ($key + 1), 'TITLE' => $value, 'TYPE' => '<span style="color:#ea8828;">' . _default . '</span>');
            $count++;
        }
        $count2 = 1;
        foreach ($fields_RET1 as $key2) {
            $dd[$count2] = $key2;
            $count2++;
        }
        foreach ($fields_RET as $row) {
            $dd[$count2] = $row;
            $count2++;
        }

        echo '<div class="table-responsive">';
        ListOutput($dd, $columns, _userField, _userFields, $link, array(), $LO_options);
        echo '</div>'; //.table-responsive


        echo '</div>'; //.panel.panel-default
        echo '</div>'; //.col-md-6
    }

    echo '</div>'; //.row
}

function _makeType($value, $name)
{
    $options = array('radio' => 'Checkbox', 'text' => 'Text', 'autos' => 'Auto Pull-Down', 'edits' => 'Edit Pull-Down', 'select' => 'Pull-Down', 'codeds' => 'Coded Pull-Down', 'date' => 'Date', 'numeric' => 'Number', 'textarea' => 'Long Text', 'multiple' => 'Select Multiple');
    return $options[$value];
}
