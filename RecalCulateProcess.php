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
error_reporting(0);

include('RedirectRootInc.php');
include 'Warehouse.php';
include 'Data.php';
DBQuery('Create Table TEMP_SRCG AS SELECT * FROM student_report_card_grades  WHERE SYEAR=\'' . UserSyear() . '\'  AND COURSE_PERIOD_ID IS NOT NULL AND STUDENT_ID IN (\'' . $_REQUEST['students'] . '\') AND MARKING_PERIOD_ID=\'' . $_REQUEST['mp'] . '\'');
DBQuery('DELETE FROM student_report_card_grades WHERE SYEAR=\'' . UserSyear() . '\'  AND COURSE_PERIOD_ID IS NOT NULL AND STUDENT_ID IN (\'' . $_REQUEST['students'] . '\') AND MARKING_PERIOD_ID=\'' . $_REQUEST['mp'] . '\'');
DBQuery('INSERT INTO `student_report_card_grades`(`syear`, `school_id`, `student_id`, `course_period_id`, `report_card_grade_id`, `report_card_comment_id`, `comment`, `grade_percent`, `marking_period_id`, `grade_letter`, `weighted_gp`, `unweighted_gp`, `gp_scale`,`gpa_cal`, `credit_attempted`, `credit_earned`, `credit_category`,`course_code`, `course_title`, `id`) SELECT `syear`, `school_id`, `student_id`, `course_period_id`, `report_card_grade_id`, `report_card_comment_id`, `comment`, `grade_percent`, `marking_period_id`, `grade_letter`, `weighted_gp`, `unweighted_gp`, `gp_scale`,`gpa_cal`, `credit_attempted`, `credit_earned`, `credit_category`, `course_code`,`course_title`, `id` FROM TEMP_SRCG ');
DBQuery('DROP TABLE TEMP_SRCG');
$students_RET = DBGet(DBQuery('SELECT STUDENT_ID,MARKING_PERIOD_ID FROM student_report_card_grades WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND COURSE_PERIOD_ID IS NULL GROUP BY MARKING_PERIOD_ID'));
foreach ($students_RET as $stu_key => $stu_val) {
  $res =  DBGet(DBQuery('SELECT
    SUM(srcg.weighted_gp/s.reporting_gp_scale) AS sum_weighted_factors,  COUNT(*) AS count_weighted_factors,                        
    SUM(srcg.unweighted_gp/srcg.gp_scale) AS sum_unweighted_factors, 
    COUNT(*) AS count_unweighted_factors,
   IF(ISNULL( sum(srcg.unweighted_gp) ),  (select SUM(sg.weighted_gp*sg.credit_earned) from student_report_card_grades sg where sg.marking_period_id=\'' . $stu_val['MARKING_PERIOD_ID'] . '\' AND sg.student_id=\'' . $stu_val['STUDENT_ID'] . '\' AND sg.gpa_cal=\'Y\')/ (select sum(sg.credit_attempted) from student_report_card_grades sg where sg.marking_period_id=\'' . $stu_val['MARKING_PERIOD_ID'] . '\' AND sg.student_id=\'' . $stu_val['STUDENT_ID'] . '\' AND sg.gpa_cal=\'Y\'),
                      IF(ISNULL( sum(srcg.weighted_gp) ), (select SUM(sg.unweighted_gp*sg.credit_earned) from student_report_card_grades sg where sg.marking_period_id=\'' . $stu_val['MARKING_PERIOD_ID'] . '\' AND sg.student_id=\'' . $stu_val['STUDENT_ID'] . '\' AND sg.gpa_cal=\'Y\')/(select sum(sg.credit_attempted) from student_report_card_grades sg where sg.marking_period_id=\'' . $stu_val['MARKING_PERIOD_ID'] . '\' AND sg.student_id=\'' . $stu_val['STUDENT_ID'] . '\' AND sg.gpa_cal=\'Y\'),
                         ( (select SUM(sg.unweighted_gp*sg.credit_attempted) from student_report_card_grades sg where sg.marking_period_id=\'' . $stu_val['MARKING_PERIOD_ID'] . '\' AND sg.student_id=\'' . $stu_val['STUDENT_ID'] . '\' AND sg.gpa_cal=\'Y\')+ (select SUM(sg.weighted_gp*sg.credit_earned) from student_report_card_grades sg where sg.marking_period_id=\'' . $stu_val['MARKING_PERIOD_ID'] . '\' AND sg.student_id=\'' . $stu_val['STUDENT_ID'] . '\' AND sg.gpa_cal=\'Y\'))/(select sum(sg.credit_attempted) from student_report_card_grades sg where sg.marking_period_id=\'' . $stu_val['MARKING_PERIOD_ID'] . '\' AND sg.student_id=\'' . $stu_val['STUDENT_ID'] . '\' AND sg.gpa_cal=\'Y\')
                        )
      ) AS gpa,
    
   (select SUM(sg.weighted_gp*sg.credit_earned) from student_report_card_grades sg where sg.marking_period_id=\'' . $stu_val['MARKING_PERIOD_ID'] . '\' AND sg.student_id=\'' . $stu_val['STUDENT_ID'] . '\' AND sg.gpa_cal=\'Y\')/(select sum(sg.credit_attempted) from student_report_card_grades sg where sg.marking_period_id=\'' . $stu_val['MARKING_PERIOD_ID'] . '\' AND sg.student_id=\'' . $stu_val['STUDENT_ID'] . '\' 
                                                   AND sg.gpa_cal=\'Y\' AND sg.weighted_gp  IS NOT NULL  AND sg.unweighted_gp IS NULL GROUP BY sg.student_id, sg.marking_period_id) AS weighted_gpa,
    (select SUM(sg.unweighted_gp*sg.credit_earned) from student_report_card_grades sg where sg.marking_period_id=\'' . $stu_val['MARKING_PERIOD_ID'] . '\' AND sg.student_id=\'' . $stu_val['STUDENT_ID'] . '\' AND sg.gpa_cal=\'Y\')/ (select sum(sg.credit_attempted) from student_report_card_grades sg where sg.marking_period_id=\'' . $stu_val['MARKING_PERIOD_ID'] . '\' AND sg.student_id=\'' . $stu_val['STUDENT_ID'] . '\'
                                                     AND sg.gpa_cal=\'Y\' AND sg.unweighted_gp  IS NOT NULL  AND sg.weighted_gp IS NULL GROUP BY sg.student_id, sg.marking_period_id) unweighted_gpa,
    eg.short_name AS grade_level_short FROM student_report_card_grades srcg
  INNER JOIN schools s ON s.id=srcg.school_id

  LEFT JOIN enroll_grade eg on eg.student_id=srcg.student_id AND eg.syear=srcg.syear AND eg.school_id=srcg.school_id
  WHERE  srcg.student_id=\'' . $stu_val['STUDENT_ID'] . '\' AND srcg.gp_scale<>0 AND srcg.gpa_cal=\'Y\' AND srcg.course_period_id IS NULL AND srcg.marking_period_id NOT LIKE \'E%\'
  GROUP BY srcg.marking_period_id,eg.short_name'));

  $stu_stat = DBGet(DBQuery('SELECT COUNT(*) AS COUNT FROM student_gpa_calculated WHERE marking_period_id=\'' . $stu_val['MARKING_PERIOD_ID'] . '\' AND student_id=\'' . $stu_val['STUDENT_ID'] . '\''));

  if ($stu_stat[1]['COUNT'] == 0)
    DBQuery('INSERT INTO student_gpa_calculated(student_id,marking_period_id)
      VALUES(\'' . $stu_val['STUDENT_ID'] . '\',\'' . $stu_val['MARKING_PERIOD_ID'] . '\')');
  DBQuery('UPDATE student_gpa_calculated g
    INNER JOIN (
	SELECT s.student_id,
		SUM(s.weighted_gp/sc.reporting_gp_scale)/COUNT(*) AS cum_weighted_factor,
		SUM(s.unweighted_gp/s.gp_scale)/COUNT(*) AS cum_unweighted_factor
	FROM student_report_card_grades s
	INNER JOIN schools sc ON sc.id=s.school_id
	
	WHERE s.marking_period_id=\'' . $stu_val['MARKING_PERIOD_ID'] . '\' AND s.course_period_id IS NULL AND s.gpa_cal=\'Y\' AND 
	s.student_id=\'' . $stu_val['STUDENT_ID'] . '\') gg ON gg.student_id=g.student_id
    SET g.cum_unweighted_factor=gg.cum_unweighted_factor
    WHERE g.student_id=\'' . $stu_val['STUDENT_ID'] . '\'');

  $stu_gpa_cal = DBGet(DBQuery('SELECT COUNT(*) AS COUNT FROM student_gpa_calculated WHERE marking_period_id=\'' . $stu_val['MARKING_PERIOD_ID'] . '\' AND student_id=\'' . $stu_val['STUDENT_ID'] . '\''));
  if ($stu_gpa_cal[1]['COUNT'] != 0) {
    DBQuery('UPDATE student_gpa_calculated SET gpa=\'' . $res[1]['GPA'] . '\', weighted_gpa=\'' . $res[1]['WEIGHTED_GPA'] . '\',unweighted_gpa=\'' . $res[1]['UNWEIGHTED_GPA'] . '\' WHERE marking_period_id=\'' . $stu_val['MARKING_PERIOD_ID'] . '\' AND student_id=\'' . $stu_val['STUDENT_ID'] . '\'');
  } else
    DBQuery('INSERT INTO student_gpa_calculated(student_id,marking_period_id,mp,gpa,weighted_gpa,unweighted_gpa,grade_level_short)
      VALUES(\'' . $stu_val['STUDENT_ID'] . '\',\'' . $stu_val['MARKING_PERIOD_ID'] . '\',\'' . $stu_val['MARKING_PERIOD_ID'] . '\',\'' . $res[1]['GPA'] . '\',\'' . $res[1]['WEIGHTED_GPA'] . '\',
        \'' . $res[1]['unweighted_gpa'] . '\',\'' . $res[1]['GRADE_LEVEL_SHORT'] . '\')');
}
$sql = "CREATE TEMPORARY table temp_cum_gpa AS
    SELECT  * FROM student_report_card_grades srcg WHERE credit_attempted=
    (SELECT MAX(credit_attempted) FROM student_report_card_grades srcg1 WHERE srcg.course_period_id=srcg1.course_period_id and srcg.student_id=srcg1.student_id AND srcg1.course_period_id IS NOT NULL) 
        GROUP BY course_period_id,student_id,marking_period_id
     UNION SELECT * FROM student_report_card_grades WHERE course_period_id IS NULL AND report_card_grade_id IS NULL;";   //);
$sql .= "DROP TABLE IF EXISTS tmp;";
$sql .= "SELECT CALC_CUM_GPA_MP('" . $_REQUEST['mp'] . "');";
$sql .= "DROP TABLE IF EXISTS tmp;";
$sql .= "SELECT SET_CLASS_RANK_MP('" . $_REQUEST['mp'] . "');";

if (mysqli_multi_query($connection, $sql)) {
  echo '<br/><table><tr><td width="38"><img src="assets/icon_ok.png" /></td><td valign="middle"><span style="font-size:14px;">The grades for ' . GetMP($_REQUEST['mp']) . ' has been recalculated.</span></td></tr></table>';
}

unset($_REQUEST['modfunc']);
