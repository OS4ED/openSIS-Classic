DROP FUNCTION IF EXISTS `SET_CLASS_RANK_MP`;
DELIMITER $$
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
DELIMITER ;

DROP FUNCTION IF EXISTS `CALC_CUM_GPA_MP`;
DELIMITER $$
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

 /* UPDATE student_mp_stats sms
    INNER JOIN tmp t on t.student_id=sms.student_id
  SET
    sms.sum_weighted_factors=t.sum_weighted_factors,
    sms.count_weighted_factors=t.count_weighted_factors,
    sms.sum_unweighted_factors=t.sum_unweighted_factors,
    sms.count_unweighted_factors=t.count_unweighted_factors
  WHERE sms.marking_period_id=mp_id;*/

  /*INSERT INTO student_mp_stats(student_id,marking_period_id,sum_weighted_factors,count_weighted_factors,
    sum_unweighted_factors,count_unweighted_factors,grade_level_short)
  SELECT
      t.student_id,
      mp_id,
      t.sum_weighted_factors,
      t.count_weighted_factors,
      t.sum_unweighted_factors,
      t.count_unweighted_factors,
      t.grade_level_short
    FROM tmp t
    LEFT JOIN student_mp_stats sms ON sms.student_id=t.student_id AND sms.marking_period_id=mp_id
    WHERE sms.student_id IS NULL;*/

  INSERT INTO student_gpa_calculated (student_id,marking_period_id)
  SELECT
      t.student_id,
      mp_id
    FROM tmp t
    LEFT JOIN student_gpa_calculated sms ON sms.student_id=t.student_id AND sms.marking_period_id=mp_id
    WHERE sms.student_id IS NULL;

/*  UPDATE student_mp_stats g
    INNER JOIN (
	SELECT s.student_id,
		SUM(s.weighted_gp/sc.reporting_gp_scale)/COUNT(*) AS cum_weighted_factor,
		SUM(s.unweighted_gp/s.gp_scale)/COUNT(*) AS cum_unweighted_factor
	FROM student_report_card_grades s
	INNER JOIN schools sc ON sc.id=s.school_id
	LEFT JOIN course_periods p ON p.course_period_id=s.course_period_id
	WHERE p.marking_period_id IS NULL OR p.marking_period_id=s.marking_period_id
	GROUP BY student_id) gg ON gg.student_id=g.student_id
    SET g.cum_unweighted_factor=gg.cum_unweighted_factor, g.cum_weighted_factor=gg.cum_weighted_factor;*/

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
/*while not done DO
    IF EXISTS(SELECT student_id FROM student_gpa_running WHERE  student_gpa_running.student_id=student_id) THEN
    UPDATE student_gpa_running gc
               SET gpa_points=gp_points,gpa_points_weighted=gp_points_weighted,gc.divisor=divisor,credit_earned=credit_earned,gc.cgpa=cgpa where gc.student_id=student_id;
    ELSE
        INSERT INTO student_gpa_running(student_id,marking_period_id,gpa_points,gpa_points_weighted, divisor,credit_earned,cgpa)
          VALUES(student_id,mp_id,gp_points,gp_points_weighted,divisor,credit_earned,cgpa);
    END IF;
fetch cur1 into student_id, gp_points,gp_points_weighted,divisor,credit_earned,cgpa;
END WHILE;*/
CLOSE cur1;


RETURN 1;

END$$
DELIMITER ;




