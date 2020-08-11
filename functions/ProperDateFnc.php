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

/*
Outputs a pretty date when sent an oracle or postgres date.
*/
function ProperDate($date='',$length='long')
{
	$months_number['JAN'] = '1';
	$months_number['FEB'] = '2';
	$months_number['MAR'] = '3';
	$months_number['APR'] = '4';
	$months_number['MAY'] = '5';
	$months_number['JUN'] = '6';
	$months_number['JUL'] = '7';
	$months_number['AUG'] = '8';
	$months_number['SEP'] = '9';
	$months_number['OCT'] = '10';
	$months_number['NOV'] = '11';
	$months_number['DEC'] = '12';
			if($date && strlen($date)==9)
	{
		$year = substr($date,7);
		$month = $months_number[strtoupper(substr($date,3,3))];
		$day = substr($date,0,2)*1;
		$comment = '<!-- '.(($year<50)?20:19).$year.MonthNWSwitch(substr($date,3,3),'tonum').(substr($date,0,2)).' -->';
	}
	elseif($date)
	{
		$year = substr($date,0,4);
		$month = substr($date,5,2)*1;
		$day = substr($date,8)*1;
		$comment = '<!-- '.$year.substr($date,5,2).(substr($date,8)).' -->';
	}
	
	
		$sep = '/';

	
	if($date)
		return date((($length=='long' || Preferences('MONTH')!='F')?Preferences('MONTH'):'M').$sep.Preferences('DAY').$sep.Preferences('YEAR'),mktime(0,0,0,$month,$day,$year));

}

function ShortDate($date='',$column='')
{
	return ProperDate($date,'short');
}
function ProperDateAY($date='',$length='long')
{
    
	$m['01']='Jan';
	$m['02']='Feb';
	$m['03']='Mar';
	$m['04']='Apr';
	$m['05']='May';
	$m['06']='Jun';
	$m['07']='Jul';
	$m['08']='Aug';
	$m['09']='Sep';
	$m['10']='Oct';
	$m['11']='Nov';
	$m['12']='Dec';
	
        $break_date=explode("-",$date);
        if($date=='')
            return '-';
        else
        {
        $sep = '/';
//        return $m[$break_date[1]].'/'.$break_date[2].'/'.$break_date[0];
        return mkSisDate($break_date[0],$break_date[1],$break_date[2]);
	
}
	
}
function ProperDateMAvr($date='')
{
    if($date!='')
    {
    $month_array=array("jan"=>"01","feb"=>"02","mar"=>"03","apr"=>"04","may"=>"05","jun"=>"06","jul"=>"07","aug"=>"08","sep"=>"09","oct"=>"10","nov"=>"11","dec"=>"12");
    $date=explode("-",$date);
    if(is_numeric($date[1])){
        $date=$date[0].'-'.$date[1].'-'.$date[2];
    }
    else
    {
    $date=$date[0].'-'.$month_array[strtolower($date[1])].'-'.$date[2];
    }
    
    return $date;
    }
    else
    return date('Y-m-d');    
}
function mkSisDate($year,$month,$date)
{
    
        $monthFormat=date(Preferences('MONTH'),strtotime($year.'-'.$month.'-'.$date));
        $dayFormat=date(Preferences('DAY'),strtotime($year.'-'.$month.'-'.$date));
        $yearFormat=date(Preferences('YEAR'),strtotime($year.'-'.$month.'-'.$date));
        return $monthFormat.'/'.$dayFormat.'/'.$yearFormat;
   // return date((($length=='long' || Preferences('MONTH')!='F')?Preferences('MONTH'):'M').$sep.Preferences('DAY').$sep.Preferences('YEAR'),mktime(0,0,0,$m[$break_date[1]],$break_date[2],$break_date[0]));
}
?>