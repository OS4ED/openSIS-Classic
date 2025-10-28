<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include('RedirectRootInc.php');
include('ConfigInc.php');
include('Warehouse.php');

$id = sqlSecurityFilter($_REQUEST['id'] ?? null);
if ($id) $_SESSION['UserMP'] = $id;

$RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID, TITLE FROM school_quarters WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
if (!$RET) {
    $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID, TITLE FROM school_semesters WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
}
if (!$RET) {
    $RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID, TITLE FROM school_years WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER"));
}

$html = '<select class="select" name="mp" id="head_frm_mp_id" onchange="this.form.submit();">';
foreach ($RET as $mp) {
    $selected = ($mp['MARKING_PERIOD_ID'] == ($_SESSION['UserMP'] ?? '')) ? ' selected' : '';
    $html .= '<option value="' . $mp['MARKING_PERIOD_ID'] . '"' . $selected . '>' . $mp['TITLE'] . '</option>';
}
$html .= '</select>';

echo $html;
?>