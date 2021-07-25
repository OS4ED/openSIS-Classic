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
unset($_SESSION['_REQUEST_vars']['subject_id']);unset($_SESSION['_REQUEST_vars']['course_id']);unset($_SESSION['_REQUEST_vars']['course_period_id']);
if($_REQUEST['ses'])
{
    DBQuery("DROP TABLE IF EXISTS temp_schedule");
    DBQuery("CREATE TABLE temp_schedule AS SELECT * FROM schedule WHERE 0");
    unset($_SESSION['course_periods']);
}
// if only one subject, select it automatically -- works for Course Setup and Choose a Course
if($_REQUEST['modfunc']!='delete' && !$_REQUEST['subject_id'])
{
	$subjects_RET = DBGet(DBQuery('SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\''));
	if(count($subjects_RET)==1)
		$_REQUEST['subject_id'] = $subjects_RET[1]['SUBJECT_ID'];
}
if($_REQUEST['course_modfunc']=='search')
{
	PopTable('header','Search');
	echo "<FORM name=F1 id=F1 action=ForWindow.php?modname=".strip_tags(trim($_REQUEST[modname]))."&modfunc=".strip_tags(trim($_REQUEST[modfunc]))."&course_modfunc=search method=POST>";
	echo '<TABLE><TR><TD><INPUT type=text class=form-control name=search_term value="'.strip_tags(trim($_REQUEST['search_term'])).'"></TD><TD><INPUT type=submit class="btn btn-primary" value='._search.' onclick=\'formload_ajax("F1")\';></TD></TR></TABLE>';
	echo '</FORM>';
	PopTable('footer');
	if($_REQUEST['search_term'])
	{
		$subjects_RET = DBGet(DBQuery('SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE (UPPER(TITLE) LIKE \''.'%'.strtoupper($_REQUEST['search_term']).'%' .'\' OR UPPER(SHORT_NAME) = \''.strtoupper($_REQUEST['search_term']).'\') AND SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
		$courses_RET = DBGet(DBQuery('SELECT SUBJECT_ID,COURSE_ID,TITLE FROM courses WHERE (UPPER(TITLE) LIKE \''.'%'.strtoupper($_REQUEST['search_term']).'%'.'\' OR UPPER(SHORT_NAME) = \''.strtoupper($_REQUEST['search_term']).'\') AND SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
		$periods_RET = DBGet(DBQuery('SELECT c.SUBJECT_ID,cp.COURSE_ID,cp.COURSE_PERIOD_ID,cp.TITLE FROM course_periods cp,courses c WHERE cp.COURSE_ID=c.COURSE_ID AND (UPPER(cp.TITLE) LIKE \''.'%'.strtoupper($_REQUEST['search_term']).'%'.'\' OR UPPER(cp.SHORT_NAME) = \''.strtoupper($_REQUEST['search_term']).'\') AND cp.SYEAR=\''.UserSyear().'\' AND cp.SCHOOL_ID=\''.UserSchool().'\''));

		echo '<TABLE><TR><TD valign=top>';
		$link['TITLE']['link'] = "ForWindow.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]";
		
		$link['TITLE']['variables'] = array('subject_id'=>'SUBJECT_ID');
		ListOutput($subjects_RET,array('TITLE'=>'Subject'),_subject,_subjects,$link,array(),array('search'=>false,'save'=>false));
		echo '</TD><TD valign=top>';
		$link['TITLE']['link'] = "ForWindow.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]";
		
		$link['TITLE']['variables'] = array('subject_id'=>'SUBJECT_ID','course_id'=>'COURSE_ID');
		ListOutput($courses_RET,array('TITLE'=>'Course'),_course,_courses,$link,array(),array('search'=>false,'save'=>false));
		echo '</TD><TD valign=top>';
		$link['TITLE']['link'] = "ForWindow.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]";
		
		$link['TITLE']['variables'] = array('subject_id'=>'SUBJECT_ID','course_id'=>'COURSE_ID','course_period_id'=>'COURSE_PERIOD_ID');
		ListOutput($periods_RET,array('TITLE'=>'Course Period'),_coursePeriod,_coursePeriods,$link,array(),array('search'=>false,'save'=>false));
		echo '</TD></TR></TABLE>';
	}
}
if((!$_REQUEST['modfunc'] || $_REQUEST['modfunc']=='choose_course') && !$_REQUEST['course_modfunc'])
{
	if($_REQUEST['modfunc']!='choose_course')
		DrawBC(""._scheduling." > ".ProgramTitle());
        echo '<center><div id="conf_div"></div></center>';
        echo '<center><div id="calculating" style=display:none><img src="assets/ajax-loader.gif" width="15px" ><br />'._checkingSchedulePleaseWait.'...</div></center>';
        if($clash)
                                    echo "<center><b>"._thereIsAConflict.". "._youCannotAddThisCoursePeriod." </b></center>".ErrorMessage($clash,'note')."";
       
        echo '<FORM name="courses" method="post">';
        echo '<center><div id="resp_div">';
        echo '<TABLE id="resp_table">';
        foreach($_SESSION['course_periods'] as $cp_id=>$title)
        {
            echo '<tr id="selected_course_tr_'.$cp_id.'"><td align=left><INPUT type="checkbox" id="selected_course_'.$cp_id.'" name="selected_course_periods[]" checked="checked" value="'.$cp_id.'"></td><td><b> '.$title.'</b></td></tr>';
        }
        echo '</TABLE>';
        echo '</div></center>';
        if($_POST['exit'])
        {
            DBQuery("DROP TABLE IF EXISTS temp_schedule");
            unset($_SESSION['course_periods']);
            unset($_REQUEST['selected_course_periods']);
            echo '<script type=text/javascript>window.close();</script>';
        }
        else if($_POST['done'])
            {
            $cp_list = '\''.implode('\',\'',$_REQUEST['selected_course_periods']).'\'';
            $parent_course=array();
            foreach($_REQUEST['selected_course_periods'] as $val)
            {
            $res=DBGet( DBQuery("SELECT parent_id from  course_periods WHERE course_period_id=".$val));
         
            if($res[1]['PARENT_ID']!=$val)
            {
           $res_sch=DBGet( DBQuery('SELECT count(*) as res from  schedule WHERE course_period_id='.$res[1]['PARENT_ID'].' and student_id='.UserStudentID()));
           
            if($res_sch[1]['RES']>0)
            {
           DBQuery("INSERT INTO schedule(syear, school_id, student_id, start_date, end_date,modified_by, course_id, course_weight, course_period_id, mp, marking_period_id, scheduler_lock, dropped) SELECT syear, school_id, student_id, start_date, end_date, modified_by, course_id, course_weight, course_period_id, mp, marking_period_id, scheduler_lock, dropped FROM temp_schedule WHERE course_period_id =$val");
            DBQuery("DROP TABLE IF EXISTS temp_schedule");
            unset($_SESSION['course_periods']);
            unset($_SESSION['marking_period_id']);
            unset($_REQUEST['selected_course_periods']);                 
            }
            else {
                
                $parent_course[]=$val;
               
            }
            }
          else {

               
              DBQuery("INSERT INTO schedule(syear, school_id, student_id, start_date, end_date,modified_by, course_id, course_weight, course_period_id, mp, marking_period_id, scheduler_lock, dropped) SELECT syear, school_id, student_id, start_date, end_date, modified_by, course_id, course_weight, course_period_id, mp, marking_period_id, scheduler_lock, dropped FROM temp_schedule WHERE course_period_id=$val");

            unset($_SESSION['course_periods']);
            unset($_SESSION['marking_period_id']);
            unset($_REQUEST['selected_course_periods']);
          }
            }
            
            $parent_course=implode(',', $parent_course);
            if($parent_course!='')
            {
            $parent_course_name=DBGet( DBQuery("SELECT title from  course_periods WHERE course_period_id in (".$parent_course.")"));
         
            $parent_c_name=array();
            foreach($parent_course_name as $title)
            { 
                foreach($title as $c_title)
                $parent_c_name[]=  $c_title;
            }
            if(count($parent_c_name)>0)
            $parent_c_name=  implode(',', $parent_c_name);
            else {
             $parent_c_name='';
             }
             
            }
            
            $_SESSION['conflict_cp']=$parent_c_name;
           echo "<script type=text/javascript>opener.document.location='Modules.php?modname=".strip_tags(trim($_REQUEST[modname]))."&student_id=".UserStudentID()."';window.close();</script>";
          }
	$LO_options = array('save'=>false,'search'=>false);

	if(!$_REQUEST['subject_id'] || $_REQUEST['modfunc']=='choose_course')

	DrawHeaderHome('Courses',"<A HREF=ForWindow.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&course_modfunc=search>Search</A>");

	echo '<TABLE><TR>';
	$sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='".UserSchool()."' AND SYEAR='".UserSyear()."' ORDER BY TITLE";
	$QI = DBQuery($sql);
	$subjects_RET = DBGet($QI);
	if(count($subjects_RET))
	{
		if($_REQUEST['subject_id'])
		{
			foreach($subjects_RET as $key=>$value)
			{
				if($value['SUBJECT_ID']==$_REQUEST['subject_id'])
					$subjects_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
			}
		}
	}

	echo '<TD valign=top>';
	$columns = array('TITLE'=>_subject);
	$link = array();
	$link['TITLE']['link'] = "ForWindow.php?modname=$_REQUEST[modname]";
	
	$link['TITLE']['variables'] = array('subject_id'=>'SUBJECT_ID');
	if($_REQUEST['modfunc']!='choose_course')
		$link['add']['link'] = "ForWindow.php?modname=$_REQUEST[modname]&subject_id=new";
		
	else
		$link['TITLE']['link'] .= "&modfunc=$_REQUEST[modfunc]";

	ListOutput($subjects_RET,$columns,_subject,_subjects,$link,array(),$LO_options,'ForWindow');
	echo '</TD>';

	if($_REQUEST['subject_id'] && $_REQUEST['subject_id']!='new')
	{
		
            $sql="SELECT COURSE_ID,c.TITLE, CONCAT_WS(' - ',c.short_name,c.title) AS GRADE_COURSE FROM courses c LEFT JOIN school_gradelevels sg ON c.grade_level=sg.id WHERE SUBJECT_ID='$_REQUEST[subject_id]' ORDER BY c.TITLE";
		$QI = DBQuery($sql);
		$courses_RET = DBGet($QI);

		if(count($courses_RET))
		{
			if($_REQUEST['course_id'])
			{
				foreach($courses_RET as $key=>$value)
				{
					if($value['COURSE_ID']==$_REQUEST['course_id'])
						$courses_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
				}
			}
		}

		echo '<TD valign=top>';
		$columns = array('GRADE_COURSE'=>_course);
		$link = array();
		$link['GRADE_COURSE']['link'] = "ForWindow.php?modname=$_REQUEST[modname]&subject_id=$_REQUEST[subject_id]";
		
		$link['GRADE_COURSE']['variables'] = array('course_id'=>'COURSE_ID');
		if($_REQUEST['modfunc']!='choose_course')
			$link['add']['link'] = "ForWindow.php?modname=$_REQUEST[modname]&subject_id=$_REQUEST[subject_id]&course_id=new";
			
		else
			$link['GRADE_COURSE']['link'] .= "&modfunc=$_REQUEST[modfunc]";
	
		ListOutput($courses_RET,$columns,_course,_courses,$link,array(),$LO_options,'ForWindow');
		echo '</TD>';

		if($_REQUEST['course_id'] && $_REQUEST['course_id']!='new')
		{
                    $sql = "SELECT COURSE_PERIOD_ID AS CHECKBOX,COURSE_PERIOD_ID,TITLE,COALESCE(TOTAL_SEATS-FILLED_SEATS,0) AS AVAILABLE_SEATS FROM course_periods WHERE COURSE_ID='$_REQUEST[course_id]'AND (marking_period_id IS NOT NULL AND marking_period_id IN(".  GetAllMP(GetMPTable(GetMP(UserMP(),'TABLE')),  UserMP()).") OR marking_period_id IS NULL AND '".date('Y-m-d')."' <= end_date) ORDER BY TITLE";
                $QI = DBQuery($sql);

                $functions= array('CHECKBOX'=>'_makeChooseCheckbox');
                $periods_RET = DBGet($QI,$functions);
                if(count($periods_RET))
                {
                    if($_REQUEST['course_period_id'])
                    {
                        foreach($periods_RET as $key=>$value)
                        {
                            if($value['COURSE_PERIOD_ID']==$_REQUEST['course_period_id'])
                                $periods_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
                        }
                    }
                }

                echo '<TD valign=top>';
                
                    
                    $columns = array('CHECKBOX'=>'</A><A>','TITLE'=>_coursePeriod);

                if($_REQUEST['modname']=='scheduling/Schedule.php')
                    $columns += array('AVAILABLE_SEATS'=>_availableSeats);
                $link = array();
                ListOutput($periods_RET,$columns,_period,_periods,$link,array(),$LO_options,'ForWindow');
                if(count($periods_RET))
                {
                        echo '<BR>'.SubmitButton(done,'done','class="btn btn-primary" ').'&nbsp;&nbsp;'.SubmitButton(clearExit,'exit','class="btn btn-primary" ');
                }
                echo '</TD>';
            echo '</FORM>';
		}
	}

	echo '</TR></TABLE>';
}
function _makeChooseCheckbox($value,$title)
{
        return '<INPUT type=checkbox id=course_'.$value.' name=course_periods['.$value.'] value='.$value. (array_key_exists($value,$_SESSION['course_periods'])?' checked':'').' onchange="verify_schedule(this);" >';
}


?>
