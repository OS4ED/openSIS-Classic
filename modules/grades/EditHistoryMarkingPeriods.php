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
if ($_REQUEST['modfunc'] == 'update') {
    foreach ($_REQUEST['year_values'] as $id => $column) {
        foreach ($column as $colname => $colvalue) {
            if ($_REQUEST['day_values'][$id][$colname] &&
                    $_REQUEST['month_values'][$id][$colname] &&
                    $_REQUEST['year_values'][$id][$colname])
                $_REQUEST['values'][$id][$colname] = $_REQUEST['day_values'][$id][$colname] . '-' . $_REQUEST['month_values'][$id][$colname] . '-' . $_REQUEST['year_values'][$id][$colname];
        }
    }
    foreach (sqlSecurityFilter($_REQUEST['values']) as $id => $columns) {
        if ($id != 'new') {

            $sql = 'UPDATE history_marking_periods SET ';

            foreach ($columns as $column => $value)
            {
                if($column == 'POST_END_DATE')
                {
                    $sql .= $column . '=\'' . date("Y-m-d", strtotime($value)) . '\',';
                }
                else
                {
                    $sql .= $column . '=\'' . str_replace("\'", "''", trim($value)) . '\',';
                }
            }

            if ($_REQUEST['tab_id'] != 'new')
                $sql = substr($sql, 0, -1) . ' WHERE MARKING_PERIOD_ID=\'' . $id . '\'';
            else
                $sql = substr($sql, 0, -1) . ' WHERE MARKING_PERIOD_ID=\'' . $id . '\'';
            DBQuery($sql);
        }
        else {

            DBQuery('INSERT INTO marking_period_id_generator (id)VALUES (NULL)');
            $id_RET = DBGet(DBQuery('SELECT  max(id) AS ID from marking_period_id_generator'));
            $MARKING_PERIOD_ID_VALUE = $id_RET[1]['ID'];
            $sql = 'INSERT INTO history_marking_periods ';
            $fields = 'MARKING_PERIOD_ID, SCHOOL_ID, ';
            $values = $MARKING_PERIOD_ID_VALUE . ", " . UserSchool() . ", ";

            $go = false;
            foreach ($columns as $column => $value)
                if ($value) {
                    $fields .= $column . ',';

                    if($column == 'POST_END_DATE')
                    {
                        $values .= '\'' . date("Y-m-d", strtotime($value)) . '\',';
                    }
                    else
                    {
                        $values .= '\'' . str_replace("\'", "''", $value) . '\',';
                    }

                    $go = true;
                }
            $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';

            if ($go && trim($columns['NAME']))
                DBQuery($sql);
        }
    }
    unset($_REQUEST['modfunc']);
}
if ($_REQUEST['modfunc'] == 'remove') {
    if (DeletePromptX(_historyMarkingPeriod)) {
        DBQuery('DELETE FROM history_marking_periods WHERE MARKING_PERIOD_ID=\'' . $_REQUEST['id'] . '\'');
    }
}

if (!$_REQUEST['modfunc']) {
    echo "<FORM action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=update&tab_id=" . strip_tags(trim($_REQUEST['tab_id'])) . "&mp_id=$mp_id method=POST>";
    DrawHeader(ProgramTitle(), SubmitButton(_save, '', 'class="btn btn-primary" onclick="self_disable(this);"'));
    echo '<hr class="no-margin"/>';

    $sql = 'SELECT * FROM history_marking_periods WHERE SCHOOL_ID = ' . UserSchool() . ' ORDER BY SYEAR, POST_END_DATE';

    $functions = array('MP_TYPE' => 'makeSelectInput',
        'NAME' => 'makeTextInput',
        'POST_END_DATE' => 'makeDateInput',
        'SYEAR' => 'makeSchoolYearSelectInput'
    );
    $LO_columns = array('MP_TYPE' =>_type,
        'NAME' =>_name,
        'POST_END_DATE' =>_gradePostDate,
        'SYEAR' =>_schoolYear,
    );
    $link['add']['html'] = array('MP_TYPE' => makeSelectInput('', 'MP_TYPE'),
        'NAME' => makeTextInput('', 'NAME'),
        'POST_END_DATE' => makeDateInput('', 'POST_END_DATE'),
        'SYEAR' => makeSchoolYearSelectInput('', 'SYEAR')
    );


    $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=remove"; //&mp_id=$mp_id";
    $link['remove']['variables'] = array('id' => 'MARKING_PERIOD_ID');
    $link['add']['html']['remove'] = button('add');
    $LO_ret = DBGet(DBQuery($sql), $functions);

    echo '<div class="panel-body no-padding">';
    ListOutput($LO_ret, $LO_columns,  _historyMarkingPeriod, _historyMarkingPeriods, $link, array(), array('count' =>true, 'download' =>true, 'search' =>false));
    echo '</div>';
    echo '<div class="panel-footer p-r-20 text-right">' . SubmitButton(_save, '', 'class="btn btn-primary" onclick="self_disable(this);"') . '</div>';
    echo '</FORM>';
}

echo '</div>';

function makeTextInput($value, $name) {
    global $THIS_RET;

    if ($THIS_RET['MARKING_PERIOD_ID'])
        $id = $THIS_RET['MARKING_PERIOD_ID'];
    else
        $id = 'new';


    $extra = 'size=20 maxlength=28';

    return TextInput($value, "values[$id][$name]", '', $extra);
}

function makeDateInput($value, $name) {
    global $THIS_RET;

    if ($THIS_RET['MARKING_PERIOD_ID'])
        $id = $THIS_RET['MARKING_PERIOD_ID'];
    else
        $id = 'new';

    if ($value == '0000-00-00' || $value == '' || $value == NULL)
        $value = '';

    if ($id != 'new')
        return DateInputAY($value, "values[$id][$name]", $id);
    else
        return DateInputAY($value, "values[$id][$name]", 0);
}

function makeSelectInput($value, $name) {
    global $THIS_RET;

    if ($THIS_RET['MARKING_PERIOD_ID'])
        $id = $THIS_RET['MARKING_PERIOD_ID'];
    else
        $id = 'new';

    $options = array('year' => _year,
     'semester' => _semester,
     'quarter' => _quarter,
    );

    return SelectInput(trim($value), "values[$id][$name]", '', $options, false);
}

function makeSchoolYearSelectInput($value, $name) {
    global $THIS_RET;

    if ($THIS_RET['MARKING_PERIOD_ID'])
        $id = $THIS_RET['MARKING_PERIOD_ID'];
    else
        $id = 'new';
    $options = array();

    $get_min_year = DBGet(DBQuery('SELECT MIN(`syear`) AS MIN_SYEAR FROM `school_years` WHERE `school_id` = \'' . UserSchool() . '\''));
    $least_available_syear = $get_min_year[1]['MIN_SYEAR'];

    foreach (range($least_available_syear - 15, UserSyear()) as $year)
        $options[$year] = $year . '-' . ($year + 1);

    return SelectInput(trim($value), "values[$id][$name]", '', $options, false);
}

function formatSyear($value) {
    return substr($value, 2) . '-' . substr($value + 1, 2);
}

?>
