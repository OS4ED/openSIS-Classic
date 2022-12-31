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
include('lang/language.php');
include('../../RedirectModulesInc.php');

echo '<script type="text/javascript" src="assets/js/plugins/forms/styling/uniform.min.js"></script>';
echo '<script type="text/javascript" src="assets/js/plugins/forms/styling/switch.min.js"></script>';
echo '<script type="text/javascript" src="assets/js/pages/form_checkboxes_radios.js"></script>';

DrawBC("" . _schoolSetup . " > " . ProgramTitle());

if (!isset($_REQUEST['page_display'])) {
    $_REQUEST['page_display'] = 'SystemPreference';
}

echo '<div class="row">';
echo '<div class="col-md-4">';

echo '<div class="panel panel-white">';
echo '<div class="panel-heading"><h5 class="panel-title">' . _systemPreferences . '</h5></div>';
echo '<div class="panel-body p-0">';
echo '<ul class="nav nav-pills nav-stacked nav-pills-primary m-b-0 p-0">';
echo '<li ' . ((clean_param($_REQUEST['page_display'], PARAM_ALPHAMOD) == 'SystemPreference') ? 'class="active"' : '') . '><a href="Modules.php?modname=' . strip_tags(trim($_REQUEST['modname'])) . '&page_display=SystemPreference"><i class="icon-watch2"></i> &nbsp;' . _setHalfDayAndFullDayMinutes . '</a></li>';
echo '<li ' . ((clean_param($_REQUEST['page_display'], PARAM_ALPHAMOD) == 'FAILURE') ? 'class="active"' : '') . '><a href="Modules.php?modname=' . strip_tags(trim($_REQUEST['modname'])) . '&page_display=FAILURE"><i class="icon-key"></i> &nbsp;' . _setLoginFailureAllowanceCount . '</a></li>';
echo '<li ' . ((clean_param($_REQUEST['page_display'], PARAM_ALPHAMOD) == 'INACTIVITY') ? 'class="active"' : '') . '><a href="Modules.php?modname=' . strip_tags(trim($_REQUEST['modname'])) . '&page_display=INACTIVITY"><i class="icon-calendar22"></i> &nbsp;' . _setAllowableUserInactivityDays . '</a></li>';
echo '<li ' . ((clean_param($_REQUEST['page_display'], PARAM_ALPHAMOD) == 'MAINTENANCE') ? 'class="active"' : '') . '><a href="Modules.php?modname=' . strip_tags(trim($_REQUEST['modname'])) . '&page_display=MAINTENANCE"><i class="icon-hammer-wrench"></i> &nbsp;' . _putSystemInMaintenanceMode . '</a></li>';
echo '<li ' . ((clean_param($_REQUEST['page_display'], PARAM_ALPHAMOD) == 'CURRENCY') ? 'class="active"' : '') . '><a href="Modules.php?modname=' . strip_tags(trim($_REQUEST['modname'])) . '&page_display=CURRENCY"><i class="icon-coins"></i> &nbsp;' . _setCurrency . '</a></li>';
echo '<li ' . ((clean_param($_REQUEST['page_display'], PARAM_ALPHAMOD) == 'CLASSRANK') ? 'class="active"' : '') . '><a href="Modules.php?modname=' . strip_tags(trim($_REQUEST['modname'])) . '&page_display=CLASSRANK"><i class="icon-podium"></i> &nbsp;' . _displayClassRank . '</a></li>';
echo '<li ' . ((clean_param($_REQUEST['page_display'], PARAM_ALPHAMOD) == 'UPDATENOTIFY') ? 'class="active"' : '') . '><a href="Modules.php?modname=' . strip_tags(trim($_REQUEST['modname'])) . '&page_display=UPDATENOTIFY"><i class="icon-bell3"></i> &nbsp;' . _displayNotifications . '</a></li>';
echo '</ul>';
echo '</div>'; //.panel-body
echo '</div>'; //.panel

echo '</div>';
echo '<div class="col-md-8">';