DROP FUNCTION IF EXISTS `CALC_GPA_MP`;
DELIMITER $$
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
  AND (eg.START_DATE IS NULL OR eg.START_DATE='0000-00-00'  OR eg.START_DATE<=CURDATE()) AND (eg.END_DATE IS NULL OR eg.END_DATE='0000-00-00'  OR eg.END_DATE>=CURDATE())  
  GROUP BY srcg.student_id,eg.short_name;

  /*IF EXISTS(SELECT NULL FROM student_mp_stats WHERE marking_period_id=mp_id AND student_id=s_id) THEN
    UPDATE student_mp_stats
    SET
      sum_weighted_factors=@sum_weighted_factors,
      count_weighted_factors=@count_weighted_factors,
      sum_unweighted_factors=@sum_unweighted_factors,
      count_unweighted_factors=@count_unweighted_factors
    WHERE marking_period_id=mp_id AND student_id=s_id;
  ELSE
    INSERT INTO student_mp_stats(student_id,marking_period_id,sum_weighted_factors,count_weighted_factors,
        sum_unweighted_factors,count_unweighted_factors,grade_level_short)
      VALUES(s_id,mp_id,@sum_weighted_factors,@count_weighted_factors,@sum_unweighted_factors,
        @count_unweighted_factors,@grade_level_short);
  END IF;

  UPDATE student_mp_stats g
    INNER JOIN (
	SELECT s.student_id,
		SUM(s.weighted_gp/sc.reporting_gp_scale)/COUNT(*) AS cum_weighted_factor,
		SUM(s.unweighted_gp/s.gp_scale)/COUNT(*) AS cum_unweighted_factor
	FROM student_report_card_grades s
	INNER JOIN schools sc ON sc.id=s.school_id
	LEFT JOIN course_periods p ON p.course_period_id=s.course_period_id
	WHERE s.course_period_id IS NOT NULL AND p.marking_period_id IS NULL OR p.marking_period_id=s.marking_period_id
	GROUP BY student_id) gg ON gg.student_id=g.student_id
    SET g.cum_unweighted_factor=gg.cum_unweighted_factor, g.cum_weighted_factor=gg.cum_weighted_factor
    WHERE g.student_id=s_id;*/

  IF NOT EXISTS(SELECT NULL FROM student_gpa_calculated WHERE marking_period_id=mp_id AND student_id=s_id) THEN
    INSERT INTO student_gpa_calculated (student_id,marking_period_id)
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
DELIMITER ;



DROP FUNCTION IF EXISTS `RE_CALC_GPA_MP`;
DELIMITER $$
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
AND (eg.START_DATE IS NULL OR eg.START_DATE='0000-00-00'  OR eg.START_DATE<=CURDATE()) AND (eg.END_DATE IS NULL OR eg.END_DATE='0000-00-00'  OR eg.END_DATE>=CURDATE())
  GROUP BY srcg.student_id,eg.short_name;

  /*IF EXISTS(SELECT NULL FROM student_mp_stats WHERE marking_period_id=mp_id AND student_id=s_id) THEN
    UPDATE student_mp_stats
    SET
      sum_weighted_factors=@sum_weighted_factors,
      count_weighted_factors=@count_weighted_factors,
      sum_unweighted_factors=@sum_unweighted_factors,
      count_unweighted_factors=@count_unweighted_factors
    WHERE marking_period_id=mp_id AND student_id=s_id;
  ELSE
    INSERT INTO student_mp_stats(student_id,marking_period_id,sum_weighted_factors,count_weighted_factors,
        sum_unweighted_factors,count_unweighted_factors,grade_level_short)
      VALUES(s_id,mp_id,@sum_weighted_factors,@count_weighted_factors,@sum_unweighted_factors,
        @count_unweighted_factors,@grade_level_short);
  END IF;

  UPDATE student_mp_stats g
    INNER JOIN (
	SELECT s.student_id,
		SUM(s.weighted_gp/sc.reporting_gp_scale)/COUNT(*) AS cum_weighted_factor,
		SUM(s.unweighted_gp/s.gp_scale)/COUNT(*) AS cum_unweighted_factor
	FROM student_report_card_grades s
	INNER JOIN schools sc ON sc.id=s.school_id
	LEFT JOIN course_periods p ON p.course_period_id=s.course_period_id
	WHERE p.marking_period_id IS NULL OR p.marking_period_id=s.marking_period_id
	GROUP BY student_id) gg ON gg.student_id=g.student_id
    SET g.cum_unweighted_factor=gg.cum_unweighted_factor, g.cum_weighted_factor=gg.cum_weighted_factor
    WHERE g.student_id=s_id;*/

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

DROP FUNCTION IF EXISTS `CREDIT`;
DELIMITER $$
CREATE FUNCTION `CREDIT`(
 	cp_id int,
 	mp_id int
 ) RETURNS decimal(10,3)
BEGIN
  SELECT credits,IF(ISNULL(marking_period_id),'Y',marking_period_id),mp INTO @credits,@marking_period_id,@mp FROM course_periods WHERE course_period_id=cp_id;
   SELECT mp_type INTO @mp_type FROM marking_periods WHERE marking_period_id=mp_id;
  
IF @marking_period_id='Y' THEN 
RETURN @credits;
   ELSEIF   @marking_period_id=mp_id THEN
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
DELIMITER ;

