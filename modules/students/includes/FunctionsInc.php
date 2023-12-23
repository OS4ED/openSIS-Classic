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

function _makeTextInput($column, $name, $size, $request = 'students', $title = "")
{
    global $value, $field;

    if ($_REQUEST['student_id'] == 'new' && $field['DEFAULT_SELECTION']) {
        $value[$column] = $field['DEFAULT_SELECTION'];
        $div = false;
        $req = $field['REQUIRED'] == 'Y' ? array('<FONT color=red>', '</FONT>') : array('', '');
    } else {
        $div = true;
        $req = $field['REQUIRED'] == 'Y' && $value[$column] == '' ? array('<FONT color=red>', '</FONT>') : array('', '');
    }
    if ($_REQUEST['modname'] == 'students/PrintStudentInfo.php') {
        if ($field['TYPE'] == 'numeric')
            $value[$column] = str_replace('.00', '', $value[$column]);
        if ($field['TYPE'] == 'multiple')
            $value[$column] = rtrim(ltrim(str_replace('||', ', ', $value[$column]), ', '), ', ');
        if ($field['TYPE'] == 'radio' && $value[$column] == 'Y')
            $value[$column] = str_replace('Y', _yes, $value[$column]);
        if ($field['TYPE'] == 'codeds') {
            $codedarr = explode("\n", $field['SELECT_OPTIONS']);
            foreach ($codedarr as $val) {
                $parts = explode('|', $val);
                if ($value[$column] == $parts[0]) {
                    $value[$column] = $parts[1];
                }
            }
        }
    }


    return TextInput($value[$column], $request . '[' . $column . ']', $title, $size, $div);
}

function _makeDateInput($column, $name, $request = 'students')
{ //for custom fields
    global $value, $field;

    if ($_REQUEST['student_id'] == 'new' && $field['DEFAULT_SELECTION']) {
        $value[$column] = $field['DEFAULT_SELECTION'];
        $div = false;
        $req = $field['REQUIRED'] == 'Y' ? array('<FONT color=red>', '</FONT>') : array('', '');
    } else {
        $div = true;
        $req = $field['REQUIRED'] == 'Y' && $value[$column] == '' ? array('<FONT color=red>', '</FONT>') : array('', '');
    }

    return DateInput($value[$column], $request . '[' . $column . ']', $req[0] . $name . $req[1], $div);
}

