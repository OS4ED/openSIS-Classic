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
if(clean_param($_REQUEST['modfunc'],PARAM_ALPHAMOD)=='save' && AllowEdit())
{
	$current_RET = DBGet(DBQuery('SELECT STUDENT_ID FROM students_join_users WHERE STAFF_ID=\''.UserStaffID().'\''),array(),array('STUDENT_ID'));
	foreach($_REQUEST['student'] as $student_id=>$yes)
	{
		if(!$current_RET[$student_id]&& UserStaffID()!='')
		{
			$sql = 'INSERT INTO students_join_users (STUDENT_ID,STAFF_ID) values(\''.$student_id.'\',\''.UserStaffID().'\')';
			DBQuery($sql);
		}
	}
	unset($_REQUEST['modfunc']);
                  unset($_SESSION['_REQUEST_vars']['modfunc']);
	$note = "The selected user's profile now includes access to the selected students.";
}
DrawBC(""._users." > ".ProgramTitle());



if(isset($_REQUEST['staff_id']) && $_REQUEST['staff_id']!='new' || (UserStaffID()))
{
            if($_REQUEST['staff_id'])
                $staff_id=$_REQUEST['staff_id'];
            else
                $staff_id=UserStaffID ();
            $RET = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME FROM staff WHERE STAFF_ID=\''.$staff_id.'\''));
            $count_staff_RET=DBGet(DBQuery('SELECT COUNT(*) AS NUM FROM staff'));
            if($count_staff_RET[1]['NUM']>1){
                DrawHeaderHome( ''._selectedUser.': '.$RET[1]['FIRST_NAME'].'&nbsp;'.$RET[1]['LAST_NAME'].' (<A HREF=Side.php?staff_id=new&modcat='.$_REQUEST['modcat'].'><font color=red>'._selectedUser.':</font></A>) | <A HREF=Modules.php?modname='.$_REQUEST['modname'].'&search_modfunc=list&next_modname=users/User.php&ajax=true&bottom_back=true&return_session=true target=body>Back to User List</A>');
            }else{
                DrawHeaderHome( ''._selectedUser.': '.$RET[1]['FIRST_NAME'].'&nbsp;'.$RET[1]['LAST_NAME'].' (<A HREF=Side.php?staff_id=new&modcat='.$_REQUEST['modcat'].'><font color=red>'._selectedUser.':</font></A>)');
            }
}


if(clean_param($_REQUEST['modfunc'],PARAM_ALPHAMOD)=='delete' && AllowEdit())
{
	if(DeletePromptCommon('student from that user','remove access to'))
	{
		DBQuery('DELETE FROM students_join_users WHERE STUDENT_ID=\''.$_REQUEST[student_id].'\' AND STAFF_ID=\''.UserStaffID().'\'');
		unset($_REQUEST['modfunc']);
	}
}


if($_REQUEST['modfunc']!='delete')
{	
	if(!UserStaffID())
		Search('staff_id','parent');
	else
	{
		$profile = DBGet(DBQuery('SELECT PROFILE FROM staff WHERE STAFF_ID=\''.UserStaffID().'\''));
		if($profile[1]['PROFILE']!='parent')
		{
			unset($_SESSION['staff_id']);
//			echo '<script language=JavaScript>parent.side.location="'.$_SESSION['Side_PHP_SELF'].'?modcat="+parent.side.document.forms[0].modcat.value;</script>';			
			Search('staff_id','parent');
		}
	}
	
	if(UserStaffID())
	{
		if(clean_param($_REQUEST['search_modfunc'],PARAM_ALPHAMOD)=='list')
		{
			echo "<FORM action=Modules.php?modname=$_REQUEST[modname]&modfunc=save method=POST>";
			
		}
	}
	
	if($note)
		DrawHeader('<IMG SRC=assets/check.gif>'.$note);
	if(UserStaffID())
	{
		echo '<CENTER><TABLE width="" align="center"><TR><TD valign=top>';
		DrawHeader('<div class="big_font">Associated students with '.$RET[1]['FIRST_NAME'].'&nbsp;'.$RET[1]['LAST_NAME'].'</div>',$extra['header_right']);
		$current_RET = DBGet(DBQuery('SELECT u.STUDENT_ID,CONCAT(s.LAST_NAME,\' \',s.FIRST_NAME) AS FULL_NAME FROM students_join_users u,students s WHERE s.STUDENT_ID=u.STUDENT_ID AND u.STAFF_ID=\''.UserStaffID().'\''));
		$link['remove'] = array('link'=>"Modules.php?modname=$_REQUEST[modname]&modfunc=delete",'variables'=>array('student_id'=>'STUDENT_ID'));
			
		ListOutput($current_RET,array('FULL_NAME'=>'Students'),'','',$link,array(),array('search'=>false));
		echo '</TD></TR></TABLE><div class="clear"></div><div style="width:830px;">';
		
			if(clean_param($_REQUEST['search_modfunc'],PARAM_ALPHAMOD)=='list')
			echo '<div style="margin-bottom:-28px;">'.DrawHeader('',SubmitButton(addSelectedStudents,'','class=btn_large')).'</div>';
			
		$extra['link'] = array('FULL_NAME'=>false);
		$extra['SELECT'] = ",NULL AS CHECKBOX";
		$extra['functions'] = array('CHECKBOX'=>'_makeChooseCheckbox');
		$extra['columns_before'] = array('CHECKBOX'=>'</A><INPUT type=checkbox value=Y name=controller onclick="checkAll(this.form,this.form.controller.checked,\'student\');"><A>');
		$extra['new'] = true;
		$extra['options']['search'] = false;
	
		if(AllowEdit())
			Search('student_id',$extra);
		
		echo '</div></CENTER>';
		
		if(clean_param($_REQUEST['search_modfunc'],PARAM_ALPHAMOD)=='list')
			echo "<BR><CENTER>".SubmitButton(addSelectedStudents,'','class=btn_large')."</CENTER></FORM>";
	}
}

function _makeChooseCheckbox($value,$title)
{	global $THIS_RET;

	return "<INPUT type=checkbox name=student[".$THIS_RET['STUDENT_ID']."] value=Y>";
}

?>