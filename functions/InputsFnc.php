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

function DateInput($value, $name, $title = '', $div = true, $allow_na = true) {
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        if ($value == '' || $div == false)
            return PrepareDate($value, "_$name", $allow_na) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
        else
            return "<DIV id='div$name'><div onclick='javascript:addHTML(\"" . str_replace('"', '\"', PrepareDate($value, "_$name", $allow_na, array('Y' => 1, 'M' => 1, 'D' => 1))) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . "\",\"div$name\",true)'>" . (($value != '') ? ProperDate($value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . '</div></DIV>';
    }
    else {
        return (($value != '') ? ProperDate($value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
    }
}

function DateInput2($value, $name, $selectid, $title = '', $div = true, $allow_na = true) {
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        if ($value == '' || $div == false)
            return PrepareDate2($value, "_$name", $selectid, $allow_na) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
        else
            return "<DIV id='div$name'><div onclick='javascript:addHTML(\"" . str_replace('"', '\"', PrepareDate2($value, "_$name", $selectid, $allow_na, array('Y' => 1, 'M' => 1, 'D' => 1))) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . "\",\"div$name\",true)'>" . (($value != '') ? ProperDate($value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . '</div></DIV>';
    }
    else {
        return (($value != '') ? ProperDate($value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
    }
}

function SearchDateInput($day, $month, $year, $allow_day, $allow_month, $allow_year) {
    $dt = '';

    $dt.= '<div class="input-group datepicker-group" id="original_date_' . $day . '" value="" style="">';
    $dt.= '<span class="input-group-addon"><i class="icon-calendar22"></i></span>';
    $dt.= '<input id="date_' . $day . '" placeholder="'._selectDate.'" value="" class="form-control daterange-single" type="text">';
    $dt.= '</div>';

    $dt.= '<input value="" id="monthSelect_date_' . $day . '" name="' . $month . '" type="hidden">';
    $dt.= '<input value="" id="daySelect_date_' . $day . '" name="' . $day . '" type="hidden">';
    $dt.= '<input value="" id="yearSelect_date_' . $day . '" name="' . $year . '" type="hidden">';

    /* if ($allow_day == 'Y') {
      $dt.='<div class="col-xs-3">';
      $dt.='<select class="form-control" name="' . $day . '" id="' . $day . '">';
      $dt.='<option value="">Day</option>';
      for ($i = 1; $i <= 31; $i++) {
      if ($i < 10)
      $i = '0' . $i;

      $dt.='<option value="' . $i . '">' . $i . '</option>';
      }
      $dt.='</select>';
      $dt.='</div>';
      }


      if ($allow_month == 'Y') {
      $dt.='<div class="col-xs-3">';
      $dt.='<select class="form-control" name="' . $month . '" id="' . $month . '">';
      $dt.='<option value="">Month</option><option value="01">January</option><option value="02">February</option><option value="03">March</option><option value="04">April</option><option value="05">May</option><option value="06">June</option><option value="07">July</option><option value="08">August</option><option value="09">September</option><option value="10">October</option><option value="11">November</option><option value="12">December</option>';
      $dt.='</select>';
      $dt.='</div>';
      }


      if ($allow_year == 'Y') {
      $dt.='<div class="col-xs-3">';
      $dt.='<select class="form-control" name="' . $year . '" id="' . $year . '">';
      $dt.='<option value="">Year</option>';
      for ($i = 1930; $i <= 2030; $i++) {
      $dt.='<option value="' . $i . '">' . $i . '</option>';
      }
      $dt.='</select>';
      $dt.='</div>';
      } */

    return $dt;
}




function SearchDateInputDob($day, $month, $year, $allow_day, $allow_month, $allow_year) {
    $dt = '';

    $dt.= '<div class="input-group datepicker-group-month-date" id="original_date_' . $day . '" value="" style="">';
    $dt.= '<span class="input-group-addon"><i class="icon-calendar22"></i></span>';
    $dt.= '<input id="date_' . $day . '" placeholder="'._selectDate.'" value="" class="form-control daterange-single" type="text">';
    $dt.= '</div>';

    $dt.= '<input value="" id="monthSelect_date_' . $day . '" name="' . $month . '" type="hidden">';
    $dt.= '<input value="" id="daySelect_date_' . $day . '" name="' . $day . '" type="hidden">';
   

    /* if ($allow_day == 'Y') {
      $dt.='<div class="col-xs-3">';
      $dt.='<select class="form-control" name="' . $day . '" id="' . $day . '">';
      $dt.='<option value="">Day</option>';
      for ($i = 1; $i <= 31; $i++) {
      if ($i < 10)
      $i = '0' . $i;

      $dt.='<option value="' . $i . '">' . $i . '</option>';
      }
      $dt.='</select>';
      $dt.='</div>';
      }


      if ($allow_month == 'Y') {
      $dt.='<div class="col-xs-3">';
      $dt.='<select class="form-control" name="' . $month . '" id="' . $month . '">';
      $dt.='<option value="">Month</option><option value="01">January</option><option value="02">February</option><option value="03">March</option><option value="04">April</option><option value="05">May</option><option value="06">June</option><option value="07">July</option><option value="08">August</option><option value="09">September</option><option value="10">October</option><option value="11">November</option><option value="12">December</option>';
      $dt.='</select>';
      $dt.='</div>';
      }


      if ($allow_year == 'Y') {
      $dt.='<div class="col-xs-3">';
      $dt.='<select class="form-control" name="' . $year . '" id="' . $year . '">';
      $dt.='<option value="">Year</option>';
      for ($i = 1930; $i <= 2030; $i++) {
      $dt.='<option value="' . $i . '">' . $i . '</option>';
      }
      $dt.='</select>';
      $dt.='</div>';
      } */

    return $dt;
}


function DateInput_for_EndInput($value, $name, $title = '', $div = true, $allow_na = true) {
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        if ($value == '' || $div == false)
            return PrepareDate_for_EndInput($value, "_$name", $allow_na) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
        else
            return "<DIV id='div$name'><div onclick='javascript:addHTML(\"" . str_replace('"', '\"', PrepareDate_for_EndInput($value, "_$name", true, array('Y' => 1, 'M' => 1, 'D' => 1))) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . "\",\"div$name\",true)'>" . (($value != '') ? ProperDate($value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . '</div></DIV>';
    } else
        return (($value != '') ? ProperDate($value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

function DateInput_for_EndInputModal($value, $name, $title = '', $div = true, $allow_na = true) {
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    if (!$_REQUEST['_openSIS_PDF']) {
        if ($value == '' || $div == false)
            return PrepareDate_for_EndInput($value, "_$name", $allow_na) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
        else
            return "<DIV id='div$name'><div onclick='javascript:addHTML(\"" . str_replace('"', '\"', PrepareDate_for_EndInput($value, "_$name", true, array('Y' => 1, 'M' => 1, 'D' => 1))) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . "\",\"div$name\",true)'>" . (($value != '') ? ProperDate($value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . '</div></DIV>';
    } else
        return (($value != '') ? ProperDate($value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

function TextInput($value, $name, $title = '', $options = '', $div = true, $divOptions = '') {
    $original_title = $title;
    $title = str_replace('*', '', $original_title);
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    // mab - support array style $option values
    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));
        $value1 = is_array($value) ? $value[1] : $value;
        $value = is_array($value) ? $value[0] : $value;
        $dgei = 'document.getElementById(\"input$name\").focus();';

        if (strpos($options, 'size') === false && $value != '')
            $options .= ' size=' . strlen($value);
        elseif (strpos($options, 'size') === false)
            $options .= ' size=10';

        if (strstr($value, '\\') != '')
            $div = false;
        if ((trim(str_replace("","",$value)) == '' || trim($div) == false))
            return (($title != '') ? '<label for="' . $name . '" class="control-label text-right col-lg-4">' . str_replace('*', '<span class="text-danger">*</span>', $original_title) . '</label><div class="col-lg-8">' : '') . "<INPUT class=\"form-control\" type=text id=$name name=$name " . (($value || $value === '0') ? "value=\"$value\"" : '') . " $options>" . (($title != '') ? '</div>' : '');
        else {

            return (($title != '') ? '<label for="' . $name . '" class="control-label text-right col-lg-4">' . str_replace('*', '<span class="text-danger">*</span>', $original_title) . '</label><div class="col-lg-8">' : '') . "<DIV id='div$name'><div " . $divOptions . " onclick='javascript:addHTML(\"<INPUT type=text class=form-control id=input$name name=$name " . (($value || $value === '0') ? "value=\\\"" . str_replace('"', '&rdquo;', $value) . "\\\"" : '') . " $options>\",\"div$name\",true); document.getElementById(\"input$name\").focus();' readonly=\"readonly\" class=\"form-control\">" . $value . '</div></DIV>' . (($title != '') ? '</div>' : '');
        }
    } else {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));
        return ($title != '' ? '<label class="control-label text-right col-lg-4">' . $title . '</label><div class="col-lg-8">' : '') . '<div class="form-control" disabled=disabled>' . (((is_array($value) ? $value[1] : $value) != '') ? (is_array($value) ? $value[1] : $value) : '-') . '</div>' . ($title != '' ? '</div>' : '');
    }
}

function TextInputPortal($value, $name, $title = '', $options = '', $div = true, $divOptions = '') {
    $original_title = $title;
    $title = str_replace('*', '', $original_title);
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    // mab - support array style $option values
    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));
        $value1 = is_array($value) ? $value[1] : $value;
        $value = is_array($value) ? $value[0] : $value;

        if (strpos($options, 'size') === false && $value != '')
            $options .= ' size=' . strlen($value);
        elseif (strpos($options, 'size') === false)
            $options .= ' size=10';

        if (strstr($value, '\\') != '')
            $div = false;
        if ((trim($value) == '' || $div == false))
//            return (($title != '') ? '<label for="' . $name . '" class="control-label text-right col-lg-4">' . $title . '</label><div class="col-lg-8">' : '') . "<INPUT class=\"form-control\"  type=text id=$name name=$name " . (($value || $value === '0') ? "value=\"$value\"" : '') . " $options>" . (($title != '') ? '</div>' : '');
            return (($title != '') ? '<label for="' . $name . '" class="control-label text-right col-lg-4">' . str_replace('*', '<span class="text-danger">*</span>', $original_title) . '</label><div class="col-lg-8">' : '') . "<INPUT class=\"form-control\" type=text id=$name name=$name " . (($value || $value === '0') ? "value=\"$value\"" : '') . " $options>" . (($title != '') ? '</div>' : '');
        else {

            return (($title != '') ? '<label for="' . $name . '" class="control-label text-right col-lg-4">' . str_replace('*', '<span class="text-danger">*</span>', $original_title) . '</label><div class="col-lg-8">' : '') . "<DIV id='div$name'><div " . $divOptions . " onclick='javascript:addHTML(\"<INPUT type=text class=form-control id=input$name name=$name " . (($value || $value === '0') ? "value=\\\"" . str_replace('"', '&rdquo;', $value) . "\\\"" : '') . " $options>\",\"div$name\",true); document.getElementById(\"input$name\").focus();' readonly=\"readonly\" class=\"form-control\">" . str_replace("\n", '\n', str_replace('"', '\"', addcslashes(str_replace("\r", '', (string)$value), "\0..\37'\\"))) . '</div></DIV>' . (($title != '') ? '</div>' : '');
        }
    } else {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));
        return ($title != '' ? '<label class="control-label text-right col-lg-4">' . $title . '</label><div class="col-lg-8">' : '') . '<div class="form-control" disabled=disabled>' . (((is_array($value) ? $value[1] : $value) != '') ? (is_array($value) ? $value[1] : $value) : '-') . '</div>' . ($title != '' ? '</div>' : '');
    }
}

function TextInput_time($value, $name, $title = '', $options = '', $div = true, $divOptions = '') {
    $original_title = $title;
    $title = str_replace('*', '', $original_title);
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    // mab - support array style $option values
    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));
        $value1 = is_array($value) ? $value[1] : $value;
        $value = is_array($value) ? $value[0] : $value;

        if (strpos($options, 'size') === false && $value != '')
            $options .= ' size=' . strlen($value);
        elseif (strpos($options, 'size') === false)
            $options .= ' size=7';

        if (strstr($value, '\\') != '')
            $div = false;
        if ((trim($value) == '' || $div == false))
//            return (($title != '') ? '<label for="' . $name . '" class="control-label text-right col-lg-4">' . $title . '</label><div class="col-lg-8">' : '') . "<INPUT class=\"form-control\"  type=text id=$name name=$name " . (($value || $value === '0') ? "value=\"$value\"" : '') . " $options>" . (($title != '') ? '</div>' : '');
            return (($title != '') ? '<label for="' . $name . '" class="control-label text-right col-lg-4">' . str_replace('*', '<span class="text-danger">*</span>', $original_title) . '</label><div class="col-lg-8">' : '') . "<div class=\"input-group clockpicker\"><INPUT class=\"form-control\" type=text id=$name name=$name " . (($value || $value === '0') ? "value=\"$value\"" : '') . " $options></div>" . (($title != '') ? '</div>' : '');
        else {
            $tempId = rand(0000, 9999);
            return (($title != '') ? '<label for="' . $name . '" class="control-label text-right col-lg-4">' . str_replace('*', '<span class="text-danger">*</span>', $original_title) . '</label><div class="col-lg-8">' : '') . "<DIV id='div" . $tempId . "' onclick=\"$('#div" . $tempId . "').hide(); $('#div" . $tempId . "picker').show();\" readonly=\"readonly\" class=\"form-control\"><div " . $divOptions . " document.getElementById(\"input$name\").focus();'>" . $value . "</div></DIV><div class=\"input-group clockpicker\" id=\"div" . $tempId . "picker\" style=\"display:none;\"><INPUT type=text class=form-control id=\"input$name\" name=\"$name\" " . (($value || $value === '0') ? 'value="' . str_replace('"', '&rdquo;', $value) . '"' : '') . $options . "></div>" . (($title != '') ? "</div>" : "");
        }
    } else {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));
        return ($title != '' ? '<label class="control-label text-right col-lg-4">' . $title . '</label><div class="col-lg-8">' : '') . '<div class="form-control" disabled=disabled>' . (((is_array($value) ? $value[1] : $value) != '') ? (is_array($value) ? $value[1] : $value) : '-') . '</div>' . ($title != '' ? '</div>' : '');
    }
}

