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
include 'modules/grades/DeletePromptX.fnc.php';
DrawBC(""._gradebook." > " . ProgramTitle());


if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'update') {
    if ($_REQUEST['tab_id'] != 'new') {
        $id = '';
        $dt = '';
        foreach ($_REQUEST['values'] as $id => $dt) {
            
        }
    }
    if (clean_param($_REQUEST['values'], PARAM_NOTAGS) && ($_POST['values'] || $_REQUEST['ajax'])) {
        if ($_REQUEST['tab_id']) {

            foreach ($_REQUEST['values'] as $id => $columns) {
                if (!(isset($columns['TITLE']) && trim($columns['TITLE']) == '')) {
                    $title = '';
                    $flag = 1;
                    if ($id != 'new') {
                        if ($_REQUEST['tab_id'] != 'new')
                            $sql = 'UPDATE report_card_grades SET ';
                        else
                            $sql = 'UPDATE report_card_grade_scales SET ';

                        foreach ($columns as $column => $value) {
                            #################gcu customization####################   
                            if ($column == 'GPA_CAL' && $value == 'Y' && $_REQUEST['tab_id'] == 'new')
                                $GPA_CAL_selected = true;


                            ##############end#####################################       
                            $value = paramlib_validation($column, $value);
                            if (($column == 'GPA_VALUE' || $column == 'UNWEIGHTED_GP') && $value == '')
                                $value = 0;


                            if ($value == '0' && $column != 'GPA_VALUE' && $column != 'UNWEIGHTED_GP' && $column != 'BREAK_OFF') {
                                $value = _NULL;
                            }
                            if (isset($value))
                                $sql .= $column . '=\'' . str_replace("'", "''", str_replace("\'", "'", trim($value))) . '\',';
                            else
                                $sql .= $column . '=NULL ,';

                            if ($column == 'TITLE') {

                                $title = str_replace("'", "''", trim($value));
                            }
                            if ($column == 'BREAK_OFF') {

                                $break = str_replace("'", "''", trim($value));
                            }
                        }
                        #################gcu customization#################### 
                        if (!isset($GPA_CAL_selected) && $_REQUEST['tab_id'] == 'new')
                            $sql .= 'GPA_CAL="N",';
                        ##############end#####################################    
                        if ($_REQUEST['tab_id'] != 'new')
                            $sql = substr($sql, 0, -1) . ' WHERE ID=\'' . $id . '\'';
                        else
                            $sql = substr($sql, 0, -1) . ' WHERE ID=\'' . $id . '\'';

                        if ($_REQUEST['tab_id'] != 'new') {
                            $validate_title = DBGet(DBQuery('SELECT COUNT(1) as TITLE_EX FROM report_card_grades WHERE SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool() . ' AND UPPER(TITLE)=\'' . strtoupper($title) . '\' AND ID!=\'' . $id . '\' and grade_scale_id =' . $_REQUEST['tab_id']));
                            $match = DBGet(DBQuery('select TITLE from report_card_grades WHERE ID=\'' . $id . '\' '));
                            $match = $match[1]['TITLE'];
                            if (trim($match) == trim($title)) {
                                $flag = 0;
                            }
                            if (isset($break) && $break != '') {
                                $validate_break = DBGet(DBQuery('SELECT COUNT(*) as NO FROM report_card_grades WHERE SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool() . ' AND GRADE_SCALE_ID=' . $_REQUEST['tab_id'] . ' AND break_off=\'' . $break . '\' AND ID!=' . $id));

                                $v_break = $validate_break[1]['NO'];
                            }
                        } else {

                            $validate_title = DBGet(DBQuery('SELECT COUNT(1) as TITLE_EX FROM report_card_grade_scales WHERE SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool() . ' AND UPPER(TITLE)=\'' . strtoupper($title) . '\' AND ID!=\'' . $id . '\''));
                            $match = DBGet(DBQuery('select TITLE from report_card_grade_scales WHERE ID=\'' . $id . '\''));
                            $match = $match[1]['TITLE'];
                            if (trim($match) == trim($title)) {
                                $flag = 0;
                            }

                            $validate_break = DBGet(DBQuery('SELECT COUNT(*) as NO FROM report_card_grades WHERE SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool() . ' AND GRADE_SCALE_ID=\'' . $_REQUEST['tab_id'] . '\' AND break_off=\'' . $break . '\' AND ID!=' . $id));
                            $v_break = $validate_break[1]['NO'];
                        }
                        if ($validate_title[1]['TITLE_EX'] != 0 && $flag != 0) {
                            echo "<div class=\"alert bg-danger alert-styled-left\">"._unableToSaveDataBecauseTitleAlreadyExists."</div>";
                            break;
                        } 
                        // else if ($v_break > 0) {
                        //     echo "<div class=\"alert bg-danger alert-styled-left\">"._unableToSaveDataBecauseBreakOfAlreadyExists."</div>";
                        //     break;
                        // } 
                        else {
                            DBQuery($sql); //update query
                            unset($v_break);
                            unset($break);
                        }
                        unset($GPA_CAL_selected); ##gcu customization  
                    } else {
                        if (clean_param(trim($_REQUEST['values']['new']['TITLE']), PARAM_NOTAGS) != '') {
                            if ($_REQUEST['tab_id'] != 'new') {
                                $sql = 'INSERT INTO report_card_grades ';
                                $fields = 'SCHOOL_ID,SYEAR,GRADE_SCALE_ID,';
                                $values = '\'' . UserSchool() . '\',\'' . UserSyear() . '\',\'' . $_REQUEST['tab_id'] . '\',';
                            } else {
                                $sql = 'INSERT INTO report_card_grade_scales ';
                                $fields = 'SCHOOL_ID,SYEAR,';
                                $values = '\'' . UserSchool() . '\',\'' . UserSyear() . '\',';
                            }
                            $columns['UNWEIGHTED_GP'] = isset($columns['UNWEIGHTED_GP']) && trim($columns['UNWEIGHTED_GP']) != '' ? $columns['UNWEIGHTED_GP'] : $columns['GPA_VALUE'];

                            $go = false;
                            if (!$columns['GPA_CAL'] && $_REQUEST['tab_id'] == 'new')
                                $columns['GPA_CAL'] = 'N';
                            foreach ($columns as $column => $value) {
                                if (trim($value) != '' && $column != 'GPA_VALUE' && $column != 'UNWEIGHTED_GP') {
                                    $value = paramlib_validation($column, $value);
                                    $fields .= $column . ',';
                                    $values .= '\'' . str_replace("'", "''", str_replace("\'", "''", trim($value))) . '\',';
                                    $go = true;
                                    if ($column == 'TITLE') {
                                        $title = str_replace("'", "''", trim($value));
                                    }
                                    if ($column == 'BREAK_OFF') {

                                        $break = trim($value);
                                    }
                                }
                                if ($_REQUEST['tab_id'] != 'new' && (($value == '' && $column == 'GPA_VALUE') || ($column == 'UNWEIGHTED_GP' && $value == ''))) {
                                    $value = 0;
                                    $fields .= $column . ',';
                                    $values .= '\'' . str_replace("'", "''", str_replace("\'", "''", 0)) . '\',';
                                    $go = true;
                                } elseif ($_REQUEST['tab_id'] != 'new' && (($value != '' && $column == 'GPA_VALUE') || ($column == 'UNWEIGHTED_GP' && $value != ''))) {
                                    $value = paramlib_validation($column, $value);
                                    $fields .= $column . ',';
                                    $values .= '\'' . str_replace("'", "''", str_replace("\'", "''", trim($value))) . '\',';
                                    $go = true;
                                }
                            }

                            $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';
                            if ($go) {

                                if ($_REQUEST['tab_id'] != 'new') {
                                    $validate_title = DBGet(DBQuery('SELECT COUNT(1) as TITLE_EX FROM report_card_grades WHERE SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool() . ' AND TITLE=\'' . $title . '\' AND GRADE_SCALE_ID=\'' . $_REQUEST['tab_id'] . '\' '));

                                    $validate_break = DBGet(DBQuery('SELECT COUNT(*) as NO FROM report_card_grades WHERE SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool() . ' AND break_off=\'' . $break . '\' AND GRADE_SCALE_ID=\'' . $_REQUEST['tab_id'] . '\' '));

                                    $v_break = $validate_break[1]['NO'];
                                } else {
                                    $validate_title = DBGet(DBQuery('SELECT COUNT(1) as TITLE_EX FROM report_card_grade_scales WHERE SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool() . ' AND TITLE=\'' . $title . '\' '));
                                    $validate_break = DBGet(DBQuery('SELECT COUNT(*) as NO FROM report_card_grades WHERE SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool() . ' AND break_off=\'' . $break . '\' AND GRADE_SCALE_ID=\'' . $_REQUEST['tab_id'] . '\' '));

                                    $v_break = $validate_break[1]['NO'];
                                }
                                if ($validate_title[1]['TITLE_EX'] != 0 || $new_cat == 'attendance') {
                                    echo "<div class=\"alert bg-danger alert-styled-left\">"._unableToSaveDataBecauseTitleAlreadyExists."</div>";
                                    break;
                                }
                                
                                    DBQuery($sql); //insert query
                                
                            }
                        }
                    }
                }//Title validation ends
            }
        }
    }
    unset($_REQUEST['modfunc']);
}

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'remove') {
    if ($_REQUEST['tab_id'] != 'new') {
        $has_assigned_RET = DBGet(DBQuery('SELECT COUNT(*) AS TOTAL_ASSIGNED FROM student_report_card_grades WHERE REPORT_CARD_GRADE_ID=\'' . $_REQUEST['id'] . '\''));
        $has_assigned = $has_assigned_RET[1]['TOTAL_ASSIGNED'];
    } else {
        $has_assigned_RET = DBGet(DBQuery('SELECT COUNT(*) AS TOTAL_ASSIGNED FROM student_report_card_grades WHERE REPORT_CARD_GRADE_ID IN ( SELECT ID FROM report_card_grades WHERE GRADE_SCALE_ID =\'' . $_REQUEST['id'] . '\')'));
        $has_assigned = $has_assigned_RET[1]['TOTAL_ASSIGNED'];
    }
    if ($has_assigned > 0) {
        UnableDeletePromptX(_cannotDeleteBecauseStudentGradesAreAssociated.'.');
    } else {
        if ($_REQUEST['tab_id'] != 'new') {
            if (DeletePromptX(_reportCardGrade)) {
                DBQuery("DELETE FROM report_card_grades WHERE ID='$_REQUEST[id]'");
            }
        } else
        if (DeletePromptX(_reportCardGradingScale)) {
            $ret = DBGet(DBQuery("select * from course_periods where grade_scale_id=$_REQUEST[id]"));
            $count_associated_period = count($ret);
            if ($count_associated_period == 0) {
                DBQuery('DELETE FROM report_card_grades WHERE GRADE_SCALE_ID=\'' . $_REQUEST[id] . '\'');
                DBQuery('DELETE FROM report_card_grade_scales WHERE ID=\'' . $_REQUEST[id] . '\'');
            } else {
                echo '<BR>';
                PopTable('header',  _alertMessage);
                echo "<CENTER><h4>"._alreadyAssociatedWithACoursePeriod."</h4><br><FORM action=$PHP_tmp_SELF METHOD=POST><INPUT type=button class='btn btn-primary' name=delete_cancel value="._ok." onclick='load_link(\"Modules.php?modname=$_REQUEST[modname]\");'></FORM></CENTER>";
                PopTable('footer');
                return false;
            }
            unset($_SESSION['GR_scale_id']);
        }
    }
}

