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
error_reporting(0);
include('../../RedirectModulesInc.php');
if ($_REQUEST['month_values'] && ($_POST['month_values'] || $_REQUEST['ajax'])) {
    foreach ($_REQUEST['month_values'] as $id => $columns) {
        foreach ($columns as $column => $value) {
            $_REQUEST['values'][$id][$column] = $_REQUEST['day_values'][$id][$column] . '-' . $value . '-' . $_REQUEST['year_values'][$id][$column];
            if ($_REQUEST['values'][$id][$column] == '--')
                $_REQUEST['values'][$id][$column] = '';
        }
    }
    $_POST['values'] = $_REQUEST['values'];
}

$err = '';
if ($_REQUEST['values'] && ($_POST['values'] || $_REQUEST['ajax'])) {
    foreach ($_REQUEST['values'] as $id => $columns) {
        $title = '';
        if ($id != 'new') {
            $cnt = 0;
            if ($_REQUEST['values'][$id]['START_DATE']) {
                $check = $_REQUEST['values'][$id]['START_DATE'];
            } else {
                $check_date = DBGet(DBQuery('SELECT * FROM eligibility_activities WHERE ID=\'' . $id . '\''));
                $check_date = $check_date[1];
                $check = $check_date['START_DATE'];
            }
            if ($_REQUEST['values'][$id]['END_DATE']) {
                $check1 = $_REQUEST['values'][$id]['END_DATE'];
            } else {
                $check_date1 = DBGet(DBQuery('SELECT * FROM eligibility_activities WHERE ID=\'' . $id . '\''));
                $check_date1 = $check_date1[1];
                $check1 = $check_date1['END_DATE'];
            }
            $days = floor((strtotime($check1, 0) - strtotime($check, 0)) / 86400);

            $sql = 'UPDATE eligibility_activities SET ';
            foreach ($columns as $column => $value) {
                if ($column == 'TITLE') {
                    $value = str_replace("'", "\'", clean_param(trim($value), PARAM_SPCL));
                    $title = strtoupper(str_replace("'", "\'", clean_param($value, PARAM_SPCL)));
                    if ($title == '') {
                        $err = '<div class="alert bg-danger alert-styled-left">'._cannotAddActivityWithBlankTitle.'.</div>';
                        $cnt = 1;
                    }
                }
                if ($column == 'START_DATE') {
                    $value1 = explode('-', $value);

                    $value = $value1[2] . '-' . $value1[1] . '-' . $value1[0];
                    $s_date1 = strtotime($value);
                }
                if ($column == 'END_DATE') {
                    $value1 = explode('-', $value);

                    $value = $value1[2] . '-' . $value1[1] . '-' . $value1[0];
                    $e_date1 = strtotime($value);
                } else {

                    $value = clean_param($value, PARAM_SPCL);
                }
                $sql .= $column . '=\'' . str_replace("\'", "''", trim($value)) . '\',';
            }
            $sql = substr($sql, 0, -1) . ' WHERE ID=\'' . $id . '\'';

            $check_rec = DBGet(DBQuery('SELECT COUNT(*) as REC_EX FROM eligibility_activities WHERE UPPER(TITLE)=\'' . $title . '\' AND ID!=\'' . $id . '\' AND SYEAR=\'' . UserSyear() . '\'  AND SCHOOL_ID=\'' . UserSchool() . '\''));



            if ($s_date1 == '' || $e_date1 == '') {
                $err = '<div class="alert bg-danger alert-styled-left">'._startDateOrEndDateCannotBeBlankTitle.'.</div>';
                $cnt = 1;
            }
            if ($s_date1 > $e_date1) {
                $err = '<div class="alert bg-danger alert-styled-left">'._endDateMustBeGreaterThanBeginDate.'.</div>';
                $cnt = 1;
            }
            if ($cnt == 0) {
                if ($check_rec[1]['REC_EX'] == 0) {
                    DBQuery($sql);
                } else {
                    $err = '<div class="alert bg-danger alert-styled-left">'._cannotAddActivityWithSameTitle.'.</div>';
                }
            }
        } else {
            $cnt = 0;
            $sql = 'INSERT INTO eligibility_activities ';

            $fields = 'SCHOOL_ID,SYEAR,';
            $values = '\'' . UserSchool() . '\',\'' . UserSyear() . '\',';

            $go = 0;
            foreach ($columns as $column => $value) {
                if ($column == 'TITLE') {
                    $value = str_replace("'", "\'", clean_param($value, PARAM_SPCL));
                    $title = strtoupper(str_replace("'", "\'", clean_param($value, PARAM_SPCL)));
                }
                if ($column == 'START_DATE') {
//                    echo $value;
//                    $value1=explode('/',$value);
//                   echo $value=$value1[2].'-'.$value1[0].'-'.$value1[1];
                    $s_date = strtotime($value);
                }
                if ($column == 'END_DATE') {
//                     echo $value;
//                    $value1=explode('/',$value);
//                   echo $value=$value1[2].'-'.$value1[0].'-'.$value1[1];
                    $e_date = strtotime($value);
                }
                if (trim($value)) {
                    if ($column == 'END_DATE' || $column == 'START_DATE') {

                        $value1 = explode('-', $value);

                        $value = $value1[2] . '-' . $value1[1] . '-' . $value1[0];
                    }
                    $fields .= $column . ',';
                    $values .= '\'' . str_replace("\'", "''", trim($value)) . '\',';
                    $go = true;
                }

                if ($title == '' && ($s_date != '' && $e_date == '') || ($s_date == '' && $e_date != '')) {
                    $err = '<div class="alert bg-danger alert-styled-left">'._cannotAddActivityWithBlankTitle.'.</div>';
                    $cnt = 1;
                }
            }
            $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';

            if ($go) {
                $check_rec = DBGet(DBQuery('SELECT COUNT(*) as REC_EX FROM eligibility_activities WHERE UPPER(TITLE)=\'' . $title . '\' AND SYEAR=\'' . UserSyear() . '\'  AND SCHOOL_ID=\'' . UserSchool() . '\''));
                if ($s_date == '' || $e_date == '' && $title != '') {
                    $err = '<div class="alert bg-danger alert-styled-left">'._startDateOrEndDateCannotBeBlank.'.</div>';
                    $cnt = 1;
                }
                if ($s_date > $e_date && $s_date != '' && $e_date != '') {
                    $err = '<div class="alert bg-danger alert-styled-left">'._endDateMustBeGreaterThanBeginDate.'.</div>';
                    $cnt = 1;
                }

                if ($cnt == 0) {
                    if ($check_rec[1]['REC_EX'] == 0) {

                        DBQuery($sql);
                    } else
                        $err = '<div class="alert bg-danger alert-styled-left">'._cannotAddActivityWithSameTitle.'.</div>';
                }
            }
        }
    }
}
if (isset($err) && $err != '') {
    echo $err;
    unset($err);
}
DrawBC(""._extracurricular." > " . ProgramTitle());


