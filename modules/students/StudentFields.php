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
DrawBC("" . _students . " > " . ProgramTitle());
$_openSIS['allow_edit'] = true;

echo '<input id="customFieldModule" type="hidden" value="student">';

$not_default = false;
$err_msg = '';
if (clean_param($_REQUEST['tables'], PARAM_NOTAGS) && ($_POST['tables'] || $_REQUEST['ajax'])) {
    $table = $_REQUEST['table'];
    foreach ($_REQUEST['tables'] as $id => $columns) {
        if ($id != 'new') {
            if ($columns['CATEGORY_ID'] && $columns['CATEGORY_ID'] != $_REQUEST['category_id'])
                $_REQUEST['category_id'] = $columns['CATEGORY_ID'];

            $sql = "UPDATE $table SET ";

            if ($_REQUEST['DEFAULT_DATATYPE_' . $id] == 'multiple' && $columns['DEFAULT_SELECTION'] != '') {
                $columns['DEFAULT_SELECTION'] = '||' . $columns['DEFAULT_SELECTION'] . '||';
            }

            if ($_REQUEST['DEFAULT_DATATYPE_' . $id] == 'numeric' && $columns['REQUIRED'] == 'Y' && ($columns['DEFAULT_SELECTION'] == NULL || $columns['DEFAULT_SELECTION'] == '')) {
                $columns['DEFAULT_SELECTION'] = '0.00';
            }

            foreach ($columns as $column => $value) {
                if ($column == 'TITLE' && $value != '') {
                    $value = str_replace("'", "''", clean_param(trim($value), PARAM_SPCL));
                    $title = strtoupper(str_replace("'", "''", clean_param($value, PARAM_SPCL)));
                }
                $value = paramlib_validation($column, $value);
                $sql .= $column . "='" . trim($value) . "',";
            }

            $chk_title = DBGet(DBQuery('SELECT COUNT(*) AS TITLE_FOUND FROM student_field_categories WHERE TITLE=\'' . $title . '\' AND ID <>' . $id));

            if ($chk_title[1]['TITLE_FOUND'] != 0) {

                echo $err_msg = "<div class=\"alert bg-danger alert-styled-left\">" . _titleAlreadyExists . "</div>";
            } else {
                $sql = substr($sql, 0, -1) . " WHERE ID='$id'";

                $go = true;
                if ($table == 'custom_fields')
                    $custom_field_id = $id;
                $sql;
                DBQuery($sql);
            }
        } else {
            $sql = "INSERT INTO $table ";

            if ($table == 'custom_fields') {
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

                // $id = DBGet(DBQuery("SHOW TABLE STATUS LIKE 'custom_fields'"));
                // $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
                // $id = $id[1]['ID'];
                $fields = "CATEGORY_ID,SYSTEM_FIELD,";
                $values = "'" . $_REQUEST['category_id'] . "','N',";
                // $_REQUEST['id'] = $id;

                // switch ($columns['TYPE']) {
                //     case 'radio':
                //         $Sql_add_column = "ALTER TABLE students ADD CUSTOM_$id VARCHAR(1) ";
                //         break;

                //     case 'text':
                //         $Sql_add_column = "ALTER TABLE students ADD CUSTOM_$id VARCHAR(255)";
                //         break;

                //     case 'select':
                //     case 'autos':
                //     case 'edits':
                //         $Sql_add_column = "ALTER TABLE students ADD CUSTOM_$id VARCHAR(100)";
                //         break;

                //     case 'codeds':
                //         $Sql_add_column = "ALTER TABLE students ADD CUSTOM_$id VARCHAR(15)";
                //         break;

                //     case 'multiple':
                //         $Sql_add_column = "ALTER TABLE students ADD CUSTOM_$id VARCHAR(255)";
                //         break;

                //     case 'numeric':
                //         $Sql_add_column = "ALTER TABLE students ADD CUSTOM_$id NUMERIC(20,2)";
                //         if (!is_numeric($columns['DEFAULT_SELECTION'])) {
                //             $not_default = true;
                //             $columns['DEFAULT_SELECTION'] = '';
                //         }
                //         break;

                //     case 'date':
                //         $Sql_add_column = "ALTER TABLE students ADD CUSTOM_$id  DATE";
                //         if (preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $columns['DEFAULT_SELECTION']) === 0) {
                //             $not_default = true;
                //             $columns['DEFAULT_SELECTION'] = '';
                //         }
                //         break;

                //     case 'textarea':
                //         $Sql_add_column = "ALTER TABLE students ADD CUSTOM_$id LONGTEXT";
                //         $not_default = true;
                //         break;
                // }
                // if ($columns['REQUIRED']) {
                //     $Sql_add_column .= " NOT NULL ";
                // } else {
                //     $Sql_add_column .= " NULL ";
                // }
                // if ($columns['DEFAULT_SELECTION'] && $not_default == false) {
                //     $Sql_add_column .= " DEFAULT  '" . $columns['DEFAULT_SELECTION'] . "' ";
                // }
                // DBQuery($Sql_add_column);
                //                                                                      
                // unset($table);
            } elseif ($table == 'student_field_categories') {
                if (trim($_REQUEST['tables']['new']['TITLE']) != '') {
                    $chk_title = DBGet(DBQuery('SELECT COUNT(*) AS TITLE_FOUND FROM student_field_categories WHERE TITLE=\'' . str_replace("'", "''", trim($_REQUEST['tables']['new']['TITLE'])) . '\''));
                    if ($chk_title[1]['TITLE_FOUND'] != 0)
                        $err_msg = "<div class=\"alert bg-danger alert-styled-left\">" . _titleAlreadyExists . "</div>";
                    // else {


                    //     $id = DBGet(DBQuery("SHOW TABLE STATUS LIKE 'student_field_categories'"));
                    //     $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
                    //     $id = $id[1]['ID'];
                    //     $fields = "";
                    //     $values = "";
                    //     $_REQUEST['category_id'] = $id;
                    //     // add to profile or permissions of user creating it
                    //     if (User('PROFILE_ID') != '')
                    //         DBQuery("INSERT INTO profile_exceptions (PROFILE_ID,MODNAME,CAN_USE,CAN_EDIT) values('" . User('PROFILE_ID') . "','students/Student.php&category_id=$id','Y','Y')");
                    //     else {
                    //         $profile_id_mod = DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE USER_ID='" . User('STAFF_ID')));
                    //         $profile_id_mod = $profile_id_mod[1]['PROFILE_ID'];
                    //         if ($profile_id_mod != '')
                    //             DBQuery("INSERT INTO profile_exceptions (PROFILE_ID,MODNAME,CAN_USE,CAN_EDIT) values('" . $profile_id_mod . "','students/Student.php&category_id=$id','Y','Y')");
                    //     }
                    // }
                }
            }

            $go = false;

            foreach ($columns as $column => $value) {
                if (trim($value)) {
                    //                                   
                    $fields .= $column . ',';
                    if ($column == 'TITLE' && $value != '') {
                        $value = str_replace("'", "''", clean_param(trim($value), PARAM_SPCL));
                    }
                    $values .= "'" . $value . "',";
                    $go = true;
                }
            }
            $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';
        }

        if ($go) {
            if ($err_msg == '') {
                DBQuery($sql);
                if ($id == 'new') {
                    if ($table == 'student_field_categories') {
                        $_REQUEST['category_id'] = mysqli_insert_id($connection);
                        if (User('PROFILE_ID') != '')
                            DBQuery("INSERT INTO profile_exceptions (PROFILE_ID,MODNAME,CAN_USE,CAN_EDIT) values('" . User('PROFILE_ID') . "','students/Student.php&category_id=$_REQUEST[category_id]','Y','Y')");
                        else {
                            $profile_id_mod = DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE USER_ID='" . User('STAFF_ID')));
                            $profile_id_mod = $profile_id_mod[1]['PROFILE_ID'];
                            if ($profile_id_mod != '')
                                DBQuery("INSERT INTO profile_exceptions (PROFILE_ID,MODNAME,CAN_USE,CAN_EDIT) values('" . $profile_id_mod . "','students/Student.php&category_id=$_REQUEST[category_id]','Y','Y')");
                        }
                    } else if ($table == 'custom_fields') {
                        $_REQUEST['id'] = $id = mysqli_insert_id($connection);

                        switch ($columns['TYPE']) {
                            case 'radio':
                                $Sql_add_column = "ALTER TABLE students ADD CUSTOM_$id VARCHAR(1) ";
                                break;

                            case 'text':
                                $Sql_add_column = "ALTER TABLE students ADD CUSTOM_$id VARCHAR(255)";
                                break;

                            case 'select':
                            case 'autos':
                            case 'edits':
                                $Sql_add_column = "ALTER TABLE students ADD CUSTOM_$id VARCHAR(100)";
                                break;

                            case 'codeds':
                                $Sql_add_column = "ALTER TABLE students ADD CUSTOM_$id VARCHAR(15)";
                                break;

                            case 'multiple':
                                $Sql_add_column = "ALTER TABLE students ADD CUSTOM_$id VARCHAR(255)";
                                break;

                            case 'numeric':
                                $Sql_add_column = "ALTER TABLE students ADD CUSTOM_$id NUMERIC(20,2)";
                                if (!is_numeric($columns['DEFAULT_SELECTION'])) {
                                    $not_default = true;
                                    $columns['DEFAULT_SELECTION'] = '';
                                }
                                break;

                            case 'date':
                                $Sql_add_column = "ALTER TABLE students ADD CUSTOM_$id  DATE";
                                if (preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $columns['DEFAULT_SELECTION']) === 0) {
                                    $not_default = true;
                                    $columns['DEFAULT_SELECTION'] = '';
                                }
                                break;

                            case 'textarea':
                                $Sql_add_column = "ALTER TABLE students ADD CUSTOM_$id LONGTEXT";
                                $not_default = true;
                                break;
                        }
                        if ($columns['REQUIRED']) {
                            $Sql_add_column .= " NOT NULL ";
                        } else {
                            $Sql_add_column .= " NULL ";
                        }
                        if ($columns['DEFAULT_SELECTION'] && $not_default == false) {
                            $Sql_add_column .= " DEFAULT  '" . $columns['DEFAULT_SELECTION'] . "' ";
                        }
                        DBQuery($Sql_add_column);
                        unset($table);
                    }
                }
            } else {
                if ($id == 'new')
                    echo '<script>window.location.href="Modules.php?modname=' . $_REQUEST[modname] . '&table=student_field_categories&category_id=new&err=true"</script>';
                echo $err_msg;
            }
        }
        if ($custom_field_id) {
            $custom_update = DBGet(DBQuery("SELECT TYPE,REQUIRED,DEFAULT_SELECTION,HIDE FROM custom_fields WHERE ID=$custom_field_id"));
            $custom_update = $custom_update[1];
            switch ($custom_update['TYPE']) {
                case 'radio':
                    $Sql_modify_column = "ALTER TABLE students MODIFY CUSTOM_$id VARCHAR(1) ";
                    break;

                case 'text':
                    $Sql_modify_column = "ALTER TABLE students MODIFY CUSTOM_$id VARCHAR(255)";
                    break;

                case 'select':
                case 'autos':
                case 'edits':
                    $Sql_modify_column = "ALTER TABLE students MODIFY CUSTOM_$id VARCHAR(100)";
                    break;

                case 'codeds':
                    $Sql_modify_column = "ALTER TABLE students MODIFY CUSTOM_$id VARCHAR(15)";
                    break;

                case 'multiple':
                    $Sql_modify_column = "ALTER TABLE students MODIFY CUSTOM_$id VARCHAR(255)";
                    break;

                case 'numeric':
                    $Sql_modify_column = "ALTER TABLE students MODIFY CUSTOM_$id NUMERIC(20,2)";
                    if (!is_numeric($columns['DEFAULT_SELECTION'])) {
                        $not_default = true;
                    }
                    break;

                case 'date':
                    $Sql_modify_column = "ALTER TABLE students MODIFY CUSTOM_$id  DATE";
                    if (preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $columns['DEFAULT_SELECTION']) === 0) {
                        $not_default = true;
                    }
                    break;

                case 'textarea':
                    $Sql_modify_column = "ALTER TABLE students MODIFY CUSTOM_$id LONGTEXT";
                    // $not_default = true;
                    break;
            }
            if ($custom_update['REQUIRED']) {
                $Sql_modify_column .= " NOT NULL";
            } else {
                $Sql_modify_column .= " NULL";
            }
            if ($custom_update['DEFAULT_SELECTION'] && $not_default == false) {
                $Sql_modify_column .= " DEFAULT  '" . $custom_update['DEFAULT_SELECTION'] . "' ";

                $existing_column_updt = 'UPDATE `students` SET CUSTOM_' . $id . ' = \'' . $custom_update['DEFAULT_SELECTION'] . '\' WHERE (CUSTOM_' . $id . ' IS NULL OR CUSTOM_' . $id . ' = "")';
            }

            DBQuery($Sql_modify_column);

            if ($existing_column_updt)
                DBQuery($existing_column_updt);
        }
    }
    unset($_REQUEST['tables']);
}

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'delete') {
    if ($_REQUEST['id']) {
        $id = $_REQUEST['id'];
        $has_assigned_RET = DBGet(DBQuery("SELECT COUNT(CUSTOM_$id) AS TOTAL_ASSIGNED FROM students WHERE CUSTOM_$id<>''"));
        $has_assigned = $has_assigned_RET[1]['TOTAL_ASSIGNED'];
        $msg = _cannotDeleteBecauseStudentFieldsAreAssociated;
    } else if ($_REQUEST['category_id'] == 5) {
        $has_assigned_RET = DBGet(DBQuery("SELECT COUNT(*) AS TOTAL_ASSIGNED FROM student_goal"));
        $has_assigned = $has_assigned_RET[1]['TOTAL_ASSIGNED'];
        $msg = _cannotDeleteBecauseFieldCategoriesAreAssociated;
    } else {
        $catId = $_REQUEST['category_id'];
        $sql = "Select id as customid from custom_fields where category_id=$catId";
        $customFields_for_catIdArr = DBGet(DBQuery($sql));

        $deletable_category = TRUE;
        for ($i = 1; $i <= count($customFields_for_catIdArr); $i++) {
            $customId = $customFields_for_catIdArr[$i]['CUSTOMID'];
            $assigned_RET = DBGet(DBQuery("SELECT COUNT(CUSTOM_$customId) AS TOTAL_ASSIGNED FROM students WHERE CUSTOM_$customId<>''"));
            $assigned = $assigned_RET[1]['TOTAL_ASSIGNED'];
            if ($assigned > 0) {
                $deletable_category = FALSE;
                break;
            }
        }
    }
    if ($has_assigned > 0) {
        $queryString = 'category_id=' . $_REQUEST['category_id'];
        UnableDeletePromptMod($msg, '', $queryString);
    } else {

        if ($_REQUEST['id']) {
            if (DeletePromptCommon(_studentField)) {
                $id = $_REQUEST['id'];
                DBQuery("DELETE FROM custom_fields WHERE ID='$id'");
                DBQuery("ALTER TABLE students DROP COLUMN CUSTOM_$id");
                $_REQUEST['modfunc'] = '';
                unset($_REQUEST['id']);
            }
        } elseif ($_REQUEST['category_id']) {
            if ($deletable_category == FALSE) {
                $msg = _cannotDeleteBecauseStudentFieldsAreAssociated;
                $queryString = 'category_id=' . $_REQUEST['category_id'];
                UnableDeletePromptMod($msg, '', $queryString);
            } elseif (DeletePromptCommon(_studentFieldCategoryAndAllFieldsInTheCategory)) {
                $fields = DBGet(DBQuery("SELECT ID FROM custom_fields WHERE SYSTEM_FIELD='N' AND CATEGORY_ID='$_REQUEST[category_id]'"));
                foreach ($fields as $field) {
                    DBQuery("DELETE FROM custom_fields WHERE ID='$field[ID]'");
                    DBQuery("ALTER TABLE students DROP COLUMN CUSTOM_$field[ID]");
                }
                DBQuery("DELETE FROM student_field_categories WHERE ID='$_REQUEST[category_id]'");
                // remove from profiles and permissions
                DBQuery("DELETE FROM profile_exceptions WHERE MODNAME='students/Student.php&category_id=$_REQUEST[category_id]'");
                $_REQUEST['modfunc'] = '';
                unset($_REQUEST['category_id']);
            }
        }
    }
}