function TextInputModHidden($value, $name, $title = '', $options = '', $div = true) {

    // mab - support array style $option values
    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));
        $value1 = is_array($value) ? $value[1] : $value;
        $value = is_array($value) ? $value[0] : $value;

        if (strpos($options, 'size') === false && $value != '')
            $options .= ' size=' . strlen($value);
        elseif (strpos($options, 'size') === false)
            $options .= ' size=10';

        if (strstr($value, '\\') != '')
            $div = false;
        if ((trim($value) == '' || $div == false))
            return "<INPUT class=\"form-control\" type=text id=$name name=$name " . (($value || $value === '0') ? "value=\"$value\"" : '') . " $options>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
        else {

            return "<DIV id='div$name'><div onclick='javascript:addHTML(\"<INPUT type=text class=form-control id=input$name name=$name " . (($value || $value === '0') ? "value=\\\"" . str_replace('"', '&rdquo;', $value) . "\\\"" : '') . " $options>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . "\",\"div$name\",true); document.getElementById(\"input$name\").focus();'>" . (($value != '') ? str_replace('"', '&rdquo;', $value1) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . '</small>' : '') . '</div></DIV>';
        }
    } else {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));
        return (((is_array($value) ? $value[1] : $value) != '') ? (is_array($value) ? $value[1] : $value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
    }
}

function TextInputCusId($value, $name, $title = '', $options = '', $div = true, $ex_id = '') {
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    // mab - support array style $option values
    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));
        $value1 = is_array($value) ? $value[1] : $value;
        $value = is_array($value) ? $value[0] : $value;

        if (strpos($options, 'size') === false && $value != '')
            $options .= ' size=' . strlen($value);
        elseif (strpos($options, 'size') === false)
            $options .= ' size=10';

        if ((trim($value) == '' || $div == false))
            return "<label class=\"control-label text-right col-lg-4\">" . ($title != '' ? $title : '') . "</label><div class=\"col-lg-8\"><INPUT class=control-label type=text name=$name " . (($value || $value === '0') ? "value=\"$value\"" : '') . " $options></div>";
        else {
            if ($ex_id == '')
                return "<label class=\"control-label text-right col-lg-4\">$title</label><div class=\"col-lg-8\"><div id='div$name'><div onclick='javascript:addHTML(\"<INPUT class=form-control type=text id=input$name name=$name " . (($value || $value === '0') ? "value=\\\"" . str_replace('"', '&rdquo;', $value) . "\\\"" : '') . " $options>" . "\",\"div$name\",true); document.getElementById(\"input$name\").focus();'><input type=\"text\" readonly=\"readonly\" class=\"form-control\" value=\"" . (($value != '') ? str_replace('"', '&rdquo;', $value1) : '-') . ($title != '' ? $title : '') . '" /></div></div></div>';
            else
                return "<label class=\"control-label text-right col-lg-4\">$title</label><div class=\"col-lg-8\"><div id='div$name'><div onclick='javascript:addHTML(\"<INPUT class=form-control partha type=text id=$ex_id name=$name " . (($value || $value === '0') ? "value=\\\"" . str_replace('"', '&rdquo;', $value) . "\\\"" : '') . " $options>" . "\",\"div$name\",true); document.getElementById(\"$ex_id\").focus();'><input type=\"text\" readonly=\"readonly\" class=\"form-control\" value=\"" . (($value != '') ? str_replace('"', '&rdquo;', $value1) : '-') . ($title != '' ? $title : '') . '" /></div></div></div>';
        }
    } else
        return (((is_array($value) ? $value[1] : $value) != '') ? (is_array($value) ? $value[1] : $value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

function TextInput_mail($value, $name, $title = '', $options = '', $div = true) {
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    // mab - support array style $option values
    if (!$_REQUEST['_openSIS_PDF']) {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));
        $value1 = is_array($value) ? $value[1] : $value;
        $value = is_array($value) ? $value[0] : $value;

        if (strpos($options, 'size') === false && $value != '')
            $options .= ' size=' . strlen($value);
        elseif (strpos($options, 'size') === false)
            $options .= ' size=10';


        return "<INPUT class=\"form-control\" id=$name type=text name=$name " . (($value || $value === '0') ? "value=\"$value\"" : '') . " $options>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
    } else
        return (((is_array($value) ? $value[1] : $value) != '') ? (is_array($value) ? $value[1] : $value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

function TextInput_mod_a($value, $name, $title = '', $options = '', $div = true) {
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    // mab - support array style $option values
    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));
        $value1 = is_array($value) ? $value[1] : $value;
        $value = is_array($value) ? $value[0] : $value;

        if (strpos($options, 'size') === false && $value != '')
            $options .= ' size=' . strlen($value);
        elseif (strpos($options, 'size') === false)
            $options .= ' size=10';

        if ((trim($value) == '' || $div == false))
            return (($title != '') ? '<label for="' . $name . '" class="control-label col-lg-4">' . $title . '</label><div class="col-lg-8">' : '') . "<INPUT class='form-control' placeholder='" . $title . "' type=\"text\" name=$name " . (($value || $value === '0') ? "value=\"$value\"" : '') . " id=$name " . (($value || $value === '0') ? "value=\"$value\"" : '') . " $options>" . (($title != '') ? '</div>' : '');
        else
            return "<DIV id='div$name'><div onclick='javascript:addHTML(\"<INPUT type=text placeholder=\\\"" . $title . "\\\" class=form-control id=input$name name=$name " . (($value || $value === '0') ? "value=\\\"" . str_replace('"', '&rdquo;', $value) . "\\\"" : '') . " $options>" . ($title != '' ? $title : '') . "\",\"div$name\",true); document.getElementById(\"input$name\").focus();'>" . (($value != '') ? str_replace('"', '&rdquo;', $value1) : '-') . ($title != '' ? $title : '') . "</div></DIV>";
    } else
        return (((is_array($value) ? $value[1] : $value) != '') ? (is_array($value) ? $value[1] : $value) : '-') . ($title != '' ? '<label class="control-label">' . $title . '</label>' : '');
}

