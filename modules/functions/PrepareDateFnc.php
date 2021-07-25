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
// SEND PrepareDate a name prefix, and a date in oracle format 'd-M-y' as the selected date to have returned a date selection series
// of pull-down menus
// For the default to be Not Specified, send a date of 00-000-00 -- For today's date, send nothing
// The date pull-downs will create three variables, monthtitle, daytitle, yeartitle
// The third parameter (booleen) specifies whether Not Specified should be allowed as an option

function PrepareDate($date = '', $title = '', $allow_na = true, $options = '', $cal_img = 'Y') {
    //print_r($options);
    global $_openSIS;
    if ($options == '') {
        $options = array();
    }
    if (!$options['Y'] && !$options['M'] && !$options['D'] && !$options['C']) {
        $options += array('Y' =>true, 'M' =>true, 'D' =>true, 'C' =>true);
    }
    if ($options['disabled'] == true) {
        $extraM = ' DISABLED ';
        $extraD = ' DISABLED ';
        $extraY = ' DISABLED ';
    }
    if ($options['short'] == true)
        $extraM = "style='width:60;' ";
    if ($options['submit'] == true) {
        $tmp_REQUEST['M'] = $tmp_REQUEST['D'] = $tmp_REQUEST['Y'] = $_REQUEST;
        unset($tmp_REQUEST['M']['month' . $title]);
        unset($tmp_REQUEST['D']['day' . $title]);
        unset($tmp_REQUEST['Y']['year' . $title]);
        $extraM .= "onchange='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST['M']) . "&amp;month$title=\"+this.form.month$title.value;'";
        $extraD .= "onchange='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST['D']) . "&amp;day$title=\"+this.form.day$title.value;'";
        $extraY .= "onchange='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST['Y']) . "&amp;year$title=\"+this.form.year$title.value;'";
    }

    if ($options['C'])
        $_openSIS['PrepareDate'] ++;

    if (strlen($date) == 9) { // ORACLE
        $day = substr($date, 0, 2);
        $month = substr($date, 3, 3);
        $year = substr($date, 7, 2);
    } else { // mysql
        if ($date != '') {
            $day = substr($date, 8, 2);
            $month = MonthNWSwitch(substr($date, 5, 2), 'tochar');
            $year = substr($date, 2, 2);
        }
        if ($allow_na == false && $date == '') {
            $day = date('d');
            $month = date('m');
            $year = date('y');
        }
    }

    $return .= '<div id="date_div_'.$_openSIS['PrepareDate'].'" class="fake_datepicker" onclick="$(\'#original_date_' . $_openSIS['PrepareDate'] . '\').show(); $(\'#date_div_'.$_openSIS['PrepareDate'].'\').hide();">';
    $return .= '<div class="input-group"><span class="input-group-addon"><i class="icon-calendar22"></i></span><input readonly="readonly" data-calid="1" class="form-control" value="'.(($options['view']=='month')?date('M/Y', strtotime($date)):date('M/d/Y', strtotime($date))).'" type="text"></div>';
    $return .= '</div>';
    
    $return .= '<div class="input-group '.(($options['view']=='month')?'datepicker-group-month':'datepicker-group').'" style="display: none;" id="original_date_' . $_openSIS['PrepareDate'] . '" value="' . date('Y-m-d', strtotime($date)) . '">';
    $return .= '<span class="input-group-addon"><i class="icon-calendar22"></i></span>';
    $return .= '<input id="date_' . $_openSIS['PrepareDate'] . '" placeholder="" value="' .(($options['view']=='month')?date('m/Y', strtotime($date)):date('Y-m-d', strtotime($date))). '" class="form-control daterange-single" type="text">';
    $return .= '</div>';

    // MONTH  ---------------
    if ($options['M']) {
//		$return .= "<SELECT NAME=month".$title." class=\"form-control\" id=monthSelect".$_openSIS['PrepareDate']." $extraM>";
//		//  -------------------------------------------------------------------------- //
//		
//		if($month == 'JAN')
//			$month = 1;
//		elseif($month == 'FEB')
//			$month = 2;
//		elseif($month == 'MAR')
//			$month = 3;
//		elseif($month == 'APR')
//			$month = 4;
//		elseif($month == 'MAY')
//			$month = 5;
//		elseif($month == 'JUN')
//			$month = 6;
//		elseif($month == 'JUL')
//			$month = 7;
//		elseif($month == 'AUG')
//			$month = 8;
//		elseif($month == 'SEP')
//			$month = 9;
//		elseif($month == 'OCT')
//			$month = 10;
//		elseif($month == 'NOV')
//			$month = 11;
//		elseif($month == 'DEC')
//			$month = 12;
//		
//		//  -------------------------------------------------------------------------- //
//		if($allow_na)
//		{
//			if($month=='000')
//				$return .= "<OPTION value=\"\" SELECTED>N/A";else $return .= "<OPTION value=\"\">N/A";
//		}
//		if($month=='1'){$return .= "<OPTION VALUE=JAN SELECTED>January";}else{$return .= "<OPTION VALUE=JAN>January";}
//		if($month=='2'){$return .= "<OPTION VALUE=FEB SELECTED>February";}else{$return .= "<OPTION VALUE=FEB>February";}
//		if($month=='3'){$return .= "<OPTION VALUE=MAR SELECTED>March";}else{$return .= "<OPTION VALUE=MAR>March";}
//		if($month=='4'){$return .= "<OPTION VALUE=APR SELECTED>April";}else{$return .= "<OPTION VALUE=APR>April";}
//		if($month=='5'){$return .= "<OPTION VALUE=MAY SELECTED>May";}else{$return .= "<OPTION VALUE=MAY>May";}
//		if($month=='6'){$return .= "<OPTION VALUE=JUN SELECTED>June";}else{$return .= "<OPTION VALUE=JUN>June";}
//		if($month=='7'){$return .= "<OPTION VALUE=JUL SELECTED>July";}else{$return .= "<OPTION VALUE=JUL>July";}
//		if($month=='8'){$return .= "<OPTION VALUE=AUG SELECTED>August";}else{$return .= "<OPTION VALUE=AUG>August";}
//		if($month=='9'){$return .= "<OPTION VALUE=SEP SELECTED>September";}else{$return .= "<OPTION VALUE=SEP>September";}
//		if($month=='10'){$return .= "<OPTION VALUE=OCT SELECTED>October";}else{$return .= "<OPTION VALUE=OCT>October";}
//		if($month=='11'){$return .= "<OPTION VALUE=NOV SELECTED>November";}else{$return .= "<OPTION VALUE=NOV>November";}
//		if($month=='12'){$return .= "<OPTION VALUE=DEC SELECTED>December";}else{$return .= "<OPTION VALUE=DEC>December";}
//		
//		$return .= "</SELECT>";
        $return .= '<input value="' . date('m', strtotime($date)) . '" id="monthSelect_date_' . $_openSIS['PrepareDate'] . '" name="month' . $title . '" type="hidden">';
    }

    // DAY  ---------------
    if ($options['D']) {
//        $return .= "<SELECT NAME=day" . $title . " id=daySelect" . $_openSIS['PrepareDate'] . " class=\"form-control\" $extraD>";
//        if ($allow_na) {
//            if ($day == '00') {
//                $return .= "<OPTION value=\"\" SELECTED>N/A";
//            } else {
//                $return .= "<OPTION value=\"\">N/A";
//            }
//        }
//
//        for ($i = 1; $i <= 31; $i++) {
//            if (strlen($i) == 1)
//                $print = '0' . $i;
//            else
//                $print = $i;
//
//            $return .="<OPTION VALUE=" . $print;
//            if ($day == $print)
//                $return .=" SELECTED";
//            $return .=">$i ";
//        }
//        $return .="</SELECT></div>";

        $return .= '<input value="' . date('d', strtotime($date)) . '" id="daySelect_date_' . $_openSIS['PrepareDate'] . '" name="day' . $title . '" type="hidden">';
    }

    // YEAR	 ---------------
    if ($options['Y']) {
//        if (!$year) {
//
//            $begin = date('Y') - 60;
//            $end = date('Y') + 20;
//        } else {
//            if ($year < 50)
//                $year = '20' . $year;
//            else
//                $year = '19' . $year;
//
//
//
//            $begin = $year - 59;
//            $end = $year + 21;
//        }
//
//        $return .="<div class=\"form-group\"><SELECT NAME=year" . $title . " id=yearSelect" . $_openSIS['PrepareDate'] . " class=\"form-control\" $extraY>";
//        if ($allow_na) {
//            if ($year == '00') {
//                $return .= "<OPTION value=\"\" SELECTED>N/A";
//            } else {
//                $return .= "<OPTION value=\"\">N/A";
//            }
//        }
//
//        for ($i = $begin; $i <= $end; $i++) {
//            $return .="<OPTION VALUE=" . substr($i, 0);
//            if ($year == $i) {
//                $return .=" SELECTED";
//            }
//            $return .=">" . $i;
//        }
//        $return .="</SELECT></div>";

        $return .= '<input value="' . date('Y', strtotime($date)) . '" id="yearSelect_date_' . $_openSIS['PrepareDate'] . '" name="year' . $title . '" type="hidden">';
    }

//    if ($options['C']) {
//        if ($cal_img != 'N') {
//            $return .= '<span style="padding-top:4px"><img src="assets/calendar.gif" id="trigger' . $_openSIS['PrepareDate'] . '" style="cursor: pointer;" onmouseover=this.style.background=""; onmouseout=this.style.background=""; onClick=' . "MakeDate('" . $_openSIS['PrepareDate'] . "',this);" . ' /></span>';
//        }
//    }
    if ($_REQUEST['_openSIS_PDF'])
        $return = ProperDateAY($date);
    return $return;
}