if (optional_param('modfunc', '', PARAM_NOTAGS) == 'remove') {
    $has_assigned_RET = DBGet(DBQuery('SELECT COUNT(*) AS TOTAL_ASSIGNED FROM student_eligibility_activities WHERE ACTIVITY_ID=\'' . $_REQUEST['id'] . '\''));
    $has_assigned = $has_assigned_RET[1]['TOTAL_ASSIGNED'];
    if ($has_assigned > 0) {
        UnableDeletePrompt(_cannotDeleteBecauseEligibilityActivitiesAreAssociated);
    } else {
        if (DeletePrompt_activity('activity')) {
            DBQuery('DELETE FROM eligibility_activities WHERE ID=\'' . $_REQUEST['id'] . '\'');
            unset($_REQUEST['modfunc']);
        }
    }
}

if ($_REQUEST['modfunc'] != 'remove') {
    $sql = 'SELECT ID,TITLE,START_DATE,END_DATE FROM eligibility_activities WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY TITLE';
    $QI = DBQuery($sql);
    $activities_RET = DBGet($QI, array('TITLE' => 'makeTextInput'));
    $last_id = 0;
    $ids = $activities_RET[1]['ID'];
    foreach ($activities_RET as $ari => $ard) {

        if ($ari != 1)
            $ids = $ids + 1;
        $activities_RET[$ari]['START_DATE'] = makeDateInput($activities_RET[$ari]['START_DATE'], 'START_DATE', $ids, $ard['ID']);
        $ids = $ids + 1;
        $activities_RET[$ari]['END_DATE'] = makeDateInput($activities_RET[$ari]['END_DATE'], 'END_DATE', $ids, $ard['ID']);
    }
    $columns = array('TITLE' =>_title,
     'START_DATE' =>_begins,
     'END_DATE' =>_ends,
    );
    $link['add']['html'] = array('TITLE' => makeTextInput('', 'TITLE'), 'START_DATE' => makeDateInput('', 'START_DATE', $ids + 1), 'END_DATE' => makeDateInput('', 'END_DATE', $ids + 2));
    $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=remove";
    $link['remove']['variables'] = array('id' => 'ID');

    echo "<FORM class=\"no-margin\" name=F1 id=F1 action=Modules.php?modname=" . optional_param('modname', '', PARAM_NOTAGS) . "&modfunc=update method=POST>";

    foreach ($activities_RET as $ci => $cd) {
        $id_arr[$cd['ID']] = $cd['ID'];
    }
    if (is_countable($id_arr) && count($id_arr) > 0)
        $id_arr = implode(',', $id_arr);
    else
        $id_arr = 0;
    echo '<input type=hidden id=id_arr value="' . $id_arr . '" />';

    echo '<div class="panel panel-default">';
    echo '<div class="panel-body p-0">';
    echo '<div class="table-responsive">';
    ListOutput($activities_RET, $columns,  _activity, _activities, $link);
    echo '</div>';
    echo '</div>'; //.panel-body
    echo '<div class="panel-footer text-right p-r-20">'.SubmitButton(_save, '', 'class="btn btn-primary" onclick="self_disable(this);"').'</div>';
    echo '</div>'; //.panel
    echo '</FORM>';
}

function makeTextInput($value, $name) {
    global $THIS_RET;

    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    return TextInput($value, 'values[' . $id . '][' . $name . ']', '', 'class=form-control maxlength=20');
}

function makeDateInput($value, $name, $optional_value = 0, $id = 'new') {

    return DateInputAY($value, 'values[' . $id . '][' . $name . ']', $optional_value);
}

?>
