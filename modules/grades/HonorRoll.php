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
include 'modules/grades/DeletePromptX.fnc.php';
DrawBC(""._gradebook." > " . ProgramTitle());
if ($_REQUEST['modfunc'] == 'save' && $_REQUEST['honor_roll']) {
    if (is_countable($_REQUEST['st_arr']) && count($_REQUEST['st_arr'])) {
        $mp = $_REQUEST['mp'];
        if ($_REQUEST['honor_roll'] != 986) {

            $SCHOOL_RET = DBGet(DBQuery('SELECT * from schools where ID = \'' . UserSchool() . '\''));
            $scale = $SCHOOL_RET[1]['REPORTING_GP_SCALE'];
            $honor = DBGet(DBQuery('SELECT VALUE  FROM honor_roll WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' ORDER BY VALUE DESC'));
            $honor_gpa1 = $_REQUEST['honor_roll'];
            foreach ($honor as $gp_val) {
                $gpa_value[] = $gp_val['VALUE'];
            }
            foreach ($gpa_value as $gpa_val_key => $gpa_val) {
                if ($gpa_val == $honor_gpa1) {
                    $key = $gpa_val_key;
                }
            }
            if ($key !== 0) {

                if ($gpa_value[$key + 1] > $honor_gpa1) {
                    $honor_gpa2 = $gpa_value[$key + 1];
                } else {
                    $honor_gpa2 = $gpa_value[$key - 1];
                }
            } elseif ($key == 0) {
                $honor_gpa2 = 100;
            }
            $st_list = '\'' . implode('\',\'', $_REQUEST['st_arr']) . '\'';
            $extra['WHERE'] = " AND s.STUDENT_ID IN ($st_list)";

            $mp_RET = DBGet(DBQuery('SELECT TITLE,END_DATE FROM marking_periods WHERE MARKING_PERIOD_ID = ' . UserMP() . ' '));
            $school_info_RET = DBGet(DBQuery('SELECT TITLE,PRINCIPAL FROM schools WHERE ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''));
            $extra['SELECT'] = ',coalesce(s.COMMON_NAME,s.FIRST_NAME) AS NICK_NAME';
            $extra['SELECT'] .= ',(SELECT SORT_ORDER FROM school_gradelevels WHERE ID=ssm.GRADE_ID) AS SORT_ORDER';
            $extra['FROM'] .= ',student_report_card_grades srg';
            if ($_REQUEST['w_course_period_id']) {
                $extra['SELECT'] .= ',(SELECT hr.TITLE FROM honor_roll hr WHERE  hr.SCHOOL_ID=' . UserSchool() . ' AND hr.SYEAR=' . UserSyear() . ' AND  hr.VALUE=(SELECT if((ROUND(AVG(srcg.grade_percent))>=' . $honor_gpa1 . ' and ROUND(AVG(srcg.grade_percent))<' . $honor_gpa2 . '),' . $honor_gpa1 . ',"")  FROM
                                                   `student_report_card_grades` srcg,course_periods cpp  WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.course_period_id=\'' . $_REQUEST['w_course_period_id'] . '\' and cpp.does_honor_roll="Y"
                                                   and srcg.`STUDENT_ID`=ssm.STUDENT_ID) )AS HONOR_ROLL';
                $extra['WHERE'] .= 'AND ((SELECT ROUND(AVG(srcg.grade_percent)) FROM
                                                   `student_report_card_grades` srcg,course_periods cpp  WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.course_period_id=\'' . $_REQUEST['w_course_period_id'] . '\' and cpp.does_honor_roll="Y"
                                                   and srcg.`STUDENT_ID`=ssm.STUDENT_ID)>=' . $honor_gpa1 . ' ) AND ((SELECT ROUND(AVG(grade_percent)) FROM
                                                   `student_report_card_grades` srcg,course_periods cpp  WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.course_period_id=\'' . $_REQUEST['w_course_period_id'] . '\' and cpp.does_honor_roll="Y"
                                                   and srcg.`STUDENT_ID`=ssm.STUDENT_ID)<' . $honor_gpa2 . ' )  GROUP BY s.STUDENT_ID';
                $extra['SELECT'] .= ',(SELECT CONCAT(st.LAST_NAME,", ",coalesce(st.FIRST_NAME)) FROM staff st,course_periods cp,course_period_var cpv,school_periods p,schedule ss WHERE st.STAFF_ID=cp.TEACHER_ID AND cpv.PERIOD_id=p.PERIOD_ID  AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID  AND p.ATTENDANCE="Y" AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID AND  cp.COURSE_PERIOD_ID=\'' . $_REQUEST['w_course_period_id'] . '\' AND ss.STUDENT_ID=s.STUDENT_ID AND ss.SYEAR=' . UserSyear() . ' AND ss.MARKING_PERIOD_ID = ' . UserMp() . ' AND (ss.START_DATE<=' . date('Y-m-d', strtotime(DBDate())) . ' AND (ss.END_DATE>=' . date('Y-m-d', strtotime(DBDate())) . ' OR ss.END_DATE IS NULL)) ORDER BY p.SORT_ORDER LIMIT 1) AS TEACHER';
                $extra['SELECT'] .= ',(SELECT cpv.ROOM_ID AS ROOM FROM course_periods cp,course_period_var cpv,school_periods p,schedule ss WHERE cpv.PERIOD_id=p.PERIOD_ID  AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID  AND p.ATTENDANCE="Y" AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID AND  cp.COURSE_PERIOD_ID=\'' . $_REQUEST['w_course_period_id'] . '\' AND ss.STUDENT_ID=s.STUDENT_ID AND ss.SYEAR=' . UserSyear() . ' AND ss.MARKING_PERIOD_ID = ' . UserMp() . ' AND (ss.START_DATE<=' . date('Y-m-d', strtotime(DBDate())) . ' AND (ss.END_DATE>=' . date("Y-m-d", strtotime(DBDate())) . ' OR ss.END_DATE IS NULL)) ORDER BY p.SORT_ORDER LIMIT 1) AS ROOM';
            } else {
                $extra['SELECT'] .= ',(SELECT hr.TITLE FROM honor_roll hr WHERE  hr.VALUE=(SELECT if((ROUND(AVG(srcg.grade_percent))>=' . $honor_gpa1 . ' and ROUND(AVG(srcg.grade_percent))<' . $honor_gpa2 . '),' . $honor_gpa1 . ',"")  FROM
                                                   `student_report_card_grades` srcg,course_periods cpp  WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll="Y"
                                                   and srcg.`STUDENT_ID`=ssm.STUDENT_ID)  AND hr.SCHOOL_ID=' . UserSchool() . ' AND hr.SYEAR=' . UserSyear() . ')AS HONOR_ROLL';
                $extra['WHERE'] .= 'AND ((SELECT ROUND(AVG(srcg.grade_percent)) FROM
                                                   `student_report_card_grades` srcg,course_periods cpp  WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll="Y"
                                                   and srcg.`STUDENT_ID`=ssm.STUDENT_ID)>=' . $honor_gpa1 . ' ) AND ((SELECT ROUND(AVG(grade_percent)) FROM
                                                   `student_report_card_grades` srcg,course_periods cpp  WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll="Y"
                                                   and srcg.`STUDENT_ID`=ssm.STUDENT_ID)<' . $honor_gpa2 . ' )  GROUP BY s.STUDENT_ID';
                $extra['SELECT'] .= ',(SELECT CONCAT(st.LAST_NAME,", ",coalesce(st.FIRST_NAME)) FROM staff st,course_periods cp,course_period_var cpv,school_periods p,schedule ss WHERE st.STAFF_ID=cp.TEACHER_ID AND cpv.PERIOD_id=p.PERIOD_ID  AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID  AND p.ATTENDANCE="Y" AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID AND ss.STUDENT_ID=s.STUDENT_ID AND ss.SYEAR=' . UserSyear() . ' AND ss.MARKING_PERIOD_ID = ' . UserMp() . ' AND (ss.START_DATE<=' . date('Y-m-d', strtotime(DBDate())) . ' AND (ss.END_DATE>=' . date('Y-m-d', strtotime(DBDate())) . ' OR ss.END_DATE IS NULL)) ORDER BY p.SORT_ORDER LIMIT 1) AS TEACHER';
                $extra['SELECT'] .= ',(SELECT cpv.ROOM_ID AS ROOM FROM course_periods cp,course_period_var cpv,school_periods p,schedule ss WHERE cpv.PERIOD_id=p.PERIOD_ID  AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID  AND p.ATTENDANCE="Y" AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID AND ss.STUDENT_ID=s.STUDENT_ID AND ss.SYEAR=' . UserSyear() . ' AND ss.MARKING_PERIOD_ID = ' . UserMp() . ' AND (ss.START_DATE<=' . date('Y-m-d', strtotime(DBDate())) . ' AND (ss.END_DATE>=' . date("Y-m-d", strtotime(DBDate())) . ' OR ss.END_DATE IS NULL)) ORDER BY p.SORT_ORDER LIMIT 1) AS ROOM';
            }
            $extra['ORDER_BY'] = 'HONOR_ROLL,SORT_ORDER DESC,ROOM,FULL_NAME';
        } elseif ($_REQUEST['honor_roll'] == 986) {
            $SCHOOL_RET = DBGet(DBQuery('SELECT * from schools where ID = \'' . UserSchool() . '\''));
            $scale = $SCHOOL_RET[1]['REPORTING_GP_SCALE'];
            $st_list = '\'' . implode('\',\'', $_REQUEST['st_arr']) . '\'';
            $extra['WHERE'] = ' AND s.STUDENT_ID IN (' . $st_list . ')';
            $mp_RET = DBGet(DBQuery('SELECT TITLE,END_DATE FROM marking_periods WHERE MARKING_PERIOD_ID =\'' . UserMP() . '\' '));
            $school_info_RET = DBGet(DBQuery('SELECT TITLE,PRINCIPAL FROM schools WHERE ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''));
            $extra['SELECT'] = ',coalesce(s.COMMON_NAME,s.FIRST_NAME) AS NICK_NAME';
            $extra['SELECT'] .= ',(SELECT SORT_ORDER FROM school_gradelevels WHERE ID=ssm.GRADE_ID) AS SORT_ORDER';
            if ($_REQUEST['w_course_period_id']) {
                $extra['SELECT'] .= ',(SELECT hr.TITLE FROM honor_roll hr WHERE hr.VALUE=
                                                                (SELECT if((ROUND(AVG(srcg.grade_percent))>=
                                                                (SELECT hr.VALUE FROM honor_roll hr WHERE hr.VALUE>=
                                                                (SELECT ROUND(AVG(srcg.grade_percent)) FROM `student_report_card_grades` srcg,course_periods cpp  WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.course_period_id=\'' . $_REQUEST['w_course_period_id'] . '\' and cpp.does_honor_roll="Y" and srcg.`STUDENT_ID`=ssm.STUDENT_ID) order by hr.value desc limit 1)
                                                                and ROUND(AVG(srcg.grade_percent))<
                                                                (SELECT hr.VALUE FROM honor_roll hr WHERE hr.VALUE
                                                                >(SELECT ROUND(AVG(srcg.grade_percent)) FROM `student_report_card_grades` srcg,course_periods cpp  WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.course_period_id=\'' . $_REQUEST['w_course_period_id'] . '\' and cpp.does_honor_roll="Y"
                                                                and srcg.STUDENT_ID=ssm.STUDENT_ID) order by hr.value asc limit 1)),(SELECT hr.VALUE FROM honor_roll hr WHERE hr.VALUE>(SELECT ROUND(AVG(srcg.grade_percent)) FROM `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.course_period_id=\'' . $_REQUEST['w_course_period_id'] . '\' and cpp.does_honor_roll="Y"
                                                                and srcg.STUDENT_ID=ssm.STUDENT_ID) order by hr.value asc limit 1),(SELECT hr.VALUE FROM honor_roll hr WHERE hr.VALUE<=(SELECT ROUND(AVG(srcg.grade_percent)) FROM `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.course_period_id=\'' . $_REQUEST['w_course_period_id'] . '\' and cpp.does_honor_roll="Y"
                                                                and srcg.STUDENT_ID=ssm.STUDENT_ID) order by hr.value desc limit 1))
                                                                FROM `student_report_card_grades`srcg,course_periods cpp  WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.course_period_id=\'' . $_REQUEST['w_course_period_id'] . '\' and cpp.does_honor_roll="Y" and `STUDENT_ID`=ssm.STUDENT_ID)  AND hr.SCHOOL_ID=' . UserSchool() . ' AND hr.SYEAR=' . UserSyear() . ')AS HONOR_ROLL';
                $extra['SELECT'] .= ',(SELECT CONCAT(st.LAST_NAME,\', \',coalesce(st.FIRST_NAME)) FROM staff st,course_periods cp,course_period_var cpv,school_periods p,schedule ss WHERE st.STAFF_ID=cp.TEACHER_ID AND cpv.PERIOD_id=p.PERIOD_ID  AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID  AND p.ATTENDANCE=\'Y\' AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=\'' . $_REQUEST['w_course_period_id'] . '\' AND ss.STUDENT_ID=s.STUDENT_ID AND ss.SYEAR=\'' . UserSyear() . '\' AND ss.MARKING_PERIOD_ID = \'' . UserMp() . '\' AND (ss.START_DATE<=\'' . DBDate() . '\' AND (ss.END_DATE>=\'' . DBDate() . '\' OR ss.END_DATE IS NULL)) ORDER BY p.SORT_ORDER LIMIT 1) AS TEACHER';
                $extra['SELECT'] .= ',(SELECT cpv.ROOM_ID AS ROOM FROM course_periods cp,course_period_var cpv,school_periods p,schedule ss WHERE cpv.PERIOD_id=p.PERIOD_ID  AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID  AND p.ATTENDANCE=\'Y\' AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=\'' . $_REQUEST['w_course_period_id'] . '\' AND ss.STUDENT_ID=s.STUDENT_ID AND ss.SYEAR=\'' . UserSyear() . '\' AND ss.MARKING_PERIOD_ID = \'' . UserMp() . '\' AND (ss.START_DATE<=\'' . DBDate() . '\' AND (ss.END_DATE>=\'' . DBDate() . '\' OR ss.END_DATE IS NULL)) ORDER BY p.SORT_ORDER LIMIT 1) AS ROOM';
            } else {
                $extra['SELECT'] .= ',(SELECT hr.TITLE FROM honor_roll hr WHERE hr.VALUE=
                                                                (SELECT if((ROUND(AVG(srcg.grade_percent))>=
                                                                (SELECT hr.VALUE FROM honor_roll hr WHERE hr.VALUE>=
                                                                (SELECT ROUND(AVG(srcg.grade_percent)) FROM `student_report_card_grades` srcg,course_periods cpp  WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll="Y" and srcg.`STUDENT_ID`=ssm.STUDENT_ID) order by hr.value desc limit 1)
                                                                and ROUND(AVG(srcg.grade_percent))<
                                                                (SELECT hr.VALUE FROM honor_roll hr WHERE hr.VALUE
                                                                >(SELECT ROUND(AVG(srcg.grade_percent)) FROM `student_report_card_grades` srcg,course_periods cpp  WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll="Y"
                                                                and srcg.STUDENT_ID=ssm.STUDENT_ID) order by hr.value asc limit 1)),(SELECT hr.VALUE FROM honor_roll hr WHERE hr.VALUE>(SELECT ROUND(AVG(srcg.grade_percent)) FROM `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll="Y"
                                                                and srcg.STUDENT_ID=ssm.STUDENT_ID) order by hr.value asc limit 1),(SELECT hr.VALUE FROM honor_roll hr WHERE hr.VALUE<=(SELECT ROUND(AVG(srcg.grade_percent)) FROM `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll="Y"
                                                                and srcg.STUDENT_ID=ssm.STUDENT_ID) order by hr.value desc limit 1))
                                                                FROM `student_report_card_grades`srcg,course_periods cpp  WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll="Y" and `STUDENT_ID`=ssm.STUDENT_ID)  AND hr.SCHOOL_ID=' . UserSchool() . ' AND hr.SYEAR=' . UserSyear() . ')AS HONOR_ROLL';
                $extra['SELECT'] .= ',(SELECT CONCAT(st.LAST_NAME,\', \',coalesce(st.FIRST_NAME)) FROM staff st,course_periods cp,course_period_var cpv,school_periods p,schedule ss WHERE st.STAFF_ID=cp.TEACHER_ID AND cpv.PERIOD_id=p.PERIOD_ID  AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID  AND p.ATTENDANCE=\'Y\' AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID AND ss.STUDENT_ID=s.STUDENT_ID AND ss.SYEAR=\'' . UserSyear() . '\' AND ss.MARKING_PERIOD_ID = \'' . UserMp() . '\' AND (ss.START_DATE<=\'' . DBDate() . '\' AND (ss.END_DATE>=\'' . DBDate() . '\' OR ss.END_DATE IS NULL)) ORDER BY p.SORT_ORDER LIMIT 1) AS TEACHER';
                $extra['SELECT'] .= ',(SELECT cpv.ROOM_ID AS ROOM FROM course_periods cp,course_period_var cpv,school_periods p,schedule ss WHERE cpv.PERIOD_id=p.PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND p.ATTENDANCE=\'Y\' AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID AND ss.STUDENT_ID=s.STUDENT_ID AND ss.SYEAR=\'' . UserSyear() . '\' AND ss.MARKING_PERIOD_ID = \'' . UserMp() . '\' AND (ss.START_DATE<=\'' . DBDate() . '\' AND (ss.END_DATE>=\'' . DBDate() . '\' OR ss.END_DATE IS NULL)) ORDER BY p.SORT_ORDER LIMIT 1) AS ROOM';
            }
            $extra['ORDER_BY'] = 'HONOR_ROLL,SORT_ORDER DESC,ROOM,FULL_NAME';
        }
        $RET = GetStuList($extra);
        if ($_REQUEST['list']) {

            echo '<CENTER>';
            echo '<TABLE width=80%>';
            echo '<TR align=center><TD colspan=6><B>' . sprintf(('%s Honor Roll'), $school_info_RET[1]['TITLE']) . ' </B></TD></TR>';
            echo '<TR align=center><TD colspan=6>&nbsp;</TD></TR>';
            $columns = array(
             'FULL_NAME' =>_student,
             'STUDENT_ID' =>_studentId,
             'ALT_ID' =>_alternateId,
             'GRADE_ID' =>_grade,
             'PHONE' =>_phone,
             'HONOR_ROLL' => _honorRoll
            );
            ListOutputPrint_Report($RET, $columns);
        } else {

            $options = '--webpage --quiet -t pdf12 --jpeg --no-links --portrait --footer t --header . --left 0.5in --top 0.5in --bodyimage ' . ($htmldocAssetsPath ? $htmldocAssetsPath : 'assets/') . 'hr_bg.jpg --fontsize 10 --textfont times';
            $handle = PDFStart();
            echo '<!-- MEDIA SIZE 8.5x11in -->';
            echo '<!-- MEDIA LANDSCAPE YES -->';
            foreach ($RET as $student) {
                echo '<CENTER>';
                echo '<TABLE>';
                echo '<TR align=center><TD><FONT size=1><BR><BR><BR><BR><BR><BR><BR><BR></FONT></TD></TR>';
                echo '<TR align=center><TD><FONT size=3>'._weHerebyRecognize.'</FONT></TD><TR>';
                echo '<TR align=center ><TD ><div style="font-family:Arial; font-size:13px; padding:0px 12px 0px 12px;"><div style="font-size:18px;">' . $student['NICK_NAME'] . ' ' . $student['LAST_NAME'] . '</div></div></TD><TR>';

                echo '<TR align=center><TD><FONT size=3>' . ''._whoHasCompletedAllTheAcademic.'<BR>'._requirementsFor.'<BR>' . $school_info_RET[1]['TITLE'] . ' ' . ($student['HONOR_ROLL']) . ' '._honorRoll.'</FONT></TD><TR>';
                echo '</TABLE>';

                echo '<TABLE width=80%>';
                echo '<TR><TD width=65%><FONT size=1><BR></TD></TR>';
                echo '<TR><TD><FONT size=4>' . $student['TEACHER'] . '<BR></FONT><FONT size=0>'._teacher.'</FONT></TD>';
                echo '<TD><FONT size=3>' . $mp_RET[1]['TITLE'] . '<BR></FONT><FONT size=0>'._markingPeriod.'</FONT></TD>';
                echo '</TR>';

                echo '<TR><TD><FONT size=4>' . $school_info_RET[1]['PRINCIPAL'] . '<BR></FONT><FONT size=0>'._principal.'</FONT></TD>';
                echo '<TD><FONT size=3>' . date('F j, Y', strtotime($mp_RET[1]['END_DATE'])) . '<BR></FONT><FONT size=0>'._date.'</FONT></TD>';
                echo '</TR>';
                echo '</TABLE>';
                echo '</CENTER>';
                echo "<div style=\"page-break-before: always;\"></div>";
                echo '<!-- NEW PAGE -->';
            }
            PDFStop($handle);
        }
    } else
        BackPrompt(_youMustChooseAtLeastOneStudent);
}
elseif ($_REQUEST['modfunc'] == 'save') {
    echo '<font color=red>First setup the Honor Roll(grades->Setup->Honor Roll Setup)..</font>';
}
if (!$_REQUEST['modfunc']) {
    if ($_REQUEST['search_modfunc'] == 'list') {
        $sem = GetParentMP('SEM', UserMP());
        $fy = GetParentMP('FY', $sem);
        $pros = GetChildrenMP('PRO', UserMP());
        if ($fy)
            $gradedmp = DBGet(DBQuery('SELECT count(*) as GRADES from student_report_card_grades where marking_period_id=' . $fy . ''));
        if (!$gradedmp[1]['GRADES'] && $sem) {
            $gradedmp = DBGet(DBQuery('SELECT count(*) as GRADES from student_report_card_grades where marking_period_id=' . $sem . ''));
            $mp = $sem;
        } else if (!$gradedmp[1]['GRADES']) {
            $gradedmp = DBGet(DBQuery('SELECT count(*) as GRADES from student_report_card_grades where marking_period_id=\'' . UserMP() . '\' '));
            $mp = UserMP();
        } else {
            $mp = $fy;
        }
        if ($_REQUEST['w_course_period_id'])
            echo "<FORM action=ForExport.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=save&include_inactive=" . strip_tags(trim($_REQUEST['include_inactive'])) . "&honor_roll=" . strip_tags(trim($_REQUEST['honor_roll'])) . "&mp=$mp&w_course_period_id=" . strip_tags(trim($_REQUEST['w_course_period_id'])) . "&_openSIS_PDF=true method=POST target=_blank>";
        else
            echo "<FORM action=ForExport.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=save&include_inactive=" . strip_tags(trim($_REQUEST['include_inactive'])) . "&honor_roll=" . strip_tags(trim($_REQUEST['honor_roll'])) . "&mp=$mp&_openSIS_PDF=true method=POST target=_blank>";
        $extra['header_right'] = SubmitButton(_createHonorRollForSelectedStudents, '', 'class="btn btn-primary"');

        $extra['extra_header_left'] = '<div>';

        $extra['extra_header_left'] .= '<label class="radio-inline"><INPUT type=radio name=list value="" checked>Certificates</label>';
        $extra['extra_header_left'] .= '<label class="radio-inline"><INPUT type=radio name=list value=list>'._list.'</label>';

        $extra['extra_header_left'] .= '</div>';
    }
    if (!isset($_REQUEST['_openSIS_PDF'])) {
        $extra['SELECT'] = ",s.STUDENT_ID AS CHECKBOX";
        $extra['functions'] = array('CHECKBOX' => '_makeChooseCheckbox');
        // $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller checked onclick="checkAll(this.form,this.form.controller.checked,\'st_arr\');"><A>');
        $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller checked onclick="checkAllDtMod(this,\'st_arr\');"><A>');
    }
    $extra['link'] = array('FULL_NAME' =>false);
    $extra['new'] = true;
    $extra['options']['search'] = false;
    $extra['force_search'] = true;

    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-lg-6">';
    Widgets('course');
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '<div class="col-lg-6">';
    MyWidgets('honor_roll', $mp);
    $extra['search'] .= '</div>'; //.col-lg-6
    $extra['search'] .= '</div>'; //.row

    if ($_REQUEST['search_modfunc'] == 'list') {
        if ($_SESSION['count_stu'] != 0) {
            $extra['footer'] = '<div class="panel-footer text-right p-r-20">' . SubmitButton(_createHonorRollForSelectedStudents, '', 'class="btn btn-primary"') . '</div>';
        }
    }
    Search('student_id', $extra);

    if ($_REQUEST['search_modfunc'] == 'list') {
        echo "</FORM>";
    }

    /*
     * Course Selection Modal Start
     */
    echo '<div id="modal_default" class="modal fade">';
    echo '<div class="modal-dialog modal-lg">';
    echo '<div class="modal-content">';
    echo '<div class="modal-header">';
    echo '<button type="button" class="close" data-dismiss="modal">×</button>';
    echo '<h4 class="modal-title">'._chooseCourse.'</h4>';
    echo '</div>';

    echo '<div class="modal-body">';
    echo '<div id="conf_div" class="text-center"></div>';
    echo '<div class="row" id="resp_table">';
    echo '<div class="col-md-4">';
    $sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
    $QI = DBQuery($sql);
    $subjects_RET = DBGet($QI);

    echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' '._subjectWas : ' '._subjectsWere) . ' '._found.'.</h6>';
    if (count($subjects_RET) > 0) {
        echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>'._subject.'</th></tr></thead>';
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
    // return '<INPUT type=checkbox name=st_arr[] value=' . $value . ' checked>';
    
    return "<input name=unused[$THIS_RET[STUDENT_ID]] value=" . $THIS_RET['STUDENT_ID'] . "  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"st_arr[]\",this,$THIS_RET[STUDENT_ID]);' />";
}
// function _makeChooseCheckbox($value, $title) {
//     return '<INPUT type=checkbox name=st_arr[] value=' . $value . ' checked>';
// }

function MyWidgets($item, $mp) {
    global $extra, $THIS_RET;

    switch ($item) {
        case 'honor_roll':



            if ($_REQUEST['honor_roll'] != 986 && $_REQUEST['honor_roll']) {
                $honor = DBGet(DBQuery('SELECT VALUE  FROM honor_roll WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' ORDER BY VALUE DESC'));
                $honor_gpa1 = $_REQUEST['honor_roll'];
                foreach ($honor as $gp_val) {
                    $gpa_value[] = $gp_val['VALUE'];
                }
                foreach ($gpa_value as $gpa_val_key => $gpa_val) {
                    if ($gpa_val == $honor_gpa1) {
                        $key = $gpa_val_key;
                    }
                }
                if ($key !== 0) {

                    if ($gpa_value[$key + 1] > $honor_gpa1) {
                        $honor_gpa2 = $gpa_value[$key + 1];
                    } else {
                        $honor_gpa2 = $gpa_value[$key - 1];
                    }
                }

                if ($honor_gpa2) {

                    $extra['FROM'] .= ',student_report_card_grades srg';
                    if ($_REQUEST['w_course_period_id']) {
                        $extra['SELECT'] .= ',( SELECT hr.TITLE FROM honor_roll hr WHERE  hr.VALUE=(SELECT if((ROUND(AVG(srcg.grade_percent))>=' . $honor_gpa1 . ' and ROUND(AVG(srcg.grade_percent))<' . $honor_gpa2 . '),' . $honor_gpa1 . ',"")  FROM
                                                   `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.course_period_id=\'' . $_REQUEST['w_course_period_id'] . '\' and cpp.does_honor_roll=\'Y\'
                                                   and srcg.STUDENT_ID=ssm.STUDENT_ID)  AND hr.SCHOOL_ID=' . UserSchool() . '  AND hr.SYEAR=' . UserSyear() . ')AS HONOR_ROLL';
                        $extra['WHERE'] .= 'AND ((SELECT ROUND(AVG(srcg.grade_percent)) FROM
                                                   `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.course_period_id=\'' . $_REQUEST['w_course_period_id'] . '\' and cpp.does_honor_roll=\'Y\'
                                                   and srcg.STUDENT_ID=ssm.STUDENT_ID)>=' . $honor_gpa1 . ' ) AND ((SELECT ROUND(AVG(srcg.grade_percent)) FROM
                                                   `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.course_period_id=\'' . $_REQUEST['w_course_period_id'] . '\' and cpp.does_honor_roll=\'Y\'
                                                   and srcg.STUDENT_ID=ssm.STUDENT_ID)<' . $honor_gpa2 . ' )  ';
                    } else {
                        $extra['SELECT'] .= ',( SELECT hr.TITLE FROM honor_roll hr WHERE  hr.VALUE=(SELECT if((ROUND(AVG(srcg.grade_percent))>=' . $honor_gpa1 . ' and ROUND(AVG(srcg.grade_percent))<' . $honor_gpa2 . '),' . $honor_gpa1 . ',"")  FROM
                                                   `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll=\'Y\'
                                                   and srcg.STUDENT_ID=ssm.STUDENT_ID)  AND hr.SCHOOL_ID=' . UserSchool() . '  AND hr.SYEAR=' . UserSyear() . ')AS HONOR_ROLL';
                        $extra['WHERE'] .= 'AND ((SELECT ROUND(AVG(srcg.grade_percent)) FROM
                                                   `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll=\'Y\'
                                                   and srcg.STUDENT_ID=ssm.STUDENT_ID)>=' . $honor_gpa1 . ' ) AND ((SELECT ROUND(AVG(srcg.grade_percent)) FROM
                                                   `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll=\'Y\'
                                                   and srcg.STUDENT_ID=ssm.STUDENT_ID)<' . $honor_gpa2 . ' )  ';
                    }
                    $extra['GROUP'] .= ' s.STUDENT_ID';
                } else {
                    $honor_gpa2 = 100;
                    $extra['FROM'] .= ',student_report_card_grades srg';
                    if ($_REQUEST['w_course_period_id']) {
                        $extra['SELECT'] .= ',(SELECT hr.TITLE FROM honor_roll hr WHERE  hr.VALUE=(SELECT if((ROUND(AVG(srcg.grade_percent))>=' . $honor_gpa1 . ' and ROUND(AVG(srcg.grade_percent))<' . $honor_gpa2 . '),' . $honor_gpa1 . ',"") FROM
                                                `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.course_period_id=\'' . $_REQUEST['w_course_period_id'] . '\' and cpp.does_honor_roll=\'Y\'
                                                and srcg.`STUDENT_ID`=ssm.STUDENT_ID)  AND hr.SCHOOL_ID=' . UserSchool() . ' AND hr.SYEAR=' . UserSyear() . ')AS HONOR_ROLL';

                        $extra['WHERE'] .= 'AND ((SELECT ROUND(AVG(srcg.grade_percent)) FROM
                                                `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.course_period_id=\'' . $_REQUEST['w_course_period_id'] . '\' and cpp.does_honor_roll=\'Y\'
                                                and srcg.`STUDENT_ID`=ssm.STUDENT_ID)>=' . $honor_gpa1 . ' ) AND ((SELECT ROUND(AVG(srcg.grade_percent)) FROM
                                                `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.course_period_id=\'' . $_REQUEST['w_course_period_id'] . '\' and cpp.does_honor_roll=\'Y\'
                                                and srcg.`STUDENT_ID`=ssm.STUDENT_ID)<' . $honor_gpa2 . ' )  ';
                    } else {
                        $extra['SELECT'] .= ',(SELECT hr.TITLE FROM honor_roll hr WHERE  hr.VALUE=(SELECT if((ROUND(AVG(srcg.grade_percent))>=' . $honor_gpa1 . ' and ROUND(AVG(srcg.grade_percent))<' . $honor_gpa2 . '),' . $honor_gpa1 . ',"") FROM
                                                `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll=\'Y\'
                                                and srcg.`STUDENT_ID`=ssm.STUDENT_ID)  AND hr.SCHOOL_ID=' . UserSchool() . ' AND hr.SYEAR=' . UserSyear() . ')AS HONOR_ROLL';

                        $extra['WHERE'] .= 'AND ((SELECT ROUND(AVG(srcg.grade_percent)) FROM
                                                `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll=\'Y\'
                                                and srcg.`STUDENT_ID`=ssm.STUDENT_ID)>=' . $honor_gpa1 . ' ) AND ((SELECT ROUND(AVG(srcg.grade_percent)) FROM
                                                `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll=\'Y\'
                                                and srcg.`STUDENT_ID`=ssm.STUDENT_ID)<' . $honor_gpa2 . ' )  ';
                    }
                    $extra['GROUP'] .= ' s.STUDENT_ID';
                }
                $extra['columns_after'] = array('HONOR_ROLL' =>_honorRoll);
            } elseif ($_REQUEST['honor_roll'] == 986) {
                if ($_REQUEST['w_course_period_id'])
                    $extra['SELECT'] .= ',(SELECT hr.TITLE FROM honor_roll hr WHERE hr.VALUE=
                                                                (SELECT if(
                                                                            (ROUND(AVG(srcg.grade_percent))>=(SELECT hr.VALUE FROM honor_roll hr WHERE hr.VALUE>=
                                                                             (SELECT ROUND(AVG(srcg.grade_percent)) FROM `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.course_period_id=\'' . $_REQUEST['w_course_period_id'] . '\' and cpp.does_honor_roll="Y" and srcg.`STUDENT_ID`=ssm.STUDENT_ID) order by hr.value desc limit 1)
                                                                and ROUND(AVG(srcg.grade_percent))<
                                                                (SELECT hr.VALUE FROM honor_roll hr WHERE hr.VALUE
                                                                >(SELECT ROUND(AVG(srcg.grade_percent)) FROM `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.course_period_id=\'' . $_REQUEST['w_course_period_id'] . '\' and cpp.does_honor_roll="Y"
                                                                and srcg.`STUDENT_ID`=ssm.STUDENT_ID) order by hr.value asc limit 1)),(SELECT hr.VALUE FROM honor_roll hr WHERE hr.VALUE>(SELECT ROUND(AVG(srcg.grade_percent)) FROM `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.course_period_id=\'' . $_REQUEST['w_course_period_id'] . '\' and cpp.does_honor_roll=\'Y\'
                                                                and srcg.`STUDENT_ID`=ssm.STUDENT_ID) order by hr.value asc limit 1),(SELECT hr.VALUE FROM honor_roll hr WHERE hr.VALUE<=(SELECT ROUND(AVG(srcg.grade_percent)) FROM `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.course_period_id=\'' . $_REQUEST['w_course_period_id'] . '\' and cpp.does_honor_roll=\'Y\'
                                                                and srcg.`STUDENT_ID`=ssm.STUDENT_ID) order by hr.value desc limit 1))
                                                                FROM `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll="Y" and srcg.`STUDENT_ID`=ssm.STUDENT_ID)  AND hr.SCHOOL_ID=' . UserSchool() . ' AND hr.SYEAR=' . UserSyear() . ')AS HONOR_ROLL';
                else
                    $extra['SELECT'] .= ',(SELECT hr.TITLE FROM honor_roll hr WHERE hr.VALUE=
                                                                (SELECT if(
                                                                            (ROUND(AVG(srcg.grade_percent))>=(SELECT hr.VALUE FROM honor_roll hr WHERE hr.VALUE>=
                                                                             (SELECT ROUND(AVG(srcg.grade_percent)) FROM `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll="Y" and srcg.`STUDENT_ID`=ssm.STUDENT_ID) order by hr.value desc limit 1)
                                                                and ROUND(AVG(srcg.grade_percent))<
                                                                (SELECT hr.VALUE FROM honor_roll hr WHERE hr.VALUE
                                                                >(SELECT ROUND(AVG(srcg.grade_percent)) FROM `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll="Y"
                                                                and srcg.`STUDENT_ID`=ssm.STUDENT_ID) order by hr.value asc limit 1)),(SELECT hr.VALUE FROM honor_roll hr WHERE hr.VALUE>(SELECT ROUND(AVG(srcg.grade_percent)) FROM `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll=\'Y\'
                                                                and srcg.`STUDENT_ID`=ssm.STUDENT_ID) order by hr.value asc limit 1),(SELECT hr.VALUE FROM honor_roll hr WHERE hr.VALUE<=(SELECT ROUND(AVG(srcg.grade_percent)) FROM `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll=\'Y\'
                                                                and srcg.`STUDENT_ID`=ssm.STUDENT_ID) order by hr.value desc limit 1))
                                                                FROM `student_report_card_grades` srcg,course_periods cpp WHERE srcg.MARKING_PERIOD_ID = ' . UserMp() . ' and srcg.course_period_id=cpp.course_period_id  and cpp.does_honor_roll="Y" and srcg.`STUDENT_ID`=ssm.STUDENT_ID)  AND hr.SCHOOL_ID=' . UserSchool() . ' AND hr.SYEAR=' . UserSyear() . ')AS HONOR_ROLL';

                $extra['columns_after'] = array('HONOR_ROLL' =>_honorRoll);
            }
            $option = DBGet(DBQuery('SELECT TITLE,VALUE  FROM honor_roll WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\'  ORDER BY VALUE'));
            $options['986'] = 'All';
            foreach ($option as $option_value) {
                $options[$option_value['VALUE']] = $option_value['TITLE'];
            }
            $extra['search'] .= '<div class="form-group"><label class="control-label col-lg-4 text-right">'._honorRoll.'</label><div class="col-lg-8">' . SelectInput("", 'honor_roll', '', $options, false, '') . '</div></div>';
            break;
    }
}

?>