<?php
include '../Data.php';
include 'function/DbGetFnc.php';
include 'function/app_functions.php';
include 'function/function.php';

include 'function/ParamLib.php';

header('Content-Type: application/json');

$_SESSION['STAFF_ID'] = $teacher_id = $staff_id = $_REQUEST['staff_id'];
$_SESSION['UserSchool'] = $_REQUEST['school_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];
$cpv_id = $_REQUEST['cpv_id'];
$mp_id = $_SESSION['UserMP'] = $_REQUEST['mp'];
$current_mp =  $_REQUEST['mp_id'];  //same as UserMP value of web
$student_data = array();

$auth_data = check_auth();
if (count($auth_data) > 0) {
    if ($auth_data['user_id'] == $teacher_id && $auth_data['user_profile'] == 'teacher') {
        $table = GetMP($_REQUEST['mp'], 'TABLE', UserSyear(), UserSchool());

        $mp_comment_data = DBGet(DBQuery('SELECT DOES_COMMENTS FROM ' . $table . ' WHERE MARKING_PERIOD_ID=\'' . $mp_id . '\''));

        $prev_mp = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE,START_DATE FROM " . $table . " WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' AND START_DATE<'" . GetMP($_REQUEST['mp_id'], 'START_DATE', UserSyear(), UserSchool()) . "' ORDER BY START_DATE DESC LIMIT 1"));
        $prev_mp = $prev_mp[1];

        if ($cpv_id != '') {
            /***custome course period***/
            $check_cp = DBGet(DBQuery('SELECT cp.MARKING_PERIOD_ID FROM course_periods cp,course_period_var cpv WHERE cpv.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND cpv.ID=' . $cpv_id));
            if ($check_cp[1]['MARKING_PERIOD_ID'] == '')
                $cp_type = 'custom';

            if (isset($cp_type) && $cp_type == 'custom') {
                $full_year_mp = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear()));
                $_REQUEST['mp_id'] = $full_year_mp[1]['MARKING_PERIOD_ID'];
            }
            /******/

            $cp_id = DBGet(DBQuery('SELECT COURSE_PERIOD_ID,PERIOD_ID FROM course_period_var WHERE ID=\'' . $cpv_id . '\''));

            $_SESSION['UserCoursePeriod'] = $course_period_id = $cp_id[1]['COURSE_PERIOD_ID'];
            $_SESSION['UserPeriod'] = $period_id = $cp_id[1]['PERIOD_ID'];

            $course_id = DBGet(DBQuery('SELECT COURSE_ID FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $course_period_id . '\''));
            $course_id = $course_id[1]['COURSE_ID'];

            $mp_RET = DBGet(DBQuery('SELECT MP FROM course_periods WHERE course_period_id = \'' . $course_period_id . '\''));

            if ($mp_RET[1]['MP'] == 'SEM') {
                $sem = GetParentMPWs('SEM', $mp_id, $_REQUEST['syear'], $_REQUEST['school_id']);
                $qtr = GetChildrenMPWs('QTR', $mp_id, $_REQUEST['syear'], $_REQUEST['school_id']);
                //$pros = GetChildrenMPWs('PRO',$mp_id,$_REQUEST['syear'],$_REQUEST['school_id']);
            }
            if ($mp_RET[1]['MP'] == 'FY') {
                $sem = GetParentMPWs('SEM', $mp_id, $_REQUEST['syear'], $_REQUEST['school_id']);
                if ($sem)
                    $fy = GetParentMPWs('FY', $sem, $_REQUEST['syear'], $_REQUEST['school_id']);
                else
                    $fy = GetParentMPWs('FY', $mp_id, $_REQUEST['syear'], $_REQUEST['school_id']);
                $qtr = GetChildrenMPWs('QTR', $mp_id, $_REQUEST['syear'], $_REQUEST['school_id']);
                $pros = GetChildrenMPWs('PRO', $mp_id, $_REQUEST['syear'], $_REQUEST['school_id']);
            }
            if ($mp_RET[1]['MP'] == 'QTR') {
                $pros = GetChildrenMPWs('PRO', $mp_id, $_REQUEST['syear'], $_REQUEST['school_id']);
            }

            // if the UserMP has been changed, the REQUESTed MP may not work
            if (!$_REQUEST['mp_id'] || strpos($str = "'" . UserMP() . "','" . $sem . "','" . $fy . "'," . $pros, "'" . ltrim($_REQUEST['mp'], 'E') . "'") === false)
                $_REQUEST['mp_id'] = UserMP();
            if ($_REQUEST['period'] == '')
                $_REQUEST['period'] = $cpv_id;
            $custom_p = 'n';
            if ($_REQUEST['period'] != '' && $_REQUEST['mp_id'] != '') {
                $check_custom = DBGet(DBQuery('SELECT cp.MARKING_PERIOD_ID FROM course_periods cp,course_period_var cpv WHERE cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cpv.ID=' . $_REQUEST['period']));
                if ($check_custom[1]['MARKING_PERIOD_ID'] == '') {
                    $get_syear_id = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=' . UserSyear() . ' AND SCHOOL_ID=' . UserSchool()));
                    $_REQUEST['mp'] = $get_syear_id[1]['MARKING_PERIOD_ID'];
                    $custom_p = 'y';
                }
            }
            //print_r($_REQUEST);
            //$course_period_id = UserCoursePeriod();
            if ($course_period_id)
                $course_RET = DBGet(DBQuery('SELECT cp.COURSE_ID,c.TITLE as COURSE_NAME, cp.TITLE, cp.GRADE_SCALE_ID,CREDIT(cp.COURSE_PERIOD_ID,\'' . $_REQUEST['mp_id'] . '\') AS CREDITS,cp.COURSE_WEIGHT FROM course_periods cp, courses c WHERE cp.COURSE_ID = c.COURSE_ID AND cp.COURSE_PERIOD_ID=\'' . $course_period_id . '\''));  //sg

            if (!$course_RET[1]['GRADE_SCALE_ID'] && !$_REQUEST['include_inactive']) {
                $msg = 'You cannot enter grades for this course.';
                $not_graded = true;
                $_REQUEST['use_percents'] = true;
                $success = 0;
            }
            $course_title = $course_RET[1]['TITLE'];
            $grade_scale_id = $course_RET[1]['GRADE_SCALE_ID'];
            $course_id = $course_RET[1]['COURSE_ID'];

            if ($_REQUEST['mp_id']) {
                $current_RET = DBGet(DBQuery('SELECT g.STUDENT_ID,g.REPORT_CARD_GRADE_ID,g.GRADE_PERCENT,g.REPORT_CARD_COMMENT_ID,g.COMMENT FROM student_report_card_grades g,course_periods cp WHERE cp.COURSE_PERIOD_ID=g.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND g.MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\''), array(), array('STUDENT_ID'));
                $current_commentsA_RET = DBGet(DBQuery('SELECT g.STUDENT_ID,g.REPORT_CARD_COMMENT_ID,g.COMMENT FROM student_report_card_comments g,course_periods cp WHERE cp.COURSE_PERIOD_ID=g.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND g.MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\' AND g.REPORT_CARD_COMMENT_ID IN (SELECT ID FROM report_card_comments WHERE COURSE_ID IS NOT NULL)'), array(), array('STUDENT_ID', 'REPORT_CARD_COMMENT_ID'));
                $current_commentsB_RET = DBGet(DBQuery('SELECT g.STUDENT_ID,g.REPORT_CARD_COMMENT_ID FROM student_report_card_comments g,course_periods cp WHERE cp.COURSE_PERIOD_ID=g.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND g.MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\' AND g.REPORT_CARD_COMMENT_ID IN (SELECT ID FROM report_card_comments WHERE COURSE_ID IS NULL)'), array(), array('STUDENT_ID'));
                $max_current_commentsB = 0;
                foreach ($current_commentsB_RET as $comments)
                    if (count($comments) > $max_current_commentsB)
                        $max_current_commentsB = count($comments);
                $current_completed = count(DBGet(DBQuery('SELECT \'\' FROM grades_completed WHERE STAFF_ID=\'' . UserWs('STAFF_ID') . '\' AND MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\' AND PERIOD_ID=\'' . UserPeriod() . '\'')));
            }
            //bjj need more information on grades to load into student_report_card_grades
            $grades_RET = DBGet(DBQuery('SELECT rcg.ID,rcg.TITLE,rcg.GPA_VALUE AS WEIGHTED_GP, rcg.UNWEIGHTED_GP ,gs.GP_SCALE  FROM report_card_grades rcg, report_card_grade_scales gs WHERE rcg.grade_scale_id = gs.id AND rcg.SYEAR=\'' . UserSyear() . '\' AND rcg.SCHOOL_ID=\'' . UserSchool() . '\' AND rcg.GRADE_SCALE_ID=\'' . $grade_scale_id . '\' ORDER BY rcg.BREAK_OFF IS NOT NULL DESC,rcg.BREAK_OFF DESC,rcg.SORT_ORDER'), array(), array('ID'));
            $commentsA_RET = DBGet(DBQuery('SELECT ID,TITLE FROM report_card_comments WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' AND (COURSE_ID=\'' . $course_id . '\' OR COURSE_ID=\'0\') ORDER BY SORT_ORDER'));
            $commentsB_RET = DBGet(DBQuery('SELECT ID,TITLE,SORT_ORDER FROM report_card_comments WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' AND COURSE_ID IS NULL ORDER BY SORT_ORDER'), array(), array('ID'));
            $grades_select = array('' => array('N/A'));
            foreach ($grades_RET as $id => $code)
                $grades_select += array($id => array($code[1]['TITLE'], '<b>' . $code[1]['TITLE'] . '</b>'));
            $commentsB_select = array();
            if (0)
                foreach ($commentsB_RET as $id => $comment)
                    $commentsB_select += array($id => array($comment[1]['SORT_ORDER'], $comment[1]['TITLE']));
            else
                foreach ($commentsB_RET as $id => $comment)
                    $commentsB_select += array($id => array($comment[1]['SORT_ORDER'] . ' - ' . substr($comment[1]['TITLE'], 0, 19) . (strlen($comment[1]['TITLE']) > 20 ? '...' : ''), $comment[1]['TITLE']));


            if ($_REQUEST['fetch_type'] == 'gradebook') {
                if ($_REQUEST['mp_id']) {
                    $config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\'' . $staff_id . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND PROGRAM=\'Gradebook\''), array(), array('TITLE'));
                    if (count($config_RET))
                        foreach ($config_RET as $title => $value)
                            $programconfig[UserWs('STAFF_ID')][$title] = $value[1]['VALUE'];
                    else
                        $programconfig[UserWs('STAFF_ID')] = true;
                    $_openSIS['_makeLetterGrade']['courses'][$course_period_id] = DBGet(DBQuery('SELECT DOES_BREAKOFF,GRADE_SCALE_ID FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $course_period_id . '\''));
                    $_SESSION['ROUNDING'] = $programconfig[UserWs('STAFF_ID')]['ROUNDING'];
                    //                include '_MakeLetterGradeFnc.php';
                    if (false && GetMP($_REQUEST['mp_id'], 'TABLE', UserSyear(), UserSchool()) == 'school_semesters')
                        $points_RET = DBGet(DBQuery('SELECT STUDENT_ID,MARKING_PERIOD_ID FROM student_report_card_grades WHERE COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND MARKING_PERIOD_ID IN (' . GetAllMP('SEM', $_REQUEST['mp_id']) . ",'E" . GetParentMPWs('SEM', $mp_id) . '\')'), array(), array('STUDENT_ID'));

                    if (GetMP($_REQUEST['mp_id'], 'TABLE', UserSyear(), UserSchool()) == 'school_quarters' || GetMP($_REQUEST['mp_id'], 'TABLE', UserSyear(), UserSchool()) == 'school_progress_periods') {
                        // the 'populate the form' approach does not require that we get precisely the right students because nothing is modified here
                        // so we don't need to filter on enrollment dates - in fact, for include_inactive we want 'em all anyway
                        if ($programconfig[UserWs('STAFF_ID')]['WEIGHT'] == 'Y') {
                            $course_periods = DBGet(DBQuery('select marking_period_id from course_periods where course_period_id=' . UserCoursePeriod()));
                            if ($course_periods[1]['MARKING_PERIOD_ID'] == NULL) {
                                $school_years = DBGet(DBQuery('select marking_period_id from  school_years where  syear=' . UserSyear() . ' and school_id=' . UserSchool()));
                                $fy_mp_id = $school_years[1]['MARKING_PERIOD_ID'];
                                $points_RET = DBGet(DBQuery('SELECT DISTINCT s.STUDENT_ID,gt.ASSIGNMENT_TYPE_ID,  gt.ASSIGNMENT_TYPE_ID,sum(' . db_case(array('gg.POINTS', "'-1'", "'0'", 'gg.POINTS')) . ') AS PARTIAL_POINTS,
                                                sum(' . db_case(array('gg.POINTS', '\'-1\' OR gg.POINTS IS NULL  OR (ga.due_date <  (select DISTINCT ssm.start_date  from student_enrollment ssm where ssm.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SCHOOL_ID=' . UserSchool() . ' AND (ssm.START_DATE IS NOT NULL AND (CURRENT_DATE<=ssm.END_DATE OR CURRENT_DATE>=ssm.END_DATE OR  ssm.END_DATE IS NULL)) order by ssm.start_date desc limit 1
                    )  ) ', "'0'", 'ga.POINTS')) . ') AS PARTIAL_TOTAL,
                                                    gt.FINAL_GRADE_PERCENT FROM students s JOIN schedule ss ON (ss.STUDENT_ID=s.STUDENT_ID AND ss.COURSE_PERIOD_ID=\'' . $course_period_id . '\') JOIN gradebook_assignments ga ON ((ga.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID OR ga.COURSE_ID=\'' . $course_id . '\' AND ga.STAFF_ID=\'' . UserWs('STAFF_ID') . '\') AND (ga.MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\' OR ga.MARKING_PERIOD_ID=\'' . $fy_mp_id . '\')) LEFT OUTER JOIN gradebook_grades gg ON (gg.STUDENT_ID=s.STUDENT_ID AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID)
                   
                                                    ,gradebook_assignment_types gt WHERE gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND gt.COURSE_ID=\'' . $course_id . '\' AND ((ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) OR gg.POINTS IS NOT NULL) GROUP BY s.STUDENT_ID,ss.START_DATE,gt.ASSIGNMENT_TYPE_ID,gt.FINAL_GRADE_PERCENT'), array(), array('STUDENT_ID'));
                            } else {

                                $points_RET = DBGet(DBQuery('SELECT DISTINCT s.STUDENT_ID,gt.ASSIGNMENT_TYPE_ID,  gt.ASSIGNMENT_TYPE_ID,sum(' . db_case(array('gg.POINTS', "'-1'", "'0'", 'gg.POINTS')) . ') AS PARTIAL_POINTS,
                                    sum(' . db_case(array('gg.POINTS', '\'-1\' OR gg.POINTS IS NULL  OR (ga.due_date <  (select DISTINCT ssm.start_date  from student_enrollment ssm where ssm.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SCHOOL_ID=' . UserSchool() . ' AND (ssm.START_DATE IS NOT NULL AND (CURRENT_DATE<=ssm.END_DATE OR CURRENT_DATE>=ssm.END_DATE OR  ssm.END_DATE IS NULL)) order by ssm.start_date desc limit 1)  ) ', "'0'", 'ga.POINTS')) . ') AS PARTIAL_TOTAL,
                                            gt.FINAL_GRADE_PERCENT FROM students s JOIN schedule ss ON (ss.STUDENT_ID=s.STUDENT_ID AND ss.COURSE_PERIOD_ID=\'' . $course_period_id . '\') JOIN gradebook_assignments ga ON ((ga.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID OR ga.COURSE_ID=\'' . $course_id . '\' AND ga.STAFF_ID=\'' . $teacher_id . '\') AND ga.MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\') LEFT OUTER JOIN gradebook_grades gg ON (gg.STUDENT_ID=s.STUDENT_ID AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID)
                                ,gradebook_assignment_types gt WHERE gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND gt.COURSE_ID=\'' . $course_id . '\' AND ((ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) OR gg.POINTS IS NOT NULL) GROUP BY s.STUDENT_ID,ss.START_DATE,gt.ASSIGNMENT_TYPE_ID,gt.FINAL_GRADE_PERCENT'), array(), array('STUDENT_ID'));
                            }
                        } else {
                            $course_periods = DBGet(DBQuery('select marking_period_id from course_periods where course_period_id=' . UserCoursePeriod()));
                            if ($course_periods[1]['MARKING_PERIOD_ID'] == NULL) {
                                $school_years = DBGet(DBQuery('select marking_period_id from  school_years where  syear=' . UserSyear() . ' and school_id=' . UserSchool()));
                                $fy_mp_id = $school_years[1]['MARKING_PERIOD_ID'];

                                $points_RET = DBGet(DBQuery('SELECT DISTINCT  s.STUDENT_ID,\'-1\' AS ASSIGNMENT_TYPE_ID,sum(' . db_case(array('gg.POINTS', "'-1'", "'0'", 'gg.POINTS')) . ') AS PARTIAL_POINTS,
                                        sum(' . db_case(array('gg.POINTS', '\'-1\' OR gg.POINTS IS NULL  OR (ga.due_date <  (select DISTINCT ssm.start_date  from student_enrollment ssm where ssm.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SCHOOL_ID=' . UserSchool() . ' AND (ssm.START_DATE IS NOT NULL AND (CURRENT_DATE<=ssm.END_DATE OR CURRENT_DATE>=ssm.END_DATE OR  ssm.END_DATE IS NULL)) order by ssm.start_date desc limit 1)  ) ', "'0'", 'ga.POINTS')) . ') AS PARTIAL_TOTAL,
                                                   \'1\' AS FINAL_GRADE_PERCENT FROM students s JOIN schedule ss ON (ss.STUDENT_ID=s.STUDENT_ID AND ss.COURSE_PERIOD_ID=\'' . $course_period_id . '\') JOIN gradebook_assignments ga ON ((ga.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID OR ga.COURSE_ID=\'' . $course_id . '\' AND ga.STAFF_ID=\'' . UserWs('STAFF_ID') . '\') AND (ga.MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\' OR ga.MARKING_PERIOD_ID=\'' . $fy_mp_id . '\')) LEFT OUTER JOIN gradebook_grades gg ON (gg.STUDENT_ID=s.STUDENT_ID AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID) 
                                WHERE  ((ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) OR gg.POINTS IS NOT NULL) GROUP BY s.STUDENT_ID,ss.START_DATE,FINAL_GRADE_PERCENT'), array(), array('STUDENT_ID'));
                            } else {

                                $points_RET = DBGet(DBQuery('SELECT DISTINCT  s.STUDENT_ID,\'-1\' AS ASSIGNMENT_TYPE_ID,sum(' . db_case(array('gg.POINTS', "'-1'", "'0'", 'gg.POINTS')) . ') AS PARTIAL_POINTS,
                                        sum(' . db_case(array('gg.POINTS', '\'-1\' OR gg.POINTS IS NULL  OR (ga.due_date <  (select DISTINCT ssm.start_date  from student_enrollment ssm where ssm.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SCHOOL_ID=' . UserSchool() . ' AND (ssm.START_DATE IS NOT NULL AND (CURRENT_DATE<=ssm.END_DATE OR CURRENT_DATE>=ssm.END_DATE OR  ssm.END_DATE IS NULL)) order by ssm.start_date desc limit 1)  ) ', "'0'", 'ga.POINTS')) . ') AS PARTIAL_TOTAL,
                                           \'1\' AS FINAL_GRADE_PERCENT FROM students s JOIN schedule ss ON (ss.STUDENT_ID=s.STUDENT_ID AND ss.COURSE_PERIOD_ID=\'' . $course_period_id . '\') JOIN gradebook_assignments ga ON ((ga.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID OR ga.COURSE_ID=\'' . $course_id . '\' AND ga.STAFF_ID=\'' . $teacher_id . '\') AND ga.MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\') LEFT OUTER JOIN gradebook_grades gg ON (gg.STUDENT_ID=s.STUDENT_ID AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID) 
                                            WHERE  ((ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) OR gg.POINTS IS NOT NULL) GROUP BY s.STUDENT_ID,ss.START_DATE,FINAL_GRADE_PERCENT'), array(), array('STUDENT_ID'));
                            }
                        }

                        if (count($points_RET)) {
                            foreach ($points_RET as $student_id => $student) {
                                ##########   Previous Calculation Start ##########
                                $total = $total_percent = 0;
                                $student_points = $total_points = $percent_weights = array();
                                $tot_weighted_percent = array();
                                $assignment_type_count = array();
                                unset($student_points);
                                unset($total_points);
                                unset($percent_weights);
                                if ($programconfig[$staff_id]['WEIGHT'] == 'Y') {
                                    $assign_typ_wg = array();
                                    $tot_weight_grade = '';
                                    if ($course_periods[1]['MARKING_PERIOD_ID'] == NULL) {
                                        $sql = 'SELECT a.TITLE,t.TITLE AS ASSIGN_TYP,a.ASSIGNED_DATE,a.DUE_DATE, t.ASSIGNMENT_TYPE_ID, t.FINAL_GRADE_PERCENT AS WEIGHT_GRADE  ,  t.FINAL_GRADE_PERCENT,t.FINAL_GRADE_PERCENT as ASSIGN_TYP_WG,g.POINTS,a.POINTS AS TOTAL_POINTS,g.COMMENT,g.POINTS AS LETTER_GRADE,g.POINTS AS LETTERWTD_GRADE,CASE WHEN (a.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE>=a.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM gradebook_assignment_types t,gradebook_assignments a LEFT OUTER JOIN gradebook_grades g ON (a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND g.STUDENT_ID=\'' . $student_id . '\' AND g.COURSE_PERIOD_ID=\'' . $course_period_id . '\') WHERE   a.ASSIGNMENT_TYPE_ID=t.ASSIGNMENT_TYPE_ID AND (a.COURSE_PERIOD_ID=\'' . $course_period_id . '\' OR a.COURSE_ID=\'' . $course_id . '\' AND a.STAFF_ID=\'' . UserWs('STAFF_ID') . '\') AND t.COURSE_ID=\'' . $course_id . '\' AND (a.MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\' OR a.MARKING_PERIOD_ID=\'' . $fy_mp_id . '\')';
                                    } else {
                                        $sql = 'SELECT a.TITLE,t.TITLE AS ASSIGN_TYP,a.ASSIGNED_DATE,a.DUE_DATE,  t.ASSIGNMENT_TYPE_ID,   t.FINAL_GRADE_PERCENT AS WEIGHT_GRADE  , t.FINAL_GRADE_PERCENT,t.FINAL_GRADE_PERCENT as ASSIGN_TYP_WG,g.POINTS,a.POINTS AS TOTAL_POINTS,g.COMMENT,g.POINTS AS LETTER_GRADE,g.POINTS AS LETTERWTD_GRADE,CASE WHEN (a.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE>=a.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM gradebook_assignment_types t,gradebook_assignments a LEFT OUTER JOIN gradebook_grades g ON (a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND g.STUDENT_ID=\'' . $student_id . '\' AND g.COURSE_PERIOD_ID=\'' . $course_period_id . '\') WHERE   a.ASSIGNMENT_TYPE_ID=t.ASSIGNMENT_TYPE_ID AND (a.COURSE_PERIOD_ID=\'' . $course_period_id . '\' OR a.COURSE_ID=\'' . $course_id . '\' AND a.STAFF_ID=\'' . UserWs('STAFF_ID') . '\') AND t.COURSE_ID=\'' . $course_id . '\' AND a.MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\'';
                                    }
                                    $sql .= ' AND (a.POINTS!=\'0\' OR g.POINTS IS NOT NULL AND g.POINTS!=\'-1\') AND a.DUE_DATE>=(select DISTINCT start_date  from student_enrollment where STUDENT_ID =\'' . $student_id . '\' AND SYEAR=' . UserSyear() . ' AND SCHOOL_ID= ' . UserSchool() . ' ORDER BY START_DATE desc limit 1) ORDER BY a.ASSIGNMENT_ID';

                                    $grades_RET1 = DBGet(DBQuery($sql), array('ASSIGN_TYP_WG' => '_makeAssnWG', 'POINTS' => '_makeExtra', 'LETTER_GRADE' => '_makeExtra'));

                                    if (count($grades_RET1)) {

                                        foreach ($grades_RET1 as $key => $val) {
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


                                        foreach ($assignment_type_count as $assign_key => $value) {
                                            if ($tot_weight_grade == '')
                                                $tot_weight_grade = round((round(($tot_weighted_percent[$assign_key] / $value), 2) * $assign_typ_wg[$assign_key]) / 100, 2);
                                            else
                                                $tot_weight_grade = $tot_weight_grade + (round((round(($tot_weighted_percent[$assign_key] / $value), 2) * $assign_typ_wg[$assign_key]) / 100, 2));
                                        }

                                        // $tot_weight_grade=$tot_weight_grade/100;

                                        //------------------------------------------------------//

                                        if (check_exam($mp_id) == 'Y') {
                                            $sql = 'select * from student_report_card_grades where student_id=\'' . $student_id . '\' and marking_period_id=\'E' . $mp_id . '\' and course_period_id=\'' . $course_period_id . '\'';
                                            $qr_exam = DBGet(DBQuery($sql));
                                            $grade_percent = $qr_exam[1]['GRADE_PERCENT'];
                                        } else {
                                            $grade_percent = 0;
                                        }
                                        $mp1 = $mp_id;

                                        if (check_exam($mp_id) == 'Y') {
                                            $mpex = '';
                                            $mpex .= "E" . $mp_id;
                                        }

                                        $prefix = 'Q-';


                                        //echo $total;
                                        $total1 = 0;
                                        if ($programconfig[UserWs('STAFF_ID')][$prefix . $mp1] != '') {

                                            $total1 += (round($tot_weight_grade, 2)) * ($programconfig[UserWs('STAFF_ID')][$prefix . $mp1] / 100);

                                            //                        break;
                                        }
                                        if ($programconfig[UserWs('STAFF_ID')][$prefix . $mpex] != '') {
                                            $total1 += ($grade_percent * $programconfig[UserWs('STAFF_ID')][$prefix . $mpex]) / 100;
                                            $temp_flag = 1;
                                            //                        break;
                                        }
                                        if ($total1 != 0) {
                                            $tot_weight_grade = $total1;
                                        }
                                        $import_RET[$student_id] = array(1 => array('REPORT_CARD_GRADE_ID' => _makeLetterGrade($tot_weight_grade / 100, $course_period_id, 0, 'ID'), 'GRADE_PERCENT' => _makeLetterGrade($tot_weight_grade / 100, $course_period_id, UserWs('STAFF_ID'), '%') . '%'));
                                    }
                                    //$tot_weight_grade=$tot_weight_grade/100;

                                    //$import_RET[$student_id] = array(1=>array('REPORT_CARD_GRADE_ID'=>_makeLetterGrade($tot_weight_grade,$course_period_id,0,'ID'),'GRADE_PERCENT'=>_makeLetterGrade($tot_weight_grade,$course_period_id,UserWs('STAFF_ID'),'%').'%'));

                                    //}
                                    else {
                                        foreach ($student as $partial_points) {
                                            if ($partial_points['PARTIAL_TOTAL'] != 0) {
                                                $total += $partial_points['PARTIAL_POINTS'] * $partial_points['FINAL_GRADE_PERCENT'] / $partial_points['PARTIAL_TOTAL'];
                                                $total_percent += $partial_points['FINAL_GRADE_PERCENT'];
                                            }
                                        }
                                        if (check_exam($mp_id) == 'Y') {
                                            $sql = 'select * from student_report_card_grades where student_id=\'' . $student_id . '\' and marking_period_id=\'E' . $mp_id . '\' and course_period_id=\'' . $course_period_id . '\'';
                                            $qr_exam = DBGet(DBQuery($sql));
                                            $grade_percent = $qr_exam[1]['GRADE_PERCENT'];
                                        } else {
                                            $grade_percent = 0;
                                        }
                                        $mp1 = $mp_id;

                                        if (check_exam($mp_id) == 'Y') {
                                            $mpex = '';
                                            $mpex .= "E" . $mp_id;
                                        }

                                        $prefix = 'Q-';
                                        //echo $total;
                                        $total1 = 0;
                                        if ($programconfig[UserWs('STAFF_ID')][$prefix . $mp1] != '') {

                                            $total1 += (round(100 * $total, 2)) * ($programconfig[UserWs('STAFF_ID')][$prefix . $mp1] / 100);
                                            $temp_flag = 1;
                                            //                        break;
                                        }
                                        //                    
                                        if ($programconfig[UserWs('STAFF_ID')][$prefix . $mpex] != '') {

                                            $total1 += ($grade_percent * $programconfig[UserWs('STAFF_ID')][$prefix . $mpex]) / 100;
                                            //                          break;
                                        }
                                        if ($total1 != 0) {
                                            $total = $total1 / 100;
                                        }
                                        $import_RET[$student_id] = array(1 => array('REPORT_CARD_GRADE_ID' => _makeLetterGrade($total, $course_period_id, 0, 'ID'), 'GRADE_PERCENT' => round(100 * $total, 2)));
                                    }
                                } else {

                                    foreach ($student as $partial_points)
                                        if ($partial_points['PARTIAL_TOTAL'] != 0) {
                                            $partial_points['PARTIAL_POINTS'] . '*' . $partial_points['FINAL_GRADE_PERCENT'] . ' /' . $partial_points['PARTIAL_TOTAL'];
                                            $total += $partial_points['PARTIAL_POINTS'] * $partial_points['FINAL_GRADE_PERCENT'] / $partial_points['PARTIAL_TOTAL'];
                                            $total_percent += $partial_points['FINAL_GRADE_PERCENT'];
                                        }
                                    if (check_exam($mp_id) == 'Y') {
                                        $sql = 'select * from student_report_card_grades where student_id=\'' . $student_id . '\' and marking_period_id=\'E' . $mp_id . '\' and course_period_id=\'' . $course_period_id . '\'';
                                        $qr_exam = DBGet(DBQuery($sql));
                                        $grade_percent = $qr_exam[1]['GRADE_PERCENT'];
                                    } else {
                                        $grade_percent = 0;
                                    }
                                    $mp1 = $mp_id;

                                    if (check_exam($mp_id) == 'Y') {
                                        $mpex = '';
                                        $mpex .= "E" . $mp_id;
                                    }

                                    $prefix = 'Q-';

                                    //echo $total;
                                    $total1 = 0;
                                    if ($programconfig[UserWs('STAFF_ID')][$prefix . $mp1] != '') {
                                        $total1 += (round(100 * $total, 2)) * ($programconfig[UserWs('STAFF_ID')][$prefix . $mp1] / 100);
                                        //                        break;
                                    }
                                    if ($programconfig[UserWs('STAFF_ID')][$prefix . $mpex] != '') {

                                        $total1 += ($grade_percent * $programconfig[UserWs('STAFF_ID')][$prefix . $mpex]) / 100;
                                        $temp_flag = 1;

                                        //                        break;
                                    }
                                    if ($total1 != 0) {
                                        $total = $total1 / 100;
                                    }
                                    $import_RET[$student_id] = array(1 => array('REPORT_CARD_GRADE_ID' => _makeLetterGrade($total, $course_period_id, 0, 'ID'), 'GRADE_PERCENT' => round(100 * $total, 2)));
                                }
                            }
                        }
                    } elseif (GetMP($_REQUEST['mp_id'], 'TABLE', UserSyear(), UserSchool()) == 'school_semesters' || GetMP($_REQUEST['mp_id'], 'TABLE', UserSyear(), UserSchool()) == 'school_years') {
                        if ($sem || (GetMP($_REQUEST['mp_id'], 'TABLE', UserSyear(), UserSchool()) == 'school_years') && $fy) {
                            if (GetMP($_REQUEST['mp_id'], 'TABLE', UserSyear(), UserSchool()) == 'school_semesters') {
                                $RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,\'Y\' AS DOES_GRADES,NULL AS DOES_EXAM FROM school_quarters WHERE SEMESTER_ID=\'' . $_REQUEST['mp_id'] . '\' UNION SELECT MARKING_PERIOD_ID,NULL AS DOES_GRADES,DOES_EXAM FROM school_semesters WHERE MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\''));
                                $prefix = 'SEM-';
                            } else {
                                $RET = DBGet(DBQuery('SELECT q.marking_period_id,\'Y\' AS DOES_GRADES,NULL AS DOES_EXAM FROM school_quarters q,school_semesters s WHERE q.SEMESTER_ID=s.MARKING_PERIOD_ID AND s.YEAR_ID=\'' . $_REQUEST['mp_id'] . '\' UNION SELECT MARKING_PERIOD_ID,DOES_GRADES,DOES_EXAM FROM school_semesters WHERE YEAR_ID=\'' . $_REQUEST['mp_id'] . '\' UNION SELECT MARKING_PERIOD_ID,NULL AS DOES_GRADES,DOES_EXAM FROM school_years WHERE MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\''));
                                $prefix = 'FY-';
                            }
                            foreach ($RET as $mp) {
                                if ($mp['DOES_GRADES'] == 'Y')
                                    $mps .= "'$mp[MARKING_PERIOD_ID]',";
                                if ($mp['DOES_EXAM'] == 'Y')
                                    $mps .= "'E$mp[MARKING_PERIOD_ID]',";
                            }
                            foreach ($RET as $mp) {
                                if ($mp['DOES_GRADES'] == 'Y')
                                    $mps1 .= "$mp[MARKING_PERIOD_ID],";
                                if ($mp['DOES_EXAM'] == 'Y')
                                    $mps1 .= "E$mp[MARKING_PERIOD_ID],";
                            }
                            $mps = substr($mps, 0, -1);
                            $temp_mps = explode(',', $mps);
                            $mps1 = substr($mps1, 0, -1);
                            $temp_mps1 = explode(',', $mps1);
                            $percents_RET = DBGet(DBQuery('SELECT STUDENT_ID,GRADE_PERCENT,MARKING_PERIOD_ID FROM student_report_card_grades WHERE COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND MARKING_PERIOD_ID IN (' . $mps . ')'), array(), array('STUDENT_ID'));
                            $temp_flag = 0;

                            foreach ($temp_mps1 as $mp1) {
                                if ($programconfig[$staff_id][$prefix . $mp1] != '') {
                                    $temp_flag = 1;
                                    break;
                                }
                            }
                            foreach ($temp_mps1 as $mp1) {
                                if ($programconfig[$staff_id][$prefix . $mp1] == '' && $temp_flag == 1) {
                                    $programconfig[$staff_id][$prefix . $mp1] = 0;
                                }
                            }
                            $temp_flag = 0;
                            foreach ($percents_RET as $student_id => $percents) {
                                $total = $total_percent = 0;
                                foreach ($percents as $percent) {
                                    if ($programconfig[$staff_id][$prefix . $percent['MARKING_PERIOD_ID']] >= 0) {
                                        $total += $percent['GRADE_PERCENT'] * $programconfig[$staff_id][$prefix . $percent['MARKING_PERIOD_ID']];
                                        $total_percent += $programconfig[$staff_id][$prefix . $percent['MARKING_PERIOD_ID']];
                                    } else {

                                        //$total += $percent['GRADE_PERCENT'] * $programconfig[UserWs('STAFF_ID')][$prefix.$percent['MARKING_PERIOD_ID']];
                                        //$total_percent += $programconfig[UserWs('STAFF_ID')][$prefix.$percent['MARKING_PERIOD_ID']];
                                        $total += $percent['GRADE_PERCENT'];
                                        $temp_flag++;
                                    }
                                }
                                if ($programconfig[$staff_id][$prefix . $percent['MARKING_PERIOD_ID']] == '' && $temp_flag > 0)
                                    $total_percent = count(explode(",", $mps));

                                //$total /= $total_percent;
                                $total /= 100;


                                if (check_exam($mp_id) == 'Y') {
                                    $sql = 'select * from student_report_card_grades where student_id=\'' . $student_id . '\' and marking_period_id=\'E' . $mp_id . '\' and course_period_id=\'' . $course_period_id . '\'';
                                    $qr_exam = DBGet(DBQuery($sql));
                                    $grade_percent = $qr_exam[1]['GRADE_PERCENT'];
                                } else {
                                    $grade_percent = 0;
                                }
                                $mp1 = $mp_id;

                                if (check_exam($mp_id) == 'Y') {
                                    $mpex = '';
                                    $mpex .= "E" . $mp_id;
                                }

                                if (GetMP($_REQUEST['mp_id'], 'TABLE') == 'school_semesters') {

                                    $prefix = 'SEM-';
                                } else {
                                    $prefix = 'FY-';
                                }


                                //echo $total;
                                $total1 = 0;
                                //  if($programconfig[UserWs('STAFF_ID')][$prefix.$mp1]!='')
                                //                    {
                                //      
                                //      $total1 +=(round(100*$total,2))*($programconfig[UserWs('STAFF_ID')][$prefix.$mp1]/100);
                                //                        
                                ////                        break;
                                //                    }
                                //                    
                                if ($programconfig[$staff_id][$prefix . $mpex] != '') {
                                    $total1 += ($grade_percent * $programconfig[$staff_id][$prefix . $mpex]) / 100;
                                    $temp_flag = 1;
                                    //                        break;
                                }
                                if ($total1 != 0) {
                                    $total = $total1 / 100;
                                }
                                $import_RET[$student_id] = array(1 => array('REPORT_CARD_GRADE_ID' => _makeLetterGrade($total, $course_period_id, $staff_id, 'ID'), 'GRADE_PERCENT' => round($total, 2)));
                            }
                        } else {
                            if ($_REQUEST['custom_cp'] == 'y')
                                $gg_mp = $_REQUEST['mp'];
                            else
                                $gg_mp =  $mp_id;
                            if ($programconfig[UserWs('STAFF_ID')]['WEIGHT'] == 'Y')
                                $points_RET = DBGet(DBQuery('SELECT DISTINCT s.STUDENT_ID,gt.ASSIGNMENT_TYPE_ID,  gt.ASSIGNMENT_TYPE_ID,sum(' . db_case(array('gg.POINTS', "'-1'", "'0'", 'gg.POINTS')) . ') AS PARTIAL_POINTS,sum(' . db_case(array('gg.POINTS', '\'-1\' OR (ga.due_date <  (select DISTINCT ssm.start_date  from student_enrollment ssm where ssm.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SCHOOL_ID=' . UserSchool() . ' AND (ssm.START_DATE IS NOT NULL AND (CURRENT_DATE<=ssm.END_DATE OR CURRENT_DATE>=ssm.END_DATE OR  ssm.END_DATE IS NULL)) order by ssm.start_date desc limit 1
        )  ) ', '\'0\'', 'ga.POINTS')) . ') AS PARTIAL_TOTAL,gt.FINAL_GRADE_PERCENT FROM students s JOIN schedule ss ON (ss.STUDENT_ID=s.STUDENT_ID AND ss.COURSE_PERIOD_ID=\'' . $course_period_id . '\') JOIN gradebook_assignments ga ON ((ga.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID OR ga.COURSE_ID=\'' . $course_id . '\' AND ga.STAFF_ID=\'' . UserWs('STAFF_ID') . '\') AND ga.MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\') LEFT OUTER JOIN gradebook_grades gg ON (gg.STUDENT_ID=s.STUDENT_ID AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID),gradebook_assignment_types gt WHERE gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND gt.COURSE_ID=\'' . $course_id . '\' AND ((ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) OR gg.POINTS IS NOT NULL) GROUP BY s.STUDENT_ID,ss.START_DATE,gt.ASSIGNMENT_TYPE_ID,gt.FINAL_GRADE_PERCENT'), array(), array('STUDENT_ID'));
                            else
                                $points_RET = DBGet(DBQuery('SELECT DISTINCT  s.STUDENT_ID,\'-1\' AS ASSIGNMENT_TYPE_ID,sum(' . db_case(array('gg.POINTS', "'-1'", "'0'", 'gg.POINTS')) . ') AS PARTIAL_POINTS,sum(' . db_case(array('gg.POINTS', '\'-1\' OR (ga.due_date <  (select DISTINCT ssm.start_date  from student_enrollment ssm where ssm.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SCHOOL_ID=' . UserSchool() . ' AND (ssm.START_DATE IS NOT NULL AND (CURRENT_DATE<=ssm.END_DATE OR CURRENT_DATE>=ssm.END_DATE OR  ssm.END_DATE IS NULL)) order by ssm.start_date desc limit 1
)  ) ', '\'0\'', 'ga.POINTS')) . ') AS PARTIAL_TOTAL,\'1\' AS FINAL_GRADE_PERCENT FROM students s JOIN schedule ss ON (ss.STUDENT_ID=s.STUDENT_ID AND ss.COURSE_PERIOD_ID=\'' . $course_period_id . '\') JOIN gradebook_assignments ga ON ((ga.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID OR ga.COURSE_ID=\'' . $course_id . '\' AND ga.STAFF_ID=\'' . UserWs('STAFF_ID') . '\') AND ga.MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\') LEFT OUTER JOIN gradebook_grades gg ON (gg.STUDENT_ID=s.STUDENT_ID AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID)   WHERE gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND ((ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) OR gg.POINTS IS NOT NULL) GROUP BY s.STUDENT_ID,ss.START_DATE,FINAL_GRADE_PERCENT'), array(), array('STUDENT_ID'));

                            if (count($points_RET)) {
                                foreach ($points_RET as $student_id => $student) {
                                    $total = $total_percent = 0;
                                    $student_points = $total_points = $percent_weights = array();
                                    $tot_weighted_percent = array();
                                    $assignment_type_count = array();
                                    unset($student_points);
                                    unset($total_points);
                                    unset($percent_weights);
                                    if ($programconfig[$staff_id]['WEIGHT'] == 'Y') {
                                        $assign_typ_wg = array();
                                        $tot_weight_grade = '';
                                        $tot_weight_grade_scale = '';
                                        $course_periods = DBGet(DBQuery('select marking_period_id from course_periods where course_period_id=' . UserCoursePeriod()));
                                        if ($course_periods[1]['MARKING_PERIOD_ID'] == NULL) {
                                            $school_years = DBGet(DBQuery('select marking_period_id from  school_years where  syear=' . UserSyear() . ' and school_id=' . UserSchool()));
                                            $fy_mp_id = $school_years[1]['MARKING_PERIOD_ID'];
                                            $sql = 'SELECT a.TITLE,t.TITLE AS ASSIGN_TYP,a.ASSIGNED_DATE,a.DUE_DATE, t.ASSIGNMENT_TYPE_ID, t.FINAL_GRADE_PERCENT AS WEIGHT_GRADE  ,  t.FINAL_GRADE_PERCENT,t.FINAL_GRADE_PERCENT as ASSIGN_TYP_WG,g.POINTS,a.POINTS AS TOTAL_POINTS,g.COMMENT,g.POINTS AS LETTER_GRADE,g.POINTS AS LETTERWTD_GRADE,CASE WHEN (a.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE>=a.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM gradebook_assignment_types t,gradebook_assignments a LEFT OUTER JOIN gradebook_grades g ON (a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND g.STUDENT_ID=\'' . $student_id . '\' AND g.COURSE_PERIOD_ID=\'' . $course_period_id . '\') WHERE   a.ASSIGNMENT_TYPE_ID=t.ASSIGNMENT_TYPE_ID AND (a.COURSE_PERIOD_ID=\'' . $course_period_id . '\' OR a.COURSE_ID=\'' . $course_id . '\' AND a.STAFF_ID=\'' . UserWs('STAFF_ID') . '\') AND t.COURSE_ID=\'' . $course_id . '\' AND (a.MARKING_PERIOD_ID=\'' . $current_mp . '\' OR a.MARKING_PERIOD_ID=\'' . $fy_mp_id . '\')';
                                        } else {
                                            $sql = 'SELECT a.TITLE,t.TITLE AS ASSIGN_TYP,a.ASSIGNED_DATE,a.DUE_DATE,  t.ASSIGNMENT_TYPE_ID,   t.FINAL_GRADE_PERCENT AS WEIGHT_GRADE  , t.FINAL_GRADE_PERCENT,t.FINAL_GRADE_PERCENT as ASSIGN_TYP_WG,g.POINTS,a.POINTS AS TOTAL_POINTS,g.COMMENT,g.POINTS AS LETTER_GRADE,g.POINTS AS LETTERWTD_GRADE,CASE WHEN (a.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE>=a.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM gradebook_assignment_types t,gradebook_assignments a LEFT OUTER JOIN gradebook_grades g ON (a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND g.STUDENT_ID=\'' . $student_id . '\' AND g.COURSE_PERIOD_ID=\'' . $course_period_id . '\') WHERE   a.ASSIGNMENT_TYPE_ID=t.ASSIGNMENT_TYPE_ID AND (a.COURSE_PERIOD_ID=\'' . $course_period_id . '\' OR a.COURSE_ID=\'' . $course_id . '\' AND a.STAFF_ID=\'' . UserWs('STAFF_ID') . '\') AND t.COURSE_ID=\'' . $course_id . '\' AND a.MARKING_PERIOD_ID=\'' . $current_mp . '\'';
                                        }
                                        $sql .= ' AND (a.POINTS!=\'0\' OR g.POINTS IS NOT NULL AND g.POINTS!=\'-1\') AND a.DUE_DATE>=(select DISTINCT start_date  from student_enrollment where STUDENT_ID =\'' . $student_id . '\' AND SYEAR=' . UserSyear() . ' AND SCHOOL_ID= ' . UserSchool() . ' ORDER BY START_DATE desc limit 1) ORDER BY a.ASSIGNMENT_ID';

                                        $grades_RET1 = DBGet(DBQuery($sql), array('ASSIGN_TYP_WG' => '_makeAssnWG', 'POINTS' => '_makeExtra', 'LETTER_GRADE' => '_makeExtra'));

                                        if (count($grades_RET1)) {
                                            foreach ($grades_RET1 as $key => $val) {
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
                                            foreach ($assignment_type_count as $assign_key => $value) {
                                                if ($tot_weight_grade == '')
                                                    $tot_weight_grade = round((round(($tot_weighted_percent[$assign_key] / $value), 2) * $assign_typ_wg[$assign_key]) / 100, 2);
                                                else
                                                    $tot_weight_grade = $tot_weight_grade + (round((round(($tot_weighted_percent[$assign_key] / $value), 2) * $assign_typ_wg[$assign_key]) / 100, 2));
                                            }

                                            //$tot_weight_grade=$tot_weight_grade/100;
                                            $tot_weight_grade_scale = $tot_weight_grade / 100;
                                            //--------------------------------------------------------------//  
                                            if (check_exam($current_mp) == 'Y') {
                                                $sql = 'select * from student_report_card_grades where student_id=\'' . $student_id . '\' and marking_period_id=\'E' . $current_mp . '\' and course_period_id=\'' . $course_period_id . '\'';
                                                $qr_exam = DBGet(DBQuery($sql));
                                                $grade_percent = $qr_exam[1]['GRADE_PERCENT'];
                                            } else {
                                                $grade_percent = 0;
                                            }
                                            $mp1 = $current_mp;

                                            if (check_exam($current_mp) == 'Y') {
                                                $mpex = '';
                                                $mpex .= "E" . $current_mp;
                                            }

                                            if (GetMP($_REQUEST['mp'], 'TABLE') == 'school_semesters') {

                                                $prefix = 'SEM-';
                                            } else {
                                                $prefix = 'FY-';
                                            }


                                            $total1 = 0;
                                            if ($programconfig[UserWs('STAFF_ID')][$prefix . $mp1] != '') {

                                                $total1 += (round($tot_weight_grade, 2)) * ($programconfig[UserWs('STAFF_ID')][$prefix . $mp1] / 100);
                                            }
                                            if ($programconfig[UserWs('STAFF_ID')][$prefix . $mpex] != '') {

                                                $total1 += ($grade_percent * $programconfig[UserWs('STAFF_ID')][$prefix . $mpex]) / 100;
                                                $temp_flag = 1;
                                            }
                                            if ($total1 != 0) {
                                                $tot_weight_grade = $total1;
                                            }


                                            //-----------------------------------------------------------------//
                                            $import_RET[$student_id] = array(1 => array('REPORT_CARD_GRADE_ID' => _makeLetterGrade($tot_weight_grade_scale, $course_period_id, 0, 'ID'), 'GRADE_PERCENT' => _makeLetterGrade($tot_weight_grade / 100, $course_period_id, UserWs('STAFF_ID'), '%') . '%'));
                                        } else {
                                            foreach ($student as $partial_points)
                                                if ($partial_points['PARTIAL_TOTAL'] != 0) {
                                                    $total += $partial_points['PARTIAL_POINTS'] * $partial_points['FINAL_GRADE_PERCENT'] / $partial_points['PARTIAL_TOTAL'];
                                                    $total_percent += $partial_points['FINAL_GRADE_PERCENT'];
                                                }


                                            if (check_exam($mp_id) == 'Y') {
                                                $sql = 'select * from student_report_card_grades where student_id=\'' . $student_id . '\' and marking_period_id=\'E' . $mp_id . '\' and course_period_id=\'' . $course_period_id . '\'';
                                                $qr_exam = DBGet(DBQuery($sql));
                                                $grade_percent = $qr_exam[1]['GRADE_PERCENT'];
                                            } else {
                                                $grade_percent = 0;
                                            }
                                            $mp1 = $mp_id;

                                            if (check_exam($mp_id) == 'Y') {
                                                $mpex = '';
                                                $mpex .= "E" . $mp_id;
                                            }

                                            if (GetMP($_REQUEST['mp'], 'TABLE') == 'school_semesters') {

                                                $prefix = 'SEM-';
                                            } else {
                                                $prefix = 'FY-';
                                            }


                                            //echo $total;
                                            $total1 = 0;
                                            if ($programconfig[UserWs('STAFF_ID')][$prefix . $mp1] != '') {

                                                $total1 += (round(100 * $total, 2)) * ($programconfig[UserWs('STAFF_ID')][$prefix . $mp1] / 100);

                                                //                        break;
                                            }
                                            //                    
                                            if ($programconfig[UserWs('STAFF_ID')][$prefix . $mpex] != '') {

                                                $total1 += ($grade_percent * $programconfig[UserWs('STAFF_ID')][$prefix . $mpex]) / 100;
                                                $temp_flag = 1;

                                                //                        break;
                                            }
                                            if ($total1 != 0) {
                                                $total = $total1 / 100;
                                            }

                                            $import_RET[$student_id] = array(1 => array('REPORT_CARD_GRADE_ID' => _makeLetterGrade($total, $course_period_id, 0, 'ID'), 'GRADE_PERCENT' => round(100 * $total, 2)));
                                        }
                                    } else {
                                        foreach ($student as $partial_points)
                                            if ($partial_points['PARTIAL_TOTAL'] != 0) {
                                                $total += $partial_points['PARTIAL_POINTS'] * $partial_points['FINAL_GRADE_PERCENT'] / $partial_points['PARTIAL_TOTAL'];
                                                $total_percent += $partial_points['FINAL_GRADE_PERCENT'];
                                            }
                                        $import_RET[$student_id] = array(1 => array('REPORT_CARD_GRADE_ID' => _makeLetterGrade($total, $course_period_id, 0, 'ID'), 'GRADE_PERCENT' => round(100 * $total, 2)));

                                        if (check_exam($mp_id) == 'Y') {
                                            $sql = 'select * from student_report_card_grades where student_id=\'' . $student_id . '\' and marking_period_id=\'E' . $mp_id . '\' and course_period_id=\'' . $course_period_id . '\'';
                                            $qr_exam = DBGet(DBQuery($sql));
                                            $grade_percent = $qr_exam[1]['GRADE_PERCENT'];
                                        } else {
                                            $grade_percent = 0;
                                        }
                                        $mp1 = $mp_id;

                                        if (check_exam($mp_id) == 'Y') {
                                            $mpex = '';
                                            $mpex .= "E" . $mp_id;
                                        }

                                        if (GetMP($_REQUEST['mp'], 'TABLE') == 'school_semesters') {

                                            $prefix = 'SEM-';
                                        } else {
                                            $prefix = 'FY-';
                                        }


                                        //echo $total;
                                        $total1 = 0;
                                        if ($programconfig[UserWs('STAFF_ID')][$prefix . $mp1] != '') {

                                            $total1 += (round(100 * $total, 2)) * ($programconfig[UserWs('STAFF_ID')][$prefix . $mp1] / 100);

                                            //                        break;
                                        }
                                        //                    
                                        if ($programconfig[UserWs('STAFF_ID')][$prefix . $mpex] != '') {

                                            $total1 += ($grade_percent * $programconfig[UserWs('STAFF_ID')][$prefix . $mpex]) / 100;
                                            $temp_flag = 1;

                                            //                        break;
                                        }
                                        if ($total1 != 0) {
                                            $total = $total1 / 100;
                                        }
                                        //---------------------------------------------------//           


                                        $import_RET[$student_id] = array(1 => array('REPORT_CARD_GRADE_ID' => _makeLetterGrade($total, $course_period_id, 0, 'ID'), 'GRADE_PERCENT' => round(100 * $total, 2)));
                                    }
                                }
                            }
                        }
                    }
                }
                unset($_SESSION['_REQUEST_vars']['modfunc']);
            }

            if ($_REQUEST['fetch_type'] == 'grades') {
                if ($_REQUEST['prev_mp']) {
                    //	include 'MakePercentGradeFnc.php';
                    $import_RET = DBGet(DBQuery('SELECT g.STUDENT_ID,g.REPORT_CARD_GRADE_ID,g.GRADE_PERCENT FROM student_report_card_grades g,course_periods cp WHERE cp.COURSE_PERIOD_ID=g.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND g.MARKING_PERIOD_ID=\'' . $_REQUEST['prev_mp'] . '\''), array(), array('STUDENT_ID'));
                    //	foreach($import_RET as $student_id=>$grade)
                    //		$import_RET[$student_id][1]['GRADE_PERCENT'] = _makePercentGrade($grade[1]['REPORT_CARD_GRADE_ID'],$course_period_id);

                    //	unset($_SESSION['_REQUEST_vars']['prev_mp']);
                }
                //	unset($_SESSION['_REQUEST_vars']['modfunc']);
            }

            if ($_REQUEST['fetch_type'] == 'comments') {
                if ($_REQUEST['prev_mp']) {
                    $import_comments_RET = DBGet(DBQuery('SELECT g.STUDENT_ID,g.REPORT_CARD_COMMENT_ID,g.COMMENT FROM student_report_card_grades g WHERE g.COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND g.MARKING_PERIOD_ID=\'' . $_REQUEST['prev_mp'] . '\''), array(), array('STUDENT_ID'));
                    $import_commentsA_RET = DBGet(DBQuery('SELECT g.STUDENT_ID,g.REPORT_CARD_COMMENT_ID,g.COMMENT FROM student_report_card_comments g WHERE g.COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND g.MARKING_PERIOD_ID=\'' . $_REQUEST['prev_mp'] . '\' AND g.REPORT_CARD_COMMENT_ID IN (SELECT ID FROM report_card_comments WHERE COURSE_ID IS NOT NULL)'), array(), array('STUDENT_ID', 'REPORT_CARD_COMMENT_ID'));
                    $import_commentsB_RET = DBGet(DBQuery('SELECT g.STUDENT_ID,g.REPORT_CARD_COMMENT_ID FROM student_report_card_comments g WHERE g.COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND g.MARKING_PERIOD_ID=\'' . $_REQUEST['prev_mp'] . '\' AND g.REPORT_CARD_COMMENT_ID IN (SELECT ID FROM report_card_comments WHERE COURSE_ID IS NULL)'), array(), array('STUDENT_ID'));

                    foreach ($import_commentsB_RET as $comments)
                        if (count($comments) > $max_current_commentsB)
                            $max_current_commentsB = count($comments);

                    //	unset($_SESSION['_REQUEST_vars']['prev_mp']);
                }
                //	unset($_SESSION['_REQUEST_vars']['modfunc']);
            }

            if ($_REQUEST['action_type'] == 'clearall') {
                foreach ($current_RET as $student_id => $prev) {
                    $current_RET[$student_id][1]['REPORT_CARD_GRADE_ID'] = '';
                    $current_RET[$student_id][1]['GRADE_PERCENT'] = '';
                    $current_RET[$student_id][1]['COMMENT'] = '';
                }
                foreach ($current_commentsA_RET as $student_id => $comments)
                    foreach ($comments as $id => $comment)
                        $current_commentsA_RET[$student_id][$id][1]['COMMENT'] = '';
                foreach ($current_commentsB_RET as $student_id => $comment)
                    foreach ($comment as $i => $comment)
                        $current_commentsB_RET[$student_id][$i] = '';
                //	unset($_SESSION['_REQUEST_vars']['modfunc']);
            }

            if ($_REQUEST['values']) // && ($_POST['values'] || $_REQUEST['ajax']) && $_REQUEST['submit']['save']
            {
                //                if(isset($cp_type) && $cp_type=='custom')
                //                {
                //                     $full_year_mp = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID='.UserSchool().' AND SYEAR='.UserSyear()));
                //                     $_REQUEST['mp_id'] = $full_year_mp[1]['MARKING_PERIOD_ID'];
                //                }
                $values = $_REQUEST["values"];
                $grade_values = json_decode($values, TRUE);
                $completed = true;
                //        print_r($grade_values);
                //        print_R($current_RET);
                foreach ($grade_values as $values) {
                    foreach ($values as $student_id => $columns) {
                        $sql = $sep = '';
                        if ($current_RET[$student_id]) {
                            if (isset($columns['grade']) && $columns['grade'] != $current_RET[$student_id][1]['REPORT_CARD_GRADE_ID']) {
                                if ($columns['percent'] == '') {
                                    $sql .= 'REPORT_CARD_GRADE_ID=NULL , GRADE_PERCENT=NULL ';
                                    $sep = ',';
                                } else {
                                    if (substr($columns['grade'], -1) == '%') {
                                        $percent = substr($columns['grade'], 0, -1);
                                        $sql .= 'REPORT_CARD_GRADE_ID=\'' . _makeLetterGrade($percent / 100, $course_period_id, 0, 'ID') . '\'';
                                        $sql .= ',GRADE_PERCENT=\'' . $percent . '\'';
                                        $sep = ',';
                                    } elseif ($columns['grade'] != $current_RET[$student_id][1]['REPORT_CARD_GRADE_ID']) {
                                        if ($columns['grade'] != '')
                                            $sql .= 'REPORT_CARD_GRADE_ID=\'' . $columns['grade'] . '\'';
                                        else
                                            $sql .= 'REPORT_CARD_GRADE_ID = NULL';
                                        $sql .= ',GRADE_PERCENT=\'' . ($columns['grade'] == '' ? '' : _makePercentGrade($columns['grade'], $course_period_id)) . '\'';

                                        $sep = ',';
                                    }
                                }


                                //bjj can we use $percent all the time?  TODO: rework this so updates to credits occur when grade is changed
                                $grade_title_RET = DBGet(DBQuery('SELECT TITLE FROM report_card_grades WHERE ID=\'' . str_replace("\'", "''", $columns['grade']) . '\''));
                                $sql .= ',GRADE_LETTER=\'' . $grade_title_RET[1]['TITLE'] . '\'';
                                if ($course_RET[1]['COURSE_WEIGHT']) {
                                    $sql .= ',WEIGHTED_GP=\'' . $grades_RET[$columns['grade']][1]['WEIGHTED_GP'] . '\'';
                                    $sql .= ',CREDIT_EARNED=\'' . ($grades_RET[$columns['grade']][1]['WEIGHTED_GP'] > 0 ? $course_RET[1]['CREDITS'] : 0) . '\'';
                                } else {
                                    $sql .= ',UNWEIGHTED_GP=\'' . $grades_RET[$columns['grade']][1]['UNWEIGHTED_GP'] . '\'';
                                    $sql .= ',CREDIT_EARNED=\'' . ($grades_RET[$columns['grade']][1]['UNWEIGHTED_GP'] > 0 ? $course_RET[1]['CREDITS'] : 0) . '\'';
                                }
                                $sql .= ',COURSE_TITLE=\'' . $course_RET[1]['COURSE_NAME'] . '\'';
                                $sql .= ',GP_SCALE=\'' . $grades_RET[$columns['grade']][1]['GP_SCALE'] . '\'';
                                $sql .= ',CREDIT_ATTEMPTED=\'' . $course_RET[1]['CREDITS'] . '\'';
                            } elseif (isset($columns['percent']) && $columns['percent'] != $current_RET[$student_id][1]['GRADE_PERCENT']) {
                                if ($columns['percent'] == '') {
                                    $sql .= 'REPORT_CARD_GRADE_ID=NULL , GRADE_PERCENT=NULL ';
                                    $sep = ',';
                                    $gp_id = "";
                                } else {
                                    $percent = rtrim($columns['percent'], '%') + 0;
                                    if ($percent > 999.9)
                                        $percent = 999.9;
                                    elseif ($percent < 0)
                                        $percent = 0;
                                    $gp_id = _makeLetterGrade($percent / 100, $course_period_id, 0, 'ID');
                                    $sql .= 'REPORT_CARD_GRADE_ID=\'' . $gp_id . '\'';
                                    if ($gp_id)
                                        $grade_title_RET = DBGet(DBQuery("SELECT TITLE FROM report_card_grades WHERE ID=$gp_id"));
                                    $sql .= ',GRADE_LETTER=\'' . $grade_title_RET[1]['TITLE'] . '\'';


                                    if ($course_RET[1]['GRADE_SCALE_ID'] != '') {
                                        if ($course_RET[1]['COURSE_WEIGHT']) {
                                            $sql .= ',WEIGHTED_GP=\'' . $grades_RET[$gp_id][1]['WEIGHTED_GP'] . '\'';
                                            $sql .= ',CREDIT_EARNED=\'' . ($grades_RET[$gp_id][1]['WEIGHTED_GP'] > 0 ? $course_RET[1]['CREDITS'] : 0) . '\'';
                                        } else {
                                            $sql .= ',UNWEIGHTED_GP=\'' . $grades_RET[$gp_id][1]['UNWEIGHTED_GP'] . '\'';
                                            $sql .= ',CREDIT_EARNED=\'' . ($grades_RET[$gp_id][1]['UNWEIGHTED_GP'] > 0 ? $course_RET[1]['CREDITS'] : 0) . '\'';
                                        }
                                    } else {
                                        if ($percent > 0)
                                            $sql .= ',CREDIT_EARNED=\'' . $course_RET[1]['CREDITS'] . '\'';
                                        else
                                            $sql .= ',CREDIT_EARNED=\'0\'';
                                    }
                                    $sql .= ',COURSE_TITLE=\'' . $course_RET[1]['COURSE_NAME'] . '\'';
                                    $sql .= ',GP_SCALE=\'' . $grades_RET[$gp_id][1]['GP_SCALE'] . '\'';
                                    $sql .= ',CREDIT_ATTEMPTED=\'' . $course_RET[1]['CREDITS'] . '\'';
                                    $sql .= ',GRADE_PERCENT=\'' . $percent . '\'';
                                    $sep = ',';
                                }
                            }

                            if (isset($columns['comment'])) {
                                $columns['comment'] = $columns['comment'];
                                $sql .= $sep;
                                if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                                    $columns['comment'] =  mysql_real_escape_string($columns['comment']);
                                }
                                $sql .= 'COMMENT=\'' . str_replace("'", "''", $columns['comment']) . ' \' ';
                            }


                            if ($sql)
                                $sql = 'UPDATE student_report_card_grades SET ' . $sql . ' WHERE STUDENT_ID=\'' . $student_id . '\' AND COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\'';
                        } elseif ($columns['grade'] || $columns['percent'] != '' || $columns['comment']) {
                            $grade = $percent = '';
                            if ($columns['grade'])
                                if (substr($columns['grade'], -1) == '%') {
                                    $percent = substr($columns['grade'], 0, -1);
                                    $grade = _makeLetterGrade($percent / 100, $course_period_id, 0, 'ID');
                                } else {
                                    $grade = $columns['grade'];
                                    $percent = _makePercentGrade($grade, $course_period_id);
                                }
                            elseif ($columns['percent'] != '') {
                                $percent = rtrim($columns['percent'], '%') + 0;
                                if ($percent > 999.9)
                                    $percent = 999.9;
                                elseif ($percent < 0)
                                    $percent = 0;
                                $grade = _makeLetterGrade($percent / 100, $course_period_id, 0, 'ID');
                            }

                            // $id = DBGet(DBQuery("SHOW TABLE STATUS LIKE 'student_report_card_grades'"));
                            // $grade_id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
                            // $ngrade_id = $grade_id[1]['ID'];

                            if (stripos($_SERVER['SERVER_SOFTWARE'], 'linux')) {
                                $columns['comment'] =  mysql_real_escape_string($columns['comment']);
                            }
                            if ($course_RET[1]['GRADE_SCALE_ID'] != '') {
                                if ($course_RET[1]['COURSE_WEIGHT']) {
                                    $WEIGHTED_GP = $grades_RET[$grade][1]['WEIGHTED_GP'];
                                    $CREDIT_EARNED = $grades_RET[$grade][1]['WEIGHTED_GP'] > 0 ? $course_RET[1]['CREDITS'] : 0;
                                } else {
                                    $UNWEIGHTED_GP = $grades_RET[$grade][1]['UNWEIGHTED_GP'];
                                    $CREDIT_EARNED = $grades_RET[$grade][1]['UNWEIGHTED_GP'] > 0 ? $course_RET[1]['CREDITS'] : 0;
                                }
                            } else {
                                if ($percent > 0)
                                    $CREDIT_EARNED = $course_RET[1]['CREDITS'];
                                else
                                    $CREDIT_EARNED = 0;
                            }
                            $sql = 'INSERT INTO student_report_card_grades (SYEAR,SCHOOL_ID,STUDENT_ID,COURSE_PERIOD_ID,MARKING_PERIOD_ID,REPORT_CARD_GRADE_ID,GRADE_PERCENT,
                               COMMENT,GRADE_LETTER,WEIGHTED_GP,UNWEIGHTED_GP,COURSE_TITLE,GP_SCALE,CREDIT_ATTEMPTED,CREDIT_EARNED)
                                                   values(\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'' . $student_id . '\',\'' . $course_period_id . '\',\'' . $_REQUEST['mp_id'] . '\',\'' . $grade . '\',\'' . $percent . '\',
                               \'' .  str_replace("'", "''", $columns['comment']) . ' \',\'' . $grades_RET[$grade][1]['TITLE'] . '\',\'' . $WEIGHTED_GP . '\',\'' . $UNWEIGHTED_GP . '\',\'' . $course_RET[1]['COURSE_NAME'] . '\',\'' . $grades_RET[$grade][1]['GP_SCALE'] . '\',\'' . $course_RET[1]['CREDITS'] . '\',\'' . $CREDIT_EARNED . '\')';
                        }

                        if ($sql) {

                            DBQuery($sql);
                        }

                        if (isset($columns['grade'])) {
                            if (!$columns['grade'])
                                $completed = false;
                        } elseif (isset($columns['percent'])) {
                            if (!$columns['percent'])
                                $completed = false;
                        }

                        foreach ($columns['commentsA'] as $id => $comment)
                            if ($current_commentsA_RET[$student_id][$id])
                                if ($comment)
                                    DBQuery('UPDATE student_report_card_comments SET COMMENT=\'' . str_replace("\'", "''", $comment) . '\' WHERE STUDENT_ID=\'' . $student_id . '\' AND COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\' AND REPORT_CARD_COMMENT_ID=\'' . $id . '\'');
                                else
                                    DBQuery("DELETE FROM student_report_card_comments WHERE STUDENT_ID=\'" . $student_id . "\' AND COURSE_PERIOD_ID=\'" . $course_period_id . "\' AND MARKING_PERIOD_ID=\'" . $_REQUEST['mp_id'] . "\' AND REPORT_CARD_COMMENT_ID=\'" . $id . "\'");
                            elseif ($comment)
                                DBQuery('INSERT INTO student_report_card_comments (SYEAR,SCHOOL_ID,STUDENT_ID,COURSE_PERIOD_ID,MARKING_PERIOD_ID,REPORT_CARD_COMMENT_ID,COMMENT)
						values(\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'' . $student_id . '\',\'' . $course_period_id . '\',\'' . $_REQUEST['mp_id'] . '\',\'' . $id . '\',\'' . $comment . '\')');

                        // create mapping for current
                        $old = array();
                        foreach ($current_commentsB_RET[$student_id] as $i => $comment)
                            $old[$comment['REPORT_CARD_COMMENT_ID']] = $i;
                        // create change list
                        $change = array();
                        foreach ($columns['commentsB'] as $i => $comment)
                            $change[$i] = array('REPORT_CARD_COMMENT_ID' => 0);
                        // prune changes already in current set and reserve if in change list
                        foreach ($columns['commentsB'] as $i => $comment)
                            if ($comment)
                                if ($old[$comment]) {
                                    if ($change[$old[$comment]])
                                        $change[$old[$comment]]['REPORT_CARD_COMMENT_ID'] = $comment;
                                    $columns['commentsB'][$i] = false;
                                }
                        // assign changes at their index if possible
                        $new = array();
                        foreach ($columns['commentsB'] as $i => $comment)
                            if ($comment)
                                if (!$new[$comment]) {
                                    if (!$change[$i]['REPORT_CARD_COMMENT_ID']) {
                                        $change[$i]['REPORT_CARD_COMMENT_ID'] = $comment;
                                        $new[$comment] = $i;
                                        $columns['commentsB'][$i] = false;
                                    }
                                } else
                                    $columns['commentsB'][$i] = false;
                        // assign remaining changes to first available
                        reset($change);
                        foreach ($columns['commentsB'] as $i => $comment)
                            if ($comment) {
                                if (!$new[$comment]) {
                                    while ($change[key($change)]['REPORT_CARD_COMMENT_ID'])
                                        next($change);
                                    $change[key($change)]['REPORT_CARD_COMMENT_ID'] = $comment;
                                    $new[$comment] = key($change);
                                }
                                $columns['commentsB'][$i] = false;
                            }

                        // update the db
                        foreach ($change as $i => $comment)
                            if ($current_commentsB_RET[$student_id][$i])
                                if ($comment['REPORT_CARD_COMMENT_ID']) {
                                    if ($comment['REPORT_CARD_COMMENT_ID'] != $current_commentsB_RET[$student_id][$i]['REPORT_CARD_COMMENT_ID'])
                                        DBQuery('UPDATE student_report_card_comments SET REPORT_CARD_COMMENT_ID=\'' . $comment['REPORT_CARD_COMMENT_ID'] . '\' WHERE STUDENT_ID=\'' . $student_id . '\' AND COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\' AND REPORT_CARD_COMMENT_ID=\'' . $current_commentsB_RET[$student_id][$i]['REPORT_CARD_COMMENT_ID'] . '\'');
                                } else
                                    DBQuery('DELETE FROM student_report_card_comments WHERE STUDENT_ID=\'' . $student_id . '\' AND COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\' AND REPORT_CARD_COMMENT_ID=\'' . $current_commentsB_RET[$student_id][$i]['REPORT_CARD_COMMENT_ID'] . '\'');
                            else
				if ($comment['REPORT_CARD_COMMENT_ID'])
                                DBQuery('INSERT INTO student_report_card_comments (SYEAR,SCHOOL_ID,STUDENT_ID,COURSE_PERIOD_ID,MARKING_PERIOD_ID,REPORT_CARD_COMMENT_ID)
						values(\'' . UserSyear() . '\',\'' . UserSchool() . '\',\'' . $student_id . '\',\'' . $course_period_id . '\',\'' . $_REQUEST['mp_id'] . '\',\'' . $comment['REPORT_CARD_COMMENT_ID'] . '\')');
                    }
                }

                if ($completed) {
                    if (!$current_completed) {

                        DBQuery('INSERT INTO grades_completed (STAFF_ID,MARKING_PERIOD_ID,PERIOD_ID) values(\'' . UserWs('STAFF_ID') . '\',\'' . $_REQUEST['mp_id'] . '\',\'' . UserPeriod() . '\')');
                    }
                } else
		if ($current_completed)
                    DBQuery('DELETE FROM grades_completed WHERE STAFF_ID=\'' . UserWs('STAFF_ID') . '\' AND MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\' AND PERIOD_ID=\'' . UserPeriod() . '\'');

                $current_RET = DBGet(DBQuery('SELECT g.STUDENT_ID,g.REPORT_CARD_GRADE_ID,g.GRADE_PERCENT,g.REPORT_CARD_COMMENT_ID,g.COMMENT FROM student_report_card_grades g WHERE g.COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND g.MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\''), array(), array('STUDENT_ID'));

                $current_commentsA_RET = DBGet(DBQuery('SELECT g.STUDENT_ID,g.REPORT_CARD_COMMENT_ID,g.COMMENT FROM student_report_card_comments g WHERE g.COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND g.MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\' AND g.REPORT_CARD_COMMENT_ID IN (SELECT ID FROM report_card_comments WHERE COURSE_ID IS NOT NULL)'), array(), array('STUDENT_ID', 'REPORT_CARD_COMMENT_ID'));
                $current_commentsB_RET = DBGet(DBQuery('SELECT g.STUDENT_ID,g.REPORT_CARD_COMMENT_ID FROM student_report_card_comments g WHERE g.COURSE_PERIOD_ID=\'' . $course_period_id . '\' AND g.MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\' AND g.REPORT_CARD_COMMENT_ID IN (SELECT ID FROM report_card_comments WHERE COURSE_ID IS NULL)'), array(), array('STUDENT_ID'));
                $max_current_commentsB = 0;
                foreach ($current_commentsB_RET as $comments)
                    if (count($comments) > $max_current_commentsB)
                        $max_current_commentsB = count($comments);
                $current_completed = count(DBGet(DBQuery('SELECT \'\' FROM grades_completed WHERE STAFF_ID=\'' . UserWs('STAFF_ID') . '\' AND MARKING_PERIOD_ID=\'' . $_REQUEST['mp_id'] . '\' AND PERIOD_ID=\'' . UserPeriod() . '\'')));
                unset($_SESSION['_REQUEST_vars']['values']);
            }

            if ($_REQUEST['values'] && ($_POST['values'] || $_REQUEST['ajax']) && $_REQUEST['submit']['cancel']) {
                unset($_SESSION['_REQUEST_vars']['values']);
            }
            if ($cpv_id != '') {
                $cp_det = DBGet(DBQuery('SELECT cp.BEGIN_DATE,cp.END_DATE,cp.MARKING_PERIOD_ID FROM course_periods cp,course_period_var cpv WHERE cpv.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND cpv.ID=' . $cpv_id));
                if ($cp_det[1]['MARKING_PERIOD_ID'] == '') {
                    $cp_type = 'custom';

                    $grade_start_date[1]['POST_START_DATE'] = date('Y-m-d', strtotime($cp_det[1]['BEGIN_DATE']));
                    //$grade_end_date=DBGet(DBQuery('SELECT `POST_END_DATE` FROM `school_years` WHERE `school_id`='.  UserSchool()));
                    $grade_end_date[1]['POST_END_DATE'] = date('Y-m-d', strtotime('+5 days', strtotime($cp_det[1]['END_DATE'])));
                    $grade_start_time = strtotime($grade_start_date[1]['POST_START_DATE']);

                    $grade_start_date[1]['POST_END_DATE'] = $grade_end_date[1]['POST_END_DATE'];
                    $grade_end_time = strtotime($grade_end_date[1]['POST_END_DATE']);
                    $current_time = strtotime(date("Y-m-d"));

                    if ($current_time >= $grade_start_time && $current_time <= $grade_end_time && $grade_start_time != '' && $grade_end_time != '') {
                        $grade_status = 'open';
                    } else if ($current_time >= $grade_end_time && $grade_end_time != '') {
                        $grade_status = 'closed';
                    } else if ($current_time <= $grade_start_time) {
                        $grade_status = 'not open yet';
                    } else {
                        $grade_status = 'not set yet';
                    }
                    if ($grade_status == 'open')
                        $allow_edit = true;
                    if (GetMP($pro, 'DOES_GRADES', UserSyear(), UserSchool()) == 'Y') {
                        $mps_data[] = array('id' => $pro, 'value' => GetMP($pro, 'TITLE', UserSyear(), UserSchool()));
                    }
                }
            }

            if ($custom_p == 'n' && $_REQUEST['mp_id'] == UserMP() && GetMP(UserMP(), 'DOES_GRADES', UserSyear(), UserSchool()) && GetMP(UserMP(), 'POST_START_DATE', UserSyear(), UserSchool()) && ($time >= strtotime(GetMP(UserMP(), 'POST_START_DATE', UserSyear(), UserSchool())) && $time <= strtotime(GetMP(UserMP(), 'POST_END_DATE', UserSyear(), UserSchool()))))
                $allow_edit = true;
            if ($custom_p == 'n') {
                $mps_data[] = array('id' => UserMP(), 'value' => GetMP(UserMP(), 'TITLE', UserSyear(), UserSchool()));
            } elseif ($custom_p == 'y') {
                if (GetMP($_REQUEST['mp_id'], 'DOES_GRADES') == 'Y') {
                    $mps_data[] = array('id' => $_REQUEST['mp_id'], 'value' => GetMP($_REQUEST['mp_id'], 'TITLE', UserSyear(), UserSchool()));
                }
                if (GetMP($_REQUEST['mp_id'], 'POST_START_DATE', UserSyear(), UserSchool()) && ($time >= strtotime(GetMP($_REQUEST['mp_id'], 'POST_START_DATE', UserSyear(), UserSchool())) && $time <= strtotime(GetMP($_REQUEST['mp_id'], 'POST_END_DATE', UserSyear(), UserSchool()))))
                    $allow_edit = true;
            }
            if ($custom_p == 'n' && GetMP(UserMP(), 'DOES_EXAM', UserSyear(), UserSchool()) == 'Y') {
                $mps_data[] = array('id' => "E" . UserMP(), 'value' => GetMP(UserMP(), 'TITLE', UserSyear(), UserSchool()) . " Exam");
            }
            if ($custom_p == 'n' && ($_REQUEST['mp_id'] == $sem || $_REQUEST['mp_id'] == 'E' . $sem) && GetMP($sem, 'POST_START_DATE', UserSyear(), UserSchool()) && ($time >= strtotime(GetMP($sem, 'POST_START_DATE', UserSyear(), UserSchool())) && $time <= strtotime(GetMP($sem, 'POST_END_DATE', UserSyear(), UserSchool()))))
                $allow_edit = true;
            if ($custom_p == 'n' && GetMP($sem, 'DOES_GRADES', UserSyear(), UserSchool()) == 'Y') {
                $mps_data[] = array('id' => $sem, 'value' => GetMP($sem, 'TITLE', UserSyear(), UserSchool()));
            }
            if ($custom_p == 'n' && GetMP($sem, 'DOES_EXAM', UserSyear(), UserSchool()) == 'Y') {
                $mps_data[] = array('id' => "E" . $sem, 'value' => GetMP($sem, 'TITLE', UserSyear(), UserSchool()) . " Exam");
            }
            if ($custom_p == 'n' && ($_REQUEST['mp_id'] == $fy || $_REQUEST['mp_id'] == 'E' . $fy) && GetMP($fy, 'POST_START_DATE', UserSyear(), UserSchool()) && ($time >= strtotime(GetMP($fy, 'POST_START_DATE', UserSyear(), UserSchool())) && $time <= strtotime(GetMP($fy, 'POST_END_DATE', UserSyear(), UserSchool()))))
                $allow_edit = true;
            if ($custom_p == 'n' && GetMP($fy, 'DOES_GRADES', UserSyear(), UserSchool()) == 'Y') {
                $mps_data[] = array('id' => $fy, 'value' => GetMP($fy, 'TITLE', UserSyear(), UserSchool()));
            }
            if ($custom_p == 'n' && GetMP($fy, 'DOES_EXAM', UserSyear(), UserSchool()) == 'Y') {
                $mps_data[] = array('id' => "E" . $fy, 'value' => GetMP($fy, 'TITLE', UserSyear(), UserSchool()) . " Exam");
            }

            // if running as a teacher program then openSIS[allow_edit] will already be set according to admin permissions
            //if(!isset($_openSIS['allow_edit']))
            //	$_openSIS['allow_edit'] = $allow_edit;

            if ($_REQUEST['use_percents'] != 'true') {
                $extra['SELECT'] = ",'' AS GRADE_PERCENT,'' AS REPORT_CARD_GRADE";
                $extra['functions'] = array('GRADE_PERCENT' => '_makeGrade', 'REPORT_CARD_GRADE' => '_makeGrade');
            } elseif ($not_graded) {
                $extra['SELECT'] = ",'' AS GRADE_PERCENT";
                $extra['functions'] = array('GRADE_PERCENT' => '_makePercent');
            } else {
                $extra['SELECT'] = ",'' AS REPORT_CARD_GRADE,'' AS GRADE_PERCENT";
                $extra['functions'] = array('REPORT_CARD_GRADE' => '_makeGrade', 'GRADE_PERCENT' => '_makePercent');
            }

            if (substr($_REQUEST['mp_id'], 0, 1) != 'E' && GetMP($_REQUEST['mp_id'], 'DOES_COMMENTS', UserSyear(), UserSchool()) == 'Y') {
                foreach ($commentsA_RET as $value) {
                    $extra['SELECT'] .= ',\'' . $value['ID'] . '\' AS CA' . $value['ID'];
                    $extra['functions'] += array('CA' . $value['ID'] => '_makeCommentsA');
                }
                for ($i = 1; $i <= $max_current_commentsB; $i++) {
                    $extra['SELECT'] .= ',\'' . $i . '\' AS CB' . $i;
                    $extra['functions'] += array('CB' . $i => '_makeCommentsB');
                }
                if (count($commentsB_select) && AllowEdit()) {
                    $extra['SELECT'] .= ',\'' . $i . '\' AS CB' . $i;
                    $extra['functions'] += array('CB' . $i => '_makeCommentsB');
                }
            }


            $extra['SELECT'] .= ",'' AS COMMENTS,'' AS COMMENT";



            $extra['functions'] += array('COMMENT' => '_makeComment');

            ////////////////////////////////////////
            $stu_RET = GetStuListWs($extra, $teacher_id);

            //$tmp_REQUEST = $_REQUEST;
            //if(strstr($tmp_REQUEST['mp'],'E')!="")
            //    $tmp_REQUEST['mp'] =   str_replace ('E','',$tmp_REQUEST['mp']);
            //unset($tmp_REQUEST['include_inactive']);
            $grade_start_date = DBGet(DBQuery('SELECT `POST_START_DATE` FROM `marking_periods` WHERE `marking_period_id`=' . $mp_id . ' AND does_grades=\'Y\''));
            $grade_end_date = DBGet(DBQuery('SELECT `POST_END_DATE` FROM `marking_periods` WHERE `marking_period_id`=' . $mp_id . ' AND does_grades=\'Y\''));
            $grade_start_time = strtotime($grade_start_date[1]['POST_START_DATE']);
            $grade_end_time = strtotime($grade_end_date[1]['POST_END_DATE']);
            $current_time = strtotime(date("Y-m-d"));
            $grade_status = '';
            if ($current_time >= $grade_start_time && $current_time <= $grade_end_time && $grade_start_time != '' && $grade_end_time != '') {
                $grade_status = 'open';
            } else if ($current_time >= $grade_end_time && $grade_end_time != '') {
                $grade_status = 'closed';
            } else if ($current_time <= $grade_start_time) {
                $grade_status = 'not open yet';
            } else {
                $grade_status = 'not set yet';
            }

            if (!isset($_openSIS['allow_edit'])) {
                // allow teacher edit if selected date is in the current quarter or in the corresponding grade posting period

                $edit_days_before = '';
                $edit_days_after = '';
                $current_qtr_id = $mp_id;
                $time = strtotime(DBDate('postgres'));

                if ((GetMP($mp_id, 'POST_START_DATE', $_REQUEST['syear'], $_REQUEST['school_id']) && ($time <= strtotime(GetMP($mp_id, 'POST_END_DATE', $_REQUEST['syear'], $_REQUEST['school_id'])))) && ($edit_days_before == '' || strtotime($date) <= $time + $edit_days_before * 86400) && ($edit_days_after == '' || strtotime($date) >= $time - $edit_days_after * 86400)) {
                    $_openSIS['allow_edit'] = true;
                } else {
                    $_openSIS['allow_edit'] = false;
                }
            }

            if (AllowEdit('grades/InputFinalGrades.php')) {
                $msg = ($current_completed ? 'These grades are complete' : 'Grade reporting is open for this marking period') . (AllowEdit('grades/InputFinalGrades.php') ? ' | You can edit these grades' : ' | Grade reporting begins on : ' . date("M d, Y ", strtotime($grade_start_date[1]['POST_START_DATE'])));
                if (AllowEdit('grades/InputFinalGrades.php'))
                    $success = 1;
                else
                    $success = 0;
            } else if ($grade_status == 'not open yet') {
                $msg = ($current_completed ? 'These grades are complete' : 'Grade reporting is not open for this marking period') . (AllowEdit('grades/InputFinalGrades.php') ? ' | You can edit these grades' : ' | Grade reporting starts on: ' . date("M d, Y ", strtotime($grade_start_date[1]['POST_START_DATE'])) . ' and ends on : ' . date("M d, Y ", strtotime($grade_end_date[1]['POST_END_DATE'])));
                if ($current_completed && AllowEdit('grades/InputFinalGrades.php'))
                    $success = 1;
                else
                    $success = 0;
            } else if ($grade_status == 'closed') {
                $msg = ($current_completed ? 'These grades are complete' : 'These grades are complete') . (AllowEdit('grades/InputFinalGrades.php') ? ' | You can edit these grades' : ' | Grade reporting ended for this marking period on : ' . date("M d, Y ", strtotime($grade_end_date[1]['POST_END_DATE'])));
                if ($current_completed && AllowEdit('grades/InputFinalGrades.php'))
                    $success = 1;
                else
                    $success = 0;
            } else if ($grade_status == 'not set yet') {
                $msg = 'Grade reporting date has not set for this marking period';
                $success = 0;
            }
            if ($success == 1) {
                $i = 0;
                $report_card_grades = array();
                foreach ($grades_select as $id => $value) {
                    $report_card_grades[$i]['ID'] = $id;
                    $report_card_grades[$i]['TITLE'] = $value[0];
                    $i++;
                }
                foreach ($stu_RET as $stu) {
                    foreach ($report_card_grades as $rcg) {
                        if ($rcg['ID'] == $stu['REPORT_CARD_GRADE']) {
                            $stu['REPORT_CARD_GRADE_TITLE'] = $rcg['TITLE'];
                            break;
                        } else {
                            $stu['REPORT_CARD_GRADE_TITLE'] = '';
                        }
                    }
                    $student_data[] = $stu;
                }
                //    $prev_mp_data = array();
                //    if(count($prev_mp[1])>0)
                //        $prev_mp_data = $prev_mp[1];
                if (count($student_data) > 0)
                    $stu_success = 1;
                else
                    $stu_success = 0;

                if ($stu_success == 1 || count($prev_mp) > 0 || count($mps_data) > 0) {
                    $data_success = 1;
                } else {
                    $data_success = 0;
                }
                $data = array(
                    'student_grades' => $student_data,
                    'student_success' => $stu_success,
                    'grade_msg_success' => $success,
                    'success' => $data_success,
                    'msg' => $msg,
                    'report_card_grades' => $report_card_grades,
                    'mp_comment_view' => ($mp_comment_data[1]['DOES_COMMENTS']) ? $mp_comment_data[1]['DOES_COMMENTS'] : 'N',
                    'previous_mp_data' => $prev_mp,
                    'mps_dd_data' => $mps_data,
                    'dd_selected_mp' => ($_REQUEST['mp_id'] != '') ? $current_mp : $_REQUEST['mp']
                );
            }
        } else {
            $success = 0;
            $msg = 'Please select a Course Period';
            $report_card_grades = array();
            if (count($student_data) > 0)
                $stu_success = 1;
            else
                $stu_success = 0;

            if ($stu_success == 1 || count($prev_mp) > 0 || count($mps_data) > 0) {
                $data_success = 1;
            } else {
                $data_success = 0;
            }
            $data = array(
                'student_grades' => $student_data,
                'student_success' => $stu_success,
                'grade_msg_success' => $success,
                'msg' => $msg,
                'success' => $data_success,
                'report_card_grades' => $report_card_grades,
                'mp_comment_view' => ($mp_comment_data[1]['DOES_COMMENTS']) ? $mp_comment_data[1]['DOES_COMMENTS'] : 'N',
                'previous_mp_data' => $prev_mp,
                'mps_dd_data' => $mps_data,
                'dd_selected_mp' => ($_REQUEST['mp_id'] != '') ? $_REQUEST['mp_id'] : $_REQUEST['mp']
            );
        }
    }
} else {
    $data = array('success' => 0, 'msg' => 'Not authenticated user');
}
/********************************* System Functions *************************************/
function _makeGrade($value, $column)
{
    global $THIS_RET, $current_RET, $import_RET, $grades_RET, $grades_select, $student_count, $tabindex;
    $tc_grade = 'n';
    if ($column == 'REPORT_CARD_GRADE') {

        if (!isset($_REQUEST['_openSIS_PDF'])) {
            $student_count++;
            $tabindex = $student_count;

            if ($import_RET[$THIS_RET['STUDENT_ID']]) {
                $select = $import_RET[$THIS_RET['STUDENT_ID']][1]['REPORT_CARD_GRADE_ID'];
                $extra_select = array($select => $grades_select[$import_RET[$THIS_RET['STUDENT_ID']][1]['REPORT_CARD_GRADE_ID']]);
                $div = false;
            } else {

                if ($current_RET[$THIS_RET['STUDENT_ID']][1]['GRADE_PERCENT'] != '')
                    $select = _makeLetterGrade($current_RET[$THIS_RET['STUDENT_ID']][1]['GRADE_PERCENT'] / 100, "", UserWs('STAFF_ID'), "%");

                //                        $rounding=DBGet(DBQuery('SELECT VALUE FROM program_user_config WHERE USER_ID=\''.UserWs('STAFF_ID').'\' AND TITLE=\'ROUNDING\' AND PROGRAM=\'Gradebook\' '));
                //                        if(count($rounding))
                //                            $_SESSION['ROUNDING']=$rounding[1]['VALUE'];
                //                        else
                //                            $_SESSION['ROUNDING']='';
                //                        
                //                        if($_SESSION['ROUNDING']=='UP')
                //                                $select = ceil($select);
                //                        elseif($_SESSION['ROUNDING']=='DOWN')
                //                                $select = floor($select);
                //                        elseif($_SESSION['ROUNDING']=='NORMAL')
                //                                $select = round($select);
                //                        
                //                        
                $rounding = DBGet(DBQuery('SELECT VALUE FROM program_user_config WHERE USER_ID=\'' . User('STAFF_ID') . '\' AND TITLE=\'ROUNDING\' AND PROGRAM=\'Gradebook\' AND VALUE LIKE \'%_' . UserCoursePeriod() . '\''));
                if (count($rounding))
                    $_SESSION['ROUNDING'] = rtrim($rounding[1]['VALUE'], '_' . UserCoursePeriod());
                else
                    $_SESSION['ROUNDING'] = '';

                if ($_SESSION['ROUNDING'] == 'UP')
                    $select = ceil($select);
                elseif ($_SESSION['ROUNDING'] == 'DOWN')
                    $select = floor($select);
                elseif ($_SESSION['ROUNDING'] == 'NORMAL')
                    $select = round($select);


                $dbf =  DBGet(DBQuery('SELECT DOES_BREAKOFF,GRADE_SCALE_ID FROM course_periods WHERE COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\''));
                $dbf = DBGet(DBQuery('SELECT DOES_BREAKOFF,GRADE_SCALE_ID FROM course_periods WHERE COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\''));
                if ($dbf[1]['DOES_BREAKOFF'] == 'Y' && $select !== '') {

                    $get_details = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\'' . UserWs('STAFF_ID') . '\' AND PROGRAM=\'Gradebook\' AND VALUE LIKE \'%_' . UserCoursePeriod() . '\'  ORDER BY VALUE DESC '));
                    if (count($get_details)) {
                        $tgs_total = DBGet(DBQuery('SELECT COUNT(*) as RET_EX FROM program_user_config WHERE USER_ID=\'' . UserWs('STAFF_ID') . '\' AND PROGRAM=\'Gradebook\' AND VALUE LIKE \'%_' . UserCoursePeriod() . '\''));
                        $tgs_total = $tgs_total[1]['RET_EX'];
                        $tgs_ac_total = DBGet(DBQuery('SELECT COUNT(*) as RET_EX FROM program_user_config WHERE USER_ID=\'' . UserWs('STAFF_ID') . '\' AND PROGRAM=\'Gradebook\' AND VALUE LIKE \'%_' . UserCoursePeriod() . '\' AND VALUE IS NOT NULL'));
                        $tgs_ac_total = $tgs_ac_total[1]['RET_EX'];
                        if ($tgs_ac_total == $tgs_total) {
                            foreach ($get_details as $i => $d) {
                                $unused_var = explode('_', $d['VALUE']);
                                $unused_var2 = explode('-', $d['TITLE']);
                                if ($select >= $unused_var[0] && is_numeric($unused_var[0]) && $unused_var2[0] == UserCoursePeriod()) {
                                    $id = $i;
                                    break;
                                }
                            }

                            $grade_id = explode('-', $get_details[$id]['TITLE']);
                            $select = $grade_id[1];
                        } else
                            $select = '';
                        $tc_grade = 'y';
                    }
                }

                if ($tc_grade == 'n')
                    $select = $current_RET[$THIS_RET['STUDENT_ID']][1]['REPORT_CARD_GRADE_ID'];
                $extra_select = array();
                $div = true;
            }
            if ($_REQUEST['modfunc'] == 'clearall')
                $select = '';
            $return = $select;
            //                        $return = SelectInput($select,'values['.$THIS_RET['STUDENT_ID'].'][grade]','',$extra_select+$grades_select,false,'tabindex='.$tabindex,$div);
        } else {
            if ($import_RET[$THIS_RET['STUDENT_ID']])
                $select = $import_RET[$THIS_RET['STUDENT_ID']][1]['REPORT_CARD_GRADE_ID'];
            else
                $select = $current_RET[$THIS_RET['STUDENT_ID']][1]['REPORT_CARD_GRADE_ID'];
            $return = $grades_RET[$select][1]['TITLE'];
        }
    } elseif ($column == 'GRADE_PERCENT') {
        if ($import_RET[$THIS_RET['STUDENT_ID']])
            $return = $select = $import_RET[$THIS_RET['STUDENT_ID']][1]['GRADE_PERCENT'] . '%';
        else {
            $select = $current_RET[$THIS_RET['STUDENT_ID']][1]['GRADE_PERCENT'];
            $rounding = DBGet(DBQuery('SELECT VALUE FROM program_user_config WHERE USER_ID=\'' . UserWs('STAFF_ID') . '\' AND TITLE=\'ROUNDING\' AND PROGRAM=\'Gradebook\' '));
            if (count($rounding))
                $_SESSION['ROUNDING'] = $rounding[1]['VALUE'];
            else
                $_SESSION['ROUNDING'] = '';

            if ($_SESSION['ROUNDING'] == 'UP')
                $select = ceil($select);
            elseif ($_SESSION['ROUNDING'] == 'DOWN')
                $select = floor($select);
            elseif ($_SESSION['ROUNDING'] == 'NORMAL')
                $select = round($select);
            $return = $select == '' ? '' : ($select + 0) . '%';
        }
    }

    return $return;
}

