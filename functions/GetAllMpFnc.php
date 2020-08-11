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
function GetAllMP($mp,$marking_period_id='0')
{	global $_openSIS;
	if($marking_period_id==0)
	{
		// there should be exactly one fy marking period
		$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
		$marking_period_id = $RET[1]['MARKING_PERIOD_ID'];
		$mp = 'FY';
	}
	elseif(!$mp) 
		 $mp = GetMPTable(GetMP($marking_period_id,'TABLE'));
             
	if(!$_openSIS['GetAllMP'][$mp])
	{
		switch($mp)
		{
			case 'PRO':
				// there should be exactly one fy marking period
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				$fy = $RET[1]['MARKING_PERIOD_ID'];

				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				foreach($RET as $value)
				{
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = "'$fy','$value[SEMESTER_ID]','$value[MARKING_PERIOD_ID]'";
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] .= ','.GetChildrenMP($mp,$value['MARKING_PERIOD_ID']);
					if(substr($_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']],-1)==',')
						$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = substr($_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']],0,-1);
				}
			break;

			case 'QTR':
				// there should be exactly one fy marking period
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				$fy = $RET[1]['MARKING_PERIOD_ID'];

				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				foreach($RET as $value)
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = "'$fy','$value[SEMESTER_ID]','$value[MARKING_PERIOD_ID]'";
			break;

			case 'SEM':
				// there should be exactly one fy marking period
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				$fy = $RET[1]['MARKING_PERIOD_ID'];

				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''),array(),array('SEMESTER_ID'));
				foreach($RET as $sem=>$value)
				{
					$_openSIS['GetAllMP'][$mp][$sem] = "'$fy','$sem'";
					foreach($value as $qtr)
						$_openSIS['GetAllMP'][$mp][$sem] .= ",'$qtr[MARKING_PERIOD_ID]'";
				}
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_semesters s WHERE NOT EXISTS (SELECT \'\' FROM school_quarters q WHERE q.SEMESTER_ID=s.MARKING_PERIOD_ID) AND SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				foreach($RET as $value)
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = "'$fy','$value[MARKING_PERIOD_ID]'";
			break;

			case 'FY':
				// there should be exactly one fy marking period which better be $marking_period_id
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''),array(),array('SEMESTER_ID'));
				$_openSIS['GetAllMP'][$mp][$marking_period_id] = "'$marking_period_id'";
				foreach($RET as $sem=>$value)
				{
					$_openSIS['GetAllMP'][$mp][$marking_period_id] .= ",'$sem'";
					foreach($value as $qtr)
						$_openSIS['GetAllMP'][$mp][$marking_period_id] .= ",'$qtr[MARKING_PERIOD_ID]'";
				}
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_semesters s WHERE NOT EXISTS (SELECT \'\' FROM school_quarters q WHERE q.SEMESTER_ID=s.MARKING_PERIOD_ID) AND SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				foreach($RET as $value)
					$_openSIS['GetAllMP'][$mp][$marking_period_id] .= ",'$value[MARKING_PERIOD_ID]'";
			break;
                        
		}
	}

	return $_openSIS['GetAllMP'][$mp][$marking_period_id];
}

function GetAllMP_Mod($mp,$marking_period_id='0')
{	global $_openSIS;

	if($marking_period_id==0)
	{
		// there should be exactly one fy marking period
		$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
		$marking_period_id = $RET[1]['MARKING_PERIOD_ID'];
		$mp = 'FY';
	}
	elseif(!$mp) 
		 $mp = GetMPTable(GetMP($marking_period_id,'TABLE'));
        
     
	if(!$_openSIS['GetAllMP'][$mp])
	{
		switch($mp)
		{
			case 'PRO':
				// there should be exactly one fy marking period
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				$fy = $RET[1]['MARKING_PERIOD_ID'];

				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				foreach($RET as $value)
				{
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = "'$fy','$value[SEMESTER_ID]','$value[MARKING_PERIOD_ID]'";
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] .= ','.GetChildrenMP($mp,$value['MARKING_PERIOD_ID']);
					if(substr($_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']],-1)==',')
						$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = substr($_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']],0,-1);
				}
			break;

			case 'QTR':
				// there should be exactly one fy marking period
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				$fy = $RET[1]['MARKING_PERIOD_ID'];

				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				foreach($RET as $value)
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = "'$fy','$value[SEMESTER_ID]','$value[MARKING_PERIOD_ID]'";
			break;

			case 'SEM':
				// there should be exactly one fy marking period
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				$fy = $RET[1]['MARKING_PERIOD_ID'];

				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''),array(),array('SEMESTER_ID'));
				foreach($RET as $sem=>$value)
				{
					$_openSIS['GetAllMP'][$mp][$sem] = "'$fy','$sem'";

				}
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_semesters s WHERE NOT EXISTS (SELECT \'\' FROM school_quarters q WHERE q.SEMESTER_ID=s.MARKING_PERIOD_ID) AND SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				foreach($RET as $value)
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = "'$fy','$value[MARKING_PERIOD_ID]'";
			break;

			case 'FY':
				// there should be exactly one fy marking period which better be $marking_period_id
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''),array(),array('SEMESTER_ID'));
				$_openSIS['GetAllMP'][$mp][$marking_period_id] = "'$marking_period_id'";
				foreach($RET as $sem=>$value)
				{
//					$_openSIS['GetAllMP'][$mp][$marking_period_id] .= ",'$sem'";
//					foreach($value as $qtr)
//						$_openSIS['GetAllMP'][$mp][$marking_period_id] .= ",'$qtr[MARKING_PERIOD_ID]'";
				}
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_semesters s WHERE NOT EXISTS (SELECT \'\' FROM school_quarters q WHERE q.SEMESTER_ID=s.MARKING_PERIOD_ID) AND SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
//				foreach($RET as $value)
//					$_openSIS['GetAllMP'][$mp][$marking_period_id] .= ",'$value[MARKING_PERIOD_ID]'";
			break;
                        
		}
	}

	return $_openSIS['GetAllMP'][$mp][$marking_period_id];
}
function GetParentMP($mp,$marking_period_id='0')
{	global $_openSIS;

	if(!$_openSIS['GetParentMP'][$mp])
	{
		switch($mp)
		{
			case 'QTR':

			break;

			case 'SEM':
				$_openSIS['GetParentMP'][$mp] = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID AS PARENT_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''),array(),array('MARKING_PERIOD_ID'));
			break;

			case 'FY':
				$_openSIS['GetParentMP'][$mp] = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,YEAR_ID AS PARENT_ID FROM school_semesters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''),array(),array('MARKING_PERIOD_ID'));
			break;
		}
	}

	return $_openSIS['GetParentMP'][$mp][$marking_period_id][1]['PARENT_ID'];
}

