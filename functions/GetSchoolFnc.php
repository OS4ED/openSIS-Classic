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
function GetSchool($sch)
{	global $_openSIS;
		if(!$_openSIS['GetSchool'])
	{
		$QI=DBQuery('SELECT ID,TITLE FROM schools');
		$_openSIS['GetSchool'] = DBGet($QI,array(),array('ID'));
	}

	if($_openSIS['GetSchool'][$sch])
		return $_openSIS['GetSchool'][$sch][1]['TITLE'];
	else
		return $sch;
}
function GetUserSchools($staff_id,$str=false)
{
      if(User('PROFILE_ID')!=4 && User('PROFILE')!='parent')
      {
        $str_return='';
        $schools=DBGet(DBQuery('SELECT SCHOOL_ID FROM staff_school_relationship WHERE staff_id='.$staff_id.' AND syear='.  UserSyear()));
        foreach($schools as $school)
        {
            $return[]=$school['SCHOOL_ID'];
            $str_return .=$school['SCHOOL_ID'].',';
        }
        if($str==true)
        {
            return substr($str_return,0,-1);
        }
        else
        {
            return $return;
        }
      }
      else if (User('PROFILE_ID')==4 || User('PROFILE')=='parent')
      {
          $schools=DBGet(DBQuery('SELECT SCHOOL_ID FROM student_enrollment WHERE STUDENT_ID='.UserStudentID().' AND SYEAR='.UserSyear().' ORDER BY ID DESC LIMIT 0,1'));
          return $schools[1]['SCHOOL_ID'];
      }
}

function GetSchoolInfo($sch)
{	global $_openSIS;
		if(!$_openSIS['GetSchoolInfo'])
	{
		$QI=DBQuery('SELECT * FROM schools');
		$_openSIS['GetSchoolInfo'] = DBGet($QI,array(),array('ID'));
	}
	if($_openSIS['GetSchoolInfo'][$sch])
		return 'Address :'.$_openSIS['GetSchoolInfo'][$sch][1]['ADDRESS'].','.$_openSIS['GetSchoolInfo'][$sch][1]['CITY'].','.$_openSIS['GetSchoolInfo'][$sch][1]['STATE'].','.$_openSIS['GetSchoolInfo'][$sch][1]['ZIPCODE']. ($_openSIS['GetSchoolInfo'][$sch][1]['PHONE']!=NULL ? ' <p> Phone :'.$_openSIS['GetSchoolInfo'][$sch][1]['PHONE'].'</p>' : '');
                 
	else
		return $sch;
}


?>