function TextAreaInput($value, $name, $title = '', $options = '', $div = true) {
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));

        if (strpos($options, 'cols') === false)
            $options .= ' cols=30';
        if (strpos($options, 'rows') === false)
            $options .= ' rows=4';
        $rows = substr($options, strpos($options, 'rows') + 5, 2) * 1;
        $cols = substr($options, strpos($options, 'cols') + 5, 2) * 1;

        if ($value == '' || $div == false)
            return (($title != '') ? '<label class="control-label col-lg-4 text-right" for="' . $name . '">' . $title . '</label><div class="col-lg-8">' : '') . "<TEXTAREA class=form-control id=$name name=$name $options>$value</TEXTAREA>" . (($title != '') ? "</div>" : "");
        else
            return ($title != '' ? '<label class="control-label col-lg-4 text-right" for="' . $name . '">' . $title . '</label><div class="col-lg-8">' : '') . "<DIV id='div$name'><div class='form-control' readonly='readonly' onclick='javascript:addHTML(\"<TEXTAREA class=form-control id=textarea$name name=$name $options>" . str_replace("\r\n", '\u000D\u000A', str_replace("'", "&#39;", $value)) . "</TEXTAREA>" . "\",\"div$name\",true); document.getElementById(\"textarea$name\").value=unescape(document.getElementById(\"textarea$name\").value);'>" . ((substr_count($value, "\r\n") > $rows) ? '<DIV>' . nl2br($value) . '</DIV>' : '<DIV>' . nl2br($value) . '</DIV>') . '</div></DIV>' . (($title != '') ? "</div>" : "");
//            return ($title != '' ? '<label class="control-label col-lg-4" for="' . $name . '">' . $title . '</label><div class="col-lg-8">' : '') . "<DIV id='div$name'><div onclick='javascript:addHTML(\"<TEXTAREA class=form-control placeholder=$title id=textarea$name name=$name $options>" . ereg_replace("[\n\r]", '\u000D\u000A', str_replace("\r\n", '\u000D\u000A', str_replace("'", "&#39;", $value))) . "</TEXTAREA>" . ($title != '' ? "<BR><small>" . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . "</small>" : '') . "\",\"div$name\",true); document.getElementById(\"textarea$name\").value=unescape(document.getElementById(\"textarea$name\").value);'>" . ((substr_count($value, "\r\n") > $rows) ? '<DIV>' . nl2br($value) . '</DIV>' : '<DIV>' . nl2br($value) . '</DIV>') . '</div></DIV>' . (($title != '') ? "</div>" : "");
    } else
        return (($value != '') ? nl2br($value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

function TextAreaInputPortal($value, $name, $title = '', $options = '', $div = true) {
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));

        if (strpos($options, 'cols') === false)
            $options .= ' cols=30';
        if (strpos($options, 'rows') === false)
            $options .= ' rows=4';
        $rows = substr($options, strpos($options, 'rows') + 5, 2) * 1;
        $cols = substr($options, strpos($options, 'cols') + 5, 2) * 1;

        if ($value == '' || $div == false)
            return (($title != '') ? '<label class="control-label col-lg-4 text-right" for="' . $name . '">' . $title . '</label><div class="col-lg-8">' : '') . "<TEXTAREA class=form-control id=$name name=$name $options>$value</TEXTAREA>" . (($title != '') ? "</div>" : "");
        else
            return ($title != '' ? '<label class="control-label col-lg-4 text-right" for="' . $name . '">' . $title . '</label><div class="col-lg-8">' : '') . "<DIV id='div$name'><div class='form-control' readonly='readonly' onclick='javascript:addHTML(\"<TEXTAREA class=form-control id=textarea$name name=$name $options>" . str_replace("\n", '\n', str_replace('"', '\"', addcslashes(str_replace("\r", '', (string)$value), "\0..\37'\\"))) . "</TEXTAREA>" . "\",\"div$name\",true); document.getElementById(\"textarea$name\").value=unescape(document.getElementById(\"textarea$name\").value);'>" . ((substr_count($value, "\r\n") > $rows) ? '<DIV>' . nl2br($value) . '</DIV>' : '<DIV>' . nl2br($value) . '</DIV>') . '</div></DIV>' . (($title != '') ? "</div>" : "");
//            return ($title != '' ? '<label class="control-label col-lg-4" for="' . $name . '">' . $title . '</label><div class="col-lg-8">' : '') . "<DIV id='div$name'><div onclick='javascript:addHTML(\"<TEXTAREA class=form-control placeholder=$title id=textarea$name name=$name $options>" . ereg_replace("[\n\r]", '\u000D\u000A', str_replace("\r\n", '\u000D\u000A', str_replace("'", "&#39;", $value))) . "</TEXTAREA>" . ($title != '' ? "<BR><small>" . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . "</small>" : '') . "\",\"div$name\",true); document.getElementById(\"textarea$name\").value=unescape(document.getElementById(\"textarea$name\").value);'>" . ((substr_count($value, "\r\n") > $rows) ? '<DIV>' . nl2br($value) . '</DIV>' : '<DIV>' . nl2br($value) . '</DIV>') . '</div></DIV>' . (($title != '') ? "</div>" : "");
    } else
        return (($value != '') ? nl2br($value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

function TextAreaInputInputFinalGrade($value, $name, $title = '', $options = '', $div = true) {
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));
        if (strpos($options, 'cols') === false)
            $options .= ' cols=30';
        if (strpos($options, 'rows') === false)
            $options .= ' rows=4';
        $rows = substr($options, strpos($options, 'rows') + 5, 2) * 1;
        $cols = substr($options, strpos($options, 'cols') + 5, 2) * 1;

        //htmlspecialchars: Convert special characters to HTML entities
        $value=htmlspecialchars($value);
        
        if ($value == '' || $div == false)
            return "<TEXTAREA class='form-control' name=$name $options>".htmlspecialchars_decode($value)."</TEXTAREA>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
        else
            return "<DIV id='div$name'><div onclick='javascript:addHTML(\"<TEXTAREA class=form-control id=textarea$name name=$name $options>" . preg_replace("[\n\r]", '\u000D\u000A', str_replace("\r\n", '\u000D\u000A', str_replace("'", "&#39;", $value))) . "</TEXTAREA>" . ($title != '' ? "<BR><small>" . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . "</small>" : '') . "\",\"div$name\",true); document.getElementById(\"textarea$name\").value=unescape(document.getElementById(\"textarea$name\").value);'><TABLE class=LO_field ><TR><TD>" . ((substr_count($value, "\r\n") > $rows) ? '<DIV style="overflow:auto; height:' . (15 * $rows) . 'px; width:' . ($cols * 10) . '; padding-right: 16px;">' . nl2br(htmlspecialchars_decode($value)) . '</DIV>' : '<DIV style="overflow:auto; width:300; padding-right: 16px;">' . nl2br(htmlspecialchars_decode($value)) . '</DIV>') . '</TD></TR></TABLE>' . ($title != '' ? '<BR><small>' . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . '</small>' : '') . '</div></DIV>';
    } else
        return ((($value) != '') ? nl2br(($value)) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

function CheckboxInput($value, $name, $title = '', $checked = '', $new = false, $yes = _yes, $no = _no, $div = true, $extra = '') {

    if ($checked) {
        if (strpos($name, 'STANDARD_GRADE_SCALE'))
            return '<i class="icon-checkbox-checked"></i>';
    }
    // $checked has been deprecated -- it remains only as a placeholder
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    if ($div == false || $new == true) {
        if ($value && $value != 'N')
            $checked = 'CHECKED';
        else
            $checked = '';
    }

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        if ($new && $div == false)
            return "<label class=checkbox-inline><INPUT type=checkbox name=$name  value=Y $checked $extra>" . ($title != '' ? ' ' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') : '') . '</label>';
        elseif ($new && $div == true)
            return "<div class=\"checkbox\"><label><INPUT type=checkbox name=$name  value=Y $checked $extra>" . ($title != '' ? $title : '') . '</label></div>';
        else
            return "<DIV id='div$name'><div " . (($title == '') ? 'class=form-control readonly=readonly' : '') . " onclick='javascript:addHTML(\"<INPUT type=hidden name=$name  value=\\\"\\\"><div class=checkbox><label><INPUT type=checkbox name=$name " . (($value) ? 'checked' : '') . " value=Y " . str_replace('"', '\"', $extra) . "> " . $title . "</label></div>\",\"div$name\",true)'> " . (($yes == 'Yes' || $no == 'No') ? ($value ? '<i class="icon-checkbox-checked"></i>' : '<i class="icon-checkbox-unchecked"></i>') : '') . ' &nbsp; &nbsp' . ($value ? $yes : $no) . ' ' . $title . "</div></DIV>";
    } else
        return ($title != '' ? '<label class="control-label">' . $title . '</label>' : '') . '<p class="form-control" readonly="readonly">' . ($value ? $yes : $no) . '</p>';
}

function CheckboxInput_comments($value, $name, $title = '', $checked = '', $new = false, $yes = _yes, $no = _no, $div = true, $extra = '') {

    if ($checked) {
        if (strpos($name, 'STANDARD_GRADE_SCALE'))
            return '<i class="icon-checkbox-checked"></i>';
    }
    // $checked has been deprecated -- it remains only as a placeholder
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    if ($div == false || $new == true) {
        if ($value && $value != 'N')
            $checked = 'CHECKED';
        else
            $checked = '';
    }

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        if ($new && $div == false)
            return "<label class=checkbox-inline><INPUT type=checkbox name=$name  value=Y $checked $extra>" . ($title != '' ? ' ' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') : '') . '</label>';
        elseif ($new && $div == true)
            return "<div class=\"checkbox\"><label><INPUT type=checkbox name=$name  value=Y $checked $extra>" . ($title != '' ? $title : '') . '</label></div>';
        else {
            if ($value == '' || $value == 'N')
                return "<DIV id='div$name'><div class=checkbox><label><INPUT type=checkbox name=$name " . (($value) ? 'checked' : '') . " value=Y " . str_replace('"', '\"', $extra) . "> " . $title . "</label></div></DIV>";
            else
                return "<DIV id='div$name'><div " . (($title == '') ? 'class=form-control readonly=readonly' : '') . " onclick='javascript:addHTML(\"<INPUT type=hidden name=$name  value=\\\"\\\"><div class=checkbox><label><INPUT type=checkbox name=$name " . (($value) ? 'checked' : '') . " value=Y " . str_replace('"', '\"', $extra) . "> " . $title . "</label></div>\",\"div$name\",true)'> " . (($yes == 'Yes' || $no == 'No') ? ($value ? '<i class="icon-checkbox-checked"></i>' : '<i class="icon-checkbox-unchecked"></i>') : '') . ' &nbsp; &nbsp' . ($value ? $yes : $no) . ' ' . $title . "</div></DIV>";
        }
    } else
        return ($title != '' ? '<label class="control-label">' . $title . '</label>' : '') . '<p class="form-control" readonly="readonly">' . ($value ? $yes : $no) . '</p>';
}

function CheckboxInputSwitch($value, $name, $title = '', $checked = '', $new = false, $yes = _yes, $no = _no, $extra = '', $switchery_color = 'switch-default', $size = 'sm') {

    // $checked has been deprecated -- it remains only as a placeholder
    if (Preferences('HIDDEN') != 'Y') {
        $div = false;
    }

    if ($switchery_color == '') {
        $switchery_color = 'switch-primary';
    }

    if ($div == false || $new == true) {
        if ($value && $value != 'N') {
            $checked = 'CHECKED';
        } else {
            $checked = '';
        }
    }

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {

        if ($new || $div == false) {
            return '<INPUT type=hidden name="' . $name . '"  value=""><div class="checkbox checkbox-switch ' . $switchery_color . ' switch-' . $size . '">'
                    . '<label>'
                    . '<input type="checkbox" value=Y name="' . $name . '" ' . $checked . ' ' . $extra . '><span></span>' . $title . '</label>'
                    . '</div>';
        } else {
            return '<INPUT type=hidden name="' . $name . '"  value="">'
                    . '<div class="checkbox checkbox-switch ' . $switchery_color . ' switch-' . $size . '">'
                    . '<label>'
                    . '<INPUT type=checkbox name="' . $name . '" ' . (($value) ? 'checked="checked"' : '') . ' value=Y ><span></span>' . $title . '</label>'
                    . '</div>';
        }
        /* if ($new || $div == false)
          return "<div class=\"checkbox-inline\"><INPUT type=checkbox name=$name  value=Y $checked $extra>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . '</div>';
          else
          return "<DIV id='div$name'><div onclick='javascript:addHTML(\"<INPUT type=hidden name=$name  value=\\\"\\\"><div class=checkbox-inline><INPUT type=checkbox name=$name " . (($value) ? 'checked' : '') . " value=Y " . str_replace('"', '\"', $extra) . "></div>" . ($title != '' ? '<BR><small>' . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . '</small>' : '') . "\",\"div$name\",true)'>" . ($value ? $yes : $no) . ($title != '' ? "<BR><small>" . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . "</small>" : '') . "</div></DIV>"; */
    } else
    //return ($value ? $yes : $no) . ($title != '' ? '<BR><small>' . $title . '</small>' : '');
        return '<div class="checkbox checkbox-switch ' . $switchery_color . ' switch-' . $size . '"><label><INPUT type=checkbox disabled="disabled" ' . ($value ? 'checked=checked' : '') . '><span></span>' . $title . '</label></div>';
}

function CheckboxInput_grade($value, $name, $title = '', $checked = '', $new = false, $yes = _yes, $no = _no, $div = true, $extra = '') {

    if ($checked) {
        if (strpos($name, 'STANDARD_GRADE_SCALE'))
            return '<i class="icon-checkbox-checked"></i>';
    }
    // $checked has been deprecated -- it remains only as a placeholder
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    if ($div == false || $new == true) {
        if ($value && $value != 'N')
            $checked = 'CHECKED';
        else
            $checked = '';
    }

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        if ($new || $div == false)
            return "<div class=\"checkbox-inline\"><INPUT type=checkbox name=$name id=$name value=Y $checked $extra>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . '</div>';
        else {
            if ($value == '' || $value == 'N')
                return "<DIV id='div$name'><div class=checkbox-inline><INPUT type=checkbox   id=$name  name=$name " . (($value) ? 'checked' : '') . " value=Y " . str_replace('"', '\"', $extra) . "></div></DIV>";
            else
                return "<DIV id='div$name'><div " . (($title == '') ? 'class=form-control readonly=readonly' : '') . " onclick='javascript:addHTML(\"<INPUT type=hidden name=$name  value=\\\"\\\"><div class=checkbox-inline><INPUT type=checkbox   id=$name  name=$name " . (($value) ? 'checked' : '') . " value=Y " . str_replace('"', '\"', $extra) . "></div>" . ($title != '' ? '<BR><small>' . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . '</small>' : '') . "\",\"div$name\",true)'>" . ($value ? $yes : $no) . ($title != '' ? "<BR><small>" . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . "</small>" : '') . "</div></DIV>";
        }
    } else
        return '<div class="form-control" disabled=disabled>' . ($value ? $yes : $no) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . '</div>';
}

