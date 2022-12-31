<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include('RedirectRootInc.php');
include'ConfigInc.php';
include 'Warehouse.php';
// include('functions/SqlSecurityFnc.php');

$id = sqlSecurityFilter($_REQUEST['id']);

if ($_REQUEST['table_name'] != '' && $_REQUEST['table_name'] == 'course_periods') {

    $sql = "SELECT COURSE_PERIOD_ID AS CHECKBOX,COURSE_PERIOD_ID,TITLE,SHORT_NAME,COALESCE(TOTAL_SEATS-FILLED_SEATS,0) AS AVAILABLE_SEATS FROM course_periods WHERE COURSE_ID='$id'AND (marking_period_id IS NOT NULL AND marking_period_id IN(" . GetAllMP(GetMPTable(GetMP(UserMP(), 'TABLE')), UserMP()) . ") OR marking_period_id IS NULL AND '" . date('Y-m-d') . "' <= end_date) ORDER BY TITLE";
    $QI = DBQuery($sql);

    $coursePeriods_RET = DBGet($QI);
    $html = 'cp_modal_cp||';

    $html .= '<h6>' . count($coursePeriods_RET) . ((count($coursePeriods_RET) == 1) ? ' '._periodWas.'' : ' '._periodsWere.'') . ' '._found.'.</h6>';

    $html .= '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>&nbsp;</th><th>'._coursePeriods.'</th><th>'._availableSeats.'</th></tr></thead>';
    $html .= '<tbody>';
    foreach ($coursePeriods_RET as $val) {
        $html .= '<tr><td><input type="checkbox" id="course_' . $val['COURSE_PERIOD_ID'] . '" name="course_periods[' . $val['COURSE_PERIOD_ID'] . ']" value=' . $val['COURSE_PERIOD_ID'] . ' onchange="verify_schedule(this);"></td><td><a href=javascript:void(0); onclick="grab_coursePeriod(' . $val['COURSE_PERIOD_ID'] . ',\'course_periods\',\'subject_id\')">' . $val['TITLE'] . ' - ' . $val['SHORT_NAME'] . '</a></td><td>' . $val['AVAILABLE_SEATS'] . '</td></tr>';
//           $html.= '<tr><td><input type="checkbox" id="course_'.$val['COURSE_PERIOD_ID'].'" name="course_periods['.$val['COURSE_PERIOD_ID'].']" value='.$val['COURSE_PERIOD_ID'].'></td><td><a href=javascript:void(0); onclick="scheduleCP('.$val['COURSE_PERIOD_ID'].')">'.$val['TITLE'].'</a></td><td>'.$val['AVAILABLE_SEATS'].'</td></tr>';
    }
    $html .= '</tbody>';
    $html .= '</table>';
//    $html.= '<table id="selected_course1" style="display: none;"><tr><td></td></tr></table>';
    
}

if ($_REQUEST['table_name'] != '' && $_REQUEST['table_name'] == 'courses') {

    $sql = "SELECT COURSE_ID,c.TITLE, CONCAT_WS(' - ',c.short_name,c.title) AS GRADE_COURSE FROM courses c LEFT JOIN school_gradelevels sg ON c.grade_level=sg.id WHERE SUBJECT_ID='$id' ORDER BY c.TITLE";
    $QI = DBQuery($sql);
    $courses_RET = DBGet($QI);
    $html = 'course_modal_cp||';
    $html .= '<h6>' . count($courses_RET) . ((count($courses_RET) == 1) ? ' '._courseWas.'' : ' '._coursesWere.'') . ' '._found.'.</h6>';
    $html .= '<table  class="table table-bordered"><thead><tr class="alpha-grey"><th>'._course.'</th></tr></thead>';
    $html .= '<tbody>';
    foreach ($courses_RET as $val) {

        $html .= '<tr><td><a href=javascript:void(0); onclick="grab_coursePeriod(' . $val['COURSE_ID'] . ',\'course_periods\',\'subject_id\')">' . $val['GRADE_COURSE'] . '</a></td></tr>';
    }
    $html .= '</tbody>';
    $html .= '</table>';
}

echo $html;
?>