function _makeSelectInput($column, $name, $request = 'students', $title = "")
{
    global $value, $field;

    if ($_REQUEST['student_id'] == 'new' && $field['DEFAULT_SELECTION']) {
        $value[$column] = $field['DEFAULT_SELECTION'];
        $div = false;
        $req = $field['REQUIRED'] == 'Y' ? array('<FONT color=red>', '</FONT>') : array('', '');
    } else {
        $field_err = false;
        $div = true;
        $req = $field['REQUIRED'] == 'Y' && $value[$column] == '' ? array('<FONT color=red>', '</FONT>') : array('', '');
    }

    $field['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $field['SELECT_OPTIONS']));
    $select_options = explode("\r", $field['SELECT_OPTIONS']);
    if (count($select_options)) {
        foreach ($select_options as $option) {
            if ($field['TYPE'] == 'codeds') {
                $option = explode('|', $option);
                if ($option[0] != '' && $option[1] != '')
                    $options[$option[0]] = $option[1];
            } else
                $options[$option] = $option;
        }
    }

    //$extra = 'class=cell_medium';
    return SelectInput($value[$column], $request . '[' . $column . ']', $title, $options, 'N/A', $extra, $div);
}

function _makeAutoSelectInput($column, $name, $request = 'students')
{
    global $value, $field;

    if ($_REQUEST['student_id'] == 'new' && $field['DEFAULT_SELECTION']) {
        $value[$column] = $field['DEFAULT_SELECTION'];
        $div = false;
        $req = $field['REQUIRED'] == 'Y' ? array('<span class="text-danger">', '</span>') : array('', '');
    } else {
        $div = true;
        $req = $field['REQUIRED'] == 'Y' && ($value[$column] == '' || $value[$column] == '---') ? array('<span class="text-danger">', '</span>') : array('', '');
    }

    // build the select list...
    // get the standard selects
    if ($field['SELECT_OPTIONS']) {
        $field['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $field['SELECT_OPTIONS']));
        $select_options = explode("\r", $field['SELECT_OPTIONS']);
    } else
        $select_options = array();
    if (count($select_options)) {
        foreach ($select_options as $option)
            if ($option != '')
                $options[$option] = $option;
    }
    // add the 'new' option, is also the separator
    $options['---'] = '---';


    // make sure the current value is in the list
    if ($value[$column] != '' && !$options[$value[$column]])
        $options[$value[$column]] = array($value[$column], '<span class=' . ($field['TYPE'] == 'autos' ? 'text-primary' : 'text-success') . '>' . $value[$column] . '</span>');

    if ($value[$column] != '---' && count($options) > 1) {

        if (isset($num_of_cus_field)) {
            $generated = true;
        }
        $extra = '';
        return SelectInput($value[$column], $request . '[' . $column . ']', '', $options, 'N/A', $extra, $div);
    } else {
        if (trim($name) != '')
            return TextInput($value[$column] == '---' ? array('---', '<span class=text-danger>---</span>') : '' . $value[$column], $request . '[' . $column . ']', $req[0] . $name . $req[1], $size, $div);
        else
            return TextInput($value[$column] == '---' ? array('---', '<span class=text-danger>---</span>') : '' . $value[$column], $request . '[' . $column . ']', '', $size, $div);
    }
}

function _makeCheckboxInput($column, $name, $request = 'students')
{
    global $value, $field;

    if ($_REQUEST['student_id'] == 'new' && $field['DEFAULT_SELECTION']) {
        $value[$column] = $field['DEFAULT_SELECTION'];
        $div = false;
    } else
        $div = true;

    return CheckboxInput($value[$column], $request . '[' . $column . ']', $name, '', ($_REQUEST['student_id'] == 'new'), '<i class="icon-checkbox-checked"></i>', '<i class="icon-checkbox-unchecked"></i>');
}

function _makeTextareaInput($column, $name, $request = 'students')
{
    global $value, $field;

    if ($_REQUEST['student_id'] == 'new' && $field['DEFAULT_SELECTION']) {
        $value[$column] = $field['DEFAULT_SELECTION'];
        $div = false;
    } else
        $div = true;

    return TextAreaInput($value[$column], $request . '[' . $column . ']', $name, '', $div);
}

// function _makeMultipleInput($column, $name, $request = 'students') {
//     global $value, $field, $_openSIS;

//     if ((AllowEdit() || $_openSIS['allow_edit']) && !$_REQUEST['_openSIS_PDF']) {
//         $field['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $field['SELECT_OPTIONS']));
//         $select_options = explode("\r", $field['SELECT_OPTIONS']);
//         if (count($select_options)) {
//             foreach ($select_options as $option)
//                 $options[$option] = $option;
//         }

//         if ($value[$column] != '')
//             $m_input.="<DIV id='div" . $request . "[" . $column . "]'><div readonly='readonly' class='form-control' onclick='javascript:addHTML(\"";
//         //$m_input.='<TABLE border=0 cellpadding=3>';
//         if (count($options) > 12) {
//             //$m_input.='<TR><TD colspan=2>';
//             $m_input.='<span color=' . Preferences('TITLES') . '>' . $name . '</span>';
//             /* if ($value[$column] != '')
//               $m_input.='<TABLE width=100% height=7 style=\"border:1;border-style: solid solid none solid;\"><TR><TD></TD></TR></TABLE>';
//               else
//               $m_input.='<TABLE width=100% height=7 style="border:1;border-style: solid solid none solid;"><TR><TD></TD></TR></TABLE>';

//               $m_input.='</TD></TR>'; */
//         }
//         //$m_input.='<TR>';
//         $i = 0;
//         foreach ($options as $option) {
//             //if ($i % 2 == 0)
//             //$m_input.='</TR><TR>';
//             if ($value[$column] != '') {

//                 $m_input.='<INPUT TYPE=hidden name=' . $request . '[' . $column . '][] value=\"\"><label class=checkbox-inline><INPUT type=checkbox class=styled name=' . $request . '[' . $column . '][] value=\"' . str_replace('"', '&quot;', $option) . '\"' . (strpos($value[$column], '||' . $option . '||') !== false ? ' CHECKED' : '') . '>' . $option . '</label>';
//             } else {
//                 $m_input.='<label class=checkbox-inline><INPUT type=checkbox class=styled name=' . $request . '[' . $column . '][] value="' . str_replace('"', '&quot;', $option) . '"' . (strpos($value[$column], '||' . $option . '||') !== false ? ' CHECKED' : '') . '>' . $option . '</label>';
//             }
//             $i++;
//         }
//         /* $m_input.='</TR><TR><TD colspan=2>';
//           if ($value[$column] != '')
//           $m_input.='<TABLE width=100% height=7 style=\"border:1;border-style: none solid solid solid;\"><TR><TD></TD></TR></TABLE>';
//           else
//           $m_input.='<TABLE width=100% height=7 style="border:1;border-style: none solid solid solid;"><TR><TD></TD></TR></TABLE>';

//           $m_input.='</TD></TR></TABLE>'; */
//         if ($value[$column] != '')
//             $m_input.="\",\"div" . $request . "[" . $column . "]" . "\",true);' >" . (($value[$column] != '') ? str_replace('"', '&rdquo;', str_replace('||', ', ', substr($value[$column], 2, -2))) : '-') . "</div></DIV>";
//     } else
//         $m_input.=(($value[$column] != '') ? str_replace('"', '&rdquo;', str_replace('||', ', ', substr($value[$column], 2, -2))) : '-<BR>');

//     $m_input.='<p class=help-block>' . $name . '</p>';
//     return $m_input;
// }

function _makeMultipleInput($column, $name, $request = 'students')
{
    global $value, $field, $_openSIS;

    if ((AllowEdit() || $_openSIS['allow_edit']) && !$_REQUEST['_openSIS_PDF']) {
        $field['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $field['SELECT_OPTIONS']));
        $select_options = explode("\r", $field['SELECT_OPTIONS']);
        if (count($select_options)) {
            foreach ($select_options as $option)
                $options[$option] = $option;
        }

        if (count($options) > 12) {
            $m_input .= '<span color=' . Preferences('TITLES') . '>' . $name . '</span>';
        }

        $i = 0;
        foreach ($options as $option) {
            if ($value[$column] != '') {
                $m_input .= '<INPUT TYPE=hidden name=' . $request . '[' . $column . '][] value="">';
                $m_input .= '<label class=checkbox-inline><INPUT type=checkbox class=styled name=' . $request . '[' . $column . '][] value="' . str_replace('"', '&quot;', $option) . '"' . (strpos($value[$column], '||' . $option . '||') !== false ? ' CHECKED' : '') . '>' . $option . '</label>';
            } else {
                $m_input .= '<label class=checkbox-inline><INPUT type=checkbox class=styled name=' . $request . '[' . $column . '][] value="' . str_replace('"', '&quot;', $option) . '"' . (strpos($value[$column], '||' . $option . '||') !== false ? ' CHECKED' : '') . '>' . $option . '</label>';
            }
            $i++;
        }
    } else
        $m_input .= (($value[$column] != '') ? str_replace('"', '&rdquo;', str_replace('||', ', ', substr($value[$column], 2, -2))) : '-<BR>');

    $m_input .= '<p class=help-block>' . $name . '</p>';
    return $m_input;
}

// MEDICAL ----
function _makeType($value, $column)
{
    global $THIS_RET;

    if (!$THIS_RET['ID'])
        $THIS_RET['ID'] = 'new';

    if ($value != '---')
        if ($value != '')
            return SelectInput($value, 'values[student_immunization][' . $THIS_RET['ID'] . '][TYPE]', '', array('Immunization' => 'Immunization', 'Physical' => 'Physical', '---' => '---', $value => $value));
        else
            return SelectInput($value, 'values[student_immunization][' . $THIS_RET['ID'] . '][TYPE]', '', array('Immunization' => 'Immunization', 'Physical' => 'Physical', '---' => '---'));
    else
        return TextInput($value, 'values[student_immunization][' . $THIS_RET['ID'] . '][TYPE]');
}

function _makeDate($value, $column = 'MEDICAL_DATE', $counter = 0, $array = '')
{ //student medical tab
    if ($array == '') {
        global $THIS_RET, $table;


        if (!$THIS_RET['ID'])
            $THIS_RET['ID'] = 'new';
    } else {
        $THIS_RET['ID'] = $array['ID'];
        $table = $array['TABLE'];
    }

    return DateInputAY($value, 'values[' . $table . '][' . $THIS_RET['ID'] . '][' . $column . ']', $counter);
    //return DateInputAY($value!='' ? date("d-M-Y", strtotime($value)) : $value , 'values[' . $table . '][' . $THIS_RET['ID'] . '][' . $column . ']', $counter);   
}

function _makeEnrollmentDates($column, $counter = 0, $ret_array = '')
{ //student enrollment tab
    if (count($ret_array) > 0 && $ret_array != '') {

        $value = $ret_array[$column];
        if ($column == 'START_DATE' && $value == '') {
            $value = DBGet(DBQuery('SELECT min(SCHOOL_DATE) AS START_DATE FROM attendance_calendar WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
            $value = $value[1]['START_DATE'];
            if (!$value || DBDate('mysql') > $value)
                $value = DBDate('mysql');
            $value = $value;
        }
        $id = $ret_array['ID'];
    } else {
        if ($column == 'START_DATE') {

            $value = DBGet(DBQuery('SELECT min(SCHOOL_DATE) AS START_DATE,SYEAR FROM attendance_calendar WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));

            $val_syear = $value[1]['SYEAR'];
            $value = $value[1]['START_DATE'];
            if (!$value || DBDate('mysql') > $value)
                $value = DBDate('mysql');
            $value = $value;
        }
        $id = 'new';
    }

    if ($ret_array['SYEAR'] == UserSyear() || $val_syear == UserSyear()) {

        return DateInputAY($value != '' ? $value : '', 'values[student_enrollment][' . $id . '][' . $column . ']', $counter);
    } else {
        // return date('M/d/Y', strtotime($value));
        return ProperDateAY($value);
    }
}

//-------------------- Edit Start --------------------------//

function _makeDate_mod($value, $column = 'MEDICAL_DATE')
{ //not used anywhere
    global $THIS_RET, $table;

    if (!$THIS_RET['ID'])
        $THIS_RET['ID'] = 'new';
    return DateInput($value, 'values[' . $table . '][' . $THIS_RET['ID'] . '][' . $column . ']');
}

function _makeDateInput_mod($column, $name, $request = 'students')
{ //for custom_field_students
    global $value, $field;

    if ($_REQUEST['student_id'] == 'new' && $field['DEFAULT_SELECTION']) {
        $value[$column] = $field['DEFAULT_SELECTION'];
        $div = false;
        $req = $field['REQUIRED'] == 'Y' ? array('<FONT color=red>', '</FONT>') : array('', '');
    } else {
        $div = true;
        $req = $field['REQUIRED'] == 'Y' && $value[$column] == '' ? array('<FONT color=red>', '</FONT>') : array('', '');

        //-------- if start -------------//
        if (strlen($value[$column]) == 11) {
            $mother_date = $value[$column];
            $date = explode("-", $mother_date);

            $day = $date[0];
            $month = $date[1];
            $year = $date[2];

            if ($month == 'JAN')
                $month = '01';
            elseif ($month == 'FEB')
                $month = '02';
            elseif ($month == 'MAR')
                $month = '03';
            elseif ($month == 'APR')
                $month = '04';
            elseif ($month == 'MAY')
                $month = '05';
            elseif ($month == 'JUN')
                $month = '06';
            elseif ($month == 'JUL')
                $month = '07';
            elseif ($month == 'AUG')
                $month = '08';
            elseif ($month == 'SEP')
                $month = '09';
            elseif ($month == 'OCT')
                $month = '10';
            elseif ($month == 'NOV')
                $month = '11';
            elseif ($month == 'DEC')
                $month = '12';

            $final_date = $year . "-" . $month . "-" . $day;
            $value[$column] = $final_date;
        }
        //--------- if end --------------//
    }

    return DateInput($value[$column], $request . '[' . $column . ']', $req[0] . $name . $req[1], $div);
}

//--------------------- Edit End ---------------------------//

function _makeCommentsn($value, $column)
{
    global $THIS_RET, $table;

    if (!$THIS_RET['ID'])
        $THIS_RET['ID'] = 'new';

    return TextAreaInput($value, 'values[' . $table . '][' . $THIS_RET['ID'] . '][' . $column . ']', '', 'rows=8 cols=50');
}

function _makeLongComments($value, $column)
{
    global $THIS_RET, $table;

    if (!$THIS_RET['ID'])
        $THIS_RET['ID'] = 'new';
    if ($THIS_RET['ID'] == 'new' || $value == '') {
        $field = "<textarea rows='1' cols='3' style='visibility:hidden;' id=" . 'values[' . $table . '][' . $THIS_RET['ID'] . '][' . $column . ']' . " name=" . 'values[' . $table . '][' . $THIS_RET['ID'] . '][' . $column . ']' . ">$value</textarea>";
        $field .= '<div class="text-center"><a href="javascript:void(0);" id="textarea" data-popup="popover" data-placement="left" title="' . _addComment . '"  data-html="true" data-content="<div class=\'form-group\'><div class=\'col-md-12\'><textarea class=\'form-control\' id=' . "values[" . $table . "][" . $THIS_RET["ID"] . "][" . $column . "]" . '  name=' . "values[" . $table . "][" . $THIS_RET['ID'] . "][" . $column . "]" . ' >' . $value . '</textarea></div></div><div class=\'text-center\'><input type=\'submit\' class=\'btn btn-primary\' value=\'Save\'></div>"><i class="icon-comments"></i><br/><div readonly="readonly">' . _enterComment . '</div></a></div>';
        return $field;
    } else {
        $field = "<textarea rows='1' cols='3' style='visibility:hidden;' id=" . 'values[' . $table . '][' . $THIS_RET['ID'] . '][' . $column . ']' . " name=" . 'values[' . $table . '][' . $THIS_RET['ID'] . '][' . $column . ']' . ">$value</textarea>";
        $field .= '<div class="text-center"><a href="javascript:void(0);" id="textarea" data-popup="popover" data-placement="left" title="' . _addComment . '"  data-html="true" data-content="<div class=\'form-group\'><div class=\'col-md-12\'><textarea class=\'form-control\' id=' . "values[" . $table . "][" . $THIS_RET["ID"] . "][" . $column . "]" . '  name=' . "values[" . $table . "][" . $THIS_RET['ID'] . "][" . $column . "]" . ' >' . $value . '</textarea></div></div><div class=\'text-center\'><input type=\'submit\' class=\'btn btn-primary\' value=\'Save\'></div>"><i class="icon-comments"></i><br/><div readonly="readonly">' . _enterComment . '</div></a></div>';
        return $field;
    }
}

function _makeComments($value, $column)
{
    global $THIS_RET, $table;

    if (!$THIS_RET['ID'])
        $THIS_RET['ID'] = 'new';

    return TextInput($value, 'values[' . $table . '][' . $THIS_RET['ID'] . '][' . $column . ']');
}

function _makeAlertComments($value, $column)
{
    global $THIS_RET, $table;

    if (!$THIS_RET['ID'])
        $THIS_RET['ID'] = 'new';

    return TextInput($value, 'values[' . $table . '][' . $THIS_RET['ID'] . '][' . $column . ']', '', 'size=40');
}

// ENROLLMENT
function _makeStartInputDate($value, $column)
{ //student enrollment info tab
    global $THIS_RET;

    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    elseif ($_REQUEST['student_id'] == 'new') {
        $id = 'new';
        $default = DBGet(DBQuery('SELECT min(SCHOOL_DATE) AS START_DATE FROM attendance_calendar WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
        $default = $default[1]['START_DATE'];
        if (!$default || DBDate() > $default)
            $default = DBDate();
        $value = $default;
    } else {
        $add = '<TD>' . button('add') . '</TD>';
        $id = 'new';
    }

    //	

    if ($_REQUEST['student_id'] == 'new')
        $div = false;
    else
        $div = true;

    $maxyear = DBGet(DBQuery('SELECT max(syear) AS SYEAR FROM student_enrollment WHERE STUDENT_ID=\'' . UserStudentID() . '\''));
    //          
    if ($THIS_RET['SYEAR'] == $maxyear[1]['SYEAR']) {
        if ($_REQUEST['student_id'] != 'new')
            return '<TABLE class=LO_field><TR>' . $add . '<TD>' . DateInputAY($value, 'values[student_enrollment][' . $id . '][' . $column . ']', $_REQUEST['student_id']) . '</TD></TR></TABLE>';
        else
            return '<TABLE class=LO_field><TR>' . $add . '<TD>' . DateInputAY($value, 'values[student_enrollment][' . $id . '][' . $column . ']', 0) . '</TD></TR></TABLE>';
    } else {
        if ($_REQUEST['student_id'] != 'new')
            return '<TABLE class=LO_field><TR>' . $add . '<TD>' . ($value == '' ? DateInputAY($value, 'values[student_enrollment][' . $id . '][' . $column . ']', $_REQUEST['student_id']) : date('M/d/Y', strtotime($value))) . '</TD></TR></TABLE>';
        else
            return '<TABLE class=LO_field><TR>' . $add . '<TD>' . ($value == '' ? DateInputAY($value, 'values[student_enrollment][' . $id . '][' . $column . ']', 0) : date('M/d/Y', strtotime($value))) . '</TD></TR></TABLE>';
    }
}

function _makeStartInputDateenrl($value, $column)
{ //student enrollment tab
    global $THIS_RET;

    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    elseif ($_REQUEST['student_id'] == 'new') {
        $id = 'new';
        $default = DBGet(DBQuery('SELECT min(SCHOOL_DATE) AS START_DATE FROM attendance_calendar WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\''));
        $default = $default[1]['START_DATE'];
        if (!$default || DBDate() > $default)
            $default = DBDate();
        $value = $default;
    } else {
        $add = '<TD>' . button('add') . '</TD>';
        $id = 'new';
    }

    if ($_REQUEST['student_id'] == 'new') {
        $div = false;
        $counter = 0;
    } else {
        $div = true;
        $counter = $id;
    }

    if ($THIS_RET['SYEAR'] == UserSyear()) {
        return '<TABLE class=LO_field><TR>' . $add . '<TD>' . DateInput($value, 'values[student_enrollment][' . $id . '][' . $column . ']', '', $div, false) . '</TD></TR></TABLE>';
    } else
        return date('F/d/Y', strtotime($value));
}

function _makeStartInputCode($value, $column)
{
    global $THIS_RET;
    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    $add_codes = array();
    $options_RET = DBGet(DBQuery('SELECT ID,TITLE AS TITLE FROM student_enrollment_codes WHERE SYEAR=\'' . ($THIS_RET['SYEAR'] != '' ? $THIS_RET['SYEAR'] : UserSyear()) . '\' AND (TYPE=\'Add\' OR TYPE=\'Roll\' OR TYPE=\'TrnE\')'));

    if ($options_RET) {
        foreach ($options_RET as $option)
            $add_codes[$option['ID']] = $option['TITLE'];
    }
    $maxyear = DBGet(DBQuery('SELECT max(syear) AS SYEAR FROM student_enrollment WHERE STUDENT_ID=\'' . UserStudentID() . '\''));
    if ($THIS_RET['SYEAR'] == $maxyear[1]['SYEAR'])
        return '<TABLE class=LO_field><TR><TD>' . SelectInput($THIS_RET['ENROLLMENT_CODE'], 'values[student_enrollment][' . $id . '][ENROLLMENT_CODE]', '', $add_codes, 'N/A', 'style="max-width:150;"') . '</TD></TR></TABLE>';
    else {
        $CODE_RET = DBGet(DBQuery("SELECT ID,TITLE AS TITLE FROM student_enrollment_codes WHERE ID='" . $THIS_RET['ENROLLMENT_CODE'] . "' "));
        return '<TABLE class=LO_field><TR><TD>' . $CODE_RET[1]['TITLE'] . '</TD></TR></TABLE>';
    }
}

function _makeEndInputDate($value, $column)
{ //not used
    global $THIS_RET;
    $drop_codes = array();

    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    // student_enrollment select create here
    $maxyear = DBGet(DBQuery('SELECT max(syear) AS SYEAR FROM student_enrollment WHERE STUDENT_ID=\'' . UserStudentID() . '\''));
    if ($THIS_RET['SYEAR'] == $maxyear[1]['SYEAR'])
        return '<TABLE class=LO_field><TR><TD>' . DateInput($value, 'values[student_enrollment][' . $id . '][' . $column . ']') . '</TD></TR></TABLE>';
    else {
        if ($value)
            return '<TABLE class=LO_field><TR><TD>' . date('M/d/Y', strtotime($value)) . '</TD></TR></TABLE>';
        else
            return '<TABLE class=LO_field><TR><TD>Na/Na/Na</TD></TR></TABLE>';
    }
}

function _makeEndInputCode($value, $column)
{
    global $THIS_RET;
    $drop_codes = array();

    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';


    $options_RET = DBGet(DBQuery('SELECT ID,TITLE AS TITLE,TYPE FROM student_enrollment_codes WHERE SYEAR=\'' . ($THIS_RET['SYEAR'] != '' ? $THIS_RET['SYEAR'] : UserSyear()) . '\'  AND (TYPE=\'Drop\' OR TYPE=\'Roll\' OR TYPE=\'TrnD\')'));

    if ($options_RET) {
        foreach ($options_RET as $option)
            $drop_codes[$option['ID']] = $option['TITLE'];
    }

    $type_RET = DBGet(DBQuery('SELECT ID, TYPE FROM student_enrollment_codes WHERE SYEAR=\'' . ($THIS_RET['SYEAR'] != '' ? $THIS_RET['SYEAR'] : UserSyear()) . '\' AND TYPE=\'TrnD\''));
    if (count($type_RET) > 0)
        $type_id = $type_RET[1]['ID'];
    // student_enrollment select create here
    $maxyear = DBGet(DBQuery('SELECT max(syear) AS SYEAR FROM student_enrollment WHERE STUDENT_ID=\'' . UserStudentID() . '\''));
    if ($THIS_RET['SYEAR'] == $maxyear[1]['SYEAR'])
        return '<TABLE class=LO_field><TR><TD>' . SelectInput_for_EndInput($THIS_RET['DROP_CODE'], 'values[student_enrollment][' . $id . '][DROP_CODE]', '', $drop_codes, $type_id, 'N/A', 'style="max-width:150;"') . '</TD></TR></TABLE>';
    else {
        $CODE_RET = DBGet(DBQuery('SELECT ID,TITLE AS TITLE FROM student_enrollment_codes WHERE ID=\'' . $THIS_RET['DROP_CODE'] . '\' '));
        return '<TABLE class=LO_field><TR><TD>' . $CODE_RET[1]['TITLE'] . '</TD></TR></TABLE>';
    }
}

function _makeSchoolInput($value, $column)
{
    global $THIS_RET, $schools;
    $schools = array();
    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    if (!$schools)
        $schools = DBGet(DBQuery('SELECT ID,TITLE FROM schools'), array(), array('ID'));

    foreach ($schools as $sid => $school)
        $options[$sid] = $school[1]['TITLE'];
    // mab - allow school to be editted if illegal value
    if ($THIS_RET['SCHOOL_ID']) {
        $name = DBGet(DBQuery('SELECT TITLE FROM schools WHERE ID=\'' . $THIS_RET['SCHOOL_ID'] . '\''));
        return $name[1]['TITLE'] . '<input type=hidden name=enrollment_id value="' . $id . '" />';
    } elseif ($_REQUEST['student_id'] != 'new') {
        if ($id != 'new') {
            if ($schools[$value]) {
                $name = DBGet(DBQuery('SELECT TITLE FROM schools WHERE ID=\'' . UserSchool() . '\''));
                return $name[1]['TITLE'] . '<input type=hidden name=enrollment_id value="' . $id . '" />';
            } else
                return SelectInput($value, 'values[student_enrollment][' . $id . '][SCHOOL_ID]', '', $options);
        } else
            return SelectInput(UserSchool(), 'values[student_enrollment][' . $id . '][SCHOOL_ID]', '', $options, false, '', false);
    } else
        return $schools[UserSchool()][1]['TITLE'] . '<input type=hidden name=enrollment_id value="' . $id . '" />';
}

function _makeStartInputCodeenrl($value, $column)
{
    global $THIS_RET;
    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    $add_codes = array();
    $options_RET = DBGet(DBQuery('SELECT ID,TITLE AS TITLE FROM student_enrollment_codes WHERE SYEAR=\'' . ($THIS_RET['SYEAR'] != '' ? $THIS_RET['SYEAR'] : UserSyear()) . '\' AND (TYPE=\'Add\' OR TYPE=\'Roll\' OR TYPE=\'TrnE\')'));

    if ($options_RET) {
        foreach ($options_RET as $option)
            $add_codes[$option['ID']] = $option['TITLE'];
    }
    $option_output = DBGet(DBQuery('SELECT ID,TITLE AS TITLE FROM student_enrollment_codes WHERE SYEAR=\'' . ($THIS_RET['SYEAR'] != '' ? $THIS_RET['SYEAR'] : UserSyear()) . '\' AND (TYPE=\'Add\' OR TYPE=\'Roll\' OR TYPE=\'TrnE\') AND ID=\'' . $value . '\''));
    if ($THIS_RET['SYEAR'] == UserSyear())
        return '<TABLE class=LO_field><TR><TD>' . SelectInput($THIS_RET['ENROLLMENT_CODE'], 'values[student_enrollment][' . $id . '][ENROLLMENT_CODE]', '', $add_codes, 'N/A', 'style="max-width:150;"') . '</TD></TR></TABLE>';
    else {
        if ($value == '')
            return "N/A";
        else
            return $option_output[1]['TITLE'];
    }
}

function _makeEndInputDateenrl($value, $column)
{
    global $THIS_RET;
    $drop_codes = array();

    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    if (!$THIS_RET['ID'])
        $id = 'new';
    if ($value['DROP_CODE'] == '') {

        if ($id == 'new')
            $date_field = '<TABLE class=LO_field><TR><TD>' . DateInputAY($value, 'values[student_enrollment][' . $id . '][' . $column . ']', 0) . '</TD></TR></TABLE>';
        else
            $date_field = '<TABLE class=LO_field><TR><TD>' . DateInputAY($value, 'values[student_enrollment][' . $id . '][' . $column . ']', $id) . '</TD></TR></TABLE>';
    }
    if ($value['DROP_CODE'] != '') {

        if ($id == 'new')
            $date_field = '<TABLE class=LO_field><TR><TD>' . DateInputAY($value, 'values[student_enrollment][' . $id . '][' . $column . ']', 0) . '</TD></TR></TABLE>';
        else
            $date_field = '<TABLE class=LO_field><TR><TD>' . DateInputAY($value, 'values[student_enrollment][' . $id . '][' . $column . ']', $id) . '</TD></TR></TABLE>';
    }

    if ($THIS_RET['SYEAR'] == UserSyear())
        return $date_field;
    else {
        if ($value == '') {
            return "N/A";
        } else {
            return $r_date = date('F/d/Y', strtotime($value));
        }
    }
}

function _makeEndInputCodeenrl($value, $column)
{
    global $THIS_RET;
    $drop_codes = array();

    if ($THIS_RET['ID'])
        $id = $THIS_RET['ID'];
    else
        $id = 'new';

    $options_RET = DBGet(DBQuery('SELECT ID,TITLE AS TITLE,TYPE FROM student_enrollment_codes WHERE SYEAR=\'' . ($THIS_RET['SYEAR'] != '' ? $THIS_RET['SYEAR'] : UserSyear()) . '\'  AND (TYPE=\'Drop\' OR TYPE=\'Roll\' OR TYPE=\'TrnD\')'));

    if ($options_RET) {
        foreach ($options_RET as $option)
            $drop_codes[$option['ID']] = $option['TITLE'];
    }

    $type_RET = DBGet(DBQuery('SELECT ID, TYPE FROM student_enrollment_codes WHERE SYEAR=\'' . ($THIS_RET['SYEAR'] != '' ? $THIS_RET['SYEAR'] : UserSyear()) . '\' AND TYPE=\'TrnD\''));
    if (count($type_RET) > 0)
        $type_id = $type_RET[1]['ID'];
    $option_output = DBGet(DBQuery('SELECT ID,TITLE AS TITLE FROM student_enrollment_codes WHERE SYEAR=\'' . ($THIS_RET['SYEAR'] != '' ? $THIS_RET['SYEAR'] : UserSyear()) . '\' AND (TYPE=\'Drop\' OR TYPE=\'Roll\' OR TYPE=\'TrnE\') AND ID=\'' . $value . '\''));
    // student_enrollment select create here
    if ($THIS_RET['SYEAR'] == UserSyear())
        return '<TABLE class=LO_field><TR><TD>' . SelectInput_for_EndInput($THIS_RET['DROP_CODE'], 'values[student_enrollment][' . $id . '][DROP_CODE]', '', $drop_codes, $type_id, 'N/A', 'style="max-width:150;"') . '</TD></TR></TABLE>';
    else {
        if ($value == '') {
            return "N/A";
        } else {
            return $option_output[1]['TITLE'];
        }
    }
}
