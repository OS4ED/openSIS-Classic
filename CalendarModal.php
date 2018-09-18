<?php

include('RedirectRootInc.php');
include'ConfigInc.php';
include 'Warehouse.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//----------------------- modal for event start---------------------//


if (($_REQUEST['event_id'] || !isset($_REQUEST['event_id'])) && !isset($_REQUEST[assignment_id])) {
    if ($_REQUEST['event_id'] != 'new' && isset($_REQUEST['event_id'])) {
        $RET = DBGet(DBQuery("SELECT TITLE,DESCRIPTION,SCHOOL_DATE,CALENDAR_ID FROM calendar_events WHERE ID='$_REQUEST[event_id]'"));
        $title = $RET[1]['TITLE'];
        $calendar_id = $RET[1]['CALENDAR_ID'];
    } else {
        $_REQUEST['event_id'] = 'new';
        $title = 'New Event';
        $RET[1]['SCHOOL_DATE'] = date('Y-m-d', strtotime($_REQUEST['school_date']));
        $RET[1]['CALENDAR_ID'] = '';
        $calendar_id = $_REQUEST['calendar_id'];
    }
    echo "<FORM name=popform class=\"m-b-0\" id=popform action=Modules.php?modname=schoolsetup/Calendar.php&dd=$_REQUEST[school_date]&modfunc=detail&event_id=$_REQUEST[event_id]&calendar_id=$calendar_id&month=$_REQUEST[month]&year=$_REQUEST[year] METHOD=POST>";
} else {
    $RET = DBGet(DBQuery('SELECT TITLE,STAFF_ID,DATE_FORMAT(DUE_DATE,\'%d-%b-%y\') AS SCHOOL_DATE,ASSIGNED_DATE,DUE_DATE,DESCRIPTION FROM gradebook_assignments WHERE ASSIGNMENT_ID=\'' . $_REQUEST[assignment_id] . '\''));
    $title = $RET[1]['TITLE'];
    $RET[1]['STAFF_ID'] = GetTeacher($RET[1]['STAFF_ID']);
}

echo '<div class="modal-body">';

echo '<div id=err_message ></div>';


if ($RET[1]['TITLE'] == '') {
    echo '<div class="form-group">';
    echo '<label class="control-label">Title : &nbsp;</label>';
    echo (User('PROFILE') == 'admin' ? TextInputModal($RET[1]['TITLE'], 'values[TITLE]', '', 'id=title placeholder="Enter Title"') : $RET[1]['TITLE']);
    echo '</div>';
} else {
    echo '<div class="form-group">';
    echo '<label class="control-label">Title : &nbsp;</label>';
    //echo (User('PROFILE') == 'admin' ? TextInputCusIdModal($RET[1]['TITLE'], 'values[TITLE]', '', ' placeholder="Enter Title"', true, 'title') : $RET[1]['TITLE']);
    echo (User('PROFILE') == 'admin' ? '<input class="form-control" id="values[TITLE]" name="values[TITLE]" value="' . $RET[1]['TITLE'] . '" placeholder="Enter Title" size="10" type="text">' : $RET[1]['TITLE']);
    echo '</div>';
}

if ($RET[1]['STAFF_ID']) {
    echo '<div class="form-group"><label class="control-label">Teacher : &nbsp;</label>' . (User('PROFILE') == 'admin' ? TextAreaInput($RET[1]['STAFF_ID'], 'values[STAFF_ID]', '', 'placeholder="Enter Teacher"') : $RET[1]['STAFF_ID']) . '</div>';
}

if ($RET[1]['ASSIGNED_DATE']) {
    echo '<div class="form-group"><label class="control-label">Assigned Date : &nbsp;</label>' . (User('PROFILE') == 'admin' ? TextAreaInput($RET[1]['ASSIGNED_DATE'], 'values[ASSIGNED_DATE]', '', 'placeholder="Enter Assigned Date"') : $RET[1]['ASSIGNED_DATE']) . '</div>';
}

if ($RET[1]['DUE_DATE']) {
    echo '<div class="form-group"><label class="control-label">Due Date : &nbsp;</label>' . (User('PROFILE') == 'admin' ? TextAreaInput($RET[1]['DUE_DATE'], 'values[DUE_DATE]', '', 'placeholder="Enter Due Date"') : $RET[1]['DUE_DATE']) . '</div>';
}
echo '<div class="form-group">';
echo '<label class="control-label">Notes : &nbsp;</label>';
//echo (User('PROFILE') == 'admin' ? TextAreaInputModal(html_entity_decode($RET[1]['DESCRIPTION']), 'values[DESCRIPTION]', '', 'style=height:200px;  placeholder="Enter Notes"') : html_entity_decode($RET[1]['DESCRIPTION']));
if ($RET[1]['DESCRIPTION'] != '') {
    $RET[1]['DESCRIPTION'] = html_entity_decode(html_entity_decode($RET[1]['DESCRIPTION']));
    $RET[1]['DESCRIPTION'] = strip_tags($RET[1]['DESCRIPTION']);
}
echo (User('PROFILE') == 'admin' ? '<input class="form-control" id="values[DESCRIPTION]" name="values[DESCRIPTION]" value="' . $RET[1]['DESCRIPTION'] . '" placeholder="Enter Notes" size="10" type="textArea">' : $RET[1]['DESCRIPTION']);
echo '</div>';

//        if (AllowEdit())
if (User('PROFILE') == 'admin')
{
    if($_REQUEST['event_id']!='new')
    {
    if($RET[1]['CALENDAR_ID']==0)
    $RET[1]['CALENDAR_ID']=1;
    else
    $RET[1]['CALENDAR_ID']=0;
    }
    echo '<div class="form-group">' . CheckboxInputSwitchModal($RET[1]['CALENDAR_ID'], 'new', 'Show Events System Wide', '', false, 'Yes', 'No', '', 'switch-success') . '</div>';
}
//else
//echo '<div class="form-group">' . ($RET[1]['CALENDAR_ID'] == '' ? '<i class="icon-checkbox-checked"></i>' : '<i class="icon-checkbox-unchecked"></i>') . '</div>';

echo '</div>'; //.modal-body

if (User('PROFILE') == 'admin') {
    echo '<div class="modal-footer">';
    echo '<INPUT type=submit class="btn btn-primary" name=button value=Save >';
    echo '&nbsp;';
    if ($_REQUEST['event_id'] != 'new') {
        echo '<INPUT type=submit name=button class="btn btn-white" value=Delete >';
    }
    echo '</div>'; //.modal-footer
}

echo '</FORM>';

unset($_REQUEST['values']);
unset($_SESSION['_REQUEST_vars']['values']);
unset($_REQUEST['button']);
unset($_SESSION['_REQUEST_vars']['button']);