if (!$_REQUEST['modfunc']) {
    if (User('PROFILE') == 'admin') {
        $grade_scales_RET = DBGet(DBQuery('SELECT ID,TITLE FROM report_card_grade_scales WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' ORDER BY SORT_ORDER'), array(), array('ID'));

        if (!$_REQUEST['tab_id'])
            if (count($grade_scales_RET))
                $_REQUEST['tab_id'] = $_SESSION['GR_scale_id'] = key($grade_scales_RET);
            else
                $_REQUEST['tab_id'] = 'new';
        else
        if ($_REQUEST['tab_id'] != 'new')
            $_SESSION['GR_scale_id'] = $_REQUEST['tab_id'];
    }
    else {
        $course_period_RET = DBGet(DBQuery('SELECT GRADE_SCALE_ID,DOES_BREAKOFF,TEACHER_ID FROM course_periods WHERE COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\''));
        if (!$course_period_RET[1]['GRADE_SCALE_ID'])
            ErrorMessage(array(_thisCourseIsNotGraded), 'fatal');
        $grade_scales_RET = DBGet(DBQuery('SELECT ID,TITLE FROM report_card_grade_scales WHERE ID=\'' . $course_period_RET[1]['GRADE_SCALE_ID'] . '\''), array(), array('ID'));
        if ($course_period_RET[1]['DOES_BREAKOFF'] == 'Y') {
            $teacher_id = $course_period_RET[1]['TEACHER_ID'];
            $config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\'' . $teacher_id . '\' AND PROGRAM=\'Gradebook\' AND VALUE LIKE \'%_' . UserCoursePeriod() . '\''), array(), array('TITLE'));
        }
        $_REQUEST['tab_id'] = key($grade_scales_RET);
    }

    $tabs = array();
    $grade_scale_select = array();
    foreach ($grade_scales_RET as $id => $grade_scale) {
        $tabs[] = array('title' => $grade_scale[1]['TITLE'], 'link' => "Modules.php?modname=$_REQUEST[modname]&tab_id=$id");
        $grade_scale_select += array($id => $grade_scale[1]['TITLE']);
    }

    if ($_REQUEST['tab_id'] != 'new') {
        $sql = 'SELECT * FROM report_card_grades WHERE GRADE_SCALE_ID=\'' . $_REQUEST['tab_id'] . '\' AND SYEAR=\'' . UserSyear() . '\' ORDER BY SORT_ORDER';
        $functions = array('TITLE' => 'makeGradesInput',
            'BREAK_OFF' => 'makeGradesInput',
            'SORT_ORDER' => 'makeGradesInput',
            'GPA_VALUE' => 'makeGradesInput',
            'UNWEIGHTED_GP' => 'makeGradesInput',
            'COMMENT' => 'makeGradesInput');
        $LO_columns = array('TITLE' =>_title,
            'BREAK_OFF' =>_breakoff,
            'GPA_VALUE' =>_weightedGpValue,
            'UNWEIGHTED_GP' =>_unweightedGpValue,
            'SORT_ORDER' =>_order,
            'COMMENT' =>_comment,
        );

        if (User('PROFILE') == 'admin' && AllowEdit()) {
            $functions += array('GRADE_SCALE_ID' => 'makeGradesInput');
            $LO_columns += array('GRADE_SCALE_ID' =>_gradeScale);
        }

        $link['add']['html'] = array('TITLE' => makeGradesInput('', 'TITLE'), 'BREAK_OFF' => makeGradesInput('', 'BREAK_OFF'), 'GPA_VALUE' => makeGradesInput('', 'GPA_VALUE'), 'UNWEIGHTED_GP' => makeGradesInput('', 'UNWEIGHTED_GP'), 'SORT_ORDER' => makeGradesInput('', 'SORT_ORDER'), 'COMMENT' => makeGradesInput('', 'COMMENT'));
        $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=remove&tab_id=$_REQUEST[tab_id]";
        $link['remove']['variables'] = array('id' => 'ID');
        $link['add']['html']['remove'] = button('add',' ','','','style="cursor: default;"','btn-success');

        if (User('PROFILE') == 'admin')
            $tabs[] = array('title' => button('add',''), 'link' => "Modules.php?modname=$_REQUEST[modname]&tab_id=new");
    }
    else {
        //BJJ modifications to $functions array and $LO_columns array to handle scale value GP_SCALE
        $sql = 'SELECT * FROM report_card_grade_scales WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' ORDER BY SORT_ORDER';
        $functions = array('TITLE' => 'makeTextInput', 'GP_SCALE' => 'makeTextInput', 'COMMENT' => 'makeTextInput', 'GPA_CAL' => 'makeCheckInput', 'SORT_ORDER' => 'makeTextInput');
        $LO_columns = array('TITLE' =>_gradeScale,
         'GP_SCALE' =>_scaleValue,
         'COMMENT' =>_comment,
         'GPA_CAL' =>_calculateGpa,
         'SORT_ORDER' =>_sortOrder,
    );

        $link['add']['html'] = array('TITLE' => makeTextInput('', 'TITLE'), 'GP_SCALE' => makeTextInput('', 'GP_SCALE'), 'COMMENT' => makeTextInput('', 'COMMENT'), 'GPA_CAL' => makeCheckInput('', 'GPA_CAL'), 'SORT_ORDER' => makeTextInput('', 'SORT_ORDER'));
        $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=remove&tab_id=new";
        $link['remove']['variables'] = array('id' => 'ID');
        $link['add']['html']['remove'] = button('add',' ','','','style="cursor: default;"','btn-success');

        $tabs[] = array('title' => button('white_add','','','','','btn-white'), 'link' => "Modules.php?modname=$_REQUEST[modname]&tab_id=new");
    }
    $LO_ret = DBGet(DBQuery($sql), $functions);
    $LO = DBGet(DBQuery($sql));
    $grade_id_arr = array();
    foreach ($LO as $ti => $td) {
        array_push($grade_id_arr, $td[ID]);
    }
    $grade_id = implode(',', $grade_id_arr);

    echo "<FORM name=F1 class=\"form-horizontal\" id=F1 action=Modules.php?modname=$_REQUEST[modname]&modfunc=update&tab_id=$_REQUEST[tab_id] method=POST>";
    if ($id != 'new')
        echo '<input type="hidden" name="h1" id="h1" value="' . $grade_id . '">';
    else {
        echo '<input type="hidden" name="h1" id="h1" value="0">';
    }

    //PopTable_wo_header('header');
    echo '<div class="panel panel-default">';
    echo '<div class="tabbable">' . WrapTabs($tabs, "Modules.php?modname=$_REQUEST[modname]&tab_id=$_REQUEST[tab_id]") . '</div>';
    echo '<div id="div_margin" class="panel-body">';
    echo '<div id="students" class="tab-content">';
    echo '<div class="table-responsive">';
    ListOutputMod($LO_ret, $LO_columns, '', '', $link, array(), array('count' =>false, 'download' =>false, 'search' =>false));
    echo '</div>'; //.table-responsive
    echo '</div>'; //.tab-content
    
    echo '</div>'; //.panel-body
    if(UserProfileID() != '2')
    {
        echo '<div class="panel-footer p-r-20 text-right">' . SubmitButton(_save, '', 'id="setupGradesBtn" class="btn  btn-primary" onclick="formcheck_grade_grade(this);"') . '</div>';
    }
    echo '</div>'; //.panel
    echo '</FORM>';
}

