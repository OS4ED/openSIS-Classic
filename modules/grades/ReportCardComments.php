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
include 'modules/grades/DeletePromptX.fnc.php';

echo '<div class="panel panel-default">';

DrawBC(""._gradebook." > " . ProgramTitle());
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'update') {
    if (clean_param($_REQUEST['values'], PARAM_NOTAGS) && ($_POST['values'] || $_REQUEST['ajax'])) {
        if ($_REQUEST['tab_id'] != '') {
            foreach ($_REQUEST['values'] as $id => $columns) {
                if ($id != 'new') {
                    $sql = 'UPDATE report_card_comments SET ';

                    foreach ($columns as $column => $value) {
                        $value = paramlib_validation($column, $value);


//                        if ($column == 'SORT_ORDER') {
//                            $c_id = ($_REQUEST['tab_id'] != 'new' ? "'$_REQUEST[tab_id]'" : 'NULL');
//
//
//                            $ck_sql = 'SELECT * from report_card_comments WHERE  SORT_ORDER=' . $value . ' AND SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear();
//
//
//                            if ($c_id == 'NULL')
//                                $ck_sql.=' AND COURSE_ID is Null';
//                            else
//                                $ck_sql.=' AND COURSE_ID=' . $c_id;
//                            $chk_dup_srt = DBGet(DBQuery($ck_sql));
//                            if (count($chk_dup_srt) > 0) {
//                                echo'<div class="alert bg-danger alert-styled-left"> ID can not be duplicate</div>';
//                                break 2;
//                            }
//                        }
                        $sql .= $column . "='" . str_replace("\'", "''", $value) . "',";
                    }
                    $sql = substr($sql, 0, -1) . ' WHERE ID=\'' . $id . '\'';

                    DBQuery($sql);
                } else {
                    if (clean_param(trim($_REQUEST['values']['new']['TITLE']), PARAM_NOTAGS) != '') {
                        $sql = 'INSERT INTO report_card_comments ';
                        $fields = 'SCHOOL_ID,SYEAR,COURSE_ID,';
                        $values = '\'' . UserSchool() . '\',\'' . UserSyear() . '\',' . ($_REQUEST['tab_id'] != 'new' ? "'$_REQUEST[tab_id]'" : 'NULL') . ',';

                        $go = false;
                        foreach ($columns as $column => $value)
                            if (trim($value) != '') {
                                $value = paramlib_validation($column, $value);

//                                if ($column == 'SORT_ORDER') {
//                                    $c_id = ($_REQUEST['tab_id'] != 'new' ? "'$_REQUEST[tab_id]'" : 'NULL');
//
//
//                                    $ck_sql = 'SELECT * from report_card_comments WHERE  SORT_ORDER=' . $value . ' AND SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear();
//
//
//                                    if ($c_id == 'NULL')
//                                        $ck_sql.=' AND COURSE_ID is Null';
//                                    else
//                                        $ck_sql.=' AND COURSE_ID=' . $c_id;
//                                    $chk_dup_srt = DBGet(DBQuery($ck_sql));
//                                    if (count($chk_dup_srt) > 0) {
//                                        echo'<div class="alert bg-danger alert-styled-left"> ID can not be duplicate</div>';
//                                        break 2;
//                                    }
//                                }
                                $fields .= $column . ',';

                                $values .= ' " ' . str_replace("\'", "'", $value) . ' ",';


                                $go = true;
                            }

                        $sql .= '(' . substr($fields, 0, -1) . ') values( ' . substr($values, 0, -1) . ' )';

                        if ($go)
                            DBQuery($sql);
                    }
                }
            }
        }
    }
    unset($_REQUEST['modfunc']);
}

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'remove') {
    $has_assigned_RET = DBGet(DBQuery('SELECT COUNT(*) AS TOTAL_ASSIGNED FROM student_report_card_comments WHERE REPORT_CARD_COMMENT_ID=\'' . $_REQUEST['id'] . '\''));
    $has_assigned = $has_assigned_RET[1]['TOTAL_ASSIGNED'];

    if ($has_assigned > 0) {
        UnableDeletePromptX(_cannotDeleteBecauseReportCardCommentsAreAssociated.'.');
    } else {
        if ($_REQUEST['tab_id'] != 'new') {
            if (DeletePromptX(_reportCardComment)) {
                DBQuery('DELETE FROM report_card_comments WHERE ID=\'' . $_REQUEST['id'] . '\'');
            }
        } else
        if (DeletePromptX(_reportCardComment)) {
            DBQuery('DELETE FROM report_card_comments WHERE ID=\'' . $_REQUEST['id'] . '\'');
        }
    }
}

