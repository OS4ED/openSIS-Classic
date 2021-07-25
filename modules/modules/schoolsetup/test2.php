<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

  $colmn = Calender_Id;
    $cal_id = paramlib_validation($colmn, $_REQUEST['calendar_id']);
    $acs_RET = DBGet(DBQuery('SELECT TITLE, DEFAULT_CALENDAR FROM school_calendars WHERE CALENDAR_ID=\'' . $cal_id . '\''));
    $acs_RET = $acs_RET[1];
    $ac_RET = DBGet(DBQuery('SELECT MIN(SCHOOL_DATE) AS START_DATE,MAX(SCHOOL_DATE) AS END_DATE FROM attendance_calendar WHERE CALENDAR_ID=\'' . $cal_id . '\''));
    $ac_RET = $ac_RET[1];

    $day_RET = DBGet(DBQuery('SELECT DAYNAME(SCHOOL_DATE) AS DAY_NAME FROM attendance_calendar WHERE CALENDAR_ID=\'' . $cal_id . '\' ORDER BY SCHOOL_DATE LIMIT 0, 7'));
    $i = 0;
    foreach ($day_RET as $day) {
        $weekdays[$i] = $day['DAY_NAME'];
        $i++;
    }
    $message = '<div class="row">';
    $message .= '<div class="col-md-12">';

    $message = '<div class="row">';

    $message .= '<div class="col-md-8">';
    $message .= '<div class="form-group">';
    $message .= '<label class="col-md-2 control-label text-right">Title</label>';
    $message .= '<div class="col-md-10">';
    $message .= '<INPUT type=text name=title class=form-control id=title value="' . $acs_RET[TITLE] . '">';
    $message .= '<div class="checkbox"><label><INPUT type=checkbox name=default value=Y ' . (($acs_RET['DEFAULT_CALENDAR'] == 'Y') ? 'checked' : '') . '> Default Calendar for this School</label></div>';
    $message .= '</div>';
    $message .= '</div>'; //.form-group
    $message .= '</div>'; //.col-md-4
    $message .= '<div class="col-md-4">';
    $message .= '</div>'; //.col-md-4

    $message .= '</div>'; //.row    
    $message .= '<div class="row">';

    $message .= '<div class="col-md-4">';
    $message .= '<div class="form-group">';
    $message .= '<label class="col-md-4 control-label text-right">From</label>';
    $message .= '<div class="col-md-8">' . DateInputAY($ac_RET['START_DATE'], '_min', 1) . '</div>';
    $message .= '</div>'; //.form-group
    $message .= '</div>'; //.col-md-4
    $message .= '<div class="col-md-4">';
    $message .= '<div class="form-group">';
    $message .= '<label class="col-md-4 control-label text-right">To</label>';
    $message .= '<div class="col-md-8">' . DateInputAY($ac_RET['END_DATE'], '_max', 2) . '</div>';
    $message .= '</div>'; //.form-group
    $message .= '</div>'; //.col-md-4

    $message .= '</div>'; //.row 
    $message .= '<div class="row">';

    $message .= '<div class="col-md-4">';
    $message .= '<div class="form-group">';
    $message .= '<label class="col-md-4 control-label text-right">Weekdays</label>';
    $message .= '<div class="col-md-8"><div class="checkbox"><label><INPUT type=checkbox value=Y name=weekdays[0] ' . ((in_array('Sunday', $weekdays) == true) ? 'CHECKED' : '') . ' DISABLED> Sunday</label></div><div class="checkbox"><label><INPUT type=checkbox value=Y name=weekdays[1] ' . ((in_array('Monday', $weekdays) == true) ? 'CHECKED' : '') . ' DISABLED> Monday</label></div><div class="checkbox"><label><INPUT type=checkbox value=Y name=weekdays[2] ' . ((in_array('Tuesday', $weekdays) == true) ? 'CHECKED' : '') . ' DISABLED> Tuesday</label></div><div class="checkbox"><label><INPUT type=checkbox value=Y name=weekdays[3] ' . ((in_array('Wednesday', $weekdays) == true) ? 'CHECKED' : '') . ' DISABLED> Wednesday</label></div><div class="checkbox"><label><INPUT type=checkbox value=Y name=weekdays[4] ' . ((in_array('Thursday', $weekdays) == true) ? 'CHECKED' : '') . ' DISABLED> Thursday</label></div><div class="checkbox"><label><INPUT type=checkbox value=Y name=weekdays[5] ' . ((in_array('Friday', $weekdays) == true) ? 'CHECKED' : '') . ' DISABLED> Friday</label></div><div class="checkbox"><label><INPUT type=checkbox value=Y name=weekdays[6] ' . ((in_array('Saturday', $weekdays) == true) ? 'CHECKED' : '') . ' DISABLED> Saturday</label></div></div>';
    $message .= '</div>'; //.form-group
    $message .= '</div>'; //.col-md-4
    $message .= '<div class="col-md-4">';
    $message .= calendarEventsVisibility();
    $message .= '</div>'; //.col-md-4

    $message .= '</div>'; //.row

    $message .= '</div>'; //.col-md-12
    $message .= '</div>'; //.row