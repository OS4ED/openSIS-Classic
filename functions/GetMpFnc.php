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
function GetMP($mp='',$column='TITLE')
{	global $_openSIS;
	// mab - need to translate marking_period_id to title to be useful as a function call from dbget
	// also, it doesn't make sense to ask for same thing you give
	if($column=='MARKING_PERIOD_ID')
		$column='TITLE';

	if(!$_openSIS['GetMP'])
	{
           
		$_openSIS['GetMP'] = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_quarters\' AS `TABLE`,\'SEMESTER_ID\' AS `PA_ID`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_quarters         WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\'
					UNION      SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_semesters\' AS `TABLE`,\'YEAR_ID\' AS `PA_ID`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_semesters        WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\'
					UNION      SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_years\' AS `TABLE`, \'-1\' AS `PA_ID`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_years            WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\'
					UNION      SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_progress_periods\' AS `TABLE`, \'-1\' AS `PA_ID`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_progress_periods WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''),array(),array('MARKING_PERIOD_ID'));

        }
        
	if(substr($mp,0,1)=='E')
	{
		if($column=='TITLE' || $column=='SHORT_NAME')
			$suffix = ' Exam';
		$mp = substr($mp,1);
	}
if($mp=='')
{
    return 'Custom';
}
 else {
   
  if($mp==0 && $column=='TITLE')
      
		return 'Full Year'.$suffix;
	else
        {
		return $_openSIS['GetMP'][$mp][1][$column].$suffix;  
        }
}
	
}

function GetMPAllSchool($mp,$column='TITLE')
{	global $_openSIS;

	// mab - need to translate marking_period_id to title to be useful as a function call from dbget
	// also, it doesn't make sense to ask for same thing you give
	if($column=='MARKING_PERIOD_ID')
		$column='TITLE';

	if(!$_openSIS['GetMP'])
	{
		$_openSIS['GetMP'] = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_quarters\'        AS `TABLE`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_quarters         WHERE SYEAR=\''.UserSyear().'\' 
					UNION      SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_semesters\'       AS `TABLE`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_semesters        WHERE SYEAR=\''.UserSyear().'\' 
					UNION      SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_years\'           AS `TABLE`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_years            WHERE SYEAR=\''.UserSyear().'\' 
					UNION      SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_progress_periods\' AS `TABLE`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_progress_periods WHERE SYEAR=\''.UserSyear().'\''),array(),array('MARKING_PERIOD_ID'));
	}
	if(substr($mp,0,1)=='E')
	{
		if($column=='TITLE' || $column=='SHORT_NAME')
			$suffix = ' Exam';
		$mp = substr($mp,1);
	}
        
	if($mp==0 && $column=='TITLE')
		return 'Full Year'.$suffix;
	else
		return $_openSIS['GetMP'][$mp][1][$column].$suffix;
}
function GetMP_teacherschedule($mp,$column='TITLE')
{	global $_openSIS;

	// mab - need to translate marking_period_id to title to be useful as a function call from dbget
	// also, it doesn't make sense to ask for same thing you give
	if($column=='MARKING_PERIOD_ID')
		$column='TITLE';

	if(!$_openSIS['GetMP'])
	{
		$_openSIS['GetMP'] = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_quarters\'        AS `TABLE`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_quarters         WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\'
					UNION      SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_semesters\'       AS `TABLE`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_semesters        WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\'
					UNION      SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_years\'           AS `TABLE`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_years            WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\'
					UNION      SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_progress_periods\' AS `TABLE`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_progress_periods WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''),array(),array('MARKING_PERIOD_ID'));
	}
	if(substr($mp,0,1)=='E')
	{
            
		if($column=='TITLE' || $column=='SHORT_NAME')
			$suffix = ' Exam';
		$mp = substr($mp,1);
	}
        if($mp=='Custom' && $column=='TITLE')
        {
            return $mp;
        }
	if($mp==0 && $column=='TITLE')
        {
		return 'Full Year'.$suffix;
        }
        
	else  
		return $_openSIS['GetMP'][$mp][1][$column].$suffix;

}
function GetMPTable($mp_table)
{
	switch($mp_table)
	{
		case 'school_years':
			return 'FY';
		break;
		case 'school_semesters':
			return 'SEM';
		break;
		case 'school_quarters':
			return 'QTR';
		break;
		case 'school_progress_periods':
			return 'PRO';
		break;
		default:
			return 'FY';
		break;
	}
}
function GetCurrentMP($mp,$date,$error=true)
{	global $_openSIS;

	switch($mp)
	{
		case 'FY':
			$table = 'school_years';
		break;

		case 'SEM':
			$table = 'school_semesters';
		break;

		case 'QTR':
			$table = 'school_quarters';
		break;

		case 'PRO':
			$table = 'school_progress_periods';
		break;
	}

	if(!$_openSIS['GetCurrentMP'][$date][$mp])
	 	$_openSIS['GetCurrentMP'][$date][$mp] = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM '.$table.' WHERE \''.$date.'\' BETWEEN START_DATE AND END_DATE AND SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));

	if($_openSIS['GetCurrentMP'][$date][$mp][1]['MARKING_PERIOD_ID'])
		return $_openSIS['GetCurrentMP'][$date][$mp][1]['MARKING_PERIOD_ID'];
	elseif(strpos($_SERVER['PHP_SELF'],'Side.php')===false && $error==true)
		ErrorMessage(array(_youAreNotCurrentlyInAMarkingPeriod));
		
}
function GetMPId($mp)
{	

	switch($mp)
	{
		case 'FY':
			$table = 'school_years';
		break;

		case 'SEM':
			$table = 'school_semesters';
		break;

		case 'QTR':
			$table = 'school_quarters';
		break;

		case 'PRO':
			$table = 'school_progress_periods';
		break;
	}

	$get_mp_id=DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM '.$table.' WHERE SCHOOL_ID='.UserSchool().' AND SYEAR='.UserSyear()));
        if($get_mp_id[1]['MARKING_PERIOD_ID']!='')
            return $get_mp_id[1]['MARKING_PERIOD_ID'];
        else
           return UserMP ();
}

function check_exam($mp)
{
    $qr=  DBGet(DBQuery('select * from marking_periods where marking_period_id=\''.$mp.'\''));

    return $qr[1]['DOES_EXAM'];
}
function ParentMP($mp)
{
     $qr=  DBGet(DBQuery('select * from marking_periods where marking_period_id=\''.$mp.'\''));

    return $qr[1]['PARENT_ID'];
}
?>