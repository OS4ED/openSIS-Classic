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

function VerifyDate_sort($date)
{
    if (strpos(strip_tags($date), '/') !== false) 
    {
    	$vdate = explode("/", $date);
    	if(count($vdate))
    	{
            $month = MonthNWSwitch($vdate[0],'tonum');
            $day = $vdate[1];
            $year = $vdate[2];
    	}
    	else
        {
            return false;
        }
    }
    else
        return false;

	return checkdate($month,$day,$year);
}

function date_to_timestamp($date)
{
    $newarr=array();
    foreach ($date as $dt)
    {
        $arr_date=explode('/', $dt);
        $month=MonthNWSwitch($arr_date[0],'tonum');
        $day = $arr_date[1];
        $year = $arr_date[2];
        array_push($newarr, mktime(0,0,0,$month,$day,$year));
    }
    return $newarr;
}

function point_to_number($point)
{
    $newarr=array();
    foreach ($point as $value)
    {
        $value=strip_tags($value);
        $rank_arr=explode(' / ', $value);

        array_push($newarr,$rank_arr[0]);
    }
    return $newarr;
}

function percent_to_number($percent)
{
    $newarr=array();
    foreach ($percent as $value)
    {
        $value=strip_tags($value);
        $rank_arr=explode('%', $value);

        array_push($newarr,$rank_arr[0]);
    }
    return $newarr;
}

function range_to_number($range)
{
    $newarr=array();
    foreach ($range as $value)
    {
        $rank_arr=explode(' - ', $value);

        array_push($newarr,$rank_arr[0]);
    }
    return $newarr;
}

function rank_to_number($rank)
{
    $newarr=array();
    foreach ($rank as $value)
    {
        $rank_arr=explode(' out of ', $value);

        array_push($newarr,$rank_arr[0]);
    }
    return $newarr;
}
?>
