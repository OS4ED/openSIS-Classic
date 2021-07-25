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
include('../../RedirectModulesInc.php');
include_once('functions/MiscExportFnc.php');
$extra['search'] .= '<TR><TD align=center colspan=2><TABLE><TR><TD><DIV id=fields_div></DIV></TD></TR></TABLE></TD></TR>';
$extra['new'] = true;
$_openSIS['CustomFields'] = true;
if($_REQUEST['ADDRESS_ID'] || $_REQUEST['fields']['ADDRESS'] || $_REQUEST['fields']['CITY'] || $_REQUEST['fields']['STATE'] || $_REQUEST['fields']['ZIPCODE'] || $_REQUEST['fields']['PHONE'] || $_REQUEST['fields']['MAIL_ADDRESS'] || $_REQUEST['fields']['MAIL_CITY'] || $_REQUEST['fields']['MAIL_STATE'] || $_REQUEST['fields']['MAIL_ZIPCODE'] || $_REQUEST['fields']['PARENTS'])
{
	$extra['SELECT'] .= ',a.ID AS ADDRESS_ID,a.ADDRESS,a.CITY,a.STATE,a.ZIPCODE,a.PHONE,a.ADDRESS AS MAIL_ADDRESS,a.CITY AS MAIL_CITY,a.STATE AS MAIL_STATE,a.ZIPCODE AS MAIL_ZIPCODE';
	$extra['addr'] = true;
	if($_REQUEST['fields']['MAIL_ADDRESS'])
        {
		$extra['students_join_address'] .= " AND a.TYPE='Mail'";
        }
}
if($_REQUEST['fields']['PARENTS'])
{
	$extra['SELECT'] .= ',ssm.STUDENT_ID AS PARENTS';
	$view_other_RET['ALL_CONTACTS'][1]['VALUE']='Y';
	if($_REQUEST['relation']!='')
	{
		$_openSIS['makeParents'] = $_REQUEST['relation'];
		$extra['students_join_address'] .= ' AND EXISTS (SELECT \'\' FROM students_join_people sjp WHERE sjp.STUDENT_ID=a.STUDENT_ID AND LOWER(sjp.REALTIONSHIP) LIKE \''.strtolower($_REQUEST['relation']).'%\') ';
	}
}
$extra['SELECT'] .= ',ssm.NEXT_SCHOOL,ssm.CALENDAR_ID,ssm.SYEAR,s.*';
if($_REQUEST['fields']['FIRST_INIT'])
	$extra['SELECT'] .= ',substr(s.FIRST_NAME,1,1) AS FIRST_INIT';

if(!$extra['functions'])
	$extra['functions'] = array('NEXT_SCHOOL'=>'_makeNextSchool','CALENDAR_ID'=>'_makeCalendar','SCHOOL_ID'=>'GetSchool','PARENTS'=>'makeParents');