function CheckboxInput_exam($value, $name, $title = '', $checked = '', $new = false, $yes = _yes, $no = _no, $div = true, $extra = '') {

    if ($checked) {
        if (strpos($name, 'STANDARD_GRADE_SCALE'))
            return '<i class="icon-checkbox-checked"></i>';
    }
    // $checked has been deprecated -- it remains only as a placeholder
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    if ($div == false || $new == true) {
        if ($value && $value != 'N')
            $checked = 'CHECKED';
        else
            $checked = '';
    }

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        if ($new || $div == false)
            return "<div class=\"checkbox-inline\"><INPUT type=checkbox name=$name id=$name value=Y $checked  $extra>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . '</div>';
        else {
            if ($value == '' || $value == 'N')
                return "<DIV id='div$name'><div class=checkbox-inline><INPUT type=checkbox id=$name  name=$name " . (($value) ? 'checked' : '') . " value=Y " . str_replace('"', '\"', $extra) . "></div></DIV>";
            else
                return "<DIV id='div$name'><div class=form-control readonly=readonly onclick='javascript:addHTML(\"<INPUT type=hidden name=$name  value=\\\"\\\"><div class=checkbox-inline><INPUT type=checkbox id=$name  name=$name " . (($value) ? 'checked' : '') . " value=Y " . str_replace('"', '\"', $extra) . "></div>" . ($title != '' ? '<BR><small>' . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . '</small>' : '') . "\",\"div$name\",true)'>" . ($value ? $yes : $no) . ($title != '' ? "<BR><small>" . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . "</small>" : '') . "</div></DIV>";
        }
    } else
        return '<div class="form-control" disabled=disabled>' . ($value ? $yes : $no) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . '</div>';
}

function CheckboxInput_var_sch($value, $name, $title = '', $checked = '', $new = false, $yes = _yes, $no = _no, $div = true, $extra = '') {

    if ($checked) {
        if (strpos($name, 'STANDARD_GRADE_SCALE'))
            return '<i class="icon-checkbox-checked"></i>';
    }
    // $checked has been deprecated -- it remains only as a placeholder
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    if ($div == false || $new == true) {
        if ($value && $value != 'N')
            $checked = 'CHECKED';
        else
            $checked = '';
    }

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        if ($new || $div == false)
            return "<div class=\"checkbox\"><label><INPUT type=checkbox name=$name  value=Y $checked $extra> " . ($title != '' ? $title : '') . '</label></div>';
        else
            return "<DIV id='div$name'><div onclick='javascript:addHTML(\"<INPUT type=hidden name=$name  value=\\\"\\\"><div class=checkbox><label><INPUT type=checkbox  id=$name name=$name " . (($value) ? 'checked' : '') . " value=Y " . str_replace('"', '\"', $extra) . "><label></div>" . ($title != '' ? str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) : '') . "\",\"div$name\",true)'>" . ($value ? $yes : $no) . ($title != '' ? str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) : '') . "</div></DIV>";
    } else
        return ($value ? $yes : $no) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

function CheckboxInputMod($value, $name, $title = '', $checked = '', $new = false, $yes = _yes, $no = _no, $div = true, $extra = '') {

    // $checked has been deprecated -- it remains only as a placeholder
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    if ($div == false || $new == true) {
        if ($value && $value != 'N')
            $checked = 'CHECKED';
        else
            $checked = '';
    }

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        if ($new || $div == false)
            return "<label class=\"form-control\"><INPUT type=checkbox  onclick=set_check_value(this,\"" . $name . "\"); id=$name  name=$name value=Y $checked $extra>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . "</label>";
        else
            return "<label id='div$name' class=\"form-control\"><div onclick='javascript:addHTML(\"<INPUT type=checkbox  onclick=set_check_value(this,\\\"$name\\\"); id=$name  name=$name " . (($value) ? 'checked' : '') . " value=Y " . str_replace('"', '\"', $extra) . ">" . ($title != '' ? '<BR><small>' . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . '</small>' : '') . "\",\"div$name\",true)'>" . ($value ? $yes : $no) . ($title != '' ? "<BR><small>" . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . "</small>" : '') . "</div></label>";
    } else
        return '<div class="form-control" disabled="disabled">' . ($value ? $yes : $no) . '</div>' . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

