<?php
    include '../Data.php';
    include 'function/DbGetFnc.php';
    include 'function/ParamLib.php';
    include 'function/app_functions.php';
    include 'function/function.php';
    
header('Content-Type: application/json');
$profile=$_REQUEST['profile'];
$staff_id = $_REQUEST['user_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];
$profile_id=$_REQUEST['profile_id'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$staff_id && $auth_data['user_profile']==$profile)
    {
        $teacher_info=array();

        if($profile=='teacher')
        {            
            $_SESSION['UserSchool'] = $_REQUEST['school_id'];
            $notes_RET = DBGet(DBQuery('SELECT IF(pn.school_id IS NULL,\'All School\',(SELECT TITLE FROM schools WHERE id=pn.school_id)) AS SCHOOL,pn.LAST_UPDATED,pn.TITLE AS TITLE,pn.CONTENT 
                            FROM portal_notes pn
                            WHERE pn.SYEAR=\''. UserSyear().'\' AND pn.START_DATE<=CURRENT_DATE AND 
                                (pn.END_DATE>=CURRENT_DATE OR pn.END_DATE IS NULL)
                                AND (pn.school_id IS NULL OR pn.school_id IN('.  GetUserSchoolsWs($staff_id,UserSyear(),$profile_id, true).'))
                                AND ('.($profile_id==''?' FIND_IN_SET(\'teacher\', pn.PUBLISHED_PROFILES)>0':' FIND_IN_SET('.$profile_id.',pn.PUBLISHED_PROFILES)>0)').'
                                ORDER BY pn.SORT_ORDER,pn.LAST_UPDATED DESC'),array('LAST_UPDATED'=>'ProperDate','CONTENT'=>'_nl2br'));
            if(count($notes_RET)>0)
            {
                foreach($notes_RET as $note)
                {
                    $notes_data[]= $note;
                }
            }
            else 
            {
                $notes_data = array();
            }
            $events_RET = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                    FROM calendar_events ce,calendar_events_visibility cev,schools s
                    WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                        AND ce.SYEAR=\''.UserSyear().'\'
                        AND ce.school_id IN('.  GetUserSchoolsWs($staff_id,UserSyear(),$profile_id, true).')
                        AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=cev.CALENDAR_ID 
                        AND '.($profile_id==''?'cev.PROFILE=\'teacher\'':'cev.PROFILE_ID='.$profile_id).' 
                        ORDER BY ce.SCHOOL_DATE,s.TITLE'),array('SCHOOL_DATE'=>'ProperDate','DESCRIPTION'=>'makeDescription'));
            $events_RET1 = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                    FROM calendar_events ce,schools s
                    WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                        AND ce.SYEAR=\''.UserSyear().'\'
                        AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=0 ORDER BY ce.SCHOOL_DATE,s.TITLE'),array('SCHOOL_DATE'=>'ProperDate','DESCRIPTION'=>'makeDescription'));
            $event_count=count($events_RET)+1;
            if(count($events_RET)>0)
            {
                foreach($events_RET as $event)
                {
                    $events_data[]= $event;
                }
            }
            else 
            {
                $events_data = array();
            }
            foreach ($events_RET1 as $events_RET_key => $events_RET_value) 
            {
                $events_RET[$event_count]=$events_RET_value;
                $event_count++;
            }
            $teacher_info['notes_count'] = count($notes_RET);
            $teacher_info['all_notes'] = $notes_data;
            $teacher_info['events_count'] = count($events_data);
            $teacher_info['all_events'] = $events_data;
            $teacher_info['success'] = 1;

        }
        elseif($profile=='student')
        {
            $_SESSION['UserSchool'] = $_REQUEST['school_id'];
            $_SESSION['STUDENT_ID'] = $_REQUEST['user_id'];
            $mp_id = $_SESSION['UserMP'] = $_REQUEST['mp_id'];
            $notes_RET = DBGet(DBQuery('SELECT IF(pn.school_id IS NULL,\'All School\',(SELECT TITLE FROM schools WHERE id=pn.school_id)) AS SCHOOL,pn.LAST_UPDATED,pn.TITLE AS TITLE,pn.CONTENT 
                                FROM portal_notes pn
                                WHERE pn.SYEAR=\''.UserSyear().'\' AND pn.START_DATE<=CURRENT_DATE AND 
                                    (pn.END_DATE>=CURRENT_DATE OR pn.END_DATE IS NULL)
                                    AND (pn.school_id IS NULL OR pn.school_id IN('. UserSchool().'))
                                    AND ('.($profile_id==''?' FIND_IN_SET(\'teacher\', pn.PUBLISHED_PROFILES)>0':' FIND_IN_SET('.$profile_id.',pn.PUBLISHED_PROFILES)>0)').'
                                    ORDER BY pn.SORT_ORDER,pn.LAST_UPDATED DESC'),array('LAST_UPDATED'=>'ProperDate','CONTENT'=>'_nl2br'));
            
            if(count($notes_RET)>0)
            {
                foreach($notes_RET as $note)
                {
                    $notes_data[]= $note;
                }
            }
            else 
            {
                $notes_data = array();
            }
            
            $events_RET = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                    FROM calendar_events ce,calendar_events_visibility cev,schools s
                    WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                        AND ce.SYEAR=\''.UserSyear().'\'
                        AND ce.school_id IN('. UserSchool().')
                        AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=cev.CALENDAR_ID 
                        AND '.($profile_id==''?'cev.PROFILE=\'teacher\'':'cev.PROFILE_ID='.$profile_id).' 
                        ORDER BY ce.SCHOOL_DATE,s.TITLE'),array('SCHOOL_DATE'=>'ProperDate','DESCRIPTION'=>'makeDescription'));
            $events_RET1 = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                    FROM calendar_events ce,schools s
                    WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                        AND ce.SYEAR=\''.UserSyear().'\'
                        AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=0 ORDER BY ce.SCHOOL_DATE,s.TITLE'),array('SCHOOL_DATE'=>'ProperDate','DESCRIPTION'=>'makeDescription'));
            $event_count=count($events_RET)+1;
            foreach ($events_RET1 as $events_RET_key => $events_RET_value) 
            {
                $events_RET[$event_count]=$events_RET_value;
                $event_count++;
            }
            if(count($events_RET)>0)
            {
                foreach($events_RET as $event)
                {
                    $events_data[]= $event;
                }
            }
            else 
            {
                $events_data = array();
            }
            $courses_RET=  DBGet(DBQuery('SELECT DISTINCT c.TITLE ,cp.COURSE_PERIOD_ID,cp.COURSE_ID,cp.TEACHER_ID AS STAFF_ID,cp.MARKING_PERIOD_ID AS MPI FROM schedule s,course_periods cp,courses c,attendance_calendar acc WHERE s.SYEAR=\''.UserSyear().'\' AND cp.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID AND (s.MARKING_PERIOD_ID IN (SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE  UNION SELECT MARKING_PERIOD_ID FROM school_semesters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE  UNION SELECT MARKING_PERIOD_ID FROM school_quarters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE )or s.MARKING_PERIOD_ID  is NULL)  AND (\''.date('Y-m-d').'\' BETWEEN s.START_DATE AND s.END_DATE OR \''.date('Y-m-d').'\'>=s.START_DATE AND s.END_DATE IS NULL) AND s.STUDENT_ID='.UserStudentIDWs().($profile=='teacher'?' AND cp.TEACHER_ID=\''.$staff_id.'\'':'').' AND c.COURSE_ID=cp.COURSE_ID ORDER BY (SELECT SORT_ORDER FROM school_periods WHERE PERIOD_ID=cp.course_period_id)'));
            $asgnmnts_data = array();
            foreach($courses_RET as $course)
            {
                $teacher_id = $course['STAFF_ID'];
                
                $assignments_Graded = DBGet(DBQuery( 'SELECT gg.STUDENT_ID,ga.ASSIGNMENT_ID,gg.POINTS,gg.COMMENT,ga.TITLE,ga.DESCRIPTION,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS AS POINTS_POSSIBLE,at.TITLE AS CATEGORY
                                                   FROM gradebook_assignments ga LEFT OUTER JOIN gradebook_grades gg
                                                  ON (gg.COURSE_PERIOD_ID=\''.$course[COURSE_PERIOD_ID].'\' AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.STUDENT_ID=\''.UserStudentIDWs().'\'),gradebook_assignment_types at
                                                  WHERE (ga.COURSE_PERIOD_ID=\''.$course[COURSE_PERIOD_ID].'\' OR ga.COURSE_ID=\''.$course[COURSE_ID].'\' AND ga.STAFF_ID=\''.$teacher_id.'\') AND ga.MARKING_PERIOD_ID=\''.$mp_id.'\'
                                                   AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND (gg.POINTS IS NOT NULL) AND (ga.POINTS!=\'0\' OR gg.POINTS IS NOT NULL AND gg.POINTS!=\'-1\') ORDER BY ga.ASSIGNMENT_ID DESC'));
            
                foreach($assignments_Graded AS $assignments_Graded)
                $GRADED_ASSIGNMENT_ID[]= $assignments_Graded['ASSIGNMENT_ID'];
                $ASSIGNMENT_ID_GRADED = implode(",", $GRADED_ASSIGNMENT_ID);

                $GRADED_ASSIGNMENT = '( '.$ASSIGNMENT_ID_GRADED.' )';
		   
		   
                $full_year_mp=DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID='.UserSchool().' AND SYEAR='.UserSyear()));
                $full_year_mp=$full_year_mp[1]['MARKING_PERIOD_ID'];
		$assignments_RET = array();  
                if(count($assignments_Graded))
		{
                    $assignments_RET = DBGet(DBQuery( 'SELECT ga.ASSIGNMENT_ID,ga.TITLE,ga.DESCRIPTION as COMMENT,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS AS POINTS_POSSIBLE,at.TITLE AS CATEGORY,(SELECT cs.TITLE FROM course_subjects cs LEFT JOIN courses c ON c.subject_id = cs.subject_id LEFT JOIN course_periods cp ON cp.course_id = c.course_id WHERE cp.course_period_id = ga.course_period_id) AS SUBJECT FROM gradebook_assignments ga, gradebook_assignment_types at    WHERE ga.ASSIGNMENT_ID NOT IN '.$GRADED_ASSIGNMENT.' AND (ga.COURSE_PERIOD_ID=\''.$course[COURSE_PERIOD_ID].'\' OR ga.COURSE_ID=\''.$course[COURSE_ID].'\' AND ga.STAFF_ID=\''.$teacher_id.'\') AND (ga.MARKING_PERIOD_ID=\''.$mp_id.'\'or ga.MARKING_PERIOD_ID='.$full_year_mp.')
                                                   AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND( CURRENT_DATE>=ga.ASSIGNED_DATE OR CURRENT_DATE<=ga.ASSIGNED_DATE)AND ga.DUE_DATE IS NOT NULL AND CURRENT_DATE<=ga.DUE_DATE
                                                   AND (ga.POINTS!=\'0\') ORDER BY ga.ASSIGNMENT_ID DESC'));
		}
                else
                {
                    $assignments_RET = DBGet(DBQuery( 'SELECT ga.ASSIGNMENT_ID,ga.TITLE,ga.DESCRIPTION as COMMENT,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS AS POINTS_POSSIBLE,at.TITLE AS CATEGORY,(SELECT cs.TITLE FROM course_subjects cs LEFT JOIN courses c ON c.subject_id = cs.subject_id LEFT JOIN course_periods cp ON cp.course_id = c.course_id WHERE cp.course_period_id = ga.course_period_id) AS SUBJECT
                                                   FROM gradebook_assignments ga
                                                 ,gradebook_assignment_types at
                                                  WHERE (ga.COURSE_PERIOD_ID=\''.$course[COURSE_PERIOD_ID].'\' OR ga.COURSE_ID=\''.$course[COURSE_ID].'\' AND ga.STAFF_ID=\''.$teacher_id.'\') AND (ga.MARKING_PERIOD_ID=\''.$mp_id.'\' or ga.MARKING_PERIOD_ID='.$full_year_mp.')
                                                   AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND( CURRENT_DATE>=ga.ASSIGNED_DATE OR CURRENT_DATE<=ga.ASSIGNED_DATE)AND ga.DUE_DATE IS NOT NULL AND CURRENT_DATE<=ga.DUE_DATE
                                                   AND (ga.POINTS!=\'0\') ORDER BY ga.ASSIGNMENT_ID DESC'));
		}
                if(count($assignments_RET)>0)
                {
                    foreach($assignments_RET as $asgnmnt)
                    {
                        $asgnmnts_data[]= $asgnmnt;
                    }
                }
            }
            
                
            $teacher_info['notes_count'] = count($notes_RET);
            $teacher_info['all_notes'] = $notes_data;
            $teacher_info['events_count'] = count($events_data);
            $teacher_info['all_events'] = $events_data;
            $teacher_info['assignments_count'] = count($asgnmnts_data);
            $teacher_info['all_assignments'] = $asgnmnts_data;
            $teacher_info['success'] = 1;
        }
        elseif($profile=='parent')
        {
            $_SESSION['STAFF_ID'] = $staff_id;
            $_SESSION['student_id'] = $_REQUEST['student_id'];
            $mp_id = $_SESSION['UserMP'] = $_REQUEST['mp_id'];
            
            $school_sql = "SELECT school_id FROM student_enrollment WHERE syear = ".$_REQUEST['syear']." AND student_id = ".$_REQUEST['student_id']." ORDER BY id DESC LIMIT 0,1"; // AND start_date <= '".date('Y-m-d')."' AND (end_date IS NULL OR end_date > '".date('Y-m-d')."')
            $school_RET = DBGet(DBQuery($school_sql));
            $_SESSION['UserSchool'] = $_REQUEST['school_id']=$school_RET[1]['SCHOOL_ID'];
            
            $notes_RET = DBGet(DBQuery('SELECT IF(pn.school_id IS NULL,\'All School\',(SELECT TITLE FROM schools WHERE id=pn.school_id)) AS SCHOOL,pn.LAST_UPDATED,pn.TITLE AS TITLE,pn.CONTENT 
                                FROM portal_notes pn
                                WHERE pn.SYEAR=\''.UserSyear().'\' AND pn.START_DATE<=CURRENT_DATE AND 
                                    (pn.END_DATE>=CURRENT_DATE OR pn.END_DATE IS NULL)
                                    AND (pn.school_id IS NULL OR pn.school_id IN('.  GetUserSchoolsWs($staff_id,UserSyear(),$profile_id, true).'))
                                    AND ('.($profile_id==''?' FIND_IN_SET(\'teacher\', pn.PUBLISHED_PROFILES)>0':' FIND_IN_SET('.$profile_id.',pn.PUBLISHED_PROFILES)>0)').'
                                    ORDER BY pn.SORT_ORDER,pn.LAST_UPDATED DESC'),array('LAST_UPDATED'=>'ProperDate','CONTENT'=>'_nl2br'));
                if(count($notes_RET)>0)
                {
                    foreach($notes_RET as $note)
                    {
                        $notes_data[]= $note;
                    }
                }
                else 
                {
                    $notes_data = array();
                }


                $events_RET = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                    FROM calendar_events ce,calendar_events_visibility cev,schools s
                    WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                        AND ce.SYEAR=\''.UserSyear().'\'
                        AND ce.school_id IN('.  GetUserSchoolsWs($login_uniform['USER_ID'],UserSyear(),$profile_id, true).')
                        AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=cev.CALENDAR_ID 
                        AND '.($profile_id==''?'cev.PROFILE=\'teacher\'':'cev.PROFILE_ID='.$profile_id).' 
                        ORDER BY ce.SCHOOL_DATE,s.TITLE'),array('SCHOOL_DATE'=>'ProperDate','DESCRIPTION'=>'makeDescription'));
                $events_RET1 = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                    FROM calendar_events ce,schools s
                    WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                        AND ce.SYEAR=\''.UserSyear().'\'
                        AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=0 ORDER BY ce.SCHOOL_DATE,s.TITLE'),array('SCHOOL_DATE'=>'ProperDate','DESCRIPTION'=>'makeDescription'));
                $event_count=count($events_RET)+1;
                foreach ($events_RET1 as $events_RET_key => $events_RET_value) 
                {
                    $events_RET[$event_count]=$events_RET_value;
                    $event_count++;
                }
                if(count($events_RET)>0)
                {
                    foreach($events_RET as $event)
                    {
                        $events_data[]= $event;
                    }
                }
                else 
                {
                    $events_data = array();
                }

            $courses_RET=  DBGet(DBQuery('SELECT DISTINCT c.TITLE ,cp.COURSE_PERIOD_ID,cp.COURSE_ID,cp.TEACHER_ID AS STAFF_ID FROM schedule s,course_periods cp,course_period_var cpv,courses c,attendance_calendar acc WHERE s.SYEAR=\''.UserSyear().'\' AND cp.COURSE_PERIOD_ID=s.COURSE_PERIOD_ID  AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID  AND (s.MARKING_PERIOD_ID IN (SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE  UNION SELECT MARKING_PERIOD_ID FROM school_semesters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE  UNION SELECT MARKING_PERIOD_ID FROM school_quarters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE )or s.MARKING_PERIOD_ID  is NULL) AND (\''.date('Y-m-d').'\' BETWEEN s.START_DATE AND s.END_DATE OR \''.date('Y-m-d').'\'>=s.START_DATE AND s.END_DATE IS NULL) AND s.STUDENT_ID=\''.UserStudentIDWs().'\' AND cp.GRADE_SCALE_ID IS NOT NULL'.(UserWs('PROFILE')=='teacher'?' AND cp.TEACHER_ID=\''.UserWs('STAFF_ID').'\'':'').' AND c.COURSE_ID=cp.COURSE_ID ORDER BY (SELECT SORT_ORDER FROM school_periods WHERE PERIOD_ID=cpv.PERIOD_ID)'));
            $asgnmnts_data = array();
foreach($courses_RET as $course)
	{
                $teacher_id = $course['STAFF_ID'];
            $assignments_Graded = DBGet(DBQuery( 'SELECT gg.STUDENT_ID,ga.ASSIGNMENT_ID,gg.POINTS,gg.COMMENT,ga.TITLE,ga.DESCRIPTION,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS AS POINTS_POSSIBLE,at.TITLE AS CATEGORY
                                                   FROM gradebook_assignments ga LEFT OUTER JOIN gradebook_grades gg
                                                      ON (gg.COURSE_PERIOD_ID=\''.$course[COURSE_PERIOD_ID].'\' AND gg.ASSIGNMENT_ID=ga.ASSIGNMENT_ID AND gg.STUDENT_ID=\''.UserStudentIDWs().'\'),gradebook_assignment_types at
                                                      WHERE (ga.COURSE_PERIOD_ID=\''.$course[COURSE_PERIOD_ID].'\' OR ga.COURSE_ID=\''.$course[COURSE_ID].'\' AND ga.STAFF_ID=\''.$teacher_id.'\') AND ga.MARKING_PERIOD_ID=\''.$mp_id.'\'
                                                   AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND (gg.POINTS IS NOT NULL) AND (ga.POINTS!=\'0\' OR gg.POINTS IS NOT NULL AND gg.POINTS!=\'-1\') ORDER BY ga.ASSIGNMENT_ID DESC'));
          
            foreach($assignments_Graded AS $assignments_Graded)
            $GRADED_ASSIGNMENT_ID[]= $assignments_Graded['ASSIGNMENT_ID'];
            $ASSIGNMENT_ID_GRADED = implode(",", $GRADED_ASSIGNMENT_ID);
           
           $GRADED_ASSIGNMENT = '( '.$ASSIGNMENT_ID_GRADED.' )';
		   
            $full_year_mp=DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID='.UserSchool().' AND SYEAR='.UserSyear()));
             $full_year_mp=$full_year_mp[1]['MARKING_PERIOD_ID'];
           
          if(count($assignments_Graded))
		  {
                    $assignments_RET = DBGet(DBQuery( 'SELECT ga.ASSIGNMENT_ID,ga.TITLE,ga.DESCRIPTION as COMMENT,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS AS POINTS_POSSIBLE,at.TITLE AS CATEGORY,(SELECT cs.TITLE FROM course_subjects cs LEFT JOIN courses c ON c.subject_id = cs.subject_id LEFT JOIN course_periods cp ON cp.course_id = c.course_id WHERE cp.course_period_id = ga.course_period_id) AS SUBJECT
                                                   FROM gradebook_assignments ga
                                                 ,gradebook_assignment_types at
                                                      WHERE ga.ASSIGNMENT_ID NOT IN '.$GRADED_ASSIGNMENT.' AND (ga.COURSE_PERIOD_ID=\''.$course[COURSE_PERIOD_ID].'\' OR ga.COURSE_ID='.$course[COURSE_ID].' AND ga.STAFF_ID='.$teacher_id.') AND (ga.MARKING_PERIOD_ID=\''.$mp_id.'\'or ga.MARKING_PERIOD_ID='.$full_year_mp.')
                                                   AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND(  CURRENT_DATE>=ga.ASSIGNED_DATE OR CURRENT_DATE<=ga.ASSIGNED_DATE )AND ga.DUE_DATE IS NOT NULL AND CURRENT_DATE<=ga.DUE_DATE
                                                   AND (ga.POINTS!=\'0\') ORDER BY ga.ASSIGNMENT_ID DESC'));
		   }
         else
		 {
                    $assignments_RET = DBGet(DBQuery( 'SELECT ga.ASSIGNMENT_ID,ga.TITLE,ga.DESCRIPTION as COMMENT,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS AS POINTS_POSSIBLE,at.TITLE AS CATEGORY,(SELECT cs.TITLE FROM course_subjects cs LEFT JOIN courses c ON c.subject_id = cs.subject_id LEFT JOIN course_periods cp ON cp.course_id = c.course_id WHERE cp.course_period_id = ga.course_period_id) AS SUBJECT
                                                   FROM gradebook_assignments ga
                                                 ,gradebook_assignment_types at
                                                      WHERE (ga.COURSE_PERIOD_ID=\''.$course[COURSE_PERIOD_ID].'\' OR ga.COURSE_ID=\''.$course[COURSE_ID].'\' AND ga.STAFF_ID=\''.$teacher_id.'\') AND (ga.MARKING_PERIOD_ID=\''.$mp_id.'\' or ga.MARKING_PERIOD_ID='.$full_year_mp.')
                                                   AND at.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND( CURRENT_DATE>=ga.ASSIGNED_DATE OR CURRENT_DATE<=ga.ASSIGNED_DATE)AND ga.DUE_DATE IS NOT NULL AND CURRENT_DATE<=ga.DUE_DATE
                                                   AND (ga.POINTS!=\'0\') ORDER BY ga.ASSIGNMENT_ID DESC'));
			}
     
                if(count($assignments_RET)>0)
		{
                    foreach($assignments_RET as $asgnmnt)
			{
                        $asgnmnts_data[]= $asgnmnt;
			}
		}
            }
                
                $teacher_info['notes_count'] = count($notes_RET);
                $teacher_info['all_notes'] = $notes_data;
                $teacher_info['events_count'] = count($events_RET);
                $teacher_info['all_events'] = $events_data;
            $teacher_info['assignments_count'] = count($asgnmnts_data);
            $teacher_info['all_assignments'] = $asgnmnts_data;
            $teacher_info['success'] = 1;
            }
    }   
    else 
    {
       $data = array('success' => 0, 'msg' => 'Not authenticated user'); 
    }
}
else 
{
    $data = array('success' => 0, 'msg' => 'Not authenticated user');
}

echo json_encode($teacher_info);
?>