DROP FUNCTION IF EXISTS `STUDENT_DISABLE`;
DELIMITER $$
CREATE FUNCTION `STUDENT_DISABLE`(
stu_id int
) RETURNS int(1)
BEGIN
UPDATE students set is_disable ='Y' where (select end_date from student_enrollment where  student_id=stu_id ORDER BY id DESC LIMIT 1) IS NOT NULL AND (select end_date from student_enrollment where  student_id=stu_id ORDER BY id DESC LIMIT 1)< CURDATE() AND  student_id=stu_id;
RETURN 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS `SEAT_COUNT`;
DELIMITER $$
CREATE PROCEDURE `SEAT_COUNT`() 
BEGIN
UPDATE course_periods SET filled_seats=filled_seats-1 WHERE COURSE_PERIOD_ID IN (SELECT COURSE_PERIOD_ID FROM schedule WHERE end_date IS NOT NULL AND end_date < CURDATE() AND dropped='N');
UPDATE schedule SET dropped='Y' WHERE end_date IS NOT NULL AND end_date < CURDATE() AND dropped='N';
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS `SEAT_FILL`;
DELIMITER $$
CREATE PROCEDURE `SEAT_FILL`() 
BEGIN
UPDATE course_periods SET filled_seats=filled_seats+1 WHERE COURSE_PERIOD_ID IN (SELECT COURSE_PERIOD_ID FROM schedule WHERE dropped='Y' AND ( end_date IS NULL OR end_date >= CURDATE()));
UPDATE schedule SET dropped='N' WHERE dropped='Y' AND ( end_date IS NULL OR end_date >= CURDATE()) ;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS `TEACHER_REASSIGNMENT`;
DELIMITER $$
CREATE PROCEDURE `TEACHER_REASSIGNMENT`()
BEGIN
UPDATE course_periods cp,course_period_var cpv,teacher_reassignment tr,school_periods sp,marking_periods mp,staff st SET cp.title=CONCAT(sp.title,IF(cp.mp<>'FY',CONCAT(' - ',mp.short_name),''),IF(CHAR_LENGTH(cpv.days)<5,CONCAT(' - ',cpv.days),''),' - ',cp.short_name,' - ',CONCAT_WS(' ',st.first_name,st.middle_name,st.last_name)), cp.teacher_id=tr.teacher_id WHERE cpv.period_id=sp.period_id and cp.marking_period_id=mp.marking_period_id and st.staff_id=tr.teacher_id and cp.course_period_id=tr.course_period_id AND assign_date <= CURDATE() AND updated='N' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID; 
 UPDATE teacher_reassignment SET updated='Y' WHERE assign_date <=CURDATE() AND updated='N';
 END$$
DELIMITER ;


DROP PROCEDURE IF EXISTS `ATTENDANCE_CALC`;
DELIMITER $$
CREATE PROCEDURE `ATTENDANCE_CALC`(IN cp_id INT)
BEGIN
DELETE FROM missing_attendance WHERE COURSE_PERIOD_ID=cp_id;
INSERT INTO missing_attendance(SCHOOL_ID,SYEAR,SCHOOL_DATE,COURSE_PERIOD_ID,PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID) 
        SELECT s.ID AS SCHOOL_ID,acc.SYEAR,acc.SCHOOL_DATE,cp.COURSE_PERIOD_ID,cpv.PERIOD_ID, IF(tra.course_period_id=cp.course_period_id AND acc.school_date<tra.assign_date =true,tra.pre_teacher_id,cp.teacher_id) AS TEACHER_ID,
        cp.SECONDARY_TEACHER_ID FROM attendance_calendar acc INNER JOIN course_periods cp ON cp.CALENDAR_ID=acc.CALENDAR_ID INNER JOIN course_period_var cpv ON cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID 
        AND (cpv.COURSE_PERIOD_DATE IS NULL AND position(substring('UMTWHFS' FROM DAYOFWEEK(acc.SCHOOL_DATE) FOR 1) IN cpv.DAYS)>0 OR cpv.COURSE_PERIOD_DATE IS NOT NULL AND cpv.COURSE_PERIOD_DATE=acc.SCHOOL_DATE) 
        INNER JOIN schools s ON s.ID=acc.SCHOOL_ID LEFT JOIN teacher_reassignment tra ON (cp.course_period_id=tra.course_period_id) INNER JOIN schedule sch ON sch.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID 
        AND sch.student_id IN(SELECT student_id FROM student_enrollment se WHERE sch.school_id=se.school_id AND sch.syear=se.syear AND start_date<=acc.school_date AND (end_date IS NULL OR end_date>=acc.school_date))
        AND (cp.MARKING_PERIOD_ID IS NOT NULL AND cp.MARKING_PERIOD_ID IN (SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE UNION SELECT MARKING_PERIOD_ID FROM school_semesters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE UNION SELECT MARKING_PERIOD_ID FROM school_quarters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE) OR (cp.MARKING_PERIOD_ID IS NULL AND acc.school_date BETWEEN cp.begin_date AND cp.end_date))
        AND sch.START_DATE<=acc.SCHOOL_DATE AND (sch.END_DATE IS NULL OR sch.END_DATE>=acc.SCHOOL_DATE ) AND cpv.DOES_ATTENDANCE='Y' AND acc.SCHOOL_DATE<CURDATE() AND cp.course_period_id=cp_id 
        AND NOT EXISTS (SELECT '' FROM  attendance_completed ac WHERE ac.SCHOOL_DATE=acc.SCHOOL_DATE AND ac.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND ac.PERIOD_ID=cpv.PERIOD_ID 
        AND IF(tra.course_period_id=cp.course_period_id AND acc.school_date<=tra.assign_date =true,ac.staff_id=tra.pre_teacher_id,ac.staff_id=cp.teacher_id)) 
        GROUP BY acc.SCHOOL_DATE,cp.COURSE_PERIOD_ID,cp.TEACHER_ID,cpv.PERIOD_ID;