//for calendar date
function CheckboxInput_Calendar($value, $name, $title = '', $checked = '', $new = false, $yes = _yes, $no = _no, $div = true, $extra = '') {
    // $checked has been deprecated -- it remains only as a placeholder

    if ($new == true) {
        if ($value && $value != 'N')
            $checked = 'CHECKED';
        else
            $checked = '';
    }

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        if ($new || $div == false)
            return "<INPUT type=checkbox name=$name value=Y $checked $extra>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
        else
            return "<DIV id='div$name'><div onclick='javascript:addHTML(\"<INPUT type=hidden name=$name value=\\\"\\\"><INPUT type=checkbox name=$name " . (($value) ? 'checked' : '') . " value=Y " . str_replace('"', '\"', $extra) . ">" . ($title != '' ? '<BR><small>' . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . '</small>' : '') . "\",\"div$name\",true)'>" . ($value ? $yes : $no) . ($title != '' ? "<BR><small>" . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . "</small>" : '') . "</div></DIV>";
    } else
        return ($value ? $yes : $no) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

function CheckboxInputWithID($value, $name, $id, $title = '', $checked = '', $new = false, $yes = _yes, $no = _no, $div = true, $extra = '') {
    // $checked has been deprecated -- it remains only as a placeholder
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    if ($div == false || $new == true) {
        if ($value && $value != 'N')
            $checked = 'CHECKED';
        else
            $checked = '';
    }

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        if ($new || $div == false)
            return "<INPUT type=checkbox name=$name id=$id value=Y $checked $extra>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
        else
            return "<DIV id='div$name'><div onclick='javascript:addHTML(\"<INPUT type=hidden name=$name value=\\\"\\\"><INPUT type=checkbox name=$name " . (($value) ? 'checked' : '') . " value=Y " . str_replace('"', '\"', $extra) . ">" . ($title != '' ? '<BR><small>' . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . '</small>' : '') . "\",\"div$name\",true)'>" . ($value ? $yes : $no) . ($title != '' ? "<BR><small>" . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . "</small>" : '') . "</div></DIV>";
    } else
        return ($value ? $yes : $no) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

function SelectInput($value, $name, $title, $options, $allow_na = 'N/A', $extra = '', $div = true) {
    if(empty($title)) $title = '';
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    // mab - support array style $option values
    // mab - append current val to select list if not in list

    if ($value != '' && !$options[$value])
        $options[$value] = array($value, '<FONT color=red>' . $value . '</FONT>');

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
//        $return = (($title != '') ? '<label class="control-label text-right col-lg-4">' . $title . '</label><div class="col-lg-8">' : '');
        $return = (($title != '') ? '<label class="control-label text-right col-lg-4">' . $title . '</label><div class="col-lg-8">' : '');
        if ($value != '' && $div) {
            $return .= "<DIV id='div$name'><div onclick='javascript:addHTML(\"";
            $extra = str_replace('"', '\"', $extra);
        }

        //$return .= "<SELECT name=$name id=$name $extra class=form-control>";
        $return .= "<SELECT name=$name $extra class=form-control>";
        if ($allow_na !== false) {
            if ($value != '' && $div)
                $return .= '<OPTION value=\"\">' . $allow_na;
            else
                $return .= '<OPTION value="">' . $allow_na;
        }
        if (is_countable($options) && count($options)) {
            foreach ($options as $key => $val) {

                settype($key, 'string');
                if ($value != '' && $div)
                    $return .= "<OPTION value=\\\"" . str_replace("'", '&#39;', $key) . "\\\" " . (($value == $key && (!($value == false && ($value !== $key)) || ($value === '0' && $key === 0))) ? 'SELECTED' : '') . ">" . str_replace("'", '&#39;', (is_array($val) ? $val[0] : $val));
                else
                    $return .= "<OPTION value=\"$key\" " . (($value == $key && !($value == false && $value !== $key)) ? 'SELECTED' : '') . ">" . (is_array($val) ? $val[0] : $val);
            }
        }
        $return .= "</SELECT>";


        if ($value != '' && $div) {
            $return .= "\",\"div$name\",true)' class=form-control readonly=readonly>" . (is_array($options[$value]) ? $options[$value][1] : $options[$value]) . '</div></DIV>';
        }

        $return .= (($title != '') ? '</div>' : '');
    } else
        $return = '<div class="form-control" disabled="disabled">' . (((is_array($options[$value]) ? $options[$value][1] : $options[$value]) != '') ? (is_array($options[$value]) ? $options[$value][1] : $options[$value]) : ($allow_na !== false ? ($allow_na ? $allow_na : '-') : '-')) . '</div>' . ($title != '' ? '<p class="help-block">' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</p>' : '');

    return $return;
}

function SelectInputDisabledMsg($value, $name, $title, $options, $allow_na, $extra, $div, $msg) {
    if(empty($title)) $title = '';
    if(empty($allow_na)) $allow_na = 'N/A';
    if(empty($extra)) $extra = '';
    if(empty($div)) $div = true;
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    // mab - support array style $option values
    // mab - append current val to select list if not in list

    if ($value != '' && !$options[$value])
        $options[$value] = array($value, '<FONT color=red>' . $value . '</FONT>');

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        if ($value != '' && $div) {
            $return = "<DIV id='div$name'><div class=\"form-control\" disabled=\"disabled\" onclick='show_this_msg(\"" . $msg . "\");javascript:addHTML(\"";
            $extra = str_replace('"', '\"', $extra);
        }

        $return .= "<SELECT class=form-control name=$name $extra disabled>";
        if ($allow_na !== false) {
            if ($value != '' && $div)
                $return .= '<OPTION value=\"\">' . $allow_na;
            else
                $return .= '<OPTION value="">' . $allow_na;
        }

        if (count($options)) {
            foreach ($options as $key => $val) {

                settype($key, 'string');
                if ($value != '' && $div)
                    $return .= "<OPTION value=\\\"" . str_replace("'", '&#39;', $key) . "\\\" " . (($value == $key && (!($value == false && ($value !== $key)) || ($value === '0' && $key === 0))) ? 'SELECTED' : '') . ">" . str_replace("'", '&#39;', (is_array($val) ? $val[0] : $val));
                else
                    $return .= "<OPTION value=\"$key\" " . (($value == $key && !($value == false && $value !== $key)) ? 'SELECTED' : '') . ">" . (is_array($val) ? $val[0] : $val);
            }
        }
        $return .= "</SELECT>";

        if ($title != '')
            $return .= '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '<FONT>' : '') . '</small>';
        if ($value != '' && $div)
            $return .="\",\"div$name\",true)'>" . (is_array($options[$value]) ? $options[$value][1] : $options[$value]) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . '</div></DIV>';
    } else
        $return = (((is_array($options[$value]) ? $options[$value][1] : $options[$value]) != '') ? (is_array($options[$value]) ? $options[$value][1] : $options[$value]) : ($allow_na !== false ? ($allow_na ? $allow_na : '-') : '-')) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');

    return $return;
}

function SelectInputForCal($value, $name, $title, $options, $allow_na = 'N/A', $extra = '', $div = true) {
    if(empty($title)) $title = '';
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    // mab - support array style $option values
    // mab - append current val to select list if not in list
    if ($value != '' && !$options[$value])
        $options[$value] = array($value, '<FONT color=red>' . $value . '</FONT>');


    if ($value != '' && $div) {
        $return = "<DIV id='div$name'><div onclick='javascript:addHTML(\"";
        $extra = str_replace('"', '\"', $extra);
    }

    $return .= "<SELECT name=$name $extra>";
    if ($allow_na !== false) {
        if ($value != '' && $div)
            $return .= '<OPTION value=\"\">' . $allow_na;
        else
            $return .= '<OPTION value="">' . $allow_na;
    }
    if (count($options)) {
        foreach ($options as $key => $val) {
            settype($key, 'string');
            if ($value != '' && $div)
                $return .= "<OPTION value=\\\"" . str_replace("'", '&#39;', $key) . "\\\" " . (($value == $key && (!($value == false && ($value !== $key)) || ($value === '0' && $key === 0))) ? 'SELECTED' : '') . ">" . str_replace("'", '&#39;', (is_array($val) ? $val[0] : $val));
            else
                $return .= "<OPTION value=\"$key\" " . (($value == $key && !($value == false && $value !== $key)) ? 'SELECTED' : '') . ">" . (is_array($val) ? $val[0] : $val);
        }
    }
    $return .= "</SELECT>";
    if ($title != '')
        $return .= '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '<FONT>' : '') . '</small>';
    if ($value != '' && $div)
        $return .="\",\"div$name\",true)'>" . (is_array($options[$value]) ? $options[$value][1] : $options[$value]) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . '</div></DIV>';


    return $return;
}

function SelectInput_for_EndInput($value, $name, $title, $options, $type_id = '', $allow_na = 'N/A', $extra = '', $div = true) {
    if(empty($title)) $title = '';
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    // mab - support array style $option values
    // mab - append current val to select list if not in list
    if ($value != '' && !$options[$value])
        $options[$value] = array($value, '<FONT color=red>' . $value . '</FONT>');

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        if ($value != '' && $div) {
            $return = "<DIV id='div$name'><div onclick='javascript:addHTML(\"";
            $extra = str_replace('"', '\"', $extra);
        }

        $onchange = str_replace('"', '\"', "onchange=javascript:if(this.value==\"$type_id\")TransferredOutModal(\"detail\"," . UserStudentID() . ",$type_id);");
        if ($value != '' && $div)
            $return .= "<SELECT class=form-control name=$name $extra  $onchange>";
        else
            $return .= "<SELECT class=form-control name=$name $extra  onchange=javascript:if(this.value==\"$type_id\")TransferredOutModal(\"detail\"," . UserStudentID() . ",$type_id);>";
        if ($allow_na !== false) {
            if ($value != '' && $div)
                $return .= '<OPTION value=\"\">' . $allow_na;
            else
                $return .= '<OPTION value="">' . $allow_na;
        }
        if (count($options)) {
            foreach ($options as $key => $val) {
                settype($key, 'string');
                if ($value != '' && $div)
                    $return .= "<OPTION value=\\\"" . str_replace("'", '&#39;', $key) . "\\\" " . (($value == $key && (!($value == false && ($value !== $key)) || ($value === '0' && $key === 0))) ? 'SELECTED' : '') . ">" . str_replace("'", '&#39;', (is_array($val) ? $val[0] : $val));
                else {
                    $return .= "<OPTION value=\"$key\" " . (($value == $key && !($value == false && $value !== $key)) ? 'SELECTED' : '') . " >" . (is_array($val) ? $val[0] : $val);
                }
            }
        }
        $return .= "</SELECT>";
        if ($title != '')
            $return .= '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '<FONT>' : '') . '</small>';
        if ($value != '' && $div)
            $return .="\",\"div$name\",true)'>" . (is_array($options[$value]) ? $options[$value][1] : $options[$value]) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . '</div></DIV>';
    } else
        $return = (((is_array($options[$value]) ? $options[$value][1] : $options[$value]) != '') ? (is_array($options[$value]) ? $options[$value][1] : $options[$value]) : ($allow_na !== false ? ($allow_na ? $allow_na : '-') : '-')) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');

    return $return;
}

