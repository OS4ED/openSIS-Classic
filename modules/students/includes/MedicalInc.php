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
include('../../../RedirectIncludes.php');

include_once('modules/students/includes/FunctionsInc.php');

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'delete') {
    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel'])
        echo '</FORM>';

    if (DeletePromptMod($_REQUEST['title'], '&include=MedicalInc&category_id=' . $_REQUEST[category_id])) {
        DBQuery("DELETE FROM $_REQUEST[table] WHERE ID='$_REQUEST[id]'");
        unset($_REQUEST['modfunc']);
    }
}

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'update')
    unset($_REQUEST['modfunc']);

if (!$_REQUEST['modfunc']) {
    echo '<div id="dc"></div>';
    echo '<h5 class="text-primary">Medical Information</h5>';
    $_REQUEST['category_id'] = 2;
    
    echo '<div class="form-horizontal">';
    
    echo '<div class="row">';
    echo '<div class="col-md-6">';
    echo '<div class="form-group">'.TextInput($student['PHYSICIAN'], 'medical_info[PHYSICIAN]', 'Primary Care Physician', 'class=cell_medium maxlength=100').'</div>';
    echo '</div><div class="col-md-6">';
    echo '<div class="form-group">'.TextInput($student['PHYSICIAN_PHONE'], 'medical_info[PHYSICIAN_PHONE]', 'Physician\'s Phone', 'class=cell_medium maxlength=100').'</div>';
    echo '</div>'; //.col-md-6
    echo '</div>'; //.row
    
    echo '<div class="row">';
    echo '<div class="col-md-6">';
    echo '<div class="form-group">'.TextInput($student['PREFERRED_HOSPITAL'], 'medical_info[PREFERRED_HOSPITAL]', 'Preferred Medical Facility', 'class=cell_medium maxlength=100').'</div>';
    echo '</div>'; //.col-md-6    
    echo '</div>'; //.row    
    

    
    include('modules/students/includes/OtherInfoInc.php');
       
    echo '</div>'; //.form-horizontal
    
    echo '<br/>';

    $table = 'student_medical_notes';

    $functions = array('DOCTORS_NOTE_COMMENTS' => '_makeAlertComments');
    $med_RET = DBGet(DBQuery('SELECT ID,STUDENT_ID,DOCTORS_NOTE_DATE,DOCTORS_NOTE_COMMENTS
                    FROM student_medical_notes
                    WHERE STUDENT_ID=\'' . UserStudentID() . '\''), $functions);
    foreach ($med_RET as $mi => $md) {
        $med_RET[$mi]['DOCTORS_NOTE_DATE'] = _makeDate($md['DOCTORS_NOTE_DATE'], 'DOCTORS_NOTE_DATE', $mi, array('ID' => $md['ID'], 'TABLE' => 'student_medical_notes'));
    }
    $counter_for_date = count($med_RET) + 1;
    $columns = array('DOCTORS_NOTE_DATE' => 'Date', 'DOCTORS_NOTE_COMMENTS' => 'Doctor\'s Note');
    $link['add']['html'] = array('DOCTORS_NOTE_DATE' => _makeDate('', 'DOCTORS_NOTE_DATE', $counter_for_date), 'DOCTORS_NOTE_COMMENTS' => _makeAlertComments('', 'DOCTORS_NOTE_COMMENTS'));
    $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&include=$_REQUEST[include]&modfunc=delete&table=student_medical_notes&title=" . urlencode('Medical Note');
    $link['remove']['variables'] = array('id' => 'ID');
    if (!isset($_REQUEST['dwnl']) || $_REQUEST['dwnl'] == 'notes'){
        echo '<div class="panel panel-default">';
        echo '<div class="table-responsive">';
        ListOutput_Medical($med_RET, $columns, 'Medical Note', 'Medical Notes', $link, 'notes', array(), array('search' => false));
        echo '</div>';
        echo '</div>';
    }

    
    
    /*
     * Immunization/Physical Records
     */
    $table = 'student_immunization';

    $functions = array('TYPE' => '_makeType', 'COMMENTS' => '_makeAlertComments');
    $med_RET = DBGet(DBQuery('SELECT ID,TYPE,MEDICAL_DATE,COMMENTS FROM student_immunization WHERE STUDENT_ID=\'' . UserStudentID() . '\' ORDER BY MEDICAL_DATE,TYPE'), $functions);
    $columns = array('TYPE' => 'Type', 'MEDICAL_DATE' => 'Date', 'COMMENTS' => 'Comments');
    foreach ($med_RET as $mi => $md) {
        $counter_for_date = $counter_for_date + 1;
        $med_RET[$mi]['MEDICAL_DATE'] = _makeDate($md['MEDICAL_DATE'], 'MEDICAL_DATE', $counter_for_date, array('ID' => $md['ID'], 'TABLE' => 'student_immunization'));
    }
    $counter_for_date = $counter_for_date + 1;
    $link['add']['html'] = array('TYPE' => _makeType('', 'TYPE'), 'MEDICAL_DATE' => _makeDate('', 'MEDICAL_DATE', $counter_for_date), 'COMMENTS' => _makeAlertComments('', 'COMMENTS'));
    $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&include=$_REQUEST[include]&modfunc=delete&table=student_immunization&title=" . urlencode('immunization or physical');
    $link['remove']['variables'] = array('id' => 'ID');

    if (count($med_RET) == 0)
        $plural = 'Immunizations or Physicals';
    else
        $plural = 'Immunizations and Physicals';

    
    echo '<div class="panel panel-default"><div class="panel-heading"><h5 class="panel-title">Immunization/Physical Record</h5></div>';
        
    if (!isset($_REQUEST['dwnl']) || $_REQUEST['dwnl'] == 'immunizations'){
        echo '<div class="table-responsive">';
        ListOutput_Medical($med_RET, $columns, 'Immunization or Physical', $plural, $link, 'immunizations', array(), array('search' => false));
        echo '</div>';
        echo '</div>';
    }
    
    $table = 'student_medical_alerts';

    $functions = array('TITLE' => '_makeAlertComments');
    $med_RET = DBGet(DBQuery('SELECT ID,TITLE,ALERT_DATE FROM student_medical_alerts WHERE STUDENT_ID=\'' . UserStudentID() . '\' ORDER BY ID'), $functions);
    $columns = array('ALERT_DATE' => 'Alert Date', 'TITLE' => 'Medical Alert');
    foreach ($med_RET as $mi => $md) {
        $counter_for_date = $counter_for_date + 1;
        $med_RET[$mi]['ALERT_DATE'] = _makeDate($md['ALERT_DATE'], 'ALERT_DATE', $counter_for_date, array('ID' => $md['ID'], 'TABLE' => 'student_medical_alerts'));
    }
    $counter_for_date = $counter_for_date + 1;
    $link['add']['html'] = array('ALERT_DATE' => _makeDate('', 'ALERT_DATE', $counter_for_date), 'TITLE' => _makeAlertComments('', 'TITLE'));
    $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&include=$_REQUEST[include]&modfunc=delete&table=student_medical_alerts&title=" . urlencode('medical alert');
    $link['remove']['variables'] = array('id' => 'ID');
    
    /*
     * Medical Alerts
     */
    echo '<div class="panel panel-default"><div class="panel-heading"><h5 class="panel-title">Medical Alert</h5></div>';
    if (!isset($_REQUEST['dwnl']) || $_REQUEST['dwnl'] == 'medical'){
        echo '<div class="table-responsive">';
        ListOutput_Medical($med_RET, $columns, 'Medical Alert', 'Medical Alerts', $link, 'medical', array(), array('search' => false));
        echo '</div>';
        echo '</div>';
    }

   
    $table = 'student_medical_visits';

    $functions = array('TIME_IN' => '_makeComments', 'TIME_OUT' => '_makeComments', 'REASON' => '_makeComments', 'RESULT' => '_makeComments', 'COMMENTS' => '_makeLongComments');
    $med_RET = DBGet(DBQuery('SELECT ID,SCHOOL_DATE,TIME_IN,TIME_OUT,REASON,RESULT,COMMENTS FROM student_medical_visits WHERE STUDENT_ID=\'' . UserStudentID() . '\' ORDER BY SCHOOL_DATE'), $functions);
    $columns = array('SCHOOL_DATE' => 'Date', 'TIME_IN' => 'Time In', 'TIME_OUT' => 'Time Out', 'REASON' => 'Reason', 'RESULT' => 'Result', 'COMMENTS' => 'Comments');
    foreach ($med_RET as $mi => $md) {
        $counter_for_date = $counter_for_date + 1;
        $med_RET[$mi]['SCHOOL_DATE'] = _makeDate($md['SCHOOL_DATE'], 'SCHOOL_DATE', $counter_for_date, array('ID' => $md['ID'], 'TABLE' => 'student_medical_visits'));
    }
    $counter_for_date = $counter_for_date + 1;
    $link['add']['html'] = array('SCHOOL_DATE' => _makeDate('', 'SCHOOL_DATE', $counter_for_date), 'TIME_IN' => _makeComments('', 'TIME_IN'), 'TIME_OUT' => _makeComments('', 'TIME_OUT'), 'REASON' => _makeComments('', 'REASON'), 'RESULT' => _makeComments('', 'RESULT'), 'COMMENTS' => _makeLongComments('', 'COMMENTS'));
    $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&include=$_REQUEST[include]&modfunc=delete&table=student_medical_visits&title=" . urlencode('visit');
    $link['remove']['variables'] = array('id' => 'ID');
    
    /*
     * Nurse Visit Records
     */
    echo '<div class="panel panel-default m-b-0"><div class="panel-heading"><h4 class="panel-title">Nurse Visit Record</h4></div>';
    if (!isset($_REQUEST['dwnl']) || $_REQUEST['dwnl'] == 'nurse'){
        echo '<div class="table-responsive">';
        ListOutput_Medical($med_RET, $columns, 'Nurse Visit', 'Nurse Visits', $link, 'nurse', array(), array('search' => false));
        echo '</div>';
        echo '</div>';
    }

}
?>