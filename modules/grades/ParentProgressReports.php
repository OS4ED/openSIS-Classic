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
include '_makeLetterGrade.fnc.php';
if ($_REQUEST['modfunc'] == 'save') {
    $st_id = UserStudentID();
    $extra['SELECT'] = ',ssm.START_DATE';
    $extra['WHERE'] = ' AND s.STUDENT_ID =' . $st_id;
    Widgets('mailing_labels');
    $extra['moreland_cust'] = 'assignment_grade';
    $RET = GetStuList($extra);

    if (count($RET)) {
        $columns = array(
            'ASSIGN_TYP' => _assignmentType,
            'ASSIGN_TYP_WG' => _weight . ' (%)',
            'TITLE' => _assignment,
        );
        if ($_REQUEST['assigned_date'] == 'Y')
            $columns += array(
                'ASSIGNED_DATE' => _assignedDate,
            );
        if ($_REQUEST['due_date'] == 'Y')
            $columns += array(
                'DUE_DATE' => _dueDate,
            );
        $columns += array(
            'POINTS' => _points,
            'LETTER_GRADE' => _grade,
            'WEIGHT_GRADE' => _weightedGrade,
            'COMMENT' => _comment,
        );

        $handle = PDFStart();
        foreach ($RET as $student) {
            $student_points = $total_points = $percent_weights = array();
            $tot_weighted_percent = array();
            $assignment_type_count = array();

            unset($_openSIS['DrawHeader']);
            echo "<table width=100%  style=\" font-family:Arial; font-size:12px;\" >";
            echo "<tr><td width=105>" . DrawLogo() . "</td><td  style=\"font-size:15px; font-weight:bold; padding-top:20px;\">" . GetSchool(UserSchool()) . "<div style=\"font-size:12px;\">" . _studentProgressReport . "</div></td><td align=right style=\"padding-top:20px;\">" . ProperDate(DBDate()) . "<br/>" . _studentProgressReport . "</td></tr><tr><td colspan=3 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";
            echo '<table border=0 style=\"font-size:12px;\">';
            echo "<tr><td>" . _studentName . ":</td>";
            echo "<td>" . $student['FULL_NAME'] . "</td></tr>";
            echo "<tr><td>" . _id . ":</td>";
            echo "<td>" . $student['STUDENT_ID'] . " </td></tr>";
            echo "<tr><td>" . _grade . ":</td>";
            echo "<td>" . $student['GRADE_ID'] . " </td></tr>";

            echo "<tr><td>" . _markingPeriod . ":</td>";
            echo "<td>" . GetMP(UserMP()) . " </td></tr>";
            echo '</table>';

            #############################
            $MP_TYPE_RET = DBGet(DBQuery('SELECT MP_TYPE FROM marking_periods WHERE MARKING_PERIOD_ID=' . UserMP() . ' LIMIT 1'));
            $MP_TYPE = $MP_TYPE_RET[1]['MP_TYPE'];
            if ($MP_TYPE == 'year') {
                $MP_TYPE = 'FY';
            } else if ($MP_TYPE == 'semester') {
                $MP_TYPE = 'SEM';
            } else if ($MP_TYPE == 'quarter') {
                $MP_TYPE = 'QTR';
            } else {
                $MP_TYPE = '';
            }
            if ($MP_TYPE == "QTR") {
                $quarter_val = DBGet(DBQuery('SELECT START_DATE, END_DATE FROM school_quarters WHERE MARKING_PERIOD_ID=' . UserMP() . ' '));
                $q = $quarter_val[1];
                $quarter = DBGet(DBQuery('SELECT MARKING_PERIOD_ID  FROM school_quarters WHERE ((\'' . $q['START_DATE'] . '\'Between START_DATE And END_DATE ) OR(\'' . $q['END_DATE'] . '\'Between START_DATE And END_DATE ))  AND SCHOOL_ID=999'));
                $EVAL = $quarter[1];
            }
            if ($MP_TYPE == "SEM") {
                $semester_val = DBGet(DBQuery('SELECT START_DATE, END_DATE FROM school_semesters WHERE MARKING_PERIOD_ID=' . UserMP() . ' '));
                $q = $semester_val[1];
                $semester = DBGet(DBQuery('SELECT MARKING_PERIOD_ID  FROM school_semesters WHERE ((\'' . $q['START_DATE'] . '\'Between START_DATE And END_DATE ) OR(\'' . $q['END_DATE'] . '\'Between START_DATE And END_DATE )) AND SCHOOL_ID=999'));
                $EVAL = $semester[1];
            }
            if ($MP_TYPE == "FY") {
                $year_val = DBGet(DBQuery('SELECT START_DATE, END_DATE FROM school_years WHERE MARKING_PERIOD_ID=' . UserMP() . ' '));
                $q = $year_val[1];
                $year = DBGet(DBQuery('SELECT MARKING_PERIOD_ID  FROM school_years WHERE ((\'' . $q['START_DATE'] . '\'Between START_DATE And END_DATE ) OR(\'' . $q['END_DATE'] . '\'Between START_DATE And END_DATE )) AND SCHOOL_ID=999 '));
                $EVAL = $year[1];
            }

            ############################## 

            if ($_REQUEST['mailing_labels'] == 'Y')
                echo '<tr><TD colspan=2>' . $student['MAILING_LABEL'] . '</TD></TR>';


            $courselist_ret = DBGet(DBQuery('SELECT s.TITLE AS COURSE, s.COURSE_ID, cp.COURSE_PERIOD_ID,cp.TEACHER_ID 
                                                        FROM gradebook_grades g, courses s, course_periods cp, gradebook_assignments ga, schedule sc
                                                        WHERE cp.COURSE_PERIOD_ID = ga.COURSE_PERIOD_ID AND sc.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND sc.STUDENT_ID=g.STUDENT_ID 
                                                        AND s.COURSE_ID = cp.COURSE_ID AND ga.assignment_id = g.assignment_id AND ga.marking_period_id in (' . GetAllMP($MP_TYPE, UserMP()) . ')  and  g.STUDENT_ID=\'' . $student['STUDENT_ID'] . '\' and s.syear=\'' . UserSyear() . '\' group by cp.COURSE_PERIOD_ID'));
            if (!empty($courselist_ret[1])) {
                foreach ($courselist_ret as $courselist => $course) {
                    unset($student_points);
                    unset($total_points);
                    unset($percent_weights);
                    unset($total_stpoints);
                    unset($total_asgnpoints);
                    unset($tot_weighted_percent);
                    unset($assignment_type_count);

                    $course_title = $course['COURSE'];
                    $course_id = $course['COURSE_ID'];
                    $course_period_id = $course['COURSE_PERIOD_ID'];
                    $config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\'' . $course['TEACHER_ID'] . '\' AND PROGRAM=\'Gradebook\' AND VALUE LIKE \'%_' . $course_period_id . '\''), array(), array('TITLE'));
                    if (count($config_RET))
                        foreach ($config_RET as $title => $value)
                            $program_config[$course['TEACHER_ID']][$course_period_id][$title] = rtrim($value[1]['VALUE'], '_' . $course_period_id);
                    else
                        $program_config[$course['TEACHER_ID']][$course_period_id] = true;
                    $course_period_title = DBGet(DBQuery('SELECT TITLE FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $course_period_id . '\' '));
                    echo '<table border=0 style=\"font-size:12px;\">';
                    echo "<tr><td>" . _course . ":</td><td>" . $course_title . "</td></tr>";
                    echo "<tr><td>" . _coursePeriod . ":</td><td>" . $course_period_title[1]['TITLE'] . "</td></tr>";

                    if ($program_config[$course['TEACHER_ID']][$course_period_id]['WEIGHT'] == 'Y') {
                        $course_periods = DBGet(DBQuery('select marking_period_id from course_periods where course_period_id=' . $course_period_id));
                        if ($course_periods[1]['MARKING_PERIOD_ID'] == NULL) {
                            $assignment_type_ids = DBGet(DBQuery('SELECT group_concat(distinct(assignment_type_id)) AS assignment_type_ids FROM gradebook_assignments a JOIN gradebook_grades g ON (a.ASSIGNMENT_ID = g.ASSIGNMENT_ID AND g.STUDENT_ID=\'' . $student['STUDENT_ID'] . '\' AND g.COURSE_PERIOD_ID=\'' . $course_period_id . '\') WHERE (a.COURSE_PERIOD_ID=\'' . $course_period_id . '\' OR a.COURSE_ID=\'' . $course_id . '\') AND (a.MARKING_PERIOD_ID=\'' . UserMP() . '\' OR a.MARKING_PERIOD_ID=\'' . $fy_mp_id . '\')'));

                            $assignment_type_weight = DBGet(DBQuery('SELECT SUM(FINAL_GRADE_PERCENT) AS FINAL_GRADE_PERCENT FROM gradebook_assignment_types WHERE assignment_type_id IN (' . $assignment_type_ids[1]['ASSIGNMENT_TYPE_IDS'] . ')'));
                            $assignment_type_weight = $assignment_type_weight[1]['FINAL_GRADE_PERCENT'];

                            $school_years = DBGet(DBQuery('select marking_period_id from  school_years where  syear=' . UserSyear() . ' and school_id=' . UserSchool()));
                            $fy_mp_id = $school_years[1]['MARKING_PERIOD_ID'];
                            // $sql = 'SELECT ' . $course_period_id . ' as COURSE_PERIOD_ID,a.TITLE,t.TITLE AS ASSIGN_TYP,a.ASSIGNED_DATE,a.DUE_DATE,      t.ASSIGNMENT_TYPE_ID,  (t.FINAL_GRADE_PERCENT / (SELECT SUM(FINAL_GRADE_PERCENT) FROM gradebook_assignment_types WHERE COURSE_ID = \'' . $course_id . '\')) as FINAL_GRADE_PERCENT, (t.FINAL_GRADE_PERCENT / (SELECT SUM(FINAL_GRADE_PERCENT) FROM gradebook_assignment_types WHERE COURSE_ID = \'' . $course_id . '\')) as ASSIGN_TYP_WG,t.FINAL_GRADE_PERCENT AS WEIGHT_GRADE  ,g.POINTS,a.POINTS AS TOTAL_POINTS,g.COMMENT,g.POINTS AS LETTER_GRADE,g.POINTS AS LETTERWTD_GRADE,' . $course['TEACHER_ID'] . ' AS CP_TEACHER_ID,CASE WHEN (a.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE>=a.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM gradebook_assignment_types t,gradebook_assignments a 
                            //             LEFT OUTER JOIN gradebook_grades g ON (a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND g.STUDENT_ID=\'' . $student['STUDENT_ID'] . '\' AND g.COURSE_PERIOD_ID=\'' . $course_period_id . '\') 
                            //                  WHERE   a.ASSIGNMENT_TYPE_ID=t.ASSIGNMENT_TYPE_ID AND (a.COURSE_PERIOD_ID=\'' . $course_period_id . '\' OR a.COURSE_ID=\'' . $course_id . '\' ) AND t.COURSE_ID=\'' . $course_id . '\' AND (a.MARKING_PERIOD_ID=\'' . UserMP() . '\' OR a.MARKING_PERIOD_ID=\'' . $fy_mp_id . '\')';

                            $sql = 'SELECT ' . $course_period_id . ' as COURSE_PERIOD_ID,a.TITLE,t.TITLE AS ASSIGN_TYP,a.ASSIGNED_DATE,a.DUE_DATE, t.ASSIGNMENT_TYPE_ID,  (t.FINAL_GRADE_PERCENT / '.$assignment_type_weight.') as FINAL_GRADE_PERCENT, t.FINAL_GRADE_PERCENT as ASSIGN_TYP_WG,t.FINAL_GRADE_PERCENT AS WEIGHT_GRADE  ,g.POINTS,a.POINTS AS TOTAL_POINTS,g.COMMENT,g.POINTS AS LETTER_GRADE,g.POINTS AS LETTERWTD_GRADE,' . $course['TEACHER_ID'] . ' AS CP_TEACHER_ID,CASE WHEN (a.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE>=a.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM gradebook_assignment_types t,gradebook_assignments a 
                                        LEFT OUTER JOIN gradebook_grades g ON (a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND g.STUDENT_ID=\'' . $student['STUDENT_ID'] . '\' AND g.COURSE_PERIOD_ID=\'' . $course_period_id . '\') 
                                             WHERE   a.ASSIGNMENT_TYPE_ID=t.ASSIGNMENT_TYPE_ID AND (a.COURSE_PERIOD_ID=\'' . $course_period_id . '\' OR a.COURSE_ID=\'' . $course_id . '\' ) AND t.COURSE_ID=\'' . $course_id . '\' AND (a.MARKING_PERIOD_ID=\'' . UserMP() . '\' OR a.MARKING_PERIOD_ID=\'' . $fy_mp_id . '\')';
                        } else {
                            $assignment_type_ids = DBGet(DBQuery('SELECT group_concat(distinct(assignment_type_id)) AS assignment_type_ids FROM gradebook_assignments a JOIN gradebook_grades g ON (a.ASSIGNMENT_ID = g.ASSIGNMENT_ID AND g.STUDENT_ID=\'' . $student['STUDENT_ID'] . '\' AND g.COURSE_PERIOD_ID=\'' . $course_period_id . '\') WHERE (a.COURSE_PERIOD_ID=\'' . $course_period_id . '\' OR a.COURSE_ID=\'' . $course_id . '\') AND (a.MARKING_PERIOD_ID=\'' . UserMP() . '\')'));
                    
                            $assignment_type_weight = DBGet(DBQuery('SELECT SUM(FINAL_GRADE_PERCENT) AS FINAL_GRADE_PERCENT FROM gradebook_assignment_types WHERE assignment_type_id IN ('.$assignment_type_ids[1]['ASSIGNMENT_TYPE_IDS'].')'));
                            $assignment_type_weight = $assignment_type_weight[1]['FINAL_GRADE_PERCENT'];

                            // $sql = 'SELECT ' . $course_period_id . ' as COURSE_PERIOD_ID,a.TITLE,t.TITLE AS ASSIGN_TYP,a.ASSIGNED_DATE,a.DUE_DATE,      t.ASSIGNMENT_TYPE_ID,     t.FINAL_GRADE_PERCENT,t.FINAL_GRADE_PERCENT as ASSIGN_TYP_WG,t.FINAL_GRADE_PERCENT AS WEIGHT_GRADE  ,g.POINTS,a.POINTS AS TOTAL_POINTS,g.COMMENT,g.POINTS AS LETTER_GRADE,g.POINTS AS LETTERWTD_GRADE,' . $course['TEACHER_ID'] . ' AS CP_TEACHER_ID,CASE WHEN (a.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE>=a.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM gradebook_assignment_types t,gradebook_assignments a 
                            //             LEFT OUTER JOIN gradebook_grades g ON (a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND g.STUDENT_ID=\'' . $student['STUDENT_ID'] . '\' AND g.COURSE_PERIOD_ID=\'' . $course_period_id . '\') 
                            //                  WHERE   a.ASSIGNMENT_TYPE_ID=t.ASSIGNMENT_TYPE_ID AND (a.COURSE_PERIOD_ID=\'' . $course_period_id . '\' OR a.COURSE_ID=\'' . $course_id . '\' ) AND t.COURSE_ID=\'' . $course_id . '\' AND a.MARKING_PERIOD_ID=\'' . UserMP() . '\'';

                            $sql = 'SELECT ' . $course_period_id . ' as COURSE_PERIOD_ID,a.TITLE,t.TITLE AS ASSIGN_TYP,a.ASSIGNED_DATE,a.DUE_DATE,      t.ASSIGNMENT_TYPE_ID,     (t.FINAL_GRADE_PERCENT / '.$assignment_type_weight.') as FINAL_GRADE_PERCENT,t.FINAL_GRADE_PERCENT as ASSIGN_TYP_WG,t.FINAL_GRADE_PERCENT AS WEIGHT_GRADE  ,g.POINTS,a.POINTS AS TOTAL_POINTS,g.COMMENT,g.POINTS AS LETTER_GRADE,g.POINTS AS LETTERWTD_GRADE,' . $course['TEACHER_ID'] . ' AS CP_TEACHER_ID,CASE WHEN (a.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE>=a.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM gradebook_assignment_types t,gradebook_assignments a 
                                        LEFT OUTER JOIN gradebook_grades g ON (a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND g.STUDENT_ID=\'' . $student['STUDENT_ID'] . '\' AND g.COURSE_PERIOD_ID=\'' . $course_period_id . '\') 
                                             WHERE   a.ASSIGNMENT_TYPE_ID=t.ASSIGNMENT_TYPE_ID AND (a.COURSE_PERIOD_ID=\'' . $course_period_id . '\' OR a.COURSE_ID=\'' . $course_id . '\' ) AND t.COURSE_ID=\'' . $course_id . '\' AND a.MARKING_PERIOD_ID=\'' . UserMP() . '\'';

                        }
                    } else {
                        $course_periods = DBGet(DBQuery('select marking_period_id from course_periods where course_period_id=' . $course_period_id));
                        if ($course_periods[1]['MARKING_PERIOD_ID'] == NULL) {
                            $school_years = DBGet(DBQuery('select marking_period_id from  school_years where  syear=' . UserSyear() . ' and school_id=' . UserSchool()));
                            $fy_mp_id = $school_years[1]['MARKING_PERIOD_ID'];

                            $sql = 'SELECT ' . $course_period_id . ' as COURSE_PERIOD_ID,a.TITLE,t.TITLE AS ASSIGN_TYP,a.ASSIGNED_DATE,a.DUE_DATE,\'-1\' AS ASSIGNMENT_TYPE_ID,\'1\' AS FINAL_GRADE_PERCENT,\'N/A\' as ASSIGN_TYP_WG,\'N/A\' as WEIGHT_GRADE,g.POINTS,a.POINTS AS TOTAL_POINTS,g.COMMENT,g.POINTS AS LETTER_GRADE,g.POINTS AS LETTERWTD_GRADE,' . $course['TEACHER_ID'] . ' AS CP_TEACHER_ID,CASE WHEN (a.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE>=a.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM    gradebook_assignment_types t,gradebook_assignments a
                                        LEFT OUTER JOIN gradebook_grades g ON (a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND g.STUDENT_ID=\'' . $student['STUDENT_ID'] . '\' AND g.COURSE_PERIOD_ID=\'' . $course_period_id . '\')
                                             WHERE     a.ASSIGNMENT_TYPE_ID=t.ASSIGNMENT_TYPE_ID AND   (a.COURSE_PERIOD_ID=\'' . $course_period_id . '\' OR a.COURSE_ID=\'' . $course_id . '\')  AND t.COURSE_ID=\'' . $course_id . '\' AND (a.MARKING_PERIOD_ID=\'' . UserMP() . '\' OR a.MARKING_PERIOD_ID=\'' . $fy_mp_id . '\')';
                        } else {
                            $sql = 'SELECT ' . $course_period_id . ' as COURSE_PERIOD_ID,a.TITLE,t.TITLE AS ASSIGN_TYP,a.ASSIGNED_DATE,a.DUE_DATE,\'-1\' AS ASSIGNMENT_TYPE_ID,\'1\' AS FINAL_GRADE_PERCENT,\'N/A\' as ASSIGN_TYP_WG,\'N/A\' as WEIGHT_GRADE,g.POINTS,a.POINTS AS TOTAL_POINTS,g.COMMENT,g.POINTS AS LETTER_GRADE,g.POINTS AS LETTERWTD_GRADE,' . $course['TEACHER_ID'] . ' AS CP_TEACHER_ID,CASE WHEN (a.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE>=a.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM    gradebook_assignment_types t,gradebook_assignments a
                                        LEFT OUTER JOIN gradebook_grades g ON (a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND g.STUDENT_ID=\'' . $student['STUDENT_ID'] . '\' AND g.COURSE_PERIOD_ID=\'' . $course_period_id . '\')
                                             WHERE       a.ASSIGNMENT_TYPE_ID=t.ASSIGNMENT_TYPE_ID AND  (a.COURSE_PERIOD_ID=\'' . $course_period_id . '\' OR a.COURSE_ID=\'' . $course_id . '\')  AND t.COURSE_ID=\'' . $course_id . '\' AND a.MARKING_PERIOD_ID=\'' . UserMP() . '\'';
                        }
                    }
                    if ($_REQUEST['exclude_notdue'] == 'Y')
                        $sql .= ' AND ((a.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE>=DUE_DATE) OR g.POINTS IS NOT NULL)';
                    if ($_REQUEST['exclude_ec'] == 'Y')
                        $sql .= ' AND (a.POINTS!=\'0\' OR g.POINTS IS NOT NULL AND g.POINTS!=\'-1\')';
                    $sql .= ' AND a.DUE_DATE>=\'' . $student['START_DATE'] . '\' ORDER BY a.ASSIGNMENT_TYPE_ID';
                    $grades_RET = DBGet(DBQuery($sql), array('ASSIGNED_DATE' => '_removeSpaces', 'ASSIGN_TYP_WG' => '_makeAssnWG', 'DUE_DATE' => '_removeSpaces', 'TITLE' => '_removeSpaces', 'POINTS' => '_makeExtra', 'LETTER_GRADE' => '_makeExtra', 'WEIGHT_GRADE' => '_makeWtg'));

                    //			$sum_points = $sum_percent = 0;
                    if (is_countable($percent_weights) && count($percent_weights)) {

                        foreach ($percent_weights as $assignment_type_id => $percent) {
                            //				$sum_points += $student_points[$assignment_type_id] * $percent_weights[$assignment_type_id] / $total_points[$assignment_type_id];
                            //				$sum_percent += $percent;
                            $total_stpoints += $student_points[$assignment_type_id];
                            $total_asgnpoints += $total_points[$assignment_type_id];
                        }
                        //			if($sum_percent>0)
                        //				$sum_points /= $sum_percent;
                    }
                    //			else
                    //				$sum_points = 0;
                    if ($program_config[$course['TEACHER_ID']][$course_period_id]['WEIGHT'] == 'Y') {
                        $assign_typ_wg = array();
                        $tot_weight_grade = 0;
                        //                           $sum_points=0;

                        if (count($grades_RET)) {
                            foreach ($grades_RET as $key => $val) {
                                if ($val['LETTERWTD_GRADE'] != -1.00 && $val['LETTERWTD_GRADE'] != '') {
                                    $wper = explode('%', $val['LETTER_GRADE']);
                                    if ($tot_weighted_percent[$val['ASSIGNMENT_TYPE_ID']] != '')
                                        $tot_weighted_percent[$val['ASSIGNMENT_TYPE_ID']] = $tot_weighted_percent[$val['ASSIGNMENT_TYPE_ID']] + $wper[0];
                                    else
                                        $tot_weighted_percent[$val['ASSIGNMENT_TYPE_ID']] = $wper[0];
                                    if ($assignment_type_count[$val['ASSIGNMENT_TYPE_ID']] != '')
                                        $assignment_type_count[$val['ASSIGNMENT_TYPE_ID']] = $assignment_type_count[$val['ASSIGNMENT_TYPE_ID']] + 1;
                                    else
                                        $assignment_type_count[$val['ASSIGNMENT_TYPE_ID']] = 1;
                                    if ($val['ASSIGN_TYP_WG'] != '')
                                        $assign_typ_wg[$val['ASSIGNMENT_TYPE_ID']] = substr($val['ASSIGN_TYP_WG'], 0, -2);
                                }
                            }
                            $total_weightage = 0;
                            foreach ($assignment_type_count as $assign_key => $value) {
                                $total_weightage = $total_weightage + $assign_typ_wg[$assign_key];
                                if ($tot_weight_grade == 0)
                                    $tot_weight_grade = round((round(($tot_weighted_percent[$assign_key] / $value), 2) * $assign_typ_wg[$assign_key]) / 100, 2);
                                else
                                    $tot_weight_grade = $tot_weight_grade + (round((round(($tot_weighted_percent[$assign_key] / $value), 2) * $assign_typ_wg[$assign_key]) / 100, 2));
                            }
                            $tot_weight_grade = $tot_weight_grade / 100;
                        }
                    }
                    // $tot_weight_grade = ($tot_weight_grade / $total_weightage) * 100;
                    $tot_weight_grade=($total_weightage == 0 ? 0 : ($tot_weight_grade/$total_weightage)*100);


                    //                            $link['add']['html'] = array('TITLE'=>'<B>Total</B>','LETTER_GRADE'=>'( '.$total_stpoints.' / '.$total_asgnpoints.' ) '._makeLetterGrade(($total_stpoints/$total_asgnpoints),$course_period_id,  $course['TEACHER_ID'],"%").'%&nbsp;'._makeLetterGrade(($total_stpoints/$total_asgnpoints),$course_period_id,  $course['TEACHER_ID']),'WEIGHT_GRADE'=>$programconfig[$course['TEACHER_ID']][$course_period_id]['WEIGHT']=='Y'?_makeLetterGrade($tot_weight_grade,"",$course['TEACHER_ID'],'%').'%&nbsp;'._makeLetterGrade($tot_weight_grade,$course_period_id,$course['TEACHER_ID']):'N/A');
                    $total_st_div_agn_points = ($total_asgnpoints == 0 ? 0 : $total_stpoints/$total_asgnpoints);
                    $link['add']['html'] = array('TITLE' => '<font style="font-size:13;font-weight:bold;"><B>' . _total . '</B></font>', 'POINTS' => '<font style="font-size:13;font-weight:bold;">' . $total_stpoints . ' / ' . $total_asgnpoints . '</font>', 'LETTER_GRADE' => '<font style="font-size:13;font-weight:bold;">' . _makeLetterGrade($total_st_div_agn_points, $course_period_id, $course['TEACHER_ID'], "%") . '%&nbsp;' . _makeLetterGrade($total_st_div_agn_points, $course_period_id, $course['TEACHER_ID']) . '</font>', 'WEIGHT_GRADE' => '<font style="font-size:13;font-weight:bold;">' . ($program_config[$course['TEACHER_ID']][$course_period_id]['WEIGHT'] == 'Y' ? _makeLetterGrade($tot_weight_grade, "", $course['TEACHER_ID'], '%') . '%&nbsp;' . _makeLetterGrade($tot_weight_grade, $course_period_id, $course['TEACHER_ID']) : 'N/A') . '</font>');



                    //			$link['add']['html']['ASSIGNED_DATE'] = $link['add']['html']['DUE_DATE'] = $link['add']['html']['POINTS'] = $link['add']['html']['COMMENT'] = ' &nbsp; ';
                    $link['add']['html']['ASSIGNED_DATE'] = $link['add']['html']['DUE_DATE'] = $link['add']['html']['COMMENT'] = ' &nbsp; ';
                    echo '</table>';

                    if ($_REQUEST['list_type'] == 'total') {
                        echo '<table border=0  style=\"font-size:12px;\" >';
                        echo '<tr><td> ' . _total . ':</td><td>' . _makeLetterGrade($total_st_div_agn_points, $course_period_id, $course['TEACHER_ID'], "%") . '%&nbsp;' . _makeLetterGrade($total_st_div_agn_points, $course_period_id, $course['TEACHER_ID']) . '</td> </tr>';
                        echo '<tr><td> ' . _totalWeightedGrade . ':</td><td>' . ($program_config[$course['TEACHER_ID']][$course_period_id]['WEIGHT'] == 'Y' ? _makeLetterGrade($tot_weight_grade, "", $course['TEACHER_ID'], '%') . '%&nbsp;' . _makeLetterGrade($tot_weight_grade, $course_period_id, $course['TEACHER_ID']) : '' . _nA . '') . '</td> </tr>';
                        echo '<tr><td></td></tr>';
                        echo '</table>';
                    } else
                        ListOutputPrint($grades_RET, $columns, _assignment, _assignments, $link, array(), array('center' => false, 'add' => true));
                    unset($percent_weights);
                }
            } else {
                echo "<p style='color:red'><b>" . _noGradesWereFound . "</b></p>";
            }
            echo '<div style="page-break-before: always;">&nbsp;</div>';
        }

        PDFStop($handle);
    } else
        BackPrompt(_noStudentsWereFound . '.');
}

if (!$_REQUEST['modfunc']) {

    echo "<FORM action=ForExport.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=save&include_inactive=" . strip_tags(trim($_REQUEST['include_inactive'])) . "&_openSIS_PDF=true method=POST target=_blank>";
    //echo '<div class="panel">';
    //echo '<div class="panel-body">';
    PopTable('header', _progressReports);

    echo '<div class="row">';
    echo '<div class="col-md-4">';
    echo '<div class="form-group"><div class="checkbox checkbox-switch switch-success switch-xs"><label><INPUT type=checkbox value=Y name=assigned_date id=assigned_date><span></span>' . _assignedDate . '</label></div></div>';
    echo '</div>';
    echo '<div class="col-md-4">';
    echo '<div class="form-group"><div class="checkbox checkbox-switch switch-success switch-xs"><label><INPUT type=checkbox value=Y name=exclude_ec checked><span></span>' . _excludeUngradedECAssignments . '</label></div></div>';
    echo '</div>'; //.col-md-4
    echo '</div>'; //.row

    echo '<div class="row">';
    echo '<div class="col-md-4">';
    echo '<div class="form-group"><div class="checkbox checkbox-switch switch-success switch-xs"><label><INPUT type=checkbox value=Y name=due_date id=due_date checked><span></span>' . _dueDate . '</label></div></div>';
    echo '</div>';
    echo '<div class="col-md-4">';
    echo '<div class="form-group"><div class="checkbox checkbox-switch switch-success switch-xs"><label><INPUT type=checkbox value=Y name=exclude_notdue><span></span>' . _excludeUngradedAssignmentsNotDue . '</label></div></div>';
    echo '</div>'; //.col-md-4
    echo '</div>'; //.row

    echo '<div class="row">';
    echo '<div class="col-md-4">';
    echo '<div class="form-group"><div class="radio"><label><INPUT class="styled" type=radio value=detail name=list_type onclick="assignmentDetails()" checked=true>' . _withAssignmentDetails . '</label></div></div>';
    echo '</div>';
    echo '<div class="col-md-4">';
    echo '<div class="form-group"><div class="radio"><label><INPUT class="styled" type=radio value=total name=list_type onclick="totalsOnly()">' . _totalsOnly . '</label></div></div>';
    echo '</div>'; //.col-md-4
    echo '</div>'; //.row

    PopTable('footer', '<div class="text-center"><INPUT type=submit value="' . _createProgressReports . '"  class="btn btn-primary"></div>');

    echo '</FORM>';
}

function _makeExtra($value, $column)
{
    global $THIS_RET, $student_points, $total_points, $percent_weights;

    if ($column == 'POINTS') {
        if ($THIS_RET['TOTAL_POINTS'] != '0')
            if ($value != '-1') {
                if (($THIS_RET['DUE'] || $value != '') && $value != '') {
                    $student_points[$THIS_RET['ASSIGNMENT_TYPE_ID']] += $value;
                    $total_points[$THIS_RET['ASSIGNMENT_TYPE_ID']] += $THIS_RET['TOTAL_POINTS'];
                    $percent_weights[$THIS_RET['ASSIGNMENT_TYPE_ID']] = $THIS_RET['FINAL_GRADE_PERCENT'];
                }
                // return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR><TD><font size=-1>' . (rtrim(rtrim($value, '0'), '.') + 0) . '</font></TD><TD><font size=-1>&nbsp;/&nbsp;</font></TD><TD><font size=-1>' . $THIS_RET['TOTAL_POINTS'] . '</font></TD></TR></TABLE>';
                $outputval = rtrim(rtrim($value,'0'),'.');
                return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR><TD><font size=-1>' . ($outputval == '' ? 0 : $outputval) . '</font></TD><TD><font size=-1>&nbsp;/&nbsp;</font></TD><TD><font size=-1>' . $THIS_RET['TOTAL_POINTS'] . '</font></TD></TR></TABLE>';
            } else
                return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR><TD><font size=-1>Excluded</font></TD><TD></TD><TD></TD></TR></TABLE>';
        else {
            $student_points[$THIS_RET['ASSIGNMENT_TYPE_ID']] += $value;
            // return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR><TD><font size=-1>' . (rtrim(rtrim($value, '0'), '.') + 0) . '</font></TD><TD><font size=-1>&nbsp;/&nbsp;</font></TD><TD><font size=-1>' . $THIS_RET['TOTAL_POINTS'] . '</font></TD></TR></TABLE>';
            return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR><TD><font size=-1>' . rtrim(rtrim($value, '0'), '.') . '</font></TD><TD><font size=-1>&nbsp;/&nbsp;</font></TD><TD><font size=-1>' . $THIS_RET['TOTAL_POINTS'] . '</font></TD></TR></TABLE>';
        }
    } elseif ($column == 'LETTER_GRADE') {

        if ($THIS_RET['TOTAL_POINTS'] != '0')
            if ($value != '-1')
                if ($THIS_RET['DUE'] && $value == '')
                    return 'Not Graded';
                else if ($THIS_RET['DUE'] || $value != '') {
                    $qr = DBGet(DBQuery('SELECT TEACHER_ID FROM course_periods WHERE COURSE_PERIOD_ID=' . $THIS_RET['COURSE_PERIOD_ID']));
                    return _makeLetterGrade($value / $THIS_RET['TOTAL_POINTS'], $THIS_RET['COURSE_PERIOD_ID'], $qr[1]['TEACHER_ID'], "%") . '%&nbsp;' . _makeLetterGrade($value / $THIS_RET['TOTAL_POINTS'], $THIS_RET['COURSE_PERIOD_ID'], $qr[1]['TEACHER_ID']);
                } else
                    return 'Due';
            else
                return 'N/A';
        else
            return 'E/C';
    }
}

function _removeSpaces($value, $column)
{
    if ($column == 'ASSIGNED_DATE' || $column == 'DUE_DATE')
        $value = ProperDate($value);

    return str_replace(' ', '&nbsp;', str_replace('&', '&amp;', $value));
}

function _makeChooseCheckbox($value, $title)
{
    return '<INPUT type=checkbox name=st_arr[] value=' . $value . ' checked>';
}

function _makeAssnWG($value, $column)
{
    global $THIS_RET, $student_points, $total_points, $percent_weights;
    return ($THIS_RET['ASSIGN_TYP_WG'] != 'N/A' ? ($value * 100) . ' %' : $THIS_RET['ASSIGN_TYP_WG']);
}

function _makeWtg($value, $column)
{
    global $THIS_RET, $student_points, $total_points, $percent_weights;
    $wtdper = ($THIS_RET['POINTS'] / $THIS_RET['TOTAL_POINTS']) * $THIS_RET['FINAL_GRADE_PERCENT'];
    return (($THIS_RET['LETTERWTD_GRADE'] != -1.00 && $THIS_RET['LETTERWTD_GRADE'] != '' && $THIS_RET['ASSIGN_TYP_WG'] != 'N/A') ? _makeLetterGrade($wtdper, $THIS_RET['COURSE_PERIOD_ID'], $THIS_RET['CP_TEACHER_ID'], '%') . '%' : 'N/A');
}
?>
<script>
    
    function totalsOnly(){
   var assigned_date = document.getElementById("assigned_date");
   document.getElementById("assigned_date").disabled = true;
   document.getElementById("due_date").disabled = true;
   if(assigned_date.checked){
document.getElementById("assigned_date").checked = false;
   }
   var due_date = document.getElementById("due_date");
   if(due_date.checked){
document.getElementById("due_date").checked = false;
   }
    }
    
    function assignmentDetails(){
        document.getElementById("due_date").checked = true;
        document.getElementById("due_date").disabled = false;
        document.getElementById("assigned_date").disabled = false;
        
    }
   </script>