function NoInput($value, $title = '') {
    return ($title != '' ? '<label class="control-label col-lg-4 text-right">' . $title . '</label><div class="col-lg-8">' : '') . ($value != '' ? '<div class="form-control">' . $value . '</div>' : '<div class="form-control">-</div>') . ($title != '' ? '</div>' : '');
}

function TextInputSchool($value, $name, $title = '', $options = '', $div = true) {
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    // mab - support array style $option values
    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));
        $value1 = is_array($value) ? $value[1] : $value;
        $value = is_array($value) ? $value[0] : $value;

        if (strpos($options, 'size') === false && $value != '')
            $options .= ' size=' . strlen($value);
        elseif (strpos($options, 'size') === false)
            $options .= 'type=\'text\' class=\"cell_wide\"';

        if ((trim($value) == '' || $div == false))
            return "<INPUT type=text name=$name " . (($value || $value === '0') ? "value=\"$value\"" : '') . " $options>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
        else
            return "<DIV id='div$name'><div onclick='javascript:addHTML(\"<INPUT type=text id=input$name name=$name " . (($value || $value === '0') ? "value=\\\"" . str_replace('"', '&rdquo;', $value) . "\\\"" : '') . " $options>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . "\",\"div$name\",true); document.getElementById(\"input$name\").focus();'>" . (($value != '') ? str_replace('"', '&rdquo;', $value1) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . '</small>' : '') . '</div></DIV>';
    } else
        return (((is_array($value) ? $value[1] : $value) != '') ? (is_array($value) ? $value[1] : $value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

function ModTextInput($value, $name, $title = '', $options = '', $div = true) {

    // mab - support array style $option values
    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));
        $value1 = is_array($value) ? $value[1] : $value;
        $value = is_array($value) ? $value[0] : $value;

        if (strpos($options, 'size') === false && $value != '')
            $options .= ' size=' . strlen($value);
        elseif (strpos($options, 'size') === false)
            $options .= ' size=10';

        if ((trim($value) == '' || $div == false))
            return "<INPUT type=text name=$name " . (($value || $value === '0') ? "value=\"$value\"" : '') . " $options>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
        else
            return "<INPUT type=text name=$name " . (($value || $value === '0') ? "value=\"$value\"" : '') . " $options>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
    } else
        return (((is_array($value) ? $value[1] : $value) != '') ? (is_array($value) ? $value[1] : $value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

function TextInputWrap($value, $name, $title = '', $options = '', $div = true, $wrap = '') {
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    // mab - support array style $option values
    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));
        $value1 = is_array($value) ? $value[1] : $value;
        $value = is_array($value) ? $value[0] : $value;


        if (strpos($options, 'size') === false && $value != '')
            $options .= ' size=' . strlen($value);
        elseif (strpos($options, 'size') === false)
            $options .= ' size=10';
        else
            $options .= ' size=10 class=form-control';



        if ((trim($value) == '' || $div == false))
            return "<INPUT type=text name=$name " . (($value || $value === '0') ? "value=\"$value\"" : '') . " $options>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
        else
            return "<DIV id='div$name' STYLE='word-wrap:break-word; width:" . $wrap . "px; overflow:auto'><div onclick='javascript:addHTML(\"<INPUT type=text id=input$name name=$name " . (($value || $value === '0') ? "value=\\\"" . str_replace('"', '&rdquo;', $value) . "\\\"" : '') . " $options>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . "\",\"div$name\",true); document.getElementById(\"input$name\").focus();'>" . (($value != '') ? str_replace('"', '&rdquo;', $value1) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . '</small>' : '') . '</div></DIV>';
    } else
        return (((is_array($value) ? $value[1] : $value) != '') ? (is_array($value) ? $value[1] : $value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

function TextAreaInputWrap($value, $name, $title = '', $options = '', $div = true, $wrap = '') {
    if (Preferences('HIDDEN') != 'Y')
        $div = false;


    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));

        if (strpos($options, 'cols') === false)
            $options .= ' cols=70';
        if (strpos($options, 'rows') === false)
            $options .= ' rows=8';
        $rows = substr($options, strpos($options, 'rows') + 5, 2) * 1;
        $cols = substr($options, strpos($options, 'cols') + 5, 2) * 1;

        if ($value == '' || $div == false)
            return "<TEXTAREA name=$name $options >$value</TEXTAREA>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
        else
            return "<DIV id='div$name' STYLE='word-wrap:break-word; '><div onclick='javascript:addHTML(\"<TEXTAREA id=textarea$name name=$name $options >" . preg_replace("[\n\r]", '\u000D\u000A', str_replace("\r\n", '\u000D\u000A', str_replace("'", "&#39;", $value))) . "</TEXTAREA>" . ($title != '' ? "<BR><small>" . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . "</small>" : '') . "\",\"div$name\",true); document.getElementById(\"textarea$name\").value=unescape(document.getElementById(\"textarea$name\").value);'><TABLE class=LO_field ><TR><TD>" . ((substr_count($value, "\r\n") > $rows) ? '<DIV style="overflow:auto; height:' . (15 * $rows) . 'px; width:' . ($cols * 10) . '; padding-right: 16px;">' . nl2br($value) . '</DIV>' : '<DIV style="overflow:auto; width:300; padding-right: 16px;">' . nl2br($value) . '</DIV>') . '</TD></TR></TABLE>' . ($title != '' ? '<BR><small>' . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . '</small>' : '') . '</div></DIV>';
    } else
        return (($value != '') ? nl2br($value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

function StandardTextAreaInput($value, $name, $title = '', $options = '', $div = true) {
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));

        if (strpos($options, 'cols') === false)
            $options .= ' cols=30';
        if (strpos($options, 'rows') === false)
            $options .= ' rows=4';
        $rows = substr($options, strpos($options, 'rows') + 5, 2) * 1;
        $cols = substr($options, strpos($options, 'cols') + 5, 2) * 1;

        if ($value == '' || $div == false)
            return "<TEXTAREA name=$name id=$name $options>$value</TEXTAREA>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
        else
            return "<DIV id='div$name'><div onclick='javascript:addHTML(\"<TEXTAREA id=textarea$name name=$name $options>" . preg_replace("[\n\r]", '\u000D\u000A', str_replace("\r\n", '\u000D\u000A', str_replace("'", "&#39;", $value))) . "</TEXTAREA>" . ($title != '' ? "<BR><small>" . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . "</small>" : '') . "\",\"div$name\",true); document.getElementById(\"textarea$name\").value=unescape(document.getElementById(\"textarea$name\").value);'><TABLE class=LO_field ><TR><TD>" . ((substr_count($value, "\r\n") > $rows) ? '<DIV style="overflow:auto; height:' . (15 * $rows) . 'px; width:' . ($cols * 10) . '; padding-right: 16px;">' . nl2br($value) . '</DIV>' : '<DIV style="overflow:auto; width:100; padding-right: 16px;">' . nl2br($value) . '</DIV>') . '</TD></TR></TABLE>' . ($title != '' ? '<BR><small>' . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . '</small>' : '') . '</div></DIV>';
    } else
        return (($value != '') ? nl2br($value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

function DateInputAY($value, $name, $counter = 1, $div_visibility = false) {

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {

        if ($value != '') {
            $month_names = array('JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC');
            $show = "value='$value'";
            $date_sep = explode('-', $value);
            //$monthVal = "value='" . $month_names[$date_sep[1] - 1] . "'";
            $monthVal = "value='" . $date_sep[1] . "'";

            //$yearVal = "value='$date_sep[0]'";
            $yearVal = "value='$date_sep[0]'";
            //$dayVal = "value='$date_sep[2]'";
            $dayVal = "value='$date_sep[2]'";
            $display = "";
        } else {
            $show = "";
            $date_sep = "";
            $monthVal = "";
            $yearVal = "";
            $dayVal = "";
            $display = "";
        }

        if ($value != '') {
            if ($div_visibility == false) {
                /*
                 * Old Calendar Style
                 * init(' . $counter . ',2);
                 */
                return '<div id="date_div_' . $counter . '" class="fake_datepicker" onClick="$(\'#original_date_' . $counter . '\').show(); $(\'#date_div_' . $counter . '\').hide();">' . (($title != '') ? '<label class="control-label col-md-4 text-right">' . $title . '</label><div class="col-md-8">' : '') . '<div class="input-group"><span class="input-group-addon"><i class="icon-calendar22"></i></span><input type="text" readonly="readonly" data-calid="' . $counter . '" class="form-control" value="' . ProperDateAY($value) . '" /></div>' . (($title != '') ? '</div>' : '') . '</div>'
                        //. '<div id="date_div_' . $counter . '" class="fake_datepicker" onClick="$(\'#original_date_' . $counter . '\').show(); $(\'#date_div_' . $counter . '\').hide();" style="display: inline" >' . (($title != '') ? '<label class="control-label col-md-4 text-right">' . $title . '</label><div class="col-md-8">' : '') . '<div class="input-group"><span class="input-group-addon"><i class="icon-calendar22"></i></span><input type="text" readonly="readonly" data-calid="'.$counter.'" class="form-control" value="' .$value. '" /></div>' . (($title != '') ? '</div>' : '') . '</div>'
                        . '<div class="input-group datepicker-group" id="original_date_' . $counter . '" ' . $show . '  style="display:none;">'
                        . '<span class="input-group-addon"><i class="icon-calendar22"></i></span>'
                        . '<input type="text" id="date_' . $counter . '" placeholder="' . $title . '" id="date_' . $counter . '" ' . $show . ' class="form-control daterange-single" value="' . $value . '">'
                        . '</div>'
                        . '<input type=hidden ' . $monthVal . ' id="monthSelect_date_' . $counter . '" name="month_' . $name . '" >'
                        . '<input type=hidden ' . $dayVal . '  id="daySelect_date_' . $counter . '"   name="day_' . $name . '">'
                        . '<input type=hidden ' . $yearVal . '  id="yearSelect_date_' . $counter . '" name="year_' . $name . '" >';
            } else {
                return '<div class="input-group datepicker-group" id="original_date_' . $counter . '" ' . $show . '>'
                        . '<span class="input-group-addon"><i class="icon-calendar22"></i></span>'
                        . '<input type="text" placeholder="' . $title . '" id="date_' . $counter . '" ' . $show . ' class="form-control daterange-single" value="">'
                        . '</div>'
                        . '<input type=hidden ' . $monthVal . ' id="monthSelect_date_' . $counter . '" name="month_' . $name . '" >'
                        . '<input type=hidden ' . $dayVal . '  id="daySelect_date_' . $counter . '"   name="day_' . $name . '">'
                        . '<input type=hidden ' . $yearVal . '  id="yearSelect_date_' . $counter . '" name="year_' . $name . '" >';
                //return '<table cellspacing="0" cellpadding="0"><tr><td><input type=text id="date_' . $counter . '" ' . $show . '  readonly></td><td>&nbsp; </td><td><a onClick="init(' . $counter . ',1);"><img src="assets/calendar.gif"  /></a><input type=hidden ' . $monthVal . ' id="monthSelect' . $counter . '" name="month_' . $name . '" ><input type=hidden ' . $dayVal . '  id="daySelect' . $counter . '"   name="day_' . $name . '"><input type=hidden ' . $yearVal . '  id="yearSelect' . $counter . '" name="year_' . $name . '" ></td></tr></table>';
            }
        } else {
            return '<div class="input-group datepicker-group" id="original_date_' . $counter . '" ' . $show . '>'
                    . '<span class="input-group-addon"><i class="icon-calendar22"></i></span>'
                    . '<input type="text" placeholder="' . $title . '" id="date_' . $counter . '" ' . $show . ' class="form-control daterange-single" value="">'
                    . '</div>'
                    . '<input type=hidden ' . $monthVal . ' id="monthSelect_date_' . $counter . '" name="month_' . $name . '" >'
                    . '<input type=hidden ' . $dayVal . '  id="daySelect_date_' . $counter . '"   name="day_' . $name . '">'
                    . '<input type=hidden ' . $yearVal . '  id="yearSelect_date_' . $counter . '" name="year_' . $name . '" >';
        }
        //return '<table cellspacing="0" cellpadding="0"><tr><td><input type=text id="date_' . $counter . '" ' . $show . '  readonly></td><td>&nbsp; </td><td><a onClick="init(' . $counter . ',1);"><img src="assets/calendar.gif"  /></a><input type=hidden ' . $monthVal . ' id="monthSelect' . $counter . '" name="month_' . $name . '" ><input type=hidden ' . $dayVal . '  id="daySelect' . $counter . '"   name="day_' . $name . '"><input type=hidden ' . $yearVal . '  id="yearSelect' . $counter . '" name="year_' . $name . '" ></td></tr></table>';
    } else {
        return '<div class="form-control" disabled=disabled>' . ProperDateAY($value) . '</div>';
    }
}

function DateInputAY_red($value, $name, $counter, $cp_id) {

    if(empty($counter)) $counter = 1;
    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        if ($value != '') {
            $month_names = array('JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC');
            $show = "value='$value'";
            $date_sep = explode('-', $value);
            $monthVal = "value='" . $month_names[$date_sep[1] - 1] . "'";

            $yearVal = "value='$date_sep[0]'";
            $dayVal = "value='$date_sep[2]'";
            $display = "";
        } else {
            $show = "";
            $date_sep = "";
            $monthVal = "";
            $yearVal = "";
            $dayVal = "";
            $display = "";
        }

        if ($value != '') {
            $student_id = UserStudentID();
            $qr = DBGet(DBQuery('select end_date from student_enrollment where student_id=' . $student_id . ' order by id desc limit 0,1'));

            $stu_end_date = $qr[1]['END_DATE'];
            $qr1 = DBGet(DBQuery('select end_date from course_periods where COURSE_PERIOD_ID=' . $cp_id . ''));

            $cr_end_date = $qr1[1]['END_DATE'];
            if (strtotime($cr_end_date) > strtotime($stu_end_date) && $stu_end_date != '') {
                return '<table cellspacing="0" cellpadding="0"><tr><td><div id="date_div_' . $counter . '" style="display: inline" ><FONT color=red>' . ProperDateAY($value) . '</FONT></div><input type=text id="date_' . $counter . '" ' . $show . '  style="display:none" readonly></td><td>&nbsp; </td><td><a onClick="init(' . $counter . ',2);"><img src="assets/calendar.gif"  /></a><input type=hidden ' . $monthVal . ' id="monthSelect' . $counter . '" name="month_' . $name . '" ><input type=hidden ' . $dayVal . '  id="daySelect' . $counter . '"   name="day_' . $name . '"><input type=hidden ' . $yearVal . '  id="yearSelect' . $counter . '" name="year_' . $name . '" ></td></tr></table>';
            } else {
                return '<table cellspacing="0" cellpadding="0"><tr><td><div id="date_div_' . $counter . '" style="display: inline" >' . ProperDateAY($value) . '</div><input type=text id="date_' . $counter . '" ' . $show . '  style="display:none" readonly></td><td>&nbsp; </td><td><a onClick="init(' . $counter . ',2);"><img src="assets/calendar.gif"  /></a><input type=hidden ' . $monthVal . ' id="monthSelect' . $counter . '" name="month_' . $name . '" ><input type=hidden ' . $dayVal . '  id="daySelect' . $counter . '"   name="day_' . $name . '"><input type=hidden ' . $yearVal . '  id="yearSelect' . $counter . '" name="year_' . $name . '" ></td></tr></table>';
            }
            return '<table cellspacing="0" cellpadding="0"><tr><td><div id="date_div_' . $counter . '" style="display: inline" >' . ProperDateAY($value) . '</div><input type=text id="date_' . $counter . '" ' . $show . '  style="display:none" readonly></td><td>&nbsp; </td><td><a onClick="init(' . $counter . ',2);"><img src="assets/calendar.gif"  /></a><input type=hidden ' . $monthVal . ' id="monthSelect' . $counter . '" name="month_' . $name . '" ><input type=hidden ' . $dayVal . '  id="daySelect' . $counter . '"   name="day_' . $name . '"><input type=hidden ' . $yearVal . '  id="yearSelect' . $counter . '" name="year_' . $name . '" ></td></tr></table>';
        } else
            return '<table cellspacing="0" cellpadding="0"><tr><td><input type=text id="date_' . $counter . '" ' . $show . '  readonly></td><td>&nbsp; </td><td><a onClick="init(' . $counter . ',1);"><img src="assets/calendar.gif"  /></a><input type=hidden ' . $monthVal . ' id="monthSelect' . $counter . '" name="month_' . $name . '" ><input type=hidden ' . $dayVal . '  id="daySelect' . $counter . '"   name="day_' . $name . '"><input type=hidden ' . $yearVal . '  id="yearSelect' . $counter . '" name="year_' . $name . '" ></td></tr></table>';
    } else
        return ProperDateAY($value);
}

function TextInputModal($value, $name, $title = '', $options = '', $div = true) {
    $original_title = $title;
    $title = str_replace('*', '', $original_title);
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    // mab - support array style $option values
    if (!$_REQUEST['_openSIS_PDF']) {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));
        $value1 = is_array($value) ? $value[1] : $value;
        $value = is_array($value) ? $value[0] : $value;

        if (strpos($options, 'size') === false && $value != '')
            $options .= ' size=' . strlen($value);
        elseif (strpos($options, 'size') === false)
            $options .= ' size=10';

        if (strstr($value, '\\') != '')
            $div = false;
        if ((trim($value) == '' || $div == false))
//            return (($title != '') ? '<label for="' . $name . '" class="control-label text-right col-lg-4">' . $title . '</label><div class="col-lg-8">' : '') . "<INPUT class=\"form-control\"  type=text id=$name name=$name " . (($value || $value === '0') ? "value=\"$value\"" : '') . " $options>" . (($title != '') ? '</div>' : '');
            return (($title != '') ? '<label for="' . $name . '" class="control-label text-right col-lg-4">' . str_replace('*', '<span class="text-danger">*</span>', $original_title) . '</label><div class="col-lg-8">' : '') . "<INPUT class=\"form-control\" type=text id=$name name=$name " . (($value || $value === '0') ? "value=\"$value\"" : '') . " $options>" . (($title != '') ? '</div>' : '');
        else {

            return (($title != '') ? '<label for="' . $name . '" class="control-label text-right col-lg-4">' . str_replace('*', '<span class="text-danger">*</span>', $original_title) . '</label><div class="col-lg-8">' : '') . "<DIV id='div$name'><div onclick='javascript:addHTML(\"<INPUT type=text class=form-control id=input$name name=$name " . (($value || $value === '0') ? "value=\\\"" . str_replace('"', '&rdquo;', $value) . "\\\"" : '') . " $options>\",\"div$name\",true); document.getElementById(\"input$name\").focus();' readonly=\"readonly\" class=\"form-control\">" . $value . '</div></DIV>' . (($title != '') ? '</div>' : '');
        }
    } else {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));
        return ($title != '' ? '<label class="control-label text-right col-lg-4">' . $title . '</label><div class="col-lg-8">' : '') . '<div class="form-control" disabled=disabled>' . (((is_array($value) ? $value[1] : $value) != '') ? (is_array($value) ? $value[1] : $value) : '-') . '</div>' . ($title != '' ? '</div>' : '');
    }
}

