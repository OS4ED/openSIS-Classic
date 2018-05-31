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
// GET ALL THE config ITEMS FOR eligibility
include('../../RedirectModulesInc.php');
$start_end_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_config WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND PROGRAM=\'eligibility\''));
$arr = array();
if (count($start_end_RET)) {
    foreach ($start_end_RET as $value)
        $arr[$value['TITLE']] = $value['VALUE'];
}

if ($_REQUEST['values']) {
    if ($_REQUEST['values']['START_M'] == 'PM')
        $_REQUEST['values']['START_HOUR']+=12;
    if ($_REQUEST['values']['END_M'] == 'PM')
        $_REQUEST['values']['END_HOUR']+=12;

    $start = $_REQUEST['values']['START_DAY'] . $_REQUEST['values']['START_HOUR'] . $_REQUEST['values']['START_MINUTE'];
    $end = $_REQUEST['values']['END_DAY'] . $_REQUEST['values']['END_HOUR'] . $_REQUEST['values']['END_MINUTE'];
    foreach ($_REQUEST['values'] as $key => $value) {
        if (isset($$key)) {
            DBQuery('UPDATE program_config SET VALUE=\'' . $value . '\' WHERE PROGRAM=\'eligibility\' AND TITLE=\'' . $key . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'');
        } else {
            DBQuery('INSERT INTO program_config (SYEAR,SCHOOL_ID,PROGRAM,TITLE,VALUE) values(\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'eligibility\',\'' . $key . '\',\'' . $value . '\')');
        }
    }

    $start_end_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_config WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND PROGRAM=\'eligibility\''));
    if (count($start_end_RET)) {
        foreach ($start_end_RET as $value)
            $arr[$value['TITLE']] = $value['VALUE'];
    }
}

DrawBC("Extracurricular > " . ProgramTitle());
$days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
for ($i = 0; $i < 7; $i++)
    $day_options[$i] = $days[$i];

for ($i = 1; $i <= 11; $i++)
    $hour_options[$i] = $i;
$hour_options['0'] = '12';

for ($i = 0; $i <= 9; $i++)
    $minute_options[$i] = '0' . $i;
for ($i = 10; $i <= 59; $i++)
    $minute_options[$i] = $i;

$m_options = array('AM' => 'AM', 'PM' => 'PM');
$START_HOUR = $arr['START_HOUR'];
if ($arr['START_HOUR'] > 12) {
    $START_HOUR-=12;
    $START_M = 'PM';
}
else
    $START_M = 'AM';

$END_HOUR = $arr['END_HOUR'];
if ($arr['END_HOUR'] > 12) {
    $END_HOUR-=12;
    $END_M = 'PM';
}
else
    $END_M = 'AM';
PopTable('header', 'Allow Extracurricular Posting');
echo "<FORM name=F1 id=F1 class=form-horizontal action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . " method=POST>";
echo '<div class="row">';
if (count($start_end_RET)) {
    $start_value = $days[$arr['START_DAY']] . ', ' . $START_HOUR . ':' . str_pad($arr['START_MINUTE'], 2, 0, STR_PAD_LEFT) . ' ' . $START_M;
    $end_value = $days[$arr['END_DAY']] . ', ' . $END_HOUR . ':' . str_pad($arr['END_MINUTE'], 2, 0, STR_PAD_LEFT) . ' ' . $END_M;
    echo '<TR><TD><STRONG>From</STRONG></TD><TD><DIV id=start_time><div onclick=\'addHTML("<TABLE><TR><TD>' . str_replace('"', '\"', SelectInput($arr['START_DAY'], 'values[START_DAY]', '', $day_options, false, '', false)) . '</TD><TD>' . str_replace('"', '\"', SelectInput($START_HOUR, 'values[START_HOUR]', '', $hour_options, false, '', false)) . ' :</TD><TD>' . str_replace('"', '\"', SelectInput($arr['START_MINUTE'], 'values[START_MINUTE]', '', $minute_options, false, '', false)) . '</TD><TD>' . str_replace('"', '\"', SelectInput($START_M, 'values[START_M]', '', $m_options, false, '', false)) . '</TD></TR></TABLE>","start_time",true);\'>' . $start_value . '</div></DIV></TD></TR>';
    echo '<TR><TD><STRONG>To</STRONG></TD><TD><DIV id=end_time><div onclick=\'addHTML("<TABLE><TR><TD>' . str_replace('"', '\"', SelectInput($arr['END_DAY'], 'values[END_DAY]', '', $day_options, false, '', false)) . '</TD><TD>' . str_replace('"', '\"', SelectInput($END_HOUR, 'values[END_HOUR]', '', $hour_options, false, '', false)) . ' :</TD><TD>' . str_replace('"', '\"', SelectInput($arr['END_MINUTE'], 'values[END_MINUTE]', '', $minute_options, false, '', false)) . '</TD><TD>' . str_replace('"', '\"', SelectInput($END_M, 'values[END_M]', '', $m_options, false, '', false)) . '</TD></TR></TABLE>","end_time",true);\'>' . $end_value . '</div></DIV></TD></TR>';
} else {
    echo '<div class="col-md-4"><div class="form-inline"><label class="control-label col-md-3">From</label>' . SelectInput($START_DAY, 'values[START_DAY]', '', $day_options, false, '', false) . ' &nbsp; ' . SelectInput($START_HOUR, 'values[START_HOUR]', '', $hour_options, false, '', false) . ' : ' . SelectInput($START_MINUTE, 'values[START_MINUTE]', '', $minute_options, false, '', false) . ' &nbsp; ' . SelectInput($START_M, 'values[START_M]', '', $m_options, false, '', false) . '</div></div>';
    echo '<div class="col-md-4"><div class="form-inline"><label class="control-label col-md-2">To</label>' . SelectInput($END_DAY, 'values[END_DAY]', '', $day_options, false, '', false) . ' &nbsp; ' . SelectInput($END_HOUR, 'values[END_HOUR]', '', $hour_options, false, '', false) . ' : ' . SelectInput($END_MINUTE, 'values[END_MINUTE]', '', $minute_options, false, '', false) . ' &nbsp; ' . SelectInput($END_M, 'values[END_M]', '', $m_options, false, '', false) . '</div></div>';
}
echo '<div class="col-md-2">' . SubmitButton('Save', '', 'class="btn btn-primary" onclick="formcheck_eligibility_entrytimes();"') . '</div>';
echo '</row>';
echo '</FORM>';
PopTable('footer');
?>