function _makePercent($value, $column)
{
    global $THIS_RET, $current_RET, $grades_RET, $student_count, $tabindex, $import_RET;

    if ($column == 'GRADE_PERCENT') {
        if (!isset($_REQUEST['_openSIS_PDF'])) {
            $student_count++;
            $tabindex = $student_count;
            if ($import_RET[$THIS_RET['STUDENT_ID']])
                $return = $import_RET[$THIS_RET['STUDENT_ID']][1]['GRADE_PERCENT'] == '' ? '' : ($import_RET[$THIS_RET['STUDENT_ID']][1]['GRADE_PERCENT'] + 0) . '%';
            //				$return = TextInput($import_RET[$THIS_RET['STUDENT_ID']][1]['GRADE_PERCENT']==''?'':($import_RET[$THIS_RET['STUDENT_ID']][1]['GRADE_PERCENT']+0).'%',"values[$THIS_RET[STUDENT_ID]][percent]",'',(0?'readonly ':'').'size=6 maxlength=6 tabindex='.$tabindex,false);
            else
                $return = $current_RET[$THIS_RET['STUDENT_ID']][1]['GRADE_PERCENT'] == '' ? '' : ($current_RET[$THIS_RET['STUDENT_ID']][1]['GRADE_PERCENT'] + 0) . '%';
            //				$return = TextInput($current_RET[$THIS_RET['STUDENT_ID']][1]['GRADE_PERCENT']==''?'':($current_RET[$THIS_RET['STUDENT_ID']][1]['GRADE_PERCENT']+0).'%',"values[$THIS_RET[STUDENT_ID]][percent]",'',(0?'readonly ':'').'size=6 maxlength=6 tabindex='.$tabindex,!$current_RET[$THIS_RET['STUDENT_ID']][1]['DIV']);
        } else
            $return = $current_RET[$THIS_RET['STUDENT_ID']][1]['GRADE_PERCENT'] == '' ? '' : ($current_RET[$THIS_RET['STUDENT_ID']][1]['GRADE_PERCENT'] + 0) . '%';
    } elseif ($column == 'REPORT_CARD_GRADE')
        $return = $grades_RET[$current_RET[$THIS_RET['STUDENT_ID']][1]['REPORT_CARD_GRADE_ID']][1]['TITLE'];

    return $return;
}

