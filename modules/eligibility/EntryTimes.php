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
// GET ALL THE config ITEMS FOR eligibility
include('../../RedirectModulesInc.php');
$start_end_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_config WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND PROGRAM=\'eligibility\''));
$arr = array();
if (count($start_end_RET)) {
    foreach ($start_end_RET as $value)
        $arr[$value['TITLE']] = $value['VALUE'];
}

if ($_REQUEST['values']) {
    if(isset($_REQUEST['values']['START_TIME']) && $_REQUEST['values']['START_TIME']!='')
    {
        $start_value1=explode(' ',$_REQUEST['values']['START_TIME']);
        $start_value2=explode(':',$start_value1[0]);
        $_REQUEST['values']['START_HOUR']=$start_value2[0];
        $_REQUEST['values']['START_MINUTE']=$start_value2[1];
        $_REQUEST['values']['START_M']=$start_value1[1];
    }
    if(isset($_REQUEST['values']['END_TIME']) && $_REQUEST['values']['END_TIME']!='')
    {
        $end_value1=explode(' ',$_REQUEST['values']['END_TIME']);
        $end_value2=explode(':',$end_value1[0]);
        $_REQUEST['values']['END_HOUR']=$end_value2[0];
        $_REQUEST['values']['END_MINUTE']=$end_value2[1];
        $_REQUEST['values']['END_M']=$end_value1[1];
    }
    if ($_REQUEST['values']['START_M'] == 'PM')
        $_REQUEST['values']['START_HOUR']+=12;
    if ($_REQUEST['values']['END_M'] == 'PM')
        $_REQUEST['values']['END_HOUR']+=12;

    $start = $_REQUEST['values']['START_DAY'] . $_REQUEST['values']['START_HOUR'] . $_REQUEST['values']['START_MINUTE'];
    $end = $_REQUEST['values']['END_DAY'] . $_REQUEST['values']['END_HOUR'] . $_REQUEST['values']['END_MINUTE'];

    $this_REQUEST = $_REQUEST['values'];

    $this_REQUEST['START_HOUR'] = (string)$this_REQUEST['START_HOUR'];
    $this_REQUEST['END_HOUR'] = (string)$this_REQUEST['END_HOUR'];

    $new_REQUEST = sqlSecurityFilter($this_REQUEST);

    foreach ($new_REQUEST as $key => $value) {
        if($key!='START_TIME' && $key!='END_TIME')
        {
            if (isset($$key)) {
                DBQuery('UPDATE program_config SET VALUE=\'' . $value . '\' WHERE PROGRAM=\'eligibility\' AND TITLE=\'' . $key . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'');
            } else {
                DBQuery('INSERT INTO program_config (SYEAR,SCHOOL_ID,PROGRAM,TITLE,VALUE) values(\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'eligibility\',\'' . $key . '\',\'' . $value . '\')');
            }
        }
    }

    $start_end_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_config WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND PROGRAM=\'eligibility\''));
    if (count($start_end_RET)) {
        foreach ($start_end_RET as $value)
            $arr[$value['TITLE']] = $value['VALUE'];
    }
}

DrawBC(""._extracurricular." > " . ProgramTitle());
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
$START_HOUR = intval($arr['START_HOUR']);
if ($arr['START_HOUR'] > 12) {
    $START_HOUR-=12;
    $START_M = 'PM';
}
else
    $START_M = 'AM';


$END_HOUR = intval($arr['END_HOUR']);
if ($arr['END_HOUR'] > 12) {
    $END_HOUR-=12;
    $END_M = 'PM';
}
else
    $END_M = 'AM';

echo '<div class="row">';
echo '<div class="col-md-6 col-md-offset-3">';
echo "<FORM name=F1 id=F1 class=form-horizontal action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . " method=POST>";
PopTable('header',  _allowExtracurricularPosting);
echo '<div>';


if (count($start_end_RET)) {
    $start_value = $days[$arr['START_DAY']] . ', ' . $START_HOUR . ':' . str_pad($arr['START_MINUTE'], 2, 0, STR_PAD_LEFT) . ' ' . $START_M;
    $end_value = $days[$arr['END_DAY']] . ', ' . $END_HOUR . ':' . str_pad($arr['END_MINUTE'], 2, 0, STR_PAD_LEFT) . ' ' . $END_M;
    $start_time = $START_HOUR . ':' . str_pad($arr['START_MINUTE'], 2, 0, STR_PAD_LEFT) . ' ' . $START_M;
    $end_time = $END_HOUR . ':' . str_pad($arr['END_MINUTE'], 2, 0, STR_PAD_LEFT) . ' ' . $END_M;

    echo '<div class="form-group"><label class="control-label col-md-2 text-right">'._from.' :</label>';
    echo '<div class="col-md-10"><div class="form-inline"><div class="row"><div class= col-sm-3>' . SelectInput($arr['START_DAY'], 'values[START_DAY]', '', $day_options,false, '') . '</div> &nbsp; <div class= col-sm-4>'. TextInput_time($start_time, 'values[START_TIME]', '', NULL) . '</div></div></div></div>';
    echo '</div>'; //form-group

    echo '<div class="form-group"><label class="control-label col-md-2 text-right">'._to.' :</label>';
    // echo '<div class="col-md-10"><div class="form-inline">' . SelectInput($arr['END_DAY'], 'values[END_DAY]', '', $day_options, false, '', false) . ' &nbsp; '. TextInput_time($end_time, 'values[END_TIME]', '', NULL) . '</div></div>';
    echo '<div class="col-md-10"><div class="form-inline"><div class="row"><div class= col-sm-3>' . SelectInput($arr['END_DAY'], 'values[END_DAY]','', $day_options,false, '') . '</div> &nbsp; <div class= col-sm-4>'. TextInput_time($end_time, 'values[END_TIME]', '', NULL) . '</div></div></div></div>';
    echo '</div>';
} else {
    echo '<div class="form-group"><label class="control-label col-md-2 text-right">'._from.' :</label><div class="col-md-10"><div class="form-inline"><div class="row"><div class= col-sm-3>' . SelectInput($START_DAY, 'values[START_DAY]', '', $day_options, false, '', false) . ' </div> &nbsp; <div class= col-sm-4>'. TextInput_time('', 'values[START_TIME]', '', NULL) . '</div></div></div></div></div>';

    echo '<div class="form-group"><label class="control-label col-md-2 text-right">'._to.' :</label><div class="col-md-10"><div class="form-inline"><div class="row"><div class= col-sm-3>' . SelectInput($END_DAY, 'values[END_DAY]', '', $day_options, false, '', false) . ' </div> &nbsp; <div class= col-sm-4>' . TextInput_time('', 'values[END_TIME]', '', NULL)  . '</div></div></div></div></div>';
}


$btn = SubmitButton(_save, '', 'id="entryTimesBtn" class="btn btn-primary" onclick="formcheck_eligibility_entrytimes(this);"');
echo '</div>';
PopTable('footer',  $btn);
echo '</FORM>';
echo '</div>';
echo '</div>';
?>
