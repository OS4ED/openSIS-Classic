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
function DBDate($type='oracle')
{
	if($type=='oracle')
		return strtoupper(date('d-M-y'));
	elseif($type=='postgres')
		return date('Y-m-d');
	elseif($type=="mysql")
		return date('Y-m-d');
}
function DaySname($value,$pattern='1')
{
	$days=array('Monday'=>'M','Tuesday'=>'T','Wednesday'=>'W','Thursday'=>'H','Friday'=>'F','Saturday'=>'S','Sunday'=>'U');
        if($pattern==1)
        return $days[$value];
        else
        return array_search($value,$days);
}
function DaySnameMod($value,$pattern='1')
{
	$days=array('Monday'=>'M','Tuesday'=>'T','Wednesday'=>'W','Thursday'=>'H','Friday'=>'F','Saturday'=>'S','Sunday'=>'U');
        if(in_array($value,$days))
        {
            if($pattern==1)
            return $days[$value];
            else
            return array_search($value,$days);
        }
        else
        {
            $val_arr=str_split($value);
            $key="";
            foreach($val_arr as $val)
            {
                if (array_search($val, $days) != false) {
                    if ($checker == true) {
                            $key .= ', ';
                    }
                    $key .= (array_search($val, $days));
                    $checker = true;
                }
            }
            //$key_arr=str_split($key);
            return $key; 
        }
}
function MonthFormatter($value,$pattern='1')
{
    
	$days=array('JAN'=>'01','FEB'=>'02','MAR'=>'03','APR'=>'04','MAY'=>'05','JUN'=>'06','JUL'=>'07','AUG'=>'08','SEP'=>'09','OCT'=>'10','NOV'=>'11','DEC'=>'12');
        if($pattern==1)
        {
        return $days[$value];
        }
        else
        {
        return array_search($value,$days);
        }
}

?>
