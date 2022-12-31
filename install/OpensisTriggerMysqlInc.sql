DROP TRIGGER IF EXISTS `td_student_report_card_grades`;
DELIMITER $$
CREATE TRIGGER `td_student_report_card_grades`
    AFTER DELETE ON student_report_card_grades
    FOR EACH ROW
	SELECT CALC_GPA_MP(OLD.student_id, OLD.marking_period_id) INTO @return$$
DELIMITER ;

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



DROP TRIGGER IF EXISTS tu_periods;
CREATE TRIGGER tu_periods
    AFTER UPDATE ON school_periods
    FOR EACH ROW
        UPDATE course_period_var SET start_time=NEW.start_time,end_time=NEW.end_time WHERE period_id=NEW.period_id;

DROP TRIGGER IF EXISTS tu_school_years;
CREATE TRIGGER tu_school_years
    AFTER UPDATE ON school_years
    FOR EACH ROW
        UPDATE course_periods SET begin_date=NEW.start_date,end_date=NEW.end_date WHERE marking_period_id=NEW.marking_period_id;

DROP TRIGGER IF EXISTS tu_school_semesters;
CREATE TRIGGER tu_school_semesters
    AFTER UPDATE ON school_semesters
    FOR EACH ROW
        UPDATE course_periods SET begin_date=NEW.start_date,end_date=NEW.end_date WHERE marking_period_id=NEW.marking_period_id;

DROP TRIGGER IF EXISTS tu_school_quarters;
CREATE TRIGGER tu_school_quarters
    AFTER UPDATE ON school_quarters
    FOR EACH ROW
        UPDATE course_periods SET begin_date=NEW.start_date,end_date=NEW.end_date WHERE marking_period_id=NEW.marking_period_id;

DROP TRIGGER IF EXISTS ti_course_period_var;
CREATE TRIGGER ti_course_period_var
    AFTER INSERT ON course_period_var
    FOR EACH ROW
	CALL ATTENDANCE_CALC(NEW.course_period_id);

DROP TRIGGER IF EXISTS tu_course_period_var;
CREATE TRIGGER tu_course_period_var
    AFTER UPDATE ON course_period_var
    FOR EACH ROW
	CALL ATTENDANCE_CALC(NEW.course_period_id);

DROP TRIGGER IF EXISTS td_course_period_var;
CREATE TRIGGER td_course_period_var
    AFTER DELETE ON course_period_var
    FOR EACH ROW
	CALL ATTENDANCE_CALC(OLD.course_period_id);

DROP TRIGGER IF EXISTS tu_course_periods;
DELIMITER $$
CREATE TRIGGER tu_course_periods
    AFTER UPDATE ON course_periods
    FOR EACH ROW
    BEGIN
	CALL ATTENDANCE_CALC(NEW.course_period_id);
    END$$
DELIMITER ;

DROP TRIGGER IF EXISTS td_course_periods;
DELIMITER $$
CREATE TRIGGER td_course_periods
    AFTER DELETE ON course_periods
    FOR EACH ROW
    BEGIN
	DELETE FROM course_period_var WHERE course_period_id=OLD.course_period_id;
    END$$
DELIMITER ;

DROP TRIGGER IF EXISTS ti_schdule;
DELIMITER $$
CREATE TRIGGER ti_schdule
    AFTER INSERT ON schedule
    FOR EACH ROW
    BEGIN
        UPDATE course_periods SET filled_seats=filled_seats+1 WHERE course_period_id=NEW.course_period_id;
	CALL ATTENDANCE_CALC(NEW.course_period_id);
    END$$
DELIMITER ;

DROP TRIGGER IF EXISTS tu_schedule;
CREATE TRIGGER tu_schedule
    AFTER UPDATE ON schedule
    FOR EACH ROW
	CALL ATTENDANCE_CALC(NEW.course_period_id);

DROP TRIGGER IF EXISTS td_schedule;
DELIMITER $$
CREATE TRIGGER td_schedule
    AFTER DELETE ON schedule
    FOR EACH ROW
    BEGIN
        UPDATE course_periods SET filled_seats=filled_seats-1 WHERE course_period_id=OLD.course_period_id AND OLD.dropped='N';
	CALL ATTENDANCE_CALC(OLD.course_period_id);
    END$$
