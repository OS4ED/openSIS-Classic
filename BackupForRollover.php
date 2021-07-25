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
include('RedirectRootInc.php');
include('Warehouse.php');
require_once("Data.php");
sleep(5);
$print_form=1;
$output_messages=array();
//test mysql connection
ini_set('memory_limit','9000M');
ini_set('max_execution_time','50000');
ini_set('max_input_time','50000');
if(User('PROFILE')=='admin'&& isset($_REQUEST['action']) && $_REQUEST['action']=='Backup' )
{
	$mysql_host=$DatabaseServer;
	$mysql_database=$DatabaseName;
	$mysql_username=$DatabaseUsername;
        $mysql_password=$DatabasePassword;
        $mysql_port=$DatabasePort;
        
        
        $print_form = 0;
    $date_time = date("m-d-Y");

    $backup_folder = 'opensis_databackup';
    if(!is_dir($backup_folder))
    {
      mkdir("opensis_databackup");
    }

    $Export_FileName = 'opensis_databackup/'.$mysql_database . 'Backup' . $date_time . '.sql';
    // $dbconn = new mysqli($mysql_host, $mysql_username, $mysql_password, $mysql_database, $mysql_port);
    $dbconn = $connection;
    if ($dbconn->connect_errno != 0)
        exit($dbconn->error);
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $result = $dbconn->query("SHOW VARIABLES LIKE 'basedir'");
        $row = $result->fetch_assoc();
        $mysql_dir1 = substr($row['Value'], 0, 2);
        $sql_path_arr=explode("\\",$_SERVER['MYSQL_HOME']);
        $sql_path="\\".$sql_path_arr[1].'\\'.$sql_path_arr[2].'\\'.$sql_path_arr[3];
        $mysql_dir = str_replace('\\', '\\\\', $mysql_dir1.$_SERVER['MYSQL_HOME']);
//        $mysql_dir = str_replace('\\', '\\\\', $mysql_dir1.$sql_path);
    }
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        if ($mysql_password == '')
            exec("$mysql_dir\\mysqldump -n -c --skip-add-locks --skip-disable-keys --routines --triggers --user $mysql_username  $mysql_database > $Export_FileName");
        else
            exec("$mysql_dir\\mysqldump -n -c --skip-add-locks --skip-disable-keys --routines --triggers --user $mysql_username --password='$mysql_password' $mysql_database > $Export_FileName");
    }
    else {
        exec("mysqldump -n -c --skip-add-locks --skip-disable-keys --routines --triggers --user $mysql_username --password='$mysql_password' $mysql_database > $Export_FileName");
    }
	//$mysql_password=$_REQUEST['mysql_password'];
        
