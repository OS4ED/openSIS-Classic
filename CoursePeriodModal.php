<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include('RedirectRootInc.php');
include'ConfigInc.php';
include 'Warehouse.php';
// include('functions/SqlSecurityFnc.php');

$cp_id = sqlSecurityFilter($_REQUEST['course_period_id']);
$meet_date = sqlSecurityFilter($_REQUEST['meet_date']);
$cpv_id = sqlSecurityFilter($_REQUEST['cpv_id']);
$subject_id = sqlSecurityFilter($_REQUEST['subject_id']);
$course_id = sqlSecurityFilter($_REQUEST['course_id']);
$id = sqlSecurityFilter($_REQUEST['id']);
$calendar_id = sqlSecurityFilter($_REQUEST['calendar_id']);
if(isset($_SESSION['language']) && $_SESSION['language']=='fr'){
    define("_classRoom","Salle de cours");
    define("_period","Période");
    define("_days","Journées");
    define("_takesAttendance","La participation prend");
    define("_room","Chambre");
    define("_time","Temps");
}
elseif(isset($_SESSION['language']) && $_SESSION['language']=='es'){
    define("_classRoom","Salón de clases");
    define("_period","Período");
    define("_days","Dias");
    define("_takesAttendance","toma de Asistencia");
    define("_room","Habitación");
    define("_time","Hora");
}else{
    define("_classRoom","Class Room");
    define("_period","Period");
    define("_days","Days");
    define("_takesAttendance","Takes Attendance");
    define("_room","Room");
    define("_time","Time");
}