function GetChildrenMP($mp,$marking_period_id='0')
{	global $_openSIS;

	switch($mp)
	{
		case 'FY':
			if(!$_openSIS['GetChildrenMP']['FY'])
			{
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''),array(),array('SEMESTER_ID'));
				foreach($RET as $sem=>$value)
				{
					$_openSIS['GetChildrenMP'][$mp]['0'] .= ",'$sem'";
					foreach($value as $qtr)
						$_openSIS['GetChildrenMP'][$mp]['0'] .= ",'$qtr[MARKING_PERIOD_ID]'";
				}
				$_openSIS['GetChildrenMP'][$mp]['0'] = substr($_openSIS['GetChildrenMP'][$mp]['0'],1);
			}
			return $_openSIS['GetChildrenMP'][$mp]['0'];
		break;

		case 'SEM':
			if(GetMP($marking_period_id,'TABLE')=='school_quarters')
				$marking_period_id = GetParentMP('SEM',$marking_period_id);
			if(!$_openSIS['GetChildrenMP']['SEM'])
			{
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''),array(),array('SEMESTER_ID'));
				foreach($RET as $sem=>$value)
				{
					foreach($value as $qtr)
						$_openSIS['GetChildrenMP'][$mp][$sem] .= ",'$qtr[MARKING_PERIOD_ID]'";
					$_openSIS['GetChildrenMP'][$mp][$sem] = substr($_openSIS['GetChildrenMP'][$mp][$sem],1);
				}
			}
			return $_openSIS['GetChildrenMP'][$mp][$marking_period_id];
		break;

		case 'QTR':
			return "".$marking_period_id."";
		break;

		case 'PRO':
			if(!$_openSIS['GetChildrenMP']['PRO'])
			{
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,QUARTER_ID FROM school_progress_periods WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''),array(),array('QUARTER_ID'));
				foreach($RET as $qtr=>$value)
				{
					foreach($value as $pro)
						$_openSIS['GetChildrenMP'][$mp][$qtr] .= ",'$pro[MARKING_PERIOD_ID]'";
					$_openSIS['GetChildrenMP'][$mp][$qtr] = substr($_openSIS['GetChildrenMP'][$mp][$qtr],1);
				}
			}
			return $_openSIS['GetChildrenMP'][$mp][$marking_period_id];
		break;
	}
}
function GetMPChildren($mp,$marking_period_id='0')
{	global $_openSIS;

	switch($mp)
	{
		case 'year':
			if(!$_openSIS['GetChildrenMP']['FY'])
			{
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''),array(),array('SEMESTER_ID'));
				foreach($RET as $sem=>$value)
				{
					$_openSIS['GetChildrenMP'][$mp]['0'] .= ",'$sem'";
					foreach($value as $qtr)
						$_openSIS['GetChildrenMP'][$mp]['0'] .= ",'$qtr[MARKING_PERIOD_ID]'";
				}
				$_openSIS['GetChildrenMP'][$mp]['0'] = substr($_openSIS['GetChildrenMP'][$mp]['0'],1);
                                if($_openSIS['GetChildrenMP'][$mp]['0']!='')
                                    $_openSIS['GetChildrenMP'][$mp]['0']=$_openSIS['GetChildrenMP'][$mp]['0'].','.$marking_period_id;
                                else
                                    $_openSIS['GetChildrenMP'][$mp]['0']=$marking_period_id;
			}
			return $_openSIS['GetChildrenMP'][$mp]['0'];
		break;

		case 'semester':
			if(GetMP($marking_period_id,'TABLE')=='school_quarters')
				$marking_period_id = GetParentMP('SEM',$marking_period_id);
			if(!$_openSIS['GetChildrenMP']['SEM'])
			{
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''),array(),array('SEMESTER_ID'));
				foreach($RET as $sem=>$value)
				{
					foreach($value as $qtr)
						$_openSIS['GetChildrenMP'][$mp][$sem] .= ",'$qtr[MARKING_PERIOD_ID]'";
					$_openSIS['GetChildrenMP'][$mp][$sem] = substr($_openSIS['GetChildrenMP'][$mp][$sem],1);
				}
                                if($_openSIS['GetChildrenMP'][$mp][$marking_period_id]!='')
                                    $_openSIS['GetChildrenMP'][$mp][$marking_period_id]=$_openSIS['GetChildrenMP'][$mp][$marking_period_id].','.$marking_period_id;
                                else
                                    $_openSIS['GetChildrenMP'][$mp][$marking_period_id]=$marking_period_id;
			}
                        
			return $_openSIS['GetChildrenMP'][$mp][$marking_period_id];
		break;

		case 'quarter':
			return "".$marking_period_id."";
		break;

        }
}
?>