//		_mysql_test($mysql_host,$mysql_database, $mysql_username, $mysql_password,$mysql_port);
//		
//			$print_form=0;
//			
//                        $date_time=date("m-d-Y");
//                    ;
//                        $Export_FileName=$mysql_database.'_'.$date_time ;
//
//
//			$myfile = fopen($Export_FileName.".sql", "w");
//                        fclose($myfile);
//         unset($myfile);
//			                        $f_content= "-- Server version:". mysqli_get_server_info()."\n";
//                        $f_content= "-- PHP Version: ".phpversion()."\n\n";
//                        $f_content.= 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";';
//
//                        $f_content.= "\n\n/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
//                        $f_content.= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
//                        $f_content.= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
//                        $f_content.= "/*!40101 SET NAMES utf8 */;\n\n";
//
//                        $f_content.= "--\n";
//                        $f_content.= "-- Database: `$mysql_database`\n";
//                        $f_content.= "--\n\n";
//                        $f_content.= "-- --------------------------------------------------------\n\n";
//
//
//
//			$f_content.=_mysqldump($mysql_database) or die('not working');
//                        $myfile = fopen($Export_FileName.".sql", "w") or die('not working');
//                        fwrite($myfile, $f_content) or die('not working');
//                        fclose($myfile) or die('not working');
                        echo _fileSaved;
			
		
}
function _mysqldump($mysql_database)
{

        $sql='show tables where tables_in_'.$mysql_database.' not like \'course_details%\' and tables_in_'.$mysql_database.' not like \'enroll_grade%\'
               and tables_in_'.$mysql_database.' not like \'marking_periods%\' and tables_in_'.$mysql_database.' not like \'student_contacts%\' and tables_in_'.$mysql_database.' not like \'transcript_grades%\' ;';
	$result= DBGet(DBQuery($sql));
        $column_name=strtoupper('tables_in_'.$mysql_database);
	if( count($result)>0)
	{
                foreach($result as $row)
		{
                        $f_content_n.= _mysqldump_table_structure($row[$column_name]);
			$f_content_n.= _mysqldump_table_data($row[$column_name]);
		}
	$f_content_n.= "--
              --
              --

                    CREATE VIEW marking_periods AS
            SELECT q.marking_period_id, 'openSIS' AS mp_source, q.syear,
                q.school_id, 'quarter' AS mp_type, q.title, q.short_name,
                q.sort_order, q.semester_id AS parent_id,
                s.year_id AS grandparent_id, q.start_date,
                q.end_date, q.post_start_date,
                q.post_end_date, q.does_grades,
                q.does_exam, q.does_comments
            FROM school_quarters q
            JOIN school_semesters s ON q.semester_id = s.marking_period_id
        UNION
            SELECT marking_period_id, 'openSIS' AS mp_source, syear,
                school_id, 'semester' AS mp_type, title, short_name,
                sort_order, year_id AS parent_id,
                -1 AS grandparent_id, start_date,
                end_date, post_start_date,
                post_end_date, does_grades,
                does_exam, does_comments
            FROM school_semesters
        UNION
            SELECT marking_period_id, 'openSIS' AS mp_source, syear,
                school_id, 'year' AS mp_type, title, short_name,
                sort_order, -1 AS parent_id,
                -1 AS grandparent_id, start_date,
                end_date, post_start_date,
                post_end_date, does_grades,
                does_exam, does_comments
            FROM school_years
        UNION
            SELECT marking_period_id, 'History' AS mp_source, syear,
                school_id, mp_type, name AS title, NULL AS short_name,
                NULL AS sort_order, parent_id,
                -1 AS grandparent_id, NULL AS start_date,
                post_end_date AS end_date, NULL AS post_start_date,
                post_end_date, 'Y' AS does_grades,
                NULL AS does_exam, NULL AS does_comments
                FROM history_marking_periods;\n

          

             CREATE VIEW course_details AS
            SELECT cp.school_id, cp.syear, cp.marking_period_id, cp.period_id, c.subject_id,
            cp.course_id, cp.course_period_id, cp.teacher_id,cp. secondary_teacher_id, c.title AS course_title,
            cp.title AS cp_title, cp.grade_scale_id, cp.mp, cp.credits
            FROM course_periods cp, courses c WHERE (cp.course_id = c.course_id);\n\n

            CREATE VIEW enroll_grade AS
            SELECT e.id, e.syear, e.school_id, e.student_id, e.start_date, e.end_date, sg.short_name, sg.title
            FROM student_enrollment e, school_gradelevels sg WHERE (e.grade_id = sg.id);\n\n


            CREATE VIEW transcript_grades AS
            SELECT s.id AS school_id, IF(mp.mp_source='history',(SELECT school_name FROM history_school WHERE student_id=rcg.student_id and marking_period_id=mp.marking_period_id),s.title) AS school_name,mp_source, mp.marking_period_id AS mp_id,
         mp.title AS mp_name, mp.syear, mp.end_date AS posted, rcg.student_id,
         sgc.grade_level_short AS gradelevel, rcg.grade_letter, rcg.unweighted_gp AS gp_value,
         rcg.weighted_gp AS weighting, rcg.gp_scale, rcg.credit_attempted, rcg.credit_earned,
         rcg.credit_category,rcg.course_period_id AS course_period_id, rcg.course_title AS course_name,
                (SELECT courses.short_name FROM course_periods,courses  WHERE course_periods.course_id=courses.course_id and course_periods.course_period_id=rcg.course_period_id) AS course_short_name,rcg.gpa_cal AS gpa_cal,
         sgc.weighted_gpa,
         sgc.unweighted_gpa,
                          sgc.gpa,
         sgc.class_rank,mp.sort_order
            FROM student_report_card_grades rcg
            INNER JOIN marking_periods mp ON mp.marking_period_id = rcg.marking_period_id AND mp.mp_type IN ('year','semester','quarter')
            INNER JOIN student_gpa_calculated sgc ON sgc.student_id = rcg.student_id AND sgc.marking_period_id = rcg.marking_period_id
            INNER JOIN schools s ON s.id = mp.school_id;\n
            ";
        $f_content_n.= "DELIMITER $$
--
-- Procedures
--

DROP FUNCTION IF EXISTS`isDateInMarkingPeriodWorkingDates`;
DELIMITER $$
CREATE FUNCTION `isDateInMarkingPeriodWorkingDates`(`marking_period` INT(10), `date` DATE) RETURNS TINYINT(1) NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER BEGIN
    IF NOT EXISTS(
        SELECT *
        FROM `marking_periods`
        WHERE `marking_period_id` = marking_period
            AND (
                date BETWEEN `start_date` AND `end_date`
            )
    ) THEN RETURN FALSE;
    END IF;

    IF NOT EXISTS(
        SELECT *
        FROM `marking_periods`
        WHERE `parent_id` = marking_period
    ) THEN RETURN TRUE;
    END IF;

    IF NOT EXISTS(
        SELECT *
        FROM `marking_periods`
        WHERE `parent_id` = marking_period
            AND (
                date BETWEEN `start_date` AND `end_date`
            )
    ) THEN RETURN FALSE;
    END IF;

    IF NOT EXISTS(
        SELECT *
        FROM `marking_periods`
        WHERE `grandparent_id` = marking_period
            AND `parent_id` = (
                SELECT `marking_period_id`
                FROM `marking_periods`
                WHERE `parent_id` = marking_period
                    AND (
                        date BETWEEN `start_date` AND `end_date`
                    )
            )
    ) THEN RETURN TRUE;
    END IF;

    IF NOT EXISTS(
        SELECT *
        FROM `marking_periods`
        WHERE `grandparent_id` = marking_period
            AND `parent_id` = (
                SELECT `marking_period_id`
                FROM `marking_periods`
                WHERE `parent_id` = marking_period
                    AND (
                        date BETWEEN `start_date` AND `end_date`
                    )
            )
            AND (
                date BETWEEN `start_date` AND `end_date`
            )
    ) THEN RETURN FALSE;
    END IF;

    RETURN TRUE;
END$$
DELIMITER ;

CREATE PROCEDURE `ATTENDANCE_CALC`(IN cp_id INT,IN year INT,IN school INT)
BEGIN
DELETE FROM missing_attendance WHERE COURSE_PERIOD_ID=cp_id;
INSERT INTO missing_attendance(SCHOOL_ID,SYEAR,SCHOOL_DATE,COURSE_PERIOD_ID,PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID) SELECT s.ID AS SCHOOL_ID,acc.SYEAR,acc.SCHOOL_DATE,cp.COURSE_PERIOD_ID,cp.PERIOD_ID, IF(tra.course_period_id=cp.course_period_id AND acc.school_date<tra.assign_date =true,tra.pre_teacher_id,cp.teacher_id) AS TEACHER_ID,cp.SECONDARY_TEACHER_ID FROM attendance_calendar acc INNER JOIN marking_periods mp ON mp.SYEAR=acc.SYEAR AND mp.SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN mp.START_DATE AND mp.END_DATE INNER JOIN course_periods cp ON cp.MARKING_PERIOD_ID=mp.MARKING_PERIOD_ID AND cp.DOES_ATTENDANCE='Y' AND cp.CALENDAR_ID=acc.CALENDAR_ID LEFT JOIN teacher_reassignment tra ON (cp.course_period_id=tra.course_period_id) INNER JOIN school_periods sp ON sp.SYEAR=acc.SYEAR AND sp.SCHOOL_ID=acc.SCHOOL_ID AND sp.PERIOD_ID=cp.PERIOD_ID AND (sp.BLOCK IS NULL AND position(substring('UMTWHFS' FROM DAYOFWEEK(acc.SCHOOL_DATE) FOR 1) IN cp.DAYS)>0 OR sp.BLOCK IS NOT NULL AND acc.BLOCK IS NOT NULL AND sp.BLOCK=acc.BLOCK) INNER JOIN schools s ON s.ID=acc.SCHOOL_ID INNER JOIN schedule sch ON sch.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND sch.START_DATE<=acc.SCHOOL_DATE AND (sch.END_DATE IS NULL OR sch.END_DATE>=acc.SCHOOL_DATE ) AND cp.COURSE_PERIOD_ID= cp_id LEFT JOIN attendance_completed ac ON ac.SCHOOL_DATE=acc.SCHOOL_DATE AND IF(tra.course_period_id=cp.course_period_id AND acc.school_date<=tra.assign_date =true,ac.staff_id=tra.pre_teacher_id,ac.staff_id=cp.teacher_id) AND ac.PERIOD_ID=sp.PERIOD_ID WHERE acc.SYEAR=year AND acc.SCHOOL_ID=school AND (acc.MINUTES IS NOT NULL AND acc.MINUTES>0) AND acc.SCHOOL_DATE<=CURDATE() AND ac.STAFF_ID IS NULL  AND isDateInMarkingPeriodWorkingDates(cp.marking_period_id, acc.SCHOOL_DATE) GROUP BY s.TITLE,acc.SCHOOL_DATE,cp.TITLE,cp.COURSE_PERIOD_ID,cp.TEACHER_ID;
END$$

CREATE PROCEDURE `ATTENDANCE_CALC_BY_DATE`(IN sch_dt DATE,IN year INT,IN school INT)
BEGIN
 DELETE FROM missing_attendance WHERE SCHOOL_DATE=sch_dt AND SYEAR=year AND SCHOOL_ID=school;
 INSERT INTO missing_attendance(SCHOOL_ID,SYEAR,SCHOOL_DATE,COURSE_PERIOD_ID,PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID) SELECT s.ID AS SCHOOL_ID,acc.SYEAR,acc.SCHOOL_DATE,cp.COURSE_PERIOD_ID,cp.PERIOD_ID, IF(tra.course_period_id=cp.course_period_id AND acc.school_date<tra.assign_date =true,tra.pre_teacher_id,cp.teacher_id) AS TEACHER_ID,cp.SECONDARY_TEACHER_ID FROM attendance_calendar acc INNER JOIN marking_periods mp ON mp.SYEAR=acc.SYEAR AND mp.SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN mp.START_DATE AND mp.END_DATE INNER JOIN course_periods cp ON cp.MARKING_PERIOD_ID=mp.MARKING_PERIOD_ID AND cp.DOES_ATTENDANCE='Y' AND cp.CALENDAR_ID=acc.CALENDAR_ID LEFT JOIN teacher_reassignment tra ON (cp.course_period_id=tra.course_period_id) INNER JOIN school_periods sp ON sp.SYEAR=acc.SYEAR AND sp.SCHOOL_ID=acc.SCHOOL_ID AND sp.PERIOD_ID=cp.PERIOD_ID AND (sp.BLOCK IS NULL AND position(substring('UMTWHFS' FROM DAYOFWEEK(acc.SCHOOL_DATE) FOR 1) IN cp.DAYS)>0 OR sp.BLOCK IS NOT NULL AND acc.BLOCK IS NOT NULL AND sp.BLOCK=acc.BLOCK) INNER JOIN schools s ON s.ID=acc.SCHOOL_ID INNER JOIN schedule sch ON sch.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND sch.START_DATE<=acc.SCHOOL_DATE AND (sch.END_DATE IS NULL OR sch.END_DATE>=acc.SCHOOL_DATE )  LEFT JOIN attendance_completed ac ON ac.SCHOOL_DATE=acc.SCHOOL_DATE AND IF(tra.course_period_id=cp.course_period_id AND acc.school_date<tra.assign_date =true,ac.staff_id=tra.pre_teacher_id,ac.staff_id=cp.teacher_id) AND ac.PERIOD_ID=sp.PERIOD_ID WHERE acc.SYEAR=year AND acc.SCHOOL_ID=school AND (acc.MINUTES IS NOT NULL AND acc.MINUTES>0) AND acc.SCHOOL_DATE=sch_dt AND ac.STAFF_ID IS NULL  AND isDateInMarkingPeriodWorkingDates(cp.marking_period_id, acc.SCHOOL_DATE) GROUP BY s.TITLE,acc.SCHOOL_DATE,cp.TITLE,cp.COURSE_PERIOD_ID,cp.TEACHER_ID;
END$$

CREATE PROCEDURE `SEAT_COUNT`() 
BEGIN
UPDATE course_periods SET filled_seats=filled_seats-1 WHERE COURSE_PERIOD_ID IN (SELECT COURSE_PERIOD_ID FROM schedule WHERE end_date IS NOT NULL AND end_date < CURDATE() AND dropped='N');
UPDATE schedule SET dropped='Y' WHERE end_date IS NOT NULL AND end_date < CURDATE() AND dropped='N';
END$$

CREATE PROCEDURE `SEAT_FILL`() 
BEGIN
UPDATE course_periods SET filled_seats=filled_seats+1 WHERE COURSE_PERIOD_ID IN (SELECT COURSE_PERIOD_ID FROM schedule WHERE dropped='Y' AND ( end_date IS NULL OR end_date >= CURDATE()));
UPDATE schedule SET dropped='N' WHERE dropped='Y' AND ( end_date IS NULL OR end_date >= CURDATE()) ;
END$$

CREATE PROCEDURE `TEACHER_REASSIGNMENT`()
BEGIN
 UPDATE course_periods cp,teacher_reassignment tr,school_periods sp,marking_periods mp,staff st SET cp.title=CONCAT(sp.title,IF(cp.mp<>'FY',CONCAT(' - ',mp.short_name),''),IF(CHAR_LENGTH(cp.days)<5,CONCAT(' - ',cp.days),''),' - ',cp.short_name,' - ',CONCAT_WS(' ',st.first_name,st.middle_name,st.last_name)), cp.teacher_id=tr.teacher_id WHERE cp.period_id=sp.period_id and cp.marking_period_id=mp.marking_period_id and st.staff_id=tr.teacher_id and cp.course_period_id=tr.course_period_id AND assign_date <= CURDATE() AND updated='N';
 UPDATE teacher_reassignment SET updated='Y' WHERE assign_date <=CURDATE() AND updated='N';
 END$$

--
-- functions
--
CREATE FUNCTION `CALC_CUM_GPA_MP`(
mp_id int
) RETURNS int(11)
BEGIN

DECLARE req_mp INT DEFAULT 0;
DECLARE done INT DEFAULT 0;
DECLARE gp_points DECIMAL(10,2);
DECLARE student_id INT;
DECLARE gp_points_weighted DECIMAL(10,2);
DECLARE divisor DECIMAL(10,2);
DECLARE credit_earned DECIMAL(10,2);
DECLARE cgpa DECIMAL(10,2);

DECLARE cur1 CURSOR FOR
   SELECT srcg.student_id,
                  IF(ISNULL(sum(srcg.unweighted_gp)),  (SUM(srcg.weighted_gp*srcg.credit_earned)),
                      IF(ISNULL(sum(srcg.weighted_gp)), SUM(srcg.unweighted_gp*srcg.credit_earned),
                         ( SUM(srcg.unweighted_gp*srcg.credit_attempted)+ SUM(srcg.weighted_gp*srcg.credit_earned))
                        ))as gp_points,

                      SUM(srcg.weighted_gp*srcg.credit_earned) as gp_points_weighted,
                      SUM(srcg.credit_attempted) as divisor,
                      SUM(srcg.credit_earned) as credit_earned,
   		      IF(ISNULL(sum(srcg.unweighted_gp)),  (SUM(srcg.weighted_gp*srcg.credit_earned))/ sum(srcg.credit_attempted),
                          IF(ISNULL(sum(srcg.weighted_gp)), SUM(srcg.unweighted_gp*srcg.credit_earned)/sum(srcg.credit_attempted),
                             ( SUM(srcg.unweighted_gp*srcg.credit_attempted)+ SUM(srcg.weighted_gp*srcg.credit_earned))/sum(srcg.credit_attempted)
                            )
                         ) as cgpa

            FROM marking_periods mp,temp_cum_gpa srcg
            INNER JOIN schools sc ON sc.id=srcg.school_id
            WHERE srcg.marking_period_id= mp.marking_period_id AND srcg.gp_scale<>0 AND srcg.marking_period_id NOT LIKE 'E%'
            AND mp.marking_period_id IN (SELECT marking_period_id  FROM marking_periods WHERE mp_type=req_mp )
            GROUP BY srcg.student_id;
 DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;


  CREATE TEMPORARY TABLE tmp(
    student_id int,
    sum_weighted_factors decimal(10,6),
    count_weighted_factors int,
    sum_unweighted_factors decimal(10,6),
    count_unweighted_factors int,
    grade_level_short varchar(10)
  );

  INSERT INTO tmp(student_id,sum_weighted_factors,count_weighted_factors,
    sum_unweighted_factors, count_unweighted_factors,grade_level_short)
  SELECT
    srcg.student_id,
    SUM(srcg.weighted_gp/s.reporting_gp_scale) AS sum_weighted_factors,
    COUNT(*) AS count_weighted_factors,
    SUM(srcg.unweighted_gp/srcg.gp_scale) AS sum_unweighted_factors,
    COUNT(*) AS count_unweighted_factors,
    eg.short_name
  FROM student_report_card_grades srcg
  INNER JOIN schools s ON s.id=srcg.school_id
  LEFT JOIN enroll_grade eg on eg.student_id=srcg.student_id AND eg.syear=srcg.syear AND eg.school_id=srcg.school_id
  WHERE srcg.marking_period_id=mp_id AND srcg.gp_scale<>0 AND srcg.marking_period_id NOT LIKE 'E%'
  GROUP BY srcg.student_id,eg.short_name;

  INSERT INTO student_gpa_calculated (student_id,marking_period_id)
  SELECT
      t.student_id,
      mp_id
    FROM tmp t
    LEFT JOIN student_gpa_calculated sms ON sms.student_id=t.student_id AND sms.marking_period_id=mp_id
    WHERE sms.student_id IS NULL;

  UPDATE student_gpa_calculated g
    INNER JOIN (
	SELECT s.student_id,
		SUM(s.weighted_gp/sc.reporting_gp_scale)/COUNT(*) AS cum_weighted_factor,
		SUM(s.unweighted_gp/s.gp_scale)/COUNT(*) AS cum_unweighted_factor
	FROM student_report_card_grades s
	INNER JOIN schools sc ON sc.id=s.school_id
	LEFT JOIN course_periods p ON p.course_period_id=s.course_period_id
	WHERE p.marking_period_id IS NULL OR p.marking_period_id=s.marking_period_id
	GROUP BY student_id) gg ON gg.student_id=g.student_id
    SET g.cum_unweighted_factor=gg.cum_unweighted_factor;


    SELECT mp_type INTO @mp_type FROM marking_periods WHERE marking_period_id=mp_id;

 
    IF @mp_type = 'quarter'  THEN
           set req_mp = 'quarter';
    ELSEIF @mp_type = 'semester'  THEN
        IF EXISTS(SELECT student_id FROM student_report_card_grades srcg WHERE srcg.marking_period_id IN (SELECT marking_period_id  FROM marking_periods WHERE mp_type=@mp_type)) THEN
           set req_mp  = 'semester';
       ELSE
           set req_mp  = 'quarter';
        END IF;
   ELSEIF @mp_type = 'year'  THEN
           IF EXISTS(SELECT student_id FROM student_report_card_grades srcg WHERE srcg.MARKING_PERIOD_ID IN (SELECT marking_period_id  FROM marking_periods WHERE mp_type='semester')
                     UNION  SELECT student_id FROM student_report_card_grades srcg WHERE srcg.MARKING_PERIOD_ID IN (SELECT marking_period_id  FROM history_marking_periods WHERE mp_type='semester')
                     ) THEN
                 set req_mp  = 'semester';
         
          ELSE
                  set req_mp  = 'quarter ';
            END IF;
   END IF;



open cur1;
fetch cur1 into student_id, gp_points,gp_points_weighted,divisor,credit_earned,cgpa;

while not done DO
    IF EXISTS(SELECT student_id FROM student_gpa_calculated WHERE  student_gpa_calculated.student_id=student_id) THEN
    UPDATE student_gpa_calculated gc
               SET gc.cgpa=cgpa where gc.student_id=student_id and gc.marking_period_id=mp_id;
    ELSE
        INSERT INTO student_gpa_running(student_id,marking_period_id,mp,cgpa)
          VALUES(student_id,mp_id,mp_id,cgpa);
    END IF;
fetch cur1 into student_id, gp_points,gp_points_weighted,divisor,credit_earned,cgpa;
END WHILE;
CLOSE cur1;

RETURN 1;

END$$

CREATE FUNCTION `CALC_GPA_MP`(
 	s_id int,
 	mp_id int
 ) RETURNS int(11)
BEGIN
   SELECT
    SUM(srcg.weighted_gp/s.reporting_gp_scale) AS sum_weighted_factors, 
    COUNT(*) AS count_weighted_factors,                        
    SUM(srcg.unweighted_gp/srcg.gp_scale) AS sum_unweighted_factors, 
    COUNT(*) AS count_unweighted_factors,
   IF(ISNULL(sum(srcg.unweighted_gp)),  (SUM(srcg.weighted_gp*srcg.credit_earned))/ sum(srcg.credit_attempted),
                      IF(ISNULL(sum(srcg.weighted_gp)), SUM(srcg.unweighted_gp*srcg.credit_earned)/sum(srcg.credit_attempted),
                         ( SUM(srcg.unweighted_gp*srcg.credit_attempted)+ SUM(srcg.weighted_gp*srcg.credit_earned))/sum(srcg.credit_attempted)
                        )
      ),
    
    SUM(srcg.weighted_gp*srcg.credit_earned)/(select sum(sg.credit_attempted) from student_report_card_grades sg where sg.marking_period_id=mp_id AND sg.student_id=s_id
                                                  AND sg.weighted_gp  IS NOT NULL  AND sg.unweighted_gp IS NULL AND sg.course_period_id IS NOT NULL GROUP BY sg.student_id, sg.marking_period_id) ,
    SUM(srcg.unweighted_gp*srcg.credit_earned)/ (select sum(sg.credit_attempted) from student_report_card_grades sg where sg.marking_period_id=mp_id AND sg.student_id=s_id
                                                     AND sg.unweighted_gp  IS NOT NULL  AND sg.weighted_gp IS NULL AND sg.course_period_id IS NOT NULL GROUP BY sg.student_id, sg.marking_period_id) ,
    eg.short_name
  INTO
    @sum_weighted_factors,
    @count_weighted_factors,
    @sum_unweighted_factors,
    @count_unweighted_factors,
    @gpa,
    @weighted_gpa,
    @unweighted_gpa,
    @grade_level_short
  FROM student_report_card_grades srcg
  INNER JOIN schools s ON s.id=srcg.school_id
INNER JOIN course_periods cp ON cp.course_period_id=srcg.course_period_id
INNER JOIN report_card_grade_scales rcgs ON rcgs.id=cp.grade_scale_id
  LEFT JOIN enroll_grade eg on eg.student_id=srcg.student_id AND eg.syear=srcg.syear AND eg.school_id=srcg.school_id
  WHERE srcg.marking_period_id=mp_id AND srcg.student_id=s_id AND srcg.gp_scale<>0 AND srcg.course_period_id IS NOT NULL AND (rcgs.gpa_cal='Y' OR cp.grade_scale_id IS NULL) AND srcg.marking_period_id NOT LIKE 'E%'
  GROUP BY srcg.student_id,eg.short_name;

  IF NOT EXISTS(SELECT NULL FROM student_gpa_calculated WHERE marking_period_id=mp_id AND student_id=s_id) THEN
    INSERT INTO student_gpa_calculated(student_id,marking_period_id)
      VALUES(s_id,mp_id);
  END IF;

  UPDATE student_gpa_calculated g
    INNER JOIN (
	SELECT s.student_id,
		SUM(s.unweighted_gp/s.gp_scale)/COUNT(*) AS cum_unweighted_factor
	FROM student_report_card_grades s
	INNER JOIN schools sc ON sc.id=s.school_id
	LEFT JOIN course_periods p ON p.course_period_id=s.course_period_id
	WHERE s.course_period_id IS NOT NULL AND p.marking_period_id IS NULL OR p.marking_period_id=s.marking_period_id
	GROUP BY student_id) gg ON gg.student_id=g.student_id
    SET g.cum_unweighted_factor=gg.cum_unweighted_factor
    WHERE g.student_id=s_id;


IF EXISTS(SELECT student_id FROM student_gpa_calculated WHERE marking_period_id=mp_id AND student_id=s_id) THEN
    UPDATE student_gpa_calculated
    SET
      gpa            = @gpa,
      weighted_gpa   =@weighted_gpa,
      unweighted_gpa =@unweighted_gpa

    WHERE marking_period_id=mp_id AND student_id=s_id;
  ELSE
        INSERT INTO student_gpa_calculated(student_id,marking_period_id,mp,gpa,weighted_gpa,unweighted_gpa,grade_level_short)
            VALUES(s_id,mp_id,mp_id,@gpa,@weighted_gpa,@unweighted_gpa,@grade_level_short  );
                   

   END IF;

  RETURN 0;
 END$$

CREATE FUNCTION `CREDIT`(
 	cp_id int,
 	mp_id int
 ) RETURNS decimal(10,3)
BEGIN
  SELECT credits,marking_period_id,mp INTO @credits,@marking_period_id,@mp FROM course_periods WHERE course_period_id=cp_id;
  SELECT mp_type INTO @mp_type FROM marking_periods WHERE marking_period_id=mp_id;
 
  IF @marking_period_id=mp_id THEN
    RETURN @credits;
ELSEIF @mp = 'QTR' AND @mp_type = 'semester' THEN
     RETURN @credits;
   ELSEIF @mp='FY' AND @mp_type='semester' THEN
     SELECT COUNT(*) INTO @val FROM marking_periods WHERE parent_id=@marking_period_id GROUP BY parent_id;
   ELSEIF @mp = 'FY' AND @mp_type = 'quarter' THEN
     SELECT count(*) into @val FROM marking_periods WHERE grandparent_id=@marking_period_id GROUP BY grandparent_id;
   ELSEIF @mp = 'SEM' AND @mp_type = 'quarter' THEN
     SELECT count(*) into @val FROM marking_periods WHERE parent_id=@marking_period_id GROUP BY parent_id;
   ELSE
     RETURN 0;
   END IF;
   IF @val > 0 THEN
     RETURN @credits/@val;
   END IF;
   RETURN 0;
END$$

CREATE FUNCTION fn_marking_period_seq () RETURNS INT
BEGIN
  INSERT INTO marking_period_id_generator VALUES(NULL);
RETURN LAST_INSERT_ID();
END$$

CREATE FUNCTION `SET_CLASS_RANK_MP`(
	mp_id int
) RETURNS int(11)
BEGIN

DECLARE done INT DEFAULT 0;
DECLARE marking_period_id INT;
DECLARE student_id INT;
DECLARE rank NUMERIC;

declare cur1 cursor for
select
  mp.marking_period_id,
  sgc.student_id,
 (select count(*)+1 
   from student_gpa_calculated sgc3
   where sgc3.gpa > sgc.gpa
     and sgc3.marking_period_id = mp.marking_period_id 
     and sgc3.student_id in (select distinct sgc2.student_id 
                                                from student_gpa_calculated sgc2, student_enrollment se2
                                                where sgc2.student_id = se2.student_id 
                                                and sgc2.marking_period_id = mp.marking_period_id 
                                                and se2.grade_id = se.grade_id
                                                and se2.syear = se.syear
                                                group by gpa
                                )
  ) as rank
  from student_enrollment se, student_gpa_calculated sgc, marking_periods mp
  where se.student_id = sgc.student_id
    and sgc.marking_period_id = mp.marking_period_id
    and mp.marking_period_id = mp_id
    and se.syear = mp.syear
    and not sgc.gpa is null
  order by grade_id, rank;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

open cur1;
fetch cur1 into marking_period_id,student_id,rank;

while not done DO
	update student_gpa_calculated sgc
	  set
	    class_rank = rank
	where sgc.marking_period_id = marking_period_id
	  and sgc.student_id = student_id;
	fetch cur1 into marking_period_id,student_id,rank;
END WHILE;
CLOSE cur1;

RETURN 1;
END$$

CREATE FUNCTION `STUDENT_DISABLE`(
stu_id int
) RETURNS int(1)
BEGIN
UPDATE students set is_disable ='Y' where (select end_date from student_enrollment where  student_id=stu_id ORDER BY id DESC LIMIT 1) IS NOT NULL AND (select end_date from student_enrollment where  student_id=stu_id ORDER BY id DESC LIMIT 1)< CURDATE() AND  student_id=stu_id;
RETURN 1;
END$$

CREATE FUNCTION `RE_CALC_GPA_MP`(
	s_id int,
	mp_id int,
        sy int,
        sch_id int
) RETURNS int(11)
BEGIN
  SELECT
    SUM(srcg.weighted_gp/s.reporting_gp_scale) AS sum_weighted_factors, 
    COUNT(*) AS count_weighted_factors,                        
    SUM(srcg.unweighted_gp/srcg.gp_scale) AS sum_unweighted_factors, 
    COUNT(*) AS count_unweighted_factors,
   IF(ISNULL(sum(srcg.unweighted_gp)),  (SUM(srcg.weighted_gp*srcg.credit_earned))/ sum(srcg.credit_attempted),
                      IF(ISNULL(sum(srcg.weighted_gp)), SUM(srcg.unweighted_gp*srcg.credit_earned)/sum(srcg.credit_attempted),
                         ( SUM(srcg.unweighted_gp*srcg.credit_attempted)+ SUM(srcg.weighted_gp*srcg.credit_earned))/sum(srcg.credit_attempted)
                        )
      ),
    
    SUM(srcg.weighted_gp*srcg.credit_earned)/(select sum(sg.credit_attempted) from student_report_card_grades sg where sg.marking_period_id=mp_id AND sg.student_id=s_id
                                                  AND sg.weighted_gp  IS NOT NULL  AND sg.unweighted_gp IS NULL GROUP BY sg.student_id, sg.marking_period_id) ,
    SUM(srcg.unweighted_gp*srcg.credit_earned)/ (select sum(sg.credit_attempted) from student_report_card_grades sg where sg.marking_period_id=mp_id AND sg.student_id=s_id
                                                     AND sg.unweighted_gp  IS NOT NULL  AND sg.weighted_gp IS NULL GROUP BY sg.student_id, sg.marking_period_id) ,
    eg.short_name
  INTO
    @sum_weighted_factors,
    @count_weighted_factors,
    @sum_unweighted_factors,
    @count_unweighted_factors,
    @gpa,
    @weighted_gpa,
    @unweighted_gpa,
    @grade_level_short
  FROM student_report_card_grades srcg
  INNER JOIN schools s ON s.id=srcg.school_id
  LEFT JOIN enroll_grade eg on eg.student_id=srcg.student_id AND eg.syear=srcg.syear AND eg.school_id=srcg.school_id
  WHERE srcg.marking_period_id=mp_id AND srcg.student_id=s_id AND srcg.gp_scale<>0 AND srcg.school_id=sch_id AND srcg.syear=sy AND srcg.marking_period_id NOT LIKE 'E%'
  GROUP BY srcg.student_id,eg.short_name;

  

  IF NOT EXISTS(SELECT NULL FROM student_gpa_calculated WHERE marking_period_id=mp_id AND student_id=s_id) THEN
    INSERT INTO student_mp_stats(student_id,marking_period_id)
      VALUES(s_id,mp_id);
  END IF;

  UPDATE student_gpa_calculated g
    INNER JOIN (
	SELECT s.student_id,
		SUM(s.unweighted_gp/s.gp_scale)/COUNT(*) AS cum_unweighted_factor
	FROM student_report_card_grades s
	INNER JOIN schools sc ON sc.id=s.school_id
	LEFT JOIN course_periods p ON p.course_period_id=s.course_period_id
	WHERE p.marking_period_id IS NULL OR p.marking_period_id=s.marking_period_id
	GROUP BY student_id) gg ON gg.student_id=g.student_id
    SET g.cum_unweighted_factor=gg.cum_unweighted_factor
    WHERE g.student_id=s_id;

IF EXISTS(SELECT student_id FROM student_gpa_calculated WHERE marking_period_id=mp_id AND student_id=s_id) THEN
    UPDATE student_gpa_calculated
    SET
      gpa            = @gpa,
      weighted_gpa   =@weighted_gpa,
      unweighted_gpa =@unweighted_gpa

    WHERE marking_period_id=mp_id AND student_id=s_id;
  ELSE
        INSERT INTO student_gpa_calculated(student_id,marking_period_id,mp,gpa,weighted_gpa,unweighted_gpa,grade_level_short)
            VALUES(s_id,mp_id,mp_id,@gpa,@weighted_gpa,@unweighted_gpa,@grade_level_short  );
                   

   END IF;

  RETURN 0;
END$$
DELIMITER ;
-- --------------------------------------------------------\n
";
$f_content_n.= "--
-- Triggers `STUDENT_REPORT_CARD_GRADES`
--
DROP TRIGGER IF EXISTS `ti_student_report_card_grades`;
DELIMITER $$
CREATE TRIGGER `ti_student_report_card_grades`
    AFTER INSERT ON student_report_card_grades
    FOR EACH ROW
	SELECT CALC_GPA_MP(NEW.student_id, NEW.marking_period_id) INTO @return$$
DELIMITER ;

DROP TRIGGER IF EXISTS `tu_student_report_card_grades`;
DELIMITER $$
CREATE TRIGGER `tu_student_report_card_grades`
    AFTER UPDATE ON student_report_card_grades
    FOR EACH ROW
	SELECT CALC_GPA_MP(NEW.student_id, NEW.marking_period_id) INTO @return$$
DELIMITER ;

DROP TRIGGER IF EXISTS `td_student_report_card_grades`;
DELIMITER $$
CREATE TRIGGER `td_student_report_card_grades`
    AFTER DELETE ON student_report_card_grades
    FOR EACH ROW
	SELECT CALC_GPA_MP(OLD.student_id, OLD.marking_period_id) INTO @return$$
DELIMITER ;

DROP TRIGGER IF EXISTS `tu_cp_missing_attendance`;
DELIMITER $$
CREATE TRIGGER `tu_cp_missing_attendance`
    AFTER UPDATE ON course_periods
    FOR EACH ROW
    BEGIN
    IF OLD.does_attendance<>NEW.does_attendance THEN
	CALL ATTENDANCE_CALC(NEW.course_period_id, NEW.syear,NEW.school_id);
    END IF;
    END$$
DELIMITER ;
    
DROP TRIGGER IF EXISTS `td_cp_missing_attendance`;
CREATE TRIGGER `td_cp_missing_attendance`
    AFTER DELETE ON course_periods
    FOR EACH ROW
        DELETE FROM missing_attendance WHERE course_period_id=OLD.course_period_id;

DROP TRIGGER IF EXISTS `ti_cal_missing_attendance`;
DELIMITER $$
CREATE TRIGGER `ti_cal_missing_attendance`
    AFTER INSERT ON attendance_calendar
    FOR EACH ROW
    BEGIN
    DECLARE associations INT;
    SET associations = (SELECT COUNT(course_period_id) FROM `course_periods` WHERE calendar_id=NEW.calendar_id);
    IF associations>0 THEN
	CALL ATTENDANCE_CALC_BY_DATE(NEW.school_date, NEW.syear,NEW.school_id);
    END IF;
    END$$
DELIMITER ;

DROP TRIGGER IF EXISTS `td_cal_missing_attendance`;
CREATE TRIGGER `td_cal_missing_attendance`
    AFTER DELETE ON attendance_calendar
    FOR EACH ROW
	DELETE mi.* FROM missing_attendance mi,course_periods cp WHERE mi.course_period_id=cp.course_period_id and cp.calendar_id=OLD.calendar_id AND mi.SCHOOL_DATE=OLD.school_date;

-- --------------------------------------------------------";
        }
	else
	{
		$f_content_n.= "/* no tables in $mysql_database \n";
	}
	//mysql_free_result($result);
        return $f_content_n;
}

