--
--
--

--SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;





CREATE TABLE `api_info` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `api_key` varchar(255) CHARACTER SET utf8 NOT NULL,
 `api_secret` varchar(255) CHARACTER SET utf8 NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE app (
    name character varying(100) NOT NULL,
    value character varying(100) NOT NULL
)ENGINE=InnoDB;


CREATE TABLE attendance_calendar (
    syear numeric(4,0) NOT NULL,
    school_id numeric NOT NULL,
    school_date date NOT NULL,
    minutes numeric,
    block character varying(10),
    calendar_id numeric NOT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE school_calendars (
    school_id numeric,
    title character varying(100),
    syear numeric(4,0),
    calendar_id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    default_calendar character varying(1),
    days VARCHAR( 7 ),
    rollover_id numeric,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `calendar_events_visibility` (
  `calendar_id` int(11) NOT NULL,
  `profile_id` int(11) DEFAULT NULL,
  `profile` character varying(50) DEFAULT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE attendance_code_categories (
    id  INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    syear numeric(4,0),
    school_id numeric,
    title character varying(255),
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


ALTER TABLE attendance_code_categories AUTO_INCREMENT=1;


CREATE TABLE attendance_codes (
    id int(8) not null auto_increment primary key,
    syear numeric(4,0),
    school_id numeric,
    title character varying(100),
    short_name character varying(10),
    type character varying(10),
    state_code character varying(1),
    default_code character varying(1),
    table_name numeric,
    sort_order numeric,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


ALTER TABLE attendance_codes AUTO_INCREMENT=1;


CREATE TABLE attendance_completed (
    staff_id numeric NOT NULL,
    school_date date NOT NULL,
    period_id numeric NOT NULL,
    course_period_id INT(11) NOT NULL,
    substitute_staff_id numeric NULL DEFAULT NULL,
    is_taken_by_substitute_staff char(1) NULL DEFAULT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE attendance_day (
    student_id numeric NOT NULL,
    school_date date NOT NULL,
    minutes_present numeric,
    state_value numeric(2,1),
    syear numeric(4,0),
    marking_period_id integer,
    comment character varying(255),
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE attendance_period (
    student_id numeric NOT NULL,
    school_date date NOT NULL,
    period_id numeric NOT NULL,
    attendance_code numeric,
    attendance_teacher_code numeric,
    attendance_reason character varying(100),
    admin character varying(1),
    course_period_id numeric,
    marking_period_id integer,
    comment character varying(100),
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE calendar_events (
    id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    syear numeric(4,0),
    school_id numeric,
    calendar_id numeric,
    school_date date,
    title character varying(50),
    description text,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


ALTER TABLE calendar_events AUTO_INCREMENT=1;


/*CREATE TABLE config (
    title character varying(100),
    syear numeric(4,0),
    login character varying(3)
)ENGINE=InnoDB;*/


CREATE TABLE course_periods (
    syear int(4) NOT NULL,
    school_id numeric NOT NULL,
    course_period_id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    course_id numeric NOT NULL,
    course_weight character varying(10),
    title character varying(100),
    short_name text,
    mp character varying(3),
    marking_period_id integer NULL DEFAULT NULL,
    begin_date date NULL DEFAULT NULL,
    end_date date NULL DEFAULT NULL,
    teacher_id int,
    secondary_teacher_id int,
    total_seats int,
    filled_seats numeric NOT NULL default 0,
    does_honor_roll character varying(1),
    does_class_rank character varying(1),
    gender_restriction character varying(1),
    house_restriction character varying(1),
    availability int,
    parent_id int,
    calendar_id int,
    half_day character varying(1),
    does_breakoff character varying(1),
    rollover_id int,
    grade_scale_id int,
    credits decimal(10,3) null default null,
    schedule_type ENUM( 'FIXED', 'VARIABLE', 'BLOCKED' ),
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified_by int(11) NOT NULL,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `course_period_var` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_period_id` int(11) NOT NULL,
  `days` varchar(7) DEFAULT NULL,
  `course_period_date` date DEFAULT NULL,
  `period_id` int(11) NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `room_id` int(11) NOT NULL,
  `does_attendance` varchar(1) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE rooms (
 room_id int(11) NOT NULL AUTO_INCREMENT,
 school_id int(11) NOT NULL,
 title varchar(50) NOT NULL,
 capacity int(11) DEFAULT NULL,
 description text,
 sort_order int(11) DEFAULT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL,
 PRIMARY KEY (room_id)
) ENGINE=InnoDB; 

CREATE TABLE courses (
    syear numeric(4,0) NOT NULL,
    course_id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    subject_id numeric NOT NULL,
    school_id numeric NOT NULL,
    grade_level numeric,
    title character varying(100),
    short_name character varying(25),
    rollover_id numeric,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


ALTER TABLE courses AUTO_INCREMENT=1;


CREATE TABLE course_subjects (
    syear numeric(4,0),
    school_id numeric,
    subject_id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title text,
    short_name text,
    rollover_id numeric,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;



ALTER TABLE course_subjects AUTO_INCREMENT=1;

CREATE TABLE custom_fields (
    id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    type character varying(10),
    search character varying(1),
    title character varying(30),
    sort_order numeric,
    select_options character varying(10000),
    category_id numeric,
    system_field character(1),
    required character varying(1),
    default_selection character varying(255),
	hide varchar(1),
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;
ALTER TABLE custom_fields AUTO_INCREMENT=1;


CREATE TABLE eligibility (
    student_id numeric,
    syear numeric(4,0),
    school_date date,
    period_id numeric,
    eligibility_code character varying(20),
    course_period_id numeric,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE eligibility_activities (
    id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    syear numeric(4,0),
    school_id numeric,
    title character varying(100),
    start_date date,
    end_date date,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;



ALTER TABLE eligibility_activities AUTO_INCREMENT=1;


CREATE TABLE eligibility_completed (
    staff_id numeric NOT NULL,
    school_date date NOT NULL,
    period_id numeric NOT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE school_gradelevels (
    id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    school_id numeric,
    short_name character varying(5),
    title character varying(50),
    next_grade_id numeric,
    sort_order numeric,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE student_enrollment (
    id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    syear numeric(4,0),
    school_id numeric,
    student_id numeric,
    grade_id numeric,
    start_date date,
    end_date date,
    enrollment_code numeric,
    drop_code numeric,
    next_school numeric,
    calendar_id numeric,
    last_school numeric,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;

ALTER TABLE student_enrollment AUTO_INCREMENT=1;


CREATE TABLE gradebook_assignment_types (
    assignment_type_id  INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    staff_id numeric,
    course_id numeric,
    title character varying(100),
    final_grade_percent numeric(6,5),
    course_period_id numeric,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


ALTER TABLE gradebook_assignment_types AUTO_INCREMENT=1;


CREATE TABLE gradebook_assignments (
    assignment_id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    staff_id numeric,
    marking_period_id integer,
    course_period_id numeric,
    course_id numeric,
    assignment_type_id numeric,
    title character varying(100),
    assigned_date date,
    due_date date,
    points numeric,
    description longtext,
	ungraded int(8) NOT NULL DEFAULT 1,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


ALTER TABLE gradebook_assignments AUTO_INCREMENT=1;


CREATE TABLE gradebook_grades (
    student_id numeric NOT NULL,
    period_id numeric,
    course_period_id numeric NOT NULL,
    assignment_id numeric NOT NULL,
    points numeric(6,2),
    comment longtext,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE grades_completed (
    staff_id numeric NOT NULL,
    marking_period_id integer NOT NULL,
    period_id numeric NOT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE history_marking_periods (
    parent_id integer,
    mp_type character(20),
    name character(30),
    post_end_date date,
    school_id integer,
    syear integer,
    marking_period_id integer,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE `honor_roll` (
`id` INT NOT NULL AUTO_INCREMENT ,
`school_id` INT NOT NULL ,
`syear` INT(4) NOT NULL ,
`title` VARCHAR( 100 ) NOT NULL ,
`value` VARCHAR( 100 ) NULL ,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL,
PRIMARY KEY (  `id` )
) ENGINE = InnoDB ;

CREATE TABLE lunch_period (
    student_id numeric,
    school_date date,
    period_id numeric,
    attendance_code numeric,
    attendance_teacher_code numeric,
    attendance_reason character varying(100),
    admin character varying(1),
    course_period_id numeric,
    marking_period_id integer,
    lunch_period character varying(100),
    table_name numeric,
    comment varchar(100) default NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `missing_attendance` (
  `school_id` int(11) NOT NULL,
  `syear` varchar(6) NOT NULL,
  `school_date` date NOT NULL,
  `course_period_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `secondary_teacher_id` int(11) default NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE school_quarters (
    marking_period_id integer NOT NULL,
    syear numeric(4,0),
    school_id numeric,
    semester_id numeric,
    title character varying(50),
    short_name character varying(10),
    sort_order numeric,
    start_date date,
    end_date date,
    post_start_date date,
    post_end_date date,
    does_grades character varying(1),
    does_exam character varying(1),
    does_comments character varying(1),
    rollover_id numeric,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE school_semesters (
    marking_period_id integer NOT NULL,
    syear numeric(4,0),
    school_id numeric,
    year_id numeric,
    title character varying(50),
    short_name character varying(10),
    sort_order numeric,
    start_date date,
    end_date date,
    post_start_date date,
    post_end_date date,
    does_grades character varying(1),
    does_exam character varying(1),
    does_comments character varying(1),
    rollover_id numeric,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE school_years (
    marking_period_id integer NOT NULL,
    syear numeric(4,0),
    school_id numeric,
    title character varying(50),
    short_name character varying(10),
    sort_order numeric,
    start_date date,
    end_date date,
    post_start_date date,
    post_end_date date,
    does_grades character varying(1),
    does_exam character varying(1),
    does_comments character varying(1),
    rollover_id numeric,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


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
    FROM history_marking_periods;




CREATE TABLE marking_period_id_generator (
    id INTEGER NOT NULL AUTO_INCREMENT KEY
);
DROP FUNCTION IF EXISTS `fn_marking_period_seq`;
DELIMITER $$
CREATE FUNCTION fn_marking_period_seq () RETURNS INT
BEGIN
  INSERT INTO marking_period_id_generator VALUES(NULL);
 -- DELETE FROM marking_period_id_generator;
RETURN LAST_INSERT_ID();
END$$
DELIMITER ;
ALTER TABLE marking_period_id_generator AUTO_INCREMENT=12;

-- ALTER TABLE `marking_periods` ADD PRIMARY KEY(`marking_period_id`);
 --ALTER TABLE `marking_periods` CHANGE `marking_period_id` `marking_period_id` INT(8) NOT NULL AUTO_INCREMENT ;


/*CREATE TABLE old_course_weights (
    syear numeric(4,0),
    school_id numeric,
    course_id numeric,
    course_weight character varying(10),
    gpa_multiplier numeric,
    year_fraction numeric,
    rollover_id numeric
)ENGINE=InnoDB;*/


CREATE TABLE `people` (
 `staff_id` int(11) NOT NULL AUTO_INCREMENT,
 `current_school_id` decimal(10,0) DEFAULT NULL,
 `title` varchar(5) DEFAULT NULL,
 `first_name` varchar(100) DEFAULT NULL,
 `last_name` varchar(100) DEFAULT NULL,
 `middle_name` varchar(100) DEFAULT NULL,
 `home_phone` varchar(255) DEFAULT NULL,
 `work_phone` varchar(255) DEFAULT NULL,
 `cell_phone` varchar(255) DEFAULT NULL,
 `email` varchar(100) DEFAULT NULL,
 `custody` varchar(1) DEFAULT NULL,
 `profile` varchar(30) DEFAULT NULL,
 `profile_id` decimal(10,0) DEFAULT NULL,
 `is_disable` varchar(10) DEFAULT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL,
 PRIMARY KEY (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `people_field_categories` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `sort_order` decimal(10,0) DEFAULT NULL,
  `include` varchar(100) DEFAULT NULL,
  `admin` char(1) DEFAULT NULL,
  `teacher` char(1) DEFAULT NULL,
  `parent` char(1) DEFAULT NULL,
  `none` char(1) DEFAULT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;




CREATE TABLE people_fields (
    id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    type character varying(10),
    search character varying(1),
    title character varying(30),
    sort_order numeric,
    select_options character varying(10000),
    category_id numeric,
    system_field character(1),
    required character varying(1),
    default_selection character varying(255),
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;




ALTER TABLE people_fields AUTO_INCREMENT=1;





CREATE TABLE portal_notes (
    id int(8) not null auto_increment primary key,
    school_id numeric,
    syear numeric(4,0),
    title character varying(255),
    content longtext,
    sort_order numeric,
    published_user numeric,
   last_updated timestamp ,
    start_date date,
    end_date date,
    published_profiles character varying(255),
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;

ALTER TABLE portal_notes AUTO_INCREMENT=1;


CREATE TABLE profile_exceptions (
    profile_id numeric,
    modname character varying(255),
    can_use character varying(1),
    can_edit character varying(1),
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE program_config (
    syear numeric(4,0),
    school_id numeric,
    program character varying(255),
    title character varying(100),
    value character varying(100),
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE program_user_config (
    user_id numeric NOT NULL,
    school_id numeric NULL,
    program character varying(255),
    title character varying(100),
    value character varying(100),
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE report_card_comments (
    id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    syear numeric(4,0),
    school_id numeric,
    course_id numeric,
    sort_order numeric,
    title text,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


ALTER TABLE report_card_comments AUTO_INCREMENT=1;


CREATE TABLE `report_card_grade_scales` (
 `id` int(8) NOT NULL AUTO_INCREMENT,
 `syear` decimal(4,0) DEFAULT NULL,
 `school_id` decimal(10,0) NOT NULL,
 `title` varchar(25) DEFAULT NULL,
 `comment` varchar(100) DEFAULT NULL,
 `sort_order` decimal(10,0) DEFAULT NULL,
 `rollover_id` decimal(10,0) DEFAULT NULL,
 `gp_scale` decimal(10,3) DEFAULT NULL,
 `gpa_cal` enum('Y','N') NOT NULL DEFAULT 'Y',
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




ALTER TABLE report_card_grade_scales AUTO_INCREMENT=1;


CREATE TABLE report_card_grades (
    id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    syear numeric(4,0),
    school_id numeric,
    title character varying(15),
    sort_order numeric,
    gpa_value numeric(4,2),
    break_off numeric,
    comment longtext,
    grade_scale_id numeric,
    unweighted_gp numeric(4,2),
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
   )ENGINE=InnoDB;


ALTER TABLE report_card_grades AUTO_INCREMENT=1;


CREATE TABLE schedule (
    syear numeric(4,0) NOT NULL,
    school_id numeric,
    student_id numeric NOT NULL,
    start_date date NOT NULL,
    end_date date,
    modified_date date,
    modified_by character varying(255),
    course_id numeric NOT NULL,
    course_weight character varying(10),
    course_period_id numeric NOT NULL,
    mp character varying(3),
    marking_period_id integer,
    scheduler_lock character varying(1),
	dropped character varying(1) DEFAULT 'N',
    id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE schedule_requests (
    syear numeric(4,0),
    school_id numeric,
    request_id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    student_id numeric,
    subject_id numeric,
    course_id numeric,
    course_weight character varying(10),
    marking_period_id integer,
    priority numeric,
    with_teacher_id numeric,
    not_teacher_id numeric,
    with_period_id numeric,
    not_period_id numeric,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


ALTER TABLE schedule_requests AUTO_INCREMENT=1;


ALTER TABLE schedule AUTO_INCREMENT=1;


ALTER TABLE school_gradelevels AUTO_INCREMENT=1;


CREATE TABLE school_periods (
    period_id int(8) not null auto_increment primary key,
    syear numeric(4,0),
    school_id numeric,
    sort_order numeric,
    title character varying(100),
    short_name character varying(10),
    length numeric,
    block character varying(10),
    ignore_scheduling character varying(10), 
    attendance character varying(1),
    rollover_id numeric,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


ALTER TABLE school_periods AUTO_INCREMENT=1;


CREATE TABLE school_progress_periods (
    marking_period_id integer NOT NULL,
    syear numeric(4,0),
    school_id numeric,
    quarter_id numeric,
    title character varying(50),
    short_name character varying(10),
    sort_order numeric,
    start_date date,
    end_date date,
    post_start_date date,
    post_end_date date,
    does_grades character varying(1),
    does_exam character varying(1),
    does_comments character varying(1),
    rollover_id numeric,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE schools (
    id int(8) not null auto_increment primary key,
	syear numeric(4,0),
    title character varying(100),
    address character varying(100),
    city character varying(100),
    state character varying(100),
    zipcode character varying(255),
    area_code numeric(3,0),
    phone character varying(30),
    principal character varying(100),
    www_address character varying(100),
    e_mail character varying(100),
    reporting_gp_scale numeric(10,3),
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;

--
-- Table structure for table `school_custom_fields`
--

CREATE TABLE IF NOT EXISTS `school_custom_fields` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `type` varchar(10) DEFAULT NULL,
  `search` varchar(1) DEFAULT NULL,
  `title` varchar(30) DEFAULT NULL,
  `sort_order` decimal(10,0) DEFAULT NULL,
  `select_options` varchar(10000) DEFAULT NULL,
  `category_id` decimal(10,0) DEFAULT NULL,
  `system_field` char(1) DEFAULT NULL,
  `required` varchar(1) DEFAULT NULL,
  `default_selection` varchar(255) DEFAULT NULL,
  `hide` varchar(1) DEFAULT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE system_preference (
id INT( 8 ) NOT NULL AUTO_INCREMENT PRIMARY KEY,
school_id INT( 8 ) NOT NULL,
full_day_minute INT( 8 ),
half_day_minute INT( 8 ),
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


--
-- Table structure for table `staff`
--

CREATE TABLE IF NOT EXISTS `staff` (
  `staff_id` int(8) NOT NULL AUTO_INCREMENT,
  `current_school_id` decimal(10,0) DEFAULT NULL,
  `title` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `first_name` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `last_name` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `middle_name` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `phone` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `profile` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `homeroom` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `profile_id` decimal(10,0) DEFAULT NULL,
  `primary_language_id` int(8) DEFAULT NULL,
  `gender` varchar(8) CHARACTER SET utf8 DEFAULT NULL,
  `ethnicity_id` int(8) DEFAULT NULL,
  `birthdate` date  NULL,
  `alternate_id` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `name_suffix` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `second_language_id` int(8) DEFAULT NULL,
  `third_language_id` int(8) DEFAULT NULL,
  `is_disable` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `physical_disability` varchar(1) CHARACTER SET utf8 DEFAULT NULL,
  `disability_desc` VARCHAR( 225 ) DEFAULT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`staff_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;







CREATE TABLE `staff_field_categories` (
 `id` int(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
 `title` varchar(100) DEFAULT NULL,
 `sort_order` decimal(10,0) DEFAULT NULL,
 `include` varchar(100) DEFAULT NULL,
 `admin` char(1) DEFAULT NULL,
 `teacher` char(1) DEFAULT NULL,
 `parent` char(1) DEFAULT NULL,
 `none` char(1) DEFAULT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE staff_field_categories AUTO_INCREMENT=1;


CREATE TABLE staff_fields (
    id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    type character varying(10),
    search character varying(1),
    title character varying(30),
    sort_order numeric,
    select_options character varying(10000),
    category_id numeric,
    system_field character(1),
    required character varying(1),
    default_selection character varying(255),
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;



ALTER TABLE staff_fields AUTO_INCREMENT=1;


CREATE TABLE student_eligibility_activities (
    syear numeric(4,0),
    student_id numeric,
    activity_id numeric,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE student_enrollment_codes (
    id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    syear numeric(4,0),
    title character varying(100),
    short_name character varying(10),
    type character varying(4),
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


ALTER TABLE student_enrollment_codes AUTO_INCREMENT=1;


CREATE TABLE student_field_categories (
    id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title character varying(100),
    sort_order numeric,
    include character varying(100),
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


ALTER TABLE student_field_categories AUTO_INCREMENT=1;


CREATE TABLE student_gpa_calculated (
    student_id numeric,
    marking_period_id integer,
    mp character varying(4),
    gpa decimal(10,2),
    weighted_gpa decimal(10,2),
    unweighted_gpa decimal(10,2),
    class_rank numeric,
    grade_level_short character varying(100) default NULL,
    cgpa decimal(10,2) DEFAULT NULL,
    cum_unweighted_factor decimal(10,6),
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


/*CREATE TABLE student_gpa_running (
    student_id numeric,
    marking_period_id integer,
    gpa_points decimal(10,2),
    gpa_points_weighted decimal(10,2),
    divisor decimal(10,2),
	credit_earned decimal(10,2),
    cgpa decimal(10,2) DEFAULT NULL
)ENGINE=InnoDB;*/


CREATE TABLE student_immunization (
    id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    student_id numeric,
    type character varying(25),
    medical_date date,
    comments longtext,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE student_medical_alerts (
    id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    student_id numeric,
    title text,
    alert_date date,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;

ALTER TABLE student_medical_alerts AUTO_INCREMENT=1;


ALTER TABLE student_immunization AUTO_INCREMENT=1;


CREATE TABLE student_medical_visits (
    id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    student_id numeric,
    school_date date,
    time_in character varying(20),
    time_out character varying(20),
    reason text,
    result text,
    comments longtext,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;

ALTER TABLE student_medical_visits AUTO_INCREMENT=1;

CREATE TABLE student_medical_notes (
    id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    student_id numeric NOT NULL,
    doctors_note_date date,
    doctors_note_comments longtext,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;

CREATE TABLE student_mp_comments (
    id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    student_id numeric NOT NULL,
    syear numeric(4,0) NOT NULL,
    marking_period_id integer NOT NULL,
    staff_id integer,
    comment longtext,
    comment_date date,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


/*CREATE TABLE student_mp_stats (
    student_id integer NOT NULL,
    marking_period_id integer NOT NULL,
    cum_weighted_factor decimal(10,6),
    cum_unweighted_factor decimal(10,6),
    cum_rank integer,
    mp_rank integer,
    sum_weighted_factors decimal(10,6),
    sum_unweighted_factors decimal(10,6),
    count_weighted_factors integer,
    count_unweighted_factors integer,
    grade_level_short character varying(3),
    class_size integer
)ENGINE=InnoDB*/


CREATE TABLE student_report_card_comments (
    syear numeric(4,0) NOT NULL,
    school_id numeric,
    student_id numeric NOT NULL,
    course_period_id numeric NOT NULL,
    report_card_comment_id numeric NOT NULL,
    comment character varying(1),
    marking_period_id integer NOT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;

CREATE TABLE `student_report_card_grades` (
 `syear` decimal(4,0) DEFAULT NULL,
 `school_id` decimal(10,0) DEFAULT NULL,
 `student_id` decimal(10,0) NOT NULL,
 `course_period_id` decimal(10,0) DEFAULT NULL,
 `report_card_grade_id` decimal(10,0) DEFAULT NULL,
 `report_card_comment_id` decimal(10,0) DEFAULT NULL,
 `comment` longtext,
 `grade_percent` decimal(5,2) DEFAULT NULL,
 `marking_period_id` varchar(10) NOT NULL,
 `grade_letter` varchar(5) DEFAULT NULL,
 `weighted_gp` decimal(10,3) DEFAULT NULL,
 `unweighted_gp` decimal(10,3) DEFAULT NULL,
 `gp_scale` decimal(10,3) DEFAULT NULL,
 `gpa_cal` varchar(2) DEFAULT NULL,
 `credit_attempted` decimal(10,3) DEFAULT NULL,
 `credit_earned` decimal(10,3) DEFAULT NULL,
 `credit_category` varchar(10) DEFAULT NULL,
 `course_code` varchar(100) DEFAULT NULL,
 `course_title` text,
 `id` int(8) NOT NULL AUTO_INCREMENT,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE student_report_card_grades AUTO_INCREMENT=1;

CREATE TABLE students (
    student_id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    last_name character varying(50) NOT NULL,
    first_name character varying(50) NOT NULL,
    middle_name character varying(50),
    name_suffix character varying(3),
    gender character varying(255),
    ethnicity character varying(255),
    common_name character varying(255),
    social_security character varying(255),
    birthdate character varying(255),
    language character varying(255),
    estimated_grad_date character varying(255),
    alt_id character varying(50),
    email character varying(50),
    phone character varying(30),
    is_disable varchar(10) default NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;

ALTER TABLE students AUTO_INCREMENT=1;





CREATE TABLE students_join_people (
   id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
   student_id numeric NOT NULL,
   person_id numeric(10,0) NOT NULL,
   is_emergency varchar(10) DEFAULT NULL,
   emergency_type varchar(100) DEFAULT NULL,
   relationship varchar(100) NOT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;

ALTER TABLE students_join_people AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `user_profiles` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `profile` varchar(30) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE user_profiles AUTO_INCREMENT=1;


CREATE TABLE hacking_log (
    host_name character varying(20),
    ip_address character varying(20),
    login_date date,
    version character varying(20),
    php_self character varying(20),
    document_root character varying(100),
    script_name character varying(100),
    modname character varying(100),
    username character varying(20),
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE `teacher_reassignment` (
 `course_period_id` int(11) NOT NULL,
 `teacher_id` int(11) NOT NULL,
 `assign_date` date NOT NULL,
 `modified_date` date NOT NULL,
 `pre_teacher_id` int(11) NOT NULL,
 `modified_by` int(11) NOT NULL,
 `updated` enum('Y','N') NOT NULL DEFAULT 'N',
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `staff_school_relationship` (
  `staff_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `syear` int(4) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  PRIMARY KEY (`staff_id`,`school_id`,`syear`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `medical_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `syear` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `physician` varchar(255) DEFAULT NULL,
  `physician_phone` varchar(255) DEFAULT NULL,
  `preferred_hospital` varchar(255) DEFAULT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `student_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `syear` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `street_address_1` varchar(5000) DEFAULT NULL,
  `street_address_2` varchar(5000) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `zipcode` varchar(255) DEFAULT NULL,
  `bus_pickup` varchar(1) DEFAULT NULL,
  `bus_dropoff` varchar(1) DEFAULT NULL,
  `bus_no` varchar(255) DEFAULT NULL,
  `type` varchar(500) NOT NULL,
  `people_id` int(11) DEFAULT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- Table structure for table `mail_group`
CREATE TABLE IF NOT EXISTS `mail_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `creation_date` datetime NOT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `school_id` int(11) NOT NULL, 
 `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `mail_group` ADD INDEX `mail_group_ind` (`school_id`) USING BTREE;


--
-- Table structure for table `mail_groupmembers`
--

CREATE TABLE IF NOT EXISTS `mail_groupmembers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `profile` varchar(255) NOT NULL,
  `school_id` int(11) NOT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;# MySQL returned an empty result set (i.e. zero rows).
# MySQL returned an empty result set (i.e. zero rows).

ALTER TABLE `mail_groupmembers` ADD INDEX `mail_groupmembers_ind` (`school_id`) USING BTREE;
-- --------------------------------------------------------

--
-- Table structure for table `msg_inbox`
--

CREATE TABLE IF NOT EXISTS `msg_inbox` (
  `mail_id` int(11) NOT NULL AUTO_INCREMENT,
  `to_user` varchar(211) NOT NULL,
  `from_user` varchar(211) NOT NULL,
  `mail_Subject` varchar(211) NULL,
  `mail_body` longtext NOT NULL,
  `mail_datetime` datetime NULL,
  `mail_attachment` varchar(211) NULL,
  `isdraft` int(11) NULL,
  `istrash` varchar(255) NULL,
  `to_multiple_users` varchar(255) NULL,
  `to_cc` varchar(255) NULL,
  `to_cc_multiple` varchar(255) NULL,
  `to_bcc` varchar(255) NULL,
  `to_bcc_multiple` varchar(255) NULL,
  `mail_read_unread` varchar(255) NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`mail_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;# MySQL returned an empty result set (i.e. zero rows).
# MySQL returned an empty result set (i.e. zero rows).


-- --------------------------------------------------------

--
-- Table structure for table `msg_outbox`
--

CREATE TABLE IF NOT EXISTS `msg_outbox` (
  `mail_id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user` varchar(211) NOT NULL,
  `to_user` varchar(211) NOT NULL,
  `mail_subject` varchar(211) NULL,
  `mail_body` longtext NOT NULL,
  `mail_datetime` datetime NULL,
  `mail_attachment` varchar(211) NULL,
  `istrash` int(11) NULL,
  `to_cc` varchar(255) NULL,
  `to_bcc` varchar(255) NULL,
  `to_grpName` varchar(255) NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`mail_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
--
--

CREATE TABLE IF NOT EXISTS `temp_message_filepath_ws` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyval` varchar(100) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE `device_info` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `device_type` varchar(255) CHARACTER SET utf8 NOT NULL,
  `device_token` longtext CHARACTER SET utf8 NOT NULL,
  `device_id` longtext CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `filters` (
  `filter_id` int(11) NOT NULL,
  `filter_name` varchar(255) DEFAULT NULL,
  `school_id` int(11) DEFAULT '0',
  `show_to` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `filter_fields` (
  `filter_field_id` int(11) NOT NULL,
  `filter_id` int(11) DEFAULT NULL,
  `filter_column` varchar(255) DEFAULT NULL,
  `filter_value` longtext
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



ALTER TABLE `filters`
  ADD PRIMARY KEY (`filter_id`);

--
-- Indexes for table `filter_fields`
--
ALTER TABLE `filter_fields`
  ADD PRIMARY KEY (`filter_field_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `filters`
--
ALTER TABLE `filters`
  MODIFY `filter_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `filter_fields`
--
ALTER TABLE `filter_fields`
  MODIFY `filter_field_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;



ALTER TABLE school_calendars AUTO_INCREMENT=1;



ALTER TABLE course_periods AUTO_INCREMENT=1;


-- ALTER TABLE marking_periods AUTO_INCREMENT=1;

--
--
--






ALTER TABLE attendance_calendar
    ADD CONSTRAINT attendance_calendar_pkey PRIMARY KEY (syear, school_id, school_date, calendar_id);


--ALTER TABLE attendance_completed
  -- ADD CONSTRAINT attendance_completed_pkey PRIMARY KEY (staff_id, school_date, period_id);


ALTER TABLE attendance_day
    ADD CONSTRAINT attendance_day_pkey PRIMARY KEY (student_id, school_date);


ALTER TABLE attendance_period
    ADD CONSTRAINT attendance_period_pkey PRIMARY KEY (student_id, school_date, course_period_id,period_id);


-- ALTER TABLE calendar_events
  --  ADD CONSTRAINT calendar_events_pkey PRIMARY KEY (id);


-- ALTER TABLE course_periods
 --   ADD CONSTRAINT course_periods_pkey PRIMARY KEY (course_period_id);


-- ALTER TABLE course_subjects
 --   ADD CONSTRAINT course_subjects_pkey PRIMARY KEY (subject_id);


-- ALTER TABLE courses
   --  ADD CONSTRAINT courses_pkey PRIMARY KEY (course_id);


-- ALTER TABLE custom_fields
 --   ADD CONSTRAINT custom_fields_pkey PRIMARY KEY (id);

-- ALTER TABLE eligibility_activities
 --   ADD CONSTRAINT eligibility_activities_pkey PRIMARY KEY (id);


ALTER TABLE eligibility_completed
    ADD CONSTRAINT eligibility_completed_pkey PRIMARY KEY (staff_id, school_date, period_id);


-- ALTER TABLE gradebook_assignment_types
 --   ADD CONSTRAINT gradebook_assignment_types_pkey PRIMARY KEY (assignment_type_id);


-- ALTER TABLE gradebook_assignments
--    ADD CONSTRAINT gradebook_assignments_pkey PRIMARY KEY (assignment_id);


ALTER TABLE gradebook_grades
    ADD CONSTRAINT gradebook_grades_pkey PRIMARY KEY (student_id, assignment_id, course_period_id);


ALTER TABLE grades_completed
    ADD CONSTRAINT grades_completed_pkey PRIMARY KEY (staff_id, marking_period_id, period_id);


ALTER TABLE history_marking_periods
    ADD CONSTRAINT history_marking_periods_pkey PRIMARY KEY (marking_period_id);






ALTER TABLE school_progress_periods
    ADD CONSTRAINT school_progress_periods_pkey PRIMARY KEY (marking_period_id);



ALTER TABLE school_quarters
    ADD CONSTRAINT school_quarters_pkey PRIMARY KEY (marking_period_id);



ALTER TABLE school_semesters
    ADD CONSTRAINT school_semesters_pkey PRIMARY KEY (marking_period_id);



ALTER TABLE school_years
    ADD CONSTRAINT school_years_pkey PRIMARY KEY (marking_period_id);




ALTER TABLE student_report_card_comments
    ADD CONSTRAINT student_report_card_comments_pkey PRIMARY KEY (syear, student_id, course_period_id, marking_period_id, report_card_comment_id);

ALTER TABLE `staff` ADD `img_name` VARCHAR(255) NULL AFTER `disability_desc`;
ALTER TABLE `staff` ADD `img_content` LONGBLOB NULL AFTER `img_name`;
-- ALTER TABLE `staff_school_relationship` ADD `start_date` DATE NOT NULL ,
-- ADD `end_date` DATE NOT NULL ;





CREATE INDEX address_desc_ind2  USING btree ON custom_fields(type);


CREATE INDEX address_fields_ind3  USING btree ON custom_fields(category_id);


CREATE INDEX attendance_code_categories_ind1  USING btree ON attendance_code_categories(id);


CREATE INDEX attendance_code_categories_ind2  USING btree ON attendance_code_categories(syear, school_id);


CREATE INDEX attendance_codes_ind2  USING btree ON attendance_codes(syear, school_id);


CREATE INDEX attendance_codes_ind3  USING btree ON attendance_codes(short_name);


CREATE INDEX attendance_period_ind1  USING btree ON attendance_period(student_id);


CREATE INDEX attendance_period_ind2  USING btree ON attendance_period(period_id);


CREATE INDEX attendance_period_ind3  USING btree ON attendance_period(attendance_code);


CREATE INDEX attendance_period_ind4  USING btree ON attendance_period(school_date);


CREATE INDEX attendance_period_ind5  USING btree ON attendance_period(attendance_code);


CREATE INDEX course_periods_ind1  USING btree ON course_periods(syear);


CREATE INDEX course_periods_ind2  USING btree ON course_periods(course_id, course_weight, syear, school_id);


CREATE INDEX course_periods_ind3  USING btree ON course_periods(course_period_id);


-- CREATE INDEX course_periods_ind4  USING btree ON course_periods(period_id);


CREATE INDEX course_periods_ind5  USING btree ON course_periods(parent_id);


CREATE INDEX course_subjects_ind1  USING btree ON course_subjects(syear, school_id, subject_id);


CREATE INDEX courses_ind1  USING btree ON courses(course_id, syear);


CREATE INDEX courses_ind2  USING btree ON courses(subject_id);


CREATE INDEX custom_desc_ind  USING btree ON custom_fields(id);


CREATE INDEX custom_desc_ind2  USING btree ON custom_fields(type);


CREATE INDEX custom_fields_ind3  USING btree ON custom_fields(category_id);


CREATE INDEX eligibility_activities_ind1  USING btree ON eligibility_activities(school_id, syear);


CREATE INDEX eligibility_ind1  USING btree ON eligibility(student_id, course_period_id, school_date);


CREATE INDEX gradebook_assignment_types_ind1  USING btree ON gradebook_assignments(staff_id, course_id);


CREATE INDEX gradebook_assignments_ind1  USING btree ON gradebook_assignments(staff_id, marking_period_id);


CREATE INDEX gradebook_assignments_ind2  USING btree ON gradebook_assignments(course_id, course_period_id);


CREATE INDEX gradebook_assignments_ind3  USING btree ON gradebook_assignments(assignment_type_id);


CREATE INDEX gradebook_grades_ind1  USING btree ON gradebook_grades(assignment_id);


CREATE INDEX history_marking_period_ind1  USING btree ON history_marking_periods(school_id);


CREATE INDEX history_marking_period_ind2  USING btree ON history_marking_periods(syear);


CREATE INDEX history_marking_period_ind3  USING btree ON history_marking_periods(mp_type);


CREATE INDEX name  USING btree ON students(last_name, first_name, middle_name);


--CREATE INDEX people_1  USING btree ON people(last_name, first_name);


--CREATE INDEX people_3  USING btree ON people(person_id, last_name, first_name, middle_name);


CREATE INDEX people_desc_ind  USING btree ON people_fields(id);


CREATE INDEX people_desc_ind2  USING btree ON custom_fields(type);


CREATE INDEX people_fields_ind3  USING btree ON custom_fields(category_id);





CREATE INDEX program_config_ind1  USING btree ON program_config(program, school_id, syear);


CREATE INDEX program_user_config_ind1  USING btree ON program_user_config(user_id, program);


--CREATE INDEX relations_meets_2  USING btree ON students_join_people(person_id);


--CREATE INDEX relations_meets_5  USING btree ON students_join_people(id);


--CREATE INDEX relations_meets_6  USING btree ON students_join_people(custody, emergency);


CREATE INDEX report_card_comments_ind1  USING btree ON report_card_comments(syear, school_id);


CREATE INDEX report_card_grades_ind1  USING btree ON report_card_grades(syear, school_id);


CREATE INDEX schedule_ind1  USING btree ON schedule(course_id, course_weight);


CREATE INDEX schedule_ind2  USING btree ON schedule(course_period_id);


CREATE INDEX schedule_ind3  USING btree ON schedule(student_id, marking_period_id, start_date, end_date);


CREATE INDEX schedule_ind4  USING btree ON schedule(syear, school_id);


CREATE INDEX schedule_requests_ind1  USING btree ON schedule_requests(student_id, course_id, course_weight, syear, school_id);


CREATE INDEX schedule_requests_ind2  USING btree ON schedule_requests(syear, school_id);


CREATE INDEX schedule_requests_ind3  USING btree ON schedule_requests(course_id, course_weight, syear, school_id);


CREATE INDEX schedule_requests_ind4  USING btree ON schedule_requests(with_teacher_id);


CREATE INDEX schedule_requests_ind5  USING btree ON schedule_requests(not_teacher_id);


CREATE INDEX schedule_requests_ind6  USING btree ON schedule_requests(with_period_id);


CREATE INDEX schedule_requests_ind7  USING btree ON schedule_requests(not_period_id);


CREATE INDEX schedule_requests_ind8  USING btree ON schedule_requests(request_id);


CREATE INDEX school_gradelevels_ind1  USING btree ON school_gradelevels(school_id);


CREATE INDEX school_periods_ind1  USING btree ON school_periods(period_id, syear);


CREATE INDEX school_progress_periods_ind1  USING btree ON school_progress_periods(quarter_id);


CREATE INDEX school_progress_periods_ind2  USING btree ON school_progress_periods(syear, school_id, start_date, end_date);


CREATE INDEX school_quarters_ind1  USING btree ON school_quarters(semester_id);


CREATE INDEX school_quarters_ind2  USING btree ON school_quarters(syear, school_id, start_date, end_date);


CREATE INDEX school_semesters_ind1  USING btree ON school_semesters(year_id);


CREATE INDEX school_semesters_ind2  USING btree ON school_semesters(syear, school_id, start_date, end_date);


CREATE INDEX school_years_ind2  USING btree ON school_years(syear, school_id, start_date, end_date);


CREATE INDEX schools_ind1  USING btree ON schools(syear);

CREATE INDEX staff_desc_ind1  USING btree ON staff_fields(id);


CREATE INDEX staff_desc_ind2  USING btree ON staff_fields(type);


CREATE INDEX staff_fields_ind3  USING btree ON staff_fields(category_id);


CREATE INDEX staff_ind2  USING btree ON staff(last_name, first_name);





CREATE INDEX student_eligibility_activities_ind1  USING btree ON student_eligibility_activities(student_id);


CREATE INDEX student_enrollment_1  USING btree ON student_enrollment(student_id, enrollment_code);


CREATE INDEX student_enrollment_2  USING btree ON student_enrollment(grade_id);


CREATE INDEX student_enrollment_3  USING btree ON student_enrollment(syear, student_id, school_id, grade_id);


CREATE INDEX student_enrollment_6  USING btree ON student_enrollment(syear, student_id,start_date, end_date);


CREATE INDEX student_enrollment_7  USING btree ON student_enrollment(school_id);


CREATE INDEX student_gpa_calculated_ind1  USING btree ON student_gpa_calculated(marking_period_id, student_id);


--CREATE INDEX student_gpa_running_ind1  USING btree ON student_gpa_running(marking_period_id, student_id);


CREATE INDEX student_medical_alerts_ind1  USING btree ON student_medical_alerts(student_id);


CREATE INDEX student_medical_ind1  USING btree ON student_immunization(student_id);


CREATE INDEX student_medical_visits_ind1  USING btree ON student_medical_visits(student_id);


CREATE INDEX student_report_card_comments_ind1  USING btree ON student_report_card_comments(school_id);


CREATE INDEX student_report_card_grades_ind1  USING btree ON student_report_card_grades(school_id);


CREATE INDEX student_report_card_grades_ind2  USING btree ON student_report_card_grades(student_id);


CREATE INDEX student_report_card_grades_ind3  USING btree ON student_report_card_grades(course_period_id);


CREATE INDEX student_report_card_grades_ind4  USING btree ON student_report_card_grades(marking_period_id);




--CREATE INDEX students_join_people_ind1  USING btree ON students_join_people(student_id);




--
-- TABLE STRUCTURE FOR TABLE history_school
--
CREATE TABLE IF NOT EXISTS `history_school` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `marking_period_id` int(11) NOT NULL,
  `school_name` varchar(100) NOT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL,
   PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
--
--

CREATE VIEW course_details AS
  SELECT cp.school_id, cp.syear, cp.marking_period_id, c.subject_id,
	  cp.course_id, cp.course_period_id, cp.teacher_id,cp. secondary_teacher_id, c.title AS course_title,
	  cp.title AS cp_title, cp.grade_scale_id, cp.mp, cp.credits,cp.begin_date,cp.end_date
  FROM course_periods cp, courses c WHERE (cp.course_id = c.course_id);

CREATE VIEW enroll_grade AS
  SELECT e.id, e.syear, e.school_id, e.student_id, e.start_date, e.end_date, sg.short_name, sg.title
  FROM student_enrollment e, school_gradelevels sg WHERE (e.grade_id = sg.id);

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
    INNER JOIN schools s ON s.id = mp.school_id;


-- ****************** For storing all log details ***************************
CREATE TABLE login_records
(
  syear numeric(5),
  first_name character varying(100),
  last_name character varying(100),
  profile character varying(50),
  user_name character varying(100),
  login_time timestamp,
  faillog_count numeric(4),
  staff_id numeric(10),
  id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  faillog_time varchar(255) DEFAULT NULL,
  ip_address character varying(20),
  status character varying(50),
  school_id DECIMAL( 10 ) NULL,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE `login_authentication` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `user_id` int(11) NOT NULL,
 `profile_id` int(11) NOT NULL,
 `username` varchar(255) DEFAULT NULL,
 `password` varchar(255) DEFAULT NULL,
 `last_login` datetime DEFAULT NULL,
 `failed_login` int(3) NOT NULL DEFAULT '0',
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `COMPOSITE` (`user_id`,`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ******************** For Creating Login Seq **************************

ALTER TABLE login_records AUTO_INCREMENT=1;
-- ********************* Log Maintain Table *****************************
CREATE TABLE log_maintain
(
  id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  value numeric(30),
  session_id character varying(100),
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


-- *********************** Log Maintain Sequence ************************


ALTER TABLE log_maintain AUTO_INCREMENT=1;

--
-- Table structure for table  system_preference_misc
--

CREATE TABLE system_preference_misc (
  fail_count decimal(5,0) NOT NULL default '3',
  activity_days decimal(5,0) NOT NULL default '30',
  system_maintenance_switch char(1) character set utf8 collate utf8_bin default NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE student_goal
(
  goal_id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  student_id numeric NOT NULL,
  goal_title character varying(100),
  start_date date,
  end_date date,
  goal_description text,
  school_id numeric(10),
  syear numeric(10),
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


CREATE TABLE student_goal_progress
(
  progress_id INT(8) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  goal_id numeric NOT NULL,
  student_id numeric NOT NULL,
  start_date date,
  progress_name text NOT NULL,
  proficiency character varying(100) NOT NULL,
  progress_description text NOT NULL,
  course_period_id numeric(10),
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL
)ENGINE=InnoDB;


ALTER TABLE attendance_period DROP PRIMARY KEY ,ADD PRIMARY KEY (student_id,school_date,period_id);

ALTER TABLE attendance_completed ADD cpv_id INT NOT NULL AFTER course_period_id; 

ALTER TABLE student_goal AUTO_INCREMENT=1;





ALTER TABLE student_goal_progress AUTO_INCREMENT=1;

--
-- TABLE STRUCTURE FOR TABLE login_message
--

CREATE TABLE login_message (
  id INT(8) NOT NULL AUTO_INCREMENT,
  message longtext DEFAULT NULL,
display char(1) character set utf8 collate utf8_bin default NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `ethnicity`
--

CREATE TABLE IF NOT EXISTS `ethnicity` (
  `ethnicity_id` int(8) NOT NULL AUTO_INCREMENT,
  `ethnicity_name` varchar(255) NOT NULL,
  `sort_order` int(8) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date time ethnicity record modified',
 `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ethnicity_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- Table structure for table `language`
--

CREATE TABLE IF NOT EXISTS `language` (
  `language_id` int(8) NOT NULL AUTO_INCREMENT,
  `language_name` varchar(127) NOT NULL,
  `sort_order` int(8) DEFAULT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`language_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

--
-- Table structure for table `staff_address`
--

CREATE TABLE IF NOT EXISTS `staff_address` (
  `staff_address_id` int(8) NOT NULL AUTO_INCREMENT,
  `staff_id` int(8) NOT NULL,
  `staff_address1_primary` text NOT NULL,
  `staff_address2_primary` text,
  `staff_city_primary` varchar(255) NOT NULL,
  `staff_state_primary` varchar(255) NOT NULL,
  `staff_zip_primary` varchar(255) NOT NULL,
  `staff_address1_mail` text NOT NULL,
  `staff_address2_mail` text,
  `staff_city_mail` varchar(255) NOT NULL,
  `staff_state_mail` varchar(255) NOT NULL,
  `staff_zip_mail` varchar(255) NOT NULL,
  `last_update` datetime NOT NULL,
  `staff_pobox_mail` varchar(255) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date time staff address record modified',
 `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`staff_address_id`),
  UNIQUE KEY `staff_id` (`staff_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `staff_school_info`
--

CREATE TABLE IF NOT EXISTS `staff_school_info` (
  `staff_school_info_id` int(8) NOT NULL AUTO_INCREMENT,
  `staff_id` int(8) NOT NULL,
  `category` varchar(255) NOT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `home_school` int(8) NOT NULL,
  `opensis_access` char(1) NOT NULL DEFAULT 'N',
  `opensis_profile` varchar(255) DEFAULT NULL,
  `school_access` varchar(255) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date and time staff school info was modified',
  `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`staff_school_info_id`),
  UNIQUE KEY `staff_id` (`staff_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;




--
-- Table structure for table `staff_certification`
--

CREATE TABLE IF NOT EXISTS `staff_certification` (
  `staff_certification_id` int(8) NOT NULL AUTO_INCREMENT,
  `staff_id` int(8) NOT NULL,
  `staff_certification_date` date DEFAULT NULL,
  `staff_certification_expiry_date` date DEFAULT NULL,
  `staff_certification_code` varchar(127) DEFAULT NULL,
  `staff_certification_short_name` varchar(127) DEFAULT NULL,
  `staff_certification_name` varchar(255) DEFAULT NULL,
  `staff_primary_certification_indicator` char(1) DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `staff_certification_description` text,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`staff_certification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `staff_contact`
--

CREATE TABLE IF NOT EXISTS `staff_contact` (
  `staff_phone_id` int(8) NOT NULL AUTO_INCREMENT,
  `staff_id` int(8) NOT NULL,
  `last_update` datetime NOT NULL,
  `staff_home_phone` varchar(62) DEFAULT NULL,
  `staff_mobile_phone` varchar(62) DEFAULT NULL,
  `staff_work_phone` varchar(62) DEFAULT NULL,
  `staff_work_email` varchar(127) DEFAULT NULL,
  `staff_personal_email` varchar(127) DEFAULT NULL,
`last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`staff_phone_id`),
  UNIQUE KEY `staff_id` (`staff_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `staff_emergency_contact`
--

CREATE TABLE IF NOT EXISTS `staff_emergency_contact` (
  `staff_emergency_contact_id` int(8) NOT NULL AUTO_INCREMENT,
  `staff_id` int(8) NOT NULL,
  `staff_emergency_first_name` varchar(255) NOT NULL,
  `staff_emergency_last_name` varchar(255) NOT NULL,
  `staff_emergency_relationship` varchar(255) NOT NULL,
  `staff_emergency_home_phone` varchar(64) DEFAULT NULL,
  `staff_emergency_mobile_phone` varchar(64) DEFAULT NULL,
  `staff_emergency_work_phone` varchar(64) DEFAULT NULL,
  `staff_emergency_email` varchar(255) DEFAULT NULL,
 `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`staff_emergency_contact_id`),
  UNIQUE KEY `staff_id` (`staff_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE  `student_enrollment` ADD  `section_id` VARCHAR( 255 ) NULL AFTER  `grade_id`;

CREATE TABLE IF NOT EXISTS `school_gradelevel_sections` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `school_id` decimal(10,0) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `sort_order` decimal(10,0) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `school_gradelevels_ind1` (`school_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `user_file_upload` (
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `students` CHANGE `language` `language_id` INT(8) NULL DEFAULT NULL;

ALTER TABLE `students` CHANGE `ethnicity` `ethnicity_id` INT(11) NULL DEFAULT NULL;


ALTER TABLE `missing_attendance` ADD KEY `idx_appstart_check` (`course_period_id`,`period_id`,`syear`,`school_id`,`school_date`);

ALTER TABLE `missing_attendance` ADD KEY `idx_missing_attendance_syear` (`syear`);

ALTER TABLE `login_authentication` ADD KEY `idx_login_authentication_username_password` (`username`,`password`);

ALTER TABLE students ADD INDEX `idx_students_search` (`is_disable`) COMMENT 'Student Info -> search all';

ALTER TABLE student_enrollment ADD INDEX `idx_student_search` (`school_id`,`syear`,`start_date`,`end_date`,`drop_code`) COMMENT 'Student Info -> search all';

ALTER TABLE `student_report_card_grades` ADD KEY `student_report_card_grades_ind5` (`report_card_grade_id`);

ALTER TABLE `student_report_card_grades` ADD KEY `student_report_card_grades_ind6` (`report_card_comment_id`);

ALTER TABLE `student_report_card_grades` ADD KEY `idx_srcg_comb1` (`student_id`,`course_period_id`,`marking_period_id`);

ALTER TABLE `student_report_card_grades` ADD KEY `idx_srcg_comb2` (`course_period_id`,`marking_period_id`);