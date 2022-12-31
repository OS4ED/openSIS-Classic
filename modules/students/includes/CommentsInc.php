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

include('../../../RedirectIncludes.php');
include_once('modules/students/includes/FunctionsInc.php');
if(clean_param($_REQUEST['modfunc'],PARAM_ALPHAMOD)=='delete')
{
    if(User('PROFILE')=='admin'||User('PROFILE')=='teacher')
    {
	if(!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel'])
	echo '</FORM>';
        if($_REQUEST['staff_id']==User('STAFF_ID'))
        {
            if(DeletePromptCommon(_comment))            
            {
                DBQuery("DELETE FROM $_REQUEST[table] WHERE ID='$_REQUEST[id]'");
		unset($_REQUEST['modfunc']);
            }        
        }
        else 
        {
                echo '<BR>';
		PopTable('header', _alertMessage);
		echo "<CENTER><h4>"._youCanNotDeleteCommentOfAnotherUser."</h4><br><FORM action=$PHP_tmp_SELF METHOD=POST><INPUT type=button class='btn btn-primary' name=delete_cancel value="._ok." onclick='window.location=\"Modules.php?modname=".$_REQUEST['modname']."&category_id=".$_REQUEST['category_id']."&table=".$_REQUEST['table']."&include=".$_REQUEST['include']."&subject_id=".$_REQUEST['subject_id']."&course_id=".$_REQUEST['course_id']."&course_period_id=".$_REQUEST['course_period_id']."\"'></FORM></CENTER>";
		PopTable('footer');
                unset($_REQUEST['modfunc']);
		return false;
        }
        
     }
    }

if(clean_param($_REQUEST['modfunc'],PARAM_ALPHAMOD)=='update')
{
	
    unset($_SESSION['_REQUEST_vars']['modfunc']);
	unset($_SESSION['_REQUEST_vars']['values']);
}
if(!$_REQUEST['modfunc'])
{
	
	
	$table = 'student_mp_comments';

        $functions = array('COMMENT'=>'_makeCommentsn');
        if(User('PROFILE')=='admin' || User('PROFILE')=='teacher'|| User('PROFILE')=='parent' || User('PROFILE')=='student')
	$comments_RET = DBGet(DBQuery("SELECT ID,COMMENT_DATE,COMMENT,CONCAT(s.FIRST_NAME,' ',s.LAST_NAME)AS USER_NAME,student_mp_comments.STAFF_ID FROM student_mp_comments,staff s WHERE STUDENT_ID='".UserStudentID()."'  AND s.STAFF_ID=student_mp_comments.STAFF_ID ORDER BY ID DESC"),$functions);
        $counter_for_date=0;
        foreach($comments_RET as $mi=>$md)
        {
            $counter_for_date=$counter_for_date+1;
            $comments_RET[$mi]['COMMENT_DATE']=_makeDate($md['COMMENT_DATE'],'COMMENT_DATE',$counter_for_date,array('ID'=>$md['ID'],'TABLE'=>'student_mp_comments'));
        }
        $counter_for_date=$counter_for_date+1;
    $columns = array('USER_NAME'=>_enteredBy,
    'COMMENT_DATE'=>_date,
    'COMMENT'=>_comments,
);
	$link['add']['html'] = array('COMMENT_DATE'=>_makeDate('','COMMENT_DATE',$counter_for_date),'COMMENT'=>_makeCommentsn('','COMMENT'),'USER_NAME'=>'');
	  if(User('PROFILE')=='admin' ||User('PROFILE')=='teacher')
          {
            $link['remove']['link'] = "Modules.php?modname=$_REQUEST[modname]&include=$_REQUEST[include]&modfunc=delete&table=student_mp_comments&title=".urlencode('medical comment');
            $link['remove']['variables'] = array('id'=>'ID','staff_id'=>'STAFF_ID');
          
           
          }
          if(User('PROFILE')=='admin')
          {
           $link['USER_NAME']['link'] = "Modules.php?modname=users/Staff.php";
         $link['USER_NAME']['variables'] = array('staff_id'=>'STAFF_ID');
          }
        
	ListOutput($comments_RET,$columns,_comment,_comments,$link,array(),array('search'=>false));


	$_REQUEST['category_id'] = '4';
	$separator = '<hr>';
	
}


?>