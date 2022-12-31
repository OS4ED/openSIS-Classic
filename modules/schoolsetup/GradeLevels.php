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

if (clean_param($_REQUEST['values'], PARAM_NOTAGS) && ($_POST['values'] || $_REQUEST['ajax'])) {
    foreach ($_REQUEST['values'] as $id => $columns) {
        if ($id != 'new') {
            $sql1 = 'SELECT TITLE,SHORT_NAME,SORT_ORDER FROM school_gradelevels WHERE SCHOOL_ID =\'' . UserSchool() . '\' AND ID!=' . $id;
            $gradelevels =  DBGet(DBQuery($sql1));
            for ($i = 1; $i <= count($gradelevels); $i++) {
                $shortname[$i] = $gradelevels[$i]['SHORT_NAME'];
                $grd_title[$i] = $gradelevels[$i]['TITLE'];
                $sort_order[$i] = $gradelevels[$i]['SORT_ORDER'];
            }
            $sql = 'UPDATE school_gradelevels SET ';
            foreach ($columns as $column => $value) {
                $value = trim(paramlib_validation($column, $value));
                if (($column == 'NEXT_GRADE_ID' || $column == 'SORT_ORDER')  && (str_replace("\'", "''", $value) == '' || str_replace("\'", "''", $value) == 0))
                    $sql .= $column . '=NULL,';
                else
                    $sql .= $column . '=\'' . singleQuoteReplace("'", "''", $value) . '\',';
            }
            $sql = substr($sql, 0, -1) . ' WHERE ID=\'' . $id . '\'';

            DBQuery($sql);
        } else {
            $sql = 'SELECT TITLE,SHORT_NAME,SORT_ORDER FROM school_gradelevels WHERE SCHOOL_ID =\'' . UserSchool() . '\'';
            $gradelevels =  DBGet(DBQuery($sql));
            for ($i = 1; $i <= count($gradelevels); $i++) {
                $shortname[$i] = $gradelevels[$i]['SHORT_NAME'];
                $grd_title[$i] = $gradelevels[$i]['TITLE'];
                $sort_order[$i] = $gradelevels[$i]['SORT_ORDER'];
            }
            if (is_array($grd_title) && in_array($columns['TITLE'], $grd_title)) {
                $err_msg = _titleAlreadyExists;
                break;
            } else {
                if (is_array($shortname) && in_array($columns['SHORT_NAME'], $shortname) && $columns['SHORT_NAME'] != '') {
                    $err_msg = _shortNameAlreadyExists;
                    break;
                } else {
                    if (clean_param(trim($_REQUEST['values']['new']['TITLE']), PARAM_NOTAGS) != '') {
                        $sql = 'INSERT INTO school_gradelevels ';
                        $fields = 'SCHOOL_ID,';
                        $values = '\'' . UserSchool() . '\',';

                        $go = 0;
                        foreach ($columns as $column => $value) {
                            if (trim($value)) {
                                $value = trim(paramlib_validation($column, $value));
                                $fields .= $column . ',';
                                $values .= '\'' . singleQuoteReplace("'", "''", $value) . '\',';
                                $go = true;
                            }
                        }
                        $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';

                        if ($go)
                            DBQuery($sql);
                    }
                }
            }
        }
    }
}
DrawBC("" . _schoolSetup . " > " . ProgramTitle());

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'remove') {
    $grd_id = paramlib_validation($colmn = 'PERIOD_ID', $_REQUEST['id']);
    $has_assigned_RET = DBGet(DBQuery('SELECT COUNT(*) AS TOTAL_ASSIGNED FROM student_enrollment WHERE GRADE_ID=\'' . $grd_id . '\''));
    $has_assigned = $has_assigned_RET[1]['TOTAL_ASSIGNED'];
    if ($has_assigned > 0) {
        UnableDeletePrompt(_cannotDeleteBecauseGradeLevelsAreAssociated . '.');
    } else {
        if (DeletePrompt_GradeLevel('grade level')) {
            DBQuery("DELETE FROM school_gradelevels WHERE ID='$grd_id'");
            DBQuery('UPDATE school_gradelevels SET NEXT_GRADE_ID=NUll WHERE NEXT_GRADE_ID=' . $grd_id);
            unset($_REQUEST['modfunc']);
        }
    }
}
if ($_REQUEST['modfunc'] != 'remove') {
    $sql = 'SELECT ID,TITLE,SHORT_NAME,SORT_ORDER,NEXT_GRADE_ID FROM school_gradelevels WHERE SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER';
    $QI = DBQuery($sql);
    $LO = DBGet(DBQuery($sql));
    $grade_id_arr = array();
    foreach ($LO as $ti => $td) {
        array_push($grade_id_arr, $td['ID']);
    }
    $grade_id = implode(',', $grade_id_arr);
    $grades_RET = DBGet($QI, array('TITLE' => 'makeTextInput', 'SHORT_NAME' => 'makeTextInput', 'SORT_ORDER' => 'makeTextInput', 'NEXT_GRADE_ID' => 'makeGradeInput'));

    $columns = array('TITLE' => _title, 'SHORT_NAME' => _shortName, 'SORT_ORDER' => _sortOrder, 'NEXT_GRADE_ID' => _nextGrade);
    $link['add']['html'] = array('TITLE' => makeTextInput('', 'TITLE'), 'SHORT_NAME' => makeTextInput('', 'SHORT_NAME'), 'SORT_ORDER' => makeTextInputMod2('', 'SORT_ORDER'), 'NEXT_GRADE_ID' => makeGradeInput('', 'NEXT_GRADE_ID'));
    $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=remove";
    $link['remove']['variables'] = array('id' => 'ID');
    if ($err_msg) {
        echo '<div class="alert bg-danger alert-styled-left">';
        echo '<button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">' . _close . '</span></button>' . $err_msg . '</div>';

        unset($err_msg);
    }
    echo "<FORM name=F1 id=F1 action=Modules.php?modname=" . strip_tags(trim($_REQUEST['modname'])) . "&modfunc=update method=POST>";
    echo '<div class="panel panel-white">';
    echo '<input type="hidden" name="h1" id="h1" value="' . $grade_id . '">';
    ListOutput($grades_RET, $columns, _gradeLevel, _gradeLevels, $link, true, array('search' => false));
    if (AllowEdit()) {
        echo '<hr class="no-margin"/><div class="panel-body text-right"><INPUT id="setupGradeLvlBtn" class="btn btn-primary" type=submit value=' . _save . ' onclick="formcheck_school_setup_grade_levels(this);"></div>';
    }
    echo '</div>';
    echo '</FORM>';
}

