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
$schoolinfo = DBGET(DBQUERY('SELECT * FROM schools WHERE ID = ' . UserSchool()));
$schoolinfo = $schoolinfo[1];
$tsyear = UserSyear();
$tpicturepath = $openSISPath . $StudentPicturesPath;
$studataquery = 'select 
s.first_name
, s.last_name
, s.middle_name
, s.gender as gender
, s.birthdate as birthdate
, s.phone as student_phone
, a.STREET_ADDRESS_1 as address
, a.city
, a.state
, a.zipcode
, sg.title as grade_title
, sg.short_name as grade_short
, (select start_date from student_enrollment where student_id = s.student_id order by syear, start_date limit 1) as init_enroll
, CASE 
WHEN sg.short_name = \'12\' THEN e.syear + 1
WHEN sg.short_name = \'11\' THEN e.syear + 2
WHEN sg.short_name = \'10\' THEN e.syear + 3
WHEN sg.short_name = \'09\' THEN e.syear + 4
  END AS gradyear
from students s
inner join student_enrollment e on e.student_id=s.student_id and (e.start_date <= e.end_date or e.end_date is null) and e.syear = ' . $tsyear . '
inner join school_gradelevels sg on sg.id=e.grade_id
inner join schools sch on sch.id=e.school_id
left join student_address a on (a.student_id=s.student_id and a.type=\'Home Address\')
where  s.student_id = ';
$creditquery = 'SELECT divisor AS credit_attempted,credit_earned AS credit_earned
FROM student_gpa_running sgr
WHERE  sgr.student_id = ';

$cgpaquery = 'select *
from student_gpa_running sgr
where sgr.student_id= ';
if ($_REQUEST['modfunc'] == 'save') {
    $handle = PDFStart();
    //loop through each student
    foreach ($_REQUEST['st_arr'] as $arrkey => $student_id) {
        $total_QP_transcript = 0;
        $total_QP_transcript_fy = 0;
        $total_QP_transcript_qr = 0;
        $total_CGPA = 0;
        $total_CGPA_earned = 0;
        $total_credit_earned = 0;
        $total_CGPA_earned_fy = 0;
        $total_CGPA_earned_qr = 0;
        $total_CGPA_attemted = 0;
        $tot_qp = 0;
        if (User('PROFILE') == 'admin' || UserStudentID() == $student_id) {

            $stu_ret = DBGet(DBQuery($studataquery . $student_id), array('BIRTHDATE' => 'ProperDate'));
            $sinfo = $stu_ret[1];
            $school_html = '<table border="0" align=right style="padding-right:40px"><tr><td align=right><table border="0" cellpadding="4" cellspacing="0">
		<tr><td>' . DrawLogo() . '</td></tr>
                          <tr>
                            <td valign="top" ><div style="font-family:Arial; font-size:13px;">
                              <div style="font-size:18px; font-weight:bold; ">' . $schoolinfo['TITLE'] . '</div>
                              <div>' . $schoolinfo['ADDRESS'] . '</div>
                              <div>' . $schoolinfo['CITY'] . ', ' . $schoolinfo['STATE'] . '&nbsp;&nbsp;' . $schoolinfo['ZIPCODE'] . '</div>
                              
                          ';
            if ($schoolinfo['PHONE'])
                $school_html .= '<div>Phone: ' . $schoolinfo['PHONE'] . '</div>
                          ';


            $school_html .= '<div style="font-size:15px; ">' . $schoolinfo['PRINCIPAL'] . ', Principal</div></div> </td>
                            </tr>
                          </table></td></tr></table>';


            $tquery = "select * from transcript_grades where student_id = $student_id  order by mp_id  ";

            $TRET = DBGet(DBQuery($tquery));
            $course_html = array(0 => '', 1 => '', 2 => '');
            $colnum = 0;
            $last_posted = null;
            $last_mp_name = null;
            $section_html = '';
            $crd_ernd = 0;

            $section = 0;

            $tsecs = array();
            $trecs = array();
            $tsection = 0;
            //loop through each transcript record
            foreach ($TRET as $rec) {
                if ($rec['POSTED'] != $last_posted || $rec['MP_NAME'] != $last_mp_name) {
                    if (count($trecs) > 0) {
                        array_push($tsecs, $trecs);
                    }
                    $trecs = array();
                }
                array_push($trecs, $rec);
                $last_posted = $rec['POSTED'];
                $last_mp_name = $rec['MP_NAME'];
            }
            array_push($tsecs, $trecs);
//        $temp_TRET=array();
//        $sort_oc=DBGet(DBQuery('SELECT DISTINCT SORT_ORDER FROM marking_periods WHERE SYEAR='.UserSyear().' AND SCHOOL_ID='.UserSchool()));
//        $sort_oc=count($sort_oc);
//        $mp_oc=DBGet(DBQuery('SELECT DISTINCT MARKING_PERIOD_ID FROM marking_periods WHERE SYEAR='.UserSyear().' AND SCHOOL_ID='.UserSchool()));
//        $mp_oc=count($mp_oc);
//        if($mp_oc!=$sort_oc)
//        {
//            $max_sort_order=DBGet(DBQuery('SELECT MAX(SORT_ORDER) as SORT_ORDER FROM marking_periods WHERE SYEAR='.UserSyear().' AND SCHOOL_ID='.UserSchool()));
//            $max_sort_order=$max_sort_order[1]['SORT_ORDER'];
//            foreach($tsecs as $tret_index=>$tret_val)
//            {
//                if($temp_TRET[strtotime($tret_val[0]['POSTED'])+$tret_val[0]['MP_ID']]!='')
//                $counter=$tret_val[0]['MP_ID']+1;
//                else
//                $counter=$tret_val[0]['MP_ID'];
//                $counter=strtotime($tret_val[0]['POSTED'])+$counter;
//                $temp_TRET[$counter]=$tret_val[0]['MP_ID'];
//            }
//        }
//        else
//        {
//            $max_sort_order=DBGet(DBQuery('SELECT MAX(SORT_ORDER) as SORT_ORDER FROM marking_periods WHERE SYEAR='.UserSyear().' AND SCHOOL_ID='.UserSchool()));
//            $max_sort_order=$max_sort_order[1]['SORT_ORDER'];
//            foreach($tsecs as $tret_index=>$tret_val)
//            {
//
//                if($tret_val[0]['MP_SOURCE']=='openSIS')
//                {
//                    $counter=DBGet(DBQuery('SELECT SORT_ORDER FROM marking_periods WHERE MARKING_PERIOD_ID='.$tret_val[0]['MP_ID']));
//                    $counter=$counter[1]['SORT_ORDER'];
//                }
//                else
//                    $counter=$max_sort_order+$tret_val[0]['MP_ID'];   
//                $temp_TRET[$counter]=$tret_val[0]['MP_ID'];
//
//
//            }
//        }
//        sort($temp_TRET);
//        $temp_tsecs=array();
//        foreach($tsecs as $tret_index=>$tret_val)
//        {
//            foreach($temp_TRET as $tcheck_i=>$tcheck_v)
//            {
//                if($tcheck_v==$tret_val[0]['MP_ID'] && $temp_tsecs[$tcheck_i]=='')
//                {
//                $temp_tsecs[$tcheck_i]=$tret_val;   
//                break;
//                }
//            }
//            
//        }
//        $tsecs = array();
//        for($i=0;$i<count($temp_TRET);$i++)
//        $tsecs[$i]=$temp_tsecs[$i];
//        

            if ($_REQUEST['template'] == 'two')
                $totallines = 45;
            else
                $totallines = 200;
            $linesleft = $totallines;
            $tcolumns = array(0 => array(), 1 => array(), 2 => array());
            $colnum = 0;
            foreach ($tsecs as $tsec) {
                if (count($tsec) + 3 > $linesleft) {
                    $colnum += 1;
                    $linesleft = $totallines;
                }
                array_push($tcolumns[$colnum], $tsec);
                $linesleft -= count($tsec) + 3;
            }
            $colnum = 0;
            foreach ($tcolumns as $tcolumn) {

                foreach ($tcolumn as $tsection) {

                    $firstrec = $tsection[0];
                    $posted_arr = explode('-', $firstrec['POSTED']);


                    if ($firstrec['SCHOOL_ID'] != '' && $firstrec['SYEAR'] != '') {
                        $gradelevel = DBGet(DBQuery('SELECT sg.TITLE FROM school_gradelevels sg,student_enrollment se WHERE se.STUDENT_ID=' . $firstrec['STUDENT_ID'] . ' AND se.SCHOOL_ID=' . $firstrec['SCHOOL_ID'] . ' AND se.SYEAR=' . $firstrec['SYEAR'] . ' AND se.GRADE_ID=sg.ID ORDER BY se.ID DESC LIMIT 0,1'));
                        $gradelevel = $gradelevel[1]['TITLE'];
                    }
                    if ($gradelevel == '' && $firstrec['MP_SOURCE'] == 'History')
                        $gradelevel = ($firstrec['GRADELEVEL'] != '' ? $firstrec['GRADELEVEL'] : 'Not Found');
                    $course_html[$colnum] .= "<tr><td colspan='4'><font color=red>$firstrec[SCHOOL_NAME]($gradelevel)</font></td></tr><tr><td height=\"8\" style='font-size:14px; border-bottom:1px solid #000;'>&nbsp;&nbsp;&nbsp;<b>Courses</b></td>
                  <td height=\"8\" style='font-size:14px; border-bottom:1px solid #000;' align='center' >&nbsp;&nbsp;&nbsp;<b>Credit Hours</TD>
                  <td align='center' height=\"8\" style='font-size:14px; border-bottom:1px solid #000;'><b>Credits Earned</b></td>
                  <td height=\"8\" style='font-size:14px; border-bottom:1px solid #000;' align='center' >&nbsp;&nbsp;&nbsp;<b>" . $firstrec['MP_NAME'] . " - Grade " . "(" . $posted_arr[1] . '/' . $posted_arr[0] . ")</b></td>
                  <td align='center' height=\"8\" style='font-size:14px; border-bottom:1px solid #000;'><b>GP Value</b></td>";

                    $cred_attempted = 0;
                    $cred_earned = 0;
                    $cred_earned_fy = 0;
                    $cred_earned_sem = 0;
                    $cred_earned_qr = 0;
                    $total_QP_value = 0;
                    $total_QP_value_fy = 0;
                    $total_QP_value_qr = 0;
                    $totqp = 0;

                    foreach ($tsection as $trec) {
                        if ($trec['GP_VALUE'])
                            $gp_val = $trec['GP_VALUE'];
                        else
                            $gp_val = $trec['WEIGHTING'];
                        $gradeletter = $trec['GRADE_LETTER'];
                        if ($trec['COURSE_PERIOD_ID'] != '') {
                            $grd_scl = DBGet(DBQuery('SELECT GRADE_SCALE_ID FROM course_periods WHERE course_period_id=\'' . $trec['COURSE_PERIOD_ID'] . '\''));
                            if ($grd_scl[1]['GRADE_SCALE_ID'] != '') {

                                $grade_scl_gpa = DBGet(DBQuery('SELECT GPA_CAL FROM report_card_grade_scales WHERE ID=' . $grd_scl[1]['GRADE_SCALE_ID']));
                                if ($grade_scl_gpa[1]['GPA_CAL'] == 'Y')
                                    $QP_value = ($trec['CREDIT_EARNED'] * $gp_val);
                                else
                                    $QP_value = 0.00;
                            }
                            else {
                                $trec['CREDIT_EARNED'] = $trec['CREDIT_ATTEMPTED'];
                                $QP_value = ($trec['CREDIT_EARNED'] * $gp_val);
                            }
                        } else {
                            if ($trec['GPA_CAL'] == 'Y')
                                $QP_value = ($trec['CREDIT_EARNED'] * $gp_val);
                            else
                                $QP_value = 0.00;
                        }
                        if ($trec['COURSE_PERIOD_ID']) {
                            $mp_id = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM course_periods WHERE COURSE_PERIOD_ID=' . $trec['COURSE_PERIOD_ID']));
                            if ($mp_id[1]['MARKING_PERIOD_ID'] != '')
                                $get_mp_tp = DBGet(DBQuery('SELECT MP_TYPE FROM marking_periods WHERE MARKING_PERIOD_ID=' . $mp_id[1]['MARKING_PERIOD_ID']));
                            else {
                                $mp_id = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM student_report_card_grades WHERE COURSE_PERIOD_ID=' . $trec['COURSE_PERIOD_ID']));
                                $get_mp_tp = DBGet(DBQuery('SELECT MP_TYPE FROM marking_periods WHERE MARKING_PERIOD_ID=' . $mp_id[1]['MARKING_PERIOD_ID']));
                            }
                            $get_mp_tp_m = DBGet(DBQuery('SELECT MP_TYPE FROM marking_periods WHERE MARKING_PERIOD_ID=' . $trec['MP_ID']));
                        } else {
                            $get_mp_tp_m = DBGet(DBQuery('SELECT MP_TYPE FROM marking_periods WHERE MARKING_PERIOD_ID=' . $trec['MP_ID']));
                            $get_mp_tp[1]['MP_TYPE'] = $get_mp_tp_m[1]['MP_TYPE'];
                        }
                        $course_html[$colnum] .= "<tr><td height=\"8\">&nbsp;&nbsp;&nbsp;" . $trec['COURSE_NAME'] . "</td>
                                             <td>" . sprintf("%01.2f", $trec['CREDIT_ATTEMPTED']) . "</td>
                                             <td style='font-family:Arial; font-size:12px;'>" . sprintf("%01.2f", $trec['CREDIT_EARNED']) . "</td>
                                             <td style='font-family:Arial; font-size:12px;' align=center>" . $gradeletter . "</td>
                                             <td style='font-family:Arial; font-size:12px;'>" . sprintf("%01.2f", ($trec['CREDIT_EARNED'] * $gp_val)) . "</td>
                                             ";
                        $totqp = ($totqp + ($trec['CREDIT_EARNED'] * $gp_val));
                        $tot_qp = ($tot_qp + ($trec['CREDIT_EARNED'] * $gp_val));
                        $qtr_gpa = $trec['GPA'];


                        $cred_attempted += $trec['CREDIT_ATTEMPTED'];
                        $cred_earned += $trec['CREDIT_EARNED'];


                        if ($get_mp_tp[1]['MP_TYPE'] == 'year' && $get_mp_tp[1]['MP_TYPE'] == $get_mp_tp_m[1]['MP_TYPE']) {
                            $total_QP_value_fy += $QP_value;
                            $cred_earned_fy += $trec['CREDIT_EARNED'];
                        }
                        if ($get_mp_tp[1]['MP_TYPE'] == 'semester' && $get_mp_tp[1]['MP_TYPE'] == $get_mp_tp_m[1]['MP_TYPE']) {
                            $total_QP_value += $QP_value;
                            $cred_earned_sem += $trec['CREDIT_EARNED'];
                        }
                        if ($get_mp_tp[1]['MP_TYPE'] == 'quarter' && $get_mp_tp[1]['MP_TYPE'] == $get_mp_tp_m[1]['MP_TYPE']) {
                            $total_QP_value_qr += $QP_value;
                            $cred_earned_qr += $trec['CREDIT_EARNED'];
                        }
                    }
                    $crd_ernd+=$cred_earned;
                    $total_credit_earned = $total_credit_earned + $cred_earned;

                    $total_QP_transcript = $total_QP_transcript + $total_QP_value;
                    $total_QP_transcript_fy = $total_QP_transcript_fy + $total_QP_value_fy;
                    $total_QP_transcript_qr = $total_QP_transcript_qr + $total_QP_value_qr;
                    $total_CGPA_earned = $total_CGPA_earned + $cred_earned_sem;
                    $total_CGPA_earned_fy = $total_CGPA_earned_fy + $cred_earned_fy;
                    $total_CGPA_earned_qr = $total_CGPA_earned_qr + $cred_earned_qr;
                    $total_CGPA_attemted = $total_CGPA_attemted + $cred_attempted;
                    $total_CGPA = $total_CGPA + ($total_QP_value / $qtr_gpa);

                    $course_html[$colnum] .= "<tr><td colspan=3 style='font-size:16px; border-top:1px solid #000;'>
                                            <TABLE width='100%' style='font-family:Arial; font-size:12px;'>
                                            <TR><TD>Credit Attempted: " . sprintf("%01.2f", $cred_attempted) . " / Credit Earned: " . sprintf("%01.2f", $cred_earned) . " / GPA: " . sprintf("%01.2f", ($totqp / $cred_attempted)) . "</TD>
                                            </TR></TABLE></td></tr>";

                    unset($qtr_gpa);
                    unset($totqp);
                }
                $colnum += 1;
            }
            $picturehtml = '';
            if ($_REQUEST['show_photo']) {
                $stu_img_info = DBGet(DBQuery('SELECT * FROM user_file_upload WHERE USER_ID=' .$student_id. ' AND PROFILE_ID=3 AND SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear() . ' AND FILE_INFO=\'stuimg\''));
                if (count($stu_img_info) > 0) {
                    $picturehtml = '<td valign="top" align="left" width=30%><img style="padding:4px; width:144px; border:1px solid #333333; background-color:#fff;" src="data:image/jpeg;base64,' . base64_encode($stu_img_info[1]['CONTENT']) . '"></td>';
                } else {
                    $picturehtml = '<td valign="top" align="left" width=30%><img style="padding:4px; border:1px solid #333333; background-color:#fff;" src="assets/noimage.jpg"></td>';
                }
            }

            $grade_scale = DBGet(DBQuery('SELECT rcg.TITLE,rcg.GPA_VALUE, rcg.UNWEIGHTED_GP,rcg.COMMENT,rcgs.GP_SCALE FROM report_card_grade_scales rcgs,report_card_grades rcg
                                        WHERE rcg.grade_scale_id =rcgs.id and rcg.syear=\'' . $tsyear . '\' and rcg.school_id=\'' . UserSchool() . '\' ORDER BY rcg.SORT_ORDER'));

            $grade_scale_value = $grade_scale[1];

            $general_info_html = '<tr><td>
                              <table height="130px" width="60%">
                              <tr><td colspan="3"> GPA & CGPA based on a ' . $grade_scale_value['GP_SCALE'] . '-point scale as follows:</td></tr>
	     	              <tr><td>
                                   <table height="130px" width="90%">
                                   <tr><td >Grade Letter</td><td >Weighted Grade Points</td><td >Unweighted Grade Points</td><td >Comments</td></tr>';
            foreach ($grade_scale as $grade_scale_val) {

                $general_info_html .= '<tr><td>' . $grade_scale_val['TITLE'] . '</td>
                                      <td>' . $grade_scale_val['GPA_VALUE'] . '</td>
                                      <td>' . $grade_scale_val['UNWEIGHTED_GP'] . '</td>
                                      <td>' . $grade_scale_val['COMMENT'] . '</td></tr>';
            }
            $general_info_html .= '</table>
                               </td></tr>';

            if ($probation) {
                $general_info_html = $general_info_html .
                        '<tr><td width="2%"></td><td width="3%" style="padding-bottom:15px">Status:</td><td width="95%"> ACADEMIC PROBATION
         Please be reminded of Section 2.3.6 of the Academic Handbook:
         If students fail to raise their CGPA above 3.0 for two consecutive semesters
         the default action is dismissal from the Program.</td></tr>' .
                        '</table><BR><BR></td></tr>';
            } else {
                $general_info_html = $general_info_html .
                        '</table><BR><BR></td></tr>';
            }
            $student_html = '
                <table border="0" style="font-family:Arial; font-size:12px;" cellpadding="0" cellspacing="0"><tr>' . $picturehtml .
                    '<td width=70% valign=bottom>
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" style="font-family:Arial; font-size:12px;">
                        <tr><td valign=bottom><div style="font-family:Arial; font-size:13px; padding:0px 12px 0px 12px;"><div style="font-size:18px;">' . $sinfo['LAST_NAME'] . ', ' . $sinfo['FIRST_NAME'] . ' ' . $sinfo['MIDDLE_NAME'] . '</div>
                            <div>' . $sinfo['ADDRESS'] . '</div>
                            <div>' . $sinfo['CITY'] . ', ' . $sinfo['STATE'] . '  ' . $sinfo['ZIPCODE'] . '</div>
                            <div><b>Phone:</b>  ' . $sinfo['STUDENT_PHONE'] . '</div>
							<div><table cellspacing="0" cellpadding="3" border="1"  style="font-family:Arial; font-size:13px; border-collapse: collapse; text-align:center"><tr><td><b>Date of Birth</b></td><td><b>Gender</b></td><td><b>Grade</b></td></tr><tr><td>' . str_replace('-', '/', $sinfo['BIRTHDATE']) . '</td><td>' . $sinfo['GENDER'] . '</td><td>' . $sinfo['GRADE_SHORT'] . '</td></tr></table>' . '</div>
							</td>

                        </tr></table></td></tr><tr><td colspan="2" style="padding:6px 0px 6px 0px;"><table width="100%" cellspacing="0" cellpadding="3" border="1" align=center  style="font-family:Arial; font-size:13px; border-collapse: collapse; text-align:center"><tr><td><b>Cumulative GPA:</b> ' . sprintf("%01.2f", (($tot_qp) / ($total_CGPA_attemted))) . '&nbsp;&nbsp;&nbsp;&nbsp;
                            
                                </td></tr><tr><td><b>Total Credit Attempted:</b> ' . sprintf("%01.2f", $total_CGPA_attemted) . '&nbsp;&nbsp;&nbsp;&nbsp;<b>Total Credit Earned:</b> ' . sprintf("%01.2f", $total_credit_earned) . '</td></tr></table></td></tr></table>';




            echo '  <!-- HEADER CENTER "' . $schoolinfo['TITLE'] . ' Transcript" -->
                <!-- FOOTER CENTER "Transcript is unofficial unless signed by a school official" -->
              <!-- MEDIA LEFT .25in -->
              <!-- MEDIA TOP .25in -->
              <!-- MEDIA RIGHT .25in -->
              <!-- MEDIA BOTTOM .25in -->
            <table width="860px" border="0" cellpadding="2" cellspacing="0">
                
              <tr>  <!-- this is the header row -->
                <td height="100" valign="top">
                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        
                        <td width="50%" valign="top" align="center">' . $student_html . '</td>
                        
                        <td width="50%" valign="top" align="right">' . $school_html . '</td>
                      </tr>
                    </table>
                </td>
              </tr>  <!-- end of header row -->
              <tr>   <!-- this is the main body row -->
                <td width="100%" valign="top" >
                  <table width="100%" height="400px" border="1" cellpadding="0" cellspacing="0">
                    <tr>
                        <td valign="top">
                            <table width="100%" border="0" cellpadding="0" cellspacing="6" style="font-family:Arial; font-size:12px;">
                                  <tr>
                                    <td valign="top" align="left" valign="top">     <!-- -->
                                        <table border="0" cellpadding="3" cellspacing="0" style="font-family:Arial; font-size:12px;">
                                            ' . $course_html[0] . '
                                        </table>
                                      </td>
                                      <td valign="top"align="center"><table width="100%">' . $course_html[1] . '</table></td>
                                      <td valign="top"align="center"><table width="100%">' . $course_html[2] . '</table></td>
                                    </tr>
                            </table>
                        </td>
                    </tr> ' . $general_info_html . '
                  </table>
                </td>
              
              </tr>  <!-- end of main body row -->
              <tr>   <!-- this is the footer row --> 
                <td align=left>
                    <table align=left>
                        <tr>
                           
                            <td valign="Top" align="left">
                                <table width="100%" >
                                    
                                    <tr><td colspan="3" height="10">&nbsp;</td></tr> 
                                    <tr valign="bottom">
                                        <td align="center" valign="bottom"><br>_______________________________</td>
										<td colspan="2" >&nbsp;</td>
                                    </tr> 
									<tr>
                                        <td align="left" valign="top" style="font-family:Arial; font-size:13px; font-weight:bold">Signature</td>
                                        <td colspan="2">&nbsp;</td>
                                        
                                    </tr>
                                    <tr><td colspan="3" height="10">&nbsp;</td></tr> 
                                    <tr valign="bottom">
                                        <td align="center" valign="bottom"><br>_______________________________</td>
										<td colspan="2" >&nbsp;</td>
                                    </tr> 
									<tr>
                                        <td align="left" valign="top" style="font-family:Arial; font-size:13px; font-weight:bold">Title</td>
                                        <td colspan="2">&nbsp;</td>
                                        
                                    </tr> 
                                </table>
                            </td>
                       </tr>     
                   </table> 
                </td>
              </tr>   <!-- end of footer row -->
            </table><div style="page-break-before: always;">&nbsp;</div>';
            echo '<!-- NEW PAGE -->';
        }
    }
    PDFStop($handle);
}
if (!$_REQUEST['modfunc']) {
    DrawBC("Gradebook > " . ProgramTitle());
    if ($_REQUEST['search_modfunc'] == 'list') {
        echo "<FORM action=ForExport.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=save&_openSIS_PDF=true method=POST target=_blank>";

        $extra['extra_header_left'] = '<div class="form-inline">';
        $extra['extra_header_left'] .= '<div class="form-group"><div class="checkbox checkbox-switch switch-success switch-xs"><label><input type="checkbox" name="show_photo" id="show_photo" /><span></span> Include Student Picture</label></div></div>';
        $extra['extra_header_left'] .= '<div class="form-group"><div class="checkbox checkbox-switch switch-success switch-xs"><label><input type="checkbox" name="incl_mp_grades" id="" checked disabled /><span></span> Include Marking Period grades</label></div></div>';
        $extra['extra_header_left'] .= '<div class="form-group"><label class="radio-inline"><input type="radio" class="styled" name="template" id="" value="two" checked /> Two Column Template</label>';
        $extra['extra_header_left'] .= '<label class="radio-inline"><input class="styled" type="radio" name="template" id="" value="single" /> Single Column Template</label></div>';
        $extra['extra_header_left'] .= '</div>';
    }
    $extra['link'] = array('FULL_NAME' => false);
    $extra['SELECT'] = ",s.STUDENT_ID AS CHECKBOX";
    $extra['functions'] = array('CHECKBOX' => '_makeChooseCheckbox');
//    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller checked onclick="checkAll(this.form,this.form.controller.checked,\'st_arr\');"><A>');
//    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAll(this.form,this.form.controller.checked,\'unused\');"><A>');
    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAllDtMod(this,\'st_arr\');"><A>');
    $extra['new'] = true;
    $extra['options']['search'] = false;
    $extra['force_search'] = true;

    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-lg-6">';
    Widgets('course');
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '</div>'; //.row

    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-lg-6">';
    $extra['search'] .= '<div class="well mb-20 pt-5 pb-5">';
    $extra['search'] .= '<div class="pl-10">';
    Widgets('gpa');
    $extra['search'] .= '</div>';
    $extra['search'] .= '</div>'; //.well
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '<div class="col-lg-6">';
    $extra['search'] .= '<div class="well mb-20 pt-5 pb-5">';
    Widgets('letter_grade');
    $extra['search'] .= '</div>'; //.well
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '</div>'; //.row



    Search('student_id', $extra, 'true');
    if ($_REQUEST['search_modfunc'] == 'list') {
        if ($_SESSION['count_stu'] != 0)
            echo '<div class="text-center"><INPUT type=submit class="btn btn-primary" value=\'Create Transcripts for Selected Students\'></div>';
        echo "</FORM>";
    }

    echo '<div id="modal_default" class="modal fade">';
    echo '<div class="modal-dialog modal-lg">';
    echo '<div class="modal-content">';
    echo '<div class="modal-header">';
    echo '<button type="button" class="close" data-dismiss="modal">×</button>';
    echo '<h4 class="modal-title">Choose course</h4>';
    echo '</div>';

    echo '<div class="modal-body">';
    echo '<div id="conf_div" class="text-center"></div>';
    echo '<div class="row" id="resp_table">';
    echo '<div class="col-md-4">';
    $sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
    $QI = DBQuery($sql);
    $subjects_RET = DBGet($QI);

    echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' Subject was' : ' Subjects were') . ' found.</h6>';
    if (count($subjects_RET) > 0) {
        echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>Subject</th></tr></thead>';
        foreach ($subjects_RET as $val) {
            echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearch(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
        }
        echo '</table>';
    }
    echo '</div>';
    echo '<div class="col-md-4" id="course_modal"></div>';
    echo '<div class="col-md-4" id="cp_modal"></div>';
    echo '</div>'; //.row
    echo '</div>'; //.modal-body
    echo '</div>'; //.modal-content
    echo '</div>'; //.modal-dialog
    echo '</div>'; //.modal
}

function _makeChooseCheckbox($value, $title) {
    global $THIS_RET;
//    return '<INPUT type=checkbox name=st_arr[] value=' . $value . ' checked>';
    
//    return "<input name=unused[$THIS_RET[STUDENT_ID]]  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckbox(\"values[STUDENTS][$THIS_RET[STUDENT_ID]]\",this,$THIS_RET[STUDENT_ID]);' />";
    
    return "<input name=unused[$THIS_RET[STUDENT_ID]] value=" . $THIS_RET[STUDENT_ID] . "  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"st_arr[]\",this,$THIS_RET[STUDENT_ID]);' />";
}

function _convertlinefeed($string) {
    return str_replace("\n", "&nbsp;&nbsp;&nbsp;", $string);
}

?>
