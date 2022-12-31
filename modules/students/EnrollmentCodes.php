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
if (clean_param($_REQUEST['values'], PARAM_NOTAGS) && ($_POST['values'] || $_REQUEST['ajax'])) {
    foreach ($_REQUEST['values'] as $id => $columns) {
        $go = false;
        $error = false;
        if ($id != 'new') {
            $select_enroll = DBGet(DBQuery('SELECT TYPE FROM student_enrollment_codes WHERE ID=\'' . $id . '\''));

            $sql = 'UPDATE student_enrollment_codes SET ';

            foreach ($columns as $column => $value) {
                if (($select_enroll[1]['TYPE'] == 'Roll' || $select_enroll[1]['TYPE'] == 'TrnD' || $select_enroll[1]['TYPE'] == 'TrnE' || $value == 'Roll' || $value == 'TrnD' || $value == 'TrnE') && $column == 'TYPE') {
                    $error = true;
                    continue;
                }
                $value = paramlib_validation($column, trim($value));
                $sql .= $column . '=\'' . str_replace("'", "''", $value) . ' \',';
                $go = true;
            }
            $sql = substr($sql, 0, -1) . ' WHERE ID=\'' . $id . '\'';
            if ($go)
                DBQuery($sql);

            if ($error) {
                ShowErrPhp(_canTEditTypeBecauseItIsNotEditable);
            }
        } else {
            if ($columns['TYPE'] != 'Roll' && $columns['TYPE'] != 'TrnD' && $columns['TYPE'] != 'TrnE') {
                $sql = 'INSERT INTO student_enrollment_codes ';

                $fields = 'SYEAR,';
                $values = '\'' . UserSyear() . '\',';

                $go = 0;
                foreach ($columns as $column => $value) {
                    if (trim($value)) {
                        $value = paramlib_validation($column, $value);
                        $fields .= $column . ',';
                        $values .= '\'' . str_replace("'", " \'", $value) . '\',';
                        $go = true;
                    }
                }
                $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';

                if ($go)
                    DBQuery($sql);
            }
            else {
                ShowErrPhp(_youCanTAddAnyEnrollmentCodeInThisType);
            }
        }
    }
}

DrawBC(""._students." > " . ProgramTitle());

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'remove') {
    $select_enroll = DBGet(DBQuery('SELECT TYPE FROM student_enrollment_codes WHERE ID=\'' . $_REQUEST['id'] . '\''));

    if ($select_enroll[1]['TYPE'] != 'Roll' && $select_enroll[1]['TYPE'] != 'TrnD' && $select_enroll[1]['TYPE'] != 'TrnE') {
        $has_assigned_RET = DBGet(DBQuery('SELECT COUNT(*) AS TOTAL_ASSIGNED FROM student_enrollment WHERE  ENROLLMENT_CODE=\'' . $_REQUEST['id'] . '\''));
        $has_assigned = $has_assigned_RET[1]['TOTAL_ASSIGNED'];
        if ($has_assigned > 0) {
            UnableDeletePrompt(_cannotDeleteBecauseEnrollmentCodesAreAssociated);
        } else {
            if (DeletePromptMod('enrollment code', $_REQUEST['modname'])) {
                DBQuery('DELETE FROM student_enrollment_codes WHERE ID=\'' . $_REQUEST['id'] . '\'');
                unset($_REQUEST['modfunc']);
            }
        }
    } else {
        UnableDeletePrompt(_cannotDeleteBecauseItIsNotDeletable);
    }
}

if ($_REQUEST['modfunc'] != 'remove') {
    $sql = 'SELECT ID,TITLE,SHORT_NAME,TYPE FROM student_enrollment_codes WHERE SYEAR=\'' . UserSyear() . '\'  ORDER BY TITLE';
    $QI = DBQuery($sql);
    $codes_RET = DBGet($QI, array('TITLE' => 'makeTextInput', 'SHORT_NAME' => 'makeTextInput', 'TYPE' => 'makeSelectInput'));

    $columns = array('TITLE' =>_title,
     'SHORT_NAME' =>_shortName,
     'TYPE' =>_type,
    );
    $link['add']['html'] = array('TITLE' => makeTextInput('', 'TITLE'), 'SHORT_NAME' => makeTextInput('', 'SHORT_NAME'), 'TYPE' => makeSelectInput('', 'TYPE'));
    $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=remove";

    $link['remove']['variables'] = array('id' => 'ID');

    echo "<FORM name=F1 id=F1 action=Modules.php?modname=$_REQUEST[modname]&modfunc=update method=POST>";

    echo '<div class="panel panel-default">';
    ListOutput($codes_RET, $columns,  _enrollmentCode, _enrollmentCodes, $link);
    foreach ($codes_RET as $ci => $cd) {
        $id_arr[$cd['ID']] = $cd['ID'];
    }
    if (count($id_arr) > 0)
        $id_arr = implode(',', $id_arr);
    else
        $id_arr = 0;
    echo '<input type=hidden id=id_arr value="' . $id_arr . '" />';
    echo '<hr class="no-margin" />';
    echo '<div class="panel-body text-right">' . SubmitButton(_save, '', 'id="setupEncCodeBtn" class="btn btn-primary" onClick=formcheck_enrollment_code(this);') . '</div>';
    echo '</div>'; //.panel
    echo '</FORM>';
}

function makeTextInput($value, $name) {
    global $THIS_RET;

    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    if ($name == 'SHORT_NAME')
        $extra = 'size=5 maxlength=10 class=cell_floating id=stu_short_' . $id;
    else
        $extra = 'class=cell_floating';

    return TextInput($value, 'values[' . $id . '][' . $name . ']', '', $extra);
}

function makeSelectInput($value, $name) {
    global $THIS_RET;

    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    if ($name == 'TYPE')
        $options = array('Add' => _add,
         'Drop' => _drop,
         'Roll' => _rollOver,
         'TrnD' => _dropTransfer,
         'TrnE' => _enrollTransfer,
        );

    return SelectInput($value, 'values[' . $id . '][' . $name . ']', '', $options);
}

function makeCheckBoxInput($value, $name) {
    global $THIS_RET;

    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    return CheckBoxInput($value, 'values[' . $id . '][' . $name . ']');
}

?>
