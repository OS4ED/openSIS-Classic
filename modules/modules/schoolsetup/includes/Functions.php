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

function _makeTextInputSchl($column, $name, $size, $request = 'values') {
    global $value, $field;

    if ($_REQUEST['id'] == 'new' && $field['DEFAULT_SELECTION']) {
        $value[$column] = $field['DEFAULT_SELECTION'];
        $div = false;
        $req = $field['REQUIRED'] == 'Y' ? array('<FONT color=red>', '</FONT>') : array('', '');
    } else {
        $div = true;
        $req = $field['REQUIRED'] == 'Y' && $value[$column] == '' ? array('<FONT color=red>', '</FONT>') : array('', '');
    }

    if ($field['TYPE'] == 'numeric')
        $value[$column] = str_replace('.00', '', $value[$column]);

    return TextInput($value[$column], $request . '[' . $column . ']', (($name!='')?$req[0] . $name . $req[1]:'') , $size, $div);
}

function _makeDateInputSchl($column, $name, $request = 'values') {
    global $value, $field;

    if ($_REQUEST['student_id'] == 'new' && $field['DEFAULT_SELECTION']) {
        $value[$column] = $field['DEFAULT_SELECTION'];
        $div = false;
        $req = $field['REQUIRED'] == 'Y' ? array('<FONT color=red>', '</FONT>') : array('', '');
    } else {
        $div = true;
        $req = $field['REQUIRED'] == 'Y' && $value[$column] == '' ? array('<FONT color=red>', '</FONT>') : array('', '');
    }


    return DateInputAY($value[$column], $request . '[' . $column . ']', 1);
}

function _makeSelectInputSchl($column, $name, $request = 'values') {
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
        foreach ($select_options as $option)
            if ($field['TYPE'] == 'codeds') {
                $option = explode('|', $option);
                if ($option[0] != '' && $option[1] != '')
                    $options[$option[0]] = $option[1];
            } else
                $options[$option] = $option;
    }

    $extra = 'class=form-control';

    if (trim($name) != '')
        return SelectInput($value[$column], $request . '[' . $column . ']', $req[0] . $name . $req[1], $options, 'N/A', $extra, $div);
    else
        return SelectInput($value[$column], $request . '[' . $column . ']', '', $options, 'N/A', $extra, $div);
}