function PrepareDate2($date = '', $title = '', $selectid, $allow_na = true, $options = '', $cal_img = 'Y') {
    global $_openSIS;
    if ($options == '')
        $options = array();
    if (!$options['Y'] && !$options['M'] && !$options['D'] && !$options['C'])
        $options += array('Y' =>true, 'M' =>true, 'D' =>true, 'C' =>true);
    if ($options['disabled'] == true) {
        $extraM = ' DISABLED ';
        $extraD = ' DISABLED ';
        $extraY = ' DISABLED ';
    }
    if ($options['short'] == true)
        $extraM = "style='width:60;' ";
    if ($options['submit'] == true) {
        $tmp_REQUEST['M'] = $tmp_REQUEST['D'] = $tmp_REQUEST['Y'] = $_REQUEST;
        unset($tmp_REQUEST['M']['month' . $title]);
        unset($tmp_REQUEST['D']['day' . $title]);
        unset($tmp_REQUEST['Y']['year' . $title]);
        $extraM .= "onchange='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST['M']) . "&amp;month$title=\"+this.form.month$title.value;'";
        $extraD .= "onchange='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST['D']) . "&amp;day$title=\"+this.form.day$title.value;'";
        $extraY .= "onchange='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST['Y']) . "&amp;year$title=\"+this.form.year$title.value;'";
    }

    if ($options['C'])
        $_openSIS['PrepareDate'] ++;

    if (strlen($date) == 9) { // ORACLE
        $day = substr($date, 0, 2);
        $month = substr($date, 3, 3);
        $year = substr($date, 7, 2);

        $return .= '<!-- ' . $year . MonthNWSwitch($month, 'tonum') . $day . ' -->';
    } else { // mysql
        $day = substr($date, 8, 2);
        $month = MonthNWSwitch(substr($date, 5, 2), 'tochar');
        $year = substr($date, 2, 2);

        $return .= '<!-- ' . $year . MonthNWSwitch($month, 'tonum') . $day . ' -->';
    }

    // MONTH  ---------------
    if ($options['M']) {
        $return .= "<div class=\"form-group\"><SELECT style=width:90px; NAME=month" . $title . " id=monthSelect" . $selectid . " class=\"form-control\" $extraM>";
        //  -------------------------------------------------------------------------- //

        if ($month == 'JAN')
            $month = 1;
        elseif ($month == 'FEB')
            $month = 2;
        elseif ($month == 'MAR')
            $month = 3;
        elseif ($month == 'APR')
            $month = 4;
        elseif ($month == 'MAY')
            $month = 5;
        elseif ($month == 'JUN')
            $month = 6;
        elseif ($month == 'JUL')
            $month = 7;
        elseif ($month == 'AUG')
            $month = 8;
        elseif ($month == 'SEP')
            $month = 9;
        elseif ($month == 'OCT')
            $month = 10;
        elseif ($month == 'NOV')
            $month = 11;
        elseif ($month == 'DEC')
            $month = 12;

        //  -------------------------------------------------------------------------- //
        if ($allow_na) {
            if ($month == '000')
                $return .= "<OPTION value=\"\" SELECTED>N/A";
            else
                $return .= "<OPTION value=\"\">N/A";
        }
        if ($month == '1') {
            $return .= "<OPTION VALUE=JAN SELECTED>January";
        } else {
            $return .= "<OPTION VALUE=JAN>January";
        }
        if ($month == '2') {
            $return .= "<OPTION VALUE=FEB SELECTED>February";
        } else {
            $return .= "<OPTION VALUE=FEB>February";
        }
        if ($month == '3') {
            $return .= "<OPTION VALUE=MAR SELECTED>March";
        } else {
            $return .= "<OPTION VALUE=MAR>March";
        }
        if ($month == '4') {
            $return .= "<OPTION VALUE=APR SELECTED>April";
        } else {
            $return .= "<OPTION VALUE=APR>April";
        }
        if ($month == '5') {
            $return .= "<OPTION VALUE=MAY SELECTED>May";
        } else {
            $return .= "<OPTION VALUE=MAY>May";
        }
        if ($month == '6') {
            $return .= "<OPTION VALUE=JUN SELECTED>June";
        } else {
            $return .= "<OPTION VALUE=JUN>June";
        }
        if ($month == '7') {
            $return .= "<OPTION VALUE=JUL SELECTED>July";
        } else {
            $return .= "<OPTION VALUE=JUL>July";
        }
        if ($month == '8') {
            $return .= "<OPTION VALUE=AUG SELECTED>August";
        } else {
            $return .= "<OPTION VALUE=AUG>August";
        }
        if ($month == '9') {
            $return .= "<OPTION VALUE=SEP SELECTED>September";
        } else {
            $return .= "<OPTION VALUE=SEP>September";
        }
        if ($month == '10') {
            $return .= "<OPTION VALUE=OCT SELECTED>October";
        } else {
            $return .= "<OPTION VALUE=OCT>October";
        }
        if ($month == '11') {
            $return .= "<OPTION VALUE=NOV SELECTED>November";
        } else {
            $return .= "<OPTION VALUE=NOV>November";
        }
        if ($month == '12') {
            $return .= "<OPTION VALUE=DEC SELECTED>December";
        } else {
            $return .= "<OPTION VALUE=DEC>December";
        }

        $return .= "</SELECT></div>";
    }

    // DAY  ---------------
    if ($options['D']) {
        $return .="<div class=\"form-group\"><SELECT NAME=day" . $title . " id=daySelect" . $selectid . " class=\"form-control\" $extraD>";
        if ($allow_na) {
            if ($day == '00') {
                $return .= "<OPTION value=\"\" SELECTED>N/A";
            } else {
                $return .= "<OPTION value=\"\">N/A";
            }
        }

        for ($i = 1; $i <= 31; $i++) {
            if (strlen($i) == 1)
                $print = '0' . $i;
            else
                $print = $i;

            $return .="<OPTION VALUE=" . $print;
            if ($day == $print)
                $return .=" SELECTED";
            $return .=">$i ";
        }
        $return .="</SELECT></div>";
    }

    // YEAR	 ---------------
    if ($options['Y']) {
        if (!$year) {

            $begin = date('Y') - 60;
            $end = date('Y') + 20;
        } else {
            if ($year < 50)
                $year = '20' . $year;
            else
                $year = '19' . $year;



            $begin = $year - 59;
            $end = $year + 21;
        }

        $return .="<div class=\"form-group\"><SELECT NAME=year" . $title . " id=yearSelect" . $selectid . " class=\"form-control\" $extraY>";
        if ($allow_na) {
            if ($year == '00') {
                $return .= "<OPTION value=\"\" SELECTED>N/A";
            } else {
                $return .= "<OPTION value=\"\">N/A";
            }
        }

        for ($i = $begin; $i <= $end; $i++) {
            $return .="<OPTION VALUE=" . substr($i, 0);
            if ($year == $i) {
                $return .=" SELECTED";
            }
            $return .=">" . $i;
        }
        $return .="</SELECT></div>";
    }

    if ($options['C']) {
        if ($cal_img != 'N') {
            $return .= '<span style="padding-top:4px"><img src="assets/calendar.gif" id="trigger' . $selectid . '" style="cursor: pointer;" onmouseover=this.style.background=""; onmouseout=this.style.background=""; onClick=' . "MakeDate('" . $selectid . "',this);" . ' /></span></div>';
        }
    }
    if ($_REQUEST['_openSIS_PDF'])
        $return = ProperDate($date);
    return $return;
}