function makeTextInput($value, $name)
{
    global $THIS_RET;

    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    if ($name != 'TITLE')
        $extra = 'size=5 maxlength=5 placeholder=' . ucwords(strtolower(str_replace('_', ' ', $name))) . ' class=form-control';
    else
        $extra = 'class=form-control placeholder=' . ucwords(strtolower(str_replace('_', ' ', $name))) . ' ';

    if ($name == 'SORT_ORDER') {
        if ($id == 'new')
            $extra = 'size=5 maxlength=5 class=form-control placeholder=' . ucwords(strtolower(str_replace('_', ' ', $name))) . ' onKeyDown="return numberOnly(event);"';
        else
            $extra = 'size=5 maxlength=5 class=form-control placeholder=' . ucwords(strtolower(str_replace('_', ' ', $name))) . ' onKeyDown=\"return numberOnly(event);\"';

        $comment = '<!-- ' . $value . ' -->';
    }

    return $comment . TextInput($value, 'values[' . $id . '][' . $name . ']', '', $extra);
}

function makeTextInputMod1($value, $name)
{
    global $THIS_RET;
    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    if ($name != 'TITLE')
        $extra = 'size=5 maxlength=2 placeholder=' . ucwords(strtolower(str_replace('_', ' ', $name))) . ' class=form-control';
    else
        $extra = 'class=form-control ';
    if ($name == 'SORT_ORDER')
        $comment = '<!-- ' . $value . ' -->';

    return $comment . TextInput($value, 'values[' . $id . '][' . $name . ']', '', $extra);
}

function makeTextInputMod2($value, $name)
{
    global $THIS_RET;
    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    if ($name != 'TITLE')
        $extra = 'size=5 maxlength=5 class=form-control placeholder=' . ucwords(strtolower(str_replace('_', ' ', $name))) . ' ';
    else
        $extra = 'class=form-control placeholder=' . ucwords(strtolower(str_replace('_', ' ', $name))) . ' ';
    if ($name == 'SORT_ORDER') {
        if ($id == 'new')
            $extra = 'size=5 maxlength=5 class=form-control placeholder=' . ucwords(strtolower(str_replace('_', ' ', $name))) . ' onKeyDown="return numberOnly(event);"';
        else
            $extra = 'size=5 maxlength=5 class=form-control placeholder=' . ucwords(strtolower(str_replace('_', ' ', $name))) . ' onKeyDown=\"return numberOnly(event);\"';

        $comment = '<!-- ' . $value . ' -->';
    }
    return $comment . TextInput($value, 'values[' . $id . '][' . $name . ']', '', $extra);
}


function makeGradeInput($value, $name)
{
    global $THIS_RET, $grades;

    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    if (!$grades) {
        $grades_RET = DBGet(DBQuery('SELECT ID,TITLE FROM school_gradelevels WHERE SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER'));
        if (count($grades_RET)) {
            foreach ($grades_RET as $grade)
                $grades[$grade['ID']] = $grade['TITLE'];
        }
    }

    return SelectInput($value, 'values[' . $id . '][' . $name . ']', '', $grades, 'N/A');
}