if($_REQUEST['search_modfunc']=='list')
{
	if(!$fields_list)
	{
		$fields_list = array('FULL_NAME'=>(Preferences('NAME')=='Common'?_lastCommon:_lastFirstM),'FIRST_NAME'=>'First','FIRST_INIT'=>'First Initial','LAST_NAME'=>'Last','MIDDLE_NAME'=>'Middle','NAME_SUFFIX'=>'Suffix','STUDENT_ID'=>'Student ID','GRADE_ID'=>'Grade','SCHOOL_ID'=>'School','NEXT_SCHOOL'=>'Rolling / Retention Options','CALENDAR_ID'=>'Calendar','USERNAME'=>'Username','PASSWORD'=>'Password','ADDRESS'=>'Address','CITY'=>'City','STATE'=>'State','ZIPCODE'=>'Zip Code','PHONE'=>'Home Phone','MAIL_ADDRESS'=>'Mailing Address','MAIL_CITY'=>'Mailing City','MAIL_STATE'=>'Mailing State','MAIL_ZIPCODE'=>'Mailing Zipcode','PARENTS'=>'Contacts');
		if($extra['field_names'])
			$fields_list += $extra['field_names'];

		$periods_RET = DBGet(DBQuery('SELECT TITLE,PERIOD_ID FROM school_periods WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY SORT_ORDER'));
		foreach($periods_RET as $period)
			$fields_list['PERIOD_'.$period['PERIOD_ID']] = $period['TITLE'].' Teacher - Room';
	}

	$custom_RET = DBGet(DBQuery('SELECT TITLE,ID,TYPE FROM custom_fields ORDER BY SORT_ORDER'));

	foreach($custom_RET as $field)
	{
		if(!$fields_list['CUSTOM_'.$field['ID']])
			$fields_list['CUSTOM_'.$field['ID']] = $field['TITLE'];
	}



	foreach($periods_RET as $period)
	{
		if($_REQUEST['month_include_active_date'])
			$date = $_REQUEST['day_include_active_date'].'-'.$_REQUEST['month_include_active_date'].'-'.$_REQUEST['year_include_active_date'];
		else
			$date = DBDate();

		if($_REQUEST['fields']['PERIOD_'.$period['PERIOD_ID']]=='Y')
			$extra['SELECT'] .= ',(SELECT CONCAT(COALESCE(st.FIRST_NAME,\' \'),\' \',COALESCE(st.LAST_NAME,\' \'),\' - \',COALESCE(cp.ROOM,\' \')) FROM staff st,schedule ss,course_periods cp WHERE ss.STUDENT_ID=ssm.STUDENT_ID AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID AND cp.TEACHER_ID=st.STAFF_ID AND cp.PERIOD_ID=\''.$period['PERIOD_ID'].'\' AND (\''.$date.'\' BETWEEN ss.START_DATE AND ss.END_DATE OR \''.$date.'\'>=ss.START_DATE AND ss.END_DATE IS NULL)) AS PERIOD_'.$period['PERIOD_ID'];
	}

	

	if($_REQUEST['fields'])
	{
		foreach($_REQUEST['fields'] as $field=>$on)
		{
			$columns[$field] = $fields_list[$field];
			if($custom_RET[substr($field,7)][1]['TYPE']=='date' && !$extra['functions'][$field])
				$extra['functions'][$field] = 'ProperDate';
			elseif($custom_RET[substr($field,7)][1]['TYPE']=='codeds' && !$extra['functions'][$field])
				$extra['functions'][$field] = 'DeCodeds';
		}
		$RET = GetStuList($extra);
		if($extra['array_function'] && function_exists($extra['array_function']))
			$extra['array_function']($RET);
		echo "<html><link rel='stylesheet' type='text/css' href='styles/Export.css'><body style=\" font-family:Arial; font-size:12px;\">";
	ListOutputCustom($RET,$columns,$extra['singular']?$extra['singular']:student,$extra['plural']?$extra['plural']:students,array(),$extra['LO_group'],$extra['LO_options']);
		echo "</body></html>";
	}
}
else
{
	if(!$fields_list)
	{
		if(AllowUse('students/Student.php&category_id=1'))
			$fields_list['General'] = array('FULL_NAME'=>(Preferences('NAME')=='Common'?_lastCommon:_lastFirstM),'FIRST_NAME'=>'First','FIRST_INIT'=>'First Initial','LAST_NAME'=>'Last','MIDDLE_NAME'=>'Middle','NAME_SUFFIX'=>'Suffix','STUDENT_ID'=>'Student ID','GRADE_ID'=>'Grade','SCHOOL_ID'=>'School','NEXT_SCHOOL'=>'Rolling / Retention Options','CALENDAR_ID'=>'Calendar','USERNAME'=>'Username','PASSWORD'=>'Password');
		if(AllowUse('students/Student.php&category_id=3'))
		{
			$fields_list['Address'] = array('ADDRESS'=>'Address','CITY'=>'City','STATE'=>'State','ZIPCODE'=>'Zip Code','PHONE'=>'Home Phone','MAIL_ADDRESS'=>'Mailing Address','MAIL_CITY'=>'Mailing City','MAIL_STATE'=>'Mailing State','MAIL_ZIPCODE'=>'Mailing Zipcode','PARENTS'=>'Contacts');

		}
		if($extra['field_names'])
			$fields_list['General'] += $extra['field_names'];
	}

	$categories_RET = DBGet(DBQuery('SELECT ID,TITLE FROM student_field_categories ORDER BY SORT_ORDER'));
	$custom_RET = DBGet(DBQuery('SELECT TITLE,ID,TYPE,CATEGORY_ID FROM custom_fields ORDER BY SORT_ORDER'),array(),array('CATEGORY_ID'));

	foreach($categories_RET as $category)
	{
		if(AllowUse('students/Student.php&category_id='.$category['ID']))
		{
			foreach($custom_RET[$category['ID']] as $field)
				$fields_list[$category['TITLE']]['CUSTOM_'.$field['ID']] = str_replace("'",'&#39;',$field['TITLE']);
		}
	}

	$periods_RET = DBGet(DBQuery('SELECT TITLE,PERIOD_ID FROM school_periods WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' ORDER BY SORT_ORDER'));
	foreach($periods_RET as $period)
		$fields_list['Schedule']['PERIOD_'.$period['PERIOD_ID']] = $period['TITLE'].' Teacher - Room';

	

	DrawHeader('<OL><SPAN id=names_div></SPAN></OL>');
	echo '<TABLE><TR><TD valign=top>';
	DrawHeader("<div><a class=big_font><img src=\"themes/blue/expanded_view.png\" />Fields</a></div><div class=break ></div>",$extra['header_right']);
	PopTable_wo_header('header');
	echo '<TABLE><TR>';
	foreach($fields_list as $category=>$fields)
	{
		echo '<TD colspan=2 class=break_headers ><b>'.$category.'<BR></b></TD></TR><TR>';
		foreach($fields as $field=>$title)
		{
			$i++;
			echo '<TD><INPUT type=checkbox onclick="addHTML(\'<LI>'.$title.'</LI>\',\'names_div\',false);addHTML(\'<INPUT type=hidden name=fields['.$field.'] value=Y>\',\'fields_div\',false);this.disabled=true">'.$title.($field=='PARENTS'?'<BR>(<small>Relation: </small><input type=text id=relation name=relation size=8>)':'').'</TD>';
			if($i%2==0)
				echo '</TR><TR>';
		}
		if($i%2!=0)
		{
			echo '<TD></TD></TR><TR>';
			$i++;
		}
	}
	echo '</TR></TABLE>';
	PopTable('footer');
	echo '</TD><TD valign=top>';
	if($Search && function_exists($Search))
	    	$Search($extra);
	else
		Search('student_id',$extra);
	echo '</TD></TR></TABLE>';
}
?>