if ($_POST['button'] == 'Clear & Exit') {
        $chek_assoc = DBGet(DBQuery('SELECT COUNT(*) as REC_EX FROM schedule WHERE COURSE_PERIOD_ID=' .$cp_id . ' AND (START_DATE<=\'' . date('Y-m-d') . '\' AND (END_DATE IS NULL OR END_DATE=\'0000-00-00\' OR END_DATE>=\'' . date('Y-m-d') . '\' ))'));
        if ($chek_assoc[1]['REC_EX'] == 0) {
            DBQuery("DELETE FROM course_period_var WHERE course_period_id=$cp_id AND  course_period_date='" . $meet_date . "' and id='" . $cpv_id . "'");
            unset($_REQUEST['values']);
            unset($_SESSION['_REQUEST_vars']['values']);
            echo '<SCRIPT language=javascript>window.location.href = "Modules.php?modname=' . $_REQUEST['modname'] . '&subject_id=' . $subject_id . '&course_id=' . $course_id . '&course_period_id=' . $cp_id . '&month=' . date(strtotime($meet_date)) . '"; window.close();</script>';
        } else {
            echo '<SCRIPT language=javascript>window.location.href = "Modules.php?modname=' . $_REQUEST['modname'] . '&error=Blocked_assoc&subject_id=' . $subject_id . '&course_id=' . $course_id . '&course_period_id=' . $cp_id . '&month=' . date(strtotime($meet_date)) . '"; window.close();</script>';
        }
    }
    else {
        $cpblocked_RET = DBGet(DBQuery("SELECT COURSE_PERIOD_DATE,PERIOD_ID,ROOM_ID,DOES_ATTENDANCE FROM course_period_var where course_period_id=$cp_id AND course_period_date='" . $meet_date . "' AND id='" . $id . "'"));
        $cpblocked_RET = $cpblocked_RET[1];
        $periods_RET = DBGet(DBQuery("SELECT PERIOD_ID,TITLE FROM school_periods WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
        if (count($periods_RET)) {
            foreach ($periods_RET as $period)
                $periods[$period['PERIOD_ID']] = $period['TITLE'];
        }

        $room_RET = DBGet(DBQuery("SELECT ROOM_ID,TITLE FROM rooms WHERE SCHOOL_ID='" . UserSchool() . "' ORDER BY SORT_ORDER"));
        if (count($room_RET)) {
            foreach ($room_RET as $room)
                $rooms[$room['ROOM_ID']] = $room['TITLE'];
        }
        if (isset($_REQUEST['values'])) {
            echo '<div class="alert bg-danger alert-styled-left">' . $conflict . ' '._onSelectedDate.'</div>';
            unset($_REQUEST['values']);
            unset($_SESSION['_REQUEST_vars']['values']);
            $_REQUEST['id'] = $_REQUEST['cpv_id'];
            $id = $cpv_id;
        }
        //PopTableforWindow('header', $title);
        echo "<FORM class=form-horizontal name=popform id=popform action=ForWindow.php?modname=schoolsetup/Courses.php&meet_date=" . $meet_date . "&modfunc=detail&mode=" . $_REQUEST['mode'] . "&subject_id=" . $subject_id . "&course_id=" . $course_id . "&course_period_id=" . $cp_id . "&calendar_id=" . $calendar_id . " METHOD=POST>";
        echo '<div class="panel">';
        echo '<div class="tabbable">';
        echo '<ul class="nav nav-tabs nav-tabs-bottom no-margin-bottom"><li class="active"><a href="javascript:void(0);">'._addClass.'</a></li></ul>';
        
        echo '<div class="panel-body">';
        echo '<div id="block_error"></div>';
        echo '<input type="hidden" name="get_status" id="get_status" value="" />';
        echo '<input type="hidden" name="' . $date . '_id" id="' . $date . '_id" value="' .$cp_id . '"/>';
        echo '<input type="hidden" id="run_block_valid" value="block"/>';
        
        if ($_REQUEST['add'] == 'new')
            unset($cpblocked_RET);
        if ($id != '')
            echo "<input type=hidden name=cpv_id value='$id' />";
        echo '<div class="form-group"><label class="control-label text-right col-lg-4">'._date.'</label><div class="col-lg-8  m-t-10">' . ProperDate($meet_date) . '</div></div>';
        echo '<div class="form-group"><label class="control-label text-right col-lg-4">'._period.'</label><div class="col-lg-8">' . SelectInputModal($cpblocked_RET['PERIOD_ID'], 'values[PERIOD_ID]', '', $periods, 'N/A', 'id=' . $date . '_period class=form-control onchange="formcheck_periods_F2(' . $date . ');"') . '</div></div>';
        echo '<input type="hidden" id="hidden_period_block" value="' . $cpblocked_RET['PERIOD_ID'] . '" />';
        echo '<div class="form-group"><label class="control-label text-right col-lg-4">'._room.'</label><div class="col-lg-8">' . SelectInputModal($cpblocked_RET['ROOM_ID'], 'values[ROOM_ID]', '', $rooms, 'N/A', 'id=' . $date . '_room ') . '</div></div>';
        echo '<div class="form-group"><label class="control-label text-right col-lg-4">'._takesAttendance.'</label><div class="col-lg-8">' . CheckboxInputSwitchModal($cpblocked_RET['DOES_ATTENDANCE'], 'values[DOES_ATTENDANCE]', '', '', false, 'Yes', 'No', ' id=' . $date . '_does_attendance onclick="formcheck_periods_attendance_F2(' . (($date != '') ? $date : 1) . ',this);"','switch-success') . '<br><div id="ajax_output"></div></div></div>';
        
        echo '</div>'; //.panel-body
        echo '<div class="panel-footer p-l-10 p-r-10 text-right"><INPUT type=submit class="btn btn-primary" name=button value='._save.' onClick="return validate_block_schedule(' . $date . ');">';
       // echo '<div class="panel-footer p-l-10 p-r-10"><INPUT type=submit class="btn btn-primary" name=button value='._save.' onClick="return validate_block_schedule(' . $date . ');">';
        echo '&nbsp;';
        if ($_REQUEST['mode'] == 'edit')
          //  echo '<INPUT type=button name=button class="btn btn-primary" value=Clear1 & Exit onclick="formload_ajax(\'popform\');"> &nbsp ';
      echo ' &nbsp; <INPUT type=button name=button class="btn btn-default" value='._clearExit.'  onclick="BlockModalPeriod(\''.$subject_id.'\','.$course_id.',\''.$cp_id.'\',\''.$calendar_id.'\',\''.$meet_date.'\',\'edit\',\''.$id.'\')";> &nbsp ';
        
        else
            echo ' &nbsp; <INPUT type=submit name=button class="btn btn-default" value='._close.' onclick="window.close();">';
        echo '</div>'; //.panel-footer
        
        echo '</div>'; //.tabbable
        echo '</div>'; //.panel
        //PopTableWindow('footer');
        echo '</FORM>';
    }