END$$
DELIMITER ;


DROP PROCEDURE IF EXISTS `ATTENDANCE_CALC_BY_DATE`;
DELIMITER $$
CREATE PROCEDURE `ATTENDANCE_CALC_BY_DATE`(IN sch_dt DATE,IN year INT,IN school INT)
BEGIN
 DELETE FROM missing_attendance WHERE SCHOOL_DATE=sch_dt AND SYEAR=year AND SCHOOL_ID=school;
 INSERT INTO missing_attendance(SCHOOL_ID,SYEAR,SCHOOL_DATE,COURSE_PERIOD_ID,PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID) SELECT s.ID AS SCHOOL_ID,acc.SYEAR,acc.SCHOOL_DATE,cp.COURSE_PERIOD_ID,cpv.PERIOD_ID, IF(tra.course_period_id=cp.course_period_id AND acc.school_date<tra.assign_date =true,tra.pre_teacher_id,cp.teacher_id) AS TEACHER_ID,cp.SECONDARY_TEACHER_ID FROM attendance_calendar acc INNER JOIN marking_periods mp ON mp.SYEAR=acc.SYEAR AND mp.SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN mp.START_DATE AND mp.END_DATE INNER JOIN course_periods cp ON cp.MARKING_PERIOD_ID=mp.MARKING_PERIOD_ID  AND cp.CALENDAR_ID=acc.CALENDAR_ID INNER JOIN course_period_var cpv ON cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cpv.DOES_ATTENDANCE='Y' LEFT JOIN teacher_reassignment tra ON (cp.course_period_id=tra.course_period_id) INNER JOIN school_periods sp ON sp.SYEAR=acc.SYEAR AND sp.SCHOOL_ID=acc.SCHOOL_ID AND sp.PERIOD_ID=cpv.PERIOD_ID AND (sp.BLOCK IS NULL AND position(substring('UMTWHFS' FROM DAYOFWEEK(acc.SCHOOL_DATE) FOR 1) IN cpv.DAYS)>0 OR sp.BLOCK IS NOT NULL AND acc.BLOCK IS NOT NULL AND sp.BLOCK=acc.BLOCK) INNER JOIN schools s ON s.ID=acc.SCHOOL_ID INNER JOIN schedule sch ON sch.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND sch.START_DATE<=acc.SCHOOL_DATE AND (sch.END_DATE IS NULL OR sch.END_DATE>=acc.SCHOOL_DATE )  LEFT JOIN attendance_completed ac ON ac.SCHOOL_DATE=acc.SCHOOL_DATE AND IF(tra.course_period_id=cp.course_period_id AND acc.school_date<tra.assign_date =true,ac.staff_id=tra.pre_teacher_id,ac.staff_id=cp.teacher_id) AND ac.PERIOD_ID=sp.PERIOD_ID WHERE acc.SYEAR=year AND acc.SCHOOL_ID=school AND (acc.MINUTES IS NOT NULL AND acc.MINUTES>0) AND acc.SCHOOL_DATE=sch_dt AND ac.STAFF_ID IS NULL GROUP BY s.TITLE,acc.SCHOOL_DATE,cp.TITLE,cp.COURSE_PERIOD_ID,cp.TEACHER_ID;
END$$
DELIMITER ;