function _makeAutoSelectInputSchl($column, $name, $request = 'values') {
    global $value, $field;

    if ($_REQUEST['student_id'] == 'new' && $field['DEFAULT_SELECTION']) {
        $value[$column] = $field['DEFAULT_SELECTION'];
        $div = false;
        $req = $field['REQUIRED'] == 'Y' ? array('<FONT color=red>', '</FONT>') : array('', '');
    } else {
        $div = true;
        $req = $field['REQUIRED'] == 'Y' && ($value[$column] == '' || $value[$column] == '---') ? array('<FONT color=red>', '</FONT>') : array('', '');
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
    // make sure the current value is in the list
    if ($value[$column] != '' && !$options[$value[$column]])
        $options[$value[$column]] = array($value[$column], '<FONT color=' . ($field['TYPE'] == 'autos' ? 'blue' : 'green') . '>' . $value[$column] . '</FONT>');

    if ($value[$column] != '---' && count($options) > 1) {

        if (isset($num_of_cus_field)) {
            $generated = true;
        }
        
        // $extra = 'style="max-width:250;"';

        if (trim($name) != '')
            return SelectInput($value[$column], $request . '[' . $column . ']', $req[0] . $name . $req[1], $options, 'N/A', $extra, $div);
        else
            return SelectInput($value[$column], $request . '[' . $column . ']', '', $options, 'N/A', $extra, $div);
    } else
        return TextInput($value[$column] == '---' ? array('---', '<FONT color=red>---</FONT>') : '' . $value[$column], $request . '[' . $column . ']', $req[0] . $name . $req[1], $size, $div);
}

function _makeCheckboxInputSchl($column, $name, $request = 'values') {
    global $value, $field;

    if ($_REQUEST['student_id'] == 'new' && $field['DEFAULT_SELECTION']) {
        $value[$column] = $field['DEFAULT_SELECTION'];
        $div = false;
    } else
        $div = true;

    return CheckboxInput($value[$column], $request . '[' . $column . ']', $name, '', ($_REQUEST['student_id'] == 'new'));
}

function _makeTextareaInputSchl($column, $name, $request = 'values') {
    global $value, $field;

    if ($_REQUEST['student_id'] == 'new' && $field['DEFAULT_SELECTION']) {
        $value[$column] = $field['DEFAULT_SELECTION'];
        $div = false;
    } else
        $div = true;

    return TextAreaInput($value[$column], $request . '[' . $column . ']', $name, '', $div);
}

// function _makeMultipleInputSchl($column, $name, $request = 'values') {
//     global $value, $field, $_openSIS;

//     if ((AllowEdit() || $_openSIS['allow_edit']) && !$_REQUEST['_openSIS_PDF']) {
//         $field['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $field['SELECT_OPTIONS']));
//         $select_options = explode("\r", $field['SELECT_OPTIONS']);
//         if (count($select_options)) {
//             foreach ($select_options as $option)
//                 $options[$option] = $option;
//         }

//         if ($value[$column] != '')
//             $m_input .= "<DIV id='div" . $request . "[" . $column . "]'><div onclick='javascript:addHTML(\"";
//         $m_input .= '<TABLE border=0 cellpadding=3>';
//         if (count($options) > 12) {
//             $m_input .= '<TR><TD colspan=2>';
//             $m_input .= '<small><FONT color=' . Preferences('TITLES') . '>' . $name . '</FONT></small>';
//             if ($value[$column] != '')
//                 $m_input .= '<TABLE width=100% height=7 style=\"border:1;border-style: solid solid none solid;\"><TR><TD></TD></TR></TABLE>';
//             else
//                 $m_input .= '<TABLE width=100% height=7 style="border:1;border-style: solid solid none solid;"><TR><TD></TD></TR></TABLE>';

//             $m_input .= '</TD></TR>';
//         }
//         $m_input .= '<TR>';
//         $i = 0;
//         foreach ($options as $option) {
//             if ($i % 2 == 0)
//                 $m_input .= '</TR><TR>';
//             if ($value[$column] != '') {

//                 $m_input .= '<TD><INPUT TYPE=hidden name=' . $request . '[' . $column . '][] value=\"\"><INPUT type=checkbox name=' . $request . '[' . $column . '][] value=\"' . str_replace('"', '&quot;', $option) . '\"' . (strpos($value[$column], '||' . $option . '||') !== false ? ' CHECKED' : '') . '><small>' . $option . '</small></TD>';
//             } else {
//                 $m_input .= '<TD><INPUT type=checkbox name=' . $request . '[' . $column . '][] value="' . str_replace('"', '&quot;', $option) . '"' . (strpos($value[$column], '||' . $option . '||') !== false ? ' CHECKED' : '') . '><small>' . $option . '</small></TD>';
//             }
//             $i++;
//         }
//         $m_input .= '</TR><TR><TD colspan=2>';
//         if ($value[$column] != '')
//             $m_input .= '<TABLE width=100% height=7 style=\"border:1;border-style: none solid solid solid;\"><TR><TD></TD></TR></TABLE>';
//         else
//             $m_input .= '<TABLE width=100% height=7 style="border:1;border-style: none solid solid solid;"><TR><TD></TD></TR></TABLE>';

//         $m_input .= '</TD></TR></TABLE>';
//         if ($value[$column] != '')
//             $m_input .= "\",\"div" . $request . "[" . $column . "]" . "\",true);' >" . (($value[$column] != '') ? str_replace('"', '&rdquo;', str_replace('||', ', ', substr($value[$column], 2, -2))) : '-') . "</div></DIV>";
//     } else
//         $m_input .= (($value[$column] != '') ? str_replace('"', '&rdquo;', str_replace('||', ', ', substr($value[$column], 2, -2))) : '-<BR>');

//     $m_input .= '<small><FONT color=' . Preferences('TITLES') . '>' . $name . '</FONT></small>';
//     return $m_input;
// }

function _makeMultipleInputSchl($column, $name, $request = 'values') {
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

    $m_input.='<p class=help-block>' . $name . '</p>';
    return $m_input;
}

// MEDICAL ----
function _makeTypeSchl($value, $column) {
    global $THIS_RET;

    if (!$THIS_RET['ID'])
        $THIS_RET['ID'] = 'new';

    if ($value != '---')
        if ($value != '')
            return SelectInput($value, 'values[student_medical][' . $THIS_RET['ID'] . '][TYPE]', '', array('Immunization' => 'Immunization', 'Physical' => 'Physical', '---' => '---', $value => $value));
        else
            return SelectInput($value, 'values[student_medical][' . $THIS_RET['ID'] . '][TYPE]', '', array('Immunization' => 'Immunization', 'Physical' => 'Physical', '---' => '---'));
    else
        return TextInput($value, 'values[student_medical][' . $THIS_RET['ID'] . '][TYPE]');
}

function _makeDateSchl($value, $column = 'MEDICAL_DATE') {
    global $THIS_RET, $table;

    if (!$THIS_RET['ID'])
        $THIS_RET['ID'] = 'new';


    return DateInputAY($value, 'values[' . $table . '][' . $THIS_RET['ID'] . '][' . $column . ']', 1);
}

//-------------------- Edit Start --------------------------//

function _makeDate_modSchl($value, $column = 'MEDICAL_DATE') {
    global $THIS_RET, $table;

    if (!$THIS_RET['ID'])
        $THIS_RET['ID'] = 'new';

    return DateInputAY($value, 'values[' . $table . '][' . $THIS_RET['ID'] . '][' . $column . ']', 1);
}

function _makeDateInput_modSchl($column, $name, $request = 'values', $id='') {
    global $value, $field;
    $col_no = explode('_', $column);
    $counter = $col_no[1];
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


    return DateInputAY($value[$column], $request . '[' . $column . ']', $counter);
}

?>