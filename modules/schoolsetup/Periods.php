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


if (clean_param($_REQUEST['values'], PARAM_NOTAGS) && ($_POST['values'] || $_REQUEST['ajax']) && AllowEdit()) {
    foreach ($_REQUEST['values'] as $id => $columns) {
        if (!(isset($columns['TITLE']) && trim($columns['TITLE']) == '')) {
            if ($columns['START_TIME']) {

                $columns['START_TIME'] = date("H:i", strtotime($columns['START_TIME']));
            }
            if ($columns['END_TIME']) {

                $columns['END_TIME'] = date("H:i", strtotime($columns['END_TIME']));
            }
            ##############################################################################################################

            $not_up = 0;

            if ($id != 'new' && $columns['START_TIME'] != '' && !isset($columns['END_TIME'])) {
                $period_etime = DBGet(DBQuery('SELECT END_TIME FROM  school_periods WHERE period_id=\'' . $id . '\''));
                $period_etime = explode(":", $period_etime[1]['END_TIME']);
                $period_etime = $period_etime[0] . ':' . $period_etime[1];
                if ($columns['START_TIME'] == $period_etime) {
                    $err_msg = _startTimeAndEndTimeCanNotBeSame;
                    break;
                }
            }
            if ($id != 'new' && $columns['END_TIME'] != '' && !isset($columns['START_TIME'])) {
                $period_etime = DBGet(DBQuery('SELECT START_TIME FROM  school_periods WHERE period_id=\'' . $id . '\''));
                $period_etime = explode(":", $period_etime[1]['START_TIME']);
                $period_etime = $period_etime[0] . ':' . $period_etime[1];
                if ($columns['END_TIME'] == $period_etime) {
                    $err_msg = _startTimeAndEndTimeCanNotBeSame;
                    break;
                }
            }
            if ($id != 'new' && $columns['END_TIME'] != '' && $columns['START_TIME'] != '' && $columns['START_TIME'] == $columns['END_TIME']) {
                $err_msg = _startTimeAndEndTimeCanNotBeSame;
                break;
            }
            if ($id != 'new') {
                $exist_pr = DBGet(DBQuery('SELECT * FROM  school_periods WHERE period_id=\'' . $id . '\''));
                if (isset($_REQUEST['values'][$id]['TITLE']) && $_REQUEST['values'][$id]['TITLE'] != '' || isset($_REQUEST['values'][$id]['SHORT_NAME']) && $_REQUEST['values'][$id]['SHORT_NAME'] != '') {

                    $sql = 'SELECT TITLE,SHORT_NAME,SORT_ORDER,START_TIME,END_TIME FROM  school_periods WHERE SYEAR= \'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' and period_id<>\'' . $id . '\'';
                    $periods = DBGET(DBQuery($sql));

                    for ($i = 1; $i <= count($periods); $i++) {
                        $shortname[$i] = strtoupper(str_replace(' ', '', $periods[$i]['SHORT_NAME']));
                        $p_title[$i] = strtoupper(str_replace(' ', '', $periods[$i]['TITLE']));
                        $sort_order[$i] = $periods[$i]['SORT_ORDER'];
                        $st_time[$i] = strtotime($periods[$i]['START_TIME']);
                        $end_time[$i] = strtotime($periods[$i]['END_TIME']);
                    }

                    if (in_array(strtoupper(str_replace(' ', '', $_REQUEST['values'][$id]['TITLE'])), $p_title) || in_array(strtoupper(str_replace(' ', '', $_REQUEST['values'][$id]['SHORT_NAME'])), $shortname)) {
                        $not_up = 1;
                    }
                }
                if ((isset($_REQUEST['values'][$id]['TITLE']) && $_REQUEST['values'][$id]['TITLE'] != $exist_pr[1]['TITLE']) || (isset($_REQUEST['values'][$id]['SHORT_NAME']) && $_REQUEST['values'][$id]['SHORT_NAME'] != $exist_pr[1]['SHORT_NAME']) || (isset($_REQUEST['values'][$id]['SORT_ORDER']) && $_REQUEST['values'][$id]['SORT_ORDER'] != $exist_pr[1]['SORT_ORDER']) || (isset($_REQUEST['values'][$id]['START_TIME']) && strtotime($_REQUEST['values'][$id]['START_TIME']) != strtotime($exist_pr[1]['START_TIME'])) || (isset($_REQUEST['values'][$id]['END_TIME']) && strtotime($_REQUEST['values'][$id]['END_TIME']) != strtotime($exist_pr[1]['END_TIME'])) || (isset($_REQUEST['values'][$id]['IGNORE_SCHEDULING']) && $_REQUEST['values'][$id]['IGNORE_SCHEDULING'] != $exist_pr[1]['IGNORE_SCHEDULING']) || (isset($_REQUEST['values'][$id]['ATTENDANCE']) && $_REQUEST['values'][$id]['ATTENDANCE'] != $exist_pr[1]['ATTENDANCE'])) {
                    $sql = 'UPDATE school_periods SET ';
                    $title_change = '';
                    foreach ($columns as $column => $value) {
                        $value = trim(paramlib_validation($column, $value));
                        if ($column == 'ignore_scheduling' && $value == '') {
                            $sql .= $column . '=NULL';
                            $go = true;
                        } elseif ($column == 'ATTENDANCE') {
                            if ($value == '') {
                                $per_attn_check = DBGet(DBQuery('SELECT COUNT(*) AS TOTAL FROM course_period_var WHERE PERIOD_ID=' . $id . ' AND DOES_ATTENDANCE=\'Y\''));


                                if ($per_attn_check[1]['TOTAL'] > 0) {
                                    $err = _cannotModifyUsedForAttendanceAsPeriodIsAssociated;
                                    $go = false;
                                } else {
                                    $sql .= $column . '=\'' . str_replace("'", "''", str_replace("\'", "'", $value)) . '\',';
                                    $go = true;
                                }
                            } else {
                                $sql .= $column . '=\'' . str_replace("'", "''", str_replace("\'", "'", $value)) . '\',';
                                $go = true;
                            }
                        } elseif (strtolower($column) == 'start_time' || strtolower($column) == 'end_time') {
                            $checker = DBGet(DBQuery('SELECT COUNT(1) as TOTAL FROM schedule s,course_period_var cp WHERE cp.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID AND (s.END_DATE>\'' . date('Y-m-d') . '\' OR s.END_DATE IS NULL) AND cp.PERIOD_ID=\'' . $id . '\' '));
                            if ($checker[1]['TOTAL'] == 0) {
                                $sql .= $column . '=\'' . str_replace("'", "'", str_replace("\'", "'", $value)) . '\',';
                                $go = true;
                            } else {
                                $check_for_change = DBGet(DBQuery('SELECT COUNT(*) AS REC_EX FROM school_periods WHERE PERIOD_ID=' . $id . ' AND ' . $column . '=\'' . $value . '\''));
                                if ($check_for_change[1]['REC_EX'] == 0) {
                                    $err = _cannotModifyStartTimeOrEndTimeAsPeriodIsAssociated;
                                    $go = false;
                                }
                            }
                        } else {
                            if ($column == 'TITLE' && !isset($_REQUEST['values'][$id]['SHORT_NAME'])) {
                                $exist_pr_title = DBGet(DBQuery('SELECT count(PERIOD_ID) AS TOT FROM  school_periods WHERE title=\'' . $value . '\' AND SHORT_NAME=\'' . $exist_pr[1]['SHORT_NAME'] . '\' AND SYEAR= \'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND period_id<>\'' . $id . '\''));

                                if ($exist_pr_title[1]['TOT'] > 0) {

                                    $not_up = 1;
                                }
                            }

                            if ($column == 'SHORT_NAME' && !isset($_REQUEST['values'][$id]['TITLE'])) {
                                $exist_pr_title = DBGet(DBQuery('SELECT count(PERIOD_ID) AS TOT FROM  school_periods WHERE title=\'' . $exist_pr[1]['TITLE'] . '\' AND SHORT_NAME=\'' . $value . '\' AND SYEAR= \'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND period_id<>\'' . $id . '\''));

                                if ($exist_pr_title[1]['TOT'] > 0) {

                                    $not_up = 1;
                                }
                            }
                            if ($column == 'TITLE') {
                                $title_change = str_replace("'", "''", str_replace("\'", "'", $value));
                            }

                            $sql .= $column . '=\'' . str_replace("'", "''", str_replace("\'", "'", $value)) . '\',';
                            $go = true;
                        }
                    }
                    $sql = substr($sql, 0, -1) . ' WHERE PERIOD_ID=\'' . $id . '\'';

                    $sql = str_replace('&amp;', "", $sql);
                    $sql = str_replace('&quot', "", $sql);
                    $sql = str_replace('&#039;', "", $sql);
                    $sql = str_replace('&lt;', "", $sql);
                    $sql = str_replace('&gt;', "", $sql);
                    if ($go && $not_up != 1) {
                        DBQuery($sql);
                        if ($title_change != '') {
                            $check_for_cps = DBGet(DBQuery('SELECT COURSE_PERIOD_ID,TITLE FROM course_periods WHERE COURSE_PERIOD_ID=' . $id));
                            foreach ($check_for_cps as $cpi => $cpd) {
                                $old_title = explode('-', $cpd['TITLE']);
                                $old_title[0] = $title_change;
                                $old_title = implode(' - ', $old_title);
                                $old_title = str_replace("'", "''", str_replace("\'", "''", $old_title));
                                DBQuery('UPDATE course_periods SET TITLE=\'' . $old_title . '\' WHERE COURSE_PERIOD_ID=' . $cpd['COURSE_PERIOD_ID']);
                            }
                        }
                    }
                    if ($not_up == 1) {
                        $err_msg = _alreadyAPeriodIsCreatedWithSameTitleOrShortname;
                    }

                    # -------------------------- Length Update Start -------------------------- #

                    $sql_get_length = 'SELECT start_time, end_time from school_periods WHERE period_id=\'' . $id . '\'';

                    $row_get_length = DBGet(DBQuery($sql_get_length));
                    $start_time = strtotime(date('m/d/Y') . ' ' . $row_get_length[1]['START_TIME']);
                    $end_time = strtotime(date('m/d/Y') . ' ' . $row_get_length[1]['END_TIME']);
                    if ($start_time > $end_time)
                        $end_time = strtotime(date('m/d/Y') . ' ' . $row_get_length[1]['END_TIME']) + 86400;

                    $length = ($end_time - $start_time) / 60;

                    $sql_length_update = 'UPDATE school_periods set length = ' . $length . ' where period_id=\'' . $id . '\'';
                    $res_length_update = DBQuery($sql_length_update);

                    # --------------------------- Length Update End --------------------------- #
                }
            } else {

                $sql = 'SELECT TITLE,SHORT_NAME,SORT_ORDER,START_TIME,END_TIME FROM  school_periods WHERE SYEAR= \'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\'';
                $periods = DBGET(DBQuery($sql));
                //$end_time = array();
                for ($i = 1; $i <= count($periods); $i++) {
                    $shortname[$i] = strtoupper(str_replace(' ', '', $periods[$i]['SHORT_NAME']));
                    $p_title[$i] = strtoupper(str_replace(' ', '', $periods[$i]['TITLE']));
                    $sort_order[$i] = $periods[$i]['SORT_ORDER'];
                    $st_time[$i] = strtotime($periods[$i]['START_TIME']);
                    $end_time[$i] = is_array($end_time) ? strtotime($periods[$i]['END_TIME']) : 0;
                }
                if (is_array($p_title) && in_array(strtoupper(str_replace(' ', '', $columns['TITLE'])), $p_title) || is_array($shortname) && in_array(strtoupper(str_replace(' ', '', $columns['SHORT_NAME'])), $shortname)) {
                    $err = _alreadyAPeriodIsCreatedWithSameTitleOrShortname;
                    break;
                } elseif ($columns['START_TIME']) {
                    $sql = 'INSERT INTO school_periods ';
                    $fields = 'SCHOOL_ID,SYEAR,';
                    $values = '\'' . UserSchool() . '\',\'' . UserSyear() . '\',';
                    $go = 0;
                    if ($columns['START_TIME'] == $columns['END_TIME']) {
                        $err_msg = _startTimeAndEndTimeCanNotBeSame;
                        break;
                    }
                    foreach ($columns as $column => $value) {
                        if (trim($value)) {
                            $value = trim(paramlib_validation($column, $value));
                            $fields .= $column . ',';
                            $values .= '\'' . str_replace("'", "''", str_replace("\'", "'", $value)) . '\',';
                            $go = true;
                        }
                    }
                    $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';

                    if ($go) {
                        DBQuery($sql);
                    }

                    # ----------------------------- Length Calculate start --------------------- #

                    $p_id = DBGet(DBQuery('SELECT max(PERIOD_ID) AS period_id FROM school_periods WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
                    $period_id = $p_id[1]['PERIOD_ID'];

                    $time_chk = DBGet(DBQuery('SELECT START_TIME,END_TIME FROM school_periods WHERE PERIOD_ID=\'' . $period_id . '\' AND SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
                    $start_tm_chk = $time_chk[1]['START_TIME'];
                    $end_tm_chk = $time_chk[1]['END_TIME'];

                    $start_time = strtotime(date('m/d/Y') . ' ' . $start_tm_chk);
                    $end_time = strtotime(date('m/d/Y') . ' ' . $end_tm_chk);
                    if ($start_time > $end_time)
                        $end_time = strtotime(date('m/d/Y') . ' ' . $end_tm_chk) + 86400;

                    $length = ($end_time - $start_time) / 60;

                    $sql_up = 'update school_periods set length = ' . $length . ' where period_id=\'' . $period_id . '\' and syear=\'' . UserSyear() . '\' and school_id=\'' . UserSchool() . '\'';
                    $res_up = DBQuery($sql_up);

                    # -------------------------------------------------------------------------- #
                    //}
                }
            }
        }
    }
    if ($err)
        echo '<div class="alert alert-danger">' . $err . '</div>';
}

DrawBC("" . _schoolSetup . " <i class=\"icon-arrow-right13\"></i> " . ProgramTitle());

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'remove' && AllowEdit()) {
    $prd_id = paramlib_validation($colmn = 'PERIOD_ID', $_REQUEST['id']);
    $has_assigned_RET = DBGet(DBQuery('SELECT COUNT(*) AS TOTAL_ASSIGNED FROM course_period_var WHERE PERIOD_ID=\'' . $prd_id . '\''));
    $has_assigned = $has_assigned_RET[1]['TOTAL_ASSIGNED'];
    if ($has_assigned > 0) {
        UnableDeletePrompt(_cannotDeleteBecauseCoursePeriodsAreCreatedOnThisPeriod . '.');
    } else {
        if (DeletePrompt_Period('period')) {
            DBQuery('DELETE FROM school_periods WHERE PERIOD_ID=\'' . $prd_id . '\'');
            unset($_REQUEST['modfunc']);
        }
    }
}

if ($_REQUEST['modfunc'] != 'remove') {


    $sql = 'SELECT PERIOD_ID,TITLE,SHORT_NAME,SORT_ORDER,LENGTH,START_TIME,END_TIME,ATTENDANCE AS UA,IGNORE_SCHEDULING AS IGS,ATTENDANCE,IGNORE_SCHEDULING FROM school_periods WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER';
    $QI = DBQuery($sql);



    $periods_RET = DBGet($QI, array('TITLE' => '_makeTextInput', 'SHORT_NAME' => '_makeTextInput', 'SORT_ORDER' => '_makeTextInputMod', 'LENGTH' => 'LENGTH', 'START_TIME' => '_makeTimeInput', 'END_TIME' => '_makeTimeInputEnd', 'ATTENDANCE' => '_makeCheckboxInput', 'IGNORE_SCHEDULING' => '_makeCheckboxInput'));



    $columns = array('TITLE' => _title, 'SHORT_NAME' => _shortName, 'SORT_ORDER' => _sortOrder, 'START_TIME' => _startTime, 'END_TIME' => _endTime, 'LENGTH' => _length . '<div></div>(' . _minutes . ')', 'ATTENDANCE' => _usedFor . ' <div></div>' . _attendance, 'IGNORE_SCHEDULING' => _ignoreFor . ' <div></div>' . _scheduling);


    $link['add']['html'] = array('TITLE' => _makeTextInput('', 'TITLE'), 'SHORT_NAME' => _makeTextInput('', 'SHORT_NAME'), 'SORT_ORDER' => _makeTextInputMod2('', 'SORT_ORDER'), 'START_TIME' => _makeTimeInput('', 'START_TIME'), 'END_TIME' => _makeTimeInputEnd('', 'END_TIME'), 'ATTENDANCE' => _makeCheckboxInput('', 'ATTENDANCE'), 'IGNORE_SCHEDULING' => _makeCheckboxInput('', 'IGNORE_SCHEDULING'));

    $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=remove";
    $link['remove']['variables'] = array('id' => 'PERIOD_ID');
    if ($err_msg) {
        echo '<div class="alert bg-danger alert-styled-left">';
        echo '<button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">' . _close . '</span></button>' . $err_msg . '</div>';

        unset($err_msg);
    }
    $LO = DBGet(DBQuery($sql));
    $period_id_arr = array();
    foreach ($LO as $ti => $td) {
        array_push($period_id_arr, $td['PERIOD_ID']);
    }

    $period_id = implode(',', $period_id_arr);
    echo "<FORM name=F1 id=F1 action=Modules.php?modname=" . strip_tags(trim($_REQUEST['modname'])) . "&modfunc=update method=POST>";

    echo '<input type="hidden" name="h1" id="h1" value="' . $period_id . '">';

    echo '<div id="students" class="panel panel-white">';
    ListOutputPeriod($periods_RET, $columns, _period, _periods, $link);

    $count = count($periods_RET);
    if ($count != 0) {
        $maxPeriodId = DBGet(DBQuery("select max(PERIOD_ID) as maxPeriodId from school_periods WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "'"));

        $maxPeriodId = $maxPeriodId[1]['MAXPERIODID'];
        echo "<input type=hidden id=count name=count value=$maxPeriodId />";
    } else
        echo "<input type=hidden id=count name=count value=$count />";
    echo '<hr class="no-margin"/><div class="panel-body text-right">' . SubmitButton(_save, '', 'id="setupPeriodsBtn" class="btn btn-primary" onclick="formcheck_school_setup_periods(this);"') . '</div>';
    echo '</div>';
    echo '</FORM>';
}

function _makeTextInput($value, $name)
{
    global $THIS_RET;

    if ($THIS_RET['PERIOD_ID'])
        $id = $THIS_RET['PERIOD_ID'];
    else
        $id = 'new';

    if ($name != 'TITLE')
        $extra = 'size=5 maxlength=10 placeholder=' . ucwords(strtolower(str_replace('_', ' ', $name))) . ' class=form-control ';
    else
        $extra = 'class=form-control placeholder=' . ucwords(strtolower(str_replace('_', ' ', $name)));

    return TextInput_mod_a($value, 'values[' . $id . '][' . $name . ']', '', $extra);
}

function _makeTextInputMod($value, $name)
{
    global $THIS_RET;

    if ($THIS_RET['PERIOD_ID'])
        $id = $THIS_RET['PERIOD_ID'];
    else
        $id = 'new';

    if ($THIS_RET['SORT_ORDER'] != '')
        $extra = 'size=5 maxlength=10 class=form-control placeholder=' . ucwords(strtolower(str_replace('_', ' ', $name))) . ' onkeydown=\"return numberOnly(event);\"';
    else
        $extra = 'size=5 maxlength=10 class=form-control placeholder=' . ucwords(strtolower(str_replace('_', ' ', $name))) . ' onkeydown="return numberOnly(event);"';

    return TextInput($value, 'values[' . $id . '][' . $name . ']', '', $extra);
}

function _makeTextInputMod2($value, $name)
{
    global $THIS_RET;

    if ($THIS_RET['PERIOD_ID'])
        $id = $THIS_RET['PERIOD_ID'];
    else
        $id = 'new';

    if ($name != 'TITLE')
        $extra = 'size=5 maxlength=10 class=form-control placeholder=' . ucwords(strtolower(str_replace('_', ' ', $name))) . ' onkeydown="return numberOnly(event);"';

    return TextInput($value, 'values[' . $id . '][' . $name . ']', '', $extra);
}

function _makeCheckboxInput($value, $name)
{
    global $THIS_RET;

    if ($THIS_RET['PERIOD_ID'])
        $id = $THIS_RET['PERIOD_ID'];
    else
        $id = 'new';

    return '<div class="text-center">' . CheckboxInputSwitch($value, 'values[' . $id . '][' . $name . ']', '', '', ($id == 'new' ? true : false), 'Yes', 'No', '', 'switch-success') . '</div>';
}




function _makeTimeInput($value, $name)
{
    global $THIS_RET;

    if ($THIS_RET['PERIOD_ID'])
        $id = $THIS_RET['PERIOD_ID'];
    else
        $id = 'new';
    if ($id != 'new')
        $value = date("g:i A", strtotime($value));
    $hour = substr($value, 0, strpos($value, ':'));
    $m = substr($value, 0, strpos($value, ''));

    for ($i = 1; $i <= 12; $i++)
        $hour_options[$i] = $i;

    for ($i = 0; $i <= 9; $i++)
        $minute_options[$i] = '0' . $i;
    for ($i = 10; $i <= 59; $i++)
        $minute_options[$i] = $i;

    if ($id != 'new') {
        $sql_ampm_s = 'SELECT START_TIME FROM school_periods WHERE period_id=' . $id;

        $row_ampm_s = DBGet(DBQuery($sql_ampm_s));
        $ampm_s = date("g:i A", strtotime($row_ampm_s[1]['START_TIME']));
        $f_ampm_s = substr($ampm_s, -2);

        $min_s = date("g:i A", strtotime($row_ampm_s[1]['START_TIME']));
        $f_min_s = explode(":", $min_s);
        $fn_min_s = substr($f_min_s[1], 0, 2);
        if (!is_numeric($fn_min_s))
            $fn_min_s = substr($f_min_s[1], 0, 1);
    }

    if ($id != 'new')
        return TextInput_time($hour . ':' . $fn_min_s . ' ' . $f_ampm_s, 'values[' . $id . '][START_TIME]', '');
    else
        return TextInput_time('', 'values[' . $id . '][START_TIME]', '');
}

function _makeTimeInputEnd($value, $name)
{
    global $THIS_RET;

    if ($THIS_RET['PERIOD_ID'])
        $id = $THIS_RET['PERIOD_ID'];
    else
        $id = 'new';
    if ($id != 'new')
        $value = date("g:i A", strtotime($value));
    $hour = substr($value, 0, strpos($value, ':'));
    $m = substr($value, 0, strpos($value, ''));

    for ($i = 1; $i <= 12; $i++)
        $hour_options[$i] = $i;

    for ($i = 0; $i <= 9; $i++)
        $minute_options[$i] = '0' . $i;
    for ($i = 10; $i <= 59; $i++)
        $minute_options[$i] = $i;

    if ($id != 'new') {
        $sql_ampm = 'select end_time from school_periods where period_id=' . $id;
        $res_ampm = DBQuery($sql_ampm);
        $row_ampm = DBGet($res_ampm);
        $ampm = date("g:i A", strtotime($row_ampm[1]['END_TIME']));
        $f_ampm = substr($ampm, -2);

        $min = date("g:i A", strtotime($row_ampm[1]['END_TIME']));
        $f_min = explode(":", $min);
        $fn_min = substr($f_min[1], 0, 2);
        if (!is_numeric($fn_min))
            $fn_min = substr($f_min[1], 0, 1);
    }

    if ($id != 'new')
        return TextInput_time($hour . ':' . $fn_min . ' ' . $f_ampm, 'values[' . $id . '][END_TIME]', '');
    else
        return TextInput_time('', 'values[' . $id . '][END_TIME]', '');
}