$table = 'student_report_card_comments';

function _makeComment($value, $column)
{
    global $THIS_RET, $current_RET, $import_comments_RET, $tabindex;
    $table = 'student_report_card_comments';

    if ($import_comments_RET[$THIS_RET['STUDENT_ID']]) {
        $select = $import_comments_RET[$THIS_RET['STUDENT_ID']][1]['COMMENT'];
        $div = false;
    } else {
        $select = $current_RET[$THIS_RET['STUDENT_ID']][1]['COMMENT'];
        $div = true;
    }

    if (!isset($_REQUEST['_openSIS_PDF'])) {
        $return = str_replace('"', '\"', $select);
        //		$return = TextAreaInputInputFinalGrade(str_replace('"','\"',$select),"values[$THIS_RET[STUDENT_ID]][comment]",'','maxlength=255 tabindex='.($tabindex+=100),$div);

    } else {
        $return = $select;
    }

    return $return;
}
function _makeCommentsA($value, $column)
{
    global $THIS_RET, $current_commentsA_RET, $import_commentsA_RET, $commentsA_select, $tabindex;

    if ($import_commentsA_RET[$THIS_RET['STUDENT_ID']][$value]) {
        $select = $import_commentsA_RET[$THIS_RET['STUDENT_ID']][$value][1]['COMMENT'];
        $div = false;
    } else {
        if (!$current_commentsA_RET[$THIS_RET['STUDENT_ID']] && !$import_commentsA_RET[$THIS_RET['STUDENT_ID']] && AllowEdit('grades/InputFinalGrades.php')) {
            $select = PreferencesWs('COMMENT_A', 'Gradebook');
            $div = false;
        } else {
            $select = $current_commentsA_RET[$THIS_RET['STUDENT_ID']][$value][1]['COMMENT'];
            $div = true;
        }
    }

    if (!isset($_REQUEST['_openSIS_PDF']))
        $return = $select;
    //		$return = SelectInput($select,'values['.$THIS_RET['STUDENT_ID'].'][commentsA]['.$value.']','',$commentsA_select,'N/A','tabindex='.($tabindex+=100),$div);
    else
        $return = $select != ' ' ? $select : 'o';

    return $return;
}
function _makeCommentsB($value, $column)
{
    global $THIS_RET, $current_commentsB_RET, $import_commentsB_RET, $commentsB_RET, $max_current_commentsB, $commentsB_select, $tabindex;

    if ($import_commentsB_RET[$THIS_RET['STUDENT_ID']][$value]) {
        $select = $import_commentsB_RET[$THIS_RET['STUDENT_ID']][$value]['REPORT_CARD_COMMENT_ID'];
        $div = false;
    } else {
        $select = $current_commentsB_RET[$THIS_RET['STUDENT_ID']][$value]['REPORT_CARD_COMMENT_ID'];
        $div = true;
    }

    if (!isset($_REQUEST['_openSIS_PDF']))
        if ($value > $max_current_commentsB)
            $return = '';
        //			$return = SelectInput('','values['.$THIS_RET['STUDENT_ID'].'][commentsB]['.$value.']','',$commentsB_select,'N/A','tabindex='.($tabindex+=100));
        elseif ($import_commentsB_RET[$THIS_RET['STUDENT_ID']][$value] || $current_commentsB_RET[$THIS_RET['STUDENT_ID']][$value])
            $return = $select;
        //			$return = SelectInput($select,'values['.$THIS_RET['STUDENT_ID'].'][commentsB]['.$value.']','',$commentsB_select,'N/A','tabindex='.($tabindex+=100),$div);
        else
            $return = '';
    else
        $return = $commentsB_RET[$select][1]['TITLE'];

    return $return;
}
//function _makeLetterGrade($percent,$course_period_id=0,$staff_id=0,$ret='')
//{	global $programconfig,$_openSIS;
//
//	if(!$course_period_id)
//		$course_period_id = UserCoursePeriod();
//       
//	if(!$staff_id)
//		$staff_id = UserWs('STAFF_ID');
////	if(!$programconfig[$staff_id])
////	{
//		$config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\''.$staff_id.'\' AND PROGRAM=\'Gradebook\''));
//                
//                if(count($config_RET)>0)
//                {
//			foreach($config_RET as $config_data)
//                        {
////                            foreach($config_data as $title=>$value)
//				$programuserconfig[$staff_id][$config_data['TITLE']] = $config_data['VALUE'];
//                        }
//                }
//		else
//			$programuserconfig[$staff_id] = true;
////	}
//	if(!$_openSIS['_makeLetterGrade']['courses'][$course_period_id])
//		$_openSIS['_makeLetterGrade']['courses'][$course_period_id] = DBGet(DBQuery('SELECT DOES_BREAKOFF,GRADE_SCALE_ID FROM course_periods WHERE COURSE_PERIOD_ID=\''.$course_period_id.'\''));
//	$does_breakoff = $_openSIS['_makeLetterGrade']['courses'][$course_period_id][1]['DOES_BREAKOFF'];
//	$grade_scale_id = $_openSIS['_makeLetterGrade']['courses'][$course_period_id][1]['GRADE_SCALE_ID'];
//        if($percent<1)
//	$percent *= 100;
//
//		if($programuserconfig[$staff_id]['ROUNDING']=='UP')
//			$percent = ceil($percent);
//		elseif($programuserconfig[$staff_id]['ROUNDING']=='DOWN')
//			$percent = floor($percent);
//		elseif($programuserconfig[$staff_id]['ROUNDING']=='NORMAL')
//			$percent = round($percent,2);
//                
//	
//	else
//		$percent = round($percent,2); // school default
//
//	if($ret=='%')
//		return $percent;
//        if(!$_openSIS['_makeLetterGrade']['grades'][$grade_scale_id])
//		$_openSIS['_makeLetterGrade']['grades'][$grade_scale_id] = DBGet(DBQuery('SELECT TITLE,ID,BREAK_OFF FROM report_card_grades WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\' AND GRADE_SCALE_ID=\''.$grade_scale_id.'\' ORDER BY BREAK_OFF IS NOT NULL DESC,BREAK_OFF DESC,SORT_ORDER'));
//	
//	foreach($_openSIS['_makeLetterGrade']['grades'][$grade_scale_id] as $grade)
//	{
//		if($does_breakoff=='Y' ? $percent>=$programuserconfig[$staff_id][$course_period_id.'-'.$grade['ID']] && is_numeric($programuserconfig[$staff_id][$course_period_id.'-'.$grade['ID']]) : $percent>=$grade['BREAK_OFF'])
//			return $ret=='ID' ? $grade['ID'] : $grade['TITLE'];
//	}
//}

