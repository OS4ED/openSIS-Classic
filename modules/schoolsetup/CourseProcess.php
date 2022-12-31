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
include('../../RedirectRootInc.php');
include('../../Warehouse.php');

if(isset($_SESSION['language']) && $_SESSION['language']=='fr'){
    define("_saveContinue","Enregistrer continuer");
}
elseif(isset($_SESSION['language']) && $_SESSION['language']=='es'){
    define("_saveContinue", "Guardar Continuar");
}else{
    define("_saveContinue","Save & Continue");
}
$task = $_REQUEST['task'];
$calendar_id = $_REQUEST['cal_id'];
if ($_REQUEST['cp_id'] == '')
    $course_period_id = 'new';
else
    $course_period_id = $_REQUEST['cp_id'];
if ($_REQUEST['cp_var_id'] == '')
    $course_period_var_id = 'n';
else
    $course_period_var_id = $_REQUEST['cp_var_id'];

if ($_REQUEST['msg'] == 'conflict') {
    $RET['ROOM'] = $_REQUEST['room_id'];
    $RET['PERIOD_ID'] = $_REQUEST['period_id'];
    $RET['DAYS'] = $_REQUEST['days'];
    $RET['DOES_ATTENDANCE'] = $_REQUEST['does_attendance'];
    $div_this = false;
}
$_REQUEST['modname'] = 'schoolsetup/Courses.php';

