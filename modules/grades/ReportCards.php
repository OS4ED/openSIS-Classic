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
include 'modules/grades/ConfigInc.php';
ini_set('max_execution_time', 5000);
ini_set('memory_limit', '12000M');

if (isset($_SESSION['student_id']) && $_SESSION['student_id'] != '') {
    $_REQUEST['search_modfunc'] = 'list';
}

if ($_REQUEST['modfunc'] == 'save') {
    $cur_session_RET = DBGet(DBQuery('SELECT YEAR(start_date) AS PRE,YEAR(end_date) AS POST FROM school_years WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''));
    if ($cur_session_RET[1]['PRE'] == $cur_session_RET[1]['POST']) {
        $cur_session = $cur_session_RET[1]['PRE'];
    } else {
        $cur_session = $cur_session_RET[1]['PRE'] . '-' . $cur_session_RET[1]['POST'];
    }

    if ((is_countable($_REQUEST['mp_arr']) && count($_REQUEST['mp_arr'])) && (is_countable($_REQUEST['st_arr']) && count($_REQUEST['st_arr']))) {
        //    if (count($_REQUEST['mp_arr']) && count($_REQUEST['unused'])) {
        $mp_list = '\'' . implode('\',\'', $_REQUEST['mp_arr']) . '\'';
        $last_mp = end($_REQUEST['mp_arr']);
        $st_list = '\'' . implode('\',\'', $_REQUEST['st_arr']) . '\'';
        //        $st_list = '\'' . implode('\',\'', $_REQUEST['unused']) . '\'';
        $extra['WHERE'] = ' AND s.STUDENT_ID IN (' . $st_list . ')';


        $extra['SELECT'] .= ',rc_cp.COURSE_WEIGHT,rpg.TITLE as GRADE_TITLE,sg1.GRADE_PERCENT,sg1.WEIGHTED_GP,sg1.UNWEIGHTED_GP ,sg1.CREDIT_ATTEMPTED , sg1.COMMENT as COMMENT_TITLE,sg1.STUDENT_ID,sg1.COURSE_PERIOD_ID,sg1.MARKING_PERIOD_ID,c.TITLE as COURSE_TITLE,rc_cp.TEACHER_ID AS TEACHER,sp.SORT_ORDER';

        if (($_REQUEST['elements']['period_absences'] == 'Y' && !$_REQUEST['elements']['grade_type']) || ($_REQUEST['elements']['period_absences'] == 'Y' && $_REQUEST['elements']['grade_type'] && $_REQUEST['elements']['percents']))
            $extra['SELECT'] .= ',cpv.DOES_ATTENDANCE,
				(SELECT count(*) FROM attendance_period ap,attendance_codes ac
					WHERE ac.ID=ap.ATTENDANCE_CODE AND ac.STATE_CODE=\'A\' AND ap.COURSE_PERIOD_ID=sg1.COURSE_PERIOD_ID AND ap.STUDENT_ID=ssm.STUDENT_ID) AS YTD_ABSENCES,
				(SELECT count(*) FROM attendance_period ap,attendance_codes ac
					WHERE ac.ID=ap.ATTENDANCE_CODE AND ac.STATE_CODE=\'A\' AND ap.COURSE_PERIOD_ID=sg1.COURSE_PERIOD_ID AND sg1.MARKING_PERIOD_ID=ap.MARKING_PERIOD_ID AND ap.STUDENT_ID=ssm.STUDENT_ID) AS MP_ABSENCES';
        if (($_REQUEST['elements']['gpa'] == 'Y' && !$_REQUEST['elements']['grade_type']) || ($_REQUEST['elements']['gpa'] == 'Y' && $_REQUEST['elements']['grade_type'] && $_REQUEST['elements']['percents']))
            $extra['SELECT'] .= ",sg1.weighted_gp as GPA";
        if (($_REQUEST['elements']['comments'] == 'Y' && !$_REQUEST['elements']['grade_type']) || ($_REQUEST['elements']['comments'] == 'Y' && $_REQUEST['elements']['grade_type'] && $_REQUEST['elements']['percents']))
            $extra['SELECT'] .= ',s.gender AS GENDER,s.common_name AS NICKNAME';

        $extra['FROM'] .= ',student_report_card_grades sg1 LEFT OUTER JOIN report_card_grades rpg ON (rpg.ID=sg1.REPORT_CARD_GRADE_ID),
					course_periods rc_cp,course_period_var cpv,courses c,school_periods sp,schools sc ';


        $extra['WHERE'] .= ' AND sg1.MARKING_PERIOD_ID IN (' . $mp_list . ')
					AND rc_cp.COURSE_PERIOD_ID=sg1.COURSE_PERIOD_ID AND c.COURSE_ID = rc_cp.COURSE_ID AND sg1.STUDENT_ID=ssm.STUDENT_ID AND cpv.COURSE_PERIOD_ID=rc_cp.COURSE_PERIOD_ID AND sp.PERIOD_ID=cpv.PERIOD_ID
                                                                                           AND sc.ID=sg1.SCHOOL_ID';

        $extra['ORDER'] .= ',sp.SORT_ORDER,c.TITLE';
        $extra['functions']['TEACHER'] = '_makeTeacher';
        $extra['group'] = array('STUDENT_ID', 'COURSE_PERIOD_ID', 'MARKING_PERIOD_ID');
        $RET = GetStuList($extra);
        if (($_REQUEST['elements']['comments'] == 'Y') || ($_REQUEST['elements']['comments'] == 'Y' && $_REQUEST['elements']['percents'])) {
            // GET THE COMMENTS
            unset($extra);
            $extra['WHERE'] = ' AND s.STUDENT_ID IN (' . $st_list . ')';
            $extra['SELECT_ONLY'] = 's.STUDENT_ID,sc.COURSE_PERIOD_ID,sc.MARKING_PERIOD_ID,sc.REPORT_CARD_COMMENT_ID,sc.COMMENT,(SELECT SORT_ORDER FROM report_card_comments WHERE ID=sc.REPORT_CARD_COMMENT_ID) AS SORT_ORDER';
            $extra['FROM'] = ',student_report_card_comments sc';
            $extra['WHERE'] .= ' AND sc.STUDENT_ID=s.STUDENT_ID AND sc.MARKING_PERIOD_ID=\'' . $last_mp . '\'';
            $extra['ORDER_BY'] = 'SORT_ORDER';
            $extra['group'] = array('STUDENT_ID', 'COURSE_PERIOD_ID', 'MARKING_PERIOD_ID');
            $comments_RET = GetStuList($extra);


            $all_commentsA_RET = DBGet(DBQuery('SELECT ID,TITLE,SORT_ORDER FROM report_card_comments WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' AND COURSE_ID IS NOT NULL AND COURSE_ID=\'0\' ORDER BY SORT_ORDER,ID'), array(), array('ID'));
            $commentsA_RET = DBGet(DBQuery('SELECT ID,TITLE,SORT_ORDER FROM report_card_comments WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' AND COURSE_ID IS NOT NULL AND COURSE_ID!=\'0\''), array(), array('ID'));
            $commentsB_RET = DBGet(DBQuery('SELECT ID,TITLE,SORT_ORDER FROM report_card_comments WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' AND COURSE_ID IS NULL'), array(), array('ID'));
        }
        if ((($_REQUEST['elements']['mp_tardies'] == 'Y' || $_REQUEST['elements']['ytd_tardies'] == 'Y') && !$_REQUEST['elements']['grade_type']) || (($_REQUEST['elements']['mp_tardies'] == 'Y' || $_REQUEST['elements']['ytd_tardies'] == 'Y') && $_REQUEST['elements']['grade_type'] && $_REQUEST['elements']['percents'])) {
            // GET THE ATTENDANCE
            unset($extra);
            $extra['WHERE'] = ' AND s.STUDENT_ID IN (' . $st_list . ')';
            $extra['SELECT_ONLY'] = 'ap.SCHOOL_DATE,ap.COURSE_PERIOD_ID,ac.ID AS ATTENDANCE_CODE,ap.MARKING_PERIOD_ID,ssm.STUDENT_ID';
            $extra['FROM'] = ',attendance_codes ac,attendance_period ap';
            $extra['WHERE'] .= ' AND ac.ID=ap.ATTENDANCE_CODE AND (ac.DEFAULT_CODE!=\'Y\' OR ac.DEFAULT_CODE IS NULL) AND ac.SYEAR=ssm.SYEAR AND ap.STUDENT_ID=ssm.STUDENT_ID';
            $extra['group'] = array('STUDENT_ID', 'ATTENDANCE_CODE', 'MARKING_PERIOD_ID');
            $attendance_RET = GetStuList($extra);
        }
        if ((($_REQUEST['elements']['mp_absences'] == 'Y' || $_REQUEST['elements']['ytd_absences'] == 'Y') && !$_REQUEST['elements']['grade_type']) || (($_REQUEST['elements']['mp_absences'] == 'Y' || $_REQUEST['elements']['ytd_absences'] == 'Y') && $_REQUEST['elements']['grade_type'] && $_REQUEST['elements']['percents'])) {
            // GET THE DAILY ATTENDANCE
            unset($extra);
            $extra['WHERE'] = ' AND s.STUDENT_ID IN (' . $st_list . ')';
            $extra['SELECT_ONLY'] = 'ad.SCHOOL_DATE,ad.MARKING_PERIOD_ID,ad.STATE_VALUE,ssm.STUDENT_ID';
            $extra['FROM'] = ',attendance_day ad';
            $extra['WHERE'] .= ' AND ad.STUDENT_ID=ssm.STUDENT_ID AND ad.SYEAR=ssm.SYEAR AND (ad.STATE_VALUE=\'0.0\' OR ad.STATE_VALUE=\'.5\') AND ad.SCHOOL_DATE<=\'' . GetMP($last_mp, 'END_DATE') . '\'';
            $extra['group'] = array('STUDENT_ID', 'MARKING_PERIOD_ID');
            $attendance_day_RET = GetStuList($extra);
        }


        if (count($RET)) {
            $columns = array('COURSE_TITLE' => _course);
            if ($_REQUEST['elements']['teacher'] == 'Y')
                $columns += array('TEACHER' => _teacher);
            if ($_REQUEST['elements']['period_absences'] == 'Y')
                $columns += array('ABSENCES' => 'Abs<BR>YTD / MP');
            if (count($_REQUEST['mp_arr']) > 4)
                $mp_TITLE = 'SHORT_NAME';
            else
                $mp_TITLE = 'TITLE';
            foreach ($_REQUEST['mp_arr'] as $mp)
                $columns[$mp] = GetMP($mp, $mp_TITLE);
            if ($_REQUEST['elements']['comments'] == 'Y') {  //for standard grade
                foreach ($all_commentsA_RET as $comment)
                    $columns['C' . $comment[1]['ID']] = $comment[1]['TITLE'];
                $columns['COMMENT'] = 'Comment';
            }
            if ($_REQUEST['elements']['gpa'] == 'Y')
                $columns['GPA'] = 'GPA';
            //start of report card print

            $handle = PDFStart();
            $total_stu = 1;
            if (!isset($_REQUEST['elements']['percents']) || (isset($_REQUEST['elements']['percents']) && $_REQUEST['elements']['percents'] == 'Y')) {
                foreach ($RET as $student_id => $course_periods) {

                    echo "<table width=100%  style=\" font-family:Arial; font-size:12px;\" >";
                    echo "<tr><td width=105>" . DrawLogo() . "</td><td  style=\"font-size:15px; font-weight:bold; padding-top:20px;\">" . GetSchool(UserSchool()) . ' (' . $cur_session . ')' . "<div style=\"font-size:12px;\">" . _studentReportCard . "</div></td><td align=right style=\"padding-top:20px\">" . ProperDate(DBDate()) . "<br \>" . _poweredByOpenSis . "</td></tr><tr><td colspan=3 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";
                    echo '<!-- MEDIA SIZE 8.5x11in -->';
                    if (!isset($_REQUEST['elements']['percents']) || (isset($_REQUEST['elements']['percents']) && $_REQUEST['elements']['percents'] == 'Y')) {   //when Standard Grade is not selected
                        $comments_arr = array();
                        $comments_arr_key = (is_countable($all_commentsA_RET) ? count($all_commentsA_RET) : 0) > 0;
                        unset($grades_RET);
                        $i = 0;
                        $total_grade_point = 0;
                        $Total_Credit_Hr_Attempted = 0;
                        $commentc = '';
                        foreach ($course_periods as $course_period_id => $mps) {
                            $i++;
                            //$commentc=$mps[key($mps)][1]['COMMENT_TITLE'];
                            $commentc = $mps[$last_mp][1]['COMMENT_TITLE'];
                            $grades_RET[$i]['COURSE_TITLE'] = $mps[key($mps)][1]['COURSE_TITLE'];
                            $grades_RET[$i]['TEACHER'] = $mps[key($mps)][1]['TEACHER'];
                            $grades_RET[$i]['TEACHER_ID'] = $mps[key($mps)][1]['TEACHER_ID'];
                            $grades_RET[$i]['CGPA'] = round($mps[key($mps)][1]['UNWEIGHTED_GPA'], 3);
                            if ($mps[key($mps)][1]['WEIGHTED_GP'] && $mps[key($mps)][1]['COURSE_WEIGHT']) {
                                if (substr(key($mps), 0, 1) == 'E')
                                    $mpkey = substr(key($mps), 1);
                                else
                                    $mpkey = key($mps);
                                $total_grade_point += ($mps[$mpkey][1]['WEIGHTED_GP'] * $mps[$mpkey][1]['CREDIT_ATTEMPTED']);
                                $Total_Credit_Hr_Attempted += $mps[$mpkey][1]['CREDIT_ATTEMPTED'];
                            } elseif ($mps[key($mps)][1]['UNWEIGHTED_GP']) {
                                if (substr(key($mps), 0, 1) == 'E')
                                    $mpkey = substr(key($mps), 1);
                                else
                                    $mpkey = key($mps);
                                $total_grade_point += ($mps[$mpkey][1]['UNWEIGHTED_GP'] * $mps[$mpkey][1]['CREDIT_ATTEMPTED']);
                                $Total_Credit_Hr_Attempted += $mps[$mpkey][1]['CREDIT_ATTEMPTED'];
                            }

                            if ($_REQUEST['elements']['gpa'] == 'Y')
                                $grades_RET[$i]['GPA'] = ($Total_Credit_Hr_Attempted != 0 && $total_grade_point != 0 ? sprintf("%01.3f", ($total_grade_point / $Total_Credit_Hr_Attempted)) : 0);
                            $total_grade_point = 0;
                            $To_Credit_Hr_Attempted += $Total_Credit_Hr_Attempted;
                            $to_Credit_hr_attempt[$student_id] = $To_Credit_Hr_Attempted;
                            $Total_Credit_Hr_Attempted = 0;

                            foreach ($_REQUEST['mp_arr'] as $mp) {
                                $total_p1 = 0;

                                if ($mps[$mp]) {


                                    $dbf = DBGet(DBQuery('SELECT DOES_BREAKOFF,GRADE_SCALE_ID,TEACHER_ID FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $course_period_id . '\''));
                                    $rounding = DBGet(DBQuery('SELECT VALUE FROM program_user_config WHERE USER_ID=\'' . $dbf[1]['TEACHER_ID'] . '\' AND TITLE=\'ROUNDING\' AND PROGRAM=\'Gradebook\' AND VALUE LIKE \'%_' . $course_period_id . '\''));
                                    //                                               if(count($config_RET))
                                    //			foreach($config_RET as $title=>$value)
                                    //                        {
                                    //                                $unused_var=explode('_',$value[1]['VALUE']);
                                    //                                $programconfig[$staff_id][$title] =$unused_var[0];
                                    ////				$programconfig[$staff_id][$title] = rtrim($value[1]['VALUE'],'_'.$course_period_id);
                                    //                        }
                                    //		else
                                    //			$programconfig[$staff_id] = true;


                                    if (count($rounding)) {
                                        $unused_var = explode('_', $rounding[1]['VALUE']);


                                        $_SESSION['ROUNDING'] = $unused_var[0];
                                    }
                                    //$_SESSION['ROUNDING']=rtrim($rounding[1]['VALUE'],'_'.UserCoursePeriod());
                                    else
                                        $_SESSION['ROUNDING'] = '';
                                    if ($_SESSION['ROUNDING'] == 'UP')
                                        $mps[$mp][1]['GRADE_PERCENT'] = ceil($mps[$mp][1]['GRADE_PERCENT']);
                                    elseif ($_SESSION['ROUNDING'] == 'DOWN')
                                        $mps[$mp][1]['GRADE_PERCENT'] = floor($mps[$mp][1]['GRADE_PERCENT']);
                                    elseif ($_SESSION['ROUNDING'] == 'NORMAL')
                                        $mps[$mp][1]['GRADE_PERCENT'] = round($mps[$mp][1]['GRADE_PERCENT']);
                                    if ($dbf[1]['DOES_BREAKOFF'] == 'Y' && $mps[$mp][1]['GRADE_PERCENT'] !== '' && $mps[$mp][1]['GRADE_PERCENT'] !== NULL) {
                                        $tc_grade = 'n';
                                        $get_details = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE TITLE LIKE \'' . $course_period_id . '-%' . '\' AND USER_ID=\'' . $grades_RET[$i]['TEACHER_ID'] . '\' AND PROGRAM=\'Gradebook\' AND VALUE LIKE \'%_' . UserCoursePeriod() . '\' ORDER BY VALUE DESC '));
                                        if (count($get_details)) {
                                            unset($id_mod);
                                            foreach ($get_details as $i_mod => $d_mod) {
                                                $unused_var = explode('_', $d_mod['VALUE']);
                                                if ($mps[$mp][1]['GRADE_PERCENT'] >= $unused_var[0] && !isset($id_mod)) {
                                                    $id_mod = $i_mod;
                                                }
                                            }
                                            $grade_id_mod = explode('-', $get_details[$id_mod]['TITLE']);

                                            $grades_RET[$i][$mp] = _makeLetterGrade($mps[$mp][1]['GRADE_PERCENT'] / 100, $course_period_id, $dbf[1]['TEACHER_ID'], "") . '&nbsp;';
                                            $tc_grade = 'y';
                                        }
                                        if ($tc_grade == 'n')
                                            $grades_RET[$i][$mp] = _makeLetterGrade($mps[$mp][1]['GRADE_PERCENT'] / 100, $course_period_id, $dbf[1]['TEACHER_ID'], "") . '&nbsp;';
                                    } else {
                                        if ($mps[$mp][1]['GRADE_PERCENT'] != NULl)
                                            $grades_RET[$i][$mp] = _makeLetterGrade($mps[$mp][1]['GRADE_PERCENT'] / 100, $course_period_id, $dbf[1]['TEACHER_ID'], "") . '&nbsp;';
                                    }

                                    if ($_REQUEST['elements']['percents'] == 'Y' && $mps[$mp][1]['GRADE_PERCENT'] > 0) {

                                        if ($mps[$mp][1]['GRADE_PERCENT'] != NULl) {


                                            //                                                        if($_SESSION['ROUNDING']=='UP')
                                            //                                                            $mps[$mp][1]['GRADE_PERCENT'] = ceil($mps[$mp][1]['GRADE_PERCENT']);
                                            //                                                    elseif($_SESSION['ROUNDING']=='DOWN')
                                            //                                                            $mps[$mp][1]['GRADE_PERCENT'] = floor($mps[$mp][1]['GRADE_PERCENT']);
                                            //                                                    elseif($_SESSION['ROUNDING']=='NORMAL')
                                            //                                                            $mps[$mp][1]['GRADE_PERCENT'] = round($mps[$mp][1]['GRADE_PERCENT']);
                                            $grades_RET[$i][$mp] .= '<br>' . $mps[$mp][1]['GRADE_PERCENT'] . '%';
                                        }

                                        //                                                
                                    }
                                    $last_mp = $mp;
                                }
                            }
                            if ($_REQUEST['elements']['period_absences'] == 'Y')
                                if ($mps[$last_mp][1]['DOES_ATTENDANCE'])
                                    $grades_RET[$i]['ABSENCES'] = $mps[$last_mp][1]['YTD_ABSENCES'] . ' / ' . $mps[$last_mp][1]['MP_ABSENCES'];
                                else
                                    $grades_RET[$i]['ABSENCES'] = 'n/a';
                            if ($_REQUEST['elements']['comments'] == 'Y') {
                                $sep = '';
                                foreach ($comments_RET[$student_id][$course_period_id][$last_mp] as $comment) {
                                    if ($all_commentsA_RET[$comment['REPORT_CARD_COMMENT_ID']])
                                        $grades_RET[$i]['C' . $comment['REPORT_CARD_COMMENT_ID']] = $comment['COMMENT'] != ' ' ? $comment['COMMENT'] : '&middot;';
                                    else {
                                        if ($commentsA_RET[$comment['REPORT_CARD_COMMENT_ID']]) {
                                            $grades_RET[$i]['COMMENT'] .= $sep . $commentsA_RET[$comment['REPORT_CARD_COMMENT_ID']][1]['SORT_ORDER'];
                                            $grades_RET[$i]['COMMENT'] .= '(' . ($comment['COMMENT'] != ' ' ? $comment['COMMENT'] : '&middot;') . ')';
                                            $comments_arr_key = true;
                                        } else
                                            $grades_RET[$i]['COMMENT'] .= $sep . $commentsB_RET[$comment['REPORT_CARD_COMMENT_ID']][1]['SORT_ORDER'];
                                        $sep = ', ';
                                        $comments_arr[$comment['REPORT_CARD_COMMENT_ID']] = $comment['SORT_ORDER'];
                                    }
                                }
                                if ($commentc != '')
                                    $grades_RET[$i]['COMMENT'] .= $sep . $commentc;
                                //if ($mps[$last_mp][1]['COMMENT_TITLE'])
                                //   $grades_RET[$i]['COMMENT'] .= $sep . $mps[$last_mp][1]['COMMENT_TITLE'];
                            }
                        }
                        asort($comments_arr, SORT_NUMERIC);

                        $addresses = array(0 => array());

                        foreach ($addresses as $address) {
                            unset($_openSIS['DrawHeader']);

                            echo '<table border=0>';
                            if ($_REQUEST['elements']['incl_picture'] == 'Y') {
                                $picture_c_jpg = $StudentPicturesPath . $mps[key($mps)][1]['STUDENT_ID'] . '.JPG';
                                $picture_l_jpg = $StudentPicturesPath . $mps[key($mps)][1]['STUDENT_ID'] . '.jpg';
                                if (file_exists($picture_c_jpg) || file_exists($picture_l_jpg))
                                    echo '<tr><td><IMG SRC="' . $picture_c_jpg . '" width=100 class=pic></td></tr>';
                                else
                                    echo '<tr><td><IMG src="assets/noimage.jpg" width=100 class=pic></td></tr>';
                            }
                            echo '<tr><td><strong>' . _studentName . ' :</strong></td>';
                            echo '<td>' . $mps[key($mps)][1]['FULL_NAME'] . '</td></tr>';
                            echo '<tr><td><strong>' . _studentId . ' :</strong></td>';
                            echo '<td>' . $mps[key($mps)][1]['STUDENT_ID'] . '</td></tr>';
                            echo '<tr><td><strong>' . _alternateId . ' :</strong></td>';
                            echo '<td>' . $mps[key($mps)][1]['ALT_ID'] . '</td></tr>';
                            echo '<tr><td><strong>' . _studentGrade . ' :</strong></td>';
                            echo '<td>' . $mps[key($mps)][1]['GRADE_ID'] . '</td></tr>';
                            echo '</table>';


                            $count_lines = 3;
                            if ($_REQUEST['elements']['mp_absences'] == 'Y') {
                                $count = 0;
                                foreach ($attendance_day_RET[$student_id][$last_mp] as $abs)
                                    $count += 1 - $abs['STATE_VALUE'];
                                $mp_absences = '<strong>' . _dailyAbsencesThis . ' ' . GetMP($last_mp, 'TITLE') . ' :</strong> ' . $count;
                            }
                            if ($_REQUEST['elements']['ytd_absences'] == 'Y') {
                                $count = 0;
                                foreach ($attendance_day_RET[$student_id] as $mp_abs)
                                    foreach ($mp_abs as $abs)
                                        $count += 1 - $abs['STATE_VALUE'];
                                echo '<br/><table width="100%" border="0" cellspacing="0"><tr>';
                                echo '<td><strong>' . _yearToDateDailyAbsences . ' :</strong> ' . $count . '</td>';
                                echo '<td align="right">' . $mp_absences . '</td>';
                                echo '</tr></table><br/>';
                                $count_lines++;
                            } elseif ($_REQUEST['elements']['mp_absences'] == 'Y') {
                                DrawHeader($mp_absences);
                                $count_lines++;
                            }

                            if ($_REQUEST['elements']['mp_tardies'] == 'Y') {
                                $attendance_title = DBGet(DBQuery("SELECT TITLE FROM attendance_codes WHERE id='" . $_REQUEST['mp_tardies_code'] . "'"));
                                $attendance_title = $attendance_title[1]['TITLE'];
                                $count = 0;
                                foreach ($attendance_RET[$student_id][$_REQUEST['mp_tardies_code']][$last_mp] as $abs)
                                    $count++;
                                $mp_tardies = $attendance_title . ' in ' . GetMP($last_mp, 'TITLE') . ': ' . $count;
                            }
                            if ($_REQUEST['elements']['ytd_tardies'] == 'Y') {
                                $attendance_title = DBGet(DBQuery("SELECT TITLE FROM attendance_codes WHERE id='" . $_REQUEST['ytd_tardies_code'] . "'"));
                                $attendance_title = $attendance_title[1]['TITLE'];
                                $count = 0;
                                foreach ($attendance_RET[$student_id][$_REQUEST['ytd_tardies_code']] as $mp_abs)
                                    foreach ($mp_abs as $abs)
                                        $count++;
                                DrawHeader($attendance_title . ' this year: ' . $count, $mp_tardies);
                                $count_lines++;
                            } elseif ($_REQUEST['elements']['mp_tardies'] == 'Y') {
                                DrawHeader($mp_tardies);
                                $count_lines++;
                            }
                            ListOutputPrint($grades_RET, $columns, '', '', array(), array(), array('print' => false));

                            if ($_REQUEST['elements']['comments'] == 'Y' && ($comments_arr_key || count($comments_arr))) {
                                $gender = substr($mps[key($mps)][1]['GENDER'], 0, 1);
                                $personalizations = array(
                                    '^n' => ($mps[key($mps)][1]['NICKNAME'] ? $mps[key($mps)][1]['NICKNAME'] : $mps[key($mps)][1]['FIRST_NAME']),
                                    '^s' => ($gender == 'M' ? 'his' : ($gender == 'F' ? 'her' : 'his/her'))
                                );

                                echo '<TABLE width=100%><TR><TD colspan=2><b>' . _explanationOfCommentCodes . '</b></TD>';
                                $i = 0;
                                if ($comments_arr_key)
                                    foreach ($commentsA_select as $key => $comment) {
                                        if ($i++ % 3 == 0)
                                            echo '</TR><TR valign=top>';
                                        echo '<TD>(' . ($key != ' ' ? $key : '&middot;') . '): ' . $comment[2] . '</TD>';
                                    }
                                foreach ($comments_arr as $comment => $so) {
                                    if ($i++ % 3 == 0)
                                        echo '</TR><TR valign=top>';
                                    if ($commentsA_RET[$comment])
                                        echo '<TD width=33%><small>' . $commentsA_RET[$comment][1]['SORT_ORDER'] . ': ' . str_replace(array_keys($personalizations), $personalizations, $commentsA_RET[$comment][1]['TITLE']) . '</small></TD>';
                                    else
                                        echo '<TD width=33%><small>' . $commentsB_RET[$comment][1]['SORT_ORDER'] . ': ' . str_replace(array_keys($personalizations), $personalizations, $commentsB_RET[$comment][1]['TITLE']) . '</small></TD>';
                                }
                                echo '</TR></TABLE>';
                            }
                            if ($_REQUEST['elements']['signature'] == 'Y') {
                                echo '<br/>';
                                echo '<span style="font-size:13px; font-weight:bold;"></span>';
                                echo '<br/><br/><br/>';
                                echo '<table width="100%" border=0>';
                                echo '<tr><td align="left" width=190><span style="font-size:13px; font-weight:bold; height:30px;">Teacher&rsquo;s Signature</span><br/><br/><br/><br/></td><td width="5" valign=top>:</td><td align=left  valign=top>______________________________________</td><td valign=top align=left><span style="font-size:13px; font-weight:bold; height:30px;">Date : ______________</span></td></tr>';
                                echo '<tr><td align=left><span style="font-size:13px; font-weight:bold; height:30px;">Principal&rsquo;s Signature</span><br/><br/><br/><br/></td><td  valign=top>:</td><td  valign=top>______________________________________</td>';
                                echo '<td valign=top align=left><span style="font-size:13px; font-weight:bold; height:30px;">Date : ______________</span></td></tr>';
                                echo '<tr><td align="left"><span style="font-size:13px; font-weight:bold; height:30px;">Parent/Guardian&rsquo;s Signature</span></td><td  valign=top>:</td><td valign=top>______________________________________</td><td valign=top align=left><span style="font-size:13px; font-weight:bold; height:30px;">Date : ______________</span></td></tr>';
                                echo '</table>';
                                echo '<table width="100%"><tr><td style="font-style:italic; font-size:12px;"></td></tr></table>';
                            }
                            echo '<br/><br/>';
                            if (!$_REQUEST['elements']['grade_type']) {
                                if ($total_stu < count($RET)) {
                                    echo '<span style="font-size:13px; font-weight:bold;"></span>';
                                    echo '<!-- NEW PAGE -->';
                                    echo "<div style=\"page-break-before: always;\"></div>";
                                }
                            }
                        }
                    }
                }
            }

            #################end####################################### 
            PDFStop($handle);
        } else
            BackPrompt(_missingGradesOrNoStudentsWereFound);
    } else

        BackPrompt(_youMustChooseAtLeastOneStudentAndMarkingPeriod);
}

if (!$_REQUEST['modfunc']) {
    DrawBC("" . _gradebook . " > " . ProgramTitle());

    if ($_REQUEST['search_modfunc'] == 'list') {
        echo "<FORM action=ForExport.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=save&include_inactive=" . strip_tags(trim($_REQUEST['include_inactive'])) . "&_openSIS_PDF=true&head_html=Student+Report+Card method=POST target=_blank>";


        $attendance_codes = DBGet(DBQuery("SELECT SHORT_NAME,ID FROM attendance_codes WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "' AND (DEFAULT_CODE!='Y' OR DEFAULT_CODE IS NULL) AND TABLE_NAME='0'"));

        $extra['extra_header_left'] = '<h5 class="text-primary no-margin-top">' . _includeOnReportCard . ':</h5>';
        $extra['extra_header_left'] .= '<div class="row">';
        $extra['extra_header_left'] .= '<div class="col-md-6 col-lg-4"><div class="form-group"><div class="checkbox checkbox-switch switch-success switch-xs"><label><INPUT type=checkbox name=elements[teacher] value=Y CHECKED><span></span>' . _teacher . '</label></div></div></div>';
        $extra['extra_header_left'] .= '<div class="col-md-6 col-lg-4"><div class="form-group"><div class="checkbox checkbox-switch switch-success switch-xs"><label><INPUT type=checkbox name=elements[signature] value=Y><span></span>' . _includeSignatureLine . '</label></div></div></div>';
        $extra['extra_header_left'] .= '<div class="col-md-6 col-lg-4"><div class="form-group"><div class="checkbox checkbox-switch switch-success switch-xs"><label><INPUT type=checkbox name=elements[comments] value=Y CHECKED><span></span>' . _comments . '</label></div></div></div>';
        $extra['extra_header_left'] .= '<div class="col-md-6 col-lg-4"><div class="form-group"><div class="checkbox checkbox-switch switch-success switch-xs"><label><INPUT type=checkbox name=elements[percents] value=Y><span></span>' . _percents . '</label></div></div></div>';
        $extra['extra_header_left'] .= '<div class="col-md-6 col-lg-4"><div class="form-group"><div class="checkbox checkbox-switch switch-success switch-xs"><label><INPUT type=checkbox name=elements[ytd_absences] value=Y CHECKED><span></span>' . _yearToDateDailyAbsences . '</label></div></div></div>';
        $extra['extra_header_left'] .= '<div class="col-md-6 col-lg-4"><div class="form-group"><div class="checkbox checkbox-switch switch-success switch-xs"><label><INPUT type=checkbox name=elements[mp_absences] value=Y' . (GetMP(UserMP(), 'SORT_ORDER') != 1 ? ' CHECKED' : '') . '><span></span>' . _dailyAbsencesThisMarkingPeriod . '</label></div></div></div>';
        $extra['extra_header_left'] .= '<div class="col-md-6 col-lg-4 form-inline"><div class="form-group"><div class="checkbox checkbox-switch switch-success switch-xs"><label><INPUT type=checkbox name=elements[ytd_tardies] value=Y><span></span>' . _otherAttendanceYearToDate . ' :</label></div> <SELECT name="ytd_tardies_code" class="form-control input-xs">';
        foreach ($attendance_codes as $code)
            $extra['extra_header_left'] .= '<OPTION value=' . $code['ID'] . '>' . $code['SHORT_NAME'] . '</OPTION>';
        $extra['extra_header_left'] .= '</SELECT></div></div>';
        $extra['extra_header_left'] .= '<div class="col-md-6 col-lg-4 form-inline"><div class="form-group"><div class="checkbox checkbox-switch switch-success switch-success switch-xs"><label><INPUT type=checkbox name=elements[mp_tardies] value=Y><span></span>' . _otherAttendanceThisMarkingPeriod . ':</label></div> <SELECT class="form-control input-xs" name="mp_tardies_code">';
        foreach ($attendance_codes as $code)
            $extra['extra_header_left'] .= '<OPTION value=' . $code['ID'] . '>' . $code['SHORT_NAME'] . '</OPTION>';
        $extra['extra_header_left'] .= '</SELECT></div></div>';
        $extra['extra_header_left'] .= '<div class="col-md-6 col-lg-4"><div class="form-group"><div class="checkbox checkbox-switch switch-success switch-xs"><label><INPUT type=checkbox name=elements[period_absences] value=Y><span></span>' . _periodByPeriodAbsences . '</label></div></div></div>';
        $extra['extra_header_left'] .= '<div class="col-md-6 col-lg-4"><div class="form-group"><div class="checkbox checkbox-switch switch-success switch-xs"><label><INPUT type=checkbox name=elements[gpa] value=Y><span></span>' . _gpa . '</label></div></div></div>';
        $extra['extra_header_left'] .= '</div>';

        $mps_RET = DBGet(DBQuery("SELECT SEMESTER_ID,MARKING_PERIOD_ID,SHORT_NAME FROM school_quarters WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "' ORDER BY SORT_ORDER"), array(), array('SEMESTER_ID'));

        if (!$mps_RET) {
            $mps_RET = DBGet(DBQuery("SELECT YEAR_ID,MARKING_PERIOD_ID,SHORT_NAME FROM school_semesters WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "' ORDER BY SORT_ORDER"), array(), array('MARKING_PERIOD_ID'));
        }

        if (!$mps_RET) {
            $mps_RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,SHORT_NAME FROM school_years WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "' ORDER BY SORT_ORDER"), array(), array('MARKING_PERIOD_ID'));
        }

        $extra['extra_header_left'] .= '<h5 class="text-primary">' . _markingPeriods . '</h5>';
        $extra['extra_header_left'] .= '<div class="form-group">';
        foreach ($mps_RET as $sem => $quarters) {

            foreach ($quarters as $qtr) {
                $qtr1 = $qtr['MARKING_PERIOD_ID'];
                $pro = GetChildrenMP('PRO', $qtr['MARKING_PERIOD_ID']);
                if ($pro) {
                    $pros = explode(',', str_replace("'", '', $pro));
                    foreach ($pros as $pro)
                        if (GetMP($pro, 'DOES_GRADES') == 'Y')
                            $extra['extra_header_left'] .= '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=mp_arr[] value=' . $pro . ' onclick="reportCardGpaChk();">' . GetMP($pro, 'SHORT_NAME') . '</label>';
                }
                $extra['extra_header_left'] .= '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=mp_arr[] value=' . $qtr['MARKING_PERIOD_ID'] . ' onclick="reportCardGpaChk();">' . $qtr['SHORT_NAME'] . '</label>';

                if (GetMP($qtr1, 'DOES_EXAM') == 'Y')
                    $extra['extra_header_left'] .= '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=mp_arr[] value=E' . $qtr1 . ' onclick="reportCardGpaChk();">' . GetMP($qtr1, 'SHORT_NAME') . ' Exam</label>';
            }
            if (GetMP($sem, 'DOES_EXAM') == 'Y')
                $extra['extra_header_left'] .= '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=mp_arr[] value=E' . $sem . ' onclick="reportCardGpaChk();">' . GetMP($sem, 'SHORT_NAME') . ' Exam</label>';
            if (GetMP($sem, 'DOES_GRADES') == 'Y' && $sem != $quarters[1]['MARKING_PERIOD_ID'])
                $extra['extra_header_left'] .= '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=mp_arr[] value=' . $sem . ' onclick="reportCardGpaChk();">' . GetMP($sem, 'SHORT_NAME') . '</label>';
        }
        if ($sem) {
            $fy = GetParentMP('FY', $sem);
            if (GetMP($fy, 'DOES_EXAM') == 'Y')
                $extra['extra_header_left'] .= '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=mp_arr[] value=E' . $fy . ' onclick="reportCardGpaChk();">' . GetMP($fy, 'SHORT_NAME') . ' Exam</label>';
            if (GetMP($fy, 'DOES_GRADES') == 'Y')
                $extra['extra_header_left'] .= '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=mp_arr[] value=' . $fy . ' onclick="reportCardGpaChk();">' . GetMP($fy, 'SHORT_NAME') . '</label>';
        }
        $extra['extra_header_left'] .= '</div>';

        $extra['extra_header_left'] .= $extra['search'];
        $extra['search'] = '';
    }

    $extra['link'] = array('FULL_NAME' => false);
    $extra['SELECT'] = ",s.STUDENT_ID AS CHECKBOX";
    if (isset($_SESSION['student_id']) && $_SESSION['student_id'] != '') {
        $extra['WHERE'] .= ' AND s.STUDENT_ID=' . $_SESSION['student_id'];
    }
    $extra['functions'] = array('CHECKBOX' => '_makeChooseCheckbox');
    //    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller checked onclick="checkAll(this.form,this.form.controller.checked,\'st_arr\');"><A>');
    // $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAll(this.form,this.form.controller.checked,\'st_arr\');"><A>');
    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAllDtMod(this,\'st_arr\');"><A>');
    $extra['options']['search'] = false;
    $extra['new'] = true;

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
    $extra['search'] .= '<div class="well mb-20 pt-5 pb-5">';
    Widgets('class_rank');
    $extra['search'] .= '</div>'; //.well
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '</div>'; //.row

    // echo "<pre><xmp>";
    // print_r($extra);
    // echo "</xmp></pre>";

    Search('student_id', $extra, 'true');
    if ($_REQUEST['search_modfunc'] == 'list') {
        if ($_SESSION['count_stu'] != 0)
            echo '<div class="text-right p-b-20 p-r-20"><INPUT type=submit class="btn btn-primary" value=\'' . _createReportCardsForSelectedStudents . '\'></div>';
        echo "</FORM>";
    }
}
$modal_flag = 1;
if ($_REQUEST['modname'] == 'grades/ReportCards.php' && $_REQUEST['modfunc'] == 'save')
    $modal_flag = 0;
if ($modal_flag == 1) {
    echo '<div id="modal_default" class="modal fade">';
    echo '<div class="modal-dialog modal-lg">';
    echo '<div class="modal-content">';
    echo '<div class="modal-header">';
    echo '<button type="button" class="close" data-dismiss="modal">×</button>';
    echo '<h4 class="modal-title">' . _chooseCourse . '</h4>';
    echo '</div>';

    echo '<div class="modal-body">';
    echo '<div id="conf_div" class="text-center"></div>';
    echo '<div class="row" id="resp_table">';
    echo '<div class="col-md-4">';
    $sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
    $QI = DBQuery($sql);
    $subjects_RET = DBGet($QI);

    echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' ' . _subjectWas : ' ' . _subjectsWere) . ' ' . _found . '.</h6>';
    if (count($subjects_RET) > 0) {
        echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>' . _subject . '</th></tr></thead><tbody>';
        foreach ($subjects_RET as $val) {
            echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearch(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
        }
        echo '</tbody></table>';
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

function _makeChooseCheckbox($value, $title)
{
    global $THIS_RET;


    // return '<INPUT type=checkbox name=st_arr[] value=' . $value . '>';

    return "<input name=unused_var[$THIS_RET[STUDENT_ID]] value=" . $THIS_RET['STUDENT_ID'] . "  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"st_arr[$THIS_RET[STUDENT_ID]]\",this,$THIS_RET[STUDENT_ID]);' />";
}

function _makeTeacher($teacher, $column)
{

    $TEACHER_NAME = DBGet(DBQuery("SELECT concat(first_name,' ',last_name) as name from staff where staff_id=$teacher"));

    return $TEACHER_NAME[1]['NAME'];
}
