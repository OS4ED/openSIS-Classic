<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
   $fy_RET = DBGet(DBQuery('SELECT START_DATE,END_DATE FROM school_years WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''));
    $fy_RET = $fy_RET[1];

    $message = '<div class="row">';
    $message .= '<div class="col-md-12">';
    $message .= '<div class="row">';

    $message .= '<div class="col-md-8">';
    $message .= '<div class="form-group">';
    $message .= '<label class="col-md-2 control-label text-right">Title</label>';
    $message .= '<div class="col-md-10">';
    $message .= '<INPUT type=text name=title class=form-control id=title>';
    $message .= '<div class="checkbox"><label><INPUT type=checkbox name=default value=Y> Default Calendar for this School</label></div>';
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
    $message .= '<div class="col-md-8">' . DateInputAY($fy_RET['START_DATE'], '_min', 1) . '</div>';
    $message .= '</div>'; //.form-group
    $message .= '</div>'; //.col-md-4
    $message .= '<div class="col-md-4">';
    $message .= '<div class="form-group">';
    $message .= '<label class="col-md-4 control-label text-right">To</label>';
    $message .= '<div class="col-md-8">' . DateInputAY($fy_RET['END_DATE'], '_max', 2) . '</div>';
    $message .= '</div>'; //.form-group
    $message .= '</div>'; //.col-md-4

    $message .= '</div>'; //.row 
    $message .= '<div class="row">';

    $message .= '<div class="col-md-4">';
    $message .= '<div class="form-group">';
    $message .= '<label class="col-md-4 control-label text-right">Weekdays</label>';
    $message .= '<div class="col-md-8"><div class="checkbox"><label><INPUT type=checkbox value=Y name=weekdays[0]>Sunday</label></div> <div class="checkbox"><label><INPUT type=checkbox value=Y name=weekdays[1] CHECKED>Monday</label></div> <div class="checkbox"><label><INPUT type=checkbox value=Y name=weekdays[2] CHECKED>Tuesday</label></div> <div class="checkbox"><label><INPUT type=checkbox value=Y name=weekdays[3] CHECKED>Wednesday</label></div> <div class="checkbox"><label><INPUT type=checkbox value=Y name=weekdays[4] CHECKED>Thursday</label></div> <div class="checkbox"><label><INPUT type=checkbox value=Y name=weekdays[5] CHECKED>Friday</label></div> <div class="checkbox"><label><INPUT type=checkbox value=Y name=weekdays[6]>Saturday</label></div></div>';
    $message .= '</div>'; //.form-group
    $message .= '</div>'; //.col-md-4
    $message .= '<div class="col-md-4">';
    $message .= calendarEventsVisibility();
    $message .= '</div>'; //.col-md-4

    $message .= '</div>'; //.row

    $message .= '</div>'; //.col-md-12
    $message .= '</div>'; //.row