switch ($task) {
    case 'md':
        $room_RET = DBGet(DBQuery("SELECT ROOM_ID,TITLE FROM rooms WHERE SCHOOL_ID='" . UserSchool() . "' ORDER BY SORT_ORDER"));
        if (count($room_RET)) {
            foreach ($room_RET as $room)
                $rooms[$room['ROOM_ID']] = $room['TITLE'];
        }

        $periods_RET = DBGet(DBQuery("SELECT PERIOD_ID,TITLE FROM school_periods WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
        if (count($periods_RET)) {
            foreach ($periods_RET as $period)
                $periods[$period['PERIOD_ID']] = $period['TITLE'];
        }
        if ($calendar_id != '') {
            $days_RET = DBGet(DBQuery("SELECT DAYS FROM school_calendars WHERE calendar_id=$calendar_id"));
            $cal_days = str_split($days_RET[1]['DAYS']);
            foreach ($cal_days as $day) {
                $days[$day] = conv_day($day);
            }
        }
        if ($_REQUEST['sch_type'] == 'variable') {
            if(isset($_SESSION['language']) && $_SESSION['language']=='fr'){
                define("_classRoom","Salle de cours");
                define("_period","Période");
                define("_days","Journées");
                define("_takesAttendance","La participation prend");
                define("_room", "Chambre");
                define("_time", "Temps");
                define("_saveContinue", "Enregistrer continuer");
            }
            elseif(isset($_SESSION['language']) && $_SESSION['language']=='es'){
                define("_classRoom","Salón de clases");
                define("_period","Período");
                define("_days","Dias");
                define("_takesAttendance","toma de Asistencia");
                define("_room","Habitación");
                define("_time","Hora");
                define("_saveContinue", "Guardar Continuar");
            }else{
                define("_classRoom","Class Room");
                define("_period","Period");
                define("_days","Days");
                define("_takesAttendance","Takes Attendance");
                define("_room", "Room");
                define("_time", "Time");
                define("_saveContinue", "Save & Continue");
            }
            $header = '<hr/><input type=hidden name=tables[course_periods][' . $course_period_id . '][SCHEDULE_TYPE] value=VARIABLE id="variable"/>';
            echo '<input type="hidden" name="get_status" id="get_status" value="" />';
            echo '<input type="hidden" name="cp_id" id="' . $day . '_id" value="' . $course_period_id . '"/>';
            
            $header .= '<div class="clearfix">';
            $header .= '<div class="col-md-1">';
            $header .= '<label class="control-label">'._days.'</label>';
            $header .= SelectInput($RET['DAYS'], 'course_period_variable[' . $course_period_id . '][DAYS]', '', $days, 'N/A', 'id=days class="form-control"', $div_this);
            $header .= '</div>'; //.col-md-1
            $header .= '<div class="col-md-3">';
            $header .= '<label class="control-label">'._period.'</label>';
            $header .= SelectInput($RET['PERIOD_ID'], 'course_period_variable[' . $course_period_id . '][PERIOD_ID]', '', $periods, 'N/A', 'id=' . $day . '_period class=form-control ' . $disable . ' onchange=show_period_time(this.value,"' . $day . '","' . $course_period_id . '","n");', $div_this);
            $header .= '</div>'; //.col-md-3
            $header .= '<div class="col-md-3">';
            $header .= '<label class="control-label">'._time.'</label>';
            if ($_REQUEST['msg'] != 'conflict')
                $header .='<div id=' . $day . '_period_time></div>';
            else {
                $cpdays_RET = DBGet(DBQuery("SELECT START_TIME,END_TIME FROM school_periods where period_id=$_REQUEST[period_id]"));
                $header .='<div id=' . $day . '_period_time>';
                $header .=ProperTime($cpdays_RET[1][START_TIME]) . ' To ' . ProperTime($cpdays_RET[1][END_TIME]);

                $header .='<input type=hidden name=course_period_variable[' . $course_period_id . '][' . $course_period_var_id . '][START_TIME] value="' . $cpdays_RET[1][START_TIME] . '"><input type=hidden name=course_period_variable[' . $course_period_id . '][' . $course_period_var_id . '][END_TIME] value="' . $cpdays_RET[1][END_TIME] . '"></div>';
            }
            $header .= '</div>'; //.col-md-3
            $header .= '<div class="col-md-3">';
            $header .= '<label class="control-label">'._room.'</label>';
            $header .= SelectInput($RET['ROOM'], 'course_period_variable[' . $course_period_id . '][ROOM_ID]', '', $rooms, 'N/A', 'id=' . $day . '_room  ' . $disable, $div_this);
            $header .= '</div>'; //.col-md-3
            $header .= '<div class="col-md-2">';
            $header .= '<label class="control-label">&nbsp;</label>';
            $header .= CheckboxInput_var_sch($RET['DOES_ATTENDANCE'], 'course_period_variable[' . $course_period_id . '][DOES_ATTENDANCE]', _takesAttendance, '', false, 'Yes', 'No', false, ' id=' . $day . '_does_attendance onclick="formcheck_periods_attendance_F2(' . (($day != '') ? 2 : 1) . ',this);"' . $disable);
            $header .= '</div>'; //.col-md-2
            $header .= '</div>';
            $header .= '<div id="ajax_output"></div>';
            
            echo '<input type="hidden" name="fixed_day" id="fixed_day" value="' . $day . '" />';
        } elseif ($_REQUEST['sch_type'] == 'fixed') {
            if(isset($_SESSION['language']) && $_SESSION['language']=='fr'){
                define("_classRoom","Salle de cours");
                define("_period","Période");
                define("_days","Journées");
                define("_takesAttendance","La participation prend");
                define("_room", "Chambre");
                define("_time", "Temps");
                define("_saveContinue", "Enregistrer continuer");
            }
            elseif(isset($_SESSION['language']) && $_SESSION['language']=='es'){
                define("_classRoom","Salón de clases");
                define("_period","Período");
                define("_days","Dias");
                define("_takesAttendance","toma de Asistencia");
                define("_room","Habitación");
                define("_time","Hora");
                define("_saveContinue", "Guardar Continuar");
            }else{
                define("_classRoom","Class Room");
                define("_period","Period");
                define("_days","Days");
                define("_takesAttendance","Takes Attendance");
                define("_room", "Room");
                define("_time", "Time");
                define("_saveContinue", "Save & Continue");
            }
            $header = '<hr/><input type=hidden name=tables[course_periods][' . $course_period_id . '][SCHEDULE_TYPE] value=FIXED />';
            echo '<input type="hidden" name="get_status" id="get_status" value="" />';
            echo '<input type="hidden" name="cp_id" id="' . $day . '_id" value="' . $course_period_id . '"/>';
            
            $header .= '<div class="row clearfix">';
            $header .= '<div class="col-md-3">';
            $header .= '<label class="control-label">'._classRoom.'</label>';
            $header .= SelectInput($RET['ROOM'], 'tables[course_period_var][' . $course_period_id . '][ROOM_ID]', '', $rooms, 'N/A', 'id=' . $day . '_room  ' . $disable, $div_this);
            $header .= '</div>'; //.col-md-3
            $header .= '<div class="col-md-3">';
            $header .= '<label class="control-label">'._period.'</label>';
            $header .= SelectInput($RET['PERIOD_ID'], 'tables[course_period_var][' . $course_period_id . '][PERIOD_ID]', '', $periods, 'N/A', 'id=' . $day . '_period class=form-control onchange="formcheck_periods_F2(\'' . $day . '\');"' . $disable, $div_this);
            $header .= '</div>'; //.col-md-3
            $header .= '<div class="col-md-3">';
            $header .= '<label class="control-label">'._days.'</label>';
            $header .= '<div class="days-check"><table><tr>';
            foreach ($days as $day => $short_day) {
                if (strpos($RET['DAYS'], $day) !== false || ($new && $day != 'S' && $day != 'U'))
                    $value = 'Y';
                else
                    $value = '';

                // $header .= '<TD>' . str_replace('"', '\"', CheckboxInput($value, 'tables[course_period_var][' . $course_period_id . '][DAYS][' . $day . ']', ($day == 'U' ? 'U' : $day), $checked, false, '', '', false)) . '</TD>';
                $header .= '<td><p class="p-0 m-0">'.($day == 'U' ? 'U' : $day).'</p>' . CheckboxInput($value, 'tables[course_period_var][' . $course_period_id . '][DAYS][' . $day . ']', '', $checked, true, '', '', true) . '</td>';
            }
            $header .= '</tr></table></div>';
            $header .= '</div>'; //.col-md-3
            $header .= '<div class="col-md-3">';
            $header .= '<label class="control-label">&nbsp;</label>';
            $header .= CheckboxInputSwitch($RET['DOES_ATTENDANCE'], 'tables[course_period_var][' . $course_period_id . '][DOES_ATTENDANCE]', _takesAttendance, $checked, $new, 'Yes', 'No', ' id=' . $day . '_does_attendance onclick="formcheck_periods_attendance_F2(' . (($day != '') ? 2 : 1) . ',this);"', 'switch-success');
            $header .= '</div>'; //.col-md-3
            $header .= '</div>'; //.clearfix
            
            $header .= '<div id="ajax_output"></div>';
            echo '<input type="hidden" name="fixed_day" id="fixed_day" value="' . $day . '" />';
        }
        elseif ($_REQUEST['sch_type'] == 'blocked') {
            $header = '<input type=hidden name=tables[course_periods][' . $course_period_id . '][SCHEDULE_TYPE] value=BLOCKED />';
            $header .= '<hr/>' . SubmitButton(_saveContinue , '', 'class="btn btn-primary" onclick="validate_course_period();"');
        }
        echo $header;
        break;

    case 'per_time':
        $cpdays_RET = DBGet(DBQuery("SELECT START_TIME,END_TIME FROM school_periods where period_id=$_REQUEST[period_id]"));
        echo $_REQUEST['day'] . "/" . ProperTime($cpdays_RET[1]['START_TIME']) . ' To ' . ProperTime($cpdays_RET[1]['END_TIME']);
        echo '<input type=hidden name=course_period_variable[' . $course_period_id . '][' . $course_period_var_id . '][START_TIME] value="' . $cpdays_RET[1]['START_TIME'] . '"><input type=hidden name=course_period_variable[' . $course_period_id . '][' . $course_period_var_id . '][END_TIME] value="' . $cpdays_RET[1]['END_TIME'] . '">';
}

function conv_day($short_date) {
    $days = array('U' => 'Sun', 'M' => 'Mon', 'T' => 'Tue', 'W' => 'Wed', 'H' => 'Thu', 'F' => 'Fri', 'S' => 'Sat');
    return $days[$short_date];
}

?>