DELIMITER ;

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

DROP TRIGGER IF EXISTS `ti_user_file_upload`;
CREATE TRIGGER `ti_user_file_upload`
    BEFORE INSERT ON user_file_upload
    FOR EACH ROW
        SET NEW.download_id = UUID();
DROP TRIGGER IF EXISTS `tu_login_authentication`;
DELIMITER $$
CREATE TRIGGER `tu_login_authentication` 
    AFTER UPDATE ON `login_authentication`
    FOR EACH ROW BEGIN

        UPDATE `msg_inbox` SET `from_user` = REPLACE(`from_user`, OLD.username, NEW.username) WHERE `from_user` = OLD.username;


        UPDATE `msg_inbox` SET `to_user` = REPLACE(`to_user`, OLD.username, NEW.username) WHERE `to_user` = OLD.username;
        UPDATE `msg_inbox` SET `to_user` = REPLACE(`to_user`, CONCAT(',', OLD.username, ','), CONCAT(',', NEW.username, ',')) WHERE `to_user` LIKE CONCAT ('%,', OLD.username, ',%');
        UPDATE `msg_inbox` SET `to_user` = REPLACE(`to_user`, CONCAT(OLD.username, ','), CONCAT(NEW.username, ',')) WHERE `to_user` LIKE CONCAT(OLD.username, ',%');
        UPDATE `msg_inbox` SET `to_user` = REPLACE(`to_user`, CONCAT(',', OLD.username), CONCAT(',', NEW.username)) WHERE `to_user` LIKE CONCAT('%,', OLD.username);


        UPDATE `msg_inbox` SET `istrash` = REPLACE (`istrash`, OLD.username, NEW.username) WHERE `istrash` = OLD.username;
        UPDATE `msg_inbox` SET `istrash` = REPLACE (`istrash`, CONCAT(',', OLD.username, ','), CONCAT(',', NEW.username, ',')) WHERE `istrash` LIKE CONCAT ('%,', OLD.username, ',%');
        UPDATE `msg_inbox` SET `istrash` = REPLACE (`istrash`, CONCAT(OLD.username, ','), CONCAT(NEW.username, ',')) WHERE `istrash` LIKE CONCAT(OLD.username, ',%');
        UPDATE `msg_inbox` SET `istrash` = REPLACE (`istrash`, CONCAT(',', OLD.username), CONCAT(',', NEW.username)) WHERE `istrash` LIKE CONCAT('%,', OLD.username);


        UPDATE `msg_inbox` SET `to_multiple_users` = REPLACE(`to_multiple_users`, OLD.username, NEW.username) WHERE `to_multiple_users` = OLD.username;
        UPDATE `msg_inbox` SET `to_multiple_users` = REPLACE(`to_multiple_users`, CONCAT(',', OLD.username, ','), CONCAT(',', NEW.username, ',')) WHERE `to_multiple_users` LIKE CONCAT('%,', OLD.username, ',%');
        UPDATE `msg_inbox` SET `to_multiple_users` = REPLACE(`to_multiple_users`, CONCAT(OLD.username, ','), CONCAT(NEW.username, ',')) WHERE `to_multiple_users` LIKE CONCAT(OLD.username, ',%'); 
        UPDATE `msg_inbox` SET `to_multiple_users` = REPLACE(`to_multiple_users` , CONCAT(',', OLD.username), CONCAT(',', NEW.username)) WHERE `to_multiple_users` LIKE CONCAT('%,', OLD.username);
 

        UPDATE `msg_inbox` SET `to_cc` = REPLACE (`to_cc`, OLD.username, NEW.username) WHERE  `to_cc` = OLD.username;
        UPDATE `msg_inbox` SET `to_cc` = REPLACE (`to_cc`, CONCAT(',', OLD.username, ','), CONCAT(',', NEW.username, ',')) WHERE `to_cc` LIKE CONCAT('%,', OLD.username, ',%');
        UPDATE `msg_inbox` SET `to_cc` = REPLACE (`to_cc`, CONCAT(OLD.username, ','), CONCAT(NEW.username, ',')) WHERE `to_cc` LIKE CONCAT (OLD.username, ',%');
        UPDATE `msg_inbox` SET `to_cc` = REPLACE (`to_cc`, CONCAT(',', OLD.username), CONCAT(',', NEW.username)) WHERE `to_cc` LIKE CONCAT('%,', OLD.username);


        UPDATE `msg_inbox` SET `to_cc_multiple` = REPLACE(`to_cc_multiple`, OLD.username, NEW.username) WHERE `to_cc_multiple` = OLD.username;
        UPDATE `msg_inbox` SET `to_cc_multiple` = REPLACE(`to_cc_multiple`, CONCAT(',', OLD.username, ','), CONCAT(',', NEW.username, ',')) WHERE `to_cc_multiple` LIKE CONCAT ('%,', OLD.username, ',%');
        UPDATE `msg_inbox` SET `to_cc_multiple` = REPLACE(`to_cc_multiple`, CONCAT(OLD.username, ','), CONCAT(NEW.username, ',')) WHERE `to_cc_multiple` LIKE CONCAT(OLD.username, ',%'); 
        UPDATE `msg_inbox` SET `to_cc_multiple` = REPLACE(`to_cc_multiple`, CONCAT(',', OLD.username), CONCAT(',', NEW.username)) WHERE `to_cc_multiple` LIKE CONCAT('%,', OLD.username);  


        UPDATE `msg_inbox` SET `to_bcc` = REPLACE(`to_bcc`, OLD.username, NEW.username) WHERE  `to_bcc` = OLD.username;
        UPDATE `msg_inbox` SET `to_bcc` = REPLACE(`to_bcc`, CONCAT(',', OLD.username, ','), CONCAT(',', NEW.username, ',')) WHERE `to_bcc` LIKE CONCAT('%,', OLD.username,',%');
        UPDATE `msg_inbox` SET `to_bcc` = REPLACE(`to_bcc`, CONCAT(OLD.username, ','), CONCAT(NEW.username, ',')) WHERE `to_bcc` LIKE CONCAT(OLD.username, ',%');  
        UPDATE `msg_inbox` SET `to_bcc` = REPLACE(`to_bcc`, CONCAT(',', OLD.username), CONCAT(',', NEW.username)) WHERE `to_bcc` LIKE CONCAT ('%,', OLD.username);


        UPDATE `msg_inbox` SET `to_bcc_multiple` = REPLACE(`to_bcc_multiple`, OLD.username, NEW.username) WHERE `to_bcc_multiple` = OLD.username;
        UPDATE `msg_inbox` SET `to_bcc_multiple` = REPLACE(`to_bcc_multiple`, CONCAT(',', OLD.username, ','), CONCAT(',', NEW.username, ',')) WHERE `to_bcc_multiple` LIKE CONCAT('%,', OLD.username, ',%');
        UPDATE `msg_inbox` SET `to_bcc_multiple` = REPLACE(`to_bcc_multiple`, CONCAT(OLD.username, ','), CONCAT(NEW.username, ',')) WHERE `to_bcc_multiple` LIKE CONCAT(OLD.username, ',%'); 
        UPDATE `msg_inbox` SET `to_bcc_multiple` = REPLACE(`to_bcc_multiple`, CONCAT(',', OLD.username), CONCAT(',', NEW.username)) WHERE `to_bcc_multiple` LIKE CONCAT('%,', OLD.username);
 

        UPDATE `msg_inbox` SET `mail_read_unread`= REPLACE(`mail_read_unread`, OLD.username, NEW.username) WHERE `mail_read_unread` = OLD.username;
        UPDATE `msg_inbox` SET `mail_read_unread`= REPLACE(`mail_read_unread`, CONCAT(',', OLD.username, ','), CONCAT(',', NEW.username, ',')) WHERE `mail_read_unread` LIKE CONCAT('%,', OLD.username, ',%');
        UPDATE `msg_inbox` SET `mail_read_unread`= REPLACE(`mail_read_unread`, CONCAT(OLD.username, ','), CONCAT(NEW.username, ',')) WHERE `mail_read_unread` LIKE CONCAT(OLD.username, ',%');  
        UPDATE `msg_inbox` SET `mail_read_unread`= REPLACE(`mail_read_unread`, CONCAT(',', OLD.username), CONCAT(',', NEW.username)) WHERE `mail_read_unread` LIKE CONCAT('%,', OLD.username);


        UPDATE `msg_outbox` SET `from_user` = REPLACE (`from_user`, OLD.username, NEW.username) WHERE `from_user` = OLD.username;


        UPDATE `msg_outbox` SET `to_user` = REPLACE(`to_user`, OLD.username, NEW.username) WHERE `to_user` = OLD.username;
        UPDATE `msg_outbox` SET `to_user` = REPLACE(`to_user`, CONCAT(',', OLD.username, ','), CONCAT(',', NEW.username, ',')) WHERE `to_user` LIKE CONCAT('%,', OLD.username, ',%');
        UPDATE `msg_outbox` SET `to_user` = REPLACE(`to_user`, CONCAT(OLD.username, ','), CONCAT(NEW.username, ',')) WHERE `to_user` LIKE CONCAT(OLD.username, ',%');  
        UPDATE `msg_outbox` SET `to_user` = REPLACE(`to_user`, CONCAT(',', OLD.username), CONCAT(',', NEW.username)) WHERE `to_user` LIKE CONCAT('%,', OLD.username);


        UPDATE `msg_outbox` SET `to_cc` = REPLACE(`to_cc`, OLD.username, NEW.username) WHERE  `to_cc` = OLD.username;
        UPDATE `msg_outbox` SET `to_cc` = REPLACE(`to_cc`, CONCAT(',', OLD.username, ','), CONCAT(',', NEW.username, ',')) WHERE `to_cc` LIKE CONCAT('%,', OLD.username, ',%');
        UPDATE `msg_outbox` SET `to_cc` = REPLACE(`to_cc`, CONCAT(OLD.username, ','), CONCAT(NEW.username, ',')) WHERE `to_cc` LIKE CONCAT(OLD.username, ',%');
        UPDATE `msg_outbox` SET `to_cc` = REPLACE(`to_cc`, CONCAT(',', OLD.username), CONCAT(',', NEW.username)) WHERE `to_cc` LIKE CONCAT('%,', OLD.username);
 

        UPDATE `msg_outbox` SET `to_bcc` = REPLACE(`to_bcc`, OLD.username, NEW.username) WHERE `to_bcc` = OLD.username;
        UPDATE `msg_outbox` SET `to_bcc` = REPLACE(`to_bcc`, CONCAT(',', OLD.username, ','), CONCAT(',', NEW.username, ',')) WHERE `to_bcc` LIKE CONCAT('%,', OLD.username, ',%');
        UPDATE `msg_outbox` SET `to_bcc` = REPLACE(`to_bcc`, CONCAT(OLD.username, ','), CONCAT(NEW.username, ',')) WHERE `to_bcc` LIKE CONCAT(OLD.username, ',%');  
        UPDATE `msg_outbox` SET `to_bcc` = REPLACE(`to_bcc`, CONCAT(',', OLD.username), CONCAT(',', NEW.username)) WHERE `to_bcc` LIKE CONCAT('%,', OLD.username);
 

        UPDATE `hacking_log` SET `username` = REPLACE(`username`, OLD.username, NEW.username) WHERE `username` = OLD.username;


        UPDATE `login_records` SET `user_name` = REPLACE(`user_name`, OLD.username, NEW.username) WHERE `user_name` = OLD.username;


        UPDATE `mail_group` SET `user_name` = REPLACE(`user_name`, OLD.username, NEW.username) WHERE `user_name` = OLD.username;


        UPDATE `mail_groupmembers` SET `user_name` = REPLACE(`user_name`, OLD.username, NEW.username) WHERE `user_name` =OLD.username;

END$$
DELIMITER ;
