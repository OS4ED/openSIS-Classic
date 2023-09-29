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
function Widgets($item,$allow_widget=false)
{	global $extra,$_openSIS;

	if(!is_array($extra['functions']))
		$extra['functions'] = array();

	if(UserWs('PROFILE')=='admin' || UserWs('PROFILE')=='teacher' || $allow_widget)
	{
		switch($item)
		{
			case 'course':
				if(UserWs('PROFILE')=='admin' || $allow_widget)
				{
				if($_REQUEST['w_course_period_id'])
				{
					if($_REQUEST['w_course_period_id_which']=='course')
					{
						$course = DBGet(DBQuery('SELECT c.TITLE AS COURSE_TITLE,cp.TITLE,cp.COURSE_ID FROM course_periods cp,courses c WHERE c.COURSE_ID=cp.COURSE_ID AND cp.COURSE_PERIOD_ID=\''.$_REQUEST['w_course_period_id'].'\''));
						$extra['FROM'] .= ',schedule w_ss';
						$extra['WHERE'] .= ' AND w_ss.STUDENT_ID=s.STUDENT_ID AND w_ss.SYEAR=ssm.SYEAR AND w_ss.SCHOOL_ID=ssm.SCHOOL_ID AND w_ss.COURSE_ID=\''.$course[1]['COURSE_ID'].'\' AND (\''.DBDate().'\' BETWEEN w_ss.START_DATE AND w_ss.END_DATE OR w_ss.END_DATE IS NULL)';
						$_openSIS['SearchTerms'] .= '<font color=gray><b>Course: </b></font>'.$course[1]['COURSE_TITLE'].'<BR>';
					}
					else
					{
						$extra['FROM'] .= ',schedule w_ss';
						$extra['WHERE'] .= ' AND w_ss.STUDENT_ID=s.STUDENT_ID AND w_ss.SYEAR=ssm.SYEAR AND w_ss.SCHOOL_ID=ssm.SCHOOL_ID AND w_ss.COURSE_PERIOD_ID=\''.$_REQUEST['w_course_period_id'].'\' AND (\''.DBDate().'\' BETWEEN w_ss.START_DATE AND w_ss.END_DATE OR w_ss.END_DATE IS NULL)';
						$course = DBGet(DBQuery('SELECT c.TITLE AS COURSE_TITLE,cp.TITLE,cp.COURSE_ID FROM course_periods cp,courses c WHERE c.COURSE_ID=cp.COURSE_ID AND cp.COURSE_PERIOD_ID=\''.$_REQUEST['w_course_period_id'].'\''));
						$_openSIS['SearchTerms'] .= '<font color=gray><b>Course Period: </b></font>'.$course[1]['COURSE_TITLE'].': '.$course[1]['TITLE'].'<BR>';
					}
				}
		
				$extra['search'] .= "<TR><TD align=right width=120>Course</TD><TD><DIV id=course_div></DIV> <A HREF=# onclick='window.open(\"ForWindow.php?modname=miscellaneous/ChooseCourse.php\",\"\",\"scrollbars=yes,resizable=yes,width=800,height=400\");'><SMALL>Choose</SMALL></A></TD></TR>";
				}
			break;

			case 'request':
				if(UserWs('PROFILE')=='admin' || $allow_widget)
				{
				// PART OF THIS IS DUPLICATED IN PrintRequests.php
				if($_REQUEST['request_course_id'])
				{
					$course = DBGet(DBQuery('SELECT c.TITLE FROM courses c WHERE c.COURSE_ID=\''.$_REQUEST['request_course_id'].'\''));
					if(!$_REQUEST['not_request_course'])
					{
						$extra['FROM'] .= ',schedule_requests sch_r';
						$extra['WHERE'] .= ' AND sch_r.STUDENT_ID=s.STUDENT_ID AND sch_r.SYEAR=ssm.SYEAR AND sch_r.SCHOOL_ID=ssm.SCHOOL_ID AND sch_r.COURSE_ID=\''.$_REQUEST['request_course_id'].'\'';
                     
						$_openSIS['SearchTerms'] .= '<font color=gray><b>Request: </b></font>'.$course[1]['TITLE'].'<BR>';
                        
					}
					else
					{
						$extra['WHERE'] .= ' AND NOT EXISTS (SELECT \'\' FROM schedule_requests sch_r WHERE sch_r.STUDENT_ID=ssm.STUDENT_ID AND sch_r.SYEAR=ssm.SYEAR AND sch_r.COURSE_ID=\''.$_REQUEST['request_course_id'].'\') ';
                       
						$_openSIS['SearchTerms'] .= '<font color=gray><b>Missing Request: </b></font>'.$course[1]['TITLE'].'<BR>';
                       
					}
				}
		
				$extra['search'] .= "<TR><TD align=right width=120>Request</TD><TD><DIV id=request_div></DIV> <A HREF=# onclick='window.open(\"ForWindow.php?modname=miscellaneous/ChooseRequest.php\",\"\",\"scrollbars=yes,resizable=yes,width=800,height=400\");'><SMALL>Choose</SMALL></A></TD></TR>";
				}
			break;

			case 'absences':
				if(is_numeric($_REQUEST['absences_low']) && is_numeric($_REQUEST['absences_high']))
				{
					if($_REQUEST['absences_low'] > $_REQUEST['absences_high'])
					{
						$temp = $_REQUEST['absences_high'];
						$_REQUEST['absences_high'] = $_REQUEST['absences_low'];
						$_REQUEST['absences_low'] = $temp;
					}

					if($_REQUEST['absences_low']==$_REQUEST['absences_high'])
						$extra['WHERE'] .= ' AND (SELECT sum(1-STATE_VALUE) AS STATE_VALUE FROM attendance_day ad WHERE ssm.STUDENT_ID=ad.STUDENT_ID AND ad.SYEAR=ssm.SYEAR AND ad.MARKING_PERIOD_ID IN ('.GetChildrenMP($_REQUEST['absences_term'],UserMP()).')) = \''.$_REQUEST[absences_low].'\'';
					else
						$extra['WHERE'] .= ' AND (SELECT sum(1-STATE_VALUE) AS STATE_VALUE FROM attendance_day ad WHERE ssm.STUDENT_ID=ad.STUDENT_ID AND ad.SYEAR=ssm.SYEAR AND ad.MARKING_PERIOD_ID IN ('.GetChildrenMP($_REQUEST['absences_term'],UserMP()).')) BETWEEN \''.$_REQUEST[absences_low].'\' AND \''.$_REQUEST[absences_high].'\'';
					switch($_REQUEST['absences_term'])
					{
						case 'FY':
							$term = 'this school year to date';
						break;
						case 'SEM':
							$term = 'this semester to date';
						break;
						case 'QTR':
							$term = 'this marking period to date';
						break;
					}
					$_openSIS['SearchTerms'] .= '<font color=gray><b>Days Absent '.$term.' between: </b></font>'.$_REQUEST['absences_low'].' &amp; '.$_REQUEST['absences_high'].'<BR>';
				}
				$extra['search'] .= "<TR><TD align=right width=120>Days Absent<BR><INPUT type=radio name=absences_term value=FY checked>YTD<INPUT type=radio name=absences_term value=SEM>".GetMP(GetParentMP('SEM',UserMP()),'SHORT_NAME')."<INPUT type=radio name=absences_term value=QTR>".GetMP(UserMP(),'SHORT_NAME')."</TD><TD>Between <INPUT type=text name=absences_low size=3 class=cell_small maxlength=5> &amp; <INPUT type=text name=absences_high size=3 maxlength=5 class=cell_small></TD></TR>";
			break;

			case 'gpa':
				if(is_numeric($_REQUEST['gpa_low']) && is_numeric($_REQUEST['gpa_high']))
				{
					if($_REQUEST['gpa_low'] > $_REQUEST['gpa_high'])
					{
						$temp = $_REQUEST['gpa_high'];
						$_REQUEST['gpa_high'] = $_REQUEST['gpa_low'];
						$_REQUEST['gpa_low'] = $temp;
					}
					if($_REQUEST['list_gpa'])
					{
						$extra['SELECT'] .= ',sgc.GPA,sgc.weighted_gpa, sgc.unweighted_gpa';
						$extra['columns_after']['GPA'] = 'GPA';
						
					}
					if(strpos($extra['FROM'],'student_gpa_calculated sgc')===false)
					{
						$extra['FROM'] .= ',student_gpa_calculated sgc';
						$extra['WHERE'] .= ' AND sgc.STUDENT_ID=s.STUDENT_ID AND sgc.MARKING_PERIOD_ID=\''.$_REQUEST['gpa_term'].'\'';
					}
					$extra['WHERE'] .= ' AND sgc.GPA BETWEEN \''.$_REQUEST[gpa_low].'\' AND \''.$_REQUEST[gpa_high].'\' AND sgc.MARKING_PERIOD_ID=\''.$_REQUEST['gpa_term'].'\'';
					$_openSIS['SearchTerms'] .= '<font color=gray><b>'.(($_REQUEST['gpa_weighted']=='Y')?'Weighted ':'').'GPA between: </b></font>'.$_REQUEST['gpa_low'].' &amp; '.$_REQUEST['gpa_high'].'<BR>';
				}
                                
                                if(is_numeric($_REQUEST['cgpa_low']) && is_numeric($_REQUEST['cgpa_high']))
				{
					if($_REQUEST['cgpa_low'] > $_REQUEST['cgpa_high'])
					{
						$temp = $_REQUEST['cgpa_high'];
						$_REQUEST['cgpa_high'] = $_REQUEST['cgpa_low'];
						$_REQUEST['cgpa_low'] = $temp;
					}
					if($_REQUEST['cgpa'])
					{
						$extra['SELECT'] .= ',sgr.CGPA';
						$extra['columns_after']['CGPA'] = 'CGPA';
						
					}
//					
                                        if(strpos($extra['FROM'],'student_gpa_runnings sgr')===false)
					{
						$extra['FROM'] .= ',student_gpa_calculated sgr';
						$extra['WHERE'] .= ' AND sgr.STUDENT_ID=s.STUDENT_ID ';
					}
					$extra['WHERE'] .= ' AND sgr.CGPA BETWEEN '.$_REQUEST[cgpa_low].' AND '.$_REQUEST[cgpa_high].' ';
					$_openSIS['SearchTerms'] .= '<font color=gray><b>CGPA between: </b></font>'.$_REQUEST['cgpa_low'].' &amp; '.$_REQUEST['cgpa_high'].'<BR>';
				}
                                $qrtrs_query=DBGet(DBQuery('SELECT COUNT(*) as QUARTER FROM school_quarters where SCHOOL_ID=\''.UserSchool().'\' and SYEAR=\''.UserSyear().'\''));
                                if($qrtrs_query[1]['QUARTER']>1)
                                {
				$extra['search'] .= "<TR><TD align=right width=120><INPUT type=checkbox name=list_gpa value=Y>Marking Period GPA</TD></TR>
                                                    <TR><TD align=right width=120><INPUT type=radio name=gpa_term value=".GetParentMP('SEM',UserMP()).">".GetMP(GetParentMP('SEM',UserMP()),'SHORT_NAME')."
                                                                                                                 <INPUT type=radio name=gpa_term value=".UserMP().">".GetMP(UserMP(),'SHORT_NAME')."</TD>
                                                                                                                <TD>Between<INPUT type=text name=gpa_low class=cell_small size=3 maxlength=5> &amp; <INPUT type=text name=gpa_high size=3 maxlength=5 class=cell_small></TD></TR>";
                               }
                                if($qrtrs_query[1]['QUARTER']<=1)
                                {
				$extra['search'] .= "<TR><TD align=right width=120><INPUT type=checkbox name=list_gpa value=Y>Marking Period GPA</TD></TR>
                                                    <TR><TD align=right width=120>"."<INPUT type=radio name=gpa_term value=".UserMP().">".GetMP(UserMP(),'SHORT_NAME')."</TD>
                                                                                                                <TD>Between<INPUT type=text name=gpa_low class=cell_small size=3 maxlength=5> &amp; <INPUT type=text name=gpa_high size=3 maxlength=5 class=cell_small></TD></TR>";
                               }
                                $extra['search'] .= "<TR><TD align=right width=120><INPUT type=checkbox name=cgpa value=Y>CGPA</TD></TR>
                                                    <TR><TD align=right width=120></td><td>Between<INPUT type=text name=cgpa_low class=cell_small size=3 maxlength=5> &amp; <INPUT type=text name=cgpa_high size=3 maxlength=5 class=cell_small></TD></TR>";
                                break;

			case 'class_rank':
				if(is_numeric($_REQUEST['class_rank_low']) && is_numeric($_REQUEST['class_rank_high']))
				{
					if($_REQUEST['class_rank_low'] > $_REQUEST['class_rank_high'])
					{
						$temp = $_REQUEST['class_rank_high'];
						$_REQUEST['class_rank_high'] = $_REQUEST['class_rank_low'];
						$_REQUEST['class_rank_low'] = $temp;
					}
					if(strpos($extra['FROM'],'student_gpa_calculated sgc')===false)
					{
						$extra['FROM'] .= ',student_gpa_calculated sgc';
						$extra['WHERE'] .= ' AND sgc.STUDENT_ID=s.STUDENT_ID AND sgc.MARKING_PERIOD_ID=\''.$_REQUEST['class_rank_term'].'\'';
					}
					$extra['WHERE'] .= ' AND sgc.CLASS_RANK BETWEEN \''.$_REQUEST[class_rank_low].'\' AND \''.$_REQUEST[class_rank_high].'\'';
					$_openSIS['SearchTerms'] .= '<font color=gray><b>Class Rank between: </b></font>'.$_REQUEST['class_rank_low'].' &amp; '.$_REQUEST['class_rank_high'].'<BR>';
				}
                                $qrtrs_query=DBGet(DBQuery('SELECT COUNT(*) as QUARTER FROM school_quarters where SCHOOL_ID=\''.UserSchool().'\' and SYEAR=\''.UserSyear().'\''));
                                if($qrtrs_query[1]['QUARTER']>1)
                                {
				$extra['search'] .= "<TR><TD align=right width=120>Class Rank<BR><INPUT type=radio name=class_rank_term value=CUM checked>Cumulative<INPUT type=radio name=class_rank_term value=".GetParentMP('SEM',UserMP()).">".GetMP(GetParentMP('SEM',UserMP()),'SHORT_NAME')."<INPUT type=radio name=class_rank_term value=".UserMP().">".GetMP(UserMP(),'SHORT_NAME')."";
                                }
                                if($qrtrs_query[1]['QUARTER']<=1)
                                {
                                $extra['search'] .= "<TR><TD align=right width=120>Class Rank<BR><INPUT type=radio name=class_rank_term value=CUM checked>Cumulative"."<INPUT type=radio name=class_rank_term value=".UserMP().">".GetMP(UserMP(),'SHORT_NAME')."";
                                }
                                if(strlen($pros = GetChildrenMP('PRO',UserMP())))
				{
					$pros = explode(',',str_replace("'",'',$pros));
					foreach($pros as $pro)
						$extra['search'] .= "<INPUT type=radio name=class_rank_term value=".$pro.">".GetMP($pro,'SHORT_NAME')."";
				}
				$extra['search'] .= "</TD><TD>Between<INPUT type=text name=class_rank_low size=3 maxlength=5 class=cell_small> &amp; <INPUT type=text name=class_rank_high size=3 maxlength=5 class=cell_small></TD></TR>";
			break;

			case 'letter_grade':
				if(count($_REQUEST['letter_grade']))
				{
					$_openSIS['SearchTerms'] .= '<font color=gray><b>With'.($_REQUEST['letter_grade_exclude']=='Y'?'out':'').' Report Card Grade: </b></font>';
					$letter_grades_RET = DBGet(DBQuery('SELECT ID,TITLE FROM report_card_grades WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\''),array(),array('ID'));
					foreach($_REQUEST['letter_grade'] as $grade=>$Y)
					{
						$letter_grades .= ",'$grade'";
						$_openSIS['SearchTerms'] .= $letter_grades_RET[$grade][1]['TITLE'].', ';
					}
					$_openSIS['SearchTerms'] = substr($_openSIS['SearchTerms'],0,-2);
					$extra['WHERE'] .= " AND ".($_REQUEST['letter_grade_exclude']=='Y'?'NOT ':'')."EXISTS (SELECT '' FROM student_report_card_grades sg3 WHERE sg3.STUDENT_ID=ssm.STUDENT_ID AND sg3.SYEAR=ssm.SYEAR AND sg3.REPORT_CARD_GRADE_ID IN (".substr($letter_grades,1).")".($_REQUEST['letter_grade_term']!=''?"AND sg3.MARKING_PERIOD_ID='".$_REQUEST['letter_grade_term']."' ":'').")";
					$_openSIS['SearchTerms'] .= '<BR>';
				}
                                $qrtrs_query=DBGet(DBQuery('SELECT COUNT(*) as QUARTER FROM school_quarters where SCHOOL_ID=\''.UserSchool().'\' and SYEAR=\''.UserSyear().'\''));
                                if($qrtrs_query[1]['QUARTER']>1)
                                {
				$extra['search'] .= "<TR><TD align=right width=120>Letter Grade<BR><INPUT type=checkbox name=letter_grade_exclude value=Y>Did not receive<BR><INPUT type=radio name=letter_grade_term value=".GetParentMP('SEM',UserMP()).">".GetMP(GetParentMP('SEM',UserMP()),'SHORT_NAME')."<INPUT type=radio name=letter_grade_term value=".UserMP().">".GetMP(UserMP(),'SHORT_NAME')."";
                                }
                                if($qrtrs_query[1]['QUARTER']<=1)
                                {
                                    $extra['search'] .= "<TR><TD align=right width=120>Letter Grade<BR><INPUT type=checkbox name=letter_grade_exclude value=Y>Did not receive<BR>"."<INPUT type=radio name=letter_grade_term value=".UserMP().">".GetMP(UserMP(),'SHORT_NAME')."";
                                
                                }
                                if(strlen($pros = GetChildrenMP('PRO',UserMP())))
				{
					$pros = explode(',',str_replace("'",'',$pros));
					foreach($pros as $pro)
						$extra['search'] .= "<INPUT type=radio name=letter_grade_term value=".$pro.">".GetMP($pro,'SHORT_NAME')."";
				}
				$extra['search'] .= "</TD><TD>";
				if($_REQUEST['search_modfunc']=='search_fnc' || !$_REQUEST['search_modfunc'])
					$letter_grades_RET = DBGet(DBQuery('SELECT rg.ID,rg.TITLE,rg.GRADE_SCALE_ID FROM report_card_grades rg,report_card_grade_scales rs WHERE rg.SCHOOL_ID=\''.UserSchool().'\' AND rg.SYEAR=\''.UserSyear().'\' AND rs.ID=rg.GRADE_SCALE_ID'.(User('PROFILE')=='teacher'?' AND rg.GRADE_SCALE_ID=(SELECT GRADE_SCALE_ID FROM course_periods WHERE COURSE_PERIOD_ID=\''.UserCoursePeriod().'\')':'').' ORDER BY rs.SORT_ORDER,rs.ID,rg.BREAK_OFF IS NOT NULL DESC,rg.BREAK_OFF DESC,rg.SORT_ORDER'),array(),array('GRADE_SCALE_ID'));
				foreach($letter_grades_RET as $grades)
				{
					$i = 0;
					if(count($grades))
					{
						foreach($grades as $grade)
						{
							if($i%9==0)
								$extra['search'] .= '<BR>';

							$extra['search'] .= '<INPUT type=checkbox value=Y name=letter_grade['.$grade['ID'].']>'.$grade['TITLE'];
							$i++;
						}
					}
				}
				$extra['search'] .= '</TD></TR>';
			break;

			case 'eligibility':
				if($_REQUEST['ineligible']=='Y')
				{
					$start_end_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_config WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' AND PROGRAM=\'eligibility\' AND TITLE IN (\'START_DAY\',\'END_DAY\')'));
					if(count($start_end_RET))
					{
						foreach($start_end_RET as $value)
							$$value['TITLE'] = $value['VALUE'];
					}

					switch(date('D'))
					{
						case 'Mon':
						$today = 1;
						break;
						case 'Tue':
						$today = 2;
						break;
						case 'Wed':
						$today = 3;
						break;
						case 'Thu':
						$today = 4;
						break;
						case 'Fri':
						$today = 5;
						break;
						case 'Sat':
						$today = 6;
						break;
						case 'Sun':
						$today = 7;
						break;
					}

					$start_date = strtoupper(date('d-M-y',time() - ($today-$START_DAY)*60*60*24));
					$end_date = strtoupper(date('d-M-y',time()));
					$extra['WHERE'] .= ' AND (SELECT count(*) FROM eligibility e WHERE ssm.STUDENT_ID=e.STUDENT_ID AND e.SYEAR=ssm.SYEAR AND e.SCHOOL_DATE BETWEEN \''.$start_date.'\' AND \''.$end_date.'\' AND e.ELIGIBILITY_CODE=\'FAILING\') > \'0\'';
					$_openSIS['SearchTerms'] .= '<font color=gray><b>Extracurricular: </b></font>Ineligible<BR>';
				}
				$extra['search'] .= "<TR><TD align=right width=120>Ineligible</TD><TD><INPUT type=checkbox name=ineligible value='Y'></TD></TR>";
			break;

			case 'activity':
				if($_REQUEST['activity_id'])
				{
					$extra['FROM'] .= ',student_eligibility_activities sea';
					$extra['WHERE'] .= ' AND sea.STUDENT_ID=s.STUDENT_ID AND sea.SYEAR=ssm.SYEAR AND sea.ACTIVITY_ID=\''.$_REQUEST['activity_id'].'\'';
					$activity = DBGet(DBQuery('SELECT TITLE FROM eligibility_activities WHERE ID=\''.$_REQUEST['activity_id'].'\''));
					$_openSIS['SearchTerms'] .= '<font color=gray><b>Activity: </b></font>'.$activity[1]['TITLE'].'<BR>';
				}
				if($_REQUEST['search_modfunc']=='search_fnc' || !$_REQUEST['search_modfunc'])
					$activities_RET = DBGet(DBQuery('SELECT ID,TITLE FROM eligibility_activities WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\''));
				$select = "<SELECT name=activity_id><OPTION value=''>Not Specified</OPTION>";
                                $select_arr = array();
                                $select_arr[] = array('ID'=>'','TITLE'=>'Not Specified');
                                
				if(count($activities_RET))
				{
					foreach($activities_RET as $activity)
                                        {
						$select .= "<OPTION value=$activity[ID]>$activity[TITLE]</OPTION>";
                                                $select_arr[] = array('ID'=>$activity['ID'],'TITLE'=>$activity['TITLE']);
                                        }
				}
				$select .= '</SELECT>';
                                return $select_arr;
//				$extra['search'] .= "<TR><TD align=right width=120>Activity</TD><TD>".$select."</TD></TR>";
			break;

			case 'mailing_labels':
				if($_REQUEST['mailing_labels']=='Y')
				{
					$extra['SELECT'] .= ',sam.ID AS MAILING_LABEL';
                                        $extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (sam.STUDENT_ID=ssm.STUDENT_ID AND sam.TYPE=\'Home Address\' )'.$extra['FROM'];
					$extra['functions'] += array('MAILING_LABEL'=>'MailingLabel');
				}

				$extra['search'] .= '<TR><TD align=right width=120>Mailing Labels</TD><TD><INPUT type=checkbox name=mailing_labels value=Y></TD>';
			break;

			case 'balance':
				if(is_numeric($_REQUEST['balance_low']) && is_numeric($_REQUEST['balance_high']))
				{
					if($_REQUEST['balance_low'] > $_REQUEST['balance_high'])
					{
						$temp = $_REQUEST['balance_high'];
						$_REQUEST['balance_high'] = $_REQUEST['balance_low'];
						$_REQUEST['balance_low'] = $temp;
					}
					$extra['WHERE'] .= ' AND (COALESCE((SELECT SUM(f.AMOUNT) FROM BILLING_FEES f,STUDENTS_JOIN_FEES sjf WHERE sjf.FEE_ID=f.ID AND sjf.STUDENT_ID=ssm.STUDENT_ID AND f.SYEAR=ssm.SYEAR),0)+(SELECT COALESCE(SUM(f.AMOUNT),0)-COALESCE(SUM(f.CASH),0) FROM LUNCH_TRANSACTIONS f WHERE f.STUDENT_ID=ssm.STUDENT_ID AND f.SYEAR=ssm.SYEAR)-COALESCE((SELECT SUM(p.AMOUNT) FROM BILLING_PAYMENTS p WHERE p.STUDENT_ID=ssm.STUDENT_ID AND p.SYEAR=ssm.SYEAR),0)) BETWEEN \''.$_REQUEST[balance_low].'\' AND \''.$_REQUEST[balance_high].'\' ';
				}
				$extra['search'] .= "<TR><TD align=right width=120>Student Billing Balance<BR></TD><TD>Between<INPUT type=text name=balance_low size=5 maxlength=10 class=cell_small> &amp; <INPUT type=text name=balance_high size=5 maxlength=10 class=cell_small></TD></TR>";
			break;
                        ############################ ##########################################################
                                                    case 'parents':

                                                    $extra['search'] .= "<TR><TD align=right width=120>Show Parents & Contacts<BR></TD><TD><INPUT type=radio name=show value=P></TD></TR>";
                                                    break;
                                                ############################  ##########################################################
                                                    case 'staff':

                                                    $extra['search'] .= "<TR><TD align=right width=120>Show Staff<BR></TD><TD><INPUT type=radio name=show value=S></TD></TR>";
                                                    break;

####################################################################################################################
			
			break;
		}
	}
}
?>
