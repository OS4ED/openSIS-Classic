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
                $_REQUEST['event_id']='new';
                $title = 'New Event';
                $RET[1]['SCHOOL_DATE'] = date('Y-m-d', strtotime($_REQUEST['school_date']));
                $RET[1]['CALENDAR_ID'] = '';
                $calendar_id = $_REQUEST['calendar_id'];
            }
            echo "<FORM name=popform class=\"form-horizontal\" id=popform action=Modules.php?modname=schoolsetup/Calendar.php&dd=$_REQUEST[school_date]&modfunc=detail&event_id=$_REQUEST[event_id]&calendar_id=$calendar_id&month=$_REQUEST[month]&year=$_REQUEST[year] METHOD=POST>";
        } else {
            $RET = DBGet(DBQuery('SELECT TITLE,STAFF_ID,DATE_FORMAT(DUE_DATE,\'%d-%b-%y\') AS SCHOOL_DATE,ASSIGNED_DATE,DUE_DATE,DESCRIPTION FROM gradebook_assignments WHERE ASSIGNMENT_ID=\'' . $_REQUEST[assignment_id] . '\''));
            $title = $RET[1]['TITLE'];
            $RET[1]['STAFF_ID'] = GetTeacher($RET[1]['STAFF_ID']);
        }

        PopTable('header', $title);
        echo '<div id=err_message ></div><br/>';

        echo '<div class="form-group"><label class="control-label text-right col-md-4">Date</label><div class="col-md-8">' . date("Y/M/d", strtotime($RET[1]['SCHOOL_DATE'])) . '</div></div>';

        if ($RET[1]['TITLE'] == '') {
            echo '<div class="form-group">' . (User('PROFILE') == 'admin' ? TextInputModal($RET[1]['TITLE'], 'values[TITLE]', 'Title', 'id=title') : $RET[1]['TITLE']) . '</div>';
        } else {
            echo '<div class="form-group">' . (User('PROFILE') == 'admin' ? TextInputCusIdModal($RET[1]['TITLE'], 'values[TITLE]', 'Title', '', true, 'title') : $RET[1]['TITLE']) . '</div>';
        }

        if ($RET[1]['STAFF_ID']) {
            echo '<div class="form-group"><label class="control-label text-right col-md-4">Teacher</label><div class="col-md-8">' . (User('PROFILE') == 'admin' ? TextAreaInput($RET[1]['STAFF_ID'], 'values[STAFF_ID]') : $RET[1]['STAFF_ID']) . '</div></div>';
        }
        
        if ($RET[1]['ASSIGNED_DATE']) {
            echo '<div class="form-group"><label class="control-label text-right col-md-4">Assigned Date</label><div class="col-md-8">' . (User('PROFILE') == 'admin' ? TextAreaInput($RET[1]['ASSIGNED_DATE'], 'values[ASSIGNED_DATE]') : $RET[1]['ASSIGNED_DATE']) . '</div></div>';
        }
        
        if ($RET[1]['DUE_DATE']) {
            echo '<div class="form-group"><label class="control-label text-right col-md-4">Due Date</label><div class="col-md-8">' . (User('PROFILE') == 'admin' ? TextAreaInput($RET[1]['DUE_DATE'], 'values[DUE_DATE]') : $RET[1]['DUE_DATE']) . '</div></div>';
        }
        echo '<div class="form-group">' . (User('PROFILE') == 'admin' ? TextAreaInputModal(html_entity_decode($RET[1]['DESCRIPTION']), 'values[DESCRIPTION]', 'Notes', 'style=height:200px;'): html_entity_decode($RET[1]['DESCRIPTION'])) . '</div>';
        
//        if (AllowEdit())
            if (User('PROFILE') == 'admin')
            {
            if (User('PROFILE') == 'admin')
                echo '<div class="form-group"><div class="col-xs-12">' . CheckboxInputSwitchModal($RET[1]['CALENDAR_ID'], $_REQUEST['event_id'], 'Show Events System Wide') . '</div></div>';
            else
                echo '<div class="form-group"><div class="col-xs-12">' . ($RET[1]['CALENDAR_ID'] == '' ? '<i class="icon-checkbox-checked"></i>' : '<i class="icon-checkbox-unchecked"></i>') . '</div></div>';
//onclick="return formcheck_calendar_event(); closeModalMod(\'modal_default_calendar\')"
            if (User('PROFILE') == 'admin')
                echo '<INPUT type=submit class="btn btn-primary" name=button value=Save >';
            echo '&nbsp;';
            if ($_REQUEST['event_id'] != 'new' && User('PROFILE') == 'admin')
            {
              //  echo '<INPUT type=submit name=button class="btn btn-white" value=Delete onclick="formload_ajax(\'popform\');">';
            echo '<INPUT type=submit name=button class="btn btn-white" value=Delete >';
                }
        }
        else {
            echo '<div class="form-group"><label class="control-label text-right col-md-4">Show Events System Wide</label><div class="col-md-8">' . ($RET[1]['CALENDAR_ID'] == '' ? '<i class="icon-checkbox-checked"></i>' : '<i class="icon-checkbox-unchecked"></i>') . '</div></div>';
        }
        
        PopTable('footer');
        echo '</FORM>';

        unset($_REQUEST['values']);
        unset($_SESSION['_REQUEST_vars']['values']);
        unset($_REQUEST['button']);
        unset($_SESSION['_REQUEST_vars']['button']);