if (clean_param($_REQUEST['page_display'], PARAM_ALPHAMOD) == 'SystemPreference') {
    if ((clean_param($_REQUEST['action'], PARAM_ALPHAMOD) == 'update') && (clean_param($_REQUEST['button'], PARAM_ALPHAMOD) == 'Save') && clean_param($_REQUEST['values'], PARAM_NOTAGS) && $_POST['values'] && User('PROFILE') == 'admin') {

        $sql = 'UPDATE system_preference SET ';
        foreach ($_REQUEST['values'] as $column => $value) {
            $value = paramlib_validation($column, $value);
            if ($column == 'FULL_DAY_MINUTE' && $value == '')
                $value = null;
            if ($column == 'HALF_DAY_MINUTE' && $value == '')
                $value = null;
            $sql .= $column . '=\'' . str_replace("\'", "''", $value) . '\',';
        }
        $sql = substr($sql, 0, -1) . ' WHERE SCHOOL_ID=\'' . UserSchool() . '\'';
        DBQuery($sql);
    } elseif ((clean_param($_REQUEST['action'], PARAM_ALPHAMOD) == 'insert') && (clean_param($_REQUEST['button'], PARAM_ALPHAMOD) == 'Save') && clean_param($_REQUEST['values'], PARAM_NOTAGS) && $_POST['values'] && User('PROFILE') == 'admin') {

        $sql = 'INSERT INTO system_preference SET ';
        foreach ($_REQUEST['values'] as $column => $value) {
            $value = paramlib_validation($column, $value);
            $sql .= $column . '=\'' . str_replace("\'", "''", $value) . '\',';
        }
        $sql = substr($sql, 0, -1) . ',school_id=\'' . UserSchool() . '\'';
        DBQuery($sql);
    }

    $sys_pref = DBGet(DBQuery('SELECT * FROM system_preference WHERE SCHOOL_ID=' . UserSchool()));
    $sys_pref = $sys_pref[1];


    if ($sys_pref == '') {
        echo "<FORM name=sys_pref id=sys_pref class=\"form-horizontal\" action=Modules.php?modname=" . strip_tags(trim($_REQUEST['modname'])) . "&action=insert&page_display=SystemPreference method=POST>";
    } else {

        echo "<FORM name=sys_pref id=sys_pref class=\"form-horizontal\" action=Modules.php?modname=" . strip_tags(trim($_REQUEST['modname'])) . "&action=update&page_display=SystemPreference method=POST>";
    }
    PopTable('header',  _halfDayAndFullDayMinutes);
    echo '<div class="form-group"><div class="col-md-12"><label class="control-label text-right text-uppercase"><b>' . _fullDayMinutes . '</b></label>' . TextInput($sys_pref['FULL_DAY_MINUTE'], 'values[FULL_DAY_MINUTE]', '', 'class=form-control') . '</div></div>';
    echo '<div class="form-group"><div class="col-md-12"><label class="control-label text-right text-uppercase"><b>' . _halfDayMinutes . '</b></label>' . TextInput($sys_pref['HALF_DAY_MINUTE'], 'values[HALF_DAY_MINUTE]', '', 'class=form-control') . '</div></div>';
    echo "<INPUT TYPE=SUBMIT name=button id=button class=\"btn btn-primary pull-right\" onclick='return formcheck_halfday_fullday(this);'  VALUE=" . _save . ">";
    PopTable('footer');
    echo "</FORM>";
}
if (clean_param($_REQUEST['page_display'], PARAM_ALPHAMOD) == 'MAINTENANCE') {
    if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'update') {

        if (clean_param($_REQUEST['maintain'], PARAM_NOTAGS)) {

            $check_sys_pref_misc = DBGet(DBQuery('SELECT COUNT(1) as TOTAL FROM system_preference_misc'));
            if ($check_sys_pref_misc[1]['TOTAL'] > 0) {
                $sql = 'UPDATE system_preference_misc SET ';
                foreach ($_REQUEST['maintain'] as $column_name => $value) {
                    $sql .= '' . $column_name . '=\'' . str_replace("\'", "''", str_replace("`", "''", $value)) . '\',';
                }
                $sql = substr($sql, 0, -1) . ' WHERE 1=1';
            } else
                $sql = 'INSERT INTO system_preference_misc (SYSTEM_MAINTENANCE_SWITCH) VALUES (\'' . $_REQUEST['maintain']['SYSTEM_MAINTENANCE_SWITCH'] . '\') ';
            DBQuery($sql);
        }
        foreach ($_REQUEST['values'] as $id => $columns) {
            if ($id != 'new') {
                $sql = 'UPDATE login_message SET ';
                foreach ($columns as $column => $value) {


                    if ($value == 'DISPLAY')
                        $sql .= $column . '=\'Y\',';
                    else
                        $sql .= $column . '=\'' . str_replace("\'", "''", $value) . '\',';
                }
                $sql = substr($sql, 0, -1) . ' WHERE ID=\'' . $id . '\'';
                DBQuery($sql);
            } else {
                $sql = 'INSERT INTO login_message ';
                $go = 0;
                foreach ($columns as $column => $value) {
                    if ($value) {
                        if ($value == 'DISPLAY') {
                            $fields .= $column . ',';
                            $values .= '\'' . str_replace("\'", "''", 'Y') . '\',';
                        } else {
                            $fields .= $column . ',';
                            $values .= '\'' . str_replace("\'", "''", $value) . '\',';
                        }
                        $go = true;
                    }
                }
                $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';
                if ($go) {
                    DBQuery($sql);
                }
            }
        }
        foreach ($_REQUEST['val'] as $col => $val) {
            $id = trim(substr($val, 0, strpos($val, ',')));
            $value = trim(substr($val, strpos($val, ',') + 1));
            if ($id != 'new') {
                $ID = DBGet(DBQuery('SELECT ID FROM login_message'));
                foreach ($ID as $get_ID) {
                    if ($get_ID['ID'] == $id)
                        $sql = 'UPDATE login_message SET ' . $col . '=\'Y\' WHERE ID=' . $get_ID['ID'];
                    else
                        $sql = 'UPDATE login_message SET ' . $col . '=\'N\' WHERE ID=' . $get_ID['ID'];
                    DBQuery($sql);
                }
            } else {
                $ID = DBGet(DBQuery('SELECT ID FROM login_message'));
                foreach ($ID as $get_ID) {
                    if ($get_ID['ID'] == $id)
                        $sql = 'UPDATE login_message SET ' . $col . '=\'Y\' WHERE ID=' . $get_ID['ID'];
                    else
                        $sql = 'UPDATE login_message SET ' . $col . '=\'N\' WHERE ID=' . $get_ID['ID'];
                    DBQuery($sql);
                }
                $max_ID = DBGet(DBQuery('SELECT MAX(ID) AS ID FROM login_message'));
                $login_VAL = DBGet(DBQuery('SELECT ID,MESSAGE FROM login_message WHERE ID=' . $max_ID[1]['ID'] . ' '));
                $sql = 'UPDATE login_message SET ';
                if ($login_VAL[1]['MESSAGE'] != '') {
                    $sql .= $col . '=\'Y\' ';
                    $sql .= ' WHERE ID=' . $max_ID[1]['ID'] . '';
                }
                DBQuery($sql);
            }
        }
        unset($_REQUEST['maintain']);
    }
    if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'remove') {
        if (DeletePrompt_sys_maintain('login message')) {
            DBQuery("DELETE FROM login_message WHERE ID='$_REQUEST[id]'");
            unset($_REQUEST['modfunc']);
        }
    }
    if ($_REQUEST['modfunc'] != 'remove') {
        $maintain_RET = DBGet(DBQuery("SELECT SYSTEM_MAINTENANCE_SWITCH FROM system_preference_misc LIMIT 1"));
        $maintain = $maintain_RET[1];
        echo '<div class="panel panel-white">';
        echo "<FORM name=maintenance class=no-margin id=maintenance action=Modules.php?modname=" . strip_tags(trim($_REQUEST['modname'])) . "&modfunc=update&page_display=MAINTENANCE method=POST>";
        echo '<div class="panel-heading"><input name="maintain[SYSTEM_MAINTENANCE_SWITCH]" value="" type="hidden"><div class="checkbox checkbox-switch switch-success switch-sm">
          <label ' . (($maintain['SYSTEM_MAINTENANCE_SWITCH'] == 'Y') ? 'class="text-success"' : 'text-muted') . '>
          <input type="checkbox" value=Y name="maintain[SYSTEM_MAINTENANCE_SWITCH]" ' . (($maintain['SYSTEM_MAINTENANCE_SWITCH'] == 'Y') ? 'checked="checked"' : '') . '><span></span>
          ' . (($maintain['SYSTEM_MAINTENANCE_SWITCH'] == 'Y') ? _theSystemIsUnderMaintenance : _putTheSystemInMaintenanceMode) . '
          </label>
          </div></div><hr class="no-margin"/>';
        $sql = 'SELECT ID,MESSAGE,DISPLAY FROM login_message ORDER BY ID';
        $QI = DBQuery($sql);
        $login_MESSAGE = DBGet($QI, array('MESSAGE' => '_makeContentInput', 'DISPLAY' => '_makeRadio'));
        $link['add']['html'] = array('MESSAGE' => _makeContentInput('', 'MESSAGE'), 'DISPLAY' => _makeRadio('', 'DISPLAY'));
        $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=remove&page_display=MAINTENANCE";
        $link['remove']['variables'] = array('id' => 'ID');
        $columns = array('MESSAGE' => _loginMessage, 'DISPLAY' => _display);
        ListOutput($login_MESSAGE, $columns,  _message, _messages, $link, true, array('search' => false));

        echo '<div class="panel-body">';
        //        if ($_REQUEST['page_display']) {
        //            echo "<a href=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . " class=\"btn btn-default\"><i class=\"fa fa-arrow-left\"></i>&nbsp; Back to System Preference</a>";
        //        }
        echo SubmitButton(_save, '', 'class="btn btn-primary pull-right" onclick="self_disable(this);"');
        echo '</div>';
        echo '</FORM>';
        echo '</div>';
    }
}
if (clean_param($_REQUEST['page_display'], PARAM_ALPHAMOD) == 'INACTIVITY') {

    PopTable('header',  _userInactivityDays);
    include("UserActivityDays.php");
    PopTable('footer');
}
if (clean_param($_REQUEST['page_display'], PARAM_ALPHAMOD) == 'FAILURE') {

    PopTable('header',  _loginFailureAllowance);
    include("FailureCount.php");
    PopTable('footer');
}
if (clean_param($_REQUEST['page_display'], PARAM_ALPHAMOD) == 'CURRENCY') {

    PopTable('header',  _currency);
    include("SetCurrency.php");
    PopTable('footer');
}
if (clean_param($_REQUEST['page_display'], PARAM_ALPHAMOD) == 'CLASSRANK') {

    PopTable('header',  _classRank);

    if ($_REQUEST['modfunc'] == 'update') {
        if (isset($_REQUEST['display_rank'])) {
            $rank_RET = DBGet(DBQuery('SELECT VALUE FROM program_config WHERE school_id=\'' . UserSchool() . '\' AND program=\'class_rank\' AND title=\'display\' LIMIT 0, 1'));
            if (count($rank_RET) == 0) {
                DBQuery('INSERT INTO program_config (school_id,program,title,value) VALUES(\'' . UserSchool() . '\',\'class_rank\',\'display\',\'Y\')');
            } else {
                DBQuery('UPDATE program_config SET value=\'' . $_REQUEST['display_rank'] . '\' WHERE school_id=\'' . UserSchool() . '\' AND program=\'class_rank\' AND title=\'display\'');
            }
            unset($_REQUEST['display_rank']);
            unset($_SESSION['_REQUEST_vars']['display_rank']);
        }
    }
    $rank_RET = DBGet(DBQuery('SELECT VALUE FROM program_config WHERE school_id=\'' . UserSchool() . '\' AND program=\'class_rank\' AND title=\'display\' LIMIT 0, 1'));
    $rank = $rank_RET[1];

    echo "<FORM name=failure class=no-margin id=failure action=Modules.php?modname=" . strip_tags(trim($_REQUEST['modname'])) . "&modfunc=update&page_display=CLASSRANK method=POST>";

    echo '<div class="row"><div class="col-md-12">';
    echo '<div class="form-group">' . CheckboxInputSwitch($rank['VALUE'], 'display_rank', _displayClassRank . '?', '', false, 'Yes', 'No', '', 'switch-success') . '</div>';
    echo '</div></div>';

    echo '<hr />';

    echo SubmitButton(_save, '', 'class="btn btn-primary pull-right" onclick="self_disable(this);"');

    echo '</FORM>';

    PopTable('footer');
}
if (clean_param($_REQUEST['page_display'], PARAM_ALPHAMOD) == 'UPDATENOTIFY') {

    PopTable('header',  _displayNotifications);

    if ($_REQUEST['modfunc'] == 'update') {

        if (isset($_REQUEST['display_notify'])) {
            $notify_RET = DBGet(DBQuery('SELECT VALUE FROM program_config WHERE school_id=\'' . UserSchool() . '\' AND program=\'UPDATENOTIFY\' AND title=\'display\' LIMIT 0, 1'));
            if (count($notify_RET) == 0) {
                DBQuery('INSERT INTO program_config (SYEAR,SCHOOL_ID,PROGRAM,TITLE,VALUE) VALUES(\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'UPDATENOTIFY\',\'display\',\'Y\')');
            } else {
                DBQuery('UPDATE program_config SET VALUE=\'' . $_REQUEST['display_notify'] . '\' WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND PROGRAM=\'UPDATENOTIFY\' AND TITLE=\'display\'');
            }
            unset($_REQUEST['display_notify']);
            unset($_SESSION['_REQUEST_vars']['display_notify']);
        }
        if (isset($_REQUEST['display_school_notify'])) {
            $notify_RET = DBGet(DBQuery('SELECT VALUE FROM program_config WHERE school_id=\'' . UserSchool() . '\' AND program=\'UPDATENOTIFY\' AND title=\'display_school\' LIMIT 0, 1'));
            if (count($notify_RET) == 0) {
                DBQuery('INSERT INTO program_config (SYEAR,SCHOOL_ID,PROGRAM,TITLE,VALUE) VALUES(\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'UPDATENOTIFY\',\'display_school\',\'Y\')');
            } else {
                DBQuery('UPDATE program_config SET VALUE=\'' . $_REQUEST['display_school_notify'] . '\' WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND PROGRAM=\'UPDATENOTIFY\' AND TITLE=\'display_school\'');
            }
            unset($_REQUEST['display_notify']);
            unset($_SESSION['_REQUEST_vars']['display_notify']);
        }
    }
    $notify_RET = DBGet(DBQuery('SELECT VALUE FROM program_config WHERE school_id=\'' . UserSchool() . '\' AND program=\'UPDATENOTIFY\' AND TITLE=\'display\' LIMIT 0, 1'));
    $notify_RET = $notify_RET[1];
    $notify_RET_school = DBGet(DBQuery('SELECT VALUE FROM program_config WHERE school_id=\'' . UserSchool() . '\' AND program=\'UPDATENOTIFY\' AND TITLE=\'display_school\' LIMIT 0, 1'));
    $notify_RET_school = $notify_RET_school[1];
    echo "<FORM name=failure id=failure action=Modules.php?modname=" . strip_tags(trim($_REQUEST['modname'])) . "&modfunc=update&page_display=UPDATENOTIFY method=POST>";

    echo '<div class="row"><div class="col-md-12">';
    echo '<div class="form-group">' . CheckboxInputSwitch($notify_RET['VALUE'], 'display_notify', _notifyWhenLatestVersionIsAvailable . '?', '', false, 'Yes', 'No', '', 'switch-success') . '</div>';
    echo '</div></div>';

    echo '<div class="row"><div class="col-md-12">';
    echo '<div class="form-group">' . CheckboxInputSwitch($notify_RET_school['VALUE'], 'display_school_notify', _notifyWhenSchoolSetupIsIncomplete . '?', '', false, 'Yes', 'No', '', 'switch-success') . '</div>';
    echo '</div></div>';

    echo '<hr />';
    //    if ($_REQUEST['page_display']) {
    //        echo "<a href=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . " class=\"btn btn-default\"><i class=\"fa fa-arrow-left\"></i>&nbsp; Back to System Preference</a>";
    //    }
    echo SubmitButton(_save, '', 'class="btn btn-primary pull-right" onclick="self_disable(this);"');

    echo '</FORM>';

    PopTable('footer');
}

echo '</div>';
echo '</div>';

function _makeContentInput($value, $name)
{
    global $THIS_RET;

    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';
    $THIS_RET['ID'];
    return TextareaInput($value, "values[$id][$name]", '', 'rows=8 cols=55');
}

function makeTextInput($value, $name)
{
    global $THIS_RET;
    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    if ($name != 'MESSAGE')
        $extra = 'size=5 maxlength=2 class=cell_floating';
    else
        $extra = 'class=cell_floating ';

    if ($name == 'SORT_ORDER')
        $comment = '<!-- ' . $value . ' -->';

    return $comment . TextInput($value, 'values[' . $id . '][' . $name . ']', '', $extra);
}

function _makeRadio($value, $name)
{
    global $THIS_RET;
    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    if ($THIS_RET[$name] == 'Y')
        return "<TABLE align=center><TR><TD><INPUT type=radio name=val[" . $name . "] value=" . $id . "," . $name . " CHECKED></TD></TR></TABLE>";
    else
        return "<TABLE align=center><TR><TD><INPUT type=radio name=val[" . $name . "] value=" . $id . "," . $name . "" . (AllowEdit() ? '' : ' ') . "></TD></TR></TABLE>";
}
