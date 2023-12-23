<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>openSIS Installer</title>
    <link href="../assets/css/icons/fontawesome/styles.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/installer.css?v=<?php echo rand(000, 999); ?>" type="text/css" />
</head>

<body class="outer-body">
    <section class="login">
        <div class="login-wrapper">
            <div class="panel">
                <div class="panel-heading clearfix">
                    <div class="logo">
                        <img src="assets/images/opensis_logo.png" alt="openSIS">
                    </div>
                    <h3>openSIS Installation</h3>
                </div>
                <div class="panel-body">
                    <div class="installation-steps-wrapper">
                        <div class="installation-steps text-center">
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
                            session_start();
                            ini_set('max_execution_time', '50000');
                            ini_set('max_input_time', '50000');
                            require_once("../functions/PragRepFnc.php");
                            include("CustomClassFnc.php");
                            
                            $mysql_database = $_SESSION['db'];
                            $dbUser = $_SESSION['username'];
                            $dbPass = $_SESSION['password'];
                            $dbconn = new mysqli($_SESSION['server'], $_SESSION['username'], $_SESSION['password'], $mysql_database, $_SESSION['port']);

                            if ($dbconn->connect_errno != 0)
                                exit($dbconn->error);
                            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                $result = $dbconn->query("SHOW VARIABLES LIKE 'basedir'");
                                $row = $result->fetch_assoc();
                                $mysql_dir1 = substr($row['Value'], 0, 2);
                                $mysql_dir = str_replace('\\', '\\\\', $mysql_dir1 . $_SERVER['MYSQL_HOME']);
                            }

                            $q2r = $dbconn->query("SELECT name,value FROM app where name='version'") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' error at 2</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                            $q2 = $q2r->fetch_assoc();
                            $v = $q2['value'];
                            $opensis_tab = array();
                           
                            if ($v == '5.0') {
                                $opensis_tab = array(
                                    'address', 'address_fields', 'address_field_categories', 'app', 'attendance_calendar', 'attendance_calendars', 'attendance_codes', 'attendance_code_categories', 'attendance_completed', 'attendance_day', 'attendance_period',
                                    'calendar_events', 'calendar_events_visibility', 'config', 'courses', 'course_periods', 'course_subjects', 'custom_fields', 'eligibility', 'eligibility_activities', 'eligibility_completed', 'goal', 'gradebook_assignments', 'gradebook_assignment_types', 'gradebook_grades', 'grades_completed', 'hacking_log', 'history_marking_periods', 'honor_roll', 'login_message', 'login_records', 'log_maintain',
                                    'lunch_period', 'marking_period_id_generator', 'missing_attendance', 'old_course_weights', 'people', 'people_fields', 'people_field_categories', 'people_join_contacts', 'portal_notes', 'profile_exceptions',
                                    'program_config', 'program_user_config', 'progress', 'report_card_comments', 'report_card_grades', 'report_card_grade_scales', 'schedule', 'schedule_requests', 'schools', 'school_gradelevels', 'school_periods',
                                    'school_progress_periods', 'school_quarters', 'school_semesters', 'school_years', 'staff', 'staff_exceptions', 'staff_fields', 'staff_field_categories', 'staff_school_relationship', 'students', 'students_join_address',
                                    'students_join_people', 'students_join_users', 'student_contacts', 'student_eligibility_activities', 'student_enrollment', 'student_enrollment_codes', 'student_field_categories', 'student_gpa_calculated', 'student_gpa_running', 'student_medical', 'student_medical_alerts',
                                    'student_medical_notes', 'student_medical_visits', 'student_mp_comments', 'student_mp_stats', 'student_report_card_comments', 'student_report_card_grades', 'system_preference', 'system_preference_misc', 'teacher_reassignment', 'user_profiles'
                                );
                            } else if ($v == '5.1') {
                                $opensis_tab = array(
                                    'address', 'address_fields', 'address_field_categories', 'app', 'attendance_calendar', 'attendance_calendars', 'attendance_codes', 'attendance_code_categories', 'attendance_completed', 'attendance_day', 'attendance_period',
                                    'calendar_events', 'calendar_events_visibility', 'config', 'courses', 'course_periods', 'course_subjects', 'custom_fields', 'eligibility', 'eligibility_activities', 'eligibility_completed', 'goal', 'gradebook_assignments', 'gradebook_assignment_types', 'gradebook_grades', 'grades_completed', 'hacking_log', 'history_marking_periods', 'honor_roll', 'login_message', 'login_records', 'log_maintain',
                                    'lunch_period', 'marking_period_id_generator', 'missing_attendance', 'old_course_weights', 'people', 'people_fields', 'people_field_categories', 'people_join_contacts', 'portal_notes', 'profile_exceptions',
                                    'program_config', 'program_user_config', 'progress', 'report_card_comments', 'report_card_grades', 'report_card_grade_scales', 'schedule', 'schedule_requests', 'schools', 'school_gradelevels', 'school_periods',
                                    'school_progress_periods', 'school_quarters', 'school_semesters', 'school_years', 'staff', 'staff_exceptions', 'staff_fields', 'staff_field_categories', 'staff_school_relationship', 'students', 'students_join_address',
                                    'students_join_people', 'students_join_users', 'student_contacts', 'student_eligibility_activities', 'student_enrollment', 'student_enrollment_codes', 'student_field_categories', 'student_gpa_calculated', 'student_gpa_running', 'student_medical', 'student_medical_alerts',
                                    'student_medical_notes', 'student_medical_visits', 'student_mp_comments', 'student_mp_stats', 'student_report_card_comments', 'student_report_card_grades', 'system_preference', 'system_preference_misc', 'teacher_reassignment', 'user_profiles'
                                );
                            } else if ($v == '5.2') {
                                $opensis_tab = array(
                                    'address', 'address_fields', 'address_field_categories', 'app', 'attendance_calendar', 'attendance_calendars', 'attendance_codes', 'attendance_code_categories', 'attendance_completed', 'attendance_day', 'attendance_period',
                                    'calendar_events', 'calendar_events_visibility', 'config', 'courses', 'course_periods', 'course_subjects', 'custom_fields', 'eligibility', 'eligibility_activities', 'eligibility_completed', 'goal', 'gradebook_assignments', 'gradebook_assignment_types', 'gradebook_grades', 'grades_completed', 'hacking_log', 'history_marking_periods', 'history_school', 'honor_roll', 'login_message', 'login_records', 'log_maintain',
                                    'lunch_period', 'marking_period_id_generator', 'missing_attendance', 'old_course_weights', 'people', 'people_fields', 'people_field_categories', 'people_join_contacts', 'portal_notes', 'profile_exceptions',
                                    'program_config', 'program_user_config', 'progress', 'report_card_comments', 'report_card_grades', 'report_card_grade_scales', 'schedule', 'schedule_requests', 'schools', 'school_gradelevels', 'school_periods',
                                    'school_progress_periods', 'school_quarters', 'school_semesters', 'school_years', 'staff', 'staff_exceptions', 'staff_fields', 'staff_field_categories', 'staff_school_relationship', 'students', 'students_join_address',
                                    'students_join_people', 'students_join_users', 'student_contacts', 'student_eligibility_activities', 'student_enrollment', 'student_enrollment_codes', 'student_field_categories', 'student_gpa_calculated', 'student_gpa_running', 'student_medical', 'student_medical_alerts',
                                    'student_medical_notes', 'student_medical_visits', 'student_mp_comments', 'student_mp_stats', 'student_report_card_comments', 'student_report_card_grades', 'system_preference', 'system_preference_misc', 'teacher_reassignment', 'user_profiles'
                                );
                            } else if ($v == '5.3') {
                                $opensis_tab = array(
                                    'address', 'address_fields', 'address_field_categories', 'app', 'attendance_calendar', 'attendance_calendars', 'attendance_codes', 'attendance_code_categories', 'attendance_completed', 'attendance_day', 'attendance_period',
                                    'calendar_events', 'calendar_events_visibility', 'config', 'courses', 'course_periods', 'course_subjects', 'custom_fields', 'eligibility', 'eligibility_activities', 'eligibility_completed', 'goal', 'gradebook_assignments', 'gradebook_assignment_types', 'gradebook_grades', 'grades_completed', 'hacking_log', 'history_marking_periods', 'history_school', 'honor_roll', 'login_message', 'login_records', 'log_maintain',
                                    'lunch_period', 'marking_period_id_generator', 'missing_attendance', 'old_course_weights', 'people', 'people_fields', 'people_field_categories', 'people_join_contacts', 'portal_notes', 'profile_exceptions',
                                    'program_config', 'program_user_config', 'progress', 'report_card_comments', 'report_card_grades', 'report_card_grade_scales', 'schedule', 'schedule_requests', 'schools', 'school_gradelevels', 'school_periods',
                                    'school_progress_periods', 'school_quarters', 'school_semesters', 'school_years', 'staff', 'staff_exceptions', 'staff_fields', 'staff_field_categories', 'staff_school_relationship', 'students', 'students_join_address',
                                    'students_join_people', 'students_join_users', 'student_contacts', 'student_eligibility_activities', 'student_enrollment', 'student_enrollment_codes', 'student_field_categories', 'student_gpa_calculated', 'student_gpa_running', 'student_medical', 'student_medical_alerts',
                                    'student_medical_notes', 'student_medical_visits', 'student_mp_comments', 'student_mp_stats', 'student_report_card_comments', 'student_report_card_grades', 'system_preference', 'system_preference_misc', 'teacher_reassignment', 'user_profiles'
                                );
                            } else if ($v == '5.4') {
                                $opensis_tab = array(
                                    'address', 'address_fields', 'address_field_categories', 'app', 'attendance_calendar', 'attendance_calendars', 'attendance_codes', 'attendance_code_categories', 'attendance_completed', 'attendance_day', 'attendance_period',
                                    'calendar_events', 'calendar_events_visibility', 'config', 'courses', 'course_periods', 'course_subjects', 'custom_fields', 'eligibility', 'eligibility_activities', 'eligibility_completed', 'goal', 'gradebook_assignments', 'gradebook_assignment_types', 'gradebook_grades', 'grades_completed', 'hacking_log', 'history_marking_periods', 'history_school', 'honor_roll', 'login_message', 'login_records', 'log_maintain',
                                    'lunch_period', 'marking_period_id_generator', 'missing_attendance', 'old_course_weights', 'people', 'people_fields', 'people_field_categories', 'people_join_contacts', 'portal_notes', 'profile_exceptions',
                                    'program_config', 'program_user_config', 'progress', 'report_card_comments', 'report_card_grades', 'report_card_grade_scales', 'schedule', 'schedule_requests', 'schools', 'school_gradelevels', 'school_periods',
                                    'school_progress_periods', 'school_quarters', 'school_semesters', 'school_years', 'staff', 'staff_exceptions', 'staff_fields', 'staff_field_categories', 'staff_school_relationship', 'students', 'students_join_address',
                                    'students_join_people', 'students_join_users', 'student_contacts', 'student_eligibility_activities', 'student_enrollment', 'student_enrollment_codes', 'student_field_categories', 'student_gpa_calculated', 'student_gpa_running', 'student_medical', 'student_medical_alerts',
                                    'student_medical_notes', 'student_medical_visits', 'student_mp_comments', 'student_mp_stats', 'student_report_card_comments', 'student_report_card_grades', 'system_preference', 'system_preference_misc', 'teacher_reassignment', 'user_profiles'
                                );
                            } else if ($v == '6.0') {
                                $dbconn->query('DELETE FROM custom_fields WHERE system_field=\'Y\' ') or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' error at 3</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                $dbconn->query('TRUNCATE app') or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' error at 4</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                $app_insert = "INSERT INTO `app` (`name`, `value`) VALUES
                                        ('version', '6.4'),
                                        ('date', 'July 26, 2017'),
                                        ('build', '20170726001'),
                                        ('update', '0'),
                                        ('last_updated', 'July 26, 2017');";
                                $dbconn->query($app_insert) or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . 'error at 96</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');

                                $get_schools = $dbconn->query('SELECT DISTINCT id FROM schools');
                                while ($get_schools_a = $get_schools->fetch_assoc()) {
                                    $get_sy = $dbconn->query('SELECT MAX(syear) as syear FROM school_years WHERE SCHOOL_ID=' . $get_schools_a['id']);
                                    $get_sy_a = $get_sy->fetch_assoc();
                                    $get_sy_a = $get_sy_a['syear'];
                                    $get_schools_a = $get_schools_a['id'];
                                    $dbconn->query('INSERT INTO program_config (SYEAR,SCHOOL_ID,PROGRAM,TITLE,VALUE) VALUES(\'' . $get_sy_a . '\',\'' . $get_schools_a . '\',\'UPDATENOTIFY\',\'display\',\'Y\')') or die($dbconn->error);
                                    $dbconn->query('INSERT INTO program_config (SYEAR,SCHOOL_ID,PROGRAM,TITLE,VALUE) VALUES(\'' . $get_sy_a . '\',\'' . $get_schools_a . '\',\'UPDATENOTIFY\',\'display_school\',\'Y\')') or die($dbconn->error);
                                }

                                $get_pf = $dbconn->query('SELECT COUNT(*) as rec_ex FROM profile_exceptions WHERE modname=\'students/Student.php&category_id=4\' AND can_edit=\'Y\' ');
                                $get_pf_a = $get_pf->fetch_assoc();
                                $get_pf_a = $get_pf_a['rec_ex'];
                                if ($get_pf_a > 0) {
                                    $dbconn->query('UPDATE profile_exceptions SET can_edit=\'Y\' WHERE modname=\'students/Student.php&category_id=4\'');
                                }
                                unset($get_pf_a);


                                $get_pf = $dbconn->query('SELECT COUNT(*) as rec_ex FROM profile_exceptions WHERE modname=\'students/Student.php&category_id=5\' AND can_edit=\'Y\' ');
                                $get_pf_a = $get_pf->fetch_assoc();
                                $get_pf_a = $get_pf_a['rec_ex'];
                                if ($get_pf_a > 0) {
                                    $dbconn->query('UPDATE profile_exceptions SET can_edit=\'Y\' WHERE modname=\'students/Student.php&category_id=5\'');
                                }
                                unset($get_pf_a);

                                $get_pf = $dbconn->query('SELECT COUNT(*) as rec_ex FROM profile_exceptions WHERE modname=\'students/Student.php&category_id=6\' AND can_edit=\'Y\' ');
                                $get_pf_a = $get_pf->fetch_assoc();
                                $get_pf_a = $get_pf_a['rec_ex'];
                                if ($get_pf_a > 0) {
                                    $dbconn->query('UPDATE profile_exceptions SET can_edit=\'Y\' WHERE modname=\'students/Student.php&category_id=6\'');
                                }
                                unset($get_pf_a);
                                $qr_tab = $dbconn->query("show full tables where Table_Type != 'VIEW'") or die($dbconn->error);


                                while ($fetch = $qr_tab->fetch_assoc()) {


                                    $tab1 = $fetch[0];

                                    $dbconn->query("ALTER TABLE $tab1 ENGINE=InnoDB");
                                }

                                header('Location: Step5.php');
                                exit;
                            } else if ($v == '6.1' || $v == '6.0' || $v == '6.2') {
                                $qr_tab = $dbconn->query("show full tables where Table_Type != 'VIEW'") or die($dbconn->error);


                                while ($fetch = $qr_tab->fetch_assoc()) {


                                    $tab1 = $fetch[0];

                                    $dbconn->query("ALTER TABLE $tab1 ENGINE=InnoDB");
                                }
                                $dbconn->query('TRUNCATE app');
                                $app_insert = "INSERT INTO `app` (`name`, `value`) VALUES
                                        ('version', '6.4'),
                                        ('date', 'July 26, 2017'),
                                        ('build', '20170726001'),
                                        ('update', '0'),
                                        ('last_updated', 'July 26, 2017');";
                                $dbconn->query($app_insert);
                                header('Location: Step5.php');
                                exit;
                            } else if ($v == '6.3' || $v == '6.4' || $v == '6.5' || $v == '7.0') {
                                $dbconn->query('TRUNCATE app');
                                $app_insert = "INSERT INTO `app` (`name`, `value`) VALUES
                                        ('version', '9.1'),
                                        ('date', 'December 30, 2023'),
                                        ('build', '20221230001'),
                                        ('update', '0'),
                                        ('last_updated', 'December 30, 2023');";
                                $dbconn->query($app_insert);

                                $dbconn->query('ALTER TABLE `staff` ADD `img_name` VARCHAR(255) NULL AFTER `disability_desc`');

                                $dbconn->query('ALTER TABLE `staff` ADD `img_content` LONGBLOB NULL AFTER `img_name`');

                                $dbconn->query('CREATE TABLE IF NOT EXISTS `user_file_upload` (
                                       `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                      `user_id` int(11) NOT NULL,
                                      `profile_id` int(11) NOT NULL,
                                      `school_id` int(11) NOT NULL,
                                      `syear` int(11) NOT NULL,
                                      `download_id` varchar(50) NOT NULL,
                                      `name` varchar(255) NOT NULL,
                                      `size` int(11) NOT NULL,
                                      `type` varchar(255) NOT NULL,
                                      `content` longblob NOT NULL,
                                      `file_info` varchar(255) NOT NULL
                                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1');

                                $dbconn->query('CREATE TABLE IF NOT EXISTS `temp_message_filepath_ws` (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `keyval` varchar(100) NOT NULL,
                                      `filepath` varchar(255) NOT NULL,
                                      PRIMARY KEY (`id`)
                                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1');

                                $dbconn->query('CREATE TABLE IF NOT EXISTS `device_info` (
                                      `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                      `user_id` int(11) NOT NULL,
                                      `profile_id` int(11) NOT NULL,
                                      `device_type` varchar(255) CHARACTER SET utf8 NOT NULL,
                                      `device_token` longtext CHARACTER SET utf8 NOT NULL,
                                      `device_id` longtext CHARACTER SET utf8 NOT NULL
                                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1');

                                $dbconn->query('CREATE TABLE IF NOT EXISTS `filters` (
                                      `filter_id` int(11) NOT NULL,
                                      `filter_name` varchar(255) DEFAULT NULL,
                                      `school_id` int(11) DEFAULT \'0\',
                                      `show_to` int(11) NOT NULL DEFAULT \'0\'
                                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1');

                                $dbconn->query('CREATE TABLE IF NOT EXISTS `filter_fields` (
                                      `filter_field_id` int(11) NOT NULL,
                                      `filter_id` int(11) DEFAULT NULL,
                                      `filter_column` varchar(255) DEFAULT NULL,
                                      `filter_value` longtext
                                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1');

                                $dbconn->query('CREATE TABLE IF NOT EXISTS `api_info` (
                                     `id` int(11) NOT NULL AUTO_INCREMENT,
                                     `api_key` varchar(255) CHARACTER SET utf8 NOT NULL,
                                     `api_secret` varchar(255) CHARACTER SET utf8 NOT NULL,
                                     PRIMARY KEY (`id`)
                                    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;');

                                $stu_info = $dbconn->query('SELECT * FROM students WHERE language !=\'\'') or die($dbconn->error);
                                $extra_tab = array();
                                //$fetch1 = $stu_info->fetch_assoc();

                                while ($fetch = $stu_info->fetch_assoc()) {
                                    $stu_lang = $dbconn->query('SELECT * FROM language WHERE UPPER(language_name)=UPPER(\'' . $fetch['language'] . '\')') or die($dbconn->error);
                                    $fetchlang = $stu_lang->fetch_assoc();
                                    if (count($fetchlang) > 0)
                                        $dbconn->query('UPDATE students SET language=\'' . $fetchlang['language_id'] . '\' WHERE student_id=' . $fetch['student_id']) or die($dbconn->error);
                                    else {
                                        $dbconn->query('INSERT INTO language (language_name) VALUES (\'' . $fetch['language'] . '\')') or die($dbconn->error);
                                        $stu_lang = $dbconn->query('SELECT * FROM language WHERE UPPER(language_name)=UPPER(\'' . $fetch['language'] . '\')') or die($dbconn->error);
                                        $fetchlang = $stu_lang->fetch_assoc();
                                        if (count($fetchlang) > 0)
                                            $dbconn->query('UPDATE students SET language=\'' . $fetchlang['language_id'] . '\' WHERE student_id=' . $fetch['student_id']) or die($dbconn->error);
                                    }
                                }


                                $stu_ethn_info = $dbconn->query('SELECT * FROM students WHERE ethnicity !=\'\'') or die($dbconn->error);

                                while ($ethn_fetch = $stu_ethn_info->fetch_assoc()) {
                                    $stu_ethn = $dbconn->query('SELECT * FROM ethnicity WHERE UPPER(ethnicity_name)=UPPER(\'' . $ethn_fetch['ethnicity'] . '\')') or die($dbconn->error);

                                    $fetchethn = $stu_ethn->fetch_assoc();

                                    if (count($fetchethn) > 0) {
                                        $dbconn->query('UPDATE students SET ethnicity=\'' . $fetchethn['ethnicity_id'] . '\' WHERE student_id=' . $ethn_fetch['student_id']) or die($dbconn->error);
                                    } else {
                                        $dbconn->query('INSERT INTO ethnicity (ethnicity_name) VALUES (\'' . $ethn_fetch['ethnicity'] . '\')') or die($dbconn->error);

                                        $stu_ethn = $dbconn->query('SELECT * FROM ethnicity WHERE UPPER(ethnicity_name)=UPPER(\'' . $ethn_fetch['ethnicity'] . '\')') or die($dbconn->error);

                                        $fetchethn = $stu_ethn->fetch_assoc();

                                        if (count($fetchethn) > 0) {
                                            $dbconn->query('UPDATE students SET ethnicity=\'' . $fetchethn['ethnicity_id'] . '\' WHERE student_id=' . $ethn_fetch['student_id']) or die($dbconn->error);
                                        }
                                    }
                                }


                                $dbconn->query('ALTER TABLE `filters` ADD PRIMARY KEY (`filter_id`)');
                                $dbconn->query('ALTER TABLE `filter_fields` ADD PRIMARY KEY (`filter_field_id`)');
                                $dbconn->query('ALTER TABLE `filters` MODIFY `filter_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;');
                                $dbconn->query('ALTER TABLE `filter_fields` MODIFY `filter_field_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
                                $dbconn->query('ALTER TABLE `students` CHANGE `language` `language_id` INT(8) NULL DEFAULT NULL');
                                $dbconn->query('ALTER TABLE `students` CHANGE `ethnicity` `ethnicity_id` INT(11) NULL DEFAULT NULL');
                                $dbconn->query('ALTER TABLE `mail_group` ADD `school_id` int(11) NOT NULL');
                                $dbconn->query('ALTER TABLE `mail_groupmembers` ADD `school_id` int(11) NOT NULL');
                                $dbconn->query('ALTER TABLE `mail_group` ADD INDEX `mail_group_ind` (`school_id`) USING BTREE');
                                $dbconn->query('ALTER TABLE `mail_groupmembers` ADD INDEX `mail_groupmembers_ind` (`school_id`) USING BTREE');


                                ### for Functions/Procedures/Triggers/Events - Start ###

                                $this_db = $_SESSION['db'];

                                $SQL_Procs = "OpensisProcsMysqlInc.sql";
                                $SQL_Trigger = "OpensisTriggerMysqlInc.sql";

                                executeSQL($SQL_Procs, $this_db);
                                executeSQL($SQL_Trigger, $this_db);

                                ### for Functions/Procedures/Triggers/Events - End ###


                                ### for Keys - Start ###

                                $dbconn->query('ALTER TABLE `missing_attendance` DROP KEY IF EXISTS `idx_appstart_check`');
                                $dbconn->query('ALTER TABLE `missing_attendance` ADD KEY `idx_appstart_check` (`course_period_id`,`period_id`,`syear`,`school_id`,`school_date`)');

                                $dbconn->query('ALTER TABLE `missing_attendance` DROP KEY IF EXISTS `idx_missing_attendance_syear`');
                                $dbconn->query('ALTER TABLE `missing_attendance` ADD KEY `idx_missing_attendance_syear` (`syear`)');

                                $dbconn->query('ALTER TABLE `login_authentication` DROP KEY IF EXISTS `idx_login_authentication_username_password`');
                                $dbconn->query('ALTER TABLE `login_authentication` ADD KEY `idx_login_authentication_username_password` (`username`,`password`)');

                                $dbconn->query('ALTER TABLE `students` DROP INDEX IF EXISTS `idx_student_search`');
                                $dbconn->query('ALTER TABLE students ADD INDEX IF NOT EXISTS `idx_students_search` (`is_disable`) COMMENT \'Student Info -> search all\'');

                                $dbconn->query('ALTER TABLE student_enrollment ADD INDEX IF NOT EXISTS `idx_student_search` (`school_id`,`syear`,`start_date`,`end_date`,`drop_code`) COMMENT \'Student Info -> search all\'');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `student_report_card_grades_ind5`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `student_report_card_grades_ind5` (`report_card_grade_id`)');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `student_report_card_grades_ind6`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `student_report_card_grades_ind6` (`report_card_comment_id`)');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `idx_srcg_comb1`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `idx_srcg_comb1` (`student_id`,`course_period_id`,`marking_period_id`)');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `idx_srcg_comb2`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `idx_srcg_comb2` (`course_period_id`,`marking_period_id`)');

                                ### for Keys - End ###


                                $_SESSION['mod'] = 'upgrade';
                                header('Location: Step5.php');
                                exit;
                            } else if ($v == '7.1') {

                                $dbconn->query('TRUNCATE app');
                                $app_insert = "INSERT INTO `app` (`name`, `value`) VALUES
                                        ('version', '9.1'),
                                        ('date', 'December 30, 2023'),
                                        ('build', '20221230001'),
                                        ('update', '0'),
                                        ('last_updated', 'December 30, 2023');";
                                $dbconn->query($app_insert);

                                $dbconn->query('CREATE TABLE `api_info` (
                                     `id` int(11) NOT NULL AUTO_INCREMENT,
                                     `api_key` varchar(255) CHARACTER SET utf8 NOT NULL,
                                     `api_secret` varchar(255) CHARACTER SET utf8 NOT NULL,
                                     PRIMARY KEY (`id`)
                                    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;');

                                $dbconn->query('CREATE TABLE IF NOT EXISTS `user_file_upload` (
                                    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                `user_id` int(11) NOT NULL,
                                `profile_id` int(11) NOT NULL,
                                `school_id` int(11) NOT NULL,
                                `syear` int(11) NOT NULL,
                                `download_id` varchar(50) NOT NULL,
                                `name` varchar(255) NOT NULL,
                                `size` int(11) NOT NULL,
                                `type` varchar(255) NOT NULL,
                                `content` longblob NOT NULL,
                                `file_info` varchar(255) NOT NULL
                                ) ENGINE=InnoDB DEFAULT CHARSET=latin1');

                                $stu_info = $dbconn->query('SELECT * FROM students WHERE language !=\'\'') or die($dbconn->error);
                                $extra_tab = array();
                                // $fetch1 = $stu_info->fetch_assoc();

                                while ($fetch = $stu_info->fetch_assoc()) {
                                    $stu_lang = $dbconn->query('SELECT * FROM language WHERE UPPER(language_name)=UPPER(\'' . $fetch['language'] . '\')') or die($dbconn->error);
                                    $fetchlang = $stu_lang->fetch_assoc();
                                    if (count($fetchlang) > 0)
                                        $dbconn->query('UPDATE students SET language=\'' . $fetchlang['language_id'] . '\' WHERE student_id=' . $fetch['student_id']) or die($dbconn->error);
                                    else {
                                        $dbconn->query('INSERT INTO language (language_name) VALUES (\'' . $fetch['language'] . '\')') or die($dbconn->error);
                                        $stu_lang = $dbconn->query('SELECT * FROM language WHERE UPPER(language_name)=UPPER(\'' . $fetch['language'] . '\')') or die($dbconn->error);
                                        $fetchlang = $stu_lang->fetch_assoc();
                                        if (count($fetchlang) > 0)
                                            $dbconn->query('UPDATE students SET language=\'' . $fetchlang['language_id'] . '\' WHERE student_id=' . $fetch['student_id']) or die($dbconn->error);
                                    }
                                }


                                $stu_ethn_info = $dbconn->query('SELECT * FROM students WHERE ethnicity !=\'\'') or die($dbconn->error);

                                while ($ethn_fetch = $stu_ethn_info->fetch_assoc()) {
                                    $stu_ethn = $dbconn->query('SELECT * FROM ethnicity WHERE UPPER(ethnicity_name)=UPPER(\'' . $ethn_fetch['ethnicity'] . '\')') or die($dbconn->error);

                                    $fetchethn = $stu_ethn->fetch_assoc();

                                    if (count($fetchethn) > 0) {
                                        $dbconn->query('UPDATE students SET ethnicity=\'' . $fetchethn['ethnicity_id'] . '\' WHERE student_id=' . $ethn_fetch['student_id']) or die($dbconn->error);
                                    } else {
                                        $dbconn->query('INSERT INTO ethnicity (ethnicity_name) VALUES (\'' . $ethn_fetch['ethnicity'] . '\')') or die($dbconn->error);

                                        $stu_ethn = $dbconn->query('SELECT * FROM ethnicity WHERE UPPER(ethnicity_name)=UPPER(\'' . $ethn_fetch['ethnicity'] . '\')') or die($dbconn->error);

                                        $fetchethn = $stu_ethn->fetch_assoc();

                                        if (count($fetchethn) > 0) {
                                            $dbconn->query('UPDATE students SET ethnicity=\'' . $fetchethn['ethnicity_id'] . '\' WHERE student_id=' . $ethn_fetch['student_id']) or die($dbconn->error);
                                        }
                                    }
                                }


                                $dbconn->query('ALTER TABLE `students` CHANGE `language` `language_id` INT(8) NULL DEFAULT NULL');
                                $dbconn->query('ALTER TABLE `students` CHANGE `ethnicity` `ethnicity_id` INT(11) NULL DEFAULT NULL');
                                $dbconn->query('ALTER TABLE `mail_group` ADD `school_id` int(11) NOT NULL');
                                $dbconn->query('ALTER TABLE `mail_groupmembers` ADD `school_id` int(11) NOT NULL');
                                $dbconn->query('ALTER TABLE `mail_group` ADD INDEX `mail_group_ind` (`school_id`) USING BTREE');
                                $dbconn->query('ALTER TABLE `mail_groupmembers` ADD INDEX `mail_groupmembers_ind` (`school_id`) USING BTREE');

                                ### for Functions/Procedures/Triggers/Events - Start ###

                                $this_db = $_SESSION['db'];

                                $SQL_Procs = "OpensisProcsMysqlInc.sql";
                                $SQL_Trigger = "OpensisTriggerMysqlInc.sql";

                                executeSQL($SQL_Procs, $this_db);
                                executeSQL($SQL_Trigger, $this_db);

                                ### for Functions/Procedures/Triggers/Events - End ###

                                ### for Keys - Start ###

                                $dbconn->query('ALTER TABLE `missing_attendance` DROP KEY IF EXISTS `idx_appstart_check`');
                                $dbconn->query('ALTER TABLE `missing_attendance` ADD KEY `idx_appstart_check` (`course_period_id`,`period_id`,`syear`,`school_id`,`school_date`)');

                                $dbconn->query('ALTER TABLE `missing_attendance` DROP KEY IF EXISTS `idx_missing_attendance_syear`');
                                $dbconn->query('ALTER TABLE `missing_attendance` ADD KEY `idx_missing_attendance_syear` (`syear`)');

                                $dbconn->query('ALTER TABLE `login_authentication` DROP KEY IF EXISTS `idx_login_authentication_username_password`');
                                $dbconn->query('ALTER TABLE `login_authentication` ADD KEY `idx_login_authentication_username_password` (`username`,`password`)');

                                $dbconn->query('ALTER TABLE `students` DROP INDEX IF EXISTS `idx_student_search`');
                                $dbconn->query('ALTER TABLE students ADD INDEX IF NOT EXISTS `idx_students_search` (`is_disable`) COMMENT \'Student Info -> search all\'');

                                $dbconn->query('ALTER TABLE student_enrollment ADD INDEX IF NOT EXISTS `idx_student_search` (`school_id`,`syear`,`start_date`,`end_date`,`drop_code`) COMMENT \'Student Info -> search all\'');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `student_report_card_grades_ind5`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `student_report_card_grades_ind5` (`report_card_grade_id`)');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `student_report_card_grades_ind6`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `student_report_card_grades_ind6` (`report_card_comment_id`)');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `idx_srcg_comb1`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `idx_srcg_comb1` (`student_id`,`course_period_id`,`marking_period_id`)');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `idx_srcg_comb2`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `idx_srcg_comb2` (`course_period_id`,`marking_period_id`)');

                                ### for Keys - End ###

                                $dbconn->query('ALTER TABLE `user_file_upload` ADD IF NOT EXISTS `download_id` VARCHAR(50) NOT NULL AFTER `syear`');

                                $_SESSION['mod'] = 'upgrade';
                                header('Location: Step5.php');
                                // $_SESSION['mod'] = 'upgrade';
                                exit;
                            } else if ($v == '7.2') {

                                $dbconn->query('TRUNCATE app');
                                $app_insert = "INSERT INTO `app` (`name`, `value`) VALUES
                                    ('version', '9.1'),
                                    ('date', 'December 30, 2023'),
                                    ('build', '20221230001'),
                                    ('update', '0'),
                                    ('last_updated', 'December 30, 2023');";
                                $dbconn->query($app_insert) or die($dbconn->error);

                                $dbconn->query('CREATE TABLE IF NOT EXISTS `user_file_upload` (
                                    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                   `user_id` int(11) NOT NULL,
                                   `profile_id` int(11) NOT NULL,
                                   `school_id` int(11) NOT NULL,
                                   `syear` int(11) NOT NULL,
                                   `download_id` varchar(50) NOT NULL,
                                   `name` varchar(255) NOT NULL,
                                   `size` int(11) NOT NULL,
                                   `type` varchar(255) NOT NULL,
                                   `content` longblob NOT NULL,
                                   `file_info` varchar(255) NOT NULL
                                 ) ENGINE=InnoDB DEFAULT CHARSET=latin1');

                                $stu_info = $dbconn->query('SELECT * FROM students WHERE language !=\'\'') or die($dbconn->error);


                                while ($fetch = $stu_info->fetch_assoc()) {
                                    $stu_lang = $dbconn->query('SELECT * FROM language WHERE UPPER(language_name)=UPPER(\'' . $fetch['language'] . '\')') or die($dbconn->error);
                                    $fetchlang = $stu_lang->fetch_assoc();
                                    if (count($fetchlang) > 0)
                                        $dbconn->query('UPDATE students SET language=\'' . $fetchlang['language_id'] . '\' WHERE student_id=' . $fetch['student_id']) or die($dbconn->error);
                                    else {
                                        $dbconn->query('INSERT INTO language (language_name) VALUES (\'' . $fetch['language'] . '\')') or die($dbconn->error);
                                        $stu_lang = $dbconn->query('SELECT * FROM language WHERE UPPER(language_name)=UPPER(\'' . $fetch['language'] . '\')') or die($dbconn->error);
                                        $fetchlang = $stu_lang->fetch_assoc();
                                        if (count($fetchlang) > 0)
                                            $dbconn->query('UPDATE students SET language=\'' . $fetchlang['language_id'] . '\' WHERE student_id=' . $fetch['student_id']) or die($dbconn->error);
                                    }
                                }


                                $stu_ethn_info = $dbconn->query('SELECT * FROM students WHERE ethnicity !=\'\'') or die($dbconn->error);

                                while ($ethn_fetch = $stu_ethn_info->fetch_assoc()) {
                                    $stu_ethn = $dbconn->query('SELECT * FROM ethnicity WHERE UPPER(ethnicity_name)=UPPER(\'' . $ethn_fetch['ethnicity'] . '\')') or die($dbconn->error);

                                    $fetchethn = $stu_ethn->fetch_assoc();

                                    if (count($fetchethn) > 0) {
                                        $dbconn->query('UPDATE students SET ethnicity=\'' . $fetchethn['ethnicity_id'] . '\' WHERE student_id=' . $ethn_fetch['student_id']) or die($dbconn->error);
                                    } else {
                                        $dbconn->query('INSERT INTO ethnicity (ethnicity_name) VALUES (\'' . $ethn_fetch['ethnicity'] . '\')') or die($dbconn->error);

                                        $stu_ethn = $dbconn->query('SELECT * FROM ethnicity WHERE UPPER(ethnicity_name)=UPPER(\'' . $ethn_fetch['ethnicity'] . '\')') or die($dbconn->error);

                                        $fetchethn = $stu_ethn->fetch_assoc();

                                        if (count($fetchethn) > 0) {
                                            $dbconn->query('UPDATE students SET ethnicity=\'' . $fetchethn['ethnicity_id'] . '\' WHERE student_id=' . $ethn_fetch['student_id']) or die($dbconn->error);
                                        }
                                    }
                                }


                                $dbconn->query('ALTER TABLE `students` CHANGE `language` `language_id` INT(8) NULL DEFAULT NULL');
                                $dbconn->query('ALTER TABLE `students` CHANGE `ethnicity` `ethnicity_id` INT(11) NULL DEFAULT NULL');
                                $dbconn->query('ALTER TABLE `mail_group` ADD `school_id` int(11) NOT NULL');
                                $dbconn->query('ALTER TABLE `mail_groupmembers` ADD `school_id` int(11) NOT NULL');
                                $dbconn->query('ALTER TABLE `mail_group` ADD INDEX `mail_group_ind` (`school_id`) USING BTREE');
                                $dbconn->query('ALTER TABLE `mail_groupmembers` ADD INDEX `mail_groupmembers_ind` (`school_id`) USING BTREE');

                                ### for Functions/Procedures/Triggers/Events - Start ###

                                $this_db = $_SESSION['db'];

                                $SQL_Procs = "OpensisProcsMysqlInc.sql";
                                $SQL_Trigger = "OpensisTriggerMysqlInc.sql";

                                executeSQL($SQL_Procs, $this_db);
                                executeSQL($SQL_Trigger, $this_db);

                                ### for Functions/Procedures/Triggers/Events - End ###

                                ### for Keys - Start ###

                                $dbconn->query('ALTER TABLE `missing_attendance` DROP KEY IF EXISTS `idx_appstart_check`');
                                $dbconn->query('ALTER TABLE `missing_attendance` ADD KEY `idx_appstart_check` (`course_period_id`,`period_id`,`syear`,`school_id`,`school_date`)');

                                $dbconn->query('ALTER TABLE `missing_attendance` DROP KEY IF EXISTS `idx_missing_attendance_syear`');
                                $dbconn->query('ALTER TABLE `missing_attendance` ADD KEY `idx_missing_attendance_syear` (`syear`)');

                                $dbconn->query('ALTER TABLE `login_authentication` DROP KEY IF EXISTS `idx_login_authentication_username_password`');
                                $dbconn->query('ALTER TABLE `login_authentication` ADD KEY `idx_login_authentication_username_password` (`username`,`password`)');

                                $dbconn->query('ALTER TABLE `students` DROP INDEX IF EXISTS `idx_student_search`');
                                $dbconn->query('ALTER TABLE students ADD INDEX IF NOT EXISTS `idx_students_search` (`is_disable`) COMMENT \'Student Info -> search all\'');

                                $dbconn->query('ALTER TABLE student_enrollment ADD INDEX IF NOT EXISTS `idx_student_search` (`school_id`,`syear`,`start_date`,`end_date`,`drop_code`) COMMENT \'Student Info -> search all\'');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `student_report_card_grades_ind5`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `student_report_card_grades_ind5` (`report_card_grade_id`)');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `student_report_card_grades_ind6`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `student_report_card_grades_ind6` (`report_card_comment_id`)');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `idx_srcg_comb1`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `idx_srcg_comb1` (`student_id`,`course_period_id`,`marking_period_id`)');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `idx_srcg_comb2`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `idx_srcg_comb2` (`course_period_id`,`marking_period_id`)');

                                ### for Keys - End ###

                                $dbconn->query('ALTER TABLE `user_file_upload` ADD IF NOT EXISTS `download_id` VARCHAR(50) NOT NULL AFTER `syear`');

                                $_SESSION['mod'] = 'upgrade';
                                header('Location: Step5.php');
                                // $_SESSION['mod'] = 'upgrade';
                                exit;
                            } else if ($v == '7.3') {
                                $dbconn->query('TRUNCATE app');
                                $app_insert = "INSERT INTO `app` (`name`, `value`) VALUES
                                        ('version', '9.1'),
                                        ('date', 'December 30, 2023'),
                                        ('build', '20221230001'),
                                        ('update', '0'),
                                        ('last_updated', 'December 30, 2023');";
                                $dbconn->query($app_insert) or die($dbconn->error);

                                $dbconn->query('CREATE TABLE IF NOT EXISTS `user_file_upload` (
                                    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                   `user_id` int(11) NOT NULL,
                                   `profile_id` int(11) NOT NULL,
                                   `school_id` int(11) NOT NULL,
                                   `syear` int(11) NOT NULL,
                                   `download_id` varchar(50) NOT NULL,
                                   `name` varchar(255) NOT NULL,
                                   `size` int(11) NOT NULL,
                                   `type` varchar(255) NOT NULL,
                                   `content` longblob NOT NULL,
                                   `file_info` varchar(255) NOT NULL
                                 ) ENGINE=InnoDB DEFAULT CHARSET=latin1');
                                ### for Language - Start ###

                                $check_language = $dbconn->query("SHOW COLUMNS FROM `students` LIKE 'language'") or die($dbconn->error);

                                $check_language_arr = $check_language->fetch_assoc();


                                if (is_countable($check_language_arr) && (count($check_language_arr) > 0)) {
                                    $stu_info = $dbconn->query('SELECT * FROM students WHERE language !=\'\'') or die($dbconn->error);

                                    while ($fetch = $stu_info->fetch_assoc()) {
                                        $stu_lang = $dbconn->query('SELECT * FROM language WHERE UPPER(language_name)=UPPER(\'' . $fetch['language'] . '\')') or die($dbconn->error);
                                        $fetchlang = $stu_lang->fetch_assoc();
                                        if (count($fetchlang) > 0)
                                            $dbconn->query('UPDATE students SET language=\'' . $fetchlang['language_id'] . '\' WHERE student_id=' . $fetch['student_id']) or die($dbconn->error);
                                        else {
                                            $dbconn->query('INSERT INTO language (language_name) VALUES (\'' . $fetch['language'] . '\')') or die($dbconn->error);
                                            $stu_lang = $dbconn->query('SELECT * FROM language WHERE UPPER(language_name)=UPPER(\'' . $fetch['language'] . '\')') or die($dbconn->error);
                                            $fetchlang = $stu_lang->fetch_assoc();
                                            if (count($fetchlang) > 0)
                                                $dbconn->query('UPDATE students SET language=\'' . $fetchlang['language_id'] . '\' WHERE student_id=' . $fetch['student_id']) or die($dbconn->error);
                                        }
                                    }
                                }

                                ### for Language - End ###

                                ### for Ethnicity - Start ###

                                $check_ethnicity = $dbconn->query("SHOW COLUMNS FROM `students` LIKE 'ethnicity'") or die($dbconn->error);

                                $check_ethnicity_arr = $check_ethnicity->fetch_assoc();

                                if (is_countable($check_ethnicity_arr) && (count($check_ethnicity_arr) > 0)) {
                                    $stu_ethn_info = $dbconn->query('SELECT * FROM students WHERE ethnicity !=\'\'') or die($dbconn->error);

                                    while ($ethn_fetch = $stu_ethn_info->fetch_assoc()) {
                                        $stu_ethn = $dbconn->query('SELECT * FROM ethnicity WHERE UPPER(ethnicity_name)=UPPER(\'' . $ethn_fetch['ethnicity'] . '\')') or die($dbconn->error);

                                        $fetchethn = $stu_ethn->fetch_assoc();

                                        if (count($fetchethn) > 0) {
                                            $dbconn->query('UPDATE students SET ethnicity=\'' . $fetchethn['ethnicity_id'] . '\' WHERE student_id=' . $ethn_fetch['student_id']) or die($dbconn->error);
                                        } else {
                                            $dbconn->query('INSERT INTO ethnicity (ethnicity_name) VALUES (\'' . $ethn_fetch['ethnicity'] . '\')') or die($dbconn->error);

                                            $stu_ethn = $dbconn->query('SELECT * FROM ethnicity WHERE UPPER(ethnicity_name)=UPPER(\'' . $ethn_fetch['ethnicity'] . '\')') or die($dbconn->error);

                                            $fetchethn = $stu_ethn->fetch_assoc();

                                            if (count($fetchethn) > 0) {
                                                $dbconn->query('UPDATE students SET ethnicity=\'' . $fetchethn['ethnicity_id'] . '\' WHERE student_id=' . $ethn_fetch['student_id']) or die($dbconn->error);
                                            }
                                        }
                                    }
                                }

                                ### for Ethnicity - End ###

                                $dbconn->query('ALTER TABLE `students` CHANGE `language` `language_id` INT(8) NULL DEFAULT NULL');
                                $dbconn->query('ALTER TABLE `students` CHANGE `ethnicity` `ethnicity_id` INT(11) NULL DEFAULT NULL');
                                $dbconn->query('ALTER TABLE `mail_group` ADD `school_id` int(11) NOT NULL');
                                $dbconn->query('ALTER TABLE `mail_groupmembers` ADD `school_id` int(11) NOT NULL');
                                $dbconn->query('ALTER TABLE `mail_group` ADD INDEX `mail_group_ind` (`school_id`) USING BTREE');
                                $dbconn->query('ALTER TABLE `mail_groupmembers` ADD INDEX `mail_groupmembers_ind` (`school_id`) USING BTREE');

                                ### for Functions/Procedures/Triggers/Events - Start ###

                                $this_db = $_SESSION['db'];

                                $SQL_Procs = "OpensisProcsMysqlInc.sql";
                                $SQL_Trigger = "OpensisTriggerMysqlInc.sql";

                                executeSQL($SQL_Procs, $this_db);
                                executeSQL($SQL_Trigger, $this_db);

                                ### for Functions/Procedures/Triggers/Events - End ###

                                ### for Keys - Start ###

                                $dbconn->query('ALTER TABLE `missing_attendance` DROP KEY IF EXISTS `idx_appstart_check`');
                                $dbconn->query('ALTER TABLE `missing_attendance` ADD KEY `idx_appstart_check` (`course_period_id`,`period_id`,`syear`,`school_id`,`school_date`)');

                                $dbconn->query('ALTER TABLE `missing_attendance` DROP KEY IF EXISTS `idx_missing_attendance_syear`');
                                $dbconn->query('ALTER TABLE `missing_attendance` ADD KEY `idx_missing_attendance_syear` (`syear`)');

                                $dbconn->query('ALTER TABLE `login_authentication` DROP KEY IF EXISTS `idx_login_authentication_username_password`');
                                $dbconn->query('ALTER TABLE `login_authentication` ADD KEY `idx_login_authentication_username_password` (`username`,`password`)');

                                $dbconn->query('ALTER TABLE `students` DROP INDEX IF EXISTS `idx_student_search`');
                                $dbconn->query('ALTER TABLE students ADD INDEX IF NOT EXISTS `idx_students_search` (`is_disable`) COMMENT \'Student Info -> search all\'');

                                $dbconn->query('ALTER TABLE student_enrollment ADD INDEX IF NOT EXISTS `idx_student_search` (`school_id`,`syear`,`start_date`,`end_date`,`drop_code`) COMMENT \'Student Info -> search all\'');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `student_report_card_grades_ind5`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `student_report_card_grades_ind5` (`report_card_grade_id`)');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `student_report_card_grades_ind6`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `student_report_card_grades_ind6` (`report_card_comment_id`)');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `idx_srcg_comb1`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `idx_srcg_comb1` (`student_id`,`course_period_id`,`marking_period_id`)');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `idx_srcg_comb2`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `idx_srcg_comb2` (`course_period_id`,`marking_period_id`)');

                                ### for Keys - End ###

                                $dbconn->query('ALTER TABLE `user_file_upload` ADD IF NOT EXISTS `download_id` VARCHAR(50) NOT NULL AFTER `syear`');

                                $_SESSION['mod'] = 'upgrade';
                                header('Location: Step5.php');
                                // $_SESSION['mod'] = 'upgrade';
                                exit;
                            } else if ($v == '7.4' || $v == '7.5' || $v == '7.6' || $v == '8.0') {
                                $dbconn->query('TRUNCATE app');
                                $app_insert = "INSERT INTO `app` (`name`, `value`) VALUES
                                        ('version', '9.1'),
                                        ('date', 'December 30, 2023'),
                                        ('build', '20221230001'),
                                        ('update', '0'),
                                        ('last_updated', 'December 30, 2023');";
                                $dbconn->query($app_insert) or die($dbconn->error);
                                
                                $dbconn->query('CREATE TABLE IF NOT EXISTS `user_file_upload` (
                                    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                   `user_id` int(11) NOT NULL,
                                   `profile_id` int(11) NOT NULL,
                                   `school_id` int(11) NOT NULL,
                                   `syear` int(11) NOT NULL,
                                   `download_id` varchar(50) NOT NULL,
                                   `name` varchar(255) NOT NULL,
                                   `size` int(11) NOT NULL,
                                   `type` varchar(255) NOT NULL,
                                   `content` longblob NOT NULL,
                                   `file_info` varchar(255) NOT NULL
                                 ) ENGINE=InnoDB DEFAULT CHARSET=latin1');
                                ### for Language - Start ###

                                $check_language = $dbconn->query("SHOW COLUMNS FROM `students` LIKE 'language'") or die($dbconn->error);

                                $check_language_arr = $check_language->fetch_assoc();

                                if (is_countable($check_language_arr) && (count($check_language_arr) > 0)) {
                                    $stu_info = $dbconn->query('SELECT * FROM students WHERE language !=\'\'') or die($dbconn->error);

                                    while ($fetch = $stu_info->fetch_assoc()) {
                                        $stu_lang = $dbconn->query('SELECT * FROM language WHERE UPPER(language_name)=UPPER(\'' . $fetch['language'] . '\')') or die($dbconn->error);
                                        $fetchlang = $stu_lang->fetch_assoc();
                                        if (count($fetchlang) > 0)
                                            $dbconn->query('UPDATE students SET language=\'' . $fetchlang['language_id'] . '\' WHERE student_id=' . $fetch['student_id']) or die($dbconn->error);
                                        else {
                                            $dbconn->query('INSERT INTO language (language_name) VALUES (\'' . $fetch['language'] . '\')') or die($dbconn->error);
                                            $stu_lang = $dbconn->query('SELECT * FROM language WHERE UPPER(language_name)=UPPER(\'' . $fetch['language'] . '\')') or die($dbconn->error);
                                            $fetchlang = $stu_lang->fetch_assoc();
                                            if (count($fetchlang) > 0)
                                                $dbconn->query('UPDATE students SET language=\'' . $fetchlang['language_id'] . '\' WHERE student_id=' . $fetch['student_id']) or die($dbconn->error);
                                        }
                                    }

                                    $dbconn->query('ALTER TABLE `students` CHANGE `language` `language_id` INT(8) NULL DEFAULT NULL');
                                }

                                ### for Language - End ###
                                
                                ### for Ethnicity - Start ###

                                $check_ethnicity = $dbconn->query("SHOW COLUMNS FROM `students` LIKE 'ethnicity'") or die($dbconn->error);

                                $check_ethnicity_arr = $check_ethnicity->fetch_assoc();

                                if (is_countable($check_ethnicity_arr) && (count($check_ethnicity_arr) > 0)) {
                                    $stu_ethn_info = $dbconn->query('SELECT * FROM students WHERE ethnicity !=\'\'') or die($dbconn->error);

                                    while ($ethn_fetch = $stu_ethn_info->fetch_assoc()) {
                                        $stu_ethn = $dbconn->query('SELECT * FROM ethnicity WHERE UPPER(ethnicity_name)=UPPER(\'' . $ethn_fetch['ethnicity'] . '\')') or die($dbconn->error);

                                        $fetchethn = $stu_ethn->fetch_assoc();

                                        if (count($fetchethn) > 0) {
                                            $dbconn->query('UPDATE students SET ethnicity=\'' . $fetchethn['ethnicity_id'] . '\' WHERE student_id=' . $ethn_fetch['student_id']) or die($dbconn->error);
                                        } else {
                                            $dbconn->query('INSERT INTO ethnicity (ethnicity_name) VALUES (\'' . $ethn_fetch['ethnicity'] . '\')') or die($dbconn->error);

                                            $stu_ethn = $dbconn->query('SELECT * FROM ethnicity WHERE UPPER(ethnicity_name)=UPPER(\'' . $ethn_fetch['ethnicity'] . '\')') or die($dbconn->error);

                                            $fetchethn = $stu_ethn->fetch_assoc();

                                            if (count($fetchethn) > 0) {
                                                $dbconn->query('UPDATE students SET ethnicity=\'' . $fetchethn['ethnicity_id'] . '\' WHERE student_id=' . $ethn_fetch['student_id']) or die($dbconn->error);
                                            }
                                        }
                                    }
                                    $dbconn->query('ALTER TABLE `students` CHANGE `ethnicity` `ethnicity_id` INT(11) NULL DEFAULT NULL');
                                }

                                
                                ### for Ethnicity - End ###

                                $dbconn->query('ALTER TABLE `mail_group` ADD `school_id` int(11) NOT NULL');
                                $dbconn->query('ALTER TABLE `mail_groupmembers` ADD `school_id` int(11) NOT NULL');
                                $dbconn->query('ALTER TABLE `mail_group` ADD INDEX `mail_group_ind` (`school_id`) USING BTREE');
                                $dbconn->query('ALTER TABLE `mail_groupmembers` ADD INDEX `mail_groupmembers_ind` (`school_id`) USING BTREE');

                                ### for Functions/Procedures/Triggers/Events - Start ###
                                $this_db = $_SESSION['db'];

                                $SQL_Procs = "OpensisProcsMysqlInc.sql";
                                $SQL_Trigger = "OpensisTriggerMysqlInc.sql";
            
                                executeSQL($SQL_Procs, $this_db);
                                executeSQL($SQL_Trigger, $this_db);
                                ### for Functions/Procedures/Triggers/Events - End ###

                                ### for Keys - Start ###
                                $dbconn->query('ALTER TABLE `missing_attendance` DROP KEY IF EXISTS `idx_appstart_check`');
                                $dbconn->query('ALTER TABLE `missing_attendance` ADD KEY `idx_appstart_check` (`course_period_id`,`period_id`,`syear`,`school_id`,`school_date`)');

                                $dbconn->query('ALTER TABLE `missing_attendance` DROP KEY IF EXISTS `idx_missing_attendance_syear`');
                                $dbconn->query('ALTER TABLE `missing_attendance` ADD KEY `idx_missing_attendance_syear` (`syear`)');

                                $dbconn->query('ALTER TABLE `login_authentication` DROP KEY IF EXISTS `idx_login_authentication_username_password`');
                                $dbconn->query('ALTER TABLE `login_authentication` ADD KEY `idx_login_authentication_username_password` (`username`,`password`)');

                                $dbconn->query('ALTER TABLE `students` DROP INDEX IF EXISTS `idx_student_search`');
                                $dbconn->query('ALTER TABLE students ADD INDEX IF NOT EXISTS `idx_students_search` (`is_disable`) COMMENT \'Student Info -> search all\'');

                                $dbconn->query('ALTER TABLE student_enrollment ADD INDEX IF NOT EXISTS `idx_student_search` (`school_id`,`syear`,`start_date`,`end_date`,`drop_code`) COMMENT \'Student Info -> search all\'');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `student_report_card_grades_ind5`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `student_report_card_grades_ind5` (`report_card_grade_id`)');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `student_report_card_grades_ind6`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `student_report_card_grades_ind6` (`report_card_comment_id`)');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `idx_srcg_comb1`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `idx_srcg_comb1` (`student_id`,`course_period_id`,`marking_period_id`)');

                                $dbconn->query('ALTER TABLE `student_report_card_grades` DROP KEY IF EXISTS `idx_srcg_comb2`');
                                $dbconn->query('ALTER TABLE `student_report_card_grades` ADD KEY `idx_srcg_comb2` (`course_period_id`,`marking_period_id`)');

                                ### for Keys - End ###
                              
                                $dbconn->query('ALTER TABLE `user_file_upload` ADD IF NOT EXISTS `download_id` VARCHAR(50) NOT NULL AFTER `syear`');
                                $_SESSION['mod'] = 'upgrade';
                                header('Location: Step5.php');
                                // $_SESSION['mod'] = 'upgrade';
                                exit;
                            }


                            //----------------------------To select all extra table-------------------------------------



                            $qr_tab = $dbconn->query("show full tables where Table_Type != 'VIEW'") or die($dbconn->error);
                            $extra_tab = array();
                            $fetch1 = $qr_tab->fetch_assoc();

                            while ($fetch = $qr_tab->fetch_row()) {

                                //print_r($fetch);echo '<br><br>';
                                //echo "if (!in_array($fetch[0],";print_r($opensis_tab).")<br><br>";
                                if (!in_array($fetch[0], $opensis_tab)) {

                                    $tab_name = $fetch[0];
                                    $extra_tab[] = $tab_name;
                                }
                            }


                            if (count($extra_tab) > 0) {
                                $_SESSION['extra_tab'] = 1;
                                $tab_implode = implode(' ', $extra_tab);

                                $Export_FileName_ex = "dump_extra_back.sql";

                                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {

                                    if ($dbPass == '')
                                        exec("$mysql_dir\\mysqldump --user $dbUser  $mysql_database $tab_implode > $Export_FileName_ex");
                                    else
                                        exec("$mysql_dir\\mysqldump --user $dbUser --password='$dbPass' $mysql_database $tab_implode > $Export_FileName_ex");
                                } else {
                                    exec("mysqldump --user $dbUser --password='$dbPass' $mysql_database $tab_implode > $Export_FileName_ex");
                                }

                                foreach ($extra_tab as $vk => $vv) {

                                    $dbconn->query("drop table " . $vv) or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 209</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                }
                            }



                            if ($v != '5.3' && $v != '5.4') {

                                $proceed = $dbconn->query("SELECT name,value
FROM app
WHERE value='4.6' OR value='4.7' OR value LIKE '4.8%' OR value='4.9' OR value='5.0' OR value='5.1' OR value='5.2' OR value='5.3'") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 220</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                $proceed = $proceed->fetch_assoc();
                                if (!$proceed) {
                                    $proceed = $dbconn->query("SELECT name,value
    FROM app
    WHERE value='4.6' OR value='4.7' OR value LIKE '4.8%' OR value='4.9' OR value='5.0'") or die($dbconn->error . ' at line 225');
                                    $proceed = $proceed->fetch_assoc();
                                }
                                $version = $proceed['value'];
                                $get_routines = $dbconn->query('SELECT routine_name,routine_type FROM information_schema.routines WHERE routine_schema=\'' . $mysql_database . '\' ') or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 229</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                while ($get_routines_arr = $get_routines->fetch_assoc()) {

                                    $dbconn->query('DROP ' . $get_routines_arr['routine_type'] . ' IF EXISTS ' . $get_routines_arr['routine_name']) or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 232</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                }

                                $get_trigger = $dbconn->query('SELECT trigger_name FROM information_schema.triggers WHERE trigger_schema=\'' . $mysql_database . '\' ') or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 235</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                while ($get_trigger_arr = $get_trigger->fetch_assoc()) {

                                    $dbconn->query('DROP TRIGGER IF EXISTS ' . $get_trigger_arr['trigger_name']) or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 238</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                }

                                $dbconn->query('UPDATE ' . table_to_upper('students', $version) . ' SET failed_login=0 WHERE failed_login is null') or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 241</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                if ($version != '5.2' && $version != '5.3') {
                                    $dbconn->query('Create table staff_new as SELECT * FROM ' . table_to_upper('staff', $version) . '') or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 243</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                    $dbconn->query('TRUNCATE TABLE staff_new') or die($dbconn->error . ' at line 244');
                                    $dbconn->query('ALTER TABLE `staff_new` DROP `syear`, DROP `schools`, DROP `rollover_id`') or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 245</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');

                                    $dbconn->query('DROP TABLE ' . table_to_upper('staff_school_relationship', $version) . '') or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 247</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                    $dbconn->query('CREATE TABLE ' . table_to_upper('staff_school_relationship', $version) . ' (
 `staff_id` int(11) NOT NULL,
 `school_id` int(11) NOT NULL,
 `syear` int(4) NOT NULL,
 `start_date` date NOT NULL,
 `end_date` date NOT NULL,
 PRIMARY KEY (`staff_id`,`school_id`,`syear`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8') or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 255</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');

                                    $sql = $dbconn->query('SELECT * FROM ' . table_to_upper('staff', $version) . ' order by staff_id asc') or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 257</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                    while ($row = $sql->fetch_assoc()) {
                                        if ($row['username'] != '')
                                            $staff_sql = $dbconn->query("SELECT staff_id FROM staff_new WHERE username='" . $row['username'] . "' AND username IS NOT NULL") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 260</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                        else
                                            $staff_sql = $dbconn->query("SELECT staff_id FROM staff_new WHERE first_name='" . $row['first_name'] . "' AND last_name='" . $row['last_name'] . "' AND profile='" . $row['profile'] . "'") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 262</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                        if ($staff_sql->num_rows == 0) {
                                            $staff_id = $row['staff_id'];
                                            $dbconn->query("insert into staff_new (staff_id,current_school_id,title,first_name,last_name,middle_name,username,password,phone,email,profile,homeroom,last_login,failed_login,profile_id,is_disable) values('" . $row['staff_id'] . "','" . $row['current_school_id'] . "'
            ,'" . $row['title'] . "','" . $row['first_name'] . "','" . $row['last_name'] . "','" . $row['middle_name'] . "','" . $row['username'] . "','" . $row['password'] . "'
                ,'" . $row['phone'] . "','" . $row['email'] . "','" . $row['profile'] . "','" . $row['homeroom'] . "','" . $row['last_login'] . "','" . $row['failed_login'] . "','" . $row['profile_id'] . "','" . $row['is_disable'] . "')") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 267</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                            if ($row['username'] != '')
                                                $st_info_sql = $dbconn->query("SELECT syear,staff_id,schools FROM " . table_to_upper('staff', $version) . " WHERE username='" . $row['username'] . "' AND username IS NOT NULL") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 269</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                            else
                                                $st_info_sql = $dbconn->query("SELECT syear,staff_id,schools FROM " . table_to_upper('staff', $version) . " WHERE first_name='" . $row['first_name'] . "' AND last_name='" . $row['last_name'] . "' AND profile='" . $row['profile'] . "' AND username IS NULL") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 271</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');

                                            while ($row1 = $st_info_sql->fetch_assoc()) {

                                                $school = substr(substr($row1['schools'], 0, -1), 1);
                                                $all_school = explode(',', $school);
                                                foreach ($all_school as $key => $value) {

                                                    $dbconn->query('insert into ' . table_to_upper('staff_school_relationship', $version) . ' values(\'' . $staff_id . '\',\'' . $value . '\',\'' . $row1['syear'] . '\',\'0000-00-00\',\'0000-00-00\')') or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 279</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                                }



                                                $dbconn->query("update attendance_completed set staff_id='" . $row['staff_id'] . "' WHERE staff_id='" . $row1['staff_id'] . "'") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 284</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                                $dbconn->query("update  course_periods set teacher_id='" . $row['staff_id'] . "' WHERE teacher_id='" . $row1['staff_id'] . "'") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 285</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                                $dbconn->query("update  course_periods set secondary_teacher_id='" . $row['staff_id'] . "' WHERE secondary_teacher_id='" . $row1['staff_id'] . "'") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 286</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                                $dbconn->query("update  login_records set staff_id='" . $row['staff_id'] . "' WHERE staff_id='" . $row1['staff_id'] . "'") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 287</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                                $dbconn->query("update missing_attendance set teacher_id='" . $row['staff_id'] . "' WHERE teacher_id='" . $row1['staff_id'] . "'") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 288</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                                $dbconn->query("update portal_notes set published_user='" . $row['staff_id'] . "'WHERE published_user='" . $row1['staff_id'] . "'") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 289</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                                $dbconn->query("update program_user_config set user_id='" . $row['staff_id'] . "'WHERE user_id='" . $row1['staff_id'] . "'") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 290</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                                $dbconn->query("update schedule_requests set with_teacher_id='" . $row['staff_id'] . "'WHERE with_teacher_id='" . $row1['staff_id'] . "'") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 291</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');

                                                $dbconn->query("update teacher_reassignment set teacher_id='" . $row['staff_id'] . "'WHERE teacher_id='" . $row1['staff_id'] . "'") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 293</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                                $dbconn->query("update teacher_reassignment set pre_teacher_id='" . $row['staff_id'] . "'WHERE pre_teacher_id='" . $row1['staff_id'] . "'") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 294</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                                $dbconn->query("update teacher_reassignment set modified_by='" . $row['staff_id'] . "'WHERE modified_by='" . $row1['staff_id'] . "'") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 295</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                                $dbconn->query("update gradebook_assignments set staff_id='" . $row['staff_id'] . "' WHERE staff_id='" . $row1['staff_id'] . "'") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 296</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                                $dbconn->query("update gradebook_assignment_types set staff_id='" . $row['staff_id'] . "' WHERE staff_id='" . $row1['staff_id'] . "'") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 297</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                                $dbconn->query("update grades_completed set staff_id='" . $row['staff_id'] . "' WHERE staff_id='" . $row1['staff_id'] . "'") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 298</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                                $dbconn->query("update student_mp_comments set staff_id='" . $row['staff_id'] . "' WHERE staff_id='" . $row1['staff_id'] . "'") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 299</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                                $dbconn->query("update schedule set modified_by='" . $row['staff_id'] . "' WHERE modified_by='" . $row1['staff_id'] . "'") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 300</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                            }
                                        }
                                    }

                                    $dbconn->query('DROP TABLE ' . table_to_upper('staff', $version) . '') or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 305</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                    $dbconn->query('RENAME TABLE `staff_new` TO ' . table_to_upper('staff', $version) . '') or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 306</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                }

                                if ($proceed['name']) {

                                    $dummyFile = "dummy.txt";
                                    $fpt = fopen($dummyFile, 'w');

                                    if ($fpt == FALSE) {
                                        die(show_error1() . ' Show Error 1');
                                    } else {
                                        unlink($dummyFile);
                                    }
                                    fclose($fpt);

                                    $date_time = date("m-d-Y");
                                    $mysql_database;
                                    $Export_FileName = $mysql_database . '_' . $date_time . '.sql';
                                    $myFile = "UpgradeInc.sql";

                                    executeSQL($myFile, $mysql_database);

                                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {

                                        if ($dbPass == '')
                                            exec("$mysql_dir\\mysqldump -n -t -c --skip-add-locks --skip-disable-keys --skip-triggers --user $dbUser  $mysql_database > $Export_FileName");
                                        else
                                            exec("$mysql_dir\\mysqldump -n -t -c --skip-add-locks --skip-disable-keys --skip-triggers --user $dbUser --password='$dbPass' $mysql_database > $Export_FileName");
                                    } else {
                                        exec("mysqldump -n -t -c --skip-add-locks --skip-disable-keys --skip-triggers --user $dbUser --password='$dbPass' $mysql_database > $Export_FileName");
                                    }


                                    $res_student_field = 'SHOW COLUMNS FROM ' . table_to_upper('students', $version) . ' WHERE FIELD LIKE "CUSTOM_%"';
                                    //
                                    $objCustomStudents = new custom($mysql_database);
                                    $objCustomStudents->set($res_student_field, 'students');

                                    $res_staff_field = 'SHOW COLUMNS FROM ' . table_to_upper('staff', $version) . ' WHERE FIELD LIKE "CUSTOM_%"';
                                    $objCustomStaff = new custom($mysql_database);
                                    $objCustomStaff->set($res_staff_field, 'staff');

                                    $dbconn->query("drop database $mysql_database") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 346</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');

                                    $dbconn->query("CREATE DATABASE $mysql_database CHARACTER SET utf8 COLLATE utf8_general_ci") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 348</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                    mysqli_close($dbconn);
                                    //        mysql_select_db($mysql_database);
                                    $dbconn = new mysqli($_SESSION['server'], $_SESSION['username'], $_SESSION['password'], $_SESSION['db'], $_SESSION['port']);


                                    $myFile = "OpensisUpdateSchemaMysql.sql";

                                    executeSQL($myFile, $mysql_database);

                                    //execute custome field for student
                                    foreach ($objCustomStudents->customQueryString as $query) {
                                        $dbconn->query($query);
                                    }
                                    //execute custome field for satff
                                    foreach ($objCustomStaff->customQueryString as $query) {
                                        $dbconn->query($query);
                                    }


                                    $myFile = "OpensisUpdateProcsMysql.sql";
                                    executeSQL($myFile, $mysql_database);

                                    //=====================For version prior than 4.9 only====================================
                                    if ($version != '5.0' || $version != '5.1' || $version != '5.2' || $version != '5.3') {
                                        $Export_FileName = to_upper_tables_to_import($Export_FileName);
                                    }

                                    //=========================================================
                                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {

                                        if ($dbPass == '')
                                            exec("$mysql_dir\\mysql --user $dbUser $mysql_database < $Export_FileName", $result, $status);
                                        else
                                            exec("$mysql_dir\\mysql --user $dbUser --password='$dbPass' $mysql_database < $Export_FileName", $result, $status);
                                    } else
                                        exec("mysql --user $dbUser --password='$dbPass' $mysql_database < $Export_FileName", $result, $status);


                                    if ($status != 0) {
                                        die(show_error1('db') . ' Show Error 2');
                                    }
                                    if ($version != '5.0') {
                                        unlink($Export_FileName);
                                    }
                                    $myFile = "OpensisUpdateTriggerMysql.sql";
                                    executeSQL($myFile, $mysql_database);
                                    $dbconn = new mysqli($_SESSION['server'], $_SESSION['username'], $_SESSION['password'], $_SESSION['db'], $_SESSION['port']);
                                    $dbconn->query("delete from app") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 395</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                    $appTable = "INSERT INTO `app` (`name`, `value`) VALUES
('version', '5.3'),
('date', 'December 01, 2013'),
('build', '01122013001'),
('update', '0'),
('last_updated', 'December 01, 2013')";
                                    $dbconn->query($appTable) or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 402</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                    $custom_insert = $dbconn->query("select count(*) from custom_fields where title in('Ethnicity','Common Name','Physician','Physician Phone','Preferred Hospital','Gender','Email','Phone','Language')") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 403</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                    $custom_insert = $custom_insert->fetch_assoc();
                                    $custom_insert = $custom_insert[0];
                                    if ($custom_insert < 9) {
                                        $custom_insert = "INSERT INTO `custom_fields` (`type`, `search`, `title`, `sort_order`, `select_options`, `category_id`, `system_field`, `required`, `default_selection`, `hide`) VALUES
('text', NULL, 'Ethnicity', 3, NULL, 1, 'Y', NULL, NULL, NULL),
('text', NULL, 'Common Name', 2, NULL, 1, 'Y', NULL, NULL, NULL),
('text', NULL, 'Physician', 6, NULL, 2, 'Y', NULL, NULL, NULL),
('text', NULL, 'Physician Phone', 7, NULL, 2, 'Y', NULL, NULL, NULL),
('text', NULL, 'Preferred Hospital', 8, NULL, 2, 'Y', NULL, NULL, NULL),
('text', NULL, 'Gender', 5, NULL, 1, 'Y', NULL, NULL, NULL),
('text', NULL, 'Email', 6, NULL, 1, 'Y', NULL, NULL, NULL),
('text', NULL, 'Phone', 9, NULL, 1, 'Y', NULL, NULL, NULL),
('text', NULL, 'Language', 8, NULL, 1, 'Y', NULL, NULL, NULL);";
                                        $dbconn->query($custom_insert) or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 417</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                    }
                                    $login_msg = $dbconn->query("SELECT COUNT(*) as tot FROM login_message WHERE 1") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 419</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                    $login_msg = $login_msg->fetch_assoc();
                                    $login_msg = $login_msg['tot'];
                                    if ($login_msg < 1) {
                                        $login_msg = "INSERT INTO `login_message` (`id`, `message`, `display`) VALUES
(1, 'This is a restricted network. Use of this network, its equipment, and resources is monitored at all times and requires explicit permission from the network administrator. If you do not have this permission in writing, you are violating the regulations of this network and can and will be prosecuted to the fullest extent of law. By continuing into this system, you are acknowledging that you are aware of and agree to these terms.', 'Y')";
                                        $dbconn->query($login_msg) or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 425</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                    }

                                    $syearqr = $dbconn->query("select MAX(syear) as year, MIN(start_date) as start from school_years") or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . $dbconn->error . ' at line 428</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                    $syear = $syearqr->fetch_assoc();
                                    $_SESSION['syear'] = $syear['year'];
                                    $max_syear = $syear['year'];
                                    $start_date = $syear['start'];
                                    //=============================4.8.1 To 4.9===================================
                                    if ($version != '5.0' && $version != '4.9' && $version != '5.1' && $version != '5.2' && $version != '5.3') {
                                        $up_sql = "INSERT INTO student_enrollment_codes(syear,title,short_name,type)VALUES
        (" . $max_syear . ",'Transferred out','TRAN','TrnD'),
        (" . $max_syear . ",'Transferred in','TRAN','TrnE'),
        (" . $max_syear . ",'Rolled over','ROLL','Roll'); ";
                                        $dbconn->query($up_sql) or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . show_error1() . ' Show Error 3</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');

                                        $up_sql = "INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit) VALUES
            (3, 'scheduling/PrintSchedules.php','Y',NULL),
            (1, 'scheduling/ViewSchedule.php', 'Y', NULL),
            (2, 'scheduling/ViewSchedule.php', 'Y', NULL),
            (1, 'schoolsetup/UploadLogo.php', 'Y', 'Y'); ";
                                        $dbconn->query($up_sql) or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . show_error1() . ' Show Error 4</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');

                                        $up_sql = "INSERT INTO program_config (program, title, value) VALUES
            ('MissingAttendance', 'LAST_UPDATE','" . $start_date . "'); ";
                                        $dbconn->query($up_sql) or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . show_error1() . ' Show Error 5</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');

                                        $up_sql = "UPDATE profile_exceptions SET modname='scheduling/ViewSchedule.php' WHERE modname='scheduling/Schedule.php' AND (profile_id=0 OR profile_id=3);";
                                        $dbconn->query($up_sql) or die('<i class="fa fa-exclamation-triangle fa-3x text-danger"></i><h2>' . show_error1() . ' Show Error 6</h2><br/><a href="Step0.php" class="btn btn-danger"><i class="fa fa-refresh"></i> Start Again</a>');
                                    }
                                    //====================================================================
                                    $dbconn->query("UPDATE schedule SET dropped='Y' WHERE end_date IS NOT NULL AND end_date < CURDATE() AND dropped='N'");
                                    header('Location: Upgrade6.php');
                                    unset($objCustomStudents);
                                    unset($objCustomStaff);
                                } else {
                            ?>
                                    <!DOCTYPE html>
                                    <html lang="en">

                                    <head>
                                        <meta charset="utf-8">
                                        <meta http-equiv="X-UA-Compatible" content="IE=edge">
                                        <meta name="viewport" content="width=device-width, initial-scale=1">
                                        <title>openSIS Installer</title>
                                        <link href="../assets/css/icons/fontawesome/styles.min.css" rel="stylesheet">
                                        <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css">
                                        <link rel="stylesheet" href="assets/css/installer.css?v=<?php echo rand(000, 999); ?>" type="text/css" />
                                        <noscript>
                                            <META http-equiv=REFRESH content='0;url=../EnableJavascript.php' />
                                        </noscript>
                                    </head>

                                    <body class="outer-body">
                                        <section class="login">
                                            <div class="login-wrapper">
                                                <div class="panel" style="width: 60%;">
                                                    <div class="panel-heading">
                                                        <div class="row">
                                                            <div class="col-xs-8 text-left">
                                                                <div class="logo">
                                                                    <img src="assets/images/opensis_logo.png" alt="openSIS">
                                                                </div>
                                                                <h3>Warning</h3>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="panel-body">
                                                        <div class="installation-steps-wrapper">
                                                            <div class="installation-steps text-center">
                                                                <h2 class="text-center text-danger no-margin" style="padding: 30px;">The database you have chosen is not compliant with openSIS-CE version 6.3 or 6.4 or 6.5 We are unable to proceed.</h2>
                                                                <p>Click Retry to select another database, or Exit to quit the installation.</p>

                                                                <div class="text-center" style="padding: 20px 30px;">
                                                                    <a href="Selectdb.php" class="btn btn-primary">Retry</a>
                                                                    <a href="Step0.php" class="btn btn-default">Exit</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div><!-- /.panel -->
                                                <footer>
                                                    Copyright &copy; Open Solutions for Education, Inc. (<a href="http://www.os4ed.com">OS4Ed</a>).
                                                </footer>
                                            </div>
                                        </section>
                                    </body>

                                    </html>
                            <?php
                                }
                            } else {
                                header('Location: Upgrade6.php');
                            }

                            function executeSQL($myFile, $mysql_database)
                            {
                                $sql = file_get_contents($myFile);
                                $sqllines = par_spt("/[\n]/", $sql);
                                $cmd = '';
                                $delim = false;
                                $dbconncus = new mysqli($_SESSION['server'], $_SESSION['username'], $_SESSION['password'], $mysql_database, $_SESSION['port']);
                                foreach ($sqllines as $l) {
                                    if (par_rep_mt('/^\s*--/', $l) == 0) {
                                        if (par_rep_mt('/DELIMITER \$\$/', $l) != 0) {
                                            $delim = true;
                                        } else {
                                            if (par_rep_mt('/DELIMITER ;/', $l) != 0) {
                                                $delim = false;
                                            } else {
                                                if (par_rep_mt('/END\$\$/', $l) != 0) {
                                                    $cmd .= ' END';
                                                } else {
                                                    $cmd .= ' ' . $l . "\n";
                                                }
                                            }
                                            if (par_rep_mt('/.+;/', $l) != 0 && !$delim) {

                                                $result = $dbconncus->query($cmd) or die(mysqli_error($dbconn) . ' Show Error 7');
                                                $cmd = '';
                                            }
                                        }
                                    }
                                }
                            }

                            function show_error1($msg = '')
                            {
                                if ($msg == '')
                                    $msg = 'Application does not have permission to write into install directory.';
                                elseif ($msg == 'db')
                                    $msg = 'Your database is not compatible with openSIS-CE<br />Please take this screen shot and send it to your openSIS representative for resolution.';
                                $err .= "
<html>
<head>
<link rel='stylesheet' type='text/css' href='../styles/Installer.css' />
</head>
<body>

<div style='height:280px;'>

<br /><br /><span class='header_txt'></span>

<div align='center'>
$msg
</div>
<div style='height:50px;'>&nbsp;</div>";
                                $err .= "<div align='center'><a href='Selectdb.php?mod=upgrade'><img src='images/retry.png' border='0' /></a> &nbsp; &nbsp; <a href='Step0.php'><img src='images/exit.png' border='0' /></a></div>";
                                $err .= "</div></body></html>";
                                echo $err;
                            }

                            function table_to_upper($table, $ver)
                            {
                                if ($ver == '4.6' || $ver == '4.7' || $ver == '4.8' || $ver == '4.8.1' || $ver == '4.9')
                                    $return = strtoupper($table);
                                else
                                    $return = $table;
                                return $return;
                            }

                            function to_upper_tables_to_import($input_file)
                            {
                                $output_file = 'temp_opensis5.0.sql';
                                $handle = @fopen($input_file, "r"); // Open file form read.
                                $str = '';
                                if ($handle) {
                                    while (!feof($handle)) { // Loop til end of file.
                                        $buffer = fgets($handle, 4096); // Read a line.
                                        if (substr($buffer, 0, 11) == 'INSERT INTO') {
                                            $arr_line = explode(' ', $buffer);
                                            $arr_line[2] = strtolower($arr_line[2]);
                                            $str_line = implode(' ', $arr_line);
                                            $str .= $str_line;
                                        } else {
                                            $str .= $buffer;
                                        }
                                    }
                                    fclose($handle); // Close the file.

                                    $f = fopen($output_file, "w");
                                    fwrite($f, $str);
                                }
                                return $output_file;
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>

</html>