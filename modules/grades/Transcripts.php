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


            /*
             * Create Credits and GPA Columns
             */
            if ($_REQUEST['template'] == 'two')
                $totallines = 45;
            else
                $totallines = 200;
            $linesleft = $totallines;
            $tcolumns = array(0 => array(), 1 => array());
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
                    $course_html[$colnum] .= '<div class="item" style="padding-bottom: 15px;">';

                    $firstrec = $tsection[0];
                    $posted_arr = explode('-', $firstrec['POSTED']);


                    if ($firstrec['SCHOOL_ID'] != '' && $firstrec['SYEAR'] != '') {
                        $gradelevel = DBGet(DBQuery('SELECT sg.TITLE FROM school_gradelevels sg,student_enrollment se WHERE se.STUDENT_ID=' . $firstrec['STUDENT_ID'] . ' AND se.SCHOOL_ID=' . $firstrec['SCHOOL_ID'] . ' AND se.SYEAR=' . $firstrec['SYEAR'] . ' AND se.GRADE_ID=sg.ID ORDER BY se.ID DESC LIMIT 0,1'));
                        $gradelevel = $gradelevel[1]['TITLE'];
                    }
                    if ($gradelevel == '' && $firstrec['MP_SOURCE'] == 'History')
                        $gradelevel = ($firstrec['GRADELEVEL'] != '' ? $firstrec['GRADELEVEL'] : 'Not Found');

                    $course_html[$colnum] .= '<h4 class="f-s-15 m-b-0 m-t-0"><span class="text-blue">' . $firstrec[SCHOOL_NAME] . '</span> - ' . $firstrec['MP_NAME'] . ' (' . $gradelevel . ')</h4>';
                    $course_html[$colnum] .= '<p class="m-t-0 m-b-5">Posted Date : ' . $posted_arr[1] . '/' . $posted_arr[0] . '</p>';
                    $course_html[$colnum] .= '<table class="invoice-table table-bordered">';
                    $course_html[$colnum] .= '<thead>';
                    $course_html[$colnum] .= '<tr>';
                    $course_html[$colnum] .= '<th class="text-left f-s-12">Course</th><th class="text-left f-s-12" width="20%">Credit Hours</th><th class="text-left f-s-12" width="20%">Credits Earned</th><th class="bg-grey f-s-12" width="5%">Grade</th><th class="text-left f-s-12" width="15%">GP Value</th>';
                    $course_html[$colnum] .= '</tr>';
                    $course_html[$colnum] .= '</thead>';
                    $course_html[$colnum] .= '<tbody>';

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
                        $course_html[$colnum] .= '<tr>';
                        $course_html[$colnum] .= '<td>' . $trec['COURSE_NAME'] . '</td>';
                        $course_html[$colnum] .= '<td>' . sprintf("%01.2f", $trec['CREDIT_ATTEMPTED']) . '</td>';
                        $course_html[$colnum] .= '<td>' . sprintf("%01.2f", $trec['CREDIT_EARNED']) . '</td>';
                        $course_html[$colnum] .= '<td class="bg-grey f-s-16 text-center"><b>' . $gradeletter . '</b></td>';
                        $course_html[$colnum] .= '<td>' . sprintf("%01.2f", ($trec['CREDIT_EARNED'] * $gp_val)) . '</td>';
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

                    $course_html[$colnum] .= '</tbody>';
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

                    $course_html[$colnum] .= '<tfoot>';
                    $course_html[$colnum] .= '<tr>';
                    $course_html[$colnum] .= '<td colspan="5">';
                    if($cred_attempted!=0)
                    $course_html[$colnum] .= '<p class="text-blue f-s-13">Credit Attempted: ' . sprintf("%01.2f", $cred_attempted) . ' / Credit Earned: ' . sprintf("%01.2f", $cred_earned) . ' / GPA: ' .sprintf("%01.2f", ($totqp / $cred_attempted)) . '</p>';
                    else
                    $course_html[$colnum] .= '<p class="text-blue f-s-13">Credit Attempted: ' . sprintf("%01.2f", $cred_attempted) . ' / Credit Earned: ' . sprintf("%01.2f", $cred_earned) . ' / GPA: ' .sprintf("%01.2f", 0) . '</p>';    
                    $course_html[$colnum] .= '</td>';
                    $course_html[$colnum] .= '</tr>';
                    $course_html[$colnum] .= '</tfoot>';
                    $course_html[$colnum] .= '</table>';

                    unset($qtr_gpa);
                    unset($totqp);
                    $course_html[$colnum] .= '</div>';
                    if ($_REQUEST['template'] == 'two') {
                        if ($colnum == 0) {
                            $colnum = 1;
                        } else {
                            $colnum = 0;
                        }
                    }
                }
            }
            ?>
            <div class="print-wrapper">
                <div class="print-header m-b-10">
                    <div class="school-details">
                        <h2><?php echo $schoolinfo['TITLE']; ?></h2>
                        <b>Address :</b> <?php echo (($schoolinfo['ADDRESS'] != '') ? $schoolinfo['ADDRESS'] : '') . ' ' . (($schoolinfo['CITY'] != '') ? ', ' . $schoolinfo['CITY'] : '') . (($schoolinfo['STATE'] != '') ? ', ' . $schoolinfo['STATE'] : '') . (($schoolinfo['ZIPCODE'] != '') ? ', ' . $schoolinfo['ZIPCODE'] : '') ?>
                        <?php if ($schoolinfo['PHONE']) { ?>
                            <p><b>Phone :</b> <?php echo $schoolinfo['PHONE']; ?></p>
                        <?php } ?>
                    </div>
                    <div class="header-right">
                        <h4 class="title">TRANSCRIPT</h4>
                    </div>
                </div>
                <hr/>

                <div class="transcript-header m-t-10 m-b-20">
                    <div class="transcript-student-info f-s-15">
                        <h2 class="m-0"><?php echo $sinfo['LAST_NAME'] . ', ' . $sinfo['FIRST_NAME'] . ' ' . $sinfo['MIDDLE_NAME']; ?></h2>
                        <p class="m-t-5 m-b-0"><?php echo (($sinfo['ADDRESS'] != '') ? $sinfo['ADDRESS'] : '') . (($sinfo['CITY'] != '') ? ', ' . $sinfo['CITY'] : '') . (($sinfo['STATE'] != '') ? ', ' . $sinfo['STATE'] : '') . (($sinfo['ZIPCODE'] != '') ? ', ' . $sinfo['ZIPCODE'] : ''); ?></p>
                        <p class="m-t-5 m-b-0"><b>Date of Birth :</b> <?php echo str_replace('-', '/', $sinfo['BIRTHDATE']); ?></p>
                        <p class="m-t-5 m-b-0"><b>Student ID :</b><?php echo $student_id ?></p>
                        <p class="m-t-5 m-b-0"><b>Grade Level :</b> <?php echo $sinfo['GRADE_SHORT']; ?></p>
                    </div>
                    <div class="transcript-student-overview">
                        <table class="table">
                            <tr>
                                <td class="p-r-30">Cumulative GPA</td>
                                <td><?php echo $tot_qp != 0 ? sprintf("%01.2f", ($tot_qp / $total_CGPA_attemted)) : '0.00'; ?></td>
                            </tr>
                            <tr>
                                <td class="p-r-30">Total Credit Attempted</td>
                                <td><?php echo sprintf("%01.2f", $total_CGPA_attemted); ?></td>
                            </tr>
                            <tr>
                                <td class="p-r-30">Total Credit Earned</td>
                                <td><?php echo sprintf("%01.2f", $total_credit_earned); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>


                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="vertical-align: top; padding-right: 15px;" width="50%">
                            <?php echo $course_html[0]; ?>
                        </td>
                        <?php if ($_REQUEST['template'] == 'two') { ?>
                        <td style="vertical-align: top; padding-left: 15px;">
                                <?php echo $course_html[1]; ?>
                            </td>
                        <?php } ?>
                    </tr>
                </table>

