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

