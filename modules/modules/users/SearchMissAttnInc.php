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
if ($_REQUEST['From'] && $_REQUEST['to']) {
    $From = $_REQUEST['From'];
    $to = $_REQUEST['to'];
} elseif ($_REQUEST['month_From'] && $_REQUEST['day_From'] && $_REQUEST['year_From']) {
    $_REQUEST['placed_From'] = $_REQUEST['day_From'] . '-' . $_REQUEST['month_From'] . '-' . $_REQUEST['year_From'];
    $From = (date('Y-m-d', strtotime($_REQUEST['placed_From'])));
} elseif (!$_REQUEST['month_From'] && !$_REQUEST['day_From'] && !$_REQUEST['year_From']) {
    $missing_date= DBGet(DBQuery('SELECT MIN(SCHOOL_DATE) AS SCHOOL_DATE FROM missing_attendance WHERE SCHOOL_ID='.UserSchool().' AND SYEAR='.UserSyear()));
    
    if(count($missing_date) > 0 && $missing_date[1]['SCHOOL_DATE']!="")
    {
     $_REQUEST['placed_From'] = $missing_date[1]['SCHOOL_DATE'];
    }
    else
    $_REQUEST['placed_From'] = '01-' . date('m') . '-' . date('Y');
    $From = (date('Y-m-d', strtotime($_REQUEST['placed_From'])));
    
}
if ($_REQUEST['month_to'] && $_REQUEST['day_to'] && $_REQUEST['year_to']) {
    $_REQUEST['placed_to'] = $_REQUEST['day_to'] . '-' . $_REQUEST['month_to'] . '-' . $_REQUEST['year_to'];
    $to = (date('Y-m-d', strtotime($_REQUEST['placed_to'])));
} elseif ($to == '')
    $to = date('Y-m-d', strtotime(DBDate()));


$extra['WHERE2'] = ' AND mi.school_date>=\'' . $From . '\' AND mi.school_date<\'' . $to . '\' AND mi.SYEAR=' . UserSyear();

if (User('PROFILE') == 'admin') {
    
    echo '<div class="panel panel-default">';

    $qr = DBGet(DBQuery('select START_DATE from school_years where SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear()));
//    $qr=  DBGet(DBQuery('select START_DATE from school_years where SCHOOL_ID='.UserSchool()));	
    $start_date = strtotime($qr[1]['START_DATE']);

    $date = strtotime($_REQUEST['placed_From']);


    if (!$_REQUEST['next_modname'])
        $_REQUEST['next_modname'] = 'users/User.php';

    $ERR = "";
    if ($_REQUEST['day_From'] == '' && $_REQUEST['day_to'])
        $ERR = "please select from date";
    if ($date < $start_date) {
        $ERR = " From date cannot be before school start date.";
    } else if (($_REQUEST['day_From'] && $_REQUEST['day_to']) || ($From && $to))
        $staff_RET = GetStaffList_Miss_Atn($extra);


    $header .= "<FORM class=\"m-b-0\" name=missingatten id=missingatten action=Modules.php?modname=$_REQUEST[modname]&func=save method=POST>";
    $header .= '<div class="form-inline">';
    $header .= '<div class="input-group"><span class="input-group-addon">'._from.' : </span>' . DateInputAY($From, 'From', 1).'</div>';
    $header .= '<div class="input-group"><span class="input-group-addon">'._to.' : </span>' . DateInputAY($to, 'to', 2).'</div>';
    $header .= '<INPUT type=submit class="btn btn-primary" name=go value='._go.' >';
    $header .= '</div>';
    $header .= '</form>';
    DrawHeader($header);
    
    echo '<div class="panel-body p-0">';
    if($ERR!=''){ echo '<div class="p-15 p-b-0"><span class="text-danger">' . $ERR . '</span></DIV>'; }

    if ($extra['profile']) {
        $options = array('admin' => 'Administrator', 'teacher' => 'Teacher', 'parent' => 'Parent', 'none' => 'No Access');
        $singular = $options[$extra['profile']];
        $plural = $singular . ($options[$extra['profile']] == 'none' ? 'es' : 's');
        $columns = array('FULL_NAME' => $singular, 'STAFF_ID' =>_staffId);
    } else {
        $singular = 'User';
        $plural = 'users';
//        $columns = array('FULL_NAME' => 'Staff Member', 'PROFILE' => 'Profile', 'STAFF_ID' =>_staffId);
        $columns = array('FULL_NAME' =>_name,
         'PROFILE' =>_profile,
         'STAFF_ID' =>_staffId);
        
    }
    if (is_array($extra['columns_before']))
        $columns = $extra['columns_before'] + $columns;
    if (is_array($extra['columns_after']))
        $columns += $extra['columns_after'];
    if (is_array($extra['link']))
        $link = $extra['link'];
    else {
        $link['FULL_NAME']['link'] = "Modules.php?modname=$_REQUEST[next_modname]&From=$From&to=$to";
        $link['FULL_NAME']['variables'] = array('staff_id' => 'STAFF_ID');
    }
    if (count($staff_RET)) {
        echo '<div class="p-15 p-b-0"><span class="text-danger">Following teachers have missing attendance!</span></div><hr class="no-margin"/>';
        ListOutput($staff_RET, $columns, $singular, $plural, $link, false, $extra['options']);
    } else {
        echo '<div class="p-15 p-b-0"><p class="text-danger">No missing attendance found for the selected date range!</p></div>';
    }
    echo '</div>'; //.panel-body
    echo '</div>'; //.panel
}
?>