<!--                <div class="transcript-columns <?php //echo (($_REQUEST['template'] == 'two') ? 'two-column' : ''); ?>">
                    <div class="column">
                        <?php //echo $course_html[0]; ?>
                    </div>
                    <?php if ($_REQUEST['template'] == 'two') { ?>
                        <div class="column">
                            <?php //echo $course_html[1]; ?>
                        </div>
                    <?php } ?>
                </div>-->

                <div class="text-right m-t-40">
                    <table width="100%">
                        <tr>
                            <td>
                                <table align="right" class="m-t-30">
                                    <tr>
                                        <td class="text-center p-b-40" style="border-top: 2px solid #333; width: 300px;">
                                            <i>Signature</i>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center" style="border-top: 2px solid #333; width: 300px;">
                                            <i>Title</i>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="page-break-before: always;">&nbsp;</div>


                <?php
                $grade_scale = DBGet(DBQuery('SELECT rcg.TITLE,rcg.GPA_VALUE, rcg.UNWEIGHTED_GP,rcg.COMMENT,rcgs.GP_SCALE FROM report_card_grade_scales rcgs,report_card_grades rcg
                                        WHERE rcg.grade_scale_id =rcgs.id and rcg.syear=\'' . $tsyear . '\' and rcg.school_id=\'' . UserSchool() . '\' ORDER BY rcg.SORT_ORDER'));

                $grade_scale_value = $grade_scale[1];
                ?>
                <div class="m-t-0">
                    <h3 class="m-b-5 m-t-0">GPA & CGPA based on a <?php echo $grade_scale_value['GP_SCALE']; ?>-point scale as follows:</h3>
                    <table class="invoice-table table-bordered">
                        <thead>                            
                            <tr>
                                <th class="text-center f-s-12">Grade Letter</th>
                                <th class="text-center f-s-12">Weighted Grade Points</th>
                                <th class="text-center f-s-12">Unweighted Grade Points</th>
                                <th class="text-center f-s-12">Comments</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grade_scale as $grade_scale_val) { ?>
                                <tr>
                                    <td class="text-center f-s-12"><?php echo $grade_scale_val['TITLE']; ?></td>
                                    <td class="text-center f-s-12"><?php echo $grade_scale_val['GPA_VALUE']; ?></td>
                                    <td class="text-center f-s-12"><?php echo $grade_scale_val['UNWEIGHTED_GP']; ?></td>
                                    <td class="text-center f-s-12"><?php echo $grade_scale_val['COMMENT']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div style="page-break-before: always;">&nbsp;</div>

                <?php
                $picturehtml = '';
                if ($_REQUEST['show_photo']) {
                    $stu_img_info = DBGet(DBQuery('SELECT * FROM user_file_upload WHERE USER_ID=' . $student_id . ' AND PROFILE_ID=3 AND SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear() . ' AND FILE_INFO=\'stuimg\''));
                    if (count($stu_img_info) > 0) {
                        $picturehtml = '<td valign="top" align="left" width=30%><img style="padding:4px; width:144px; border:1px solid #333333; background-color:#fff;" src="data:image/jpeg;base64,' . base64_encode($stu_img_info[1]['CONTENT']) . '"></td>';
                    } else {
                        $picturehtml = '<td valign="top" align="left" width=30%><img style="padding:4px; border:1px solid #333333; background-color:#fff;" src="assets/noimage.jpg"></td>';
                    }
                }



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




                echo '';
                echo '<!-- NEW PAGE -->';
                ?>

            </div>
            <?php
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
            echo '<div class="text-right p-b-20 p-r-20"><INPUT type=submit class="btn btn-primary" value=\'Create Transcripts for Selected Students\'></div>';
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