if (!$_REQUEST['modfunc']) {
    if (User('PROFILE') == 'admin') {
        $courses_RET = DBGet(DBQuery('SELECT TITLE,COURSE_ID FROM courses WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' AND COURSE_ID IN (SELECT DISTINCT COURSE_ID FROM course_periods WHERE GRADE_SCALE_ID IS NOT NULL) ORDER BY TITLE'));
        if (!$_REQUEST['course_id'])
            $_REQUEST['course_id'] = $courses_RET[1]['COURSE_ID'];

        $course_select = '<SELECT class="form-control" name=course_id onchange="document.location.href=\'Modules.php?modname=' . $_REQUEST['modname'] . '&course_id=\'+this.options[selectedIndex].value">';
        foreach ($courses_RET as $course)
            $course_select .= '<OPTION value=' . $course['COURSE_ID'] . ($_REQUEST['course_id'] == $course['COURSE_ID'] ? ' SELECTED' : '') . '>' . $course['TITLE'] . '</OPTION>';
        $course_select .= '</SELECT>';
    }
    else {
        $course_period_RET = DBGet(DBQuery('SELECT GRADE_SCALE_ID,DOES_BREAKOFF,TEACHER_ID FROM course_periods WHERE COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\''));
        if (!$course_period_RET[1]['GRADE_SCALE_ID'])
            ErrorMessage(array(_thisCourseIsNotGraded), 'fatal');
        $courses_RET = DBGet(DBQuery('SELECT TITLE,COURSE_ID FROM courses WHERE COURSE_ID=(SELECT COURSE_ID FROM course_periods WHERE COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\')'));

        $_REQUEST['course_id'] = $courses_RET[1]['COURSE_ID'];
    }

    if ($_REQUEST['tab_id'] != '0' && $_REQUEST['tab_id'] != 'new')
        $_REQUEST['tab_id'] = $_REQUEST['course_id'];

    $course_RET = DBGet(DBQuery("SELECT TITLE FROM courses WHERE COURSE_ID='$_REQUEST[course_id]'"));
    $tabs = array(1 => array('title' => $course_RET[1]['TITLE'], 'link' => "Modules.php?modname=$_REQUEST[modname]&course_id=$_REQUEST[course_id]&tab_id=$_REQUEST[course_id]"),
        2 => array('title' =>_allCourses, 'link' => "Modules.php?modname=$_REQUEST[modname]&course_id=$_REQUEST[course_id]&tab_id=0"),
        3 => array('title' =>_general, 'link' => "Modules.php?modname=$_REQUEST[modname]&course_id=$_REQUEST[course_id]&tab_id=new"));

    if ($_REQUEST['tab_id'] != 'new') {
        if ($_REQUEST['tab_id'])
            $sql = 'SELECT * FROM report_card_comments WHERE COURSE_ID=\'' . $_REQUEST['tab_id'] . '\' ORDER BY SORT_ORDER';
        else
        // need to be more specific since course_period_id=0 is not unique
            $sql = 'SELECT * FROM report_card_comments WHERE COURSE_ID=\'' . $_REQUEST['tab_id'] . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER';
        $functions = array('TITLE' => 'makeCommentsInput', 'SORT_ORDER' => 'makeCommentsInput');

        $LO_columns = array('TITLE' =>_comment,
         'SORT_ORDER' =>_sortOrder,
        );

        $link['add']['html'] = array('TITLE' => makeCommentsInput('', 'TITLE'), 'SORT_ORDER' => makeCommentsInput('', 'SORT_ORDER'));
        $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=remove&table=report_card_grades";
        $link['remove']['variables'] = array('id' => 'ID');
        $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=remove&tab_id=$_REQUEST[tab_id]&course_id=$_REQUEST[course_id]";
        $link['remove']['variables'] = array('id' => 'ID');
        $link['add']['html']['remove'] = button('add');
    }
    else {
        $sql = 'SELECT * FROM report_card_comments WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' AND COURSE_ID IS NULL ORDER BY SORT_ORDER';
        $functions = array('SORT_ORDER' => 'makeTextInput', 'TITLE' => 'makeTextInput');
        $LO_columns = array('SORT_ORDER' =>_id,
         'TITLE' =>_comment,
        );

        $link['add']['html'] = array('SORT_ORDER' => makeTextInput('', 'SORT_ORDER'), 'TITLE' => makeTextInput('', 'TITLE'));
        $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=remove&tab_id=new";
        $link['remove']['variables'] = array('id' => 'ID');
        $link['add']['html']['remove'] = button('add');
    }
    $LO_ret = DBGet(DBQuery($sql), $functions);
    foreach ($LO_ret as $ld)
        $report_card_comments[] = $ld['ID'];
    if (is_countable($report_card_comments) && count($report_card_comments) > 0)
        $report_card_comments = implode(',', $report_card_comments);
    echo "<FORM name=F1 id=F1 class=\"m-b-0\" action=Modules.php?modname=$_REQUEST[modname]&modfunc=update&course_id=$_REQUEST[course_id]&tab_id=$_REQUEST[tab_id] method=POST>";
        if(UserProfileID() != 2){
        $report_card_comment_submitbutton=SubmitButton(_save, '', 'id="gradeCommentBtnOne" class="btn btn-primary" onclick="formcheck_grade_comment(this);"');
    }else{
        $report_card_comment_submitbutton='';
    }
    DrawHeader(_reportCardComments, '<div class="form-inline"><div class="form-group">' . $course_select . ' &nbsp; ' . $report_card_comment_submitbutton. '</div></div>');
    echo '<input type="hidden" name="comment_ids" id="comment_ids" value="' . $report_card_comments . '">';
    echo '<hr class="no-margin"/>';

    echo '<div class="tabbable">' . WrapTabs($tabs, "Modules.php?modname=$_REQUEST[modname]&course_id=$_REQUEST[course_id]&tab_id=$_REQUEST[tab_id]") . '</div>';

    echo '<div class="panel-body">';
    echo '<div id="div_margin" class="tab-content">';
    echo "<div class=\"table-responsive\">";
    ListOutputMod($LO_ret, $LO_columns, '', '', $link, array(), array('count' =>false, 'download' =>false, 'search' =>false));
    echo "</div>"; //.table-responsive
    echo "</div>"; //.tab-content

    echo '</div>'; //.panel-body
    if(UserProfileID() != '2')
    {
        echo '<div class="panel-footer p-r-20 text-right">' . SubmitButton(_save, '', 'id="gradeCommentBtnTwo" class="btn btn-primary" onclick="formcheck_grade_comment(this);"') . '</div>';
    }
    echo '</FORM>';
}

echo '</div>';

function makeGradesInput($value, $name) {
    global $THIS_RET;

    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    if ($name == 'COMMENT')
        $extra = 'size=15 maxlength=100 class=form-control';
    elseif ($name == 'GPA_VALUE')
        $extra = 'size=5 maxlength=5 class=form-control';
    else
        $extra = 'size=5 maxlength=3 class=form-control';

    return TextInput($value, "values[$id][$name]", '', $extra);
}

function makeTextInput($value, $name) {
    global $THIS_RET;
    $extra = ' maxlength=50';
    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    return TextInput($value, "values[$id][$name]", '', $extra);
}

function makeCommentsInput($value, $name) {
    global $THIS_RET;
    $extra = ' maxlength=50';
    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    if ($name == 'SORT_ORDER') {
        if ($id == 'new')
            $extra = 'size=5 maxlength=5 class=form-control onkeydown="return numberOnly(event);"';
        else
            $extra = 'size=5 maxlength=5 class=form-control onkeydown=\"return numberOnly(event);\"';
    }
    return TextInput($value, "values[$id][$name]", '', $extra);
}

?>
