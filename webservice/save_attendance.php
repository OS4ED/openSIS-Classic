<?php

    include '../Data.php';
    include 'function/DbGetFnc.php';
//    include '../functions/DbDateFnc.php';
//    include 'function/Current.php';
    include 'function/ParamLib.php';
    include 'function/function.php';
    include 'function/app_functions.php';
    header('Content-Type: application/json');
    $student_attendanceArr=$_REQUEST["student_attendanceArr"];
    $json_result=json_decode($student_attendanceArr,TRUE);      
    $teacher_id=$cp_id=$cpv_id=$mp_id=$date=$student_id=$attendance_code='';    
    
$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$_REQUEST['staff_id'] && $auth_data['user_profile']=='teacher')
    {
    if($_REQUEST['staff_id']!='')
    {
       $teacher_id=$_REQUEST['staff_id'];
    }
    if($_REQUEST['cpv_id']!='')
    {
       $cpv_id=$_REQUEST['cpv_id'];
    }
    if($_REQUEST['mp_id']!='')
    {
       $mp_id=$_REQUEST['mp_id'];
    }
//     if($_REQUEST['period_id']!='')
//         $period_id=clean_param($_REQUEST['period_id'], PARAM_ALPHANUM);
     
    if($_REQUEST['date']!='')
    {
         $date=  date('Y-m-d', strtotime($_REQUEST['date']));         
    }
//     if($_REQUEST['student_id']!='')
//         $student_id=clean_param($_REQUEST['student_id'], PARAM_ALPHANUM);
//     
//    if($_REQUEST['attendance_code']!='')
//         $attendance_code=clean_param($_REQUEST['attendance_code'], PARAM_ALPHANUM);

    $day=date("l", strtotime($_REQUEST['date'])); 
    $days=array('Monday'=>'M','Tuesday'=>'T','Wednesday'=>'W','Thursday'=>'H','Friday'=>'F','Saturday'=>'S','Sunday'=>'U');
    $day=$days[$day];
    $cpVarArr=  DBGet(DBQuery("Select * from course_period_var where id=$cpv_id and (days like '%$day%' OR days IS NULL) and does_attendance='Y'"));
    $period_id='';
    $does_attendance='';
    if(count($cpVarArr)>0)
    {
        $period_id=$cpVarArr[1]["PERIOD_ID"];
        $does_attendance=$cpVarArr[1]["DOES_ATTENDANCE"];
        $cp_id=$cpVarArr[1]["COURSE_PERIOD_ID"];
    }
    else 
    {
        $attendance['information']='Attendance can not be taken for this day!!';  
        echo json_encode($attendance);
        exit;
    }
foreach ($json_result as $value) 
{

    $student_id=$value["student_id"];
    $attendance_code=$value["attendance_code"];
 
    if($teacher_id!='' && $cpv_id!='' && $student_id!='' && $date!=''  && $attendance_code!='' && $period_id!='' && $mp_id!='')
    {
        $teach_info = DBGet(DBQuery("SELECT CURRENT_SCHOOL_ID,MAX(ssr.SYEAR) AS SYEAR
                            FROM staff s INNER JOIN staff_school_relationship ssr USING(staff_id),school_years sy
                            WHERE sy.school_id=s.current_school_id AND sy.syear=ssr.syear AND s.staff_id=ssr.staff_id AND s.PROFILE='teacher' AND s.STAFF_ID=".$teacher_id ));
        if($teach_info[1]['CURRENT_SCHOOL_ID']!='')
        {
            $teach_info=$teach_info[1];
            $_SESSION['UserSchool']=$teach_info['CURRENT_SCHOOL_ID'];
            $_SESSION['UserSyear']=$teach_info['SYEAR'];
            $current_Q = 'SELECT ATTENDANCE_TEACHER_CODE,STUDENT_ID,ADMIN,COMMENT FROM attendance_period WHERE SCHOOL_DATE=\''.$date.'\' AND PERIOD_ID=\''.$period_id.'\' AND COURSE_PERIOD_ID=\''.$cp_id.'\' AND STUDENT_ID=\''.$student_id.'\'';
            $current_RET = DBGet(DBQuery($current_Q)); 
//print_r($current_RET);exit;
            if(count($current_RET)>0)
            {
                $sql = 'UPDATE attendance_period SET ATTENDANCE_TEACHER_CODE=\''.$attendance_code.'\' ';
                        $sql .= ',ATTENDANCE_CODE=\''.$attendance_code.'\'';
                if(isset($_REQUEST['comment'][$student_id]))
                        { $cmnt=trim($_REQUEST['comment'][$student_id]);
                          $cmnt=clean_param($cmnt,PARAM_SPCL);
                                $sql .= ',COMMENT=\''.str_replace("'", "\'", $cmnt).'\'';}
                $sql .= ' WHERE SCHOOL_DATE=\''.$date.'\' AND COURSE_PERIOD_ID=\''.$cp_id.'\' AND STUDENT_ID=\''.$student_id.'\'';
            }
            else
            { 	
                    $sql = "INSERT INTO attendance_period (STUDENT_ID,SCHOOL_DATE,MARKING_PERIOD_ID,PERIOD_ID,COURSE_PERIOD_ID,ATTENDANCE_CODE,ATTENDANCE_TEACHER_CODE) values('$student_id','$date','$mp_id','".$period_id."','".$cp_id."','".$attendance_code."','".$attendance_code."')";		                        
            }
//            echo $sql; exit;
            DBQuery($sql);
            UpdateAttendanceDaily($student_id,$date);
            $query='SELECT \'completed\' AS COMPLETED FROM attendance_completed WHERE (STAFF_ID=\''.$teacher_id.'\' OR SUBSTITUTE_STAFF_ID=\''.  $teacher_id.'\') AND SCHOOL_DATE=\''.$date.'\' AND PERIOD_ID=\''.$period_id.'\'';
            $RET = DBGet(DBQuery($query));
            if(!count($RET))
            {
                $teacher_type=DBGet(DBQuery('SELECT TEACHER_ID,SECONDARY_TEACHER_ID FROM course_periods WHERE COURSE_PERIOD_ID=\''.$cp_id.'\''));
                $secondary_teacher_id=$teacher_type[1]['SECONDARY_TEACHER_ID'];
                $teacher_id1=$teacher_type[1]['TEACHER_ID'];
                if($secondary_teacher_id==  $teacher_id)
                    DBQuery('INSERT INTO attendance_completed (STAFF_ID,SCHOOL_DATE,PERIOD_ID,COURSE_PERIOD_ID,CPV_ID,SUBSTITUTE_STAFF_ID,IS_TAKEN_BY_SUBSTITUTE_STAFF) values(\''.$teacher_id.'\',\''.$date.'\',\''.$period_id.'\',\''.$cp_id.'\',\''.$cpv_id.'\',\''.$secondary_teacher_id.'\',\'Y\')');
                elseif($teacher_id1==  $teacher_id)
                    DBQuery('INSERT INTO attendance_completed (STAFF_ID,SCHOOL_DATE,PERIOD_ID,COURSE_PERIOD_ID,CPV_ID,SUBSTITUTE_STAFF_ID) values(\''.$teacher_id.'\',\''.$date.'\',\''.$period_id.'\',\''.$cp_id.'\',\''.$cpv_id.'\',\''.$secondary_teacher_id.'\')');
                else
                    DBQuery('INSERT INTO attendance_completed (STAFF_ID,SCHOOL_DATE,PERIOD_ID,COURSE_PERIOD_ID,CPV_ID,SUBSTITUTE_STAFF_ID) values(\''. $teacher_id.'\',\''.$date.'\',\''.$period_id.'\',\''.$cp_id.'\',\''.$cpv_id.'\',\''.$secondary_teacher_id.'\')');
            } 
            DBQuery('DELETE FROM missing_attendance WHERE  SCHOOL_DATE=\''.$date.'\' AND COURSE_PERIOD_ID=\''. $cp_id.'\'');
            $attendance['information']="Attendance taken.";
        }
    }
    else {
        $attendance['information']= "Please enter all information!!!";        
    }
  }
              
            $ma_count = 0;
            
            $login_RET = DBGet(DBQuery("SELECT PROFILE,STAFF_ID,CURRENT_SCHOOL_ID,FIRST_NAME,LAST_NAME,EMAIL,s.PROFILE_ID,IS_DISABLE,MAX(ssr.SYEAR) AS SYEAR,s.GENDER
                                FROM staff s INNER JOIN staff_school_relationship ssr USING(staff_id),school_years sy
                                WHERE sy.school_id=s.current_school_id AND sy.syear=ssr.syear AND s.STAFF_ID=".$teacher_id));
            
            $att_qry=DBGet(DBQuery('SELECT Count(1) as count FROM  profile_exceptions WHERE MODNAME 
                    IN (\'attendance/TakeAttendance.php\',\'attendance/DailySummary.php\',\'attendance/StudentSummary\') AND 
                    PROFILE_ID='.$login_RET[1][PROFILE_ID].' AND CAN_USE=\'Y\' '));

                      $reassign_cp=  DBGet(DBQuery('SELECT COURSE_PERIOD_ID ,TEACHER_ID,PRE_TEACHER_ID,ASSIGN_DATE FROM teacher_reassignment WHERE ASSIGN_DATE <= \''.date('Y-m-d').'\' AND UPDATED=\'N\' '));
                      foreach($reassign_cp as $re_key=>$reassign_cp_value)
                      {
                          if(strtotime($reassign_cp_value['ASSIGN_DATE'])<= strtotime(date('Y-m-d')))
                          {   
                          $get_pname=DBGet(DBQuery("SELECT CONCAT(sp.title,IF(cp.mp!='FY',CONCAT(' - ',mp.short_name),' '),IF(CHAR_LENGTH(cpv.days)<5,CONCAT(' - ',cpv.days),' '),' - ',cp.short_name,' - ',CONCAT_WS(' ',st.first_name,st.middle_name,st.last_name)) AS CP_NAME FROM course_periods cp,course_period_var cpv,school_periods sp,marking_periods mp,staff st WHERE cpv.period_id=sp.period_id and cp.marking_period_id=mp.marking_period_id and st.staff_id=".$reassign_cp_value['TEACHER_ID']."  AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=".$reassign_cp_value['COURSE_PERIOD_ID']));
                          $get_pname=$get_pname[1]['CP_NAME'];
                          DBQuery('UPDATE course_periods SET title=\''.$get_pname.'\', teacher_id='.$reassign_cp_value['TEACHER_ID'].' WHERE COURSE_PERIOD_ID='.$reassign_cp_value['COURSE_PERIOD_ID']); 
                          DBQuery('UPDATE teacher_reassignment SET updated=\'Y\' WHERE assign_date <=CURDATE() AND updated=\'N\' AND COURSE_PERIOD_ID='.$reassign_cp_value['COURSE_PERIOD_ID']);
                          DBQuery('UPDATE missing_attendance SET TEACHER_ID='.$reassign_cp_value['TEACHER_ID'].' WHERE TEACHER_ID='.$reassign_cp_value['PRE_TEACHER_ID'].' AND COURSE_PERIOD_ID='.$reassign_cp_value['COURSE_PERIOD_ID']); 
                          }

                      }
              $schedule_exit=DBGet(DBQuery('SELECT ID FROM schedule WHERE syear=\''.$login_RET[1][SYEAR].'\' AND school_id=\''.$login_RET[1][CURRENT_SCHOOL_ID].'\' LIMIT 0,1'));
              if($schedule_exit[1]['ID']!='')
              {
                  $last_update=DBGet(DBQuery('SELECT VALUE FROM program_config WHERE PROGRAM=\'MissingAttendance\' AND TITLE=\'LAST_UPDATE\' AND SYEAR=\''.$login_RET[1][SYEAR].'\' AND SCHOOL_ID=\''.$login_RET[1][CURRENT_SCHOOL_ID].'\''));
                  if($last_update[1]['VALUE']!='')
                      {
                          if($last_update[1]['VALUE'] < date('Y-m-d'))
                          {
//                                                  echo '<script type=text/javascript>calculate_missing_atten();</script>';
                                $syear = $login_RET[1][SYEAR];
                                $flag=FALSE;
                                $RET=DBGet(DBQuery('SELECT SCHOOL_ID,SCHOOL_DATE,COURSE_PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID FROM missing_attendance WHERE SYEAR=\''.  $login_RET[1][SYEAR].'\' AND SCHOOL_ID=\''.$login_RET[1][CURRENT_SCHOOL_ID].'\' LIMIT 0,1'));
                                 if (count($RET))
                                {
                                     $flag=TRUE;
                                 }
                                $last_update=DBGet(DBQuery('SELECT VALUE FROM program_config WHERE PROGRAM=\'MissingAttendance\' AND TITLE=\'LAST_UPDATE\' AND SYEAR=\''.$login_RET[1][SYEAR].'\' AND SCHOOL_ID=\''.$login_RET[1][CURRENT_SCHOOL_ID].'\''));
                                $last_update=trim($last_update[1]['VALUE']);
                                DBQuery("INSERT INTO missing_attendance(SCHOOL_ID,SYEAR,SCHOOL_DATE,COURSE_PERIOD_ID,PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID) 
                                        SELECT s.ID AS SCHOOL_ID,acc.SYEAR,acc.SCHOOL_DATE,cp.COURSE_PERIOD_ID,cpv.PERIOD_ID, IF(tra.course_period_id=cp.course_period_id AND acc.school_date<tra.assign_date =true,tra.pre_teacher_id,cp.teacher_id) AS TEACHER_ID,
                                        cp.SECONDARY_TEACHER_ID FROM attendance_calendar acc INNER JOIN course_periods cp ON cp.CALENDAR_ID=acc.CALENDAR_ID INNER JOIN course_period_var cpv ON cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID 
                                        AND (cpv.COURSE_PERIOD_DATE IS NULL AND position(substring('UMTWHFS' FROM DAYOFWEEK(acc.SCHOOL_DATE) FOR 1) IN cpv.DAYS)>0 OR cpv.COURSE_PERIOD_DATE IS NOT NULL AND cpv.COURSE_PERIOD_DATE=acc.SCHOOL_DATE)
                                        INNER JOIN schools s ON s.ID=acc.SCHOOL_ID LEFT JOIN teacher_reassignment tra ON (cp.course_period_id=tra.course_period_id) INNER JOIN schedule sch ON sch.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID 
                                        AND sch.student_id IN(SELECT student_id FROM student_enrollment se WHERE sch.school_id=se.school_id AND sch.syear=se.syear AND start_date<=acc.school_date AND (end_date IS NULL OR end_date>=acc.school_date))
                                        AND (cp.MARKING_PERIOD_ID IN (SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE UNION SELECT MARKING_PERIOD_ID FROM school_semesters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE UNION SELECT MARKING_PERIOD_ID FROM school_quarters WHERE SCHOOL_ID=acc.SCHOOL_ID AND acc.SCHOOL_DATE BETWEEN START_DATE AND END_DATE) OR acc.school_date BETWEEN cp.begin_date AND cp.end_date)
                                        AND sch.START_DATE<=acc.SCHOOL_DATE AND (sch.END_DATE IS NULL OR sch.END_DATE>=acc.SCHOOL_DATE ) AND cpv.DOES_ATTENDANCE='Y' AND acc.SCHOOL_DATE<=CURDATE() AND acc.SCHOOL_DATE > '".$last_update."' AND acc.syear=$syear AND acc.SCHOOL_ID='".$login_RET[1][CURRENT_SCHOOL_ID]."' 
                                        AND NOT EXISTS (SELECT '' FROM  attendance_completed ac WHERE ac.SCHOOL_DATE=acc.SCHOOL_DATE AND ac.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND ac.PERIOD_ID=cpv.PERIOD_ID)  AND isDateInMarkingPeriodWorkingDates(cp.marking_period_id, acc.SCHOOL_DATE) 
                                        GROUP BY acc.SCHOOL_DATE,cp.COURSE_PERIOD_ID,cpv.PERIOD_ID");

                                DBQuery("UPDATE program_config SET VALUE=CURDATE() WHERE PROGRAM='MissingAttendance' AND TITLE='LAST_UPDATE'");

                                $RET=DBGet(DBQuery("SELECT SCHOOL_ID,SCHOOL_DATE,COURSE_PERIOD_ID,TEACHER_ID,SECONDARY_TEACHER_ID FROM missing_attendance WHERE SYEAR='".  $login_RET[1][SYEAR]."' LIMIT 0,1"));
                          }
                      }
              }
              $notes_RET = DBGet(DBQuery('SELECT IF(pn.school_id IS NULL,\'All School\',(SELECT TITLE FROM schools WHERE id=pn.school_id)) AS SCHOOL,pn.LAST_UPDATED,CONCAT(\'<b>\',pn.TITLE,\'</b>\') AS TITLE,pn.CONTENT 
                              FROM portal_notes pn
                              WHERE pn.SYEAR=\''.$login_RET[1][SYEAR].'\' AND pn.START_DATE<=CURRENT_DATE AND 
                                  (pn.END_DATE>=CURRENT_DATE OR pn.END_DATE IS NULL)
                                  AND (pn.school_id IS NULL OR pn.school_id IN('.  GetUserSchoolsWs($login_RET[1][STAFF_ID],$login_RET[1][SYEAR],$login_RET[1][PROFILE_ID], true).'))
                                  AND ('.($login_RET[1][PROFILE_ID]==''?' FIND_IN_SET(\'teacher\', pn.PUBLISHED_PROFILES)>0':' FIND_IN_SET('.$login_RET[1][PROFILE_ID].',pn.PUBLISHED_PROFILES)>0)').'
                                  ORDER BY pn.SORT_ORDER,pn.LAST_UPDATED DESC'),array('LAST_UPDATED'=>'ProperDate','CONTENT'=>'_nl2br'));

          $events_RET = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                  FROM calendar_events ce,calendar_events_visibility cev,schools s
                  WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                      AND ce.SYEAR=\''.$login_RET[1][SYEAR].'\'
                      AND ce.school_id IN('.  GetUserSchoolsWs($login_RET[1][STAFF_ID],$login_RET[1][SYEAR],$login_RET[1][PROFILE_ID], true).')
                      AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=cev.CALENDAR_ID 
                      AND '.($login_RET[1][PROFILE_ID]==''?'cev.PROFILE=\'teacher\'':'cev.PROFILE_ID='.$login_RET[1][PROFILE_ID]).' 
                      ORDER BY ce.SCHOOL_DATE,s.TITLE'),array('SCHOOL_DATE'=>'ProperDate','DESCRIPTION'=>'makeDescription'));
          $events_RET1 = DBGet(DBQuery('SELECT ce.TITLE,ce.DESCRIPTION,ce.SCHOOL_DATE,s.TITLE AS SCHOOL 
                  FROM calendar_events ce,schools s
                  WHERE ce.SCHOOL_DATE BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL 30 DAY 
                      AND ce.SYEAR=\''.$login_RET[1][SYEAR].'\'
                      AND s.ID=ce.SCHOOL_ID AND ce.CALENDAR_ID=0 ORDER BY ce.SCHOOL_DATE,s.TITLE'),array('SCHOOL_DATE'=>'ProperDate','DESCRIPTION'=>'makeDescription'));
              $event_count=count($events_RET)+1;
            foreach ($events_RET1 as $events_RET_key => $events_RET_value) 
            {
                $events_RET[$event_count]=$events_RET_value;
                $event_count++;
            }

  $RET=DBGet(DBQuery('SELECT DISTINCT s.TITLE AS SCHOOL,mi.SCHOOL_DATE,cp.TITLE AS TITLE,mi.COURSE_PERIOD_ID,mi.PERIOD_ID,cpv.ID AS CPV_ID 
      FROM missing_attendance mi,schools s,course_periods cp,course_period_var cpv WHERE s.ID=mi.SCHOOL_ID AND  cp.COURSE_PERIOD_ID=mi.COURSE_PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND (mi.TEACHER_ID=\''.$login_RET[1][STAFF_ID].'\' OR mi.SECONDARY_TEACHER_ID=\''.  $login_RET[1][STAFF_ID].'\' ) AND mi.SCHOOL_ID=\''.$login_RET[1][CURRENT_SCHOOL_ID].'\' AND mi.SYEAR=\''.$login_RET[1][SYEAR].'\' AND mi.SCHOOL_DATE < \''.date('Y-m-d').'\' AND (mi.SCHOOL_DATE=cpv.COURSE_PERIOD_DATE OR POSITION(IF(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\') LIKE \'Thu\',\'H\',(IF(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\') LIKE \'Sun\',\'U\',SUBSTR(DATE_FORMAT(mi.SCHOOL_DATE,\'%a\'),1,1)))) IN cpv.DAYS)>0) ORDER BY cp.TITLE,mi.SCHOOL_DATE '),array('SCHOOL_DATE'=>'ProperDate'));
  $codes_RET_count = DBGet(DBQuery('SELECT COUNT(*) AS CODES FROM attendance_codes WHERE SCHOOL_ID=\''.$login_RET[1][CURRENT_SCHOOL_ID].'\' AND SYEAR=\''.$login_RET[1][SYEAR].'\'  AND TYPE=\'teacher\' AND TABLE_NAME=\'0\' ORDER BY SORT_ORDER'));
  $ma_count = count($RET);
  $attendance['missing_attendance_count'] = $ma_count;
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
    echo json_encode($attendance);
 ?>