function _makeLetterGrade($percent, $course_period_id = 0, $staff_id = 0, $ret = '')
{
    global $programconfig, $_openSIS;
    if (!$course_period_id)
        $course_period_id = UserCoursePeriod();

    if (!$staff_id)
        $staff_id = UserWs('STAFF_ID');

    if (!$programconfig[$staff_id]) {
        $config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\'' . $staff_id . '\' AND PROGRAM=\'Gradebook\' AND VALUE LIKE \'%_' . $course_period_id . '\''), array(), array('TITLE'));
        if (count($config_RET))
            foreach ($config_RET as $title => $value) {
                $unused_var = explode('_', $value[1]['VALUE']);
                $programconfig[$staff_id][$title] = $unused_var[0];
                $programconfig[$staff_id]['current_course_period_id'] = $course_period_id;
                //				$programconfig[$staff_id][$title] = rtrim($value[1]['VALUE'],'_'.$course_period_id);
            }
        else
            $programconfig[$staff_id] = true;
    } else {
        if ($programconfig[$staff_id]['current_course_period_id'] != $course_period_id) {
            $programconfig[$staff_id] = array();
            $config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\'' . $staff_id . '\' AND PROGRAM=\'Gradebook\' AND VALUE LIKE \'%_' . $course_period_id . '\''), array(), array('TITLE'));
            if (count($config_RET))
                foreach ($config_RET as $title => $value) {
                    $unused_var = explode('_', $value[1]['VALUE']);
                    $programconfig[$staff_id][$title] = $unused_var[0];
                    $programconfig[$staff_id]['current_course_period_id'] = $course_period_id;
                    //				$programconfig[$staff_id][$title] = rtrim($value[1]['VALUE'],'_'.$course_period_id);
                }
            else
                $programconfig[$staff_id] = true;
        }
    }
    if (!$_openSIS['_makeLetterGrade']['courses'][$course_period_id])
        $_openSIS['_makeLetterGrade']['courses'][$course_period_id] = DBGet(DBQuery('SELECT DOES_BREAKOFF,GRADE_SCALE_ID FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $course_period_id . '\''));
    $does_breakoff = $_openSIS['_makeLetterGrade']['courses'][$course_period_id][1]['DOES_BREAKOFF'];
    $grade_scale_id = $_openSIS['_makeLetterGrade']['courses'][$course_period_id][1]['GRADE_SCALE_ID'];

    $percent *= 100;

    if ($programconfig[$staff_id]['ROUNDING'] == 'UP') {
        $percent = ceil($percent);
    } elseif ($programconfig[$staff_id]['ROUNDING'] == 'DOWN') {
        $percent = floor($percent);
    } elseif ($programconfig[$staff_id]['ROUNDING'] == 'NORMAL') {
        $percent = round($percent, 0);
    } else {
        $percent = round($percent, 2); // school default
    }
    if ($ret == '%')
        return $percent;

    if (!$_openSIS['_makeLetterGrade']['grades'][$grade_scale_id])
        $_openSIS['_makeLetterGrade']['grades'][$grade_scale_id] = DBGet(DBQuery('SELECT TITLE,ID,BREAK_OFF FROM report_card_grades WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND GRADE_SCALE_ID=\'' . $grade_scale_id . '\' ORDER BY BREAK_OFF IS NOT NULL DESC,BREAK_OFF DESC,SORT_ORDER'));

    foreach ($_openSIS['_makeLetterGrade']['grades'][$grade_scale_id] as $grade) {

        if ($does_breakoff == 'Y' ? $percent >= $programconfig[$staff_id][$course_period_id . '-' . $grade['ID']] && is_numeric($programconfig[$staff_id][$course_period_id . '-' . $grade['ID']]) : $percent >= $grade['BREAK_OFF'])
            return $ret == 'ID' ? $grade['ID'] : $grade['TITLE'];
    }
}
function _makePercentGrade($grade_id, $course_period_id = 0, $staff_id = 0)
{
    global $programconfig, $_openSIS;

    if (!$course_period_id)
        $course_period_id = UserCoursePeriod();

    if (!$staff_id)
        $staff_id = UserWs('STAFF_ID');

    if (!$programconfig[$staff_id]) {
        $config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\'' . $staff_id . '\' AND PROGRAM=\'Gradebook\''));

        if (count($config_RET) > 0) {
            foreach ($config_RET as $value) {
                $programconfig[$staff_id][$value['TITLE']] = $value['VALUE'];
            }
        } else {
            $programconfig[$staff_id] = true;
        }
    }
    if (!$_openSIS['_makeLetterGrade']['courses'][$course_period_id])
        $_openSIS['_makeLetterGrade']['courses'][$course_period_id] = DBGet(DBQuery('SELECT DOES_BREAKOFF,GRADE_SCALE_ID FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $course_period_id . '\''));
    $does_breakoff = $_openSIS['_makeLetterGrade']['courses'][$course_period_id][1]['DOES_BREAKOFF'];
    $grade_scale_id = $_openSIS['_makeLetterGrade']['courses'][$course_period_id][1]['GRADE_SCALE_ID'];

    if (!$_openSIS['_makeLetterGrade']['grades'][$grade_scale_id])
        $_openSIS['_makeLetterGrade']['grades'][$grade_scale_id] = DBGet(DBQuery('SELECT TITLE,ID,BREAK_OFF FROM report_card_grades WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND GRADE_SCALE_ID=\'' . $grade_scale_id . '\' ORDER BY BREAK_OFF IS NOT NULL DESC,BREAK_OFF DESC,SORT_ORDER'));

    $prev = $crnt = 0;
    foreach ($_openSIS['_makeLetterGrade']['grades'][$grade_scale_id] as $grade) {
        $prev = $crnt;
        $crnt = ($does_breakoff == 'Y' ? $programconfig[$staff_id][$course_period_id . '-' . $grade['ID']] : $grade['BREAK_OFF']);
        if (is_numeric($grade_id) ? $grade_id == $grade['ID'] : strtoupper($grade_id) == strtoupper($grade['TITLE'])) {
            return ($crnt + ($crnt > $prev ? 100 : $prev)) / 2;
        }
    }
    return 0;
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
                //				return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR><TD><font size=-1>'.(rtrim(rtrim($value,'0'),'.')+0).'</font></TD><TD><font size=-1>&nbsp;/&nbsp;</font></TD><TD><font size=-1>'.$THIS_RET['TOTAL_POINTS'].'</font></TD></TR></TABLE>';
                return $THIS_RET['TOTAL_POINTS'];
            } else
                return '';
        //return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR><TD><font size=-1>Excluded</font></TD><TD></TD><TD></TD></TR></TABLE>';
        else {
            $student_points[$THIS_RET['ASSIGNMENT_TYPE_ID']] += $value;
            return $THIS_RET['TOTAL_POINTS'];
            //return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR><TD><font size=-1>'.(rtrim(rtrim($value,'0'),'.')+0).'</font></TD><TD><font size=-1>&nbsp;/&nbsp;</font></TD><TD><font size=-1>'.$THIS_RET['TOTAL_POINTS'].'</font></TD></TR></TABLE>';
        }
    } elseif ($column == 'LETTER_GRADE') {
        if ($THIS_RET['TOTAL_POINTS'] != '0')
            if ($value != '-1')
                if ($THIS_RET['DUE'] && $value == '')
                    return 'Not Graded';
                else if ($THIS_RET['DUE'] || $value != '') {

                    $per = $value / $THIS_RET['TOTAL_POINTS'];

                    return _makeLetterGrade($per, "", UserWs('STAFF_ID'), "%") . '%&nbsp;' . _makeLetterGrade($value / $THIS_RET['TOTAL_POINTS'], "", UserWs('STAFF_ID'));
                } else
                    return 'Due';
            else
                return 'N/A';
        else
            return 'E/C';
    }
}

function _makeAssnWG($value, $column)
{
    global $THIS_RET, $student_points, $total_points, $percent_weights;
    return ($THIS_RET['ASSIGN_TYP_WG'] != 'N/A' ? ($value * 100) . ' %' : $THIS_RET['ASSIGN_TYP_WG']);
}

function check_exam($mp)
{
    $qr =  DBGet(DBQuery('select * from marking_periods where marking_period_id=\'' . $mp . '\''));

    return $qr[1]['DOES_EXAM'];
}

echo json_encode($data);
