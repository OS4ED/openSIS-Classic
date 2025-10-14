<?php
include '../Data.php';
include 'function/DbGetFnc.php';
//include '../functions/DbDateFnc.php';
//include 'function/Current.php';
include 'function/ParamLib.php';
include 'function/app_functions.php';
include 'function/function.php';
header('Content-Type: application/json');   

$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
$sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
$protocol = substr($sp, 0, strpos($sp, "/")) . $s;
$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
$scr_path = explode('/webservice/',$_SERVER['SCRIPT_NAME']);
$file_path = $scr_path[0];

$htpath=$protocol . "://" . $_SERVER['SERVER_NAME'] . $port;
if($file_path!='')
$htpath=$htpath."/".$file_path;
$htpath=$htpath."/assets/studentphotos/";

    $path ='../assets/studentphotos/';
    $teacher_id=$cp_id='';
    $school_id= $_REQUEST['school_id'];

$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$_REQUEST['staff_id'] && $auth_data['user_profile']==$_REQUEST['profile'])
    {
    if($_REQUEST['staff_id']!='')
    {
        $teacher_id=$_REQUEST['staff_id'];
        $login_RET = DBGet(DBQuery("SELECT PROFILE,STAFF_ID,CURRENT_SCHOOL_ID,FIRST_NAME,LAST_NAME,EMAIL,s.PROFILE_ID,IS_DISABLE,MAX(ssr.SYEAR) AS SYEAR,s.GENDER
                                FROM staff s INNER JOIN staff_school_relationship ssr USING(staff_id),school_years sy
                                WHERE sy.school_id=s.current_school_id AND sy.syear=ssr.syear AND s.STAFF_ID=".$teacher_id));
    }
    if($_REQUEST['cpv_id']!='')
    {
        $cpv_id=$_REQUEST['cpv_id'];
            $cp_id=DBGet(DBQuery('SELECT COURSE_PERIOD_ID,DOES_ATTENDANCE FROM course_period_var WHERE ID='.$cpv_id));
            
            if($cp_id[1]['DOES_ATTENDANCE']=='Y')
            $cp_id=$cp_id[1]['COURSE_PERIOD_ID'];
        else {
                $cp['success'] = 0;
                $cp['error_msg']='You cannot take attendance for this period on this day';  
                echo json_encode($cp);
                exit;
        }
    }
     
//     if($_REQUEST['mp_id']!='')
//         $mp_id=clean_param($_REQUEST['mp_id'], PARAM_ALPHANUM);
     
    if($_REQUEST['date']!='')
    {
        $day_arr=array("Monday"=>"M","Tuesday"=>"T","Wednesday"=>"W","Thursday"=>"H","Friday"=>"F","Saturday"=>"S","Sunday"=>"U");
       // $date=clean_param($_REQUEST['date'], PARAM_ALPHANUM);
//        $date=  date_parse($date);
         $date=  date('Y-m-d', strtotime($_REQUEST['date']));
         $day=date('l',strtotime($_REQUEST['date']));
         $day=$day_arr[$day];
         
         $mp_id = GetCurrentMPWs('QTR',$date,$login_RET[1][SYEAR],$school_id);
        if(!$mp_id)
            $mp_id = GetCurrentMPWs('SEM',$date,$login_RET[1][SYEAR],$school_id);
        if(!$mp_id)
            $mp_id = GetCurrentMPWs('FY',$date,$login_RET[1][SYEAR],$school_id);
    }
    if($teacher_id!='' && $cp_id!='' && $date!='')
    {
        $teach_info = DBGet(DBQuery("SELECT CURRENT_SCHOOL_ID,MAX(ssr.SYEAR) AS SYEAR
                            FROM staff s INNER JOIN staff_school_relationship ssr USING(staff_id),school_years sy
                            WHERE sy.school_id=s.current_school_id AND sy.syear=ssr.syear AND s.staff_id=ssr.staff_id AND s.PROFILE='teacher' AND s.STAFF_ID=".$teacher_id ));
        if($teach_info[1]['CURRENT_SCHOOL_ID']!='')
        {
            $teach_info=$teach_info[1];
            $_SESSION['UserSchool']=$teach_info['CURRENT_SCHOOL_ID'];
            $_SESSION['UserSyear']=$teach_info['SYEAR'];
            $defaultAttendanceCode=  DBGet(DBQuery("Select id from attendance_codes where default_code='Y' and SCHOOL_ID='".UserSchool()."' AND SYEAR='".UserSyear()."'"));
            $defaultCode=$defaultAttendanceCode[1]["ID"];
            if(!$mp_id) 
            {
                $periods_RET = array();
            }
            else 
            {
            $sql="SELECT CONCAT(s.LAST_NAME,', ',coalesce(s.COMMON_NAME,s.FIRST_NAME)) AS FULL_NAME,s.STUDENT_ID,s.GENDER FROM students s,course_periods cp,schedule ss,student_enrollment ssm,course_period_var cpv WHERE ssm.STUDENT_ID=s.STUDENT_ID AND cpv.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND cpv.ID=$cpv_id AND ssm.STUDENT_ID=ss.STUDENT_ID AND ssm.SCHOOL_ID='".UserSchool()."' AND ssm.SYEAR='".UserSyear()."' AND ssm.SYEAR=cp.SYEAR AND ssm.SYEAR=ss.SYEAR AND CASE WHEN $teacher_id =cp.teacher_id THEN cp.teacher_id=$teacher_id WHEN $teacher_id=cp.secondary_teacher_id THEN cp.secondary_teacher_id=$teacher_id ELSE cp.course_period_id IN(SELECT course_period_id from teacher_reassignment tra WHERE cp.course_period_id=tra.course_period_id AND tra.pre_teacher_id=$teacher_id) END AND (cpv.DAYS LIKE '%".$day."%' OR cpv.DAYS IS NULL) AND cp.COURSE_PERIOD_ID=$cp_id AND cp.COURSE_ID=ss.COURSE_ID AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID AND (ssm.START_DATE IS NOT NULL AND '$date'>=ssm.START_DATE AND ('$date'<=ssm.END_DATE OR ssm.END_DATE IS NULL)) AND ('$date'>=ss.START_DATE AND ('$date'<=ss.END_DATE OR ss.END_DATE IS NULL)) ORDER BY FULL_NAME";         
            $QI = DBQuery($sql);
            $periods_RET = DBGet($QI);
            }
            
            if(count($periods_RET)>0)
            {
                        $tot_count=count($periods_RET);
                        for($i=1;$i<=$tot_count;$i++)
                        {
                            $arr=$periods_RET[$i];
                            $stuPicPath=$path.$periods_RET[$i]['STUDENT_ID'].".JPG";
                            if(file_exists($stuPicPath))
                                $arr['PHOTO']=$htpath.$periods_RET[$i]['STUDENT_ID'].".JPG";
                            else 
                                $arr['PHOTO']="";
                            
                            $studentId=$periods_RET[$i]['STUDENT_ID'];
                            $sqlAttendanceArr=  DBGet(DBQuery("select attendance_code from attendance_period where student_id=$studentId and course_period_id=$cp_id AND SCHOOL_DATE='".$date."'"));                           
                            if(count($sqlAttendanceArr)==0)
                            {
//                                $arr['ATTENDANCE_CODE']="nill";
                                  $arr['ATTENDANCE_CODE']=$defaultCode;
                                  $attCode=$defaultCode;
                                $sqlStateCodeArr=  DBGet(DBQuery("select state_code from attendance_codes where id=$attCode"));
                                $arr['STATE_CODE']=$sqlStateCodeArr[1]["STATE_CODE"];
                            }
                            else 
                            {
                                $arr['ATTENDANCE_CODE']=$sqlAttendanceArr[1]["ATTENDANCE_CODE"];
                                $attCode=$sqlAttendanceArr[1]["ATTENDANCE_CODE"];
                                $sqlStateCodeArr=  DBGet(DBQuery("select state_code from attendance_codes where id=$attCode"));
                                $arr['STATE_CODE']=$sqlStateCodeArr[1]["STATE_CODE"];
                            }
                            if($arr['GENDER']=='')
                                $arr['GENDER']='';
                            $val[]=$arr;
                        }
                        $RET=  DBGet(DBQuery("Select Id,Title,SHORT_NAME,SORT_ORDER,TYPE,DEFAULT_CODE as DEFAULT_FOR_TEACHER,STATE_CODE from attendance_codes where SCHOOL_ID='".UserSchool()."' AND SYEAR='".UserSyear()."' and  type!='official' ORDER BY SORT_ORDER"));           
                        $tot_count=count($RET);
                        for($i=1;$i<=$tot_count;$i++)
                        {
                            $val1[]=$RET[$i];                        
                        }
                        $cp['attendance_code']=$val1;
                        $cp['error_msg']='nil';

                        
                        $cp['students']=$val;
                        $cp['error_msg']='nil';
                        $cp['success'] = 1;
            }
            else
            {
                $cp['success'] = 0;
                if(!$mp_id)
                    $cp['error_msg']='The selected date is not in a school quarter.';
                else    
                $cp['error_msg']='You cannot take attendance for this period on this day.';
//                $cp['error_msg']='No Course Period Found';
//                $cp['error_msg']='The selected date is not in a school quarter.';
            }
    }
    else
    {
        $cp['success'] = 0;
        if($teacher_id=='' && $cp_id=='' && $date=='') // && $mp_id==''

        $cp['error_msg']='Please Enter Teacher Id And Course Period Id And Date';
        
        elseif ($teacher_id=='') 
             $cp['error_msg']='Please Enter Teacher Id';
        elseif ($cp_id=='')
            $cp['error_msg']='Please Enter Course Period Id';   
//        elseif ($mp_id=='')
//            $cp['error_msg']='Please Enter Marking Period Id';  
        else 
            $cp['error_msg']='Please Enter Date';
        
    }
    }
    else {
        $cp['success'] = 0;
        $cp['error_msg']='No Course Period Found';
}
            $ma_count = 0;
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
  $cp['missing_attendance_count'] = $ma_count;
  
        if($cp_id!='')
        {
            $FQI = DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\'');
            $FRET = DBGet($FQI);
           
            $fy_id=$FRET[1]['MARKING_PERIOD_ID'];
            if(isset($_REQUEST['cpv_id']))
            {
                $QI = DBQuery('SELECT DISTINCT cpv.ID,cpv.PERIOD_ID,cp.COURSE_PERIOD_ID,sp.TITLE,sp.SHORT_NAME,cp.MARKING_PERIOD_ID,cpv.DAYS,sp.SORT_ORDER,c.TITLE AS COURSE_TITLE FROM course_periods cp,course_period_var cpv, school_periods sp,courses c WHERE c.COURSE_ID=cp.COURSE_ID AND cpv.PERIOD_ID=sp.PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cpv.COURSE_PERIOD_ID='.$cp_id.' AND cp.SYEAR=\''.UserSyear().'\' AND cp.SCHOOL_ID=\''.UserSchool().'\' AND (cp.TEACHER_ID=\''.$login_RET[1][STAFF_ID].'\' OR cp.SECONDARY_TEACHER_ID=\''.$login_RET[1][STAFF_ID].'\') ORDER BY sp.SORT_ORDER ');
                $RET = DBGet($QI); 
                
    //            $period_select = 'Choose Period: 
    //            <SELECT name=period onchange="document.location.href=\'Modules.php?modname='.$_REQUEST['modname'].'&period=\'+this.options[selectedIndex].value">';
    //    $period_select .= "<OPTION value='' selected>N/A</OPTION>";
                $fi=array();
                $i=0;
                foreach($RET as $period)
                {
                     $date1=  ucfirst(date("l",strtotime($_REQUEST['date'])));


                    $fi=str_split($period['DAYS']);
                    
                    $days_arr=array("Monday"=>'M',"Tuesday"=>'T',"Wednesday"=>'W',"Thursday"=>'H',"Friday"=>'F',"Saturday"=>'S',"Sunday"=>'U');
                     $d=$days_arr[$date1];
                     $cpv_data[$i]['id']=$period[ID];
                     $cpv_data[$i]['name']=$period['SHORT_NAME'].($period['MARKING_PERIOD_ID']!=$fy_id?' '.GetMP($period['MARKING_PERIOD_ID'],'SHORT_NAME',UserSyear(),UserSchool()):'').(strlen($period['DAYS'])<5?' '.$period['DAYS']:'').' - '.$period['COURSE_TITLE'];
                     $i++;
//                    $period_select .= "<OPTION value=$period[ID]".(($_REQUEST['cpv_id_miss_attn']==$period['ID'])?' SELECTED':'').">".$period['SHORT_NAME'].($period['MARKING_PERIOD_ID']!=$fy_id?' '.GetMP($period['MARKING_PERIOD_ID'],'SHORT_NAME'):'').(strlen($period['DAYS'])<5?' '.$period['DAYS']:'').' - '.$period['COURSE_TITLE'];                           

                }
    //    $period_select .= "</SELECT>"; 
            }
            else
            {

                $QI = DBQuery('SELECT DISTINCT cpv.ID,cpv.PERIOD_ID,cp.COURSE_PERIOD_ID,sp.TITLE,sp.SHORT_NAME,cp.MARKING_PERIOD_ID,cpv.DAYS,sp.SORT_ORDER,c.TITLE AS COURSE_TITLE FROM course_periods cp,course_period_var cpv, school_periods sp,courses c WHERE c.COURSE_ID=cp.COURSE_ID AND cpv.PERIOD_ID=sp.PERIOD_ID AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cpv.COURSE_PERIOD_ID='.$cp_id.' AND cp.SYEAR=\''.UserSyear().'\' AND cp.SCHOOL_ID=\''.UserSchool().'\' AND (cp.TEACHER_ID=\''.$login_RET[1][STAFF_ID].'\' OR cp.SECONDARY_TEACHER_ID=\''.$login_RET[1][STAFF_ID].'\') ORDER BY sp.SORT_ORDER ');
                $RET = DBGet($QI);

    //    $period_select = 'Choose Period: 
    //            <SELECT name=period onchange="document.location.href=\'Modules.php?modname='.$_REQUEST['modname'].'&period=\'+this.options[selectedIndex].value">';
    //    $period_select .= "<OPTION value='' selected>N/A</OPTION>";
                $fi=array();
                $i=0;
                foreach($RET as $period)
                {
                     $date1=  ucfirst(date("l",strtotime($_REQUEST['date'])));


                    $fi=str_split($period['DAYS']);

                    $days_arr=array("Monday"=>'M',"Tuesday"=>'T',"Wednesday"=>'W',"Thursday"=>'H',"Friday"=>'F',"Saturday"=>'S',"Sunday"=>'U');
                     $d=$days_arr[$date1];
                     $cpv_data[$i]['id']=$period[ID];
                     $cpv_data[$i]['name']=$period['SHORT_NAME'].($period['MARKING_PERIOD_ID']!=$fy_id?' '.GetMP($period['MARKING_PERIOD_ID'],'SHORT_NAME',UserSyear(),UserSchool()):'').(strlen($period['DAYS'])<5?' '.$period['DAYS']:'').' - '.$period['COURSE_TITLE'];
                     $i++;
//                     $period_select .= "<OPTION value=$period[ID]".((CpvId()==$period['ID'])?' SELECTED':'').">".$period['SHORT_NAME'].($period['MARKING_PERIOD_ID']!=$fy_id?' '.GetMP($period['MARKING_PERIOD_ID'],'SHORT_NAME'):'').(strlen($period['DAYS'])<5?' '.$period['DAYS']:'').' - '.$period['COURSE_TITLE']."</OPTION>";                           
//                    if(CpvId()==$period['ID'])
//                    {
//                            $_SESSION['UserPeriod'] = $period['PERIOD_ID'];
//                    }
                }
    //    $period_select .= "</SELECT>"; 
            }
    //    DrawHeader($period_select);
        }
        $cp['cpv_data'] = $cpv_data;
        $cp['selected_cpv_id'] = $_REQUEST['cpv_id'];
    }
    else 
    {
       $cp = array('success' => 0, 'msg' => 'Not authenticated user'); 
    }
}
else 
{
    $cp = array('success' => 0, 'msg' => 'Not authenticated user');
}
    echo json_encode($cp);
?>