if (!$_REQUEST['modfunc']) {
    // CATEGORIES
    $sql = "SELECT ID,TITLE,SORT_ORDER FROM student_field_categories ORDER BY SORT_ORDER,TITLE";
    $QI = DBQuery($sql);
    $categories_RET = DBGet($QI);

    if (AllowEdit() && $_REQUEST['id'] != 'new' && $_REQUEST['category_id'] != 'new' && ($_REQUEST['id'] || $_REQUEST['category_id'] > 7))
        $delete_button = "<INPUT type=button value=" . _delete . " class=\"btn btn-danger m-r-10\" onClick='javascript:window.location=\"Modules.php?modname=$_REQUEST[modname]&modfunc=delete&category_id=$_REQUEST[category_id]&id=$_REQUEST[id]\"'>" . "&nbsp;";

    // ADDING & EDITING FORM
    if ($_REQUEST['id'] && $_REQUEST['id'] != 'new') {
        $sql = "SELECT CATEGORY_ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,SORT_ORDER,REQUIRED,REQUIRED,HIDE,(SELECT TITLE FROM student_field_categories WHERE ID=CATEGORY_ID) AS CATEGORY_TITLE FROM custom_fields WHERE ID='$_REQUEST[id]'";
        $RET = DBGet(DBQuery($sql));
        $RET = $RET[1];
        $title = $RET['CATEGORY_TITLE'] . ' - ' . $RET['TITLE'];
    } elseif ($_REQUEST['category_id'] && $_REQUEST['category_id'] != 'new' && $_REQUEST['id'] != 'new') {
        $sql = "SELECT TITLE,SORT_ORDER,INCLUDE
				FROM student_field_categories
				WHERE ID='$_REQUEST[category_id]'";
        $RET = DBGet(DBQuery($sql));
        $RET = $RET[1];
        $title = $RET['TITLE'];
        $id = $RET['ID'];
        if ($id == 2)
            $RET['SORT_ORDER'] = 4;
        if ($id == 6)
            $RET['SORT_ORDER'] = 2;
        if ($id == 3)
            $RET['SORT_ORDER'] = 3;
        if ($id == 4)
            $RET['SORT_ORDER'] = 5;
        if ($id == 5)
            $RET['SORT_ORDER'] = 6;
    } elseif ($_REQUEST['id'] == 'new')
        $title = _newStudentField;
    elseif ($_REQUEST['category_id'] == 'new')
        $title = _newStudentFieldCategory;

    if ($_REQUEST['id']) {
        echo "<FORM name=F1 id=F1 class=\"form-horizontal\" action=Modules.php?modname=$_REQUEST[modname]&category_id=$_REQUEST[category_id]";
        if ($_REQUEST['id'] != 'new')
            echo "&id=$_REQUEST[id]";
        echo "&table=custom_fields method=POST>";

        echo '<div class="panel panel-default">';

        DrawHeader($title, $delete_button . SubmitButton(_save, '', 'id="setupStuFieldsBtn" class="btn btn-primary" onclick="formcheck_student_studentField_F1(this);"')); //'<INPUT type=submit value='._save.'>');
        echo '<div class="panel-body">';
        $header .= '<input type=hidden id=f_id value="' . $_REQUEST['id'] . '"/>';

        $header .= '<div class="row">';
        $header .= '<div class="col-lg-6">';
        $header .= TextInput($RET['TITLE'], 'tables[' . $_REQUEST['id'] . '][TITLE]', _fieldName);
        $header .= '</div><div class="col-lg-6">';

        // You can't change a student field type after it has been created
        // mab - allow changing between select and autos and edits and text
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
        } else {
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
        }

        $header .= '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _dataType . '<br>(' . ($_REQUEST['id'] != 'new' ? _thisValueCantBeChanged : _enterThisValueCarefullyAsThisCantBeChangedLater) . ')</label><div class="col-md-8">' . SelectInput($RET['TYPE'], 'tables[' . $_REQUEST['id'] . '][TYPE]', '', $type_options, false, 'id=type onchange="formcheck_student_studentField_F1_defalut();" ' . ($_REQUEST['id'] != 'new' ? 'disabled' : '')) . '</div></div>';

        $header .= '<input id="DEFAULT_DATATYPE_' . $_REQUEST['id'] . '" name="DEFAULT_DATATYPE_' . $_REQUEST['id'] . '" type="hidden" value="' . $RET['TYPE'] . '">';

        if ($_REQUEST['id'] != 'new' && $RET['TYPE'] != 'multiple' && $RET['TYPE'] != 'codeds' && $RET['TYPE'] != 'select' && $RET['TYPE'] != 'autos' && $RET['TYPE'] != 'edits' && $RET['TYPE'] != 'text' && $RET['TYPE'] != 'date' && $RET['TYPE'] != 'radio' && $RET['TYPE'] != 'numeric' && $RET['TYPE'] != 'textarea') {
            $_openSIS['allow_edit'] = $allow_edit;
            $_openSIS['AllowEdit'][$modname] = $AllowEdit;
        }

        $header .= '</div>'; //.col-lg-6
        $header .= '</div>'; //.row   


        foreach ($categories_RET as $type)
            $categories_options[$type['ID']] = $type['TITLE'];

        $header .= '<div class="row">';
        $header .= '<div class="col-lg-6">';
        $header .= '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _studentFieldCategory . '</label><div class="col-md-8">' . SelectInput($RET['CATEGORY_ID'] ? $RET['CATEGORY_ID'] : $_REQUEST['category_id'], 'tables[' . $_REQUEST['id'] . '][CATEGORY_ID]', '', $categories_options, false, 'onchange="formcheck_student_studentField_F1_defalut();"') . '</div></div>';
        $header .= '</div><div class="col-lg-6">';
        if ($_REQUEST['id'] == 'new' || $RET['SORT_ORDER'] == '')
            $header .= '<div class="form-group">' . TextInput($RET['SORT_ORDER'], 'tables[' . $_REQUEST['id'] . '][SORT_ORDER]', _sortOrder, 'maxlength=5 onkeydown="return numberOnly(event);"') . '</div>';
        else
            $header .= '<div class="form-group">' . TextInput($RET['SORT_ORDER'], 'tables[' . $_REQUEST['id'] . '][SORT_ORDER]', _sortOrder, 'maxlength=5 onkeydown=\"return numberOnly(event);\"') . '</div>';
        $header .= '</div>'; //.col-lg-6
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

        $colspan = 2;

        $header .= '<div class="row">';

        if ($RET['TYPE'] == 'autos' || $RET['TYPE'] == 'edits' || $RET['TYPE'] == 'select' || $RET['TYPE'] == 'codeds' || $RET['TYPE'] == 'multiple' || $_REQUEST['id'] == 'new') {
            $header .= '<div class="col-lg-6" id="show_textarea" style="display:block">';
            $header .= '<label class="control-label col-md-4 text-right">' . _pullDown . '/' . _autoPullDown . '/' . _codedPullDown . '/' . _selectMultipleChoices . '</label><div class="col-md-8">' . TextAreaInput($RET['SELECT_OPTIONS'], 'tables[' . $_REQUEST['id'] . '][SELECT_OPTIONS]', '', 'rows=7 cols=40 onkeyup=checkValidDefaultValue()') . '<p class="help-block">* ' . ucfirst(_onePerLine) . '</p><p id="exmp" class="help-block">' . $exampleText . '</p></div>';
            $colspan = 1;
            $header .= '</div>';

            $header .= '<div style="display:none;"><textarea id="SELECT_OPTIONS_VALUE_' . $_REQUEST['id'] . '">' . $RET['SELECT_OPTIONS'] . '</textarea></div>';
        }

        if ($RET['TYPE'] == 'numeric')
            $defaultValueFunc = 'onkeydown=\"return numberOnly(event);\"';
        else
            $defaultValueFunc = 'onkeyup=checkValidDefaultValue()';

        $header .= '<div class="col-lg-6">';

        if (($RET['DEFAULT_SELECTION'] == NULL || $RET['DEFAULT_SELECTION'] == '') && $RET['TYPE'] != 'date') {
            $header .= '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _default . '<br>(' . _enterThisValueCarefullyAsThisCantBeChangedLater . ')</label><div class="col-lg-8">' . TextInput_mod_a($RET['DEFAULT_SELECTION'], 'tables[' . $_REQUEST['id'] . '][DEFAULT_SELECTION]', '', $defaultValueFunc) . '<p class="help-block">* ' . _forDatesYyyyMmDdForCheckboxesYForLongTextItWillBeIgnored . '</p><p id="helpBlock" class="help-block"></p></div>';
        } else {
            $header .= '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _default . '<br>(' . _thisValueCantBeChanged . ')</label><div class="col-lg-8">' . TextInput_mod_a($RET['DEFAULT_SELECTION'], 'tables[' . $_REQUEST['id'] . '][DEFAULT_SELECTION]', '', $defaultValueFunc . ' disabled') . '<p id="helpBlock" class="help-block">' . $defaultMessage . '</p></div>';
        }

        $header .= '<input id="DEFAULT_VALUE_' . $_REQUEST['id'] . '" type="hidden" value="' . $RET['DEFAULT_SELECTION'] . '">';

        $header .= '</div>';
        $new = ($_REQUEST['id'] == 'new');
        $header .= '<div class="form-group"><label class="control-label col-lg-4 text-right">&nbsp;</label><div class="col-lg-8">';
        $header .= CheckboxInputSwitch($RET['REQUIRED'], 'tables[' . $_REQUEST['id'] . '][REQUIRED]', _required, '', $new, 'Yes', 'No', '', 'switch-success');
        $header .= CheckboxInputSwitch($RET['HIDE'], 'tables[' . $_REQUEST['id'] . '][HIDE]', _hide, '', $new, 'Yes', 'No', '', 'switch-success');
        $header .= '</div></div>';
        $header .= '</div>'; //.col-lg-6
        $header .= '</div>'; //.row
    } elseif ($_REQUEST['category_id']) {
        if ($_REQUEST['err'] == true)
            echo "<script>document.getElementById('divErr').innerHTML='<font color=red><b>" . _titleAlreadyExists . "</b></font>';</script>";
        echo "<FORM class=\"form-horizontal m-b-0\" name=F2 id=F2 action=Modules.php?modname=$_REQUEST[modname]&table=student_field_categories";
        if ($_REQUEST['category_id'] != 'new')
            echo "&category_id=$_REQUEST[category_id]";
        echo " method=POST>";

        echo '<div class="panel panel-default">';
        if ($_REQUEST['category_id'] > 7 || $_REQUEST['category_id'] == 'new')
            DrawHeader($title, $delete_button . SubmitButton(_save, '', 'id="setupStuCatBtn" class="btn btn-primary" onclick="formcheck_student_studentField_F2(this);"'));

        echo '<div class="panel-body">';
        $header .= '<input type=hidden id=t_id value="' . $_REQUEST['category_id'] . '"/>';
        $header .= '<div class="row">';
        $header .= '<div class="col-md-6"><div class="form-group">' . (($_REQUEST['category_id'] <= 7 && $_REQUEST['category_id'] != 'new') ? NoInput($RET['TITLE'], _title) : TextInput($RET['TITLE'], 'tables[' . $_REQUEST['category_id'] . '][TITLE]', _title, 'placeholder=' . _title . '')) . '</div></div>';

        if ($_REQUEST['category_id'] == 'new' || $RET['SORT_ORDER'] == '')
            $header .= '<div class="col-md-6"><div class="form-group">' . (($_REQUEST['category_id'] <= 7 && $_REQUEST['category_id'] != 'new') ? NoInput($RET['SORT_ORDER'], _sortOrder) : TextInput($RET['SORT_ORDER'], 'tables[' . $_REQUEST['category_id'] . '][SORT_ORDER]', _sortOrder, 'maxlength=5 placeholder="' . _sortOrder . '" onkeydown="return numberOnly(event);"')) . '</div></div>';
        else
            $header .= '<div class="col-md-6"><div class="form-group">' . (($_REQUEST['category_id'] <= 7 && $_REQUEST['category_id'] != 'new') ? NoInput($RET['SORT_ORDER'], _sortOrder) : TextInput($RET['SORT_ORDER'], 'tables[' . $_REQUEST['category_id'] . '][SORT_ORDER]', _sortOrder, 'maxlength=5 placeholder=\"' . _sortOrder . '\" onkeydown=\"return numberOnly(event);\"')) . '</div></div>';

        $new = ($_REQUEST['category_id'] == 'new');
        if ($_REQUEST['category_id'] > 7 || $new)
            $header .= '<div class="col-md-6"><div class="form-group"><label class="control-label col-md-4 text-right">' . _include . '</label><div class="col-md-8">' . TextInput($RET['INCLUDE'], 'tables[' . $_REQUEST['category_id'] . '][INCLUDE]', '', 'placeholder="' . _include . '"') . '<p class="help-block">' . _shouldBeLeftBlankForMostCategories . '</p></div></div></div>';

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

    if (count($categories_RET)) {
        foreach ($categories_RET as $ci => $cd) {

            if ($cd['TITLE'] == generalInfo) {
                $categories_RET1[1]['ID'] = $categories_RET[1]['ID'];
                $categories_RET1[1]['TITLE'] = $categories_RET[1]['TITLE'];
            } elseif ($cd['TITLE'] == _enrollmentInfo) {
                $categories_RET1[2]['ID'] = $categories_RET[6]['ID'];
                $categories_RET1[2]['TITLE'] = $categories_RET[6]['TITLE'];
            } else {
                $categories_RET1[$ci + 1]['ID'] = $cd['ID'];
                $categories_RET1[$ci + 1]['TITLE'] = $cd['TITLE'];
            }
        }
        unset($ci);
        unset($cd);
        ksort($categories_RET1);

        $si = 1;
        foreach ($categories_RET1 as $ci => $cd) {
            $categories_RET2[$si] = $cd;
            $categories_RET2[$si]['SORT_ORDER'] = $si;
            $si++;
        }
        unset($si);
        unset($ci);
        unset($cd);
        unset($caegories_RET1);
        $categories_RET = $categories_RET2;
        unset($caegories_RET2);
        if ($_REQUEST['category_id']) {
            foreach ($categories_RET as $key => $value) {
                if ($value['ID'] == $_REQUEST['category_id'])
                    $categories_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
            }
        }
    }

    echo '<div class="col-md-6">';
    echo '<div class="panel">';
    $columns = array(
        'TITLE' => _category,
        'SORT_ORDER' => _order,
    );
    $link = array();
    $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]";

    $link['TITLE']['variables'] = array('category_id' => 'ID');

    $link['add']['link'] = "#" . " onclick='check_content(\"Ajax.php?modname=$_REQUEST[modname]&category_id=new\");'";

    foreach ($categories_RET as $key => $value) {
        switch ($value['TITLE']) {
            case 'General Info':
                $categories_RET[$key]['TITLE'] = ucwords(strtolower(_generalInfo));
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
                $categories_RET[$key]['TITLE'] = ucwords(strtolower(_goals));
                break;
            case 'Enrollment Info':
                $categories_RET[$key]['TITLE'] = ucwords(strtolower(_enrollmentInfo));
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
    ListOutput($categories_RET, $columns, _studentFieldCategory, _studentFieldCategories, $link, array(), $LO_options);
    echo '</div>'; //.panel
    echo '</div>'; //.col-md-6
    // FIELDS
    if ($_REQUEST['category_id'] && $_REQUEST['category_id'] != 'new' && count($categories_RET)) {
        $sql = "SELECT ID,TITLE,TYPE,SORT_ORDER,'Custom' AS FEILD FROM custom_fields WHERE SYSTEM_FIELD='N' AND CATEGORY_ID='" . $_REQUEST['category_id'] . "' ORDER BY SORT_ORDER,TITLE";
        $fields_RET = DBGet(DBQuery($sql), array('TYPE' => '_makeType'));

        if (count($fields_RET)) {
            if ($_REQUEST['id'] && $_REQUEST['id'] != 'new') {
                foreach ($fields_RET as $key => $value) {
                    if ($value['ID'] == $_REQUEST['id'])
                        $fields_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
                }
            }
        }

        echo '<div class="col-md-6">';
        echo '<div class="panel">';
        $columns = array(
            'TITLE' => ucwords(strtolower(_studentField)),
            'SORT_ORDER' => _order,
            'FEILD' => _fieldType,
        );
        $link = array();

        $count = 0;
        $count++;
        switch ($_REQUEST['category_id']) {
            case 1:
                $arr = array(
                    _name,
                    _estimatedGradDate,
                    _gender,
                    _ethnicity,
                    _commonName,
                    _birthdate,
                    _primaryLanguage,
                    _email,
                    _phone,
                );
                $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&category_id=$_REQUEST[category_id]";
                $link['add']['link'] = "#" . " onclick='check_content(\"Ajax.php?modname=$_REQUEST[modname]&category_id=$_REQUEST[category_id]&id=new\");'";
                $link['TITLE']['variables'] = array('id' => 'ID');

                break;
            case 2:
                $arr = array(
                    _primaryCarePhysician,
                    _physicianSPhone,
                    _preferredMedicalFacility,
                    _dateMedicalNotes,
                    _medicalNotes,
                    _typeImmunizations,
                    _dateImmunizations,
                    _commentsImmunizations,
                    _alertDate,
                    _medicalAlert,
                    _dateNurseVisits,
                    _timeIn,
                    _timeOut,
                    _reason,
                    _result,
                    _commentsNurseVisits,
                );
                $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&category_id=$_REQUEST[category_id]";
                $link['add']['link'] = "#" . " onclick='check_content(\"Ajax.php?modname=$_REQUEST[modname]&category_id=$_REQUEST[category_id]&id=new\");'";
                $link['TITLE']['variables'] = array('id' => 'ID');

                break;
            case 3:
                $arr = array(
                    _addressLine_1,
                    _addressLine_2,
                    _city,
                    _state,
                    _zipPostalCode,
                    _schoolBusPickUp,
                    _schoolBusDropOff,
                    _busNo,
                    _relationshipToStudent,
                    _firstName,
                    _lastName,
                    _homePhone,
                    _workPhone,
                    _cellMobilePhone,
                    _email,
                    _custodyOfStudent,
                );

                break;
            case 4:
                $arr = array(_enteredBy, _date, _comments);

                break;
            case 5:
                $arr = array(
                    _goalIncTitle,
                    _beginDate,
                    _endDate,
                    _goalIncDescription,
                );
                break;
            case 6:
                $arr = array(
                    _studentId,
                    _alternateId,
                    _grade,
                    _calendar,
                    _rollingRetentionOptions,
                    _username,
                    _password,
                    _lastLogin,
                    _disableStudent,
                    _startDate,
                    _enrollmentCode,
                    _dropDate,
                    _dropCode,
                    _school,
                );

                break;
            case 7:
                $arr = array('File');

                break;
            default:
                $link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&category_id=$_REQUEST[category_id]";
                $link['add']['link'] = "#" . " onclick='check_content(\"Ajax.php?modname=$_REQUEST[modname]&category_id=$_REQUEST[category_id]&id=new\");'";
                $link['TITLE']['variables'] = array('id' => 'ID');

                break;
        }
        foreach ($arr as $key => $value) {
            $fields_RET1[$count] = array('ID' => '', 'SORT_ORDER' => ($key + 1), 'TITLE' => $value, 'FEILD' => '<span style="color:#ea8828;">' . _default . '</span>');
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
        foreach ($dd as $key => $value) {
            // print_r($value);
            switch ($value['FEILD']) {
                case 'Custom':
                    $dd[$key]['FEILD'] = _custom;
                    break;

                default:
                    break;
            }
        }
        ListOutput($dd, $columns, _studentField, _studentFields, $link, array(), $LO_options);
        echo '</div>'; //.panel
        echo '</div>'; //.col-md-6
    }
    echo '</div>'; //.row
}

function _makeType($value, $name)
{
    $options = array(
        'radio' => _checkbox,
        'text' => _text,
        'autos' => _autoPullDown,
        'edits' => _editPullDown,
        'select' => _pullDown,
        'codeds' => _codedPullDown,
        'date' => _date,
        'numeric' => _number,
        'textarea' => _longText,
        'multiple' => _selectMultiple,
    );
    return $options[$value];
}