function makeGradesInput($value, $name) {
    global $THIS_RET, $grade_scale_select, $teacher_id, $config_RET;
    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    if ($name == 'GRADE_SCALE_ID')
        return SelectInput($value, "values[$id][$name]", '', $grade_scale_select, false);
    elseif ($name == 'COMMENT')
        $extra = 'size=15 maxlength=50';
    elseif ($name == 'GPA_VALUE')
        $extra = 'size=5 maxlength=5';
    elseif ($name == 'SORT_ORDER') {
        if ($id == "new" || $_REQUEST['tab_id'] == "new" || $THIS_RET['SORT_ORDER'] == '')
            $extra = ' size=5 maxlength=5 onkeydown="return numberOnlyMod(event,this);" ';
        else
            $extra = ' size=5 maxlength=5 onkeydown=\"return numberOnlyMod(event,this);\"';
    }
    elseif ($name == 'BREAK_OFF' && $teacher_id && rtrim($config_RET[UserCoursePeriod() . '-' . $THIS_RET['ID']][1]['VALUE'], '_' . UserCoursePeriod()) != '') {
        $break_off = $config_RET[UserCoursePeriod() . '-' . $THIS_RET['ID']][1]['VALUE'];
        $break_off = explode('_', $break_off);
        if (count($break_off) > 1)
            return '<FONT color=blue>' . $break_off[0] . '</FONT>';
        else
            return '<FONT color=blue>' . $break_off . '</FONT>';
    }
    else {
        if ($name == 'TITLE') {
            $extra = "size=15 maxlength=15";
            if ($id == 'new')
                $extra .= ' id=sc_title';
        }
        elseif ($name == 'BREAK_OFF') {
            $extra = "size=15 maxlength=15";
            if ($id == 'new')
                $extra .= ' id=break_off  onkeydown="return numberOnly(event);"';
            else
                $extra = ' onkeydown=\"return numberOnly(event);\"';
        }
    }

    return TextInput($value, "values[$id][$name]", '', $extra);
}