function PrepareDate_for_EndInput($date = '', $title = '', $allow_na = true, $options = '') {
    global $_openSIS;

    if ($options == '')
        $options = array();
    if (!$options['Y'] && !$options['M'] && !$options['D'] && !$options['C'])
        $options += array('Y' =>true, 'M' =>true, 'D' =>true, 'C' =>true);

    if ($options['short'] == true)
        $extraM = "style='width:60;' ";
    if ($options['submit'] == true) {
        $tmp_REQUEST['M'] = $tmp_REQUEST['D'] = $tmp_REQUEST['Y'] = $_REQUEST;
        unset($tmp_REQUEST['M']['month' . $title]);
        unset($tmp_REQUEST['D']['day' . $title]);
        unset($tmp_REQUEST['Y']['year' . $title]);
        $extraM .= "onchange='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST['M']) . "&amp;month$title=\"+this.form.month$title.value;'";
        $extraD .= "onchange='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST['D']) . "&amp;day$title=\"+this.form.day$title.value;'";
        $extraY .= "onchange='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST['Y']) . "&amp;year$title=\"+this.form.year$title.value;'";
    }

    if ($options['C'])
        $_openSIS['PrepareDate'] ++;

    if (strlen($date) == 9) { // ORACLE
        $day = substr($date, 0, 2);
        $month = substr($date, 3, 3);
        $year = substr($date, 7, 2);

        $return .= '<!-- ' . $year . MonthNWSwitch($month, 'tonum') . $day . ' -->';
    } else { // mysql
        $day = substr($date, 8, 2);
        $month = MonthNWSwitch(substr($date, 5, 2), 'tochar');
        $year = substr($date, 2, 2);

        $return .= '<!-- ' . $year . MonthNWSwitch($month, 'tonum') . $day . ' -->';
    }

    // MONTH  ---------------
    if ($options['M']) {
        $return .= "<div class=\"form-group\"><SELECT NAME=month" . $title . " id=monthSelect" . $_openSIS['PrepareDate'] . " class=\"form-control\" $extraM>";
        //  -------------------------------------------------------------------------- //

        if ($month == 'JAN')
            $month = 1;
        elseif ($month == 'FEB')
            $month = 2;
        elseif ($month == 'MAR')
            $month = 3;
        elseif ($month == 'APR')
            $month = 4;
        elseif ($month == 'MAY')
            $month = 5;
        elseif ($month == 'JUN')
            $month = 6;
        elseif ($month == 'JUL')
            $month = 7;
        elseif ($month == 'AUG')
            $month = 8;
        elseif ($month == 'SEP')
            $month = 9;
        elseif ($month == 'OCT')
            $month = 10;
        elseif ($month == 'NOV')
            $month = 11;
        elseif ($month == 'DEC')
            $month = 12;

        //  -------------------------------------------------------------------------- //
        if ($allow_na) {
            if ($month == '000')
                $return .= "<OPTION value=\"\" SELECTED>N/A";
            else
                $return .= "<OPTION value=\"\">N/A";
        }
        if ($month == '1') {
            $return .= "<OPTION VALUE=JAN SELECTED>January";
        } else {
            $return .= "<OPTION VALUE=JAN>January";
        }
        if ($month == '2') {
            $return .= "<OPTION VALUE=FEB SELECTED>February";
        } else {
            $return .= "<OPTION VALUE=FEB>February";
        }
        if ($month == '3') {
            $return .= "<OPTION VALUE=MAR SELECTED>March";
        } else {
            $return .= "<OPTION VALUE=MAR>March";
        }
        if ($month == '4') {
            $return .= "<OPTION VALUE=APR SELECTED>April";
        } else {
            $return .= "<OPTION VALUE=APR>April";
        }
        if ($month == '5') {
            $return .= "<OPTION VALUE=MAY SELECTED>May";
        } else {
            $return .= "<OPTION VALUE=MAY>May";
        }
        if ($month == '6') {
            $return .= "<OPTION VALUE=JUN SELECTED>June";
        } else {
            $return .= "<OPTION VALUE=JUN>June";
        }
        if ($month == '7') {
            $return .= "<OPTION VALUE=JUL SELECTED>July";
        } else {
            $return .= "<OPTION VALUE=JUL>July";
        }
        if ($month == '8') {
            $return .= "<OPTION VALUE=AUG SELECTED>August";
        } else {
            $return .= "<OPTION VALUE=AUG>August";
        }
        if ($month == '9') {
            $return .= "<OPTION VALUE=SEP SELECTED>September";
        } else {
            $return .= "<OPTION VALUE=SEP>September";
        }
        if ($month == '10') {
            $return .= "<OPTION VALUE=OCT SELECTED>October";
        } else {
            $return .= "<OPTION VALUE=OCT>October";
        }
        if ($month == '11') {
            $return .= "<OPTION VALUE=NOV SELECTED>November";
        } else {
            $return .= "<OPTION VALUE=NOV>November";
        }
        if ($month == '12') {
            $return .= "<OPTION VALUE=DEC SELECTED>December";
        } else {
            $return .= "<OPTION VALUE=DEC>December";
        }

        $return .= "</SELECT></div>";
    }

    // DAY  ---------------
    if ($options['D']) {
        $return .="<div class=\"form-group\"><SELECT NAME=day" . $title . " id=daySelect" . $_openSIS['PrepareDate'] . " class=\"form-control\" $extraD>";
        if ($allow_na) {
            if ($day == '00') {
                $return .= "<OPTION value=\"\" SELECTED>N/A";
            } else {
                $return .= "<OPTION value=\"\">N/A";
            }
        }

        for ($i = 1; $i <= 31; $i++) {
            if (strlen($i) == 1)
                $print = '0' . $i;
            else
                $print = $i;

            $return .="<OPTION VALUE=" . $print;
            if ($day == $print)
                $return .=" SELECTED";
            $return .=">$i ";
        }
        $return .="</SELECT></div>";
    }

    // YEAR	 ---------------
    if ($options['Y']) {
        if (!$year) {

            $begin = date('Y') - 60;
            $end = date('Y') + 20;
        } else {
            if ($year < 50)
                $year = '20' . $year;
            else
                $year = '19' . $year;



            $begin = $year - 59;
            $end = $year + 21;
        }

        $return .="<div class=\"form-group\"><SELECT NAME=year" . $title . " id=yearSelect" . $_openSIS['PrepareDate'] . " SIZE=1 $extraY>";
        if ($allow_na) {
            if ($year == '00') {
                $return .= "<OPTION value=\"\" SELECTED>N/A";
            } else {
                $return .= "<OPTION value=\"\">N/A";
            }
        }

        for ($i = $begin; $i <= $end; $i++) {
            $return .="<OPTION VALUE=" . substr($i, 0);
            if ($year == $i) {
                $return .=" SELECTED";
            }
            $return .=">" . $i;
        }
        $return .="</SELECT></div>";
    }

    if ($options['C'])
        $return .= '</div>';

    if ($_REQUEST['_openSIS_PDF'])
        $return = ProperDate($date);
    return $return;
}

function ProperTime($time) {
    return date('g:i A', strtotime($time));
}

?>