function TextInputCusIdModal($value, $name, $title = '', $options = '', $div = true, $ex_id = '') {
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    // mab - support array style $option values
    if (!$_REQUEST['_openSIS_PDF']) {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));
        $value1 = is_array($value) ? $value[1] : $value;
        $value = is_array($value) ? $value[0] : $value;

        if (strpos($options, 'size') === false && $value != '')
            $options .= ' size=' . strlen($value);
        elseif (strpos($options, 'size') === false)
            $options .= ' size=10';

        if ((trim($value) == '' || $div == false))
            return "<label class=\"control-label text-right col-lg-4\">" . ($title != '' ? $title : '') . "</label><div class=\"col-lg-8\"><INPUT class=control-label type=text name=$name " . (($value || $value === '0') ? "value=\"$value\"" : '') . " $options></div>";
        else {
            if ($ex_id == '')
                return "<label class=\"control-label text-right col-lg-4\">$title</label><div class=\"col-lg-8\"><div id='div$name'><div onclick='javascript:addHTML(\"<INPUT class=form-control type=text id=input$name name=$name " . (($value || $value === '0') ? "value=\\\"" . str_replace('"', '&rdquo;', $value) . "\\\"" : '') . " $options>" . "\",\"div$name\",true); document.getElementById(\"input$name\").focus();'><input type=\"text\" readonly=\"readonly\" class=\"form-control\" value=\"" . (($value != '') ? str_replace('"', '&rdquo;', $value1) : '-') . ($title != '' ? $title : '') . '" /></div></div></div>';
            else
                return "<label class=\"control-label text-right col-lg-4\">$title</label><div class=\"col-lg-8\"><div id='div$name'><div onclick='javascript:addHTML(\"<INPUT class=form-control partha type=text id=$ex_id name=$name " . (($value || $value === '0') ? "value=\\\"" . str_replace('"', '&rdquo;', $value) . "\\\"" : '') . " $options>" . "\",\"div$name\",true); document.getElementById(\"$ex_id\").focus();'><input type=\"text\" readonly=\"readonly\" class=\"form-control\" value=\"" . (($value != '') ? str_replace('"', '&rdquo;', $value1) : '-') . ($title != '' ? $title : '') . '" /></div></div></div>';
        }
    } else
        return (((is_array($value) ? $value[1] : $value) != '') ? (is_array($value) ? $value[1] : $value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

function TextAreaInputModal($value, $name, $title = '', $options = '', $div = true) {
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    if (!$_REQUEST['_openSIS_PDF']) {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));

        if (strpos($options, 'cols') === false)
            $options .= ' cols=30';
        if (strpos($options, 'rows') === false)
            $options .= ' rows=4';
        $rows = substr($options, strpos($options, 'rows') + 5, 2) * 1;
        $cols = substr($options, strpos($options, 'cols') + 5, 2) * 1;

        if ($value == '' || $div == false)
            return (($title != '') ? '<label class="control-label col-lg-4 text-right" for="' . $name . '">' . $title . '</label><div class="col-lg-8">' : '') . "<TEXTAREA class=form-control id=$name name=$name $options>$value</TEXTAREA>" . (($title != '') ? "</div>" : "");
        else
            return ($title != '' ? '<label class="control-label col-lg-4 text-right" for="' . $name . '">' . $title . '</label><div class="col-lg-8">' : '') . "<DIV id='div$name'><div class='form-control' readonly='readonly' onclick='javascript:addHTML(\"<TEXTAREA class=form-control id=textarea$name name=$name $options>" . str_replace("\r\n", '\u000D\u000A', str_replace("'", "&#39;", $value)) . "</TEXTAREA>" . "\",\"div$name\",true); document.getElementById(\"textarea$name\").value=unescape(document.getElementById(\"textarea$name\").value);'>" . ((substr_count($value, "\r\n") > $rows) ? '<DIV>' . nl2br($value) . '</DIV>' : '<DIV>' . nl2br($value) . '</DIV>') . '</div></DIV>' . (($title != '') ? "</div>" : "");
//            return ($title != '' ? '<label class="control-label col-lg-4" for="' . $name . '">' . $title . '</label><div class="col-lg-8">' : '') . "<DIV id='div$name'><div onclick='javascript:addHTML(\"<TEXTAREA class=form-control placeholder=$title id=textarea$name name=$name $options>" . ereg_replace("[\n\r]", '\u000D\u000A', str_replace("\r\n", '\u000D\u000A', str_replace("'", "&#39;", $value))) . "</TEXTAREA>" . ($title != '' ? "<BR><small>" . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . "</small>" : '') . "\",\"div$name\",true); document.getElementById(\"textarea$name\").value=unescape(document.getElementById(\"textarea$name\").value);'>" . ((substr_count($value, "\r\n") > $rows) ? '<DIV>' . nl2br($value) . '</DIV>' : '<DIV>' . nl2br($value) . '</DIV>') . '</div></DIV>' . (($title != '') ? "</div>" : "");
    } else
        return (($value != '') ? nl2br($value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

function CheckboxInputSwitchModal($value, $name, $title = '', $checked = '', $new = false, $yes = _yes, $no = _no, $extra = '', $switchery_color = 'switch-default', $size = 'sm') {

    // $checked has been deprecated -- it remains only as a placeholder
    if (Preferences('HIDDEN') != 'Y') {
        $div = false;
    }

    if ($switchery_color == '') {
        $switchery_color = 'switch-primary';
    }

    if ($div == false || $new == true) {
        if ($value && $value != 'N') {
            $checked = 'CHECKED';
        } else {
            $checked = '';
        }
    }

    if (!$_REQUEST['_openSIS_PDF']) {

        if ($new || $div == false) {
            return '<INPUT type=hidden name="' . $name . '"  value=""><div class="checkbox checkbox-switch ' . $switchery_color . ' switch-' . $size . '">'
                    . '<label>'
                    . '<input type="checkbox" value=Y name="' . $name . '" ' . $checked . ' ' . $extra . '><span></span>' . $title . '</label>'
                    . '</div>';
        } else {
            return '<INPUT type=hidden name="' . $name . '"  value="">'
                    . '<div class="checkbox checkbox-switch ' . $switchery_color . ' switch-' . $size . '">'
                    . '<label>'
                    . '<INPUT type=checkbox name="' . $name . '" ' . (($value) ? 'checked="checked"' : '') . ' value=Y ><span></span>' . $title . '</label>'
                    . '</div>';
        }
        /* if ($new || $div == false)
          return "<div class=\"checkbox-inline\"><INPUT type=checkbox name=$name  value=Y $checked $extra>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '') . '</div>';
          else
          return "<DIV id='div$name'><div onclick='javascript:addHTML(\"<INPUT type=hidden name=$name  value=\\\"\\\"><div class=checkbox-inline><INPUT type=checkbox name=$name " . (($value) ? 'checked' : '') . " value=Y " . str_replace('"', '\"', $extra) . "></div>" . ($title != '' ? '<BR><small>' . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . '</small>' : '') . "\",\"div$name\",true)'>" . ($value ? $yes : $no) . ($title != '' ? "<BR><small>" . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . "</small>" : '') . "</div></DIV>"; */
    } else
    //return ($value ? $yes : $no) . ($title != '' ? '<BR><small>' . $title . '</small>' : '');
        return '<div class="checkbox checkbox-switch ' . $switchery_color . ' switch-' . $size . '"><label><INPUT type=checkbox disabled="disabled" ' . ($value ? 'checked=checked' : '') . '><span></span>' . $title . '</label></div>';
}

function SelectInputModal($value, $name, $title, $options, $allow_na = 'N/A', $extra = '', $div = true) {
    if(empty($title)) $title = '';
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    // mab - support array style $option values
    // mab - append current val to select list if not in list

    if ($value != '' && !$options[$value])
        $options[$value] = array($value, '<FONT color=red>' . $value . '</FONT>');

    if (!$_REQUEST['_openSIS_PDF']) {
//        $return = (($title != '') ? '<label class="control-label text-right col-lg-4">' . $title . '</label><div class="col-lg-8">' : '');
        $return = (($title != '') ? '<label class="control-label text-right col-lg-4">' . $title . '</label><div class="col-lg-8">' : '');
        if ($value != '' && $div) {
            $return .= "<DIV id='div$name'><div onclick='javascript:addHTML(\"";
            $extra = str_replace('"', '\"', $extra);
        }

        //$return .= "<SELECT name=$name id=$name $extra class=form-control>";
        $return .= "<SELECT name=$name $extra class=form-control>";
        if ($allow_na !== false) {
            if ($value != '' && $div)
                $return .= '<OPTION value=\"\">' . $allow_na;
            else
                $return .= '<OPTION value="">' . $allow_na;
        }
        if (count($options)) {
            foreach ($options as $key => $val) {

                settype($key, 'string');
                if ($value != '' && $div)
                    $return .= "<OPTION value=\\\"" . str_replace("'", '&#39;', $key) . "\\\" " . (($value == $key && (!($value == false && ($value !== $key)) || ($value === '0' && $key === 0))) ? 'SELECTED' : '') . ">" . str_replace("'", '&#39;', (is_array($val) ? $val[0] : $val));
                else
                    $return .= "<OPTION value=\"$key\" " . (($value == $key && !($value == false && $value !== $key)) ? 'SELECTED' : '') . ">" . (is_array($val) ? $val[0] : $val);
            }
        }
        $return .= "</SELECT>";


        if ($value != '' && $div) {
            $return .= "\",\"div$name\",true)' class=form-control readonly=readonly>" . (is_array($options[$value]) ? $options[$value][1] : $options[$value]) . '</div></DIV>';
        }

        $return .= (($title != '') ? '</div>' : '');
    } else
        $return = '<div class="form-control" disabled="disabled">' . (((is_array($options[$value]) ? $options[$value][1] : $options[$value]) != '') ? (is_array($options[$value]) ? $options[$value][1] : $options[$value]) : ($allow_na !== false ? ($allow_na ? $allow_na : '-') : '-')) . '</div>' . ($title != '' ? '<p class="help-block">' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</p>' : '');

    return $return;
}

?>