function _mysqldump_table_structure($table)
{
	$f_content_ns.= "--\n";
        $f_content_ns.= "-- Table structure for table `$table` \n";
        $f_content_ns.= "--\n\n";

       
	         $sql="show create table `$table`; ";
		$result=DBGet(DBQuery($sql));
		if( count($result)>0)
		{
			foreach($result as $row)
			{
				$f_content_ns.= $row['Create Table'].";\n\n";
			}
		}
		//mysql_free_result($result);
return $f_content_ns;
}

function _mysqldump_table_data($table)
{
	$sql='select * from `'.$table.'`;';
	$result=  DBQuery($sql);
	if( $result)
	{
            /*
		$num_rows= mysql_num_rows($result);
		$num_fields= mysql_num_fields($result);
                $numfields = mysql_num_fields($result);
            */    
                $num_rows= $result->num_rows;
		$num_fields= $result->field_count;
                $numfields = $result->field_count;
		if( $num_rows> 0)
		{
			

                        $f_content_nd.= "--\n";
                        $f_content_nd.= "-- Dumping data for table  `$table` \n";
                        $f_content_nd.= "--\n";

			$field_type=array();
			$i=0;
			while( $i <$num_fields)
			{
				//$meta= mysql_fetch_field($result, $i);
                                $meta= $result->fetch_field();
				array_push($field_type, $meta->type);
                                //$colfields[] = mysql_field_name($result,$i);
                                $colfields[] = $meta->name;
				$i++;
			}
			
			$f_content_nd.= 'insert into `'.$table.'` (';
                        for($j=0; $j < $num_fields; $j++)
                        {
                            if($j==$num_fields-1)
                            $f_content_nd.= $colfields[$j];
                        else
                        $f_content_nd.= $colfields[$j].',';
                        }
                        $f_content_nd.= ")values\n";
			$index=0;
			//while( $row= mysql_fetch_row($result))
                        while( $row= $result->fetch_row())
			{
				$f_content_nd.= '(';
				for( $i=0; $i <$num_fields; $i++)
				{
					if( is_null( $row[$i]))
						$f_content_nd.= 'null';
					else
					{
						switch( $field_type[$i])
						{
							case 'int':
								$f_content_nd.= $row[$i];
								break;
							case 'string':
							case 'blob' :
							default:
								$f_content_nd.= "'".mysqli_real_escape_string($row[$i])."'";
						}
					}
					if( $i <$num_fields-1)
						$f_content_nd.= ',';
				}
				$f_content_nd.= ')';
				if( $index <$num_rows-1)
					$f_content_nd.= ',';
				else
					$f_content_nd.= ";";
				$f_content_nd.= "\n";
				$index++;
			}
		}
	}
	//mysql_free_result($result);
	$f_content_nd.= "\n";
        return $f_content_nd;
}
function _mysql_test($mysql_host,$mysql_database, $mysql_username, $mysql_password)
{
	global $output_messages;
	$link = new mysqli($mysql_host, $mysql_username, $mysql_password,$mysql_database,$mysql_port);
	if (!$link)
	{
	   array_push($output_messages, ''._couldNotConnect.': ' . mysql_error());
	}
	else
	{
		array_push ($output_messages,""._connectedWithMySqlServer.":$mysql_username@$mysql_host successfully");
		//$db_selected = mysql_select_db($mysql_database, $link);
                $db_selected = new mysqli($mysql_host, $mysql_username, $mysql_password,$mysql_database,$mysql_port);
		if (!$db_selected)
		{
			array_push ($output_messages,''._canTUse.' $mysql_database : ' . mysql_error());
		}
		else
			array_push ($output_messages,""._connectedWithMySqlDatabase.":$mysql_database successfully");
	}
}
?>