function makeTextInput($value, $name) {
    global $THIS_RET;

    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';
    //bjj adding 'GP_SCALE'
    if ($name == 'TITLE') {
        $extra = 'size=15 maxlength=25';
        if ($id == 'new')
            $extra .=' id=title ';
    }
    elseif ($name == 'GP_SCALE') {
        $extra = 'size=5 maxlength=5';
        if ($id == 'new')
            $extra .=' id=gp_scale ';
    }
    elseif ($name == 'COMMENT')
        $extra = 'size=15 maxlength=100';
    elseif ($name == 'SORT_ORDER') {
        if ($id == 'new' || $THIS_RET['SORT_ORDER'] == '')
            $extra = 'size=5 maxlength=5 onkeydown="return numberOnly(event);"';
        else
            $extra = 'size=5 maxlength=5 onkeydown=\"return numberOnly(event);\"';
    }
    return TextInput($value, "values[$id][$name]", '', $extra);
}

//////////////////////////////////////////// Validation Start //////////////////////////////////////////////////
function makeCheckInput($value, $name) {
    global $THIS_RET;

    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';
    if ($id == 'new')
        return CheckboxInputWithID('Y', "values[$id][$name]", $name . $id, '', '', $new = false, 'Yes', 'No', '', $extra = '');
    else
        return CheckboxInputWithID($value, "values[$id][$name]", $name . $id, '', '', $new = false, 'Yes', 'No', '', $extra = '');
}

##############################################